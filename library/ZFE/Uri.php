<?php

/*
 * ZFE – платформа для построения редакторских интерфейсов.
 */

class ZFE_Uri
{
    protected $_scheme;
    protected $_username;
    protected $_password;
    protected $_host;
    protected $_port;
    protected $_path;
    protected $_query = [];
    protected $_fragment;

    public static function fromString($uri)
    {
        $components = parse_url($uri);

        $uri = new self();

        if ( ! empty($components['scheme'])) {
            $uri->_scheme = $components['scheme'];
        }

        if ( ! empty($components['host'])) {
            $uri->_host = $components['host'];
        }

        if ( ! empty($components['port'])) {
            $uri->_port = $components['port'];
        }

        if ( ! empty($components['user'])) {
            $uri->_username = $components['user'];
        }

        if ( ! empty($components['pass'])) {
            $uri->_password = $components['pass'];
        }

        if ( ! empty($components['path'])) {
            $uri->_path = $components['path'];
        }

        if ( ! empty($components['query'])) {
            $uri->_query = $uri->parseQuery($components['query']);
        }

        if ( ! empty($components['fragment'])) {
            $uri->_fragment = $components['fragment'];
        }

        return $uri;
    }

    public function setScheme($scheme)
    {
        $this->_scheme = $scheme;
    }

    public function getScheme()
    {
        return $this->_scheme;
    }

    public function setUsername($username)
    {
        $this->_username = $username;
    }

    public function getUsername()
    {
        return $this->_username;
    }

    public function setPassword($password)
    {
        $this->_password = $password;
    }

    public function getPassword()
    {
        return $this->_password;
    }

    public function setHost($host)
    {
        $this->_host = $host;
    }

    public function getHost()
    {
        return $this->_host;
    }

    public function setPort($port)
    {
        $this->_port = $port;
    }

    public function getPort()
    {
        return $this->_port;
    }

    public function setPath($path)
    {
        $this->_path = $path;
    }

    public function getPath()
    {
        return $this->_path;
    }

    public function setQuery($query)
    {
        if (is_string($query)) {
            $query = $this->parseQuery();
        }

        $this->_query = $query;
    }

    public function setQueryPart($key, $value)
    {
        $this->_query[$key] = $value;
    }

    public function removeQueryPart($key)
    {
        unset($this->_query[$key]);
    }

    public function parseQuery($queryString)
    {
        $queryFragments = explode('&', $queryString);

        $queryParts = [];
        foreach ($queryFragments as $fragment) {
            list($key, $value) = explode('=', $fragment);
            $queryParts[$key] = $value;
        }

        return $queryParts;
    }

    public function getQuery()
    {
        $queryFragments = [];
        foreach ($this->_query as $key => $value) {
            $queryFragments[] = $key . '=' . $value;
        }
        return implode('&', $queryFragments);
    }

    public function setFragment($fragment)
    {
        $this->_fragment = $fragment;
    }

    public function getFragment()
    {
        return $this->_fragment;
    }

    public function getUri()
    {
        $uri = '';

        if ( ! empty($this->_scheme)) {
            $uri .= $this->_scheme . '://';
        }

        if ( ! empty($this->_username)) {
            $uri .= $this->_username;

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
}
