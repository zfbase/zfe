<?php

/**
 * Class ZFE_Auth_Editor
 *
 * Класс программного контроля аутентификации пользователя
 */
class ZFE_Auth_Editor
{
    protected static $instance;

    public static function getInstance()
    {
        if (static::$instance === null) {
            static::$instance = new static;
        }
        return static::$instance;
    }

    /**
     * @return int
     * @throws Zend_Auth_Exception
     */
    public function getId()
    {
        $auth = Zend_Auth::getInstance();
        if (!$auth->hasIdentity()) {
            throw new Zend_Auth_Exception('Пользователь не аутентифицирован');
        }
        $identity = $auth->getIdentity();
        return intval($identity['id']);
    }

    /**
     * Возвращает аутентифицированного пользователя, если это допустимо
     *
     * @return Editors|null
     * @throws Zend_Auth_Exception
     */
    public function get() : ?Editors
    {
        return Editors::findForAuth($this->getId());
    }

    /**
     * @param Editors $editor
     * @throws Zend_Auth_Storage_Exception
     */
    public function set(Editors $editor)
    {
        $auth = Zend_Auth::getInstance();
        $this->clear();

        $auth->getStorage()->write(['id' => $editor->id]);
    }

    /**
     *
     */
    public function clear()
    {
        $auth = Zend_Auth::getInstance();
        if ($auth->hasIdentity()) {
            $auth->clearIdentity();
        }
    }
}