<?php

/*
 * ZFE – платформа для построения редакторских интерфейсов.
 */

/**
 * Модификация Doctrine_Table для авто генерации элементов форм.
 */
class ZFE_Model_Table extends Doctrine_Table
{
    /**
     * Кэш информации о колонках.
     *
     * @var array
     */
    protected $_formInfo = [];

    /**
     * Получить тип элемента формы, соответствущий столбцу модели.
     *
     * @param string $columnName
     *
     * @throws ZFE_Model_Exception
     *
     * @return null|string
     */
    public function getElementTypeForColumn($columnName)
    {
        if (!isset($this->_columns[$columnName])) {
            throw new ZFE_Model_Exception('Невозможно получить тип столбца: столбец "' . $columnName . '" не определен в модели "' . $this->getClassnameToReturn() . '"');
        }

        if (!isset($this->_formInfo[$columnName]) || !isset($this->_formInfo[$columnName]['type'])) {
            $formConfig = config('forms');
            $modelName = $this->getClassnameToReturn();

            if (in_array($columnName, $modelName::$booleanFields)) {
                $form = 'checkbox';
            }

            if (key_exists($columnName, $modelName::$autocompleteCols)) {
                $form = 'autocomplete';
            } elseif ($modelName::isDictionaryField($columnName)) {
                // Предполагается, что если поле словарное и словарь определен в модели,
                // то число вариантов не слишком много и вполне можно отобразить обычным
                // выпадающим списком. Исключения пока придется обрабатываться в ручную,
                // добавив поле в свойство $modelName::$autocompleteCols
                $form = 'select';
            } elseif ($this->isRelationColumn($columnName)) {
                // Внешние таблицы, не определенные как автокомплиты, становятся выпадающими списками
                $form = 'select';
            }

            if (!isset($form)) {
                switch ($this->_columns[$columnName]['type']) {
                    case 'integer':
                    case 'float':
                    case 'decimal':
                        $form = 'number';
                        break;
                    case 'json':
                    case 'string':
                        // Длина поля может быть не указана, например если тип MEDIUMTEXT
                        $length = $this->getElementMaxLengthForColumn($columnName);
                        $form = $length && $length < $formConfig->textarea->minLength
                            ? 'text'
                            : 'textarea';
                        break;
                    case 'timestamp':
                        $form = 'dateTimeLocal';
                        break;
                    case 'time':
                        $form = 'time';
                        break;
                    case 'date':
                        $form = 'date';
                        break;
                    default:
                        throw new ZFE_Model_Exception('Невозможно получить тип столбца: столбец "' . $columnName . '" имеет неизвестный тип');
                }
            }

            $this->_formInfo[$columnName]['type'] = $form;
        }

        return $this->_formInfo[$columnName]['type'];
    }

    /**
     * Получить максимальную длину элемента формы, соответствующего столбцу модели.
     *
     * @param string $columnName
     *
     * @throws ZFE_Model_Exception
     *
     * @return null|int
     */
    public function getElementMaxLengthForColumn($columnName)
    {
        if (!isset($this->_columns[$columnName])) {
            throw new ZFE_Model_Exception('Невозможно получить максимальную длину столбца: столбец "' . $columnName . '" не определен в модели "' . $this->getClassnameToReturn() . '"');
        }

        return $this->_columns[$columnName]['length'] ?? null;
    }

    /**
     * Проверить факт обязательности заполнения элемента формы, соответствующего столбцу модели.
     *
     * @param string $columnName
     *
     * @throws ZFE_Model_Exception
     *
     * @return null|bool
     */
    public function isElementRequiredForColumn($columnName)
    {
        if (!isset($this->_columns[$columnName])) {
            throw new ZFE_Model_Exception('Невозможно проверить обязательность столбца: столбец "' . $columnName . '" не определен в модели "' . $this->getClassnameToReturn() . '"');
        }

        return $this->_columns[$columnName]['notnull'] ?? null;
    }

    /**
     * Проверить факт допустимости только положительных значений элемента формы, соответствующего столбцу модели.
     *
     * @param string $columnName
     *
     * @throws ZFE_Model_Exception
     *
     * @return null|bool
     */
    public function isElementPositiveForColumn($columnName)
    {
        if (!isset($this->_columns[$columnName])) {
            throw new ZFE_Model_Exception('Невозможно проверить беззнаковость столбца: столбец "' . $columnName . '" не определен в модели "' . $this->getClassnameToReturn() . '"');
        }

        return $this->_columns[$columnName]['unsigned'] ?? null;
    }

    /**
     * Получить подпись элемента формы, соответствующего столбцу модели.
     *
     * @param string $columnName
     *
     * @throws ZFE_Model_Exception
     *
     * @return string
     */
    public function getElementLabelForColumn($columnName)
    {
        if (!isset($this->_columns[$columnName])) {
            throw new ZFE_Model_Exception('Невозможно получить заголовок столбца: столбец "' . $columnName . '" не определен в модели "' . $this->getClassnameToReturn() . '"');
        }

        if (!isset($this->_formInfo[$columnName]) || !isset($this->_formInfo[$columnName]['label'])) {
            $modelName = $this->getClassnameToReturn();
            $this->_formInfo[$columnName]['label'] = $modelName::getFieldName($columnName);
        }

        return $this->_formInfo[$columnName]['label'];
    }

    /**
     * Получить валидаторы для элемента формы, соответствующие столбцу модели.
     *
     * @param string $columnName
     *
     * @return array
     */
    public function getElementValidatorsForColumn($columnName)
    {
        if (!isset($this->_columns[$columnName])) {
            throw new ZFE_Model_Exception('Невозможно получить валидаторы столбца: столбец "' . $columnName . '" не определен в модели "' . $this->getClassnameToReturn() . '"');
        }

        if (!isset($this->_formInfo[$columnName]) || !isset($this->_formInfo[$columnName]['validators'])) {
            $maxLength = $this->getElementMaxLengthForColumn($columnName);

            $validators = [];

            switch ($this->getElementTypeForColumn($columnName)) {
                case 'number':
                    if ('integer' === $this->_columns[$columnName]['type']) {
                        $validators[] = ['Digits'];

                        // К сожалению, Doctrine не хранит настоящую длину для целых чисел
                        if ($this->isElementPositiveForColumn($columnName)) {
                            $validators[] = ['GreaterThan', false, [
                                'min' => 0,
                                'inclusive' => true,
                            ]];
                        }
                    } else {
                        // Стандартный хак: используем фиксированную локаль для взаимодействия
                        // с HTML5 элементом number, передающем значения в фиксированном формате
                        $validators[] = ['Float', false, ['locale' => 'en_US']];

                        if (isset($this->_columns[$columnName]['scale'])) {
                            $max = str_repeat('9', $maxLength - $this->_columns[$columnName]['scale']) .
                                '.' . str_repeat('9', $this->_columns[$columnName]['scale']);
                        } elseif (!$maxLength) {
                            break;
                        } else {
                            $max = str_repeat('9', $maxLength);
                        }

                        $min = $this->isElementPositiveForColumn($columnName)
                            ? 0
                            : -$max;

                        $validators[] = ['Between', false, [
                            'min' => $min,
                            'max' => $max,
                            'inclusive' => true,
                        ]];
                    }
                    break;
                case 'text':
                case 'textarea':
                    // Длина поля может быть не указана, например если тип MEDIUMTEXT
                    if ($maxLength) {
                        $validators[] = ['StringLength', false, [
                            'min' => 0,
                            'max' => $maxLength,
                            'encoding' => 'UTF-8',
                        ]];
                    }
                    break;
                case 'date':
                    $validators[] = ['Date', false, ['format' => 'yyyy-MM-dd']];
                    break;
                case 'time':
                    $validators[] = ['Date', false, ['format' => 'HH:mm:ss']];
                    break;
                case 'datetime':
                    $validators[] = ['Date', false, ['format' => 'yyyy-MM-ddTHH:mm:ss']];
                    break;
                case 'select':
                    $modelName = $this->getClassnameToReturn();
                    if ($modelName::isDictionaryField($columnName)) {
                        $list = $modelName::getDictionary($columnName);
                    } elseif ($this->isRelationColumn($columnName)) {
                        $relClass = $this->getModelNameForColumn($columnName);
                        $list = $relClass::getKeyValueList();
                    } else {
                        throw new ZFE_Model_Exception('Невозможно получить допустимые значения для столбца "' . $columnName . '" модели "' . $modelName . '"');
                    }
                    $validators[] = ['InArray', false, ['haystack' => array_keys($list)]];
                    break;
            }

            $this->_formInfo[$columnName]['validators'] = $validators;
        }

        return $this->_formInfo[$columnName]['validators'];
    }

    /**
     * Получить параметры элемента формы, соответствующего столбцу базы.
     *
     * @param string $columnName
     *
     * @return array
     */
    public function getElementOptionsForColumn($columnName)
    {
        $formConfig = config('forms');
        $maxLength = $this->getElementMaxLengthForColumn($columnName);

        $options = [];

        $options['label'] = $this->getElementLabelForColumn($columnName);
        $options['required'] = $this->isElementRequiredForColumn($columnName);
        $options['validators'] = $this->getElementValidatorsForColumn($columnName);

        if (isset($this->_columns[$columnName]['default'])) {
            $options['value'] = $this->_columns[$columnName]['default'];
        }

        switch ($this->getElementTypeForColumn($columnName)) {
            case 'textarea':
                if (isset($formConfig->textarea->rows->default) && $formConfig->textarea->rows->default > 0) {
                    $rows = $formConfig->textarea->rows->default;
                } elseif ($maxLength > 0) {
                    $rows = round($maxLength / $formConfig->textarea->cols->max);
                    if ($rows > $formConfig->textarea->rows->max) {
                        $rows = $formConfig->textarea->rows->max;
                    }
                }
                if ($rows > 0) {
                    $options['rows'] = $rows;
                }
                // no break
            case 'text':
                $options['filters'][] = 'StringTrim';
                if ($maxLength) {
                    $options['maxlength'] = $maxLength;
                }
                break;
            case 'number':
                if (isset($this->_columns[$columnName]['scale'])) {
                    $options['max'] = str_repeat('9', $maxLength - $this->_columns[$columnName]['scale']) .
                        '.' . str_repeat('9', $this->_columns[$columnName]['scale']);
                    $options['step'] = 1 / (10 ** $this->_columns[$columnName]['scale']);
                    $options['min'] = $this->isElementPositiveForColumn($columnName)
                        ? 0
                        : -$options['max'];
                } else {
                    // для поля integer длина всегда в модели оказывается равной 4 в независимости
                    // от длины integer в БД, по этому просто не накладываем никаких ограничений
                    $options['step'] = 1;
                }
                break;
            case 'datetime':
                $options['filters'][] = ['PregReplace', [
                    'match' => '/T/',
                    'replace' => ' ',
                ]];
                // no break
            case 'time':
                $options['filters'][] = ['Time'];
                // no break
            case 'date':
                $options['autocomplete'] = 'off';
                break;
            case 'select':
                $modelName = $this->getClassnameToReturn();
                if ($modelName::isDictionaryField($columnName)) {
                    $list = $modelName::getDictionary($columnName);
                } elseif ($this->isRelationColumn($columnName)) {
                    $relClass = $this->getModelNameForColumn($columnName);
                    $list = $relClass::getKeyValueList();
                } else {
                    throw new ZFE_Model_Exception('Невозможно получить допустимые значения для столбца "' . $columnName . '" модели "' . $modelName . '"');
                }
                if (!$options['required']) {
                    $list = [null => ''] + $list;
                }
                $options['multiOptions'] = $list;
                break;
            case 'autocomplete':
                $modelName = $this->getClassnameToReturn();
                $acOptions = $modelName::getAutocompleteOptions($columnName);
                $options = array_replace_recursive($options, $acOptions);
                break;
        }

        return $options;
    }

    /**
     * Получить имя класса модели связанного по внешнему ключу с соответствующим столбцом базы.
     *
     * @param string $columnName
     *
     * @return null|string
     */
    public function getModelNameForColumn($columnName)
    {
        foreach ($this->getRelations() as $name => $opt) {
            if ($columnName === $opt->getLocal()) {
                return $opt->getClass();
            }
        }
        return null;
    }

    /**
     * Получить псевдоним внешнего ключа, соответствующего столбцу базы.
     *
     * @param string $columnName
     *
     * @return null|string
     */
    public function getAliasForColumn($columnName)
    {
        foreach ($this->getRelations() as $name => $opt) {
            if ($columnName === $opt->getLocal()) {
                return $opt->getAlias();
            }
        }
        return null;
    }

    /**
     * Столбец связан внешним ключом?
     *
     * @param string $columnName
     *
     * @return bool
     */
    public function isRelationColumn($columnName)
    {
        foreach ($this->getRelations() as $name => $opt) {
            if ($columnName === $opt->getLocal()) {
                return true;
            }
        }
        return false;
    }

    /**
     * Найти запись по идентификатору даже если она удалена (deleted = 1).
     *
     * @see Doctrine_Table::find()
     * @see ZFE_Model_AbstractRecord_HotSelects::hardFind()
     *
     * @return mixed
     */
    public function hardFind()
    {
        if ($this->hasTemplate('ZFE_Model_Template_SoftDelete')) {
            $template = $this->getTemplate('ZFE_Model_Template_SoftDelete');

            $allowSoftDelete = $template->allowSoftDelete();
            $template->allowSoftDelete(false);
            $result = call_user_func_array([$this, 'find'], func_get_args());
            $template->allowSoftDelete($allowSoftDelete);
        } else {
            $result = call_user_func_array([$this, 'find'], func_get_args());
        }

        return $result;
    }

    //
    // Реестр служебных полей
    //

    protected $_serviceFields;

    public function clearServiceFields()
    {
        $this->_serviceFields = [];
    }

    public function addServiceField($field)
    {
        $this->_serviceFields[] = $field;
    }

    public function removeServiceField($field)
    {
        $this->_serviceFields = array_diff($this->_serviceFields, [$field]);
    }

    public function getServiceFields()
    {
        return $this->_serviceFields;
    }
}
