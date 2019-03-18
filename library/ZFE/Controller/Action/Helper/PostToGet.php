<?php

/*
 * ZFE – платформа для построения редакторских интерфейсов.
 */

/**
 * Помощник преобразования POST- данные в ЧПУ.
 */
class ZFE_Controller_Action_Helper_PostToGet extends Zend_Controller_Action_Helper_Abstract
{
    /**
     * Преобразовать POST- данные в ЧПУ.
     *
     * @param array $ignore
     */
    public function direct(array $ignore = ['submit'])
    {
        $request = $this->getRequest();
        if ($request->isPost()) {
            $uri = ZFE_Uri_Route::fromRequest($request, true);
            foreach ($ignore as $key) {
                $uri->removeParam($key);
            }
            Zend_Controller_Action_HelperBroker::getStaticHelper('redirector')->gotoUrl($uri->getUri());
        }
    }
}
