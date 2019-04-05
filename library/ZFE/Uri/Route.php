<?php

/*
 * ZFE – платформа для построения редакторских интерфейсов.
 */

/**
 * Интерфейс манипулирования URI в контексте роутинга.
 */
class ZFE_Uri_Route extends ZFE_Uri
{
    /**
     * Модуль.
     *
     * @var string|null
     */
    protected $_module;

    /**
     * Контроллер.
     *
     * @var string|null
     */
    protected $_controller;

    /**
     * Действие.
     *
     * @var string|null
     */
    protected $_action;

    /**
     * Параметры.
     *
     * @var array
     */
    protected $_params = [];

    /**
     * Модуль по умолчанию.
     *
     * @var string
     */
    protected $_defaultModule;

    /**
     * Контроллер по умолчанию.
     *
     * @var string
     */
    protected $_defaultController;

    /**
     * Действие по умолчанию.
     *
     * @var string
     */
    protected $_defaultAction;

    /**
     * Конструктор.
     */
    public function __construct()
    {
        $front = Zend_Controller_Front::getInstance();
        $this->_defaultModule = $front->getDefaultModule();
        $this->_defaultController = $front->getDefaultControllerName();
        $this->_defaultAction = $front->getDefaultAction();
    }

    /**
     * Указать модуль.
     *
     * @param string $module
     * 
     * @return static|$this
     */
    public function setModule(?string $module)
    {
        $this->_module = ($module === $this->_defaultModule) ? null : $module;
        return $this;
    }

    /**
     * Получить модуль.
     *
     * @param bool $useDefault
     *
     * @return string
     */
    public function getModule(bool $useDefault = false)
    {
        if (empty($this->_module) && $useDefault) {
            return $this->_defaultModule;
        }

        return $this->_module;
    }

    /**
     * Указать контроллер.
     *
     * @param string $controller
     * 
     * @return static|$this
     */
    public function setController(?string $controller)
    {
        $this->_controller = $controller;
        return $this;
    }

    /**
     * Получить контроллер.
     *
     * @param bool $useDefault
     *
     * @return string
     */
    public function getController(bool $useDefault = false)
    {
        if (empty($this->_controller) && $useDefault) {
            return $this->_defaultController;
        }

        return $this->_controller;
    }

    /**
     * Указать действие.
     *
     * @param string $action
     * 
     * @return static|$this
     */
    public function setAction(?string $action)
    {
        $this->_action = $action;
        return $this;
    }

    /**
     * Получить действие.
     *
     * @param bool $useDefault
     *
     * @return string
     */
    public function getAction(bool $useDefault = false)
    {
        if (empty($this->_action) && $useDefault) {
            return $this->_defaultAction;
        }

        return $this->_action;
    }

    /**
     * Установить параметры.
     *
     * @param array $params
     * 
     * @return static|$this
     */
    public function setParams(array $params)
    {
        $this->_params = $params;
        return $this;
    }

    /**
     * Добавить параметр.
     *
     * @param string $key
     * @param mixed  $value
     * 
     * @return static|$this
     */
    public function setParam(string $key, $value)
    {
        if (in_array($value, [null, ''], true)) {
            unset($this->_params[$key]);
        } else {
            $this->_params[$key] = $value;
        }

        return $this;
    }

    /**
     * Удалить параметр.
     *
     * @param string $key
     */
    public function removeParam(string $key)
    {
        $this->setParam($key, null);
        return $this;
    }

    /**
     * Получить параметр.
     *
     * @param string $key
     * 
     * @return mixed
     */
    public function getParam(string $key)
    {
        return $this->_params[$key] ?? null;
    }

    /**
     * Проверит наличие параметра.
     *
     * @param string $key
     *
     * @return bool
     */
    public function hasParam(string $key)
    {
        return isset($this->_params[$key]);
    }

    /**
     * Получить все параметры.
     *
     * @return array
     */
    public function getParams()
    {
        return $this->_params;
    }

    /**
     * Удалить все параметры.
     * 
     * @return static|$this
     */
    public function clearParams()
    {
        $this->_params = [];
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public static function fromString(string $string)
    {
        $uri = parent::fromString($string);

        $path = $uri->getPath();
        $front = Zend_Controller_Front::getInstance();
        $routes = $front->getRouter()->getRoutes();
        foreach (array_reverse($routes, true) as $name => $route) {
            if (method_exists($route, 'isAbstract') && $route->isAbstract()) {
                continue;
            }

            if ($params = $route->match($path)) {
                $uri->setModule($params['module'] ?? null);
                $uri->setController($params['controller'] ?? null);
                $uri->setAction($params['action'] ?? null);
                unset($params['module'], $params['controller'], $params['action']);
                $uri->setParams($params);
                break;
            }
        }

        $query = $uri->getQueryParts();
        if ( ! empty($query)) {
            foreach ($query as $key => $value) {
                $uri->setParam($key, $value);
            }
        }

        return $uri;
    }

    /**
     * {@inheritdoc}
     */
    public static function fromRequest(Zend_Controller_Request_Abstract $request, bool $includePost = false)
    {
        $uri = static::fromString($request->getRequestUri());

        if ($includePost && method_exists($request, 'getPost')) {
            $post = $request->getPost();
            foreach ($post as $key => $value) {
                $uri->setParam($key, $value);
            }
        }

        return $uri;
    }

    /**
     * {@inheritdoc}
     */
    public function getUri()
    {
        $front = Zend_Controller_Front::getInstance();
        $routes = $front->getRouter();
        $this->_path = $routes->assemble([
            'module' => $this->getModule(),
            'controller' => $this->getController(),
            'action' => $this->getAction(),
        ] + $this->simplifyParams()->getParams());
        $this->setQuery([]);
        return parent::getUri();
    }

    /**
     * Упростить параметры.
     * 
     * @return static|$this
     */
    public function simplifyParams()
    {
        $this->_params = self::_simplifyParamsRecursive($this->_params);
        return $this;
    }

    /**
     * Упростить массив рекурсивно.
     *
     * @param array $array
     * 
     * @return array
     */
    private static function _simplifyParamsRecursive(array $array)
    {
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                foreach (self::_simplifyParamsRecursive($value) as $v_key => $v_value) {
                    if ( ! in_array($v_value, [null, ''])) {
                        $array[$key . '_' . $v_key] = $v_value;
                    }
                }
                unset($array[$key]);
            }
        }
        return $array;
    }

    /**
     * {@inheritdoc}
     */
    public function getParts(bool $showEmpty = false)
    {
        $parts = parent::getParts();
        unset($parts['path'], $parts['query']);
        $parts = [
            'module' => $this->getModule($showEmpty),
            'controller' => $this->getController($showEmpty),
            'action' => $this->getAction($showEmpty),
            'params' => $this->getParams(),
        ];

        if ( ! $showEmpty) {
            return array_diff($parts, ['', null, []]);
        }

        return $parts;
    }

    /**
     * {@inheritdoc}
     */
    public function valid()
    {
        // Учитывая наличие адреса по умолчанию, все URI всегда валидны.
        return true;
    }
}
