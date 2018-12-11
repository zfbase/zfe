<?php

/*
 * ZFE – платформа для построения редакторских интерфейсов.
 */

/**
 * Стандартные обработчики просмотра записи.
 */
trait ZFE_Controller_AbstractResource_View
{
    protected static $_enableViewAction = false;

    public function viewAction()
    {
        if ( ! static::$_enableViewAction) {
            throw new Zend_Controller_Action_Exception('Action "view" does not exist', 404);
        }

        /** @var $item AbstractRecord */
        $item = (static::$_modelName)::find($this->getParam('id'));
        if (empty($item)) {
            throw new Zend_Controller_Action_Exception((static::$_modelName)::decline('%s не найден.', '%s не найдена.', '%s не найдено.'), 404);
        }
        $this->view->item = $item;
    }
}
