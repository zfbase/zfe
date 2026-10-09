<?php

/*
 * ZFE – платформа для построения редакторских интерфейсов.
 */

/**
 * Генератор ячейки с информацией о последнем изменении записи.
 */
class ZFE_View_Helper_LastEditedCell extends Zend_View_Helper_Abstract
{
    /**
     * Генерировать ячейку с информацией о последнем изменении записи.
     *
     * @param AbstractRecord $item
     * @param string         $class
     *
     * @return string
     */
    public function lastEditedCell(AbstractRecord $item, $class = '')
    {
        $editor = $item->contains('editor_id') && !empty($item->get('editor_id', false))
            ? '<div class="editor nowrap">' . $item->Editor->getShortName() . '</div>'
            : '';

        $updated = $item->contains('datetime_edited') ? $item->datetime_edited : null;
        if (!$updated && $item->contains('updated_at')) {
            $updated = $item->updated_at;
        }

        $datetime = $updated ? $this->view->dateTimeCompact($updated) : '';

        return $editor || $datetime
            ? '<td class="last-edited ' . $class . '">' . $datetime . $editor . '</td>'
            : '<td class="empty ' . $class . '">Неизвестно</td>';
    }
}
