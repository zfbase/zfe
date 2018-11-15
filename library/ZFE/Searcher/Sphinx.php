<?php

/*
 * ZFE – платформа для построения редакторских интерфейсов.
 */

/**
 * Средства организации основного поиска по модели по средствам Sphinx.
 *
 * @todo Добавить функционал переходов по страницам результата
 */
class ZFE_Searcher_Sphinx extends ZFE_Searcher_Abstract
{
    /**
     * Конструктор запросов Sphinx.
     *
     * @var ZFE_Searcher_QueryBuilder_Interface
     */
    protected $_sphinxQueryBuilder;

    /**
     * Конструктор запросов Doctrine.
     *
     * @var ZFE_Searcher_QueryBuilder_Interface
     */
    protected $_doctrineQueryBuilder;

    /**
     * {@inheritdoc}
     */
    protected $_paginator = 'ZFE_Sphinx_Paginator';

    /**
     * {@inheritdoc}
     */
    public function search(array $params = null)
    {
        if (null === $params) {
            $params = $this->getParamsFromRequest();
        }
        $this->filterIdsParam($params);

        $paginator = $this->getPaginator();

        if (empty($params['ids'])) {
            $sphinxQuery = $this->getSphinxQueryBuilder()->getQuery($params);

            if ($paginator) {
                $sphinxResult = $paginator::execute($sphinxQuery);
            } else {
                $sphinxResult = $sphinxQuery->execute();
            }

            $ids = ZFE_Sphinx::fetchIds($sphinxResult);
        } else {
            $ids = $params['ids'];
            $paginator::execute(null, [], count($ids));
        }

        if ($ids) {
            $doctrineQuery = $this->getDoctrineQueryBuilder()->getQuery(['ids' => $ids]);
            $doctrineQuery->orderByField('x.id', $ids);
            $doctrineQuery->setHard(true);
            return $doctrineQuery->execute();
        }

        return [];
    }

    /**
     * Установить конструктор запросов.
     *
     * @param ZFE_Searcher_QueryBuilder_Interface $builder
     *
     * @return ZFE_Searcher_Sphinx
     */
    public function setSphinxQueryBuilder(ZFE_Searcher_QueryBuilder_Interface $builder)
    {
        $this->_sphinxQueryBuilder = $builder;
        return $this;
    }

    /**
     * Получить экземпляр конструктора запросов.
     *
     * @return ZFE_Searcher_QueryBuilder_Interface
     */
    public function getSphinxQueryBuilder()
    {
        if ( ! $this->_sphinxQueryBuilder) {
            $this->_sphinxQueryBuilder = new ZFE_Searcher_QueryBuilder_Sphinx($this->_modelName);
        }

        return $this->_sphinxQueryBuilder;
    }

    /**
     * Установить конструктор запросов.
     *
     * @param ZFE_Searcher_QueryBuilder_Interface $builder
     *
     * @return ZFE_Searcher_Sphinx
     */
    public function setDoctrineQueryBuilder(ZFE_Searcher_QueryBuilder_Interface $builder)
    {
        $this->_doctrineQueryBuilder = $builder;
        return $this;
    }

    /**
     * Получить экземпляр конструктора запросов.
     *
     * @return ZFE_Searcher_QueryBuilder_Interface
     */
    public function getDoctrineQueryBuilder()
    {
        if ( ! $this->_doctrineQueryBuilder) {
            $this->_doctrineQueryBuilder = new ZFE_Searcher_QueryBuilder_Doctrine($this->_modelName);
        }

        return $this->_doctrineQueryBuilder;
    }
}
