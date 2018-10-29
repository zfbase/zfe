<?php

/*
 * ZFE – платформа для построения редакторских интерфейсов.
 */

/**
 * Форматрировать период.
 *
 * @category  ZFE
 */
class ZFE_View_Helper_Period extends Zend_View_Helper_Abstract
{
    /**
     * Форматрировать период.
     *
     * @param DateTime|string $start
     * @param DateTime|string $end
     * @param bool            $showTime
     *
     * @return string
     */
    public function period($start, $end, $showTime = false)
    {
        if ($start instanceof DateTime) {
            $startDate = $start;
        } elseif (is_string($start)) {
            $startDate = new DateTime($start);
        } else {
            $startDate = null;
        }

        if ($end instanceof DateTime) {
            $endDate = $end;
        } elseif (is_string($end)) {
            $endDate = new DateTime($end);
        } else {
            $endDate = null;
        }

        $config = Zend_Registry::get('config')->format;
        $format = ($showTime) ? $config->datetime : $config->date;

        $result = '';
        if ($start) {
            $result .= $startDate->format($format);
        }
        if ($start && $end) {
            $result .= ' – ';
        }
        if ($end) {
            $result .= $endDate->format($format);
        }
        return $result;
    }
}
