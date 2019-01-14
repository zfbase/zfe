<?php

/*
 * ZFE – платформа для построения редакторских интерфейсов.
 */

use Foolz\SphinxQL\SphinxQL;

/**
 * Конструктор запросов для Sphinx.
 *
 * @property SphinxQL $_query
 */
class ZFE_Searcher_QueryBuilder_Sphinx extends ZFE_Searcher_QueryBuilder_Abstract
{
    /**
     * Имя Sphinx-индекса.
     *
     * @var string
     */
    protected $_indexName;

    /**
     * Атрибуты, возвращаемые запросом к Sphinx.
     *
     * @var string
     */
    protected $_select = 'id';

    /**
     * Использовать расширенные фильтры?
     *
     * @var bool
     */
    protected $_useAdvancedFilters = true;

    /**
     * Число примененных расширенных фильтров.
     *
     * @var int
     */
    protected $_countUsedFilters = 0;

    /**
     * Установить имя Sphinx-индекса.
     *
     * @param string $name
     *
     * @return ZFE_Searcher_QueryBuilder_Sphinx
     */
    public function setIndexName($name)
    {
        $this->_indexName = $name;
        return $this;
    }

    /**
     * Получить имя Sphinx-индекса.
     *
     * @return string
     */
    public function getIndexName()
    {
        if ( ! $this->_indexName) {
            $modelName = $this->_modelName;
            $this->_indexName = $modelName::getSphinxIndexName();
        }

        return $this->_indexName;
    }

    /**
     * Установить правило использования расширенных фильтров.
     *
     * @param bool $use
     *
     * @return ZFE_Searcher_QueryBuilder_Sphinx
     */
    public function setUseAdvancedFilters($use)
    {
        $this->_useAdvancedFilters = (bool) $use;
        return $this;
    }

    /**
     * Получить значение правила использования расширенных фильтров.
     *
     * @return bool
     */
    public function isUseAdvancedFilters()
    {
        return $this->_useAdvancedFilters;
    }

    /**
     * Число примененных расширенных фильтров.
     *
     * @return int
     */
    public function countUsedFilters()
    {
        return $this->_countUsedFilters;
    }

    /**
     * {@inheritdoc}
     */
    protected function _create()
    {
        $this->_query = ZFE_Sphinx::query()
            ->select($this->_select)
            ->from($this->getIndexName())
        ;

        $page = $this->getParam('page');
        if ($page) {
            // Для возможности перехода на 51 страницу (при 20 результатах на странице)
            $this->_query->option('max_matches', $page * 50);
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function _filters()
    {
        $term = trim((string) $this->getParam('term'));
        if ($term) {
            $this->_query->match('*', $term);
        }

        if ($this->isUseAdvancedFilters()) {
            $this->_advancedFilters();
        }

        $deleted = $this->getParam('deleted');
        $this->_query->where('attr_deleted', (int) (bool) $deleted);
    }

    /**
     * Применить расширенные фильтры.
     */
    public function _advancedFilters()
    {
        $schema = ZFE_Sphinx::getRtIndexSchema($this->_indexName);
        foreach ($schema as $field => $type) {
            if ('id' === $field) {
                continue;
            }

            $fieldName = mb_substr($field, 5);
            switch ($type) {
                case 'rt_field':
                    $value = $this->getParam($field);
                    if ($value) {
                        $this->_query->match($field, $value);
                        $this->_countUsedFilters++;
                    }
                    break;
                case 'rt_attr_uint':
                case 'rt_attr_bool':
                case 'rt_attr_bigint':
                    $value = $this->getParam($fieldName);
                    if ($value) {
                        $this->_query->where($field, (int) $value);
                        $this->_countUsedFilters++;
                    }
                    break;
                case 'rt_attr_float':
                    $value = $this->getParam($fieldName);
                    if ($value) {
                        $this->_query->where($field, (float) $value);
                        $this->_countUsedFilters++;
                    }
                    break;
                case 'rt_attr_multi':
                case 'rt_attr_multi_64':
                    $items = $this->getParam($fieldName, []);
                    if (is_array($items) && ! empty($items)) {
                        $ids = array_map(function ($data) {
                            return (int) $data['id'];
                        }, $items);
                        $this->_query->where($field, 'IN', $ids);
                        $this->_countUsedFilters++;
                    }
                    break;
                case 'rt_attr_timestamp':
                    $value = strtotime($this->getParam($fieldName));
                    if ($value) {
                        $this->_query->where($field, $value);
                        $this->_countUsedFilters++;
                    }
                    break;
                case 'rt_attr_string':
                    $value = $this->getParam($fieldName);
                    if ($value) {
                        $this->_query->where($field, $value);
                        $this->_countUsedFilters++;
                    }
                    break;
                case 'rt_attr_json':
                    // @todo Добавить автоматический поиск по JSON-полям
                    break;
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function _order()
    {
        if ($this->hasFilters()) {
            parent::_order();
        } else {
            $this->_setEmptyFiltersOrder();
        }
    }

    /**
     * Применены ли фильтры?
     *
     * @return bool
     */
    public function hasFilters()
    {
        return 'WHERE attr_deleted = 0 ' !== $this->_query->compileWhere() || $this->_query->compileMatch();
    }

    /**
     * {@inheritdoc}
     */
    protected function _orderHelper($field, $direction = 'ASC')
    {
        $this->_query->orderBy('attr_' . $field, $direction);
    }

    /**
     * {@inheritdoc}
     */
    protected function _setDefaultOrder()
    {
    }

    protected function _setEmptyFiltersOrder()
    {
    }
}
