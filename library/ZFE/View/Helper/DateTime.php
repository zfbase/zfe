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
     * Форматировать дату (и время).
     *
     * @param string $dateTime
     * @param bool   $time
     *
     * @return string
     */
    public function dateTime($dateTime, $time = true)
    {
        return ZFE_Utilities::formatDateTime($dateTime, $time);
    }
}
