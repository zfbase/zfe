<?php

/*
 * ZFE – платформа для построения редакторских интерфейсов.
 */

use Foolz\SphinxQL\SphinxQL;

/**
 * Основной базовый контроллер приложения, для поддержки работы со Sphinx.
 *
 * @deprecated 1.30.0
 */
abstract class ZFE_Controller_AbstractResourceSphinx extends Controller_AbstractResource
{
    /**
     * Атрибут, возвращаемый запросом к Sphinx.
     *
     * @var string
     */
    protected static $_sphinxSelect = 'id';

    /**
     * Сводная страница с перечнем объектов.
     */
    public function indexAction()
    {
        $this->_helper->postToGet();

        if (!empty(static::$_searchFormName)) {
            $searchForm = new static::$_searchFormName();
            if (1 == $this->getParam('deleted')) {
                $searchForm->addElement('hidden', 'deleted', ['value' => 1]);
            }
            $searchForm->populate($this->getAllParams());
            $this->view->searchForm = $searchForm;
        }

        $ids = $this->_getIdsParam();
        if (!$ids) {
            $sphinxQuery = $this->_getSearchQuerySphinx();
            $sphinxQuery = $this->_addSphinxCaseForTrash($sphinxQuery);
            $sphinxQuery = $this->_addOrderForSearchQuerySphinx($sphinxQuery);
            $this->_searchPagesSphinx($sphinxQuery);
            $ids = ZFE_Sphinx::fetchIds(ZFE_Sphinx_Paginator::execute($sphinxQuery));
        } else {
            ZFE_Sphinx_Paginator::execute(null, [], count($ids));
        }

        if ($ids) {
            $doctrineQuery = $this->_getBaseSearchQueryDoctrine();
            $doctrineQuery->andWhereIn('x.id', $ids);
            $doctrineQuery->orderByField('x.id', $ids);
            $doctrineQuery->setHard(true);
            $this->view->items = $doctrineQuery->execute();
        } else {
            $this->view->items = [];
        }

        trigger_error('Класс ZFE_Controller_AbstractResourceSphinx устарел. '
                    . 'Используйте современное решение с ZFE_Searcher_Sphinx. ', E_USER_DEPRECATED);
    }

    /**
     * Обработка для переходов к предыдущему / следующему результату поиска.
     *
     * @param SphinxQL $q
     */
    protected function _searchPagesSphinx(SphinxQL $q)
    {
        $resultNumber = $this->getParam('rn');
        if (empty($resultNumber)) {
            return;
        }

        $revertHash = $this->getParam('rh');
        if (empty($revertHash)) {
            return;
        }
        $q->option('max_matches', $resultNumber + 1);
        $q->offset($resultNumber - 1);
        $q->limit(1);
        $item = ZFE_Sphinx::fetchOne($q, static::$_modelName);

        $baseUrl = static::$_enableViewAction ? $item->getViewUrl() : $item->getEditUrl();
        $this->redirect($baseUrl . '?h=' . $revertHash . '&rn=' . $resultNumber);
    }

    /**
     * Вернуть простой запрос по данным из request.
     *
     * @return SphinxQL
     */
    protected function _getSearchQuerySphinx()
    {
        $config = Zend_Registry::get('config');
        $modelName = static::$_modelName;
        $q = ZFE_Sphinx::query()
            ->select(static::$_sphinxSelect)
            ->from($modelName::getSphinxIndexName())
            ->limit($config->view->perpage)
        ;

        $allFullTextColsText = $this->getParam('term');
        if ($allFullTextColsText) {
            $q->match('*', $allFullTextColsText);
        }

        $page = (int) $this->getParam('page');
        if ($page) {
            // Для возможности перехода на 51 страницу (при 20 результатах на странице)
            $q->option('max_matches', $page
                * ZFE_Sphinx_Paginator::getInstance()->getItemsPerPage());
        }

        return $q;
    }

    /**
     * Опциональная надстройка над запросом для автоматического поиска
     * фильтров из конфига в параметрах запроса.
     *
     * @param SphinxQL $q
     *
     * @return SphinxQL
     */
    protected function _addAdvancedFiltersForSearchQuerySphinx(SphinxQL $q)
    {
        $modelName = static::$_modelName;
        $sphinxIndex = $modelName::getSphinxIndexName();
        $schema = ZFE_Sphinx::getRtIndexSchema($sphinxIndex);
        foreach ($schema as $field => $type) {
            switch ($type) {
                case 'rt_field':
                    $value = $this->getParam($field);
                    if ($value) {
                        $q->match($field, $value);
                    }
                    break;
                case 'rt_attr_multi':
                    $items = $this->getParam(mb_substr($field, 5), []);
                    if (is_array($items) && !empty($items)) {
                        $ids = array_map(function ($data) {
                            return (int) $data['id'];
                        }, $items);
                        $q->where($field, 'IN', $ids);
                    }
                    break;
                default:
                    $value = $this->getParam(mb_substr($field, 5));
                    if ($value) {
                        $q->where($field, (int) $value);
                    }
            }
        }
        return $q;
    }

    /**
     * Добавить сортировку к запросу поиска по моделям
     *
     * @param SphinxQL $q
     * @param bool     $reset скинуть ранее определенный порядок?
     *
     * @return SphinxQL
     */
    protected function _addOrderForSearchQuerySphinx(SphinxQL $q, $reset = false)
    {
        $order = $this->_request->getParam('order');
        if (!empty($order)) {
            $pos = mb_strrpos($order, '_');
            $field = mb_substr($order, 0, $pos);
            $direction = mb_strtoupper(mb_substr($order, $pos + 1));
            if ('ASC' === $direction || 'DESC' === $direction) {
                $reset && $q->resetOrderBy();
                $q = $this->_sphinxQueryOrder($q, $field, $direction);
            }
        }
        return $q;
    }

    /**
     * Помощник для сортировки.
     *
     * @param SphinxQL $q
     * @param string   $field     поле для сортировки
     * @param string   $direction направление сортировки ASC / DESC
     *
     * @return SphinxQL
     */
    protected function _sphinxQueryOrder(SphinxQL $q, $field, $direction = 'ASC')
    {
        $q->orderBy('attr_' . $field, $direction);
        return $q;
    }

    /**
     * Добавить условия для отображения корзины.
     *
     * @param SphinxQL $q
     *
     * @return SphinxQL
     */
    protected function _addSphinxCaseForTrash(SphinxQL $q)
    {
        $this->view->deleted = $deleted = $this->getParam('deleted');
        $q->where('attr_deleted', empty($deleted) ? 0 : 1);
        return $q;
    }
}
