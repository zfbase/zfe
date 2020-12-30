<?php

/*
 * ZFE – платформа для построения редакторских интерфейсов.
 */

/**
 * Помощник использования абстрактных вьюшек.
 *
 * @property ZFE_View $view
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
     * * ?string $resource        – ресурс для проверки права доступа (если не указано, проверяется общий ресурс или контроллер)
     * * ?string $privilege       – привилегия для проверки права доступа (если не указана проверяется экшен)
     * * string  $action          – экшен вкладки
     * * ?array  $params          – параметры запроса
     * * string  $title           – заголовок вкладки
     * * ?string $class           – класс элемента li вкладки
     * * ?string $id              - id элемента li вкладки
     * * ?bool   $onlyRegistered  – только для зарегистрированных (если есть id), по умолчанию false
     * * ?bool   $onlyValid       - только не удаленные (deleted != 0), по умолчанию true
     * * ?int    $order           - порядок отображения
     * * ?int|array $badge        - бейдж (счетчик)
     * 
     * Бейдж может быть числом, либо массивом:
     * * int     $number    – Число
     * * ?string $class     – Класс (по умолчанию – `label-default`)
     * * ?string $baseClass – Базовый класс (по умолчанию – `label`)
     * * ?string $tag       - HTML тег
     * * ?array  $attr      – HTML атрибуты
     *
     * @var array
     */
    protected $_tabs;

    /**
     * Первоначальное значение свойства $_tabs.
     */
    protected $_initialTabs = [
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
     * Ресурс для проверки прав доступа.
     *
     * @var string
     */
    protected $_resource;

    /**
     * Точка входа в помощник.
     *
     * @param ZFE_Model_AbstractRecord|null $item
     *
     * @return ZFE_View_Helper_ControlTabs
     */
    public function controlTabs(?ZFE_Model_AbstractRecord $item = null)
    {
        if (null === $this->_tabs) {
            $this->resetTabs();
        }
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
        if (!$replace && key_exists($name, $this->_tabs)) {
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
        if (!key_exists($name, $this->_tabs)) {
            if ($skipMissing) {
                return $this;
            }
            throw new ZFE_View_Helper_Exception("Вкладка с названием '${name}' не зарегистрирована.");
        }

        $this->_tabs[$name] = array_merge($this->_tabs[$name], $settings);
        return $this;
    }

    /**
     * Удалить вкладку по коду вкладки.
     *
     * @param string $name
     *
     * @return ZFE_View_Helper_ControlTabs
     */
    public function removeTab(string $name)
    {
        unset($this->_tabs[$name]);
        return $this;
    }

    /**
     * Указать ресурс для проверки прав.
     *
     * @param string $resource
     *
     * @return ZFE_View_Helper_ControlTabs
     */
    public function setResource(string $resource)
    {
        $this->_resource = $resource;
        return $this;
    }

    public function __toString()
    {
        if (!($this->_item instanceof ZFE_Model_AbstractRecord)) {
            trigger_error('Невозможно отобразить вкладки: не передана запись $item.', E_USER_WARNING);
            return '';
        }

        $front = Zend_Controller_Front::getInstance();
        $request = $front->getRequest();

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

        $dispatcher = $front->getDispatcher();
        $controllerName = $request->getControllerName();
        $controllerClass = $dispatcher->formatControllerName($controllerName);
        $dispatcher->loadClass($controllerClass);

        foreach ($this->_tabs as $tab) {
            $resource = $tab['resource'] ?? $this->_resource ?? $controllerName;
            $privilege = $tab['privilege'] ?? $tab['action'];
            if (!$this->view->isAllowedMe($resource, $privilege)) {
                continue;
            }

            if (
                in_array($privilege, Controller_AbstractResource::getEnableActions())
                && !in_array($privilege, $controllerClass::getEnableActions())
            ) {
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
            if ($isActive && !empty($tab['params'])) {
                foreach ($tab['params'] as $param => $value) {
                    if ($request->getParam($param) != $value) {
                        $isActive = false;
                        break;
                    }
                }
            }

            $isDisabled = ($onlyRegistered && !$this->_item->exists())
                       || ($onlyValid && $this->_item->isDeleted());

            $class = [];
            $class[] = array_key_exists('class', $tab) ? $tab['class'] : '';
            $class[] = $isActive ? 'active' : '';
            $class[] = $isDisabled ? 'disabled' : '';
            $class = array_diff($class, ['']);

            $markup .= '<li';
            if (!empty($tab['id'])) {
                $markup .= ' id="' . $tab['id'] . '"';
            }
            if (count($class)) {
                $markup .= ' class="' . implode(' ', $class) . '"';
            }
            $markup .= '>';

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

            $markup .= $tab['title'];

            if (isset($tab['badge'])) {
                $markup .= ' ' . $this->renderBadge($tab['badge']);
            }
            $markup .= '</a></li>';
        }

        return $markup . '</ul>';
    }

    /**
     * Сформировать бейдж.
     *
     * @param number|array $options
     * @return void
     */
    protected function renderBadge($options)
    {
        if (is_string($options) || is_string($options)) {
            $options = ['number' => $options];
        } elseif (!is_array($options)) {
            throw new ZFE_View_Helper_Exception('Не корректный параметр бейджа');
        }

        $text = $options['number'];
        $tag = $options['tag'] ?? 'span';
        $attrs = array_merge([
            'class' => ($options['baseClass'] ?? 'label') . ' ' . ($options['class'] ?? 'label-default'),
        ], ($options['attr'] ?? []));
        return $this->view->tag($tag, $attrs, $text);
    }

    /**
     * Копирует $_initialTabs в $_tabs.
     */
    public function resetTabs()
    {
        $this->_tabs = $this->_initialTabs;
    }
}
