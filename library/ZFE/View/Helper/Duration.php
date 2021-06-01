<?php

/*
 * ZFE – платформа для построения редакторских интерфейсов.
 */

/**
 * Форматировать продолжительность.
 */
class ZFE_View_Helper_Duration extends Zend_View_Helper_Abstract
{
    public function duration(int $seconds, bool $short = true)
    {
        return $short
            ? ZFE_Utilities::formatShortDuration($seconds)
            : ZFE_Utilities::formatDuration($seconds);
    }
}
