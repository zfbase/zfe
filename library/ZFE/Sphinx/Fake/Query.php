<?php

/*
 * ZFE – платформа для построения редакторских интерфейсов.
 */

class ZFE_Sphinx_Fake_Query
{
    public function offset()
    {
        return $this;
    }

    public function limit()
    {
        return $this;
    }

    public function execute()
    {
        return [];
    }
}
