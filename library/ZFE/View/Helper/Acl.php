<?php

/*
 * ZFE – платформа для построения редакторских интерфейсов.
 */

/**
 * Помощник вызова ACL.
 */
class ZFE_View_Helper_Acl extends Zend_View_Helper_Abstract
{
    /**
     * Получить ACL.
     *
     * @return ZFE_Acl
     */
    public function acl()
    {
        return Zend_Registry::get('acl');
    }
}
