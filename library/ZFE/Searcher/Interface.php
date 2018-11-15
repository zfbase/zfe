<?php

/*
 * ZFE – платформа для построения редакторских интерфейсов.
 */

/**
 * Интерфейс средств организации основного поиска по модели.
 */
interface ZFE_Searcher_Interface
{
    /**
     * Искать.
     *
     * @param array $params
     *
     * @return array|Doctrine_Collection
     */
    public function search(array $params = null);
}
