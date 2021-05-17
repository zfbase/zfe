<?php

/*
 * ZFE – платформа для построения редакторских интерфейсов.
 */

/**
 * Базовый контроллер.
 *
 * Содержит общие методы для всех контроллеров приложения.
 *
 * @property ZFE_View                           $view
 * @property ZFE_Controller_Action_HelperBroker $_helper
 */
abstract class ZFE_Controller_Abstract extends Zend_Controller_Action
{
    // Статусы стандартизированных Ajax ответов
    const STATUS_SUCCESS = '0';  // Статус успеха
    const STATUS_FAIL    = '1';  // Статус ошибки
    const STATUS_WARNING = '2';  // Статус предупреждения

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

        $this->view->develToolbar = config('debug.develToolbar');
        $this->view->containerClass = static::$_containerClass;
        $this->view->controllerName = $this->_request->getControllerName();
        $this->view->actionName = $this->_request->getActionName();
    }

    /**
     * Отправить JSON стандартной структуры.
     *
     * Если включено отображение ошибок, то будет отображено сообщение и стек вызовов функций.
     *
     * @param int    $status  статус
     * @param array  $data    данные
     * @param string $message сообщение (так же допускается массив сообщений)
     * @param mixed  $log     стек вызовов функций, приведших к ошибке:
     *                        из Exception возьмется $e->getTrace();
     *                        если будет передан true, будут использовано debug_backtrace();
     *                        если будет передано false, поле передаваться не будет;
     *                        остальные значения будут переданы «как есть»
     */
    protected function _json($status, array $data = [], $message = null, $log = true)
    {
        if (!in_array($status, [self::STATUS_SUCCESS, self::STATUS_WARNING, self::STATUS_FAIL])) {
            $this->abort(500, 'Использован недопустимый статус ответа');
        }

        $json = [
            'status' => $status,
            'data' => $data,
        ];

        if (ini_get('display_errors')) {
            $json['message'] = $message;

            if ($log instanceof Throwable) {
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
     * @param int    $code
     * @param string $customMessage
     *
     * @throws Zend_Controller_Action_Exception
     */
    public function abort($code = 500, $customMessage = null)
    {
        $message = $customMessage ?: Zend_Http_Response::responseCodeAsText($code);
        throw new Zend_Controller_Action_Exception($message, $code);
    }

    /**
     * Отправить сообщение об ошибке.
     *
     * @param string    $message
     * @param Throwable $ex
     * @param bool      $allowAjax
     */
    public function error($message, Throwable $ex = null, $allowAjax = true)
    {
        $this->notice(self::STATUS_FAIL, $message, $ex, $allowAjax);
        ZFE_Notices::err($message);
    }

    /**
     * Отправить сообщение об успешном исполнении.
     *
     * @param string $message
     * @param bool   $allowAjax
     */
    public function success($message, $allowAjax = true)
    {
        $this->notice(self::STATUS_SUCCESS, $message, null, $allowAjax);
        ZFE_Notices::ok($message);
    }

    /**
     * Отправить сообщение об предупреждении.
     *
     * @param string    $message
     * @param Throwable $ex
     * @param bool      $allowAjax
     */
    public function warning($message, Throwable $ex = null, $allowAjax = true)
    {
        $this->notice(self::STATUS_WARNING, $message, $ex, $allowAjax);
        ZFE_Notices::msg($message);
    }

    /**
     * Отправить сообщение.
     *
     * @param string    $status
     * @param string    $message
     * @param Throwable $ex
     * @param bool      $allowAjax
     */
    public function notice($status, $message, Throwable $ex = null, $allowAjax = true)
    {
        if ($ex) {
            if ($log = Zend_Registry::get('log')) {
                $log->log(
                    $ex->getMessage(),
                    Zend_Log::ERR,
                    [
                        'errno' => $ex->getCode(),
                        'file' => $ex->getFile(),
                        'line' => $ex->getLine(),
                        'context' => $ex->getTraceAsString(),
                    ]
                );
            }

            if (Zend_Registry::get('user')->noticeDetails) {
                $message = '<strong>' . $message . '</strong><br>'
                    . $ex->getMessage()
                    . '<pre>' . $ex->getTraceAsString() . '</pre>';
            }
        }

        if ($this->_request->isXmlHttpRequest() && $allowAjax) {
            $this->_json($status, [], $message);
        }
    }
}
