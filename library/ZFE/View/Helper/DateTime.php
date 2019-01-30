<?php

/*
 * ZFE – платформа для построения редакторских интерфейсов.
 */

/**
 * Форматирование даты и времени.
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
     * @param string $dateTime
     * @param bool   $time
     *
     * @return string
     */
    public function dateTime($dateTime, $time = true)
    {
        if (in_array($dateTime, ['0000-00-00', '0000-00-00 00:00:00'], true)) {
            return '';
        }

        $timestamp = strtotime($dateTime);
        if ( ! $timestamp) {
            return '';
        }

        if (null === self::$_format) {
            self::$_format['datetime'] = Zend_Registry::get('config')->format->datetime;
            self::$_format['date'] = Zend_Registry::get('config')->format->date;
        }

        return date(self::$_format[$time ? 'datetime' : 'date'], $timestamp);
    }
}
