<?php

/*
 * ZFE – платформа для построения редакторских интерфейсов.
 */

/**
 * Обработка ошибок.
 */
class ZFE_Controller_Default_Error extends Controller_Abstract
{
    /**
     * Страница ошибки.
     */
    public function errorAction()
    {
        $errors = $this->_getParam('error_handler');

        if (!$errors) {
            // Зачем говорить что попали на страницу ошибки? Просто такой страницы нету
            $this->abort(404);
        }

        switch ($errors->type) {
            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_ROUTE:
            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_CONTROLLER:
            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_ACTION:
                $this->getResponse()->setHttpResponseCode(404);
                $priority = Zend_Log::NOTICE;
                $this->view->message = 'Страница не найдена';
                break;
            default:
                $code = 500;
                $this->view->message = 'Ошибка приложения';
                if ($errors->exception instanceof Zend_Controller_Exception) {
                    $code = $errors->exception->getCode() ?: $code;
                    switch ($code) {
                        case 401:
                        case 403:
                            $this->view->message = 'Ошибка доступа';
                            break;
                        default:
                    }
                }
                $this->getResponse()->setHttpResponseCode($code);
                $priority = Zend_Log::CRIT;
                break;
        }

        ZFE_Utilities::logException($errors->exception, $priority);

        $this->view->displayException = $this->getInvokeArg('displayExceptions');
        $this->view->exception = $errors->exception;
        $this->view->displayExceptionMessage = Zend_Registry::get('user')->noticeDetails
            || $errors->exception instanceof Zend_Controller_Exception
            || $errors->exception instanceof ZFE_Exception;

        $this->view->request = $errors->request;

        if ($this->_request->isXmlHttpRequest()) {
            $this->_json(self::STATUS_FAIL, [], $errors->exception->getMessage(), $errors->exception->getTrace());
        }
    }
}
