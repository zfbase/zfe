<?php

/*
 * ZFE – платформа для построения редакторских интерфейсов.
 */

/**
 * Писатель логов в Doctrine.
 */
class ZFE_Log_Writer_Doctrine extends Zend_Log_Writer_Abstract
{
    /**
     * Name of the log model.
     *
     * @var string
     */
    protected $_modelName;

    /**
     * Relates database columns names to log data field keys.
     *
     * @var null|array
     */
    protected $_columnMap;

    /**
     * Class constructor.
     *
     * @param string $modelName Log table in database
     * @param array  $columnMap
     */
    public function __construct($modelName, $columnMap = null)
    {
        $this->_modelName = $modelName;
        $this->_columnMap = $columnMap;
    }

    /**
     * Create a new instance.
     *
     * @param array|Zend_Config $config
     *
     * @return Shared_Log_Writer_Doctrine
     */
    public static function factory($config)
    {
        $config = self::_parseConfig($config);
        $config = array_merge([
            'modelName' => null,
            'columnMap' => null,
        ], $config);

        if (isset($config['columnmap'])) {
            $config['columnMap'] = $config['columnmap'];
        }

        if (isset($config['modelname'])) {
            $config['modelName'] = $config['modelname'];
        }

        return new self(
            $config['modelName'],
            $config['columnMap']
        );
    }

    /**
     * Formatting is not possible on this writer.
     *
     * @param Zend_Log_Formatter_Interface $formatter
     *
     * @throws Zend_Log_Exception
     */
    public function setFormatter(Zend_Log_Formatter_Interface $formatter)
    {
        require_once 'Zend/Log/Exception.php';
        throw new Zend_Log_Exception(get_class($this) . ' does not support formatting');
    }

    /**
     * Write a message to the log.
     *
     * @param array $event event data
     */
    protected function _write($event)
    {
        $column_map = [
            'timestamp' => 'created',
            'message' => 'message',
            'priority' => 'priority',
            'priorityName' => 'priority_name',
        ];

        if (null !== $this->_columnMap) {
            $column_map = array_merge($column_map, $this->_columnMap);
        }

        $event_db = new $this->_modelName();
        foreach ($column_map as $zend_name => $column_name) {
            $event_db->{$column_name} = $event[$zend_name];
        }
        $event_db->save();
    }
}
