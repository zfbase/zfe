<?php

/*
 * ZFE – платформа для построения редакторских интерфейсов.
 */

class ZFE_Sphinx_Pager extends Doctrine_Pager
{
    protected function _initialize($params = [])
    {
        /** @var Foolz\SphinxQL\SphinxQL $countQuery */
        $countQuery = clone $this->getCountQuery();
        $count = $countQuery
            ->setSelect('COUNT(*)')
            ->resetOrderBy()
            ->execute()
            ->fetchNum()[0];

        $this->_setNumResults($count);
        $this->_setExecuted(true);

        $this->_adjustOffset();
    }
}
