<?php

/*
 * ZFE – платформа для построения редакторских интерфейсов.
 */

/**
 * Помощник преобразования байт в более подходящую размерность.
 */
class ZFE_View_Helper_HumanFileSize extends Zend_View_Helper_Abstract
{
    public function humanFileSize($bytes, $precision = 2)
    {
        return ZFE_File::humanFileSize($bytes, $precision);
    }
}
