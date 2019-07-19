<?php

/*
 * ZFE – платформа для построения редакторских интерфейсов.
 */

/**
 * Управление списком полей модели, для которых в истории скрываются значения.
 *
 * Пример переопределения см. в ZFE_Model_Default_Editors
 */
trait ZFE_Model_AbstractRecord_HistoryHiddenFields
{
    /**
     * Список полей модели, для которых в истории скрываются значения.
     *
     * @var null|array
     */
    protected static $_historyHiddenFields = null;

    /**
     * Очистить список скрываемых в истории поля.
     */
    protected static function _clearHistoryHiddenFields()
    {
        static::$_historyHiddenFields = [];
    }

    /**
     * Добавить поля в список полей модели, для которых в истории скрываются значения.
     *
     * @param array $fields
     */
    protected static function _addHistoryHiddenFields(array $fields)
    {
        if (!is_array(static::$_historyHiddenFields)) {
            static::_clearHistoryHiddenFields();
        }

        foreach ($fields as $field) {
            static::$_historyHiddenFields[$field] = $field;
        }
    }

    /**
     * Заменить список полей модели, для которых в истории скрываются значения.
     *
     * @param array $fields
     */
    protected static function _setHistoryHiddenFields(array $fields)
    {
        static::_clearHistoryHiddenFields();
        static::_addHistoryHiddenFields($fields);
    }

    /**
     * Удалить поле из списка полей модели, для которых в истории скрываются значения.
     *
     * @param array $fields
     */
    protected static function _removeHistoryHiddenFields(array $fields)
    {
        foreach ($fields as $field) {
            unset(static::$_historyHiddenFields[$field]);
        }
    }

    /**
     * Инициализировать список по умолчанию полей модели, для которых в истории скрываются значения.
     */
    protected static function _initHistoryHiddenFields()
    {
        static::_setHistoryHiddenFields([
        ]);
    }

    /**
     * Получить список полей модели, для которых в истории скрываются значения.
     *
     * @return array
     */
    public static function getHistoryHiddenFields()
    {
        if (null === static::$_historyHiddenFields) {
            static::_initHistoryHiddenFields();
        }

        return static::$_historyHiddenFields;
    }
}
