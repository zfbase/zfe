<?php

/*
 * ZFE – платформа для построения редакторских интерфейсов.
 */

/**
 * Конструктор запросов для Doctrine.
 *
 * @property ZFE_Query $_query
 */
class ZFE_Searcher_QueryBuilder_Doctrine extends ZFE_Searcher_QueryBuilder_Abstract
{
    /**
     * {@inheritdoc}
     */
    protected function _create()
    {
        $this->_query = ZFE_Query::create()
            ->select('x.*')
            ->from($this->_modelName . ' x')
        ;

        if ($this->_tableInstance->hasRelation('Editor')) {
            $this->_query->addFrom('x.Editor e')->addSelect('e.*');
        }

        if ($this->_tableInstance->hasRelation('Creator')) {
            $this->_query->addFrom('x.Creator c')->addSelect('c.*');
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function _filters()
    {
        $title = trim((string) $this->getParam('title'));
        if (!empty($title)) {
            $this->_query->addWhere('LOWER(' . ($this->_modelName)::$titleField . ') LIKE LOWER(?)', '%' . $title . '%');
        }

        $this->_caseForIds();
        $this->_caseForTrash();
    }

    /**
     * Обеспечение выборки конкретных записей.
     */
    protected function _caseForIds()
    {
        $ids = $this->getParam('ids');
        if ($ids) {
            $this->_query->andWhereIn('x.id', $ids);
        }
    }

    /**
     * Организация выборки для корзины.
     */
    protected function _caseForTrash()
    {
        if (($this->_modelName)::isRemovable() && ($this->_modelName)::$saveHistory) {
            if ($this->getParam('deleted')) {
                $this->_query->addWhere('x.deleted = 1');
                $this->_query->setMiddleHard(true);
            } elseif ($this->hasParam('ids')) {
                $this->_query->setMiddleHard(true);
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function _orderHelper($field, $direction = 'ASC')
    {
        if ('title' === $field) {
            $field = ($this->_modelName)::$titleField;
        }

        $this->_query->orderBy($field . ' ' . $direction);
    }

    /**
     * {@inheritdoc}
     */
    protected function _setDefaultOrder()
    {
        $this->_query->orderBy(($this->_modelName)::$defaultOrder);
    }
}
