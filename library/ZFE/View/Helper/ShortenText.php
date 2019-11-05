<?php

/*
 * ZFE – платформа для построения редакторских интерфейсов.
 */

/**
 * Укоротитель текста до определенного размера.
 */
class ZFE_View_Helper_ShortenText extends Zend_View_Helper_Abstract
{
    /**
     * Укоротить текст до определенного размера.
     *
     * @param string $text    исходный текст
     * @param string $max_len максимальная длина
     *
     * @return string сокращенный текст
     */
    public function shortenText($text, $max_len = 100)
    {
        return ZFE_Utilities::shortenText($text, $max_len);
    }
}
