<?php

/*
 * ZFE – платформа для построения редакторских интерфейсов.
 */

/**
 * Коллекция стандартных элементов форм.
 *
 * Зависимости:
 * # ZFE_Form_Helpers
 */
trait ZFE_Form_Helpers_Templates
{
    /**
     * Добавить стандартное текстовое поле.
     *
     * @param string      $id
     * @param array       $customOptions
     * @param null|string $elementName
     *
     * @return Zend_Form
     */
    public function addTextElement($id, array $customOptions = [], $elementName = null)
    {
        $table = Doctrine_Core::getTable($this->_modelName);
        $columnOptions = $table->getElementOptionsForColumn($id);
        $options = array_replace_recursive($columnOptions, $customOptions);

        return $this->addElement('text', $elementName ?: $id, $options);
    }

    /**
     * Добавить стандартное многострочное текстовое поле.
     *
     * При указание класса необходимо добавить 'autosize'
     *
     * @param string      $id
     * @param array       $customOptions
     * @param null|string $elementName
     *
     * @return Zend_Form
     */
    public function addTextareaElement($id, array $customOptions = [], $elementName = null)
    {
        $table = Doctrine_Core::getTable($this->_modelName);
        $columnOptions = $table->getElementOptionsForColumn($id);
        $localOptions = [
            'class' => 'autosize',
        ];
        $options = array_replace_recursive($columnOptions, $localOptions, $customOptions);

        return $this->addElement('textarea', $elementName ?: $id, $options);
    }

    /**
     * Добавить стандартное многострочное текстовое поле с WYSIWYG редактором
     *
     * При указание класса необходимо добавить 'html-editor'
     *
     * @param string      $id
     * @param array       $customOptions
     * @param null|string $elementName
     *
     * @return Zend_Form
     */
    public function addWysiwygElement($id, array $customOptions = [], $elementName = null)
    {
        $localOptions = [
            'class' => 'html-editor',
            'rows' => 10,
        ];
        $options = array_replace_recursive($localOptions, $customOptions);

        return $this->addTextareaElement($elementName ?: $id, $options);
    }

    /**
     * Добавить стандартное поле пароля.
     *
     * @param string      $id
     * @param array       $customOptions
     * @param null|string $elementName
     *
     * @return Zend_Form
     */
    public function addPasswordElement($id, array $customOptions = [], $elementName = null)
    {
        $table = Doctrine_Core::getTable($this->_modelName);
        $localOptions = [
            'label' => $table->getElementLabelForColumn($id),
            'required' => $table->isElementRequiredForColumn($id),
            'filters' => [
                'StringTrim',
            ],
            'autocomplete' => 'off',
        ];
        $options = array_replace_recursive($localOptions, $customOptions);

        return $this->addElement('password', $elementName ?: $id, $options);
    }

    /**
     * Добавить стандартное поле для чисел.
     *
     * @param string      $id
     * @param array       $customOptions
     * @param null|string $elementName
     *
     * @return Zend_Form
     */
    public function addNumberElement($id, array $customOptions = [], $elementName = null)
    {
        $table = Doctrine_Core::getTable($this->_modelName);
        $columnOptions = $table->getElementOptionsForColumn($id);
        $options = array_replace_recursive($columnOptions, $customOptions);

        return $this->addElement('number', $elementName ?: $id, $options);
    }

    /**
     * Добавить стандартное поле для адреса электронной почты.
     *
     * @param string      $id
     * @param array       $customOptions
     * @param null|string $elementName
     *
     * @return Zend_Form
     */
    public function addEmailElement($id, array $customOptions = [], $elementName = null)
    {
        $table = Doctrine_Core::getTable($this->_modelName);
        $columnOptions = $table->getElementOptionsForColumn($id);
        $localOptions = [
            'validators' => [
                ['EmailAddressSimple'],
            ],
        ];
        $options = array_replace_recursive($columnOptions, $localOptions, $customOptions);

        return $this->addElement('email', $elementName ?: $id, $options);
    }

    /**
     * Добавить стандартное поле для URL.
     *
     * @param string      $id
     * @param array       $customOptions
     * @param null|string $elementName
     *
     * @return Zend_Form
     */
    public function addUrlElement($id, array $customOptions = [], $elementName = null)
    {
        $table = Doctrine_Core::getTable($this->_modelName);
        $columnOptions = $table->getElementOptionsForColumn($id);
        $localOptions = [
            'placeholder' => 'http://',  // HTML5 элемент input[type=url] требует указывать протокол
            'validators' => [
                ['Url'],
            ],
        ];
        $options = array_replace_recursive($columnOptions, $localOptions, $customOptions);

        return $this->addElement('url', $elementName ?: $id, $options);
    }

    /**
     * Добавить стандартное поле для телефона.
     *
     * @param string      $id
     * @param array       $customOptions
     * @param null|string $elementName
     *
     * @return Zend_Form
     */
    public function addTelElement($id, array $customOptions = [], $elementName = null)
    {
        $table = Doctrine_Core::getTable($this->_modelName);
        $columnOptions = $table->getElementOptionsForColumn($id);
        $options = array_replace_recursive($columnOptions, $customOptions);

        return $this->addElement('tel', $elementName ?: $id, $options);
    }

    /**
     * Добавить стандартное поле для даты со временем
     *
     * @param string      $id
     * @param array       $customOptions
     * @param null|string $elementName
     *
     * @return Zend_Form
     */
    public function addDateTimeElement($id, array $customOptions = [], $elementName = null)
    {
        $table = Doctrine_Core::getTable($this->_modelName);
        $columnOptions = $table->getElementOptionsForColumn($id);
        $options = array_replace_recursive($columnOptions, $customOptions);

        return $this->addElement('dateTimeLocal', $elementName ?: $id, $options);
    }

    /**
     * Добавить стандартное поле для даты.
     *
     * @param string      $id
     * @param array       $customOptions
     * @param null|string $elementName
     *
     * @return Zend_Form
     */
    public function addDateElement($id, array $customOptions = [], $elementName = null)
    {
        $table = Doctrine_Core::getTable($this->_modelName);
        $columnOptions = $table->getElementOptionsForColumn($id);
        $options = array_replace_recursive($columnOptions, $customOptions);

        return $this->addElement('date', $elementName ?: $id, $options);
    }

    /**
     * Добавить стандартное поле для времени.
     *
     * @param string      $id
     * @param array       $customOptions
     * @param null|string $elementName
     *
     * @return Zend_Form
     */
    public function addTimeElement($id, array $customOptions = [], $elementName = null)
    {
        $table = Doctrine_Core::getTable($this->_modelName);
        $columnOptions = $table->getElementOptionsForColumn($id);
        $options = array_replace_recursive($columnOptions, $customOptions);

        return $this->addElement('time', $elementName ?: $id, $options);
    }

    /**
     * Добавить стандартное поле для месяца.
     *
     * @param string      $id
     * @param array       $customOptions
     * @param null|string $elementName
     *
     * @return Zend_Form
     */
    public function addMonthElement($id, array $customOptions = [], $elementName = null)
    {
        $table = Doctrine_Core::getTable($this->_modelName);
        $columnOptions = $table->getElementOptionsForColumn($id);
        $localOptions = [
            'validators' => [
                ['Date', false, ['format' => 'yyyy-MM']],
            ],
        ];
        $options = array_replace_recursive($columnOptions, $localOptions, $customOptions);

        return $this->addElement('month', $elementName ?: $id, $options);
    }

    /**
     * Добавить стандартное поле для недели года.
     *
     * @param string      $id
     * @param array       $customOptions
     * @param null|string $elementName
     *
     * @return Zend_Form
     */
    public function addWeekElement($id, array $customOptions = [], $elementName = null)
    {
        $table = Doctrine_Core::getTable($this->_modelName);
        $columnOptions = $table->getElementOptionsForColumn($id);
        $localOptions = [
            'validators' => [
                ['Date', false, ['format' => 'yyyy-\WW']],
            ],
        ];
        $options = array_replace_recursive($columnOptions, $localOptions, $customOptions);

        return $this->addElement('week', $elementName ?: $id, $options);
    }

    /**
     * Добавить стандартное поле для цвета.
     *
     * @param string      $id
     * @param array       $customOptions
     * @param null|string $elementName
     *
     * @return Zend_Form
     */
    public function addColorElement($id, array $customOptions = [], $elementName = null)
    {
        $table = Doctrine_Core::getTable($this->_modelName);
        $columnOptions = $table->getElementOptionsForColumn($id);
        $localOptions = [
            'validators' => [
                ['Color'],
            ],
        ];
        $options = array_replace_recursive($columnOptions, $localOptions, $customOptions);

        return $this->addElement('color', $elementName ?: $id, $options);
    }

    /**
     * Добавить стандартное поле для выбора числа из диапазона.
     *
     * @param string      $id
     * @param array       $customOptions
     * @param null|string $elementName
     *
     * @return Zend_Form
     */
    public function addRangeElement($id, array $customOptions = [], $elementName = null)
    {
        $table = Doctrine_Core::getTable($this->_modelName);
        $columnOptions = $table->getElementOptionsForColumn($id);
        $options = array_replace_recursive($columnOptions, $customOptions);

        return $this->addElement('range', $elementName ?: $id, $options);
    }

    /**
     * Добавить стандартное поле продолжительности в секундах.
     *
     * @param string      $id
     * @param array       $customOptions
     * @param null|string $elementName
     *
     * @return Zend_Form
     */
    public function addDurationElement($id, array $customOptions = [], $elementName = null)
    {
        $table = Doctrine_Core::getTable($this->_modelName);
        $columnOptions = $table->getElementOptionsForColumn($id);
        $options = array_replace_recursive($columnOptions, $customOptions);
        $this->addElement('duration', $elementName ?: $id, $options);
        $this->getElement($elementName ?: $id)->removeValidator('Digits');

        return $this;
    }

    /**
     * Добавить стандартное поле для загрузки одного файла.
     *
     * @param string      $id
     * @param array       $customOptions
     * @param null|string $elementName
     *
     * @return Zend_Form
     */
    public function addUploadElement($id, array $customOptions = [], $elementName = null)
    {
        $config = Zend_Registry::get('config');
        $userId = Zend_Auth::getInstance()->getIdentity()['id'];
        $path = realpath($config->forms->upload->tempPath) . '/' . $userId;
        ZFE_File::makePath($path);

        $modelName = $this->_modelName;

        $configOptions = [
            'label' => $modelName::getFieldName($id, 'Загрузить файл'),
            'destination' => $path,
        ];
        $options = array_replace_recursive($configOptions, $customOptions);

        return $this->addElement('file', $elementName ?: $id, $options);
    }

    /**
     * Добавить стандартное поле для загрузки одного или более файлов.
     *
     * @param string      $id
     * @param array       $customOptions
     * @param null|string $elementName
     *
     * @return Zend_Form
     */
    public function addMultiUploadElement($id, array $customOptions = [], $elementName = null)
    {
        $modelName = $this->_modelName;

        $configOptions = [
            'label' => $modelName::getFieldName($id, 'Загрузить файлы'),
            'multiple' => 'multiple',
            'isArray' => true,
        ];
        $options = array_replace_recursive($configOptions, $customOptions);

        return $this->addUploadElement($id, $options, $elementName);
    }

    /**
     * Добавить стандартное поле для загрузки одного изображения.
     *
     * @param string      $id
     * @param array       $customOptions
     * @param null|string $elementName
     *
     * @return Zend_Form
     */
    public function addPictureUploadElement($id, array $customOptions = [], $elementName = null)
    {
        $config = Zend_Registry::get('config');
        $userId = Zend_Auth::getInstance()->getIdentity()['id'];
        $path = realpath($config->forms->upload->tempPath) . '/' . $userId;
        ZFE_File::makePath($path);

        $modelName = $this->_modelName;

        $configOptions = [
            'label' => $modelName::getFieldName($id, 'Загрузить изображение'),
            'destination' => $path,
        ];
        $options = array_replace_recursive($configOptions, $customOptions);

        return $this->addElement('fileImage', $elementName ?: $id, $options);
    }

    /**
     * Добавить стандартное поле для загрузки одного или более изображений.
     *
     * @param string      $id
     * @param array       $customOptions
     * @param null|string $elementName
     *
     * @return Zend_Form
     */
    public function addMultiPictureUploadElement($id, array $customOptions = [], $elementName = null)
    {
        $modelName = $this->_modelName;

        $configOptions = [
            'label' => $modelName::getFieldName($id, 'Загрузить изображения'),
            'multiple' => 'multiple',
            'isArray' => true,
        ];
        $options = array_replace_recursive($configOptions, $customOptions);

        return $this->addPictureUploadElement($id, $options, $elementName);
    }

    /**
     * Добавить стандартное поле для загрузки одного аудио файла.
     *
     * @param string      $id
     * @param array       $customOptions
     * @param null|string $elementName
     *
     * @return Zend_Form
     */
    public function addAudioUploadElement($id, array $customOptions = [], $elementName = null)
    {
        $config = Zend_Registry::get('config');
        $userId = Zend_Auth::getInstance()->getIdentity()['id'];
        $path = realpath($config->forms->upload->tempPath) . '/' . $userId;
        ZFE_File::makePath($path);

        $modelName = $this->_modelName;

        $configOptions = [
            'label' => $modelName::getFieldName($id, 'Загрузить аудио файл'),
            'destination' => $path,
        ];
        $options = array_replace_recursive($configOptions, $customOptions);

        return $this->addElement('fileAudio', $elementName ?: $id, $options);
    }

    /**
     * Добавить стандартное поле для загрузки одного либо нескольких аудио файлов.
     *
     * @param string      $id
     * @param array       $customOptions
     * @param null|string $elementName
     *
     * @return Zend_Form
     */
    public function addMultiAudioUploadElement($id, array $customOptions = [], $elementName = null)
    {
        $modelName = $this->_modelName;

        $configOptions = [
            'label' => $modelName::getFieldName($id, 'Загрузить аудио файлы'),
            'multiple' => 'multiple',
            'isArray' => true,
        ];
        $options = array_replace_recursive($configOptions, $customOptions);

        return $this->addAudioUploadElement($id, $options, $elementName);
    }

    /**
     * Добавить стандартное поле выбора одного из многих (типа select).
     *
     * @param string      $id
     * @param array       $customOptions
     * @param null|string $elementName
     *
     * @return Zend_Form
     */
    public function addSelectElement($id, array $customOptions = [], $elementName = null)
    {
        $table = Doctrine_Core::getTable($this->_modelName);
        $columnOptions = $table->getElementOptionsForColumn($id);
        $options = array_replace_recursive($columnOptions, $customOptions);

        if (key_exists('multiOptions', $customOptions)) {
            $options['multiOptions'] = $customOptions['multiOptions'];
        }

        if (key_exists('emptyValueLabel', $options)) {
            $emptyValue = $options['emptyValue'] ?? null;
            $emptyLabel = $options['emptyValueLabel'];
            $options['multiOptions'] = [$emptyValue => $emptyLabel] + $columnOptions['multiOptions'];
            unset($options['emptyValueLabel'], $options['emptyValue']);
        }

        return $this->addElement('select', $elementName ?: $id, $options);
    }

    /**
     * Добавить стандартное поле выбора одного из многих (типа radio).
     *
     * @param string      $id
     * @param array       $customOptions
     * @param null|string $elementName
     *
     * @return Zend_Form
     */
    public function addRadioElement($id, array $customOptions = [], $elementName = null)
    {
        $table = Doctrine_Core::getTable($this->_modelName);
        $columnOptions = $table->getElementOptionsForColumn($id);
        $options = array_replace_recursive($columnOptions, $customOptions);

        return $this->addElement('radio', $elementName ?: $id, $options);
    }

    /**
     * Добавить стандартное поле checkbox.
     *
     * @param string      $id
     * @param array       $customOptions
     * @param null|string $elementName
     *
     * @return Zend_Form
     */
    public function addCheckboxElement($id, array $customOptions = [], $elementName = null)
    {
        $modelName = $this->_modelName;
        $localOptions = [
            'label' => $modelName::getFieldName($id),
        ];
        $options = array_replace_recursive($localOptions, $customOptions);

        return $this->addElement('checkbox', $elementName ?: $id, $options);
    }

    /**
     * Добавить стандартное поле автокомплита одного значения.
     *
     * @param string      $id
     * @param array       $customOptions
     * @param null|string $elementName
     *
     * @return Zend_Form
     */
    public function addAutocompleteElement($id, array $customOptions = [], $elementName = null)
    {
        $modelName = $this->_modelName;
        $acOptions = $modelName::getAutocompleteOptions($id);
        $options = array_replace_recursive($acOptions, $customOptions);

        return $this->addElement('autocomplete', $elementName ?: $id, $options);
    }

    /**
     * Добавить стандартное поле автокомплита нескольких значений.
     *
     * @param string      $id
     * @param array       $customOptions
     * @param null|string $elementName
     *
     * @return Zend_Form
     */
    public function addMultiAutocompleteElement($id, array $customOptions = [], $elementName = null)
    {
        $modelName = $this->_modelName;
        $acOptions = $modelName::getMultiAutocompleteOptions($id);
        $options = array_replace_recursive($acOptions, $customOptions);

        return $this->addElement('multiAutocomplete', $elementName ?: $id, $options);
    }

    /**
     * Добавить стандартное статическое поле.
     *
     * @param string      $id
     * @param array       $customOptions
     * @param null|string $elementName
     *
     * @return Zend_Form
     */
    public function addStaticElement($id, array $customOptions = [], $elementName = null)
    {
        $table = Doctrine_Core::getTable($this->_modelName);
        $columnOptions = $table->getElementOptionsForColumn($id);
        $options = array_replace_recursive($columnOptions, $customOptions);

        return $this->addElement('static', $elementName ?: $id, $options);
    }

    /**
     * Добавить стандартное поле автокомплита нескольких значений.
     *
     * @param string      $id
     * @param array       $customOptions
     * @param null|string $elementName
     *
     * @return Zend_Form
     */
    public function addMultiCheckboxElement($id, array $customOptions = [], $elementName = null)
    {
        $modelName = $this->_modelName;
        $mcsOptions = $modelName::getMultiCheckOrSelectOptions($id);
        $options = array_replace_recursive($mcsOptions, $customOptions);

        return $this->addElement('multiCheckbox', $elementName ?: $id, $options);
    }

    /**
     * Добавить скрытое поле.
     *
     * @param string      $id
     * @param array       $customOptions
     * @param null|string $elementName
     *
     * @return Zend_Form
     */
    public function addHiddenElement($id, array $customOptions = [], $elementName = null)
    {
        return $this->addElement('hidden', $elementName ?: $id, $customOptions);
    }

    /**
     * Добавить clearfix.
     *
     * @return Zend_Form
     */
    public function addClearfix()
    {
        return $this->addElement('clearfix', uniqid('clearfix_'));
    }
}
