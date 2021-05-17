<?php

/*
 * ZFE – платформа для построения редакторских интерфейсов.
 */

/**
 * Класс-связка между моделью и помощником вида, в архитектуре ZF+Doctrine (singleton).
 */
class ZFE_Paginator
{
    /**
     * Номер текущей страницы.
     *
     * @var int
     */
    protected $_pageNumber;

    /**
     * Число записей на странице.
     *
     * @var int
     */
    protected $_itemsPerPage;

    /**
     * Объект Doctrine_Pager с записями текущей страницы.
     *
     * @var Doctrine_Pager
     */
    protected $_pager;

    /**
     * Базовый URL.
     *
     * @var ZFE_Uri_Route
     */
    protected $_uri;

    /**
     * Экземпляр запроса.
     *
     * @var Zend_Controller_Request_Abstract
     */
    protected $_request;

    /**
     * Экземпляр - одиночка пагинатора.
     *
     * @var ZFE_Paginator
     */
    protected static $_instance = null;

    /**
     * Конструктор
     */
    protected function __construct()
    {
        $this->_request = Zend_Controller_Front::getInstance()->getRequest();
        $this->_itemsPerPage = config('view.perpage');
    }

    /**
     * Получить экземпляр пагинатора.
     *
     * @return ZFE_Paginator
     */
    public static function getInstance()
    {
        if (!self::$_instance) {
            self::$_instance = new static();
        }

        return self::$_instance;
    }

    /**
     * Установить число записей на странице.
     *
     * @param int $number
     *
     * @return ZFE_Paginator
     */
    public function setItemsPerPage($number)
    {
        $number = (int) $number;
        $this->_itemsPerPage = $number > 0 ? $number : $this->_itemsPerPage;
        return self::$_instance;
    }

    /**
     * Вернуть число записей на странице.
     *
     * @return int
     */
    public function getItemsPerPage()
    {
        return $this->_itemsPerPage;
    }

    /**
     * Установить номер текущей страницы.
     *
     * @param int $pageNumber
     *
     * @return ZFE_Paginator
     */
    public function setPageNumber($pageNumber)
    {
        $pageNumber = (int) $pageNumber;
        $this->_pageNumber = $pageNumber > 0 ? $pageNumber : 1;
        return self::$_instance;
    }

    /**
     * Получить номер текущей страницы.
     *
     * @return int|string
     */
    public function getPageNumber()
    {
        if (!$this->_pageNumber) {
            $this->setPageNumber($this->_request->getParam('page', 1));
        }
        return $this->_pageNumber;
    }

    /**
     * Установить текущий URL.
     *
     * @param string $url
     *
     * @return ZFE_Paginator
     *
     * @deprecated 1.31.6
     */
    public function setUrl($url)
    {
        $this->_uri = ZFE_Uri_Route::fromString($url);
        $this->_uri->setParam('page', '{%page_number}');
        return self::$_instance;
    }

    /**
     * Установить текущий URI.
     *
     * @param ZFE_Uri $uri
     *
     * @return ZFE_Paginator
     */
    public function setUri(ZFE_Uri $uri)
    {
        $this->_uri = $uri;
        $this->_uri->setParam('page', '{%page_number}');
        return self::$_instance;
    }

    /**
     * Получить текущий URI.
     *
     * @return ZFE_Uri
     */
    public function getUri()
    {
        if (!$this->_uri) {
            $this->setUri(ZFE_Uri_Route::fromRequest($this->_request));
        }
        return $this->_uri;
    }

    /**
     * Получить текущий URL.
     *
     * @return string
     */
    public function getUrl()
    {
        if (!$this->_uri) {
            $this->setUri(ZFE_Uri_Route::fromRequest($this->_request));
        }
        return $this->_uri->getUri();
    }

    /**
     * Получить объект Doctrine_Pager с записями текущей страницы.
     *
     * @throws Zend_Exception
     *
     * @return Doctrine_Pager
     */
    public function getPager()
    {
        if (!($this->_pager instanceof Doctrine_Pager)) {
            throw new Zend_Exception('Component Paginator not executed');
        }
        return $this->_pager;
    }

    /**
     * Выполнить запрос
     *
     * @param Doctrine_Query $query
     * @param array          $params
     *
     * @return Doctrine_Collection
     */
    public static function execute($query, array $params = [])
    {
        $instance = self::getInstance();
        $instance->_pager = new Doctrine_Pager($query, $instance->getPageNumber(), $instance->_itemsPerPage);
        return $instance->_pager->execute($params);
    }

    /**
     * Получить число результатов запроса.
     *
     * @return int
     */
    public function getNumResults()
    {
        return (int) $this->getPager()->getNumResults();
    }
}
