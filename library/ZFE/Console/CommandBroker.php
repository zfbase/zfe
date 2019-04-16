<?php

/*
 * ZFE – платформа для построения редакторских интерфейсов.
 */

/**
 * Брокер консольных команд.
 *
 * Резервирует в конфигурации следующие параметры:
 * console.prefixPath.Application_Console = APPLICATION_PATH . '/Console'  ; конфиг по умолчанию, добавляющий префикс Application_Console_Command_* для команд по адресу APPLICATION_PATH . /Console/Command/*
 * console.commands.sendmail = 'Application_Plugin_MailSender'  ; Регистрирует команду Application_Plugin_MailSender с ключом sendmail
 * console.commands[] = 'Application_Plugin_MailSender'  ; Регистрирует команду Application_Plugin_MailSender с ключом команды по умолчанию
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
        $this->addPrefixPath('ZFE_Console', ZFE_PATH . '/Console');

        $appPrefixPath = $config->console->prefixPath ?? ['Console' => APPLICATION_PATH . '/console'];
        foreach ($appPrefixPath as $namespace => $path) {
            if (is_readable($path)) {
                $this->addPrefixPath($namespace, $path);
            }
        }

        // Загрузка всех команд из директорий
        foreach ($this->_prefixPaths as $prefix => $path) {
            $this->loadCommand($path . DIRECTORY_SEPARATOR . 'Command', $prefix . '_' . 'Command');
        }

        // Собираем команды из конфига
        foreach ($config->console->commands ?? [] as $name => $command) {
            $this->registerCommand($command, !empty($name) && is_string($name) ? $name : null);
        }
    }

    /**
     * Подгрузить команды из конкретной директории с конкретным префиксом.
     *
     * @param string $path
     * @param string $prefix
     *
     * @return ZFE_Console_CommandBroker
     */
    public function loadCommand(string $path, string $prefix)
    {
        $files = scandir($path, SCANDIR_SORT_ASCENDING);
        foreach ($files as $file) {
            if ('.php' !== mb_substr($file, -4)) {
                continue;
            }

            $fileName = rtrim($path, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $file;
            $className = $prefix . '_' . mb_substr($file, 0, -4);
            if (Zend_Loader::isReadable($fileName)) {
                include_once $fileName;
                if (class_exists($className, false)) {
                    if ( ! $this->hasCommandByClass($className)) {
                        $reflection = new ReflectionClass($className);
                        if ($reflection->isSubclassOf(ZFE_Console_Command_Abstract::class) && ! $reflection->isAbstract()) {
                            $this->registerCommand($className);
                        }
                    }
                }
            }
        }

        return $this;
    }

    /**
     * Зарегистрировать команду.
     *
     * @param string|ZFE_Console_Command_Abstract $command
     * @param string|null                         $name
     * @param bool                                $replace Заменять зарегистрированную ранее команду с указанным ключом
     *
     * @return ZFE_Console_CommandBroker
     */
    public function registerCommand($command, ?string $name = null, bool $replace = true)
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
        $this->_prefixPaths[$prefix] = rtrim($path, DIRECTORY_SEPARATOR);
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
     * @return bool
     */
    public function hasCommand(string $name)
    {
        return isset($this->_commands[$name]);
    }

    /**
     * Проверить зарегистрированность команды по имени класса.
     *
     * @param string $class
     *
     * @return bool
     */
    public function hasCommandByClass(string $class)
    {
        foreach ($this->_commands as $command) {
            if (is_object($command)) {
                if (get_class($command) === $class) {
                    return true;
                }
            }

            if (is_string($command)) {
                if ($this->_getCommandClass($command) === $class) {
                    return true;
                }
            }
        }

        return false;
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
            throw new ZFE_Console_Exception("Команда '${name}' не зарегистрирована.");
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
            return is_object($command) ? gettype($command) : $command;
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

        if (false !== mb_strpos($name, '\\')) {
            $classFile = str_replace('\\', DIRECTORY_SEPARATOR, $name) . '.php';
        } else {
            $classFile = str_replace('_', DIRECTORY_SEPARATOR, $name) . '.php';
        }

        $prefixPaths = array_reverse($this->_prefixPaths, true);
        foreach ($prefixPaths as $prefix => $path) {
            $className = $prefix . '_Command_' . $name;

            if (class_exists($className)) {
                return $className;
            }

            $fileName = $path . DIRECTORY_SEPARATOR . 'Command' . DIRECTORY_SEPARATOR . $classFile;
            if (Zend_Loader::isReadable($fileName)) {
                include_once $fileName;
                if (class_exists($className, false)) {
                    return $className;
                }
            }
        }

        throw new ZFE_Console_Exception("Команда '${name}' не найдена.");
    }
}
