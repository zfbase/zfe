<?php

/*
 * ZFE – платформа для построения редакторских интерфейсов.
 */

/**
 * Помощник использования абстрактных вьюшек.
 */
class ZFE_Controller_Action_Helper_AbstractView extends Zend_Controller_Action_Helper_Abstract
{
    /**
     * Использовать абстрактные вьюшки, если нету собственных.
     *
     * @param string $action
     * @param string $controller
     */
    public function direct($action = null, $controller = null)
    {
        /** @var $viewRenderer Zend_Controller_Action_Helper_ViewRenderer */
        $viewRenderer = Zend_Controller_Action_HelperBroker::getStaticHelper('viewRenderer');

        if ($viewRenderer->getNoRender()) {
            return;
        }

        $request = $this->getRequest();
        $controller = $controller ?: str_replace('_', '/', $request->getControllerName());
        $action = $action ?: $request->getActionName();
        $view = $this->_actionController->view;

        // Если есть своя вьюшка, то делать больше ничего не надо
        foreach ($view->getScriptPaths() as $path) {
            if (file_exists($path . '/' . $controller . '/' . $action . '.phtml')) {
                return;
            }
        }

        $viewRenderer->setNoRender(true);
        $this->getResponse()->appendBody(
            $view->render('_abstract/' . $action . '.phtml')
        );
    }
}
