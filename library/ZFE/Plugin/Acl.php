<?php

/*
 * ZFE – платформа для построения редакторских интерфейсов.
 */

/**
 * Плагин авторизации и разделения прав.
 */
class ZFE_Plugin_Acl extends Zend_Controller_Plugin_Abstract
{
    /**
     * Экземпляр Zend_Acl.
     *
     * @var Zend_Acl
     */
    protected $_acl;

    /**
     * Роль.
     *
     * @var string
     */
    protected $_role;

    /**
     * Конструктор
     *
     * @param Zend_Acl $acl
     */
    public function __construct(Zend_Acl $acl)
    {
        $this->_acl = $acl;
        $this->_role = Zend_Registry::get('user')->role;
    }

    /**
     * Выполняется до того, как диспетчером будет вызвано действие.
     *
     * @param Zend_Controller_Request_Abstract $request
     */
    public function preDispatch(Zend_Controller_Request_Abstract $request)
    {
        $controller = $request->getControllerName();
        $action = $request->getActionName();

        if ( ! $this->isAllowed($controller, $action)) {
            $this->_attack($request);
        }
    }

    /**
     * Проверить наличие права к ресурсу для авторизованного пользователя.
     *
     * @param string $resource
     * @param string $privilege
     *
     * @return bool
     */
    public function isAllowed($resource, $privilege = null)
    {
        if ( ! $this->_acl->has($resource)) {
            $this->abort(404, "Ресурс {$resource} не найден");
        }

        return $this->_acl->isAllowedMe($resource, $privilege);
    }

    /**
     * Обработать запрос с нарушением прав доступа.
     *
     * @param Zend_Controller_Request_Abstract $request
     *
     * @throws Exception
     */
    protected function _attack(Zend_Controller_Request_Abstract $request)
    {
        if ('guest' === $this->_role) {
            $request->setControllerName('auth');
            $request->setActionName('login');
        } else {
            $this->abort(403, 'Доступ запрещён');
        }
    }
}
