<?php

/*
 * ZFE – платформа для построения редакторских интерфейсов.
 */

/**
 * Стандартные обработчики поиска по списку записей.
 *
 * @category  ZFE
 */
trait ZFE_Controller_AbstractResource_Index
{
    /**
     * Форма поиска по записям модели.
     *
     * Запрос по форме обрабатывается методом $this->_getSearchQuery().
     *
     * @var string
     */
    protected static $_searchFormName = 'ZFE_Form_Search_Default';

    /**
     * Собрать базовый запрос для indexAction (без фильтрации).
     *
     * Вынесено в отдельную функцию для получения единого запроса при Doctrine и Sphinx
     *
     * @return ZFE_Query
     */
    protected function _getBaseSearchQueryDoctrine()
    {
        $modelName = static::$_modelName;
        $tableInstance = Doctrine_Core::getTable($modelName);

        $q = ZFE_Query::create()
            ->select('x.*')
            ->from($modelName . ' x')
        ;

        if ($tableInstance->hasRelation('Editor')) {
            $q->addFrom('x.Editor e')->addSelect('e.*');
        }

        if ($tableInstance->hasRelation('Creator')) {
            $q->addFrom('x.Creator c')->addSelect('c.*');
        }

        return $q;
    }

    /**
     * Поиск по записям модели.
     *
     * Реализует обработку введенных данных по форме $this->_searchForm.
     *
     * @return ZFE_Query
     */
    protected function _getSearchQuery()
    {
        $modelName = static::$_modelName;
        $q = $this->_getBaseSearchQueryDoctrine();

        $title = $this->getParam('title');
        if ( ! empty($title)) {
            $q->addWhere('LOWER(' . $modelName::$titleField . ') LIKE LOWER(?)', '%' . $title . '%');
        }

        $ids = $this->_getIdsParam();
        if ($ids) {
            $q->andWhereIn('x.id', $ids);
        }

        return $q;
    }

    /**
     * Добавить условия для отображения корзины.
     *
     * @param ZFE_Query $q
     *
     * @return ZFE_Query
     */
    protected function _addCaseForTrash(ZFE_Query $q)
    {
        // Условия учитываются, только если есть право на восстановление
        if (static::$_canRestore) {
            $ids = $this->_getIdsParam();
            if ($ids) {
                $q->setHard(true);
            } else {
                $deleted = (int) $this->getParam('deleted');
                if (1 === $deleted) {
                    $q->addWhere('x.deleted = 1')->setHard(true);
                }
                $this->view->deleted = $deleted;
            }
        }
        return $q;
    }

    /**
     * Добавить сортировку к запросу поиска по моделям
     *
     * @param ZFE_Query $q
     *
     * @return ZFE_Query
     */
    protected function _addOrderForSearchQuery(ZFE_Query $q)
    {
        $modelName = static::$_modelName;

        $order = $this->_request->getParam('order');
        if ( ! empty($order)) {
            $_ = strrpos($order, '_');
            $_2 = strtoupper(substr($order, $_ + 1));
            if ('ASC' === $_2 || 'DESC' === $_2) {
                $order = substr($order, 0, $_) . ' ' . substr($order, $_ + 1);
            }

            if ('title' === substr($order, 0, 5)) {
                $query = $modelName::$titleField . substr($order, 5);
            } else {
                $query = $order;
            }

            $q->orderBy($query);
            $this->view->order = $order;
        } elseif ( ! empty($modelName::$defaultOrder)) {
            $q->orderBy($modelName::$defaultOrder);
        }

        return $q;
    }

    /**
     * Обработка для переходов к предыдущему / следующему результату поиска.
     *
     * @param ZFE_Query $q
     */
    protected function _searchPages(ZFE_Query $q)
    {
        $resultNumber = $this->getParam('rn');
        if (empty($resultNumber)) {
            return;
        }

        $revertHash = $this->getParam('rh');
        if (empty($revertHash)) {
            return;
        }

        $q->offset($resultNumber - 1);
        $q->limit(1);
        $item = $q->fetchOne();

        $baseUrl = static::$_enableViewAction ? $item->getViewUrl() : $item->getEditUrl();
        $this->redirect($baseUrl . '?h=' . $revertHash . '&rn=' . $resultNumber);
    }

    /**
     * Сводная страница с перечнем объектов.
     *
     * @throws Zend_Controller_Action_Exception
     */
    public function indexAction()
    {
        if ( ! in_array('index', static::$_enableActions, true)) {
            throw new Zend_Controller_Action_Exception('Action "index" does not exist', 404);
        }

        $this->_helper->postToGet();

        if ( ! empty(static::$_searchFormName)) {
            $searchForm = new static::$_searchFormName();
            if (1 === $this->getParam('deleted')) {
                $searchForm->addElement('hidden', 'deleted', ['value' => 1]);
            }
            $searchForm->populate($this->getAllParams());
            $this->view->searchForm = $searchForm;
        }

        $q = $this->_getSearchQuery();
        $q = $this->_addCaseForTrash($q);
        $q = $this->_addOrderForSearchQuery($q);

        $this->_searchPages($q);

        $this->view->items = ZFE_Paginator::execute($q);
    }

    /**
     * Возвращает параметр ids.
     * Параметр может содержать числа, разделенные запятыми, которые преобразуются в массив.
     */
    protected function _getIdsParam()
    {
        return array_filter(array_map('trim', explode(',', $this->getParam('ids'))), 'is_numeric');
    }
}
