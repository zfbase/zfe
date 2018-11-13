<?php

/*
 * ZFE – платформа для построения редакторских интерфейсов.
 */

/**
 * Генератор меню.
 *
 * В настоящей версии отсутствует поддержка модулей.
 * Генератор поддерживает синтаксис Zend_Navigation_Page.
 * Подробнее о возможностях генератора читайте в документации.
 */
class ZFE_View_Helper_MenuItems extends Zend_View_Helper_Abstract
{
    /**
     * Сгенерировать меню.
     *
     * @param array|Zend_Config $pages          структура меню
     * @param bool              $autoActive     автоматически выбирать активный пункт?
     * @param bool              $disabledAcl    игнорировать настройки безопасности?
     * @param bool              $dropdownEnable делать дочерние уровни выпадающими?
     *
     * @return string
     */
    public function menuItems($pages = null, $autoActive = true, $disabledAcl = false, $dropdownEnable = true)
    {
        $html = '';
        foreach ($pages as $page) {
            $page = is_array($page)
                ? (object) $page
                : $page;

            if ( ! $disabledAcl && ! $this->_isAllowed($page)) {
                continue;
            }

            if (isset($page->divider)) {
                $html .= '<li class="divider"></li>';

                // если это не просто разделитель, но еще и с названием раздела, выйдем после названия
                if ( ! isset($page->itemsHeader)) {
                    continue;
                }
            }

            if (isset($page->itemsHeader)) {
                $html .= '<li class="dropdown-header">' . $page->itemsHeader . '</li>';

                continue;
            }

            if ($page->label instanceof Zend_Config) {
                $label = '<span class="' . $page->label->ico . ' hidden-xs"></span>'
                       . '<span class="visible-xs-inline">' . $page->label->text . '</span>';
                $title = isset($page->title) ? $page->title : $page->label->text;
            } else {
                if ( ! empty($page->label) && is_string($page->label)) {
                    $label = $page->label;
                } elseif ( ! empty($page->controller)) {
                    $dispatcher = Zend_Controller_Front::getInstance()->getDispatcher();
                    $controller = $dispatcher->formatControllerName($page->controller);
                    $dispatcher->loadClass($controller);
                    $modelName = $controller::getModelName();
                    $label = $modelName::$namePlural;
                } else {
                    $label = '';
                }

                $title = isset($page->title) ? $page->title : $label;
            }

            if (isset($page->badge)) {
                $badgeClass = 'badge';
                $badgeValue = '';
                if ($page->badge instanceof Zend_Config) {
                    $badgeValue = $page->badge->value;
                    if (isset($page->badge->color)) {
                        $badgeClass .= ' badge-' . $page->badge->color;
                    }
                    $badge = $page->badge->value;
                } else {
                    $badgeValue = $page->badge;
                }
                $label .= ' <span class="' . $badgeClass . '">' . $badgeValue . '</span>';
            }

            $hasChildren = isset($page->pages) && ! empty($page->pages);

            if (isset($page->uri)) {
                $uri = $page->uri;
            } elseif (isset($page->controller)) {
                $uri = '';
                if (isset($page->module)) {
                    $uri .= '/' . $page->module;
                }
                $uri .= '/' . $page->controller;
                if (isset($page->action)) {
                    $uri .= '/' . $page->action;
                } elseif (isset($page->params) && ! empty($page->params)) {
                    $uri .= '/index';
                }
                if (isset($page->params)) {
                    foreach ($page->params as $name => $value) {
                        $uri .= '/' . $name;
                        $uri .= '/' . $value;
                    }
                }
            } else {
                $uri = '#';
            }

            $active = $this->_isActiveRecursive($page, $autoActive);

            $class = isset($page->class)
                ? [$page->class]
                : [];
            if ($hasChildren && $dropdownEnable) {
                $class[] = 'dropdown';
            }
            if ($active) {
                $class[] = 'active';
            }

            $dataAttribs = '';
            if (isset($page->data)) {
                if ($page->data instanceof Zend_Config) {
                    $data = $page->data->toArray();
                } elseif (is_array($page->data)) {
                    $data = $page->data;
                } else {
                    $data = null;
                }

                if (is_array($data)) {
                    foreach ($data as $name => $value) {
                        $dataAttribs .= ' data-' . $name . '="' . $value . '"';
                    }
                }
            }

            $html .= '<li'
                   . (isset($page->id) ? ' id="' . $page->id . '"' : '')
                   . (count($class) ? ' class="' . implode(' ', $class) . '"' : '')
                   . $dataAttribs
                   . '>';

            if ($hasChildren && $dropdownEnable) {
                $html .= '<a href="#" class="dropdown-toggle" data-toggle="dropdown" title="' . $title . '">' . $label . ' <b class="caret"></b></a>';
                $html .= '<ul class="dropdown-menu">' . $this->MenuItems($page->pages, $autoActive, $disabledAcl, $dropdownEnable) . '</ul>';
            } elseif ($hasChildren) {
                $html .= '<a title="' . $title . '">' . $label . ' <b class="caret"></b></a>';
                $html .= '<ul>' . $this->MenuItems($page->pages, $autoActive, $disabledAcl, $dropdownEnable) . '</ul>';
            } else {
                $html .= '<a href="' . $uri . '" title="' . $title . '">' . $label . '</a>';
            }

            $html .= '</li>';
        }

        return $html;
    }

    /**
     * У авторизованного пользователя есть доступ к странице?
     *
     * @param object $page
     *
     * @return bool
     */
    protected function _isAllowed($page)
    {
        $acl = Zend_Registry::get('acl');

        $resource = null;
        $privilege = 'index';
        // Если хотя бы одна привилегия ресурса запрещена,
        // то без указания привилегии, ресурс тоже будет считаться запрещенным

        if (isset($page->resource) && $acl->hasResource($page->resource)) {
            $resource = $page->resource;
            if (isset($page->privilege)) {
                $privilege = $page->privilege;
            }
        } elseif (isset($page->controller) && $acl->hasResource($page->controller)) {
            $resource = $page->controller;
            if (isset($page->action)) {
                $privilege = $page->action;
            }
        } elseif (isset($page->pages) && ! empty($page->pages)) {
            foreach ($page->pages as $child) {
                if ($this->_isAllowed($child)) {
                    return true;
                }
            }
        }

        return $acl->isAllowedMe($resource, $privilege);
    }

    /**
     * Это активный узел?
     *
     * Узел считается активным в том числе если активен его потомок.
     *
     * @param ArrayObject|Zend_Config $page       проверяемый узел
     * @param bool                    $autoActive автоматически выбирать активный узел?
     *
     * @return bool
     */
    protected function _isActiveRecursive($page, $autoActive)
    {
        if (isset($page->active) && true === $page->active) {
            return true;
        }

        if ($autoActive) {
            $controller = $action = '';
            if (isset($page->controller)) {
                $controller = $page->controller;
                if (isset($page->action)) {
                    $action = $page->action;
                }
            } elseif (isset($page->uri)) {
                $_ = explode('/', trim($page->uri, '/'));
                $controller = isset($_[0])
                    ? $_[0]
                    : 'index';
                if (isset($_[1])) {
                    $action = isset($_[1])
                        ? $_[1]
                        : 'index';
                }
            }

            $request = Zend_Controller_Front::getInstance()->getRequest();
            if ($controller === $request->getControllerName()) {
                if (empty($action) || $action === $request->getActionName()) {
                    return true;
                }
            }
        }

        if (isset($page->pages) && ! empty($page->pages)) {
            foreach ($page->pages as $child) {
                if ($this->_isActiveRecursive($child, $autoActive)) {
                    return true;
                }
            }
        }

        return false;
    }
}
