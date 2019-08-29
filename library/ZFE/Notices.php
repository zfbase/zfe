<?php

/*
 * ZFE – платформа для построения редакторских интерфейсов.
 */

/**
 * Менеджер нотификаций.
 */
final class ZFE_Notices
{
    /**
     * Данные нотификаций в сессии.
     *
     * @var Zend_Session_Namespace
     */
    private $_session;

    /**
     * Конструктор.
     */
    private function __construct()
    {
        $this->_session = new Zend_Session_Namespace('Notices');
    }

    /**
     * Экземпляр менеджера нотификаций.
     *
     * @var ZFE_Notices
     */
    private static $_instance;

    /**
     * Получить экземпляр менеджера нотификаций.
     *
     * @return ZFE_Notices
     */
    private static function _getInstance()
    {
        if (!self::$_instance) {
            self::$_instance = new self;
        }

        return self::$_instance;
    }

    /**
     * Установить сообщение произвольного типа.
     *
     * Сообщение показывается только один раз и при первом же отображении страницы.
     * Если запрос через AJAX, то сообщение отправляется сразу же.
     *
     * @param string $message сообщение
     * @param array  $options параметры
     *
     * @return ZFE_Notices
     */
    public static function add($message, array $options = [])
    {
        $instance = self::_getInstance();
        $instance->_session->events[] = [
            'message' => $message,
            'options' => $options,
        ];
        return $instance;
    }

    /**
     * Установить сообщение об ошибке.
     *
     * @param string|Throwable $message сообщение или экземпляр ошибки/исключения
     *
     * @return ZFE_Notices
     */
    public static function err($message)
    {
        if ($message instanceof Throwable) {
            $code = $message->getCode();
            $message = $message->getMessage();
            if ($code) {
                $message = sprintf('[%s] %s', $code, $message);
            }
        }
        return self::add($message, ['type' => 'danger']);
    }

    /**
     * Установить сообщение о предупреждение.
     *
     * @param string $message сообщение
     *
     * @return ZFE_Notices
     */
    public static function warn($message)
    {
        return self::add($message, ['type' => 'warning']);
    }

    /**
     * Установить сообщение об успехе.
     *
     * @param string $message сообщение
     *
     * @return ZFE_Notices
     */
    public static function ok($message)
    {
        return self::add($message, ['type' => 'success']);
    }

    /**
     * Установить сообщение об уведомление.
     *
     * @param string $message сообщение
     *
     * @return ZFE_Notices
     */
    public static function msg($message)
    {
        return self::add($message, ['type' => 'info']);
    }

    /**
     * Вернуть все сообщения.
     *
     * @return array
     */
    public static function getAll()
    {
        return self::_getInstance()->_session->events ?? [];
    }

    /**
     * Удалить ранее установленные сообщения.
     *
     * @return ZFE_Notices
     */
    public static function clear()
    {
        $instance = self::_getInstance();

        if (isset($instance->_session->events)) {
            $instance->_session->events = null;
        }

        return $instance;
    }
}
