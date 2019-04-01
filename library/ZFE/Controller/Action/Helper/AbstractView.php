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
        /** @var Zend_Controller_Action_Helper_ViewRenderer $viewRenderer */
        $viewRenderer = Zend_Controller_Action_HelperBroker::getStaticHelper('viewRenderer');

        if ($viewRenderer->getNoRender()) {
            return;
        }

        if ($viewRenderer->getNoController() || $viewRenderer->getNeverController()) {
            $nameSpec = $viewRenderer->getViewScriptPathNoControllerSpec();
        } else {
            $nameSpec = $viewRenderer->getViewScriptPathSpec();
        }

        $view = $this->_actionController->view;
        $request = $this->getRequest();

        if ($controller === null) {
            $controller = str_replace('_', '/', $request->getControllerName());
        }

        if ($action === null) {
            $action = $request->getActionName();
        }

        $parts = [
            'controller' => $controller,
            'action'     => $action,
        ];

        $inflector = $viewRenderer->getInflector();
        $inflector->setTargetReference($nameSpec);
        $script = $inflector->filter($parts);

        // Если есть своя вьюшка, то делать больше ничего не надо
        foreach ($view->getScriptPaths() as $basePath) {
            if (file_exists($basePath . DIRECTORY_SEPARATOR . $script)) {
                return;
            }
        }

        $viewRenderer->setNoRender(true);
        $this->getResponse()->appendBody(
            $view->render('_abstract/' . $action . '.phtml')
        );
    }
}
