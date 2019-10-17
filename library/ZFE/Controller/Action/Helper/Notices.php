<?php

/*
 * ZFE – платформа для построения редакторских интерфейсов.
 */

/**
 * Менеджер нотификаций.
 *
 * @deprecated 1.33.58
 */
class ZFE_Controller_Action_Helper_Notices extends Zend_Controller_Action_Helper_Abstract
{
    /**
     * Хук, выполняющийся при инициализации контроллера.
     */
    public function init()
    {
        trigger_error('Помощник ZFE_Controller_Action_Helper_Notices устарел. '
                    . 'Используйте современный способ с ZFE_Notices. ', E_USER_DEPRECATED);
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
     * @param array  $options параметры
     *
     * @return ZFE_Controller_Action_Helper_Notices
     */
    public function add($message, array $options = [])
    {
        ZFE_Notices::add($message, $options);
        return $this;
    }

    /**
     * Установить сообщение об ошибке.
     *
     * @param string|Throwable $message сообщение
     *
     * @return ZFE_Controller_Action_Helper_Notices
     */
    public function err($message)
    {
        ZFE_Notices::err($message);
        return $this;
    }

    /**
     * Установить сообщение о предупреждение.
     *
     * @param string $message сообщение
     *
     * @return ZFE_Controller_Action_Helper_Notices
     */
    public function warn($message)
    {
        ZFE_Notices::warn($message);
        return $this;
    }

    /**
     * Установить сообщение об успехе.
     *
     * @param string $message сообщение
     *
     * @return ZFE_Controller_Action_Helper_Notices
     */
    public function ok($message)
    {
        ZFE_Notices::ok($message);
        return $this;
    }

    /**
     * Установить сообщение об уведомление.
     *
     * @param string $message сообщение
     *
     * @return ZFE_Controller_Action_Helper_Notices
     */
    public function msg($message)
    {
        ZFE_Notices::msg($message);
        return $this;
    }

    /**
     * Удалить ранее установленные сообщения.
     *
     * @return ZFE_Controller_Action_Helper_Notices
     */
    public function clear()
    {
        ZFE_Notices::clear();
        return $this;
    }
}
