<?php

/*
 * ZFE – платформа для построения редакторских интерфейсов.
 */

/**
 * Вывод сведений о создании и последнем редактирование записи.
 */
class ZFE_View_Helper_HistoryMeta extends Zend_View_Helper_Abstract
{
    /**
     * Вернуть отформатированные сведений о создании и последнем редактирование записи.
     *
     * @param AbstractRecord $item
     * @param bool           $showCreator
     * @param bool           $showEditor
     * @param bool           $showVersion
     *
     * @return string
     */
    public function historyMeta(AbstractRecord $item, $showCreator = true, $showEditor = true, $showVersion = true)
    {
        $creation = '';

        $created = $item->contains('datetime_created') ? $item->datetime_created : null;
        if (!$created && $item->contains('created_at')) {
            $created = $item->created_at;
        }

        $updated = $item->contains('datetime_created') ? $item->datetime_created : null;
        if (!$updated && $item->contains('updated_at')) {
            $updated = $item->updated_at;
        }

        $showCreator = $showCreator && $item->contains('creator_id') && !empty($item->creator_id);
        $showCreator = $showCreator && $created;
        if ($showCreator) {
            $creator = $item->Creator;
            $fullName = '<span>' . $creator->getNameWithContactInfo() . '</span>';
            $datetime = '<span>' . $this->view->dateTime($created) . '</span>';
            $caption = '<span class="caption">Создал' . ($creator->isFemale() ? 'а' : '') . ':</span>';
            $creation = '<div class="editedBy">' . $caption . ' ' . $fullName . ' ' . $datetime . '</div>';
        }

        $editing = '';
        $showEditor = $showEditor && $item->contains('editor_id') && !empty($item->editor_id);
        $showEditor = $showEditor && $updated && $updated !== $created;
        if ($showEditor) {
            $editor = $item->Editor;
            $fullName = '<span>' . $editor->getNameWithContactInfo() . '</span>';
            $datetime = '<span>' . $this->view->dateTime($updated) . '</span>';
            $caption = '<span class="caption">Изменил' . ($editor->isFemale() ? 'а' : '') . ':</span>';
            $editing = '<div class="editedBy">' . $caption . ' ' . $fullName . ' ' . $datetime . '</div>';
        }

        $version = '';
        $showVersion = $showVersion && $item->contains('version') && !empty($item->version);
        $showVersion = $showVersion && $item->contains('id') && $item->exists();
        if ($showVersion) {
            $caption = '<span class="caption">Версия:</span>';
            $version = '<div class="editedBy">' . $caption . ' <span>' . $item->version . '</span></div>';
        }

        return $creation . $editing . $version;
    }
}
