<?php

/*
 * ZFE – платформа для построения редакторских интерфейсов.
 */

/**
 * Стандартные обработчики удаления и восстановления записи.
 *
 * @category  ZFE
 */
trait ZFE_Controller_AbstractResource_Merge
{
    /**
     * Возможность объединения записей.
     *
     * @var string
     */
    protected static $_canMerge = true;

    /**
     * Страница объединения записей.
     *
     * @throws Zend_Controller_Action_Exception
     * @throws Exception
     */
    public function mergeAction()
    {
        $modelName = static::$_modelName;

        if ( ! in_array('merge', static::$_enableActions, true)) {
            throw new Zend_Controller_Action_Exception('Action "merge" does not exist', 404);
        }

        if ( ! $modelName::isMergeable()) {
            throw new Zend_Controller_Action_Exception($modelName::$namePlural . ' не поддерживают стандартный механизм объединения.', 404);
        }
    }

    public function searchDuplicatesAction()
    {
        $modelName = static::$_modelName;

        if ( ! in_array('search-duplicates', static::$_enableActions, true)) {
            throw new Zend_Controller_Action_Exception('Action "search-duplicates" does not exist', 404);
        }

        if ( ! $modelName::isMergeable()) {
            throw new Zend_Controller_Action_Exception($modelName::$namePlural . ' не поддерживают стандартный механизм объединения.', 404);
        }

        $this->view->groups = $modelName::getDuplicatesGroups();
    }

    public function mergeHelperAction()
    {
        $modelName = static::$_modelName;

        $this->view->returnTo = $returnTo = $this->getParam('returnTo', $modelName::getIndexUrl());

        $ids = $this->getParam('ids');
        if (empty($ids)) {
            throw new Zend_Controller_Action_Exception($modelName::$namePlural . ' для объединения не выбраны.', 400);
        }
        if ( ! is_array($ids)) {
            if ( ! is_string($ids)) {
                throw new Zend_Controller_Action_Exception('Не корректные параметры запроса.', 400);
            }
            $ids = explode(',', $ids);
        }
        $this->view->ids = $ids;

        $tableInstance = Doctrine_Core::getTable($modelName);

        /** @var $q ZFE_Query */
        $q = ZFE_Query::create()
            ->select('x.*')
            ->from($modelName . ' x INDEXBY x.id')
            ->whereIn('x.id', $ids)
            ->groupBy('x.id')
        ;

        if ($tableInstance->hasRelation('Editor')) {
            $q->addFrom('x.Editor e')->addSelect('e.*');
        }

        if ($tableInstance->hasRelation('Creator')) {
            $q->addFrom('x.Creator c')->addSelect('c.*');
        }

        // Подсчет веса
        $weights = [];
        $relations = $tableInstance->getRelations();
        foreach ($relations as $relation) {
            if ($relation instanceof Doctrine_Relation_ForeignKey) {
                $class = $relation->getAlias();
                $q->leftJoin('x.' . $class . ' rel_' . $class);

                $pk = $relation->getTable()->getIdentifier();
                $uniq = "rel_${class}." . (is_array($pk) ? implode(", rel_${class}.", $pk) : $pk);
                $weights[] = "COUNT(DISTINCT ${uniq})";
            }
        }
        if ( ! empty($weights)) {
            $q->addSelect('(' . implode(' + ', $weights) . ') weight');
        } else {
            $q->addSelect('0 weight');
        }

        $this->view->items = $items = $q->execute();

        $diff = [];
        $map = [];
        $serviceFields = $modelName::getServiceFields();
        $serviceFields[] = 'weight';
        foreach ($items as $item) {
            foreach ($item->toArray(false) as $field => $value) {
                if ( ! in_array($field, $serviceFields, true)) {
                    if (null !== $value) {
                        $map[$field][$item['id']] = $value;
                    }
                }
            }
        }
        foreach ($map as $field => $data) {
            $data = array_diff($data, ['']);
            $map[$field] = array_unique($data);
            if (1 < count($map[$field])) {
                $diff[$field] = $map[$field];
            }
        }

        $fieldsMap = $this->getParam('field', []);
        $inaccurate = array_diff(array_keys($diff), array_keys($fieldsMap));
        if (empty($inaccurate)) {
            try {
                $master = $modelName::advancedMerge($items, $fieldsMap);

                $msg = $modelName::decline('%s успешно объединен.', '%s успешно объединена.', '%s успешно объединено.')
                     . ' <a href="' . $master->getEditUrl() . '">Показать</a>';

                if ($this->_request->isXmlHttpRequest()) {
                    $this->_json(self::STATUS_SUCCESS, [], $msg);
                }

                $this->_helper->Notices->ok($msg);
            } catch (Exception $ex) {
                if ($this->_request->isXmlHttpRequest()) {
                    $this->_json(self::STATUS_FAIL, [], $ex->getMessage());
                }

                $this->_helper->Notices->ok($msg);
            }

            $this->_redirect($returnTo);
        }
    }

    /**
     * Результаты поиска для страницы объединения записей.
     */
    public function mergeSearchAction()
    {
        $q = $this->_getMergeSearchQuery();

        $paginator = ZFE_Paginator::getInstance();
        $this->view->items = $paginator->execute($q);

        $this->_helper->layout()->disableLayout();

        $downHash = $this->getParam('h');
        $this->view->downHash = $downHash ? '?h' . $downHash : '';
    }

    /**
     * Построить запрос для поиска метода объединения.
     *
     * @return ZFE_Query
     */
    protected function _getMergeSearchQuery()
    {
        $modelName = static::$_modelName;
        $tableInstance = Doctrine_Core::getTable($modelName);

        /** @var $q ZFE_Query */
        $q = ZFE_Query::create()
            ->select('x.*')
            ->from($modelName . ' x')
            ->orderBy($modelName::$titleField)
            ->groupBy('x.id')
        ;

        if ($tableInstance->hasRelation('Editor')) {
            $q->addFrom('x.Editor e')->addSelect('e.*');
        }

        if ($tableInstance->hasRelation('Creator')) {
            $q->addFrom('x.Creator c')->addSelect('c.*');
        }

        // Подсчет веса
        $weights = [];
        $relations = $tableInstance->getRelations();
        if (count($relations) < 5) {  // Для часто используемых словарей необходимо иначе считать вес
            foreach ($relations as $relation) {
                if ($relation instanceof Doctrine_Relation_ForeignKey) {
                    $class = $relation->getAlias();
                    $q->leftJoin('x.' . $class . ' rel_' . $class);

                    $pk = $relation->getTable()->getIdentifier();
                    $uniq = "rel_${class}." . (is_array($pk) ? implode(", rel_${class}.", $pk) : $pk);
                    $weights[] = "COUNT(DISTINCT ${uniq})";
                }
            }

            if ( ! empty($weights)) {
                $q->addSelect('(' . implode(' + ', $weights) . ') weight');
            } else {
                $q->addSelect('0 weight');
            }
        } else {
            $q->addSelect('"–" weight');
        }

        // Фильтры
        $exclude = $this->getParam('exclude', []);
        if ( ! empty($exclude) && is_array($exclude)) {
            $q->andWhereNotIn('x.id', $exclude);
        }

        $term = $this->getParam('term');
        if ( ! empty($term)) {
            $q->addWhere('LOWER(' . $modelName::$titleField . ') LIKE LOWER(?)', '%' . $term . '%');
        }

        $ignoreMerged = $this->getParam('ignore_merged');
        if ( ! empty($ignoreMerged)) {
            $q->addWhere('x.merged = 0');
        }

        return $q;
    }
}
