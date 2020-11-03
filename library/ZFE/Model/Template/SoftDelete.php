<?php

/*
 * ZFE – платформа для построения редакторских интерфейсов.
 */

/**
 * Расширение модели реализующее мягкое удаление.
 */
class ZFE_Model_Template_SoftDelete extends Doctrine_Template
{
    /**
     * Слушатель обработчика истории.
     *
     * @var ZFE_Model_Template_Listener_SoftDelete
     */
    protected $_listener;

    /**
     * {@inheritdoc}
     */
    public function setTableDefinition()
    {
        if ('History' !== get_class($this->_invoker)) {
            $this->_listener = new ZFE_Model_Template_Listener_SoftDelete();
            $this->addListener($this->_listener);
        }
    }

    /**
     * Установит флаг: Разрешить мягкое удаление?
     *
     * @param bool|null $mode
     */
    public function allowSoftDelete($mode = null)
    {
        return $this->_listener->allowSoftDelete($mode);
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
        $lastSoftDelete = null;

        if ($this->_listener) {
            $lastSoftDelete = $this->_listener->allowSoftDelete();
            $this->_listener->allowSoftDelete(false);
        }

        $result = $this->_invoker->delete($conn);

        if ($this->_listener) {
            $this->_listener->allowSoftDelete($lastSoftDelete);
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

            $invoker->preUndelete();
            $invoker->hardSave($conn);
            $invoker->postUndelete();

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
     * Хук, выполняющийся перед восстановлением.
     */
    public function preUndelete()
    {
    }

    /**
     * Хук, выполняющийся после восстановления.
     */
    public function postUndelete()
    {
    }
}
