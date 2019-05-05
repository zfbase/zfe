<?php

/*
 * ZFE – платформа для построения редакторских интерфейсов.
 */

/**
 * Обертка над number_format.
 */
class ZFE_View_Helper_Number extends Zend_View_Helper_Abstract
{
    /**
     * Отформатировать запрос
     *
     * @param float  $number
     * @param int    $decimals
     * @param string $dec_point
     * @param string $thousands_sep
     *
     * @return string
     */
    public function number($number, $decimals = 0, $dec_point = '.', $thousands_sep = '&nbsp;')
    {
        return number_format($number, $decimals, $dec_point, $thousands_sep);
    }
}
