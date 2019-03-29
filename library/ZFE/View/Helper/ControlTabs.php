<?php

/*
 * ZFE – платформа для построения редакторских интерфейсов.
 */

/**
 * Помощник использования абстрактных вьюшек.
 */
class ZFE_View_Helper_ControlTabs extends Zend_View_Helper_Abstract
{
    /**
     * Базовая запись.
     * 
     * @var ZFE_Model_AbstractRecord
     */
    protected $_item;

    /**
     * Вкладки управления записью.
     *
     * Параметры вкладки:
     * * ?string $privilege       – привилегия для проверки права доступа (если не указана проверяется экшен)
     * * string  $action          – экшен вкладки
     * * ?array  $params          – параметры запроса
     * * string  $title           – заголовок вкладки
     * * ?string $class           – класс элемента li вкладки
     * * ?bool   $onlyRegistered  – только для зарегистрированных (если есть id), по умолчанию false
     * * ?bool   $onlyValid       - только не удаленные (deleted != 0), по умолчанию true
     * * ?int    $order           - порядок отображения
     *
     * @var array
     */
    protected $_tabs = [
        'edit' => [
            'action' => 'edit',
            'title' => 'Карточка',
            'onlyValid' => true,
            'order' => 1,
        ],
        'history' => [
            'action' => 'history',
            'title' => 'История',
            'class' => 'pull-right',
            'onlyRegistered' => true,
            'onlyValid' => true,
            'order' => 100,
        ],
    ];

    /**
     * Точка входа в помощник.
     *
     * @param ZFE_Model_AbstractRecord|null $item
     * 
     * @return ZFE_View_Helper_ControlTabs
     */
    public function controlTabs(?ZFE_Model_AbstractRecord $item = null)
    {
        if ($item instanceof ZFE_Model_AbstractRecord) {
            $this->setItem($item);
        }

        return $this;
    }

    /**
     * Указать базовую запись.
     *
     * @param ZFE_Model_AbstractRecord $item
     * 
     * @return ZFE_View_Helper_ControlTabs
     */
    public function setItem(ZFE_Model_AbstractRecord $item)
    {
        $this->_item = $item;
        return $this;
    }

    /**
     * Добавить вкладку.
     *
     * @param string $name
     * @param array  $tab
     * @param bool   $replace
     * 
     * @return ZFE_View_Helper_ControlTabs
     */
    public function addTab(string $name, array $tab, bool $replace = false)
    {
        if ( ! $replace && key_exists($name, $this->_tabs)) {
            throw new ZFE_View_Helper_Exception("Вкладка с названием '${name}' уже зарегистрирована.");
        }

        $this->_tabs[$name] = $tab;
        return $this;
    }

    /**
     * Изменить часть настроек вкладки.
     * 
     * Пример:
     * $view->controlTabs()->modifyTab('edit', ['title' => 'Редактирование']);
     *
     * @param string $name        код вкладки
     * @param array  $settings    новые настройки
     * @param bool   $skipMissing пропускать незарегистрированные
     * 
     * @return ZFE_View_Helper_ControlTabs
     */
    public function modifyTab(string $name, array $settings, bool $skipMissing = false)
    {
        if ( ! key_exists($name, $this->_tabs)) {
            if ($skipMissing) {
                return $this;
            } else {
                throw new ZFE_View_Helper_Exception("Вкладка с названием '${name}' не зарегистрирована.");
            }
        }

        $this->_tabs[$name] = array_merge($this->_tabs[$name], $settings);
        return $this;
    }

    /**
     * Удалить вкладку по коду вкладки
     *
     * @param string $name
     * 
     * @return ZFE_View_Helper_ControlTabs
     */
    public function removeTab(string $name)
    {
        unset($this->_tab[$name]);
        return $this;
    }

    public function __toString()
    {
        if ( ! ($this->_item instanceof ZFE_Model_AbstractRecord)) {
            trigger_error('Невозможно отобразить вкладки: не передана запись $item.', E_USER_WARNING);
            return '';
        }

        $request = Zend_Controller_Front::getInstance()->getRequest();
        $controllerName = $request->getControllerName();

        $markup = '<ul class="nav nav-tabs" style="margin-bottom: 20px;">';

        usort($this->_tabs, function ($a, $b) {
            $a = (key_exists('order', $a)) ? ((int) $a['order']) : null;
            $b = (key_exists('order', $b)) ? ((int) $b['order']) : null;

            if ((null === $a && null === $b) || $a === $b) {
                return 0;
            }

            if (null === $a) {
                return 1;
            }

            if (null === $b) {
                return -1;
            }

            return ($a < $b) ? -1 : 1;
        });
        foreach ($this->_tabs as $tab) {
            if ( ! $this->view->isAllowedMe($controllerName, $tab['privilege'] ?? $tab['action'])) {
                continue;
            }
    
            $onlyRegistered = array_key_exists('onlyRegistered', $tab)
                ? $tab['onlyRegistered']
                : false;
            if (is_callable($onlyRegistered)) {
                $onlyRegistered = $onlyRegistered($this->_item);
            }
    
            $onlyValid = array_key_exists('onlyValid', $tab)
                ? $tab['onlyValid']
                : true;
            if (is_callable($onlyValid)) {
                $onlyValid = $onlyValid($this->_item);
            }
    
            $isActive = $request->getActionName() === $tab['action'];
            if ($isActive && ! empty($tab['params'])) {
                foreach ($tab['params'] as $param => $value) {
                    if ($request->getParam($param) !== $value) {
                        $isActive = false;
                        break;
                    }
                }
            }
    
            $isDisabled = ($onlyRegistered && ! $this->_item->exists())
                       || ($onlyValid && $this->_item->isDeleted());
    
            $class = [];
            $class[] = array_key_exists('class', $tab) ? $tab['class'] : '';
            $class[] = $isActive ? 'active' : '';
            $class[] = $isDisabled ? 'disabled' : '';
            $class = array_diff($class, ['']);
            if (count($class)) {
                $markup .= '<li class="' . implode(' ', $class) . '">';
            } else {
                $markup .= '<li>';
            }
    
            if ($isActive || $isDisabled) {
                $markup .= '<a>';
            } else {
                $uri = ZFE_Uri_Route::fromRequest($request);
                $uri->setAction($tab['action']);
                $uri->setParams($tab['params'] ?? []);

                $url = $uri . $this->view->hopsHistory()->getSideHash('?');
    
                $rn = $request->getParam('rn');
                if ($rn) {
                    $url .= '&rn=' . $rn;
                }
    
                $markup .= '<a href="' . $url . '">';
            }
    
            $markup .= $tab['title'] . '</a></li>';
        }

        return $markup . '</ul>';
    }
}
