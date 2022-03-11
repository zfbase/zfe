<?php

/*
 * ZFE – платформа для построения редакторских интерфейсов.
 */

/**
 * Расширенные возможности форм.
 */
trait ZFE_Form_Extensions
{
    /**
     * Заполнить элементы загрузки файла формы загруженными файлами.
     *
     * @param ZFE_Model_AbstractRecord $item
     */
    public function populateFiles(ZFE_Model_AbstractRecord $item)
    {
        foreach ($this->getElements() as $name => $element) {
            if ($element instanceof ZFE_Form_Element_File) {
                if ($file = $item->getFileColumn($name)) {
                    $element->setFile($file);
                } elseif ($files = $item->getFilesColumn($name)) {
                    foreach ($files as $file) {
                        $element->addFile($file);
                    }
                }
            }
        }
    }

    /**
     * Получить стандартные декораторы для типа элементов.
     *
     * @param string $type
     *
     * @return array
     */
    public function getDefaultDecoratorsByElementType($type)
    {
        if (in_array($type, ['range', 'duration'])) {
            if (is_array($this->_simpleElementDecorators)) {
                return $this->_simpleElementDecorators;
            }
        }

        switch ($type) {
            case 'fileImage':
            case 'fileAudio':
                $simpleType = 'file';

                break;
            default:
                $simpleType = $type;
        }
        $decorators = parent::getDefaultDecoratorsByElementType($simpleType);

        switch ($type) {
            case 'file':
                $decorators = array_merge(['File', 'FileValue'], $decorators);

                break;
            case 'fileImage':
                $decorators = array_merge(['File', 'FileImageValue'], $decorators);

                break;
            case 'fileAudio':
                $decorators = array_merge(['File', 'FileAudioValue'], $decorators);

                break;
            case 'clearfix':
                $decorators = ['ViewHelper'];

                break;
        }

        if (empty($decorators)) {
            $decorators = is_array($this->_simpleElementDecorators)
                ? $this->_simpleElementDecorators
                : [];
        }

        // Добавляем декоратор, который решит проблему с autocomplete="off" там, где это потребуется
        $decorators[] = 'AutocompleteOff';

        return $decorators;
    }

    /**
     * Создать элемент
     *
     * @see Zend_Form::createElement()
     *
     * @param string            $type
     * @param string            $name
     * @param array|Zend_Config $options
     *
     * @throws Zend_Form_Exception
     *
     * @return Zend_Form_Element
     */
    public function createElement($type, $name, $options = null)
    {
        if (in_array($type, ['range', 'duration'])) {
            if (null === $options) {
                $options = ['class' => 'form-control'];
            } elseif (key_exists('class', $options)) {
                if (!mb_strstr($options['class'], 'form-control')) {
                    $options['class'] .= ' form-control';
                    $options['class'] = trim($options['class']);
                }
            } else {
                $options['class'] = 'form-control';
            }
        }

        // Если форма disabled, то и элементы принудительно тоже
        if ($this->_disabled) {
            $options['disabled'] = $this->_disabled;
            $options['disable'] = $this->_disabled;
        }

        return parent::createElement($type, $name, $options);
    }

    /**
     * Сделать все disabled элементы игнорируемыми.
     */
    public function setDisabledToIgnore()
    {
        foreach ($this->getElements() as $key => $element) {
            if ($this->{$key}->disabled || $this->{$key}->readonly) {
                $this->{$key}->setIgnore(true);
            }
        }
        foreach ($this->getSubForms() as $key => $subForm) {
            $this->{$key}->setDisabledToIgnore();
        }
    }

    /**
     * Общее состояние disabled всех элементов формы.
     *
     * @var bool
     */
    protected $_disabled = false;

    /**
     * Установить свойство disabled на всю форму.
     *
     * @param bool $disabled
     */
    public function setDisabled($disabled)
    {
        $this->_disabled = (bool) $disabled;

        // Расставляем свойство всем уже добавленным элементам и суб-формам
        foreach ($this->getElements() as $key => $element) {
            $this->{$key}->disabled = $this->_disabled;
            $this->{$key}->disable = $this->_disabled;
        }
        foreach ($this->getSubForms() as $key => $subForm) {
            $this->{$key}->setDisabled($this->_disabled);
        }
    }
}
