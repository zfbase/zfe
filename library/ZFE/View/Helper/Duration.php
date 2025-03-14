<?php

/*
 * ZFE – платформа для построения редакторских интерфейсов.
 */

/**
 * Форматировать продолжительность.
 */
class ZFE_View_Helper_Duration extends Zend_View_Helper_Abstract
{
    public function duration($seconds, bool $short = true)
    {
        if ($seconds === null) {
            return '';
        }
        $seconds = is_float($seconds) ? round($seconds) : intval($seconds);
        return $short
            ? ZFE_Utilities::formatShortDuration($seconds)
            : ZFE_Utilities::formatDuration($seconds);
    }
}
