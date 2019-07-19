<?php

/*
 * ZFE – платформа для построения редакторских интерфейсов.
 */

/**
 * Интерфейс манипулирования URI.
 */
class ZFE_Uri
{
    /**
     * Схема.
     *
     * @var string|null
     */
    protected $_scheme;

    /**
     * Логин.
     *
     * @var string|null
     */
    protected $_login;

    /**
     * Пароль.
     *
     * @var string|null
     */
    protected $_password;

    /**
     * Хост.
     *
     * @var string|null
     */
    protected $_host;

    /**
     * Порт.
     *
     * @var int|null
     */
    protected $_port;

    /**
     * Путь.
     *
     * @var string|null
     */
    protected $_path;

    /**
     * Запрос.
     *
     * @var array
     */
    protected $_query = [];

    /**
     * Фрагмент.
     *
     * @var string|null
     */
    protected $_fragment;

    /**
     * Создать экземпляр ZFE_Uri по строке с URI.
     *
     * @param string $string
     *
     * @return static|$this
     */
    public static function fromString(string $string)
    {
        $components = static::parseUri($string);
        $uri = new static();

        if ( ! empty($components['scheme'])) {
            $uri->setScheme($components['scheme']);
        }

        if ( ! empty($components['login'])) {
            $uri->setLogin($components['login']);
        }

        if ( ! empty($components['pass'])) {
            $uri->setPassword($components['pass']);
        }

        if ( ! empty($components['host'])) {
            $uri->setHost($components['host']);
        }

        if ( ! empty($components['port'])) {
            $uri->setPort($components['port']);
        }

        if ( ! empty($components['path'])) {
            $uri->setPath($components['path']);
        }

        if ( ! empty($components['query'])) {
            $uri->setQuery($uri->parseQuery($components['query']));
        }

        if ( ! empty($components['fragment'])) {
            $uri->setFragment($components['fragment']);
        }

        return $uri;
    }

    /**
     * Создать экземпляр ZFE_Uri по объекту запроса Zend_Controller_Request_Abstract.
     *
     * @param Zend_Controller_Request_Abstract $request
     * @param bool                             $includePost
     *
     * @return static|$this
     */
    public static function fromRequest(Zend_Controller_Request_Abstract $request, bool $includePost = false)
    {
        $uri = static::fromString($request->getRequestUri());

        if ($includePost && method_exists($request, 'getPost')) {
            $post = $request->getPost();
            foreach ($post as $key => $value) {
                $uri->setQueryPart($key, $value);
            }
        }

        return $uri;
    }

    /**
     * Разобрать URI на компоненты.
     *
     * @param string $uri
     *
     * @return array
     */
    public static function parseUri(string $uri)
    {
        $scheme =   "(?:(?:(?P<scheme>[\w\+\-\.]+):)?//)?";
        $userInfo = "(?:(?P<login>\w+):(?P<pass>\w+)@)?";
        $host =     "(?P<host>[\w\-\.]+)?";
        $port =     "(?::(?P<port>\d+))?";
        $path =     "(?P<path>[^\?#]*)?";
        $query =    "(?:\?(?P<query>[^#]+))?";
        $fragment = "(?:#(?P<fragment>\w+))?";

        $pattern = '!^' . $scheme . $userInfo . $host . $port . $path . $query . $fragment . '!u';
        preg_match($pattern, $uri, $components);
        return $components;
    }

    /**
     * Указать схему.
     *
     * @param string|null $scheme
     * 
     * @return static|$this
     */
    public function setScheme(?string $scheme)
    {
        $this->_scheme = $scheme;
        return $this;
    }

    /**
     * Получить схему.
     *
     * @return string|null
     */
    public function getScheme()
    {
        return $this->_scheme;
    }

    /**
     * Указать логин.
     *
     * @param string|null $login
     * 
     * @return static|$this
     */
    public function setLogin(?string $login)
    {
        $this->_login = $login;
        return $this;
    }

    /**
     * Получить логин.
     *
     * @return string|null
     */
    public function getLogin()
    {
        return $this->_login;
    }

    /**
     * Указать пароль.
     *
     * @param string|null $password
     * 
     * @return static|$this
     */
    public function setPassword(?string $password)
    {
        $this->_password = $password;
        return $this;
    }

    /**
     * Получить пароль.
     *
     * @return string|null
     */
    public function getPassword()
    {
        return $this->_password;
    }

    /**
     * Указать хост.
     *
     * @param string|null $host
     * 
     * @return static|$this
     */
    public function setHost(?string $host)
    {
        $this->_host = $host;
        return $this;
    }

    /**
     * Получить хост.
     *
     * @return string|null
     */
    public function getHost()
    {
        return $this->_host;
    }

    /**
     * Указать порт.
     *
     * @param int|null $port
     * 
     * @return static|$this
     */
    public function setPort(?int $port)
    {
        $this->_port = $port;
        return $this;
    }

    /**
     * Получить порт.
     *
     * @return int|null
     */
    public function getPort()
    {
        return $this->_port;
    }

    /**
     * Указать путь.
     *
     * @param string|null $path
     * 
     * @return static|$this
     */
    public function setPath(?string $path)
    {
        $this->_path = $path;
        return $this;
    }

    /**
     * Получить путь.
     *
     * @return string|null
     */
    public function getPath()
    {
        return $this->_path;
    }

    /**
     * Указать запрос.
     *
     * @param string|array $query
     * 
     * @return static|$this
     */
    public function setQuery($query)
    {
        if (is_string($query)) {
            $query = self::parseQuery($query);
        }

        if (is_array($query)) {
            $this->_query = $query;
        }

        return $this;
    }

    /**
     * Указать параметр запроса.
     *
     * @param string $key
     * @param mixed  $value
     * 
     * @return static|$this
     */
    public function setQueryPart(string $key, $value)
    {
        $this->_query[$key] = $value;
        return $this;
    }

    /**
     * Удалить параметр запроса.
     *
     * @param string $key
     * 
     * @return static|$this
     */
    public function removeQueryPart(string $key)
    {
        unset($this->_query[$key]);
        return $this;
    }

    /**
     * Разобрать запрос на составляющие.
     *
     * @param string $string
     * 
     * @return array
     */
    public static function parseQuery(string $string)
    {
        $queryFragments = explode('&', $string);

        $queryParts = [];
        foreach ($queryFragments as $fragment) {
            list($key, $value) = explode('=', $fragment);
            $queryParts[urldecode($key)] = urldecode($value);
        }

        return $queryParts;
    }

    /**
     * Получить запрос.
     *
     * @return string
     */
    public function getQuery()
    {
        $queryFragments = [];
        foreach ($this->_query as $key => $value) {
            $queryFragments[] = urlencode($key) . '=' . urlencode($value);
        }
        return implode('&', $queryFragments);
    }

    /**
     * Получить параметры запроса.
     *
     * @return array
     */
    public function getQueryParts()
    {
        return $this->_query;
    }

    /**
     * Указать фрагмент.
     *
     * @param string|null $fragment
     * 
     * @return static|$this
     */
    public function setFragment(?string $fragment)
    {
        $this->_fragment = $fragment;
        return $this;
    }

    /**
     * Получить фрагмент.
     *
     * @return string|null
     */
    public function getFragment()
    {
        return $this->_fragment;
    }

    /**
     * Получить сформированный URI.
     *
     * @return string
     */
    public function getUri()
    {
        $uri = '';

        if ( ! empty($this->_scheme)) {
            $uri .= $this->_scheme . '://';
        }

        if ( ! empty($this->_login)) {
            $uri .= $this->_login;

            if ( ! empty($this->_password)) {
                $uri .= ':' . $this->_password;
            }

            $uri .= '@';
        }

        if ( ! empty($this->_host)) {
            $uri .= $this->_host;

            if ( ! empty($this->_port)) {
                $uri .= ':' . $this->_port;
            }
        }

        if ( ! empty($this->_path)) {
            $uri .= $this->_path;
        }

        if ( ! empty($this->_query)) {
            $uri .= '?' . $this->getQuery();
        }

        if ( ! empty($this->_fragment)) {
            $uri .= '#' . $this->_fragment;
        }

        return $uri;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->getUri();
    }

    /**
     * Получить значение всех частей.
     *
     * @param bool $showEmpty показывать все (вне зависимости от заполнения)
     */
    public function getParts(bool $showEmpty = false)
    {
        $parts = [
            'scheme' => $this->_scheme,
            'login' => $this->_login,
            'password' => $this->_password,
            'host' => $this->_host,
            'port' => $this->_port,
            'path' => $this->_path,
            'query' => $this->_query,
            'fragment' => $this->_fragment,
        ];

        if ( ! $showEmpty) {
            return array_diff($parts, ['', null, []]);
        }

        return $parts;
    }

    /**
     * @return array
     */
    public function __debugInfo()
    {
        return $this->getParts(true);
    }

    /**
     * Я – валидный URI?
     *
     * @return bool
     */
    public function valid()
    {
        return $this->_host || ($this->_path && '/' !== $this->_path);
    }

    /**
     * Строка является URI?
     *
     * @param string $string
     *
     * @return bool
     */
    public static function check(string $string)
    {
        try {
            $uri = self::fromString($string);
        } catch (Throwable $t) {
            return false;
        }

        return $uri->valid();
    }
}
