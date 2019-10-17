<?php

/*
 * ZFE – платформа для построения редакторских интерфейсов.
 */

/**
 * Вывод нотификаций.
 */
class ZFE_View_Helper_Notices extends Zend_View_Helper_Abstract
{
    /**
     * Вернуть код вывода нотификации.
     *
     * @return string
     */
    public function notices()
    {
        $notices = [];

        foreach (ZFE_Notices::getAll() as $event) {
            $args = '"' . $event['message'] . '"';
            if (isset($event['options']) && !empty($event['options'])) {
                $args .= ',' . json_encode($event['options'], JSON_UNESCAPED_UNICODE);
            }
            $notices[] = '$.bootstrapGrowl(' . $args . '); ';
        }
        ZFE_Notices::clear();

        return empty($notices)
            ? ''
            : '<script text="text/javascript">' . implode('', $notices) . '</script>';
    }
}
