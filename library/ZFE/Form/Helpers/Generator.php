<?php

/*
 * ZFE – платформа для построения редакторских интерфейсов.
 */

/**
 * Генератор элементов форм по модели.
 *
 * Зависимости:
 * # ZFE_Form_Helpers
 */
trait ZFE_Form_Helpers_Generator
{
    protected $_namesMethodsMap = [
        'body' =>     'addWysiwygElement',
        'password' => 'addPasswordElement',
        'datetime' => 'addDateTimeElement',
        'date' =>     'addDateElement',
        'time' =>     'addTimeElement',
        'week' =>     'addWeekElement',
        'month' =>    'addMonthElement',
        'color' =>    'addColorElement',
        'email' =>    'addEmailElement',
        'url' =>      'addUrlElement',
        'tel' =>      'addTelElement',
    ];

    protected $_typesMethodsMap = [
        'autocomplete' =>  'addAutocompleteElement',
        'select' =>        'addSelectElement',
        'number' =>        'addNumberElement',
        'text' =>          'addTextElement',
        'textarea' =>      'addTextareaElement',
        'dateTimeLocal' => 'addDateTimeElement',
        'time' =>          'addTimeElement',
        'date' =>          'addDateElement',
    ];

    /**
     * Добавить элемент, соответствующий полю основной модели формы.
     *
     * @param string $columnName    название поля модели
     * @param array  $customOptions специальные параметры элемента
     * @param string $elementName   название элемента формы (если отличается от названия поля)
     *
     * @return Zend_Form
     */
    public function addElementForColumn($columnName, array $customOptions = [], $elementName = null)
    {
        /** @var ZFE_Model_Table $table */
        $table = Doctrine_Core::getTable($this->_modelName);
        $elementType = $table->getElementTypeForColumn($columnName);

        if (isset($this->_namesMethodsMap[$columnName])) {
            $method = $this->_namesMethodsMap[$columnName];
            $this->{$method}($columnName, $customOptions, $elementName);
        } elseif (isset($this->_typesMethodsMap[$elementType])) {
            $method = $this->_typesMethodsMap[$elementType];
            $this->{$method}($columnName, $customOptions, $elementName);
        } else {
            return $this->addElement(
                $table->getElementTypeForColumn($columnName),
                $elementName ?: $columnName,
                array_replace_recursive(
                    $table->getElementOptionsForColumn($columnName),
                    $customOptions
                )
            );
        }
    }
}
