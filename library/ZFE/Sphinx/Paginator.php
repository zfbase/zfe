<?php

/*
 * ZFE – платформа для построения редакторских интерфейсов.
 */

use Foolz\SphinxQL\Drivers\ResultSetInterface;
use Foolz\SphinxQL\SphinxQL;

class ZFE_Sphinx_Paginator extends ZFE_Paginator
{
    /**
     * @param SphinxQL $query
     * @param array    $params
     * @param int      $count
     *
     * @return ResultSetInterface
     */
    public static function execute($query, array $params = [], $count = null)
    {
        $instance = self::getInstance();

        if (null !== $count) {
            $instance->_pager = new ZFE_Sphinx_Fake_Pager(
                new ZFE_Sphinx_Fake_Query(),
                $instance->getPageNumber(),
                $instance->_itemsPerPage,
                $count
            );
        } else {
            $instance->_pager = new ZFE_Sphinx_Pager(
                $query,
                $instance->getPageNumber(),
                $instance->_itemsPerPage
            );
        }

        return $instance->_pager->execute($params);
    }
}
