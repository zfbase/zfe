<?php

/*
 * ZFE – платформа для построения редакторских интерфейсов.
 */

/**
 * Слушатель событий CRUD в моделях Doctrine, реализующий мягкое удаление.
 */
class ZFE_Model_Template_Listener_SoftDelete extends Doctrine_Record_Listener
{
    /**
     * Разрешить мягкое удаление?
     *
     * @var bool
     */
    protected $_allowSoftDelete = true;

    /**
     * Установит флаг: Разрешить мягкое удаление?
     *
     * @param bool $mode
     */
    public function allowSoftDelete($mode = null)
    {
        if ($mode !== null) {
            $this->_allowSoftDelete = $mode;
        }

        return $this->_allowSoftDelete;
    }

    /**
     * Хук preDelete.
     *
     * @param Doctrine_Event $event
     */
    public function preDelete(Doctrine_Event $event)
    {
        if ($this->_allowSoftDelete) {
            /** @var ZFE_Model_AbstractRecord $invoker */
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
     * Хук preDqlSelect.
     *
     * @param Doctrine_Event $event
     */
    public function preDqlSelect(Doctrine_Event $event)
    {
        if ($this->_allowSoftDelete) {
            $params = $event->getParams();
            $table = $params['component']['table'];
            $field = $params['alias'] . '.deleted';
            $query = $event->getQuery();

            if ($table->hasField('deleted') && !$query->isHard()) {
                if (empty($params['component']['ref'])) {
                    $query->addWhere($field . ' IS NULL OR ' . $field . ' = 0');
                }
            }
        }
    }

    /** 
     * Хук preDqlDelete.
     *
     * @param Doctrine_Event $event
     */
    public function preDqlDelete(Doctrine_Event $event)
    {
        $query = $event->getQuery();
        $invoker = $event->getInvoker();
        if ($this->_allowSoftDelete && $invoker->contains('deleted') && !$query->isHard()) {
            $query->update()->set('deleted', '?', 1);
        }
    }
}
