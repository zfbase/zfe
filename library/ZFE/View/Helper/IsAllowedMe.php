<?php

/*
 * ZFE – платформа для построения редакторских интерфейсов.
 */

/**
 * Помощник проверки доступа.
 */
class ZFE_View_Helper_IsAllowedMe extends Zend_View_Helper_Abstract
{
    /**
     * Проверить наличие доступа.
     *
     * @param string $resource
     * @param string $privilege
     *
     * @return bool
     */
    public function isAllowedMe($resource = null, $privilege = null)
    {
        return Zend_Registry::get('acl')->isAllowedMe($resource, $privilege);
    }
}
