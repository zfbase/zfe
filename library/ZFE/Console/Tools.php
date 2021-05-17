<?php

/*
 * ZFE – платформа для построения редакторских интерфейсов.
 */

/**
 * Единая точка входа для консольных скриптов.
 *
 * Резервирует в конфигурации следующие параметры (использованы значения по умолчанию):
 * console.commandBroker = "ZFE_Console_CommandBroker"
 * console.helperBroker = "ZFE_Console_HelperBroker"
 * console.logger = "ZFE_Console_Logger"
 */
class ZFE_Console_Tools
{
    /**
     * Вызываемая команда.
     *
     * @var string|null
     */
    protected $_call;

    /**
     * Параметры вызываемой команды.
     *
     * @var array
     */
    protected $_params = [];

    /**
     * Брокер команд.
     *
     * @var ZFE_Console_CommandBroker|null
     */
    protected $_commandBroker;

    /**
     * Брокер помощников.
     *
     * @var ZFE_Console_HelperBroker|null
     */
    protected $_helperBroker;

    /**
     * Логгер.
     *
     * @var ZFE_Console_Logger|null
     */
    protected $_logger;

    /**
     * Конструктор.
     */
    public function __construct()
    {
        if (isset($_SERVER['argv'])) {
            $this->_call = $_SERVER['argv'][1] ?? null;
            $this->_params = array_slice($_SERVER['argv'], 2);
        }
    }

    /**
     * Указать параметры вызываемой команды.
     *
     * @param array $params
     *
     * @return ZFE_Console_Tools
     */
    public function setParams(array $params)
    {
        $this->_params = $params;
        return $this;
    }

    /**
     * Указать брокер команд.
     *
     * @param ZFE_Console_CommandBroker $broker
     *
     * @return ZFE_Console_Tools
     */
    public function setCommandBroker(ZFE_Console_CommandBroker $broker)
    {
        $this->_commandBroker = $broker;
        return $this;
    }

    /**
     * Получить брокер команд.
     *
     * @return ZFE_Console_CommandBroker
     */
    public function getCommandBroker()
    {
        if (!$this->_commandBroker) {
            $brokerClass = config('console.commandBroker', ZFE_Console_CommandBroker::class);
            $this->_commandBroker = $brokerClass::getInstance();
        }

        return $this->_commandBroker;
    }

    /**
     * Установить брокер помощников.
     *
     * @param ZFE_Console_HelperBroker
     *
     * @return ZFE_Console_Tools
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
        if (!$this->_helperBroker) {
            $brokerClass = config('console.helperBroker', ZFE_Console_HelperBroker::class);
            $this->_helperBroker = $brokerClass::getInstance();
        }

        return $this->_helperBroker;
    }

    /**
     * Установить логгер.
     *
     * @param ZFE_Console_Logger $logger
     *
     * @return ZFE_Console_Tools
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
        if (!$this->_logger) {
            $loggerClass = config('console.logger', ZFE_Console_Logger::class);
            $this->_logger = new $loggerClass();
        }

        return $this->_logger;
    }

    /**
     * Выполнить команду.
     *
     * @param string $command
     * @param array  $params
     */
    public function run(string $command = null, array $params = null)
    {
        if ($command) {
            $this->_call = $command;
        }

        if (empty($this->_call)) {
            $this->_call = 'help';
        }

        if (null !== $params) {
            $this->_params = $params;
        }

        return $this->getCommandBroker()
            ->getCommand($this->_call)
            ->setLogger($this->getLogger())
            ->setHelperBroker($this->getHelperBroker())
            ->execute($this->_params)
        ;
    }
}
