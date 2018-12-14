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
            $this->abort(404);
        }

        /** @var $item AbstractRecord */
        $item = (static::$_modelName)::find($this->getParam('id'));
        if (empty($item)) {
            $this->abort(404, (static::$_modelName)::decline('%s не найден.', '%s не найдена.', '%s не найдено.'));
        }
        $this->view->item = $item;
    }
}
