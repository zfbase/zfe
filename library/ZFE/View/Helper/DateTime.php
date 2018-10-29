<?php

/*
 * ZFE – платформа для построения редакторских интерфейсов.
 */

/**
 * Форматирование даты и времени.
 *
 * @category  ZFE
 */
class ZFE_View_Helper_DateTime extends Zend_View_Helper_Abstract
{
    /**
     * Форматы.
     *
     * @var array[string]
     */
    protected static $_format;

    /**
     * Форматировать дату (и время).
     *
     * @param string $mixedDateTime
     * @param bool   $time
     * @param mixed  $stringDateTime
     *
     * @return string
     */
    public function dateTime($stringDateTime, $time = true)
    {
        $datetime = strtotime($stringDateTime);
        if ( ! $datetime) {
            return '';
        }

        if (null === self::$_format) {
            self::$_format['datetime'] = Zend_Registry::get('config')->format->datetime;
            self::$_format['date'] = Zend_Registry::get('config')->format->date;
        }

        return date(self::$_format[$time ? 'datetime' : 'date'], $datetime);
    }
}
