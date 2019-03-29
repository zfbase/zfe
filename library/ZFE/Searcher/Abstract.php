<?php

/*
 * ZFE – платформа для построения редакторских интерфейсов.
 */

/**
 * Средства организации основного поиска по модели.
 */
abstract class ZFE_Searcher_Abstract implements ZFE_Searcher_Interface
{
    /**
     * Наименование модели.
     *
     * @var string
     */
    protected $_modelName;

    /**
     * Пагинатор.
     *
     * @var null|string
     */
    protected $_paginator;

    /**
     * Установить имя пагинатора.
     *
     * @param null|string $paginator
     *
     * @return ZFE_Searcher_Abstract
     */
    public function setPaginator($paginator)
    {
        if (is_string($paginator) && class_exists($paginator) && $paginator instanceof ZFE_Paginator) {
            $this->_paginator = $paginator;
        } elseif (null === $paginator) {
            $this->_paginator = null;
        } else {
            throw new ZFE_Searcher_Exception('Некорректный пагинатор: допускается имя класса или NULL.');
        }

        return $this;
    }

    /**
     * Получить имя пагинатора.
     *
     * @return null|string
     */
    public function getPaginator()
    {
        return $this->_paginator;
    }

    /**
     * @param string $modelName наименование модели
     */
    public function __construct($modelName)
    {
        $this->_modelName = $modelName;
    }

    /**
     * Получить параметры из HTTP-запроса.
     *
     * @return array
     */
    public function getParamsFromRequest()
    {
        $front = Zend_Controller_Front::getInstance();
        $request = $front->getRequest();
        return $request->getParams();
    }

    /**
     * Фильтровать параметр ids.
     *
     * @param array $params
     */
    public function filterIdsParam(& $params)
    {
        if (empty($params['ids'])) {
            return $params;
        }

        if (is_string($params['ids'])) {
            $ids = explode(',', $params['ids']);
            $ids = array_map('trim', $ids);
            $ids = array_map('intval', $ids);
        } elseif (is_array($params['ids'])) {
            $ids = array_map('intval', $params['ids']);
        } elseif (is_int($params['ids'])) {
            $ids = intval($params['ids']);
        }

        $ids = array_unique($ids);
        $ids = array_diff($ids, [0]);
        if ($ids) {
            $params['ids'] = $ids;
        } else {
            unset($params['ids']);
        }
    }

    /**
     * Вернуть число примененных фильтров.
     *
     * @return int
     */
    abstract public function countUsedFilters();
}
