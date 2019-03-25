<?php

/*
 * ZFE – платформа для построения редакторских интерфейсов.
 */

/**
 * Брокер консольных команд.
 */
class ZFE_Console_CommandBroker
{
    /**
     * Зарегистрированные команды.
     *
     * @var array
     */
    protected $_commands = [];

    /**
     * Набор сопоставлений префиксов и путей.
     *
     * @var array<string>|string[]
     */
    protected $_prefixPaths = [];

    /**
     * Экземпляр брокера.
     *
     * @var ZFE_Console_CommandBroker
     */
    protected static $_instance;

    /**
     * Получить экземпляр брокера.
     *
     * @return ZFE_Console_CommandBroker
     */
    public static function getInstance()
    {
        if ( ! static::$_instance) {
            static::$_instance = new static();
        }
        return static::$_instance;
    }

    /**
     * Конструктор.
     */
    protected function __construct()
    {
        $config = Zend_Registry::get('config');
        
        // Настройка путей автозагрузчика
        $this->addPrefixPath('ZFE_Console_Command', ZFE_PATH . '/console/Command');

        if ($config->console->prefixPath ?? false) {
            foreach ($config->console->prefixPath as $name => $options) {
                $this->addPrefixPath($options['namespace'], $options['path']);
            }
        } else {
            $this->addPrefixPath('Application_Console_Command', APPLICATION_PATH . '/Console/Command');
        }
        

        // Стандартные команды
        $this->registerCommand('Help');
        $this->registerCommand('ModelsGenerate');
        $this->registerCommand('SphinxIndexer');
        $this->registerCommand('ApplySchema');
        $this->registerCommand('Migration');


        // Собираем команды из конфига
        if ($config->console->command ?? false) {
            foreach ($config->console->command as $name => $command) {
                $this->registerCommand($command, is_string($name) ? $name : null);
            }
        }
    }

    /**
     * Зарегистрировать команду.
     *
     * @param string|ZFE_Console_Command_Abstract $command
     * @param string                              $name
     * @param bool                                $replace Заменять зарегистрированную ранее команду с указанным ключом
     *
     * @return ZFE_Console_CommandBroker
     */
    public function registerCommand($command, ?string $name = null, bool $replace = false)
    {
        $class = is_string($command) ? $this->_getCommandClass($command) : get_class($command);

        $name = $name ?? $class::getName();
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

        $this->_commands[$name] = is_string($command) ? $class : $command;
        return $this;
    }

    /**
     * Удалить команду из зарегистрированных.
     *
     * @param string $name
     * 
     * @return ZFE_Console_CommandBroker
     */
    public function unregisterCommand(string $name)
    {
        unset($this->_commands[$name]);
        return $this;
    }

    /**
     * Добавить префикс и его путь.
     *
     * @param string $prefix
     * @param string $path
     * 
     * @return ZFE_Console_CommandBroker
     */
    public function addPrefixPath(string $prefix, string $path)
    {
        $this->_prefixPaths[$prefix] = $path;
        return $this;
    }

    /**
     * Очистить набор префиксов и их путей.
     *
     * @return ZFE_Console_CommandBroker
     */
    public function clearPrefixPaths()
    {
        $this->_prefixPaths = [];
        return $this;
    }

    /**
     * Проверить зарегистрированность команды.
     *
     * @param string $name
     * 
     * @return boolean
     */
    public function hasCommand(string $name)
    {
        return isset($this->_commands[$name]);
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
        if ( ! key_exists($name, $this->_commands)) {
            throw new ZFE_Console_Exception('Команда не зарегистрирована.');
        }

        $command = $this->_commands[$name];
        if (is_string($command)) {
            $class = $this->_getCommandClass($command);
            $command = new $class();
        }

        if ( ! $command instanceof ZFE_Console_Command_Abstract) {
            throw new ZFE_Console_Exception('Команда не валидна.');
        }

        return $command;
    }

    /**
     * Получить список зарегистрированных команд.
     *
     * @return array|ZFE_Console_Command_Abstract[]
     */
    public function getCommands()
    {
        return array_map(function ($command) {
            return is_object($command) ? get_type($command) : $command;
        }, $this->_commands);
    }

    protected function _getCommandClass(string $name)
    {
        // Сначала проверяем, не полное ли это название класса
        if (class_exists($name)) {
            return $name;
        }

        // Тогда считаем последней частью имени класса (именем файла)
        $name = ucfirst($name);

        if (false !== strpos($name, '\\')) {
            $classFile = str_replace('\\', DIRECTORY_SEPARATOR, $name) . '.php';
        } else {
            $classFile = str_replace('_', DIRECTORY_SEPARATOR, $name) . '.php';
        }

        $prefixPaths = array_reverse($this->_prefixPaths, true);
        foreach ($prefixPaths as $prefix => $path) {
            $className = $prefix . '_' . $name;

            if (class_exists($className)) {
                return $className;
            }

            $fileName = $path . $classFile;
            if (Zend_Loader::isReadable($fileName)) {
                include_once $fileName;
                if (class_exists($className, false)) {
                    return $className;
                }
            }
        }
    }
}
