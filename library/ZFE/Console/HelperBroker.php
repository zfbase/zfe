<?php

/*
 * ZFE – платформа для построения редакторских интерфейсов.
 */

/**
 * Брокер помощников представления консоли.
 *
 * @todo Реализовать :).
 */
class ZFE_Console_HelperBroker
{
    public function get(string $name)
    {
        switch ($name) {
            case 'Table': return new ZFE_Console_Helper_Table();
            case 'ProgressBar': return new ZFE_Console_Helper_ProgressBar();
            default: new ZFE_Console_Exception('Помощник консоли не найден.');
        }
    }
}
