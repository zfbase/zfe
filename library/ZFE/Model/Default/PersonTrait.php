<?php

/*
 * ZFE – платформа для построения редакторских интерфейсов.
 */

/**
 * Стандартная функциональность для моделей персон
 */
trait ZFE_Model_Default_PersonTrait
{
    /**
     * Получить сокращенное имя пользователя.
     *
     * @return string
     */
    public function getShortName()
    {
        $name = $this->second_name . ' ';

        if ( ! empty($this->first_name)) {
            $name .= mb_substr($this->first_name, 0, 1) . '.';
        }

        if ( ! empty($this->middle_name)) {
            $name .= mb_substr($this->middle_name, 0, 1) . '.';
        }

        return trim($name);
    }

    /**
     * Получить полное имя пользователя.
     *
     * @return string
     */
    public function getFullName()
    {
        $name = $this->second_name;

        if ( ! empty($this->first_name)) {
            $name .= ' ' . $this->first_name;
        }

        if ( ! empty($this->middle_name)) {
            $name .= ' ' . $this->middle_name;
        }

        return $name;
    }

    /**
     * @inheritDoc
     */
    public function getTitle()
    {
        if ($this->exists()) {
            return $this->getFullName();
        }

        return static::getNewTitle();
    }

    /**
     * @inheritDoc
     */
    public static function getKeyValueList($keyField = 'id', $valueField = "CONCAT_WS(' ', second_name, first_name, middle_name)", $where = null, $order = 'KEY_FIELD ASC', $groupby = null, $filterByStatus = null)
    {
        return parent::getKeyValueList($keyField, $valueField, $where, $order, $groupby);
    }
}