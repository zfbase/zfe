<?php

/*
 * ZFE – платформа для построения редакторских интерфейсов.
 */

/**
 * Интерфейс конструктора запросов.
 */
interface ZFE_Searcher_QueryBuilder_Interface
{
    /**
     * Задать фильтры.
     *
     * @param array $params
     */
    public function setParams(array $params);

    /**
     * Получить собранный запрос.
     *
     * @param array $params
     *
     * @return mixed
     */
    public function getQuery(array $params = null);
}
