<?php

/*
 * ZFE – платформа для построения редакторских интерфейсов.
 */

/**
 * Автоматически генерируемая форма редактирования записи по модели.
 *
 * Зависимости
 * # ZFE_Form_Helpers_Generator
 */
class ZFE_Form_Edit_AutoGeneration extends ZFE_Form_Horizontal
{
    /**
     * Магические названия полей модели со связями с конкретными шаблонами элементов форм
     *
     * @var array
     */
    protected $_defaultFieldMethods = [
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
        'phone' =>    'addTelElement',
    ];

    /**
     * Массив соответствий полей и шаблонов элементов форм
     * Позволяет указать типы полей с сохранением автоматической сборки.
     *
     * @var array
     */
    protected $_fieldMethods = [];

    /**
     * Пропускаемые поля.
     *
     * @var array
     */
    protected $_ignoreFields = [];

    /**
     * Инициализировать форму.
     *
     * @throws ZFE_Form_Exception
     */
    public function init()
    {
        $this->addElementId();

        $modelName = $this->_modelName;
        if (empty($modelName)) {
            throw new ZFE_Form_Exception('Автогенератор вызван без указания модели');
        }

        $ignoreFields = array_merge($modelName::getServiceFields(), $this->_ignoreFields);
        $fieldMethods = array_merge($this->_defaultFieldMethods, $this->_fieldMethods);

        $table = Doctrine_Core::getTable($modelName);
        foreach ($table->getColumnNames() as $columnName) {
            if ( ! in_array($columnName, $ignoreFields, true) && ! $this->getElement($columnName)) {
                $method = $fieldMethods[$columnName] ?? 'addElementForColumn';
                $this->{$method}($columnName);
            }
        }

        if ($table->hasColumn('status') && ! in_array('status', $ignoreFields, true)) {
            $this->addElementStatus();
        }
    }

    /**
     * Установить переданные значения.
     *
     * @param array $options
     *
     * @return Zend_Form
     */
    public function setOptions(array $options)
    {
        if (isset($options['modelName'])) {
            $this->_modelName = $options['modelName'];
            unset($options['modelName']);
        }

        return parent::setOptions($options);
    }
}
