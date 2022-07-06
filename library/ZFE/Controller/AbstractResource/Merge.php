<?php

/*
 * ZFE – платформа для построения редакторских интерфейсов.
 */

/**
 * Стандартные обработчики удаления и восстановления записи.
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
     */
    public function mergeAction()
    {
        if (!in_array('merge', static::$_enableActions)) {
            $this->abort(404);
        }

        if (!(static::$_modelName)::isMergeable()) {
            $this->abort(404, (static::$_modelName)::$namePlural . ' не поддерживают стандартный механизм объединения.');
        }
    }

    public function searchDuplicatesAction()
    {
        if (!in_array('search-duplicates', static::$_enableActions)) {
            $this->abort(404);
        }

        if (!(static::$_modelName)::isMergeable()) {
            $this->abort(404, (static::$_modelName)::$namePlural . ' не поддерживают стандартный механизм объединения.');
        }

        $this->view->groups = (static::$_modelName)::getDuplicatesGroups();
        $this->view->title('Поиск и объединение дубликатов');
    }

    public function mergeHelperAction()
    {
        $modelName = static::$_modelName;

        $this->view->returnTo = $returnTo = $this->getParam('returnTo', $modelName::getIndexUrl());

        $ids = $this->getParam('ids');
        if (empty($ids)) {
            $this->abort(400, $modelName::$namePlural . ' для объединения не выбраны.');
        }
        if (!is_array($ids)) {
            if (!is_string($ids)) {
                $this->abort(400, 'Не корректные параметры запроса.');
            }
            $ids = explode(',', $ids);
        }
        $this->view->ids = $ids;

        $tableInstance = Doctrine_Core::getTable($modelName);

        /** @var ZFE_Query $q */
        $q = ZFE_Query::create()
            ->select('x.*')
            ->from($modelName . ' x INDEXBY x.id')
            ->whereIn('x.id', $ids)
        ;

        if ($tableInstance->hasRelation('Editor')) {
            $q->addFrom('x.Editor e')->addSelect('e.*');
        }

        if ($tableInstance->hasRelation('Creator')) {
            $q->addFrom('x.Creator c')->addSelect('c.*');
        }

        /** @var Doctrine_collection<static|AbstractRecord>|Array<static|AbstractRecord> */
        $items = $this->view->items = $modelName::calcWeightsEnrichQuery($q)->execute();

        $diff = [];
        $map = [];
        $serviceFields = $modelName::getServiceFields();
        $serviceFields[] = 'weight';
        $multiAutocompleteFields = array_keys($modelName::$multiAutocompleteCols);
        $multiCheckOrSelectCols = array_keys($modelName::$multiCheckOrSelectCols);
        foreach ($items as $item) {
            foreach ($item->toArray(false) as $field => $value) {
                if (
                    !in_array($field, $serviceFields) &&
                    !in_array($field, $multiAutocompleteFields) &&
                    !in_array($field, $multiCheckOrSelectCols)
                ) {
                    if (null !== $value) {
                        $map[$field][$item['id']] = $value;
                    }
                }
            }
        }
        foreach ($map as $field => $data) {
            // Тушение предупреждений плохо, но тут действительно вполне может быть и строка и массив
            // и это норма, а не исключение, и собачка лучше чем раздувать код
            $data = @array_diff($data, ['']);
            $map[$field] = array_unique($data, SORT_REGULAR);
            if (1 < count($map[$field])) {
                $diff[$field] = $map[$field];
            }
        }

        $first = $items->getFirst();
        if ($first && $first instanceof ZfeFiles_Manageable) {
            $schemas = $first->getFileSchemas();
            foreach ($schemas as $schema) {
                if ($schema->isHidden() || !$schema->getMultiple()) {
                    continue;
                }
                foreach ($items as $item) {
                    $map[$schema->getCode()][$item['id']] = $item->getAgents($schema);
                }
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

                ZFE_Notices::ok($msg);
            } catch (Throwable $ex) {
                ZFE_Utilities::popupException($ex);

                if ($this->_request->isXmlHttpRequest()) {
                    $this->_json(self::STATUS_FAIL, [], $ex->getMessage());
                }

                ZFE_Notices::ok($msg);
            }

            $this->redirect($returnTo);
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

        /** @var ZFE_Query $q */
        $q = ZFE_Query::create()
            ->select('x.*')
            ->from($modelName . ' x')
            ->orderBy($modelName::$titleField)
        ;

        if ($tableInstance->hasRelation('Editor')) {
            $q->addFrom('x.Editor e')->addSelect('e.*');
        }

        if ($tableInstance->hasRelation('Creator')) {
            $q->addFrom('x.Creator c')->addSelect('c.*');
        }

        // Фильтры
        $exclude = $this->getParam('exclude', []);
        if (!empty($exclude) && is_array($exclude)) {
            $q->andWhereNotIn('x.id', $exclude);
        }

        $term = $this->getParam('term');
        if (!empty($term)) {
            $q->addWhere('LOWER(' . $modelName::$titleField . ') LIKE LOWER(?)', '%' . $term . '%');
        }

        $ignoreMerged = $this->getParam('ignore_merged');
        if (!empty($ignoreMerged)) {
            $q->addWhere('x.merged = 0');
        }

        return $modelName::calcWeightsEnrichQuery($q);
    }
}
