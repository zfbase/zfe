<?php

/*
 * ZFE – платформа для построения редакторских интерфейсов.
 */

/**
 * Брокер помощников представления консоли.
 *
 * Резервирует в конфигурации следующие параметры:
 * console.prefixPath.Application_Console = APPLICATION_PATH . '/Console'  ; конфиг по умолчанию, добавляющий префикс Application_Console_Helper_* для команд по адресу APPLICATION_PATH . /Console/Helper/*
 * console.helpers.charts = 'Application_Console_Helper_Charts'  ; Регистрирует команду Application_Console_Helper_Charts с ключом charts
 * console.helpers[] = 'Application_Console_Helper_Charts'  ; Регистрирует команду Application_Console_Helper_Charts с ключом команды по умолчанию
 */
class ZFE_Console_HelperBroker
{
    /**
     * Набор сопоставлений префиксов и путей.
     *
     * @var array<string>|string[]
     */
    protected $_prefixPaths = [];

    /**
     * Экземпляр брокера.
     *
     * @var ZFE_Console_HelperBroker
     */
    protected static $_instance;

    /**
     * Получить экземпляр брокера.
     *
     * @return ZFE_Console_HelperBroker
     */
    public static function getInstance()
    {
        if (!static::$_instance) {
            static::$_instance = new static();
        }
        return static::$_instance;
    }

    /**
     * Конструктор.
     */
    protected function __construct()
    {
        // Настройка путей автозагрузчика
        $this->addPrefixPath('ZFE_Console', ZFE_PATH . '/console');

        $appPrefixPath = config('console.prefixPath', ['Application_Console' => APPLICATION_PATH . '/Console']);
        foreach ($appPrefixPath as $namespace => $path) {
            $this->addPrefixPath($namespace, $path);
        }
    }

    /**
     * Добавить префикс и его путь.
     *
     * @param string $prefix
     * @param string $path
     *
     * @return ZFE_Console_HelperBroker
     */
    public function addPrefixPath(string $prefix, string $path)
    {
        $this->_prefixPaths[$prefix] = rtrim($path, DIRECTORY_SEPARATOR);
        return $this;
    }

    /**
     * Очистить набор префиксов и их путей.
     *
     * @return ZFE_Console_HelperBroker
     */
    public function clearPrefixPaths()
    {
        $this->_prefixPaths = [];
        return $this;
    }

    /**
     * Получить помощника по имени.
     *
     * @param string $name
     *
     * @return ZFE_Console_Helper_Abstract
     */
    public function get(string $name)
    {
        if (class_exists($name)) {
            return new $name;
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
            $className = $prefix . '_Helper_' . $name;

            if (class_exists($className)) {
                return new $className;
            }

            $fileName = $path . DIRECTORY_SEPARATOR . 'Helper' . DIRECTORY_SEPARATOR . $classFile;
            if (Zend_Loader::isReadable($fileName)) {
                include_once $fileName;
                if (class_exists($className, false)) {
                    return new $className;
                }
            }
        }

        throw new ZFE_Console_Exception("Помощник '${name}' не найден.");
    }
}
