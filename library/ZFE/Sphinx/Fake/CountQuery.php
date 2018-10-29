<?php

/*
 * ZFE – платформа для построения редакторских интерфейсов.
 */

class ZFE_Sphinx_Fake_CountQuery
{
    protected $_count = 0;

    public function __construct($count)
    {
        $this->_count = $count;
    }

    public function execute()
    {
        return [
            'COUNT(*)' => $this->_count,
        ];
    }
}
