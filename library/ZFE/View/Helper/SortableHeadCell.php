<?php

/*
 * ZFE – платформа для построения редакторских интерфейсов.
 */

/**
 * Генератор ячейки заголовка сортируемого поля таблицы.
 */
class ZFE_View_Helper_SortableHeadCell extends Zend_View_Helper_Abstract
{
    /**
     * Получить ячейку заголовка сортируемого поля таблицы.
     *
     * @param string $field     ключ имени поля
     * @param string $title     специальное название поля (не из базы имен полей)
     * @param string $cellClass дополнительные классы для ячейки
     *
     * @return string
     */
    public function sortableHeadCell($field, $title = null, $cellClass = '')
    {
        $modelName = $this->view->modelName;

        if (null === $title) {
            $title = $modelName::getFieldName($field);
        }

        // Получаем текущую сортировку из запроса
        // если в запросе не указана, используем заданную по умолчанию в модели
        $request = Zend_Controller_Front::getInstance()->getRequest();
        $raw_order = $request->getParam('order', $modelName::$defaultOrderKey);

        $pos = strrpos($raw_order, '_');
        if ($pos > 1) {
            $cur_field = substr($raw_order, 0, $pos);
            $cur_order = strtolower(substr($raw_order, $pos + 1));

            if ($cur_field === $field) {
                $order = 'asc' === $cur_order
                    ? 'desc'
                    : 'asc';
            } else {
                $cur_order = '';
                $order = 'asc';
            }
        } else {
            $cur_order = '';
            $order = 'asc';
        }

        switch ($cur_order) {
            case 'asc': $caret = '<i class="order dropup"><i class="caret"></i></i>'; break;
            case 'desc': $caret = '<i class="order"><i class="caret"></i></i>'; break;
            default: $caret = ''; break;
        }

        $url = $this->_getBaseUrl() . '/order/' . $field . '_' . $order;

        return
            '<th class="sortable ' . $cellClass . '">' .
                '<a href="' . $url . '">' .
                    '<span>' . $title . '</span>' .
                    $caret .
                '</a>' .
            '</th>';
    }

    /**
     * Получить базовый адрес страницы.
     *
     * @return string
     */
    protected function _getBaseUrl()
    {
        $request = Zend_Controller_Front::getInstance()->getRequest();

        $ret = [];
        $get = [];

        $ignore = [
            'module',                // модули мы не используем
            'controller', 'action',  // контроллер и экшен подставим позже
            'page',                  // после сортировки имеет смысл отображать первую страницу
            'order',                  // сортировку будем менять
        ];

        foreach ($request->getParams() as $param => $value) {
            if (in_array($param, $ignore, true)) {
                continue;
            }

            if (is_array($value)) {  // массивы игнорируем
                continue;
            }

            if (false !== strpos($value, '/')) {
                $get[] = $param . '=' . urlencode($value);
            } else {
                if ($value || '0' === $value) {
                    $ret[] = urlencode($param);
                    $ret[] = urlencode($value);
                }
            }
        }

        $controller = $request->getControllerName();
        $action = $request->getActionName();

        $url = '/' . $controller . '/' . $action;
        if (count($ret)) {
            $url .= '/' . implode('/', $ret);
        }
        if ( ! empty($get)) {
            $url .= '?' . implode('&', $get);
        }

        return $url;
    }
}
