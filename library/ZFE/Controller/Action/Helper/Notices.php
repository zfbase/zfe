<?php

/*
 * ZFE – платформа для построения редакторских интерфейсов.
 */

/**
 * Менеджер нотификаций.
 *
 * @category  ZFE
 */
class ZFE_Controller_Action_Helper_Notices extends Zend_Controller_Action_Helper_Abstract
{
    /**
     * Данные нотификаций в сессии.
     *
     * @var Zend_Session_Namespace
     */
    protected $_session;

    /**
     * Хук, выполняющийся при инициализации контроллера.
     */
    public function init()
    {
        $this->_session = new Zend_Session_Namespace('Notices');
    }

    /**
     * Псевдоним для метода $this->add().
     *
     * @param string $message
     * @param array  $options
     */
    public function direct($message, array $options = [])
    {
        return $this->add($message, $options);
    }

    /**
     * Установить сообщение произвольного типа.
     *
     * Всплывающее сообщение показывается только один раз и при первом же отображении страницы.
     * Если запрос через AJAX, то сообщение отправляется сразу же.
     *
     * @param string $message сообщение
     * @param array  $options параметры (см. библиотеку bootstrap-growl)
     *
     * @return ZFE_Controller_Action_Helpers_Notices
     */
    public function add($message, array $options = [])
    {
        $this->_session->events[] = [
            'message' => $message,
            'options' => $options,
        ];

        return $this;
    }

    /**
     * Установить сообщение об ошибке.
     *
     * @param string $message сообщение
     *
     * @return ZFE_Controller_Action_Helpers_Notices
     */
    public function err($message)
    {
        return $this->add($message, ['type' => 'danger', 'delay' => '9000']);
    }

    /**
     * Установить сообщение о предупреждение.
     *
     * @param string $message сообщение
     *
     * @return ZFE_Controller_Action_Helpers_Notices
     */
    public function warn($message)
    {
        return $this->add($message, ['type' => 'warning', 'delay' => '7000']);
    }

    /**
     * Установить сообщение об успехе.
     *
     * @param string $message сообщение
     *
     * @return ZFE_Controller_Action_Helpers_Notices
     */
    public function ok($message)
    {
        return $this->add($message, ['type' => 'success', 'delay' => '5000']);
    }

    /**
     * Установить сообщение об уведомление.
     *
     * @param string $message сообщение
     *
     * @return ZFE_Controller_Action_Helpers_Notices
     */
    public function msg($message)
    {
        return $this->add($message, ['type' => 'info']);
    }

    /**
     * Удалить ранее установленные сообщения.
     *
     * @return ZFE_Controller_Action_Helpers_Notices
     */
    public function clear()
    {
        $this->_session->events = null;
        return $this;
    }
}
