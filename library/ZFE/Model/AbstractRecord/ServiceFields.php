<?php

/*
 * ZFE – платформа для построения редакторских интерфейсов.
 */

/**
 * Управление списком служебных полей модели.
 *
 * Эти поля не надо отображать на форме редактирования, сравнении версий
 * и в других местах, где используется автоматизированная работа с полями.
 *
 * Пример переопределения см. в ZFE_Model_Default_Editors
 */
trait ZFE_Model_AbstractRecord_ServiceFields
{
    /**
     * Очистить список служебных полей модели.
     */
    protected static function _clearServiceFields()
    {
        Doctrine_Core::getTable(get_called_class())->clearServiceFields();
    }

    /**
     * Добавить поля в список служебных полей модели.
     *
     * @param array $fields
     */
    protected static function _addServiceFields(array $fields)
    {
        $table = Doctrine_Core::getTable(get_called_class());

        foreach ($fields as $field) {
            $table->addServiceField($field);
        }
    }

    /**
     * Заменить список служебных полей модели.
     *
     * @param array $fields
     */
    protected static function _setServiceFields(array $fields)
    {
        static::_clearServiceFields();
        static::_addServiceFields($fields);
    }

    /**
     * Удалить поле из списка служебных полей модели.
     *
     * @param array $fields
     */
    protected static function _removeServiceFields(array $fields)
    {
        $table = Doctrine_Core::getTable(get_called_class());

        foreach ($fields as $field) {
            $table->removeServiceField($field);
        }
    }

    /**
     * Инициализировать список по умолчанию служебных полей модели.
     */
    protected static function _initServiceFields()
    {
        static::_setServiceFields([
            'id',
            'deleted',
            'creator_id',
            'editor_id',
            'datetime_created',
            'datetime_edited',
            'version',
        ]);
    }

    /**
     * Получить список служебных полей модели.
     *
     * @return array
     */
    public static function getServiceFields()
    {
        $table = Doctrine_Core::getTable(get_called_class());
        $serviceFields = $table->getServiceFields();
        if (null === $serviceFields) {
            static::_initServiceFields();
            $serviceFields = $table->getServiceFields();
        }
        return $serviceFields;
    }
}
