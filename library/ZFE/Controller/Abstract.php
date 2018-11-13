<?php

/*
 * ZFE – платформа для построения редакторских интерфейсов.
 */

/**
 * Базовый контроллер.
 *
 * Содержит общие методы для всех контроллеров приложения.
 *
 * @property ZFE_Controller_Action_HelperBroker $_helper
 *
 * @category  ZFE
 */
abstract class ZFE_Controller_Abstract extends Zend_Controller_Action
{
    // Статусы стандартизированных Ajax ответов
    const STATUS_SUCCESS = 0;  // Статус успеха
    const STATUS_FAIL = 1;  // Статус ошибки

    /**
     * Класс контейнера всей страницы.
     *
     * @var string
     */
    protected static $_containerClass = 'container';

    /**
     * Выполняется до того, как диспетчером будет вызвано действие.
     */
    public function preDispatch()
    {
        parent::preDispatch();

        $config = Zend_Registry::get('config');

        if ($config->debug->develToolbar) {
            $this->view->develToolbar = $config->debug->develToolbar;
        }

        $this->view->containerClass = static::$_containerClass;

        $this->view->controllerName = $this->_request->getControllerName();
        $this->view->actionName = $this->_request->getActionName();
    }

    /**
     * Отправить JSON стандартной структуры.
     *
     * Если включено отображение ошибок, то будет отображено сообщение и стек вызовов функций.
     *
     * @param int             $status  статус
     * @param array           $data    данные
     * @param string          $message сообщение
     * @param Exception|mixed $log     стек вызовов функций, приведших к ошибке:
     *                                 из Exception возьмется $e->getTrace();
     *                                 если будет передан true, будут использовано debug_backtrace();
     *                                 если будет передано false, поле передаваться не будет;
     *                                 остальные значения будут переданы «как есть»
     */
    protected function _json($status, array $data = [], $message = null, $log = true)
    {
        if (self::STATUS_SUCCESS !== $status && self::STATUS_FAIL !== $status) {
            throw new ZFE_Controller_Exception('Использован недопустимый статус ответа');
        }

        $json = [];

        $json['status'] = $status;
        $json['data'] = $data;

        if (ini_get('display_errors')) {
            $json['message'] = $message;

            if ($log instanceof Exception) {
                $json['log'] = $log->getTrace();
            } elseif (true === $log) {
                $json['log'] = debug_backtrace();
            } elseif (false !== $log) {
                $json['log'] = $log;
            }
        }

        $this->_helper->json($json);
    }

    /**
     * Прерывает обработку запроса, возвращая соответствующий HTTP-код.
     *
     * @param int        $code
     * @param null|mixed $customMessage
     */
    public function abort($code = 500, $customMessage = null)
    {
        $message = $customMessage ?: Zend_Http_Response::responseCodeAsText($code);
        throw new Zend_Controller_Action_Exception($message, $code);
    }
}
