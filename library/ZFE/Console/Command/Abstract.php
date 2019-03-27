<?php

/*
 * ZFE – платформа для построения редакторских интерфейсов.
 */

/**
 * Абстрактная команда.
 */
abstract class ZFE_Console_Command_Abstract
{
    /**
     * Код.
     *
     * @var string
     */
    protected static $_name;

    /**
     * Описание.
     *
     * @var string|null
     */
    protected static $_description;

    /**
     * Подробная информация по команде.
     *
     * @var string|null
     */
    protected static $_help;

    /**
     * Резрешено выполнять из интерфейса пользователей?
     *
     * @var bool
     */
    protected static $_allowInApp = true;

    /**
     * Логгер.
     *
     * @var ZFE_Console_Logger|null
     */
    protected $_logger;

    /**
     * Брокер помощников.
     *
     * @var ZFE_Console_HelperBroker|null
     */
    protected $_helperBroker;

    /**
     * Получить код.
     *
     * @return string
     */
    public static function getName()
    {
        return static::$_name;
    }

    /**
     * Получить описание.
     *
     * @return string|null
     */
    public static function getDescription()
    {
        return static::$_description;
    }

    /**
     * Получить подробную информацию по команде.
     *
     * @return string|null
     */
    public static function getHelp()
    {
        return static::$_help;
    }

    /**
     * Получить право на исполнение из пользовательского интерфейса.
     *
     * @return bool
     */
    public static function isAllowInApp()
    {
        return static::$_allowInApp;
    }

    /**
     * Установить логгер.
     *
     * @param ZFE_Console_Logger $logger
     *
     * @return ZFE_Console_Command_Abstract
     */
    public function setLogger(ZFE_Console_Logger $logger)
    {
        $this->_logger = $logger;
        return $this;
    }

    /**
     * Получить логгер.
     *
     * @return ZFE_Console_Logger
     */
    public function getLogger()
    {
        if ( ! $this->_logger) {
            $this->_logger = new ZFE_Console_Logger();
        }

        return $this->_logger;
    }

    /**
     * Установить брокер помощников.
     *
     * @param ZFE_Console_HelperBroker
     *
     * @return ZFE_Console_Command_Abstract
     */
    public function setHelperBroker(ZFE_Console_HelperBroker $broker)
    {
        $this->_helperBroker = $broker;
        return $this;
    }

    /**
     * Получить брокера помощников.
     *
     * @return ZFE_Console_HelperBroker
     */
    public function getHelperBroker()
    {
        return $this->_helperBroker;
    }

    /**
     * Выполнить команду.
     *
     * @param array $params
     */
    abstract public function execute(array $params = []);
}
