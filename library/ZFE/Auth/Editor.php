<?php

/*
 * ZFE – платформа для построения редакторских интерфейсов.
 */

/**
 * Класс программного контроля аутентификации пользователя.
 */
class ZFE_Auth_Editor
{
    protected static $instance;

    /**
     * @return static
     */
    public static function getInstance()
    {
        if (static::$instance === null) {
            static::$instance = new static;
        }
        return static::$instance;
    }

    protected function identify()
    {
        $auth = Zend_Auth::getInstance();
        if (!$auth->hasIdentity()) {
            throw new Zend_Auth_Exception('Пользователь не аутентифицирован');
        }
        return $auth->getIdentity();
    }

    /**
     * @throws Zend_Auth_Exception
     *
     * @return int
     */
    public function getId()
    {
        $identity = $this->identify();
        return intval($identity['id']);
    }

    /**
     * @throws Zend_Auth_Exception
     *
     * @return string
     */
    public function getRole()
    {
        $identity = $this->identify();
        return $identity['role'];
    }

    /**
     * Возвращает аутентифицированного пользователя, если это допустимо.
     *
     * @throws Zend_Auth_Exception
     *
     * @return Editors|null
     */
    public function get(): ?Editors
    {
        return Editors::findForAuth($this->getId());
    }

    /**
     * @throws Zend_Auth_Storage_Exception
     */
    public function set(Editors $editor)
    {
        $this->clear();

        Zend_Auth::getInstance()
            ->getStorage()
            ->write(['id' => $editor->id])
        ;
    }

    public function clear()
    {
        $auth = Zend_Auth::getInstance();
        if ($auth->hasIdentity()) {
            $auth->clearIdentity();
        }
    }
}
