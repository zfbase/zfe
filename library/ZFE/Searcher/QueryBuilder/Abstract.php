<?php

/*
 * ZFE – платформа для построения редакторских интерфейсов.
 */

/**
 * Базовый конструктор запросов.
 */
abstract class ZFE_Searcher_QueryBuilder_Abstract implements ZFE_Searcher_QueryBuilder_Interface
{
    /**
     * Наименование модели.
     *
     * @var string
     */
    protected $_modelName;

    /**
     * Экземпляр ZFE_Model_Table.
     *
     * @var ZFE_Model_Table
     */
    protected $_tableInstance;

    /**
     * Параметры запроса.
     *
     * @var array
     */
    protected $_params;

    /**
     * Запрос.
     *
     * @var mixed
     */
    protected $_query;

    /**
     * Число примененных расширенных фильтров.
     *
     * @var int
     */
    protected $_countUsedFilters = 0;

    /**
     * @param string $modelName
     */
    public function __construct($modelName)
    {
        $this->_modelName = $modelName;
        $this->_tableInstance = Doctrine_Core::getTable($modelName);
    }

    /**
     * {@inheritdoc}
     */
    public function setParams(array $params)
    {
        $this->_params = $params;
    }

    /**
     * Получить значение фильтра.
     *
     * @param string $name
     * @param mixed  $default
     *
     * @return mixed
     */
    public function getParam($name, $default = null)
    {
        if (key_exists($name, $this->_params)) {
            return $this->_params[$name];
        }

        return $default;
    }

    /**
     * Проверить наличие фильтра.
     *
     * @param string $name
     *
     * @return bool
     */
    public function hasParam($name)
    {
        return key_exists($name, $this->_params);
    }

    /**
     * {@inheritdoc}
     */
    public function getQuery(array $params = null)
    {
        if (null !== $params) {
            $this->setParams($params);
        }

        $this->_create();
        $this->_filters();
        $this->_order();

        return $this->_query;
    }

    /**
     * Сбор основного запроса (без фильтров и сортировок).
     */
    abstract protected function _create();

    /**
     * Добавление фильтров к запросу.
     */
    abstract protected function _filters();

    /**
     * Добавление сортировки в запрос.
     */
    protected function _order()
    {
        $order = $this->getParam('order');

        if ( ! empty($order)) {
            $pos = mb_strrpos($order, '_');
            $field = mb_substr($order, 0, $pos);
            $direction = mb_strtoupper(mb_substr($order, $pos + 1));
            if ('ASC' === $direction || 'DESC' === $direction) {
                $this->_orderHelper($field, $direction);
                return;
            }
        }

        $this->_setDefaultOrder();
    }

    /**
     * Помощник указания сортировки.
     * Именно его стоит переопределять для специфических случаев.
     *
     * @param string $field     поле
     * @param string $direction направление
     */
    abstract protected function _orderHelper($field, $direction = 'ASC');

    /**
     * Установить сортировку по умолчанию.
     */
    abstract protected function _setDefaultOrder();

    /**
     * Вернуть число примененных фильтров.
     *
     * @return int
     */
    public function countUsedFilters()
    {
        return $this->_countUsedFilters;
    }
}
