<?php

/*
 * ZFE – платформа для построения редакторских интерфейсов.
 */

/**
 * @todo Добавить возможность переопределения команд, помощников и логгеров из библиотеки стандартным зендовским методом.
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
     * Зарегистрированные команды.
     *
     * @var array
     */
    protected $_commands = [];

    /**
     * Инициализированные команды.
     *
     * @var array<string>|string[]
     */
    protected $_initializedCommands = [];

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
     * Конструктор.
     */
    public function __construct()
    {
        if (isset($_SERVER['argv'])) {
            $this->_call = $_SERVER['argv'][1] ?? null;
            $this->_params = array_slice($_SERVER['argv'], 2);
        }

        $this->registryCommand(new ZFE_Console_Command_Help($this));
        $this->registryCommand(ZFE_Console_Command_ModelsGenerate::class);
        $this->registryCommand(ZFE_Console_Command_SphinxIndexer::class);
    }

    /**
     * Зарегистрировать команду.
     *
     * @param string|ZFE_Console_Command_Abstract $command
     * @param string                              $name
     * @param bool                                $replace Заменять зарегистрированную ранее команду с указанным ключом
     *
     * @return ZFE_Console_Tools
     */
    public function registryCommand($command, string $name = null, bool $replace = false)
    {
        $name = $name ?? $command::getName();
        if (empty($name)) {
            throw new ZFE_Console_Exception('Нельзя зарегистрировать команду без ключа.');
        }

        if (key_exists($name, $this->_commands) && ! $replace) {
            $prevCommand = $this->_commands[$name];
            if (is_object($prevCommand)) {
                $prevCommand = get_class($prevCommand);
            }

            throw new ZFE_Console_Exception("Ключ '${$name}' уже использован для команды '${prevCommand}'.");
        }

        $this->_commands[$name] = $command;
        return $this;
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
        if ( ! $this->_helperBroker) {
            $this->_helperBroker = new ZFE_Console_HelperBroker();
        }

        return $this->_helperBroker;
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
     * Выполнить команду.
     *
     * @param string $command
     */
    public function run(string $command = null)
    {
        if ($command) {
            $this->_call = $command;
        }

        if (empty($this->_call)) {
            $this->_call = 'help';
        }

        return $this->getCommand($this->_call)->execute($this->_params);
    }

    /**
     * Получить все команды.
     *
     * @param bool $initialize Инициализировать перед возвращением
     *
     * @return array|ZFE_Console_Command_Abstract[]
     */
    public function getCommands(bool $initialize = true)
    {
        if ( ! $initialize) {
            return $this->_commands;
        }

        return array_map(function ($command) {
            $command = $this->getCommand($command);
        }, $this->_commands);
    }

    /**
     * Вернуть инициализированную команду.
     *
     * @param string $name
     *
     * @return ZFE_Console_Command_Abstract
     */
    public function getCommand(string $name)
    {
        if (in_array($name, $this->_initializedCommands, true)) {
            return $this->_commands[$name];
        }

        if ( ! key_exists($name, $this->_commands)) {
            throw new ZFE_Console_Exception('Команда не зарегистрирована.');
        }

        $command = $this->_commands[$name];
        if (is_string($command)) {
            $command = new $command;
        }

        if ( ! $command instanceof ZFE_Console_Command_Abstract) {
            throw new ZFE_Console_Exception('Команда не валидна.');
        }

        $this->_initializedCommands[] = $name;

        $command->setLogger($this->getLogger());
        $command->setHelperBroker($this->getHelperBroker());
        return $command;
    }
}
