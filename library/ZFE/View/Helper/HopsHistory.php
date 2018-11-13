<?php

/*
 * ZFE – платформа для построения редакторских интерфейсов.
 */

/**
 * Оператор истории переходов.
 */
class ZFE_View_Helper_HopsHistory extends Zend_View_Helper_Abstract
{
    /**
     * Коллекция хешей контрольных точек переходов.
     *
     * @var Zend_Session_Namespace
     */
    protected $_hops;

    /**
     * Zend_Controller_Request_Http.
     *
     * @var Zend_Controller_Request_Http
     */
    protected $_request;

    /**
     * Сведения о ссылке «Наверх».
     *
     * @var array ['url', 'label']
     */
    protected $_upData;

    /**
     * Конструктор
     */
    public function __construct()
    {
        $this->_hops = new Zend_Session_Namespace('HopsHistory');

        $this->_request = Zend_Controller_Front::getInstance()->getRequest();

        $hash = $this->_request->getParam('h');
        if ($hash && $this->_hops->{$hash}) {
            $this->_upData = $this->_hops->{$hash};
        } else {
            $this->_upData = null;
        }
    }

    /**
     * Получить экземпляр помощника.
     *
     * @return ZFE_View_Helper_HopsHistory
     */
    public function hopsHistory()
    {
        return $this;
    }

    /**
     * Получить кнопку для направления «Наверх».
     *
     * @return null|string
     */
    public function getUpBtn()
    {
        if ($this->_upData) {
            $url = $this->_upData['url'];
            $label = $this->_upData['label'] ?: 'Наверх';
        } else {
            $module = $this->_request->getModuleName();
            $controller = $this->_request->getControllerName();
            $action = $this->_request->getActionName();
            if ('index' !== $action) {
                $url = "/{$controller}/index";
                if ($module) {
                    $url = '/' . $module . $url;
                }
                $label = 'К списку';
            } else {
                return null;
            }
        }

        $icon = $this->view->tag('span', ['class' => 'glyphicon glyphicon-arrow-up']);

        return $this->view->tag('a', [
            'role' => 'button',
            'class' => 'btn btn-xs btn-default',
            'href' => $url,
        ], $label . ' ' . $icon);
    }

    /**
     * Получить данные о переходе «Наверх».
     *
     * @return array
     */
    public function getUpData()
    {
        return $this->_upData;
    }

    /**
     * Получить ссылку для перехода (с сохранением направления «Наверх»).
     *
     * @param string $prefix класс префикса: при указании '?' будет добавлено '?h=', аналогично для '&'
     *
     * @return null|string
     */
    public function getSideHash($prefix = null)
    {
        $hash = $this->_request->getParam('h');
        switch ($prefix) {
            case '?':
                return '?h=' . $hash;
            case '&':
                return '&h=' . $hash;
            default:
                return $hash;
        }
    }

    /**
     * Хеш для переходов в направлении «Вниз».
     *
     * @var string
     */
    protected $_downHash;

    /**
     * Получить хеш для переходов в направлении «Вниз».
     *
     * @param string $label  заголовок для направления «Наверх», соответствующий хешу
     * @param string $prefix класс префикса: при указании '?' будет добавлено '?h=', аналогично для '&'
     * @param array  $params произвольные данные для прикрепления к хешу
     *
     * @return string
     */
    public function getDownHash($label = null, $prefix = null, array $params = [])
    {
        if (empty($this->_downHash)) {
            $data = [
                'url' => $this->_request->getRequestUri(),
                'label' => $label,
                'params' => $params,
            ];

            $this->_downHash = $downHash = uniqid('p');
            $this->_hops->{$downHash} = $data;
        }

        switch ($prefix) {
            case '?':
                return '?h=' . $this->_downHash;
            case '&':
                return '&h=' . $this->_downHash;
            default:
                return $this->_downHash;
        }
    }
}
