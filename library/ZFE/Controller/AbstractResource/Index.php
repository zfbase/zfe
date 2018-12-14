<?php

/*
 * ZFE – платформа для построения редакторских интерфейсов.
 */

/**
 * Стандартные обработчики поиска по списку записей.
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
        $tableInstance = Doctrine_Core::getTable(static::$_modelName);

        $q = ZFE_Query::create()
            ->select('x.*')
            ->from(static::$_modelName . ' x')
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
        $q = $this->_getBaseSearchQueryDoctrine();

        $title = $this->getParam('title');
        if ( ! empty($title)) {
            $q->addWhere('LOWER(' . (static::$_modelName)::$titleField . ') LIKE LOWER(?)', '%' . $title . '%');
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
        $order = $this->_request->getParam('order');
        if ( ! empty($order)) {
            $pos = mb_strrpos($order, '_');
            $field = mb_substr($order, 0, $pos);
            $direction = mb_strtoupper(mb_substr($order, $pos + 1));
            if (in_array($direction, ['ASC', 'DESC'], true)) {
                $order = $field . ' ' . $direction;
            }

            if ('title' === $field) {
                $query = (static::$_modelName)::$titleField . ' ' . mb_substr($order, 5);
            } else {
                $query = $order;
            }

            $q->orderBy($query);
            $this->view->order = $order;
        } elseif ( ! empty((static::$_modelName)::$defaultOrder)) {
            $q->orderBy((static::$_modelName)::$defaultOrder);
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
     */
    public function indexAction()
    {
        if ( ! in_array('index', static::$_enableActions, true)) {
            $this->abort(404);
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
     *
     * @return array
     */
    protected function _getIdsParam()
    {
        return array_filter(array_map('trim', explode(',', $this->getParam('ids'))), 'is_numeric');
    }
}
