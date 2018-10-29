<?php

/*
 * ZFE – платформа для построения редакторских интерфейсов.
 */

class ZFE_Sphinx_Pager extends Doctrine_Pager
{
    protected function _initialize($params = [])
    {
        // retrieve the number of items found
        $countQuery = clone $this->getCountQuery();
        $count = $countQuery->setSelect('COUNT(*)')->execute()->fetchNum()[0];
        //$count = $countQuery->count($this->getCountQueryParams($params));

        $this->_setNumResults($count);
        $this->_setExecuted(true); // _adjustOffset relies of _executed equals true = getNumResults()

        $this->_adjustOffset();
    }
}
