<?php

/*
 * ZFE – платформа для построения редакторских интерфейсов.
 */

/**
 * Средства организации основного поиска по модели по средствам Doctrine.
 */
class ZFE_Searcher_Doctrine extends ZFE_Searcher_Abstract
{
    /**
     * Конструктор запросов.
     *
     * @var ZFE_Searcher_QueryBuilder_Interface
     */
    protected $_queryBuilder;

    /**
     * {@inheritdoc}
     */
    protected $_paginator = 'ZFE_Paginator';

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
        if (!$this->_queryBuilder) {
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
        $this->filterIdsParam($params);

        $query = $this->getQueryBuilder()->getQuery($params);

        $revertHash = $params['rh'] ?? null;
        $resultNumber = $params['rn'] ?? null;
        $targetAction = $params['ta'] ?? null;
        if (!empty($resultNumber) && !empty($revertHash)) {
            $query->offset($resultNumber - 1);
            $query->limit(1);
            $baseUrl = $this->getBaseUrl($query->fetchOne(), $targetAction);
            $redirector = Zend_Controller_Action_HelperBroker::getStaticHelper('redirector');
            $redirector->setGotoUrl($baseUrl . '?h=' . $revertHash . '&rn=' . $resultNumber);
        }

        $paginator = $this->getPaginator();
        return $paginator ? $paginator::execute($query) : $query->execute();
    }

    /**
     * {@inheritdoc}
     */
    public function countUsedFilters()
    {
        return $this->getQueryBuilder()->countUsedFilters();
    }
}
