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
     */
    public function saveHistory($mode)
    {
        $this->_saveHistory = $mode;
    }

    /**
     * Получить ID текущего пользователя.
     *
     * @return null|int
     */
    protected function _getCurrentUserId()
    {
        if  ( ! Zend_Registry::isRegistered('user')) {
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
     * Хук preInsert.
     *
     * @param Doctrine_Event $event
     */
    public function preInsert(Doctrine_Event $event)
    {
        if ($this->_saveHistory && History::$globalRealtimeWhiteHistory) {
            /** @var $invoker ZFE_Model_AbstractRecord */
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
    }

    /**
     * Хук postInsert.
     *
     * @param Doctrine_Event $event
     */
    public function postInsert(Doctrine_Event $event)
    {
        if ($this->_saveHistory && History::$globalRealtimeWhiteHistory) {
            /** @var $invoker ZFE_Model_AbstractRecord */
            $invoker = $event->getInvoker();

            $userId = $this->_getCurrentUserId();

            // Используем только первичный ключ по полю id
            $ids = $invoker->identifier('id');
            if (1 === count($ids) && array_key_exists('id', $ids)) {
                $history = new History();
                $history->table_name = $invoker->getTableName();
                $history->content_id = $invoker->id;
                $history->action_type = History::ACTION_TYPE_INSERT;
                $history->user_id = $userId;
                $history->datetime_action = new Doctrine_Expression('NOW()');
                $history->content_version = 1;
                $history->save();

                $relations = $invoker->getTable()->getRelations();
                foreach ($relations as $rel) {
                    if ($rel instanceof Doctrine_Relation_LocalKey) {
                        $relAlias = $rel->getAlias();
                        $relObj = $invoker->get($relAlias);
                        $relIds = $relObj->identifier('id');
                        $relId = 1 === count($relIds) && array_key_exists('id', $relIds)
                            ? $relObj->id
                            : null;

                        $history = new History();
                        $history->table_name = $relObj->getTableName();
                        $history->content_id = $relId;
                        $history->column_name = $relAlias;
                        $history->content_old = null;
                        $history->content_new = $invoker->id;
                        $history->action_type = History::ACTION_TYPE_LINK;
                        $history->user_id = $userId;
                        $history->datetime_action = new Doctrine_Expression('NOW()');
                        $history->content_version = null;
                        $history->save();
                    }
                }
            }
        }
    }

    /**
     * Хук preUpdate.
     *
     * @param Doctrine_Event $event
     */
    public function preUpdate(Doctrine_Event $event)
    {
        if ($this->_saveHistory && History::$globalRealtimeWhiteHistory) {
            /** @var $invoker ZFE_Model_AbstractRecord */
            $invoker = $event->getInvoker();

            if ($invoker->contains('editor_id')) {
                $invoker->editor_id = Zend_Auth::getInstance()->getIdentity()['id'];
            }

            if ($invoker->contains('datetime_edited')) {
                $invoker->datetime_edited = new Doctrine_Expression('NOW()');
            }

            if ($invoker->contains('version')) {
                $invoker->version = $invoker->version + 1;
            }
        }
    }

    /**
     * Хук postUpdate.
     *
     * @param Doctrine_Event $event
     */
    public function postUpdate(Doctrine_Event $event)
    {
        if ($this->_saveHistory && History::$globalRealtimeWhiteHistory) {
            /** @var $invoker ZFE_Model_AbstractRecord */
            $invoker = $event->getInvoker();
            $invokerModelName = get_class($invoker);
            $tableName = $invoker->getTableName();

            $userId = $this->_getCurrentUserId();

            // Используем только первичный ключ по полю id
            $ids = $invoker->identifier('id');
            $id = 1 === count($ids) && array_key_exists('id', $ids)
                ? $invoker->id
                : null;

            $version = $invoker->contains('version')
                ? $invoker->version
                : null;

            // Имеет смысл записывать что конкретно изменилось
            // только если можем записать id изменившейся записи.
            if ($id) {
                $oldData = $invoker->getModified(true, true);
                $newData = $invoker->getModified(false, true);

                $ignoreColumns = $invokerModelName::getServiceFields();
                $hiddenColumns = $invokerModelName::getHistoryHiddenFields();

                foreach ($newData as $column => $newValue) {
                    if (in_array($column, $ignoreColumns, true)) {
                        continue;
                    }

                    if (in_array($column, $hiddenColumns, true)) {
                        $newValue = null;
                    } elseif ($newValue instanceof Doctrine_Expression) {
                        $newValue = (string) $newValue;
                    }

                    $history = new History();
                    $history->table_name = $tableName;
                    $history->content_id = $id;
                    $history->column_name = $column;
                    $history->content_old = $oldData[$column];
                    $history->content_new = $newValue;
                    $history->action_type = History::ACTION_TYPE_UPDATE;
                    $history->user_id = $userId;
                    $history->datetime_action = new Doctrine_Expression('NOW()');
                    $history->content_version = $version;
                    $history->save();
                }

                foreach ($invoker->getPendingUnlinks() as $relAlias => $relIdsData) {
                    $relIds = array_keys($relIdsData);
                    foreach ($relIds as $relId) {
                        $history = new History();
                        $history->table_name = $tableName;
                        $history->content_id = $id;
                        $history->column_name = $relAlias;
                        $history->content_old = $relId;
                        $history->content_new = null;
                        $history->action_type = History::ACTION_TYPE_UNLINK;
                        $history->user_id = $userId;
                        $history->datetime_action = new Doctrine_Expression('NOW()');
                        $history->content_version = $version;
                        $history->save();
                    }
                }
                foreach ($invoker->getPendingLinks() as $relAlias => $relIdsData) {
                    $relIds = array_keys($relIdsData);
                    foreach ($relIds as $relId) {
                        $history = new History();
                        $history->table_name = $tableName;
                        $history->content_id = $id;
                        $history->column_name = $relAlias;
                        $history->content_old = null;
                        $history->content_new = $relId;
                        $history->action_type = History::ACTION_TYPE_LINK;
                        $history->user_id = $userId;
                        $history->datetime_action = new Doctrine_Expression('NOW()');
                        $history->content_version = $version;
                        $history->save();
                    }
                }
            } else {
                $history = new History();
                $history->table_name = $invokerModelName;
                $history->action_type = History::ACTION_TYPE_UPDATE;
                $history->user_id = $userId;
                $history->datetime_action = new Doctrine_Expression('NOW()');
                $history->content_version = $version;
                $history->save();
            }
        }
    }

    /**
     * Хук preDelete.
     *
     * @param Doctrine_Event $event
     */
    public function preDelete(Doctrine_Event $event)
    {
        if ($this->_saveHistory && History::$globalRealtimeWhiteHistory) {
            /** @var $invoker ZFE_Model_AbstractRecord */
            $invoker = $event->getInvoker();

            if ($invoker->contains('deleted')) {
                if ($invoker->contains('version')) {
                    ++$invoker->version;
                }
                $invoker->deleted = true;
                $invoker->hardSave();

                $event->skipOperation();
            }
        }
    }

    /**
     * Хук postDelete.
     *
     * @param Doctrine_Event $event
     */
    public function postDelete(Doctrine_Event $event)
    {
        if ($this->_saveHistory && History::$globalRealtimeWhiteHistory) {
            /** @var $invoker ZFE_Model_AbstractRecord */
            $invoker = $event->getInvoker();

            // Используем только первичный ключ по полю id
            $ids = $invoker->identifier('id');
            $id = 1 === count($ids) && array_key_exists('id', $ids)
                ? $invoker->id
                : null;

            $history = new History();
            $history->table_name = $invoker->getTableName();
            $history->content_id = $id;
            $history->action_type = History::ACTION_TYPE_DELETE;
            $history->user_id = $this->_getCurrentUserId();
            $history->datetime_action = new Doctrine_Expression('NOW()');
            if ($invoker->contains('version')) {
                $history->content_version = $invoker->version;
            }
            $history->save();
        }
    }

    /**
     * Хук preDqlSelect.
     *
     * @param Doctrine_Event $event
     */
    public function preDqlSelect(Doctrine_Event $event)
    {
        if ($this->_saveHistory) {
            $params = $event->getParams();
            $table = $params['component']['table'];
            $field = $params['alias'] . '.deleted';
            $query = $event->getQuery();

            if ($table->hasField('deleted') && ! $query->isHard()) {
                if (empty($params['component']['ref'])) {
                    $query->addWhere($field . ' IS NULL OR ' . $field . ' = 0');
                }
            }
        }
    }
}
