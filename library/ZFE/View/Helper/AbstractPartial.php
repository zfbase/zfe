<?php

/*
 * ZFE – платформа для построения редакторских интерфейсов.
 */

/**
 * Рендер абстрактной вьюшки в собственной области видимости.
 */
class ZFE_View_Helper_AbstractPartial extends Zend_View_Helper_Partial
{
    /**
     * Отрендерить наиболее подходящий шаблон по имени.
     *
     * Поиск будет осуществляться сначала в директории шаблонов приложения, потом в директориях библиотек.
     * В каждом из них сначала в папке контроллера, потом в абстрактной.
     * Пример поиска для адреса /articles/index по убыванию приоритета:
     * 1) application/views/scripts/articles/index.phtml
     * 2) application/views/scripts/_abstract/index.phtml
     * 3) ZFE/resources/scripts/articles/index.phtml
     * 4) ZFE/resources/scripts/_abstract/index.phtml
     *
     * @param string       $name       Name of view script
     * @param array|string $module     If $model is empty, and $module is an array,
     *                                 these are the variables to populate in the
     *                                 view. Otherwise, the module in which the
     *                                 partial resides
     * @param array        $model      Variables to populate in the view
     * @param string       $controller Custom controller
     *
     * @return string|Zend_View_Helper_Partial
     */
    public function abstractPartial($name = null, $module = null, $model = null, $controller = null)
    {
        if (0 === func_num_args()) {
            return $this;
        }

        $view = $this->cloneView();
        if (isset($this->partialCounter)) {
            $view->partialCounter = $this->partialCounter;
        }
        if (isset($this->partialTotalCount)) {
            $view->partialTotalCount = $this->partialTotalCount;
        }

        if ((null !== $module) && is_string($module)) {
            require_once 'Zend/Controller/Front.php';
            $moduleDir = Zend_Controller_Front::getInstance()->getControllerDirectory($module);
            if (null === $moduleDir) {
                require_once 'Zend/View/Helper/Partial/Exception.php';
                $e = new Zend_View_Helper_Partial_Exception('Cannot render partial; module does not exist');
                $e->setView($this->view);

                throw $e;
            }
            $viewsDir = dirname($moduleDir) . '/views';
            $view->addBasePath($viewsDir);
        } elseif ((null === $model) && (null !== $module) && (is_array($module) || is_object($module))) {
            $model = $module;
        }

        if ( ! empty($model)) {
            if (is_array($model)) {
                $view->assign($model);
            } elseif (is_object($model)) {
                if (null !== ($objectKey = $this->getObjectKey())) {
                    $view->assign($objectKey, $model);
                } elseif (method_exists($model, 'toArray')) {
                    $view->assign($model->toArray());
                } else {
                    $view->assign(get_object_vars($model));
                }
            }
        }

        $render = new ZFE_View_Helper_AbstractRender();
        $render->setView($view);

        return $render->abstractRender($name, $controller);
    }
}
