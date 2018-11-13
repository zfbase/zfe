<?php

/*
 * ZFE – платформа для построения редакторских интерфейсов.
 */

/**
 * Вывод нотификаций.
 *
 * @category  ZFE
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

        $session = new Zend_Session_Namespace('Notices');

        if ( ! isset($session->events) || empty($session->events)) {
            return '';
        }

        foreach ($session->events as $event) {
            $args = '"' . $event['message'] . '"';
            if (isset($event['options']) && ! empty($event['options'])) {
                $args .= ',' . json_encode($event['options'], JSON_UNESCAPED_UNICODE);
            }
            $notices[] = '$.bootstrapGrowl(' . $args . '); ';
        }
        $session->events = null;

        return empty($notices)
            ? ''
            : '<script text="text/javascript">' . implode('', $notices) . '</script>';
    }
}
