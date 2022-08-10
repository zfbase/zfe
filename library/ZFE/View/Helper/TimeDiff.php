<?php

/*
 * ZFE – платформа для построения редакторских интерфейсов.
 */

/**
 * Получить разницу между двумя датами со временем.
 *
 * @property ZFE_View $view
 */
class ZFE_View_Helper_TimeDiff extends Zend_View_Helper_Abstract
{
    /**
     * Получить форматированный промежуток времени, прошедший с $base до $time.
     *
     * @param ?string $base
     * @param ?string $time
     */
    public function timeDiff($base, $time)
    {
        if (!$base || !$time) {
            return '';
        }

        return $this->view->duration(strtotime($time) - strtotime($base));
    }
}
