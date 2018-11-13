<?php

/*
 * ZFE – платформа для построения редакторских интерфейсов.
 */

/**
 * Расширение подгрузки правил разделения доступа.
 *
 * Основано на примере:
 * http://stackoverflow.com/questions/5209671/zend-framework-need-typical-example-of-acl
 */
class ZFE_Acl extends Zend_Acl
{
    /**
     * Сведения о текущем пользователе.
     *
     * @var stdClass
     */
    private $_user;

    /**
     * Создать новый плагин ACL.
     *
     * @param Zend_Config $config
     */
    public function __construct(Zend_Config $config)
    {
        $this->_loadRoles($config->roles);
        $this->_loadResources($config->resources);
        $this->_user = Zend_Registry::get('user');
    }

    /**
     * Загрузить роли.
     *
     * @param Zend_Config $roles
     */
    protected function _loadRoles(Zend_Config $roles)
    {
        foreach ($roles as $name => $parents) {
            if ( ! $this->hasRole($name)) {
                if (empty($parents)) {
                    $parents = null;
                } else {
                    $parents = explode(',', $parents);
                }
                $this->addRole(new Zend_Acl_Role($name), $parents);
            }
        }
    }

    /**
     * Загрузить ресурсы.
     *
     * @param Zend_Config $resources
     */
    protected function _loadResources(Zend_Config $resources)
    {
        foreach ($resources as $permissions => $controllers) {
            foreach ($controllers as $controller => $actions) {
                if ('all' === $controller) {
                    $controller = null;
                } elseif ( ! $this->has($controller)) {
                    $this->add(new Zend_Acl_Resource($controller));
                }

                // необходимо для добавления ресурсов без явной таблицы прав
                // пример использования:
                // resources.undefined.editors = ""
                if ('' === $actions) {
                    continue;
                }

                foreach ($actions as $action => $roles) {
                    if ('' === trim($roles)) {
                        continue;
                    }

                    if ('all' === $action) {
                        $action = null;
                    }

                    foreach (explode(',', $roles) as $role) {
                        if ('allow' === $permissions) {
                            $this->allow($role, $controller, $action);
                        }

                        if ('deny' === $permissions) {
                            $this->deny($role, $controller, $action);
                        }
                    }
                }
            }
        }
    }

    /**
     * Проверить, определен ли ресурс
     *
     * @param string $resource
     *
     * @return bool
     */
    public function hasResource($resource)
    {
        return in_array($resource, $this->getResources(), true);
    }

    /**
     * Проверить наличие прав.
     *
     * @param string $resource
     * @param string $privilege
     *
     * @return bool
     */
    public function isAllowedMe($resource = null, $privilege = null)
    {
        return parent::isAllowed($this->_user->role, $resource, $privilege);
    }
}
