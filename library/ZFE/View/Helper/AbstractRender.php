<?php

/*
 * ZFE – платформа для построения редакторских интерфейсов.
 */

/**
 * Рендер абстрактной вьюшки в текущей области видимости.
 *
 * @category  ZFE
 */
class ZFE_View_Helper_AbstractRender extends Zend_View_Helper_Abstract
{
    /**
     * Отрендерить наиболее подходящий шаблон по имени.
     *
     * Поис будет осуществляться сначало в директории шаблонов приложения, потом в директориях библиотек.
     * В каждом из них сначало в папке контроллера, потом в асбстрактной.
     * Пример поиска для адреса /articles/index по убыванию приоритета:
     * 1) application/views/scripts/articles/index.phtml
     * 2) application/views/scripts/_abstract/index.phtml
     * 3) ZFE/resources/scripts/articles/index.phtml
     * 4) ZFE/resources/scripts/_abstract/index.phtml
     *
     * @param string $file       локальное имя шаблона
     * @param string $controller контроллер, для которого подбирается шаблон
     *
     * @return string
     */
    public function abstractRender($file, $controller = null)
    {
        if (empty($controller)) {
            $request = Zend_Controller_Front::getInstance()->getRequest();
            $controller = str_replace('_', '/', $request->getControllerName());
        }

        foreach ($this->view->getScriptPaths() as $path) {
            $basepath = realpath($path);
            if (file_exists($basepath . '/' . $controller . '/' . $file)) {
                return $this->view->render($controller . '/' . $file);
            }
            if (file_exists($basepath . '/' . $file)) {
                return $this->view->render($file);
            }
        }

        return $this->view->render('_abstract/' . $file);
    }
}
