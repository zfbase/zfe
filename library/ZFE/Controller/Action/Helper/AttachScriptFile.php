<?php

/*
 * ZFE – платформа для построения редакторских интерфейсов.
 */

/**
 * Помощник подключения JS-файлов.
 *
 * @deprecated Используй webpack для сборки всех JS и CSS
 *
 * @category  ZFE
 */
class ZFE_Controller_Action_Helper_AttachScriptFile extends Zend_Controller_Action_Helper_Abstract
{
    /**
     * Загрузить на страницу JS-файл.
     *
     * Если атрибут не указан, загрузится скрипт по умолчанию для настоящего экшена.
     *
     * @deprecated Используй webpack для сборки всех JS и CSS
     *
     * @example $this->_helper->AttachScriptFile();  // загрузить JS: /js/current_controller-current_action.js?v=version
     * @example $this->_helper->AttachScriptFile('/js/caruseli.js');  // загрузить JS: /js/caruseli.js?v=version
     *
     * @param string $script
     */
    public function direct($script = null)
    {
        $request = $this->getRequest();
        if ( ! is_string($script)) {
            $controller = $request->getControllerName();
            $action = $request->getActionName();
            $script = "/js/{$controller}-{$action}.js";
        }

        $view = $this->_actionController->view;
        $script .= '?v=' . $view->version;
        $view->headScript()->appendFile($script);
    }
}
