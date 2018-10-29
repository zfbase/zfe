<?php

/*
 * ZFE – платформа для построения редакторских интерфейсов.
 */

class ZFE_Sphinx_Fake_Pager extends Doctrine_Pager
{
    protected $_count;

    public function __construct($query, $page, $maxPerPage = 0, $count = 0)
    {
        parent::__construct($query, $page, $maxPerPage);
        $this->_count = $count;
    }

    protected function _initialize($params = [])
    {
        $countQuery = new ZFE_Sphinx_Fake_CountQuery($this->_count);
        $count = $countQuery->execute();
        //$count = $countQuery->count($this->getCountQueryParams($params));

        $this->_setNumResults($count);
        $this->_setExecuted(true); // _adjustOffset relies of _executed equals true = getNumResults()

        $this->_adjustOffset();
    }

    public function getNumResults()
    {
        return $this->_count;
    }

    public function getMaxPerPage()
    {
        return 10000000;
    }
}
