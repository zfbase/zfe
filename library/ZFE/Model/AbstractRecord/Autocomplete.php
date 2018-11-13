<?php

/*
 * ZFE – платформа для построения редакторских интерфейсов.
 */

/**
 * Средства автодополнения.
 *
 * В настоящее время механизм автодополнений полностью автономен.
 * В следующей версии он будет интегрирован с историей, автогенерацией форм и пр.
 * Так же появится кастомизация списка полей.
 *
 * @category  ZFE
 */
trait ZFE_Model_AbstractRecord_Autocomplete
{
    use ZFE_Model_AbstractRecord_Autocomplete_Searcher;

    /**
     * Поля для выбора одного варианта с автодополнением.
     *
     * @todo Реализовать учет параметра обязательности поля.
     *
     * @example <code>
     * public static $autocompleteCols = [
     *     'theme_id' => [                          // имя элемента формы
     *         'label' => 'Тема (раздел)',          // название (подпись) элемента
     *         'source' => '/themes/autocomplete',  // адрес данных
     *         'canCreate' => true,                 // право на добавление новых значений
     *         'required' => false,                 // обязательность значения
     *         'relAlias' => 'Themes',              // псевдоним связи
     *         'relModel' => 'Themes',              // модель связи (если отличается от псевдонима)
     *     ],
     * ];
     * </code>
     *
     * @var array
     */
    public static $autocompleteCols = [];

    /**
     * Поля для выбора нескольких вариантов с автодополнением.
     *
     * @example <code>
     * public static $multiAutocompleteCols = [
     *     'tags' => [                            // имя элемента формы
     *         'label' => 'Теги',                 // название (подпись) элемента
     *         'source' => '/tags/autocomplete',  // адрес данных
     *         'canCreate' => true,               // право на добавление новых значений
     *         'min' => 0,                        // минимальное число значений
     *         'max' => 20,                       // максимальное число значений
     *         'relAlias' => 'Tags',              // псевдоним связи
     *         'relModel' => 'Tags',              // модель связи (если отличается от псевдонима)
     *         'sortable' => 'priority',          // поле для сортировки
     *     ],
     * ];
     * </code>
     *
     * @var array
     */
    public static $multiAutocompleteCols = [];

    /**
     * Дополнительные поля, отображаемые в автокомплитах.
     *
     * @example <code>
     * public static $autocompleteSelectCols = [
     *     'code',
     * ];
     * </code>
     *
     * @var array
     */
    public static $autocompleteSelectCols = [];

    /**
     * @param ZFE_Model_AbstractRecord $item
     *
     * @return array
     */
    public static function autocompleteItemToArray(ZFE_Model_AbstractRecord $item)
    {
        $data = [];
        $table = Doctrine_Core::getTable(static::class);
        foreach (static::$autocompleteSelectCols as $col) {
            if ($table->hasField($col)) {
                $data[$col] = $item[$col];
            }
        }
        return $data;
    }

    /**
     * Получить настройки поля выбора одной из записей другой таблицы с автодополнением.
     *
     * @param string $field
     *
     * @return array|bool
     */
    public static function getAutocompleteOptions($field)
    {
        $custom = static::$autocompleteCols[$field];

        // Проверяем, является ли поле автокомплитом
        if ( ! array_key_exists($field, static::$autocompleteCols)) {
            return false;
        }

        // Автоматически определяем значния по умолчанию
        /** @var $table ZFE_Model_Table */
        $table = Doctrine_Core::getTable(static::class);
        $relAlias = $table->getModelNameForColumn($field);
        $relModel = ! empty($custom['relModel']) ? $custom['relModel'] : $relAlias;
        $default = [
            'label' => static::getFieldName($field),
            'source' => $relModel::getAutocompleteUrl(),
            'canCreate' => false,
            'required' => $table->isElementRequiredForColumn($field),
            'relAlias' => $relAlias,
        ];

        return array_replace_recursive($default, $custom);
    }

    /**
     * Получить настройки поля выбора нескольких записей другой таблицы с автодополнением.
     *
     * @param string $field
     *
     * @throws ZFE_Model_Exception
     *
     * @return array|bool
     */
    public static function getMultiAutocompleteOptions($field)
    {
        // Проверяем, является ли поле автокомплитом
        if ( ! array_key_exists($field, static::$multiAutocompleteCols)) {
            return false;
        }

        // Автоматически определяем значения по умолчанию
        $custom = static::$multiAutocompleteCols[$field];
        if (empty($custom['relAlias'])) {
            throw new ZFE_Model_Exception('Для автодополнения нескольких значений необходимо указать связанную модель.');
        }
        $relAlias = $custom['relAlias'];

        $relModel = ! empty($custom['relModel']) ? $custom['relModel'] : $relAlias;

        $class = get_called_class();
        /** @var $table ZFE_Model_Table */
        $table = Doctrine_Core::getTable($class);
        /** @var $rel Doctrine_Relation_Association */
        $rel = $table->getRelation($relAlias);
        if (empty($rel)) {
            throw new ZFE_Model_Exception('Связь "' . $relAlias . '" не обнаружена в модели "' . $class . '" при определении свойств автодополнения нескольких значений.');
        }

        $default = [
            'label' => static::getFieldName($field),
            'source' => $relModel::getAutocompleteUrl(),
            'editUrl' => $relModel::getEditModalUrl(),
            'canCreate' => false,
            'min' => 0,
            'max' => null,
            'relAlias' => $relAlias,
            'relModel' => $relModel,
            'sortable' => $rel->getOrderByStatement(),
        ];

        return array_replace_recursive($default, static::$multiAutocompleteCols[$field]);
    }

    /**
     * Обработчик автодополнения одного значения для приведения записи к массиву.
     *
     * @param array $array
     *
     * @return array
     */
    protected function _autocompleteToArray(array $array)
    {
        foreach (static::$autocompleteCols as $key => $options) {
            $options = static::getAutocompleteOptions($key);
            $alias = $options['relAlias'];
            $array[$key] = [
                'id' => $this->{$alias}->exists() ? $this->{$alias}->id : '',
                'title' => $this->{$alias}->exists() ? $this->{$alias}->__toString() : '',
            ];
        }

        return $array;
    }

    /**
     * Обработчик автодополнения одного значения для получения записи из массива.
     *
     * @param array $array
     *
     * @return array
     */
    protected function _autocompleteFromArray(array $array)
    {
        foreach (static::$autocompleteCols as $key => $options) {
            $options = static::getAutocompleteOptions($key);
            if (isset($array[$key])) {
                $this->_linkOneByIdTitle(
                    $options['relAlias'],
                    $array[$key]['id'],
                    $array[$key]['title']
                );
                unset($array[$key]);
            }
        }

        return $array;
    }

    /**
     * Привязать по полям id и title.
     * Если поле id не опрделено создать новую запись со значением title и привязать его.
     *
     * @param string     $alias
     * @param int|string $id
     * @param string     $title
     */
    protected function _linkOneByIdTitle($alias, $id, $title)
    {
        $title = trim($title);

        if ( ! empty($id)) {
            if ($this->{$alias}->exists() && $this->{$alias}->id === $id) {
                return;
            }
            $this->link($alias, $id);
        } elseif ( ! empty($title)) {
            $modelName = $this->getTable()->getRelation($alias)->getClass();
            $item = new $modelName();
            $item->setTitle($title);
            $item->save();
            $this->link($alias, [$item->id]);
        } elseif ($this->{$alias}) {
            $this->{$alias} = null;
        }
    }

    /**
     * Обработчик автодополнения нескольких значений для приведения записи к массиву.
     *
     * @param array $array
     *
     * @return array
     */
    protected function _multiAutocompleteToArray(array $array)
    {
        foreach (static::$multiAutocompleteCols as $key => $options) {
            $options = static::getMultiAutocompleteOptions($key);
            $alias = $options['relAlias'];
            $model = $options['relModel'];
            $array[$key] = [];
            foreach ($this->{$alias} as $item) {
                $row = $model::autocompleteItemToArray($item) +
                    [
                        'id' => $item->id,
                        'title' => $item->__toString(),
                        'priority' => 0,
                    ];

                if ( ! empty($options['sortable'])) {
                    $rel = $this->getTable()->getRelation($alias);
                    if ($rel instanceof Doctrine_Relation_Association) {
                        $modelClassName = $rel->getAssociationTable()->getComponentName();
                        $localFieldName = $rel->getLocalFieldName();
                        $foreignFieldName = $rel->getForeignFieldName();

                        $row['priority'] = ZFE_Query::create()
                            ->select('x.' . $options['sortable'])
                            ->from($modelClassName . ' x')
                            ->where($localFieldName . ' = ?', $this->id)
                            ->andWhere($foreignFieldName . ' = ?', $item->id)
                            ->execute([], Doctrine_Core::HYDRATE_SINGLE_SCALAR);
                    } elseif ($rel instanceof Doctrine_Relation_ForeignKey) {
                        $row['priority'] = $item->{$options['sortable']};
                    }
                }

                $array[$key][] = $row;
            }
        }

        return $array;
    }

    /**
     * Обработчик автодополнения нескольких значений для получения записи из массива.
     *
     * @param array $array
     *
     * @return array
     */
    protected function _multiAutocompleteFromArray(array $array)
    {
        foreach (static::$multiAutocompleteCols as $key => $options) {
            $options = static::getMultiAutocompleteOptions($key);

            if ( ! isset($array[$key])) {
                $array[$key] = [];
            }

            $alias = $options['relAlias'];

            $ids = [];

            foreach ($array[$key] as $i => $item) {
                if ($item['id']) {
                    $ids[] = $item['id'];
                } else {
                    $modelName = $this->getTable()->getRelation($alias)->getClass();
                    $obj = new $modelName();
                    $obj->setTitle($item['title']);
                    $obj->save();
                    $ids[] = $obj->id;
                }
            }
            $this->_linkIds($alias, $ids);

            if ( ! empty($options['sortable'])) {
                $rel = $this->getTable()->getRelation($alias);
                if ($rel instanceof Doctrine_Relation_Association) {
                    $modelClassName = $rel->getAssociationTable()->getComponentName();
                    $localFieldName = $rel->getLocalFieldName();
                    $foreignFieldName = $rel->getForeignFieldName();
                    $modelTable = Doctrine_Core::getTable($modelClassName);

                    foreach ($array[$key] as $i => $item) {
                        $modelTable
                            ->createQuery()
                            ->update()
                            ->set($options['sortable'], $item['priority'])
                            ->where($localFieldName . ' = ?', $this->id)
                            ->andWhere($foreignFieldName . ' = ?', $item['id'])
                            ->execute();
                    }
                } elseif ($rel instanceof Doctrine_Relation_ForeignKey) {
                    $relTable = $rel->getTable();
                    foreach ($array[$key] as $i => $item) {
                        $relTable
                            ->createQuery()
                            ->update()
                            ->set($options['sortable'], '?', $item['priority'])
                            ->where($relTable->getIdentifier() . ' = ?', $item['id'])
                            ->execute();
                    }
                }
            }

            unset($array[$key]);
        }

        return $array;
    }

    /**
     * Обновить перичень привязанных записей по указанной связи и ID актуальных записей.
     *
     * @param string $alias
     * @param array  $new_ids
     */
    protected function _linkIds($alias, $new_ids)
    {
        $ids = $this->_getLinkedIds($alias);

        $ids_to_unlink = array_diff($ids, $new_ids);
        if ($ids_to_unlink) {
            $this->unlink($alias, $ids_to_unlink);
            $this->state($this->exists() ? Doctrine_Record::STATE_DIRTY : Doctrine_Record::STATE_TDIRTY);
        }

        $ids_to_link = array_diff($new_ids, $ids);
        if ($ids_to_link) {
            $this->link($alias, $ids_to_link);
            $this->state($this->exists() ? Doctrine_Record::STATE_DIRTY : Doctrine_Record::STATE_TDIRTY);
        }
    }

    /**
     * Получить список ID привязанных записей по указанной связи.
     *
     * @param string $alias
     *
     * @return array
     */
    protected function _getLinkedIds($alias)
    {
        $ids = [];
        foreach ($this->{$alias} as $item) {
            $ids[] = $item->id;
        }
        return $ids;
    }
}
