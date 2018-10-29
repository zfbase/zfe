<?php

/*
 * ZFE – платформа для построения редакторских интерфейсов.
 */

/**
 * Форматировщик массивов.
 *
 * @category  ZFE
 */
class ZFE_View_Helper_FormatArray extends Zend_View_Helper_Abstract
{
    /**
     * Отформатировать запрос
     *
     * @param array  $array
     * @param string $listType
     *
     * @return string
     */
    public function formatArray(array $array, $listType = 'nolist')
    {
        $html = '';

        foreach ($array as $row) {
            $html .= '<li>' . $this->view->autoFormat($row) . '</li>';
        }

        switch ($listType) {
            case 'number':
                return '<ol>' . $html . '</ol>';
            case 'marker':
                return '<ul>' . $html . '</ul>';
            case 'nolist':
                return '<ul class="list-unstyled">' . $html . '</ul>';
        }
    }
}
