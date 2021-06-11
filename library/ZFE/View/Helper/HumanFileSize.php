<?php

/*
 * ZFE – платформа для построения редакторских интерфейсов.
 */

/**
 * Помощник преобразования байт в более подходящую размерность.
 */
class ZFE_View_Helper_HumanFileSize extends Zend_View_Helper_Abstract
{
    /**
     * Привести чисто байт к удобно читаемому размеру (в КБ/МБ/...).
     *
     * @param integer $bytes
     * @param integer $precision
     *
     * @return string
     */
    public function humanFileSize($bytes, $precision = 2)
    {
        return ZFE_File::humanFileSize($bytes, $precision);
    }
}
