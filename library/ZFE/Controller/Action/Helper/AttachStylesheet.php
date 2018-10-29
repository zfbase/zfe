<?php

/*
 * ZFE – платформа для построения редакторских интерфейсов.
 */

/**
 * Помощник подключения таблиц стилей.
 *
 * @deprecated Используй webpack для сборки всех JS и CSS
 *
 * @category  ZFE
 */
class ZFE_Controller_Action_Helper_AttachStylesheet extends Zend_Controller_Action_Helper_Abstract
{
    /**
     * Загрузить на страницу таблицу стилей.
     *
     * Если атрибут не указан, загрузится файл по умолчанию для настоящего экшена.
     *
     * @deprecated Используй webpack для сборки всех JS и CSS
     *
     * @example $this->_helper->AttachStylesheet();  // загрузить CSS: /css/current_controller-current_action.css?v=version
     * @example $this->_helper->AttachStylesheet('/css/caruseli.css');  // загрузить CSS: /css/caruseli.css?v=version
     *
     * @param string $style
     */
    public function direct($style = null)
    {
        $request = $this->getRequest();
        if ( ! is_string($style)) {
            $controller = $request->getControllerName();
            $action = $request->getActionName();
            $style = "/css/{$controller}-{$action}.css";
        }

        $view = $this->_actionController->view;
        $style .= '?v=' . $view->version;
        $view->headLink()->appendStylesheet($style);
    }
}
