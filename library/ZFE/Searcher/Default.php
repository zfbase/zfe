<?php

/*
 * ZFE – платформа для построения редакторских интерфейсов.
 */

/**
 * Средства организации основного поиска по модели.
 *
 * @todo Добавить функционал переходов по страницам результата
 */
class ZFE_Searcher_Default extends ZFE_Searcher_Abstract
{
    /**
     * Конструктор запросов.
     *
     * @var ZFE_Searcher_QueryBuilder_Interface
     */
    protected $_queryBuilder;

    /**
     * Установить конструктор запросов.
     *
     * @param ZFE_Searcher_QueryBuilder_Interface $builder
     *
     * @return self
     */
    public function setQueryBuilder(ZFE_Searcher_QueryBuilder_Interface $builder)
    {
        $this->_queryBuilder = $builder;
        return $this;
    }

    /**
     * Получить экземпляр конструктора запросов.
     *
     * @return ZFE_Searcher_QueryBuilder_Interface
     */
    public function getQueryBuilder()
    {
        if ( ! $this->_queryBuilder) {
            $this->_queryBuilder = new ZFE_Searcher_QueryBuilder_Doctrine($this->_modelName);
        }

        return $this->_queryBuilder;
    }

    /**
     * {@inheritdoc}
     */
    public function search(array $params = null)
    {
        if (null === $params) {
            $params = $this->getParamsFromRequest();
        }
        $params = $this->filterIdsParam($params);

        $query = $this->getQueryBuilder()->getQuery($params);
        return ZFE_Paginator::execute($query);
    }
}
