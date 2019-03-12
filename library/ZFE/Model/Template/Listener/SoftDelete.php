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
     * @var boolean
     */
    protected $_allowSoftDelete = true;

    /**
     * Установит флаг: Разрешить мягкое удаление?
     *
     * @param bool $mode
     */
    public function allowSoftDelete($mode)
    {
        $this->_allowSoftDelete = $mode;
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
}
