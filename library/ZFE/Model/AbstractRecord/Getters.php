<?php

/*
 * ZFE – платформа для построения редакторских интерфейсов.
 */

/**
 * Геттеры данных модели и записи.
 *
 * @property array    $_dictionaryFields
 * @property string[] $_nameBaseFields
 * @property string[] $_newTitle
 * @property array    $autocompleteCols
 * @property string   $controller
 * @property array    $multiAutocompleteCols
 * @property string[] $nameFields
 * @property int      $sex
 * @property string[] $statusColor
 */
trait ZFE_Model_AbstractRecord_Getters
{
    /**
     * Получить название записи.
     *
     * @return string
     */
    public function getTitle()
    {
        if ($this->exists()) {
            return empty($this->title) ? 'Без названия' : $this->title;
        }

        return static::getNewTitle();
    }

    /**
     * Установить название записи.
     *
     * Да, в геттерах, но зато рядом с getTitle()
     *
     * @param string $title
     *
     * @return static
     */
    public function setTitle($title)
    {
        $this->title = $title;
        return $this;
    }

    /**
     * Получить название новой записи.
     *
     * @return string
     */
    public static function getNewTitle()
    {
        return static::$_newTitle[static::$sex];
    }

    /**
     * Получить имя контроллера управляющего объектами класса.
     *
     * @return string
     */
    public static function getControllerName()
    {
        return empty(static::$controller)
            ? mb_strtolower(implode('-', ZFE_Utilities::splitCamelCase(static::class)))
            : static::$controller;
    }

    /**
     * Получить имя таблицы.
     *
     * @return string
     */
    public function getTableName()
    {
        $t = $this->_table->getOption('tableName');
        $t = explode('.', $t);
        return end($t);
    }

    /**
     * Получить имя модели по имени таблицы.
     *
     * @param string $table_name
     *
     * @return string
     */
    public static function getModelNameByTableName($table_name)
    {
        return str_replace(' ', '', ucwords(str_replace('_', ' ', $table_name)));
    }

    /**
     * Получить статус записи.
     *
     * @param bool $color выделять цветом?
     *
     * @return string
     */
    public function getStatus($color = true)
    {
        $status = static::getDictionaryField('status', $this->status);
        return $color
            ? '<span style="color:' . static::$statusColor[$this->status] . '">' . $status . '</span>'
            : $status;
    }

    /**
     * Получить название поля по имени параметра.
     *
     * @param string $field
     * @param string $default
     *
     * @return string
     */
    public static function getFieldName($field, $default = null)
    {
        if (!empty(static::$nameFields[$field])) {
            return static::$nameFields[$field];
        }

        $table = Doctrine_Core::getTable(static::class);
        $definition = $table->getColumnDefinition($field);
        if (!empty($definition['comment'])) {
            return $definition['comment'];
        }

        if (!empty(static::$_nameBaseFields[$field])) {
            return static::$_nameBaseFields[$field];
        }

        if (Doctrine_Core::getTable(static::class)->hasRelation($field)) {
            foreach (static::$autocompleteCols as $code => $options) {
                if (isset($options['relAlias']) && $options['relAlias'] == $field) {
                    return static::getAutocompleteOptions($code)['label'];
                }
            }
            foreach (static::$multiAutocompleteCols as $code => $options) {
                if (isset($options['relAlias']) && $options['relAlias'] == $field) {
                    return static::getMultiAutocompleteOptions($code)['label'];
                }
            }
        }

        if (null !== $default) {
            return $default;
        }

        return $field;
    }

    /**
     * Это поле словарное?
     *
     * @param string $field имя проверяемого слова
     *
     * @return bool
     */
    public static function isDictionaryField($field)
    {
        return isset(static::$_dictionaryFields[$field]);
    }

    /**
     * Получить словарь словарного поля.
     *
     * @param string $field
     *
     * @throws ZFE_Model_Exception
     *
     * @return array
     */
    public static function getDictionary($field)
    {
        if (!static::isDictionaryField($field)) {
            throw new ZFE_Model_Exception('Обращение к неопределенному словарю');
        }

        $dictionaryName = static::$_dictionaryFields[$field][0];
        $dictionary = static::${$dictionaryName};
        for ($i = 1, $l = count(static::$_dictionaryFields[$field]); $i < $l; ++$i) {
            $_ = static::$_dictionaryFields[$field][$i];
            $dictionary = $dictionary[static::${$_}];
        }
        return $dictionary;
    }

    /**
     * Получить значение словарного поля.
     *
     * @param string $field имя поля
     * @param string $value имя значения
     *
     * @return string
     */
    public static function getDictionaryField($field, $value)
    {
        $dictionary = static::getDictionary($field);
        return $dictionary[$value] ?? '';
    }
}
