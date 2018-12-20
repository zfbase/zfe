<?php

/*
 * ZFE – платформа для построения редакторских интерфейсов.
 */

/**
 * Расширение модели для сохранения истории изменений записей.
 *
 * Шаблон предполагается подключать ко всем моделям, где нужно вести запись в историю
 * и сохранение в самих записях дат и авторов событий создания и последнего изменения,
 * а так же версии записи и флага удаления. Каждое из перечисленных выше полей может
 * отсутствовать в таблице.
 */
class ZFE_Model_Template_History extends Doctrine_Template
{
    /**
     * Слушатель обработчика истории.
     *
     * @var ZFE_Model_Template_Listener_History
     */
    protected $_listener;

    /**
     * setTableDefinition.
     */
    public function setTableDefinition()
    {
        if ('History' !== get_class($this->_invoker)) {
            $this->_listener = new ZFE_Model_Template_Listener_History();
            $this->addListener($this->_listener);
        }
    }

    /**
     * setUp.
     */
    public function setUp()
    {
        $config = Zend_Registry::get('config');

        if ($this->_table->hasColumn('editor_id')) {
            $this->hasOne($config->userModel . ' as Editor', [
                'local' => 'editor_id',
                'foreign' => 'id',
            ]);
        }

        if ($this->_table->hasColumn('creator_id')) {
            $this->hasOne($config->userModel . ' as Creator', [
                'local' => 'creator_id',
                'foreign' => 'id',
            ]);
        }
    }

    /**
     * Установить флаг записи и учета истории.
     *
     * @param bool $mode      историю писать и учитывать?
     * @param bool $noWarning не показывать предупреждение при невозможности выполнения
     */
    public function saveHistory($mode, $noWarning = false)
    {
        if ($this->_listener) {
            $this->_listener->saveHistory($mode);
        } elseif ( ! $noWarning) {
            trigger_error('Не возможно установить флаг записи и учета истории '
                        . 'при отключенном обработчике истории.', E_USER_WARNING);
        }
    }

    /**
     * Сохранить в обход механизма истории (INSERT или UPDATE).
     *
     * @param Doctrine_Connection $conn
     */
    public function hardSave(Doctrine_Connection $conn = null)
    {
        if ($this->_listener) {
            $this->_listener->saveHistory(false);
        }

        $result = $this->_invoker->save($conn);

        if ($this->_listener) {
            $this->_listener->saveHistory(true);
        }
    }

    /**
     * Удалить в обход механизма истории.
     *
     * @param Doctrine_Connection $conn
     *
     * @return bool true если успешно
     */
    public function hardDelete(Doctrine_Connection $conn = null)
    {
        if ($this->_listener) {
            $this->_listener->saveHistory(false);
        }

        $result = $this->_invoker->delete($conn);

        if ($this->_listener) {
            $this->_listener->saveHistory(true);
        }

        return $result;
    }

    /**
     * Восстановить удаленное.
     *
     * @param Doctrine_Connection $conn
     *
     * @throws ZFE_Model_Exception
     */
    public function undelete(Doctrine_Connection $conn = null)
    {
        if ($this->_table->hasColumn('deleted')) {
            $invoker = $this->_invoker;

            if ('0' === $invoker->deleted) {
                throw new ZFE_Model_Exception('Запись не может быть восстановлена, т.к. не удалена.');
            }

            $invoker->deleted = 0;
            if ($invoker->contains('version')) {
                ++$invoker->version;
            }
            $invoker->hardSave($conn);

            $history = new History();
            $history->table_name = $invoker->getTableName();
            $history->content_id = $invoker->id;
            $history->action_type = History::ACTION_TYPE_UNDELETE;
            $history->user_id = Zend_Auth::getInstance()->getIdentity()['id'];
            $history->datetime_action = new Doctrine_Expression('NOW()');
            if ($invoker->contains('version')) {
                $history->content_version = $invoker->version;
            }
            $history->save();
        } else {
            throw new ZFE_Model_Exception('Запись не поддерживает восстановление из удаленных.');
        }
    }

    /**
     * Получить копию записи по состоянию на определенную версию.
     *
     * @param int $version
     *
     * @throws ZFE_Model_Exception
     *
     * @return ZFE_Model_AbstractRecord
     */
    public function getStateForVersion($version)
    {
        $state = clone $this->_invoker;

        if ($version > $this->_invoker->version) {
            $modelName = get_class($this->_invoker);

            throw new ZFE_Model_Exception($modelName::$nameSingular . ' еще не достиг(-ла) версии ' . $version);
        }

        $history = ZFE_Query::create()
            ->select('x.*')
            ->from('History x')
            ->addWhere('x.table_name = ?', $this->_invoker->getTableName())
            ->addWhere('x.content_id = ?', $this->_invoker->id)
            ->andWhere('x.content_version > ?', $version)
            ->orderBy('x.content_version DESC')
            ->execute()
        ;

        foreach ($history as $action) { /** @var History $action */
            if ( ! empty($action->column_name) && $state->contains($action->column_name)) {
                $state->{$action->column_name} = $action->content_old;
                $state->version = $action->content_version;
            }
        }

        return $state;
    }

    /**
     * Получить редактора версии.
     *
     * @param int $version
     *
     * @return null|Editors
     */
    public function getEditorOfVersion($version)
    {
        $historyRow = Doctrine_Core::getTable('History')->findOneByTableNameAndContentIdAndContentVersion($this->_invoker->getTableName(), $this->_invoker->id, $version);
        return empty($historyRow) ? null : Editors::find($historyRow->user_id);
    }
}
