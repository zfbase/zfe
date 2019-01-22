<?php

/*
 * ZFE – платформа для построения редакторских интерфейсов.
 */

/**
 * Генератор ячейки с информацией о последнем изменении записи.
 *
 * @category  ZFE
 */
class ZFE_View_Helper_LastEditedCell extends Zend_View_Helper_Abstract
{
    /**
     * Генерировать ячейку с информацией о последнем изменении записи.
     *
     * @param AbstractRecord $item
     * @param mixed          $class
     *
     * @return string
     */
    public function lastEditedCell(AbstractRecord $item, $class = '')
    {
        $editor = $item->contains('editor_id') && ! empty($item->get('editor_id', false))
            ? '<div class="editor">' . $item->Editor->getShortName() . '</div>'
            : '';

        $datetime = $item->contains('datetime_edited') && ! empty($item->datetime_edited)
            ? $this->view->dateTimeCompact($item->datetime_edited)
            : '';

        return $editor || $datetime
            ? '<td class="last-edited ' . $class . '">' . $datetime . $editor . '</td>'
            : '<td class="empty ' . $class . '">Неизвестно</td>';
    }
}
