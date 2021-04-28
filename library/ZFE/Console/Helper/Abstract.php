<?php

/*
 * ZFE – платформа для построения редакторских интерфейсов.
 */

/**
 * Абстрактная помощник.
 */
abstract class ZFE_Console_Helper_Abstract
{
    /**
     * Рендерить.
     */
    abstract public function render(bool $echo = true): string;

    public function __toString()
    {
        return $this->render(false);
    }
}
