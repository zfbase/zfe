<?php

/*
 * ZFE – платформа для построения редакторских интерфейсов.
 */

/**
 * Слушатель событий CRUD в моделях Doctrine, реализующий сохранение истории.
 *
 * Хуки добавления, изменения и удаления записи предшествующие действию,
 * модифицируют сохраняемые значения, а выполняющиеся после действия записывают его в историю.
 */
class ZFE_Model_Template_Listener_History extends Doctrine_Record_Listener
{
    /**
     * Фиксировать событие в истории?
     *
     * @var bool
     */
    protected $_saveHistory = true;

    /**
     * Установить флаг: Фиксировать событие в истории?
     *
     * @param bool $mode
     *
     * @return bool
     */
    public function saveHistory($mode = null)
    {
        if (null === $mode) {
            return $this->_saveHistory;
        }

        $this->_saveHistory = $mode;
    }

    /**
     * Получить ID текущего пользователя.
     *
     * @return null|int
     */
    protected function _getCurrentUserId()
    {
        if (!Zend_Registry::isRegistered('user')) {
            return null;
        }

        $user = Zend_Registry::get('user');
        if ($user && $user->data instanceof Editors) {
            $userId = $user->data->id;
        } else {
            $userId = null;
        }

        return $userId;
    }

    /**
     * Используется в хуках для проверки, сохранять ли историю
     * Проверяет поле в модели и глобальную настройку.
     *
     * @return bool
     */
    protected function _historyEnabled()
    {
        return $this->_saveHistory && History::$globalRealtimeWhiteHistory;
    }

    /**
     * Получить первичный ключ записи, если он состоит из одного поля 'id'.
     *
     * @param $record Doctrine_Record
     *
     * @return null|int
     */
    protected static function _getRecordSingleColumnId($record)
    {
        $ids = $record->identifier('id');
        if (count($ids) === 1 && array_key_exists('id', $ids)) {
            return $record->id;
        }
        return null;
    }

    /**
     * Хук preInsert.
     *
     * @param Doctrine_Event $event
     */
    public function preInsert(Doctrine_Event $event)
    {
        /** @var ZFE_Model_AbstractRecord $invoker */
        $invoker = $event->getInvoker();

        $userId = $this->_getCurrentUserId();
        $datetime = new Doctrine_Expression('NOW()');

        if ($invoker->contains('creator_id')) {
            $invoker->creator_id = $userId;
        }

        if ($invoker->contains('datetime_created')) {
            $invoker->datetime_created = $datetime;
        }

        if ($invoker->contains('editor_id')) {
            $invoker->editor_id = $userId;
        }

        if ($invoker->contains('datetime_edited')) {
            $invoker->datetime_edited = $datetime;
        }

        if ($invoker->contains('version')) {
            $invoker->version = 1;
        }
    }

    /**
     * Хук postInsert.
     *
     * @param Doctrine_Event $event
     */
    public function postInsert(Doctrine_Event $event)
    {
        if (!$this->_historyEnabled()) {
            return;
        }

        /** @var ZFE_Model_AbstractRecord $invoker */
        $invoker = $event->getInvoker();

        $userId = $this->_getCurrentUserId();
        $id = static::_getRecordSingleColumnId($invoker);
        if ($id === null) {
            return;
        }

        $historyRows = [
            [
                'table_name' => $invoker->getTableName(),
                'content_id' => $invoker->id,
                'action_type' => History::ACTION_TYPE_INSERT,
                'user_id' => $userId,
                'content_version' => 1,
            ],
        ];

        $relations = $invoker->getTable()->getRelations();
        foreach ($relations as $rel) {
            if (!$rel instanceof Doctrine_Relation_LocalKey) {
                continue;
            }
            $relAlias = $rel->getAlias();
            $relObj = $invoker->get($relAlias);
            $relId = static::_getRecordSingleColumnId($relObj);
            if ($relId === null) {
                continue;
            }
            $historyRows[] = [
                'table_name' => $relObj->getTableName(),
                'content_id' => $relObj->id,
                'column_name' => $relAlias,
                'content_old' => null,
                'content_new' => $invoker->id,
                'action_type' => History::ACTION_TYPE_LINK,
                'user_id' => $userId,
                'content_version' => null,
            ];
        }
        $this->writeHistoryRows($historyRows);
    }

    /**
     * Хук preUpdate.
     *
     * @param Doctrine_Event $event
     */
    public function preUpdate(Doctrine_Event $event)
    {
        /** @var ZFE_Model_AbstractRecord $invoker */
        $invoker = $event->getInvoker();

        if ($invoker->contains('editor_id')) {
            $invoker->editor_id = $this->_getCurrentUserId();
        }

        if ($invoker->contains('datetime_edited')) {
            $invoker->datetime_edited = new Doctrine_Expression('NOW()');
        }

        if ($invoker->contains('version')) {
            $invoker->version = $invoker->version + 1;
        }
    }

    /**
     * Хук postUpdate.
     *
     * @param Doctrine_Event $event
     */
    public function postUpdate(Doctrine_Event $event)
    {
        if (!$this->_historyEnabled()) {
            return;
        }

        /** @var ZFE_Model_AbstractRecord $invoker */
        $invoker = $event->getInvoker();
        $invokerModelName = get_class($invoker);
        $tableName = $invoker->getTableName();

        $userId = $this->_getCurrentUserId();

        $historyRows = [];

        // Используем только первичный ключ по полю id
        $id = static::_getRecordSingleColumnId($invoker);

        $version = $invoker->contains('version')
            ? $invoker->version
            : null;

        // Имеет смысл записывать что конкретно изменилось
        // только если можем записать id изменившейся записи.
        if ($id === null) {
            $historyRows[] = [
                'table_name' => $invokerModelName,
                'action_type' => History::ACTION_TYPE_UPDATE,
                'user_id' => $userId,
                'datetime_action' => new Doctrine_Expression('NOW()'),
                'content_version' => $version,
            ];
        } else {
            $oldData = $invoker->getModified(true, true);
            $newData = $invoker->getModified(false, true);

            $ignoreColumns = $invokerModelName::getServiceFields();
            $hiddenColumns = $invokerModelName::getHistoryHiddenFields();

            foreach ($newData as $column => $newValue) {
                if (in_array($column, $ignoreColumns)) {
                    continue;
                }

                if (in_array($column, $hiddenColumns)) {
                    $newValue = null;
                } elseif ($newValue instanceof Doctrine_Expression) {
                    $newValue = (string) $newValue;
                }

                $historyRows[] = [
                    'table_name' => $tableName,
                    'content_id' => $id,
                    'column_name' => $column,
                    'content_old' => $oldData[$column],
                    'content_new' => $newValue,
                    'action_type' => History::ACTION_TYPE_UPDATE,
                    'user_id' => $userId,
                    'content_version' => $version,
                ];
            }

            // Unlinks
            foreach ($invoker->getPendingUnlinks() as $relAlias => $relIdsData) {
                $relIds = array_keys($relIdsData);
                foreach ($relIds as $relId) {
                    $historyRows[] = [
                        'table_name' => $tableName,
                        'content_id' => $id,
                        'column_name' => $relAlias,
                        'content_old' => $relId,
                        'content_new' => null,
                        'action_type' => History::ACTION_TYPE_UNLINK,
                        'user_id' => $userId,
                        'content_version' => $version,
                    ];
                }
            }

            // Links
            foreach ($invoker->getPendingLinks() as $relAlias => $relIdsData) {
                $relIds = array_keys($relIdsData);
                foreach ($relIds as $relId) {
                    $historyRows[] = [
                        'table_name' => $tableName,
                        'content_id' => $id,
                        'column_name' => $relAlias,
                        'content_old' => null,
                        'content_new' => $relId,
                        'action_type' => History::ACTION_TYPE_LINK,
                        'user_id' => $userId,
                        'content_version' => $version,
                    ];
                }
            }
        }
        $this->writeHistoryRows($historyRows);
    }

    /**
     * Хук postDelete.
     *
     * @param Doctrine_Event $event
     */
    public function postDelete(Doctrine_Event $event)
    {
        if (!$this->_historyEnabled()) {
            return;
        }

        /** @var ZFE_Model_AbstractRecord $invoker */
        $invoker = $event->getInvoker();

        $id = static::_getRecordSingleColumnId($invoker);

        $historyRow = [
            'table_name' => $invoker->getTableName(),
            'content_id' => $id,
            'action_type' => History::ACTION_TYPE_DELETE,
            'user_id' => $this->_getCurrentUserId(),
        ];
        if ($invoker->contains('version')) {
            $historyRow['content_version'] = $invoker->version;
        }
        $this->writeHistoryRows([$historyRow]);
    }

    public function writeHistoryRows($rows, $event= '', $record = null)
    {
        $conn = Doctrine_Manager::connection();
        $conn->beginTransaction();
        foreach ($rows as $row) {
            $row['datetime_action'] = new Doctrine_Expression('NOW()');
            $item = new History();
            $item->fromArray($row);
            $item->save($conn);
        }
        $conn->commit();
    }
}
