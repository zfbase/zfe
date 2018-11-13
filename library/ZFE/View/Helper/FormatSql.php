<?php

/*
 * ZFE – платформа для построения редакторских интерфейсов.
 */

/**
 * Форматировщик текстов SQL- запросов.
 */
class ZFE_View_Helper_FormatSql extends Zend_View_Helper_Abstract
{
    /**
     * Отформатировать запрос
     *
     * @param string $query
     *
     * @return string
     */
    public function formatSql($query)
    {
        return SqlFormatter::format($query);
    }
}
