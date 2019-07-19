<?php

/*
 * ZFE – платформа для построения редакторских интерфейсов.
 */

/**
 * Средства организации основного поиска по модели по средствам Sphinx.
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

            $revertHash = $params['rh'] ?? null;
            $resultNumber = $params['rn'] ?? null;
            if (!empty($resultNumber) && !empty($revertHash)) {
                $sphinxQuery->option('max_matches', $resultNumber + 1);
                $sphinxQuery->offset($resultNumber - 1);
                $sphinxQuery->limit(1);
                $item = ZFE_Sphinx::fetchOne($sphinxQuery, $this->_modelName);

                $redirector = Zend_Controller_Action_HelperBroker::getStaticHelper('redirector');
                $redirector->setGotoUrl($item->getUrl() . '?h=' . $revertHash . '&rn=' . $resultNumber);
            }

            if ($paginator) {
                $sphinxResult = $paginator::execute($sphinxQuery);
            } else {
                $count = (int) (clone $sphinxQuery)
                    ->setSelect('COUNT(*)')
                    ->execute()
                    ->fetchNum()[0];
                $sphinxResult = $sphinxQuery
                    ->limit($count)
                    ->option('max_matches', $count)
                    ->execute()
                ;
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
        if (!$this->_sphinxQueryBuilder) {
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
        if (!$this->_doctrineQueryBuilder) {
            $this->_doctrineQueryBuilder = new ZFE_Searcher_QueryBuilder_Doctrine($this->_modelName);
        }

        return $this->_doctrineQueryBuilder;
    }

    /**
     * {@inheritdoc}
     */
    public function countUsedFilters()
    {
        return $this->getSphinxQueryBuilder()->countUsedFilters();
    }
}
