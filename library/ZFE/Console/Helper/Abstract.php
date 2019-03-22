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
     *
     * @param bool $echo
     *
     * @return string
     */
    abstract public function render(bool $echo = true);

    public function __toString()
    {
        return $this->render(false);
    }
}
