<?php

/*
 * ZFE – платформа для построения редакторских интерфейсов.
 */

/**
 * Помощник вывода нотификаций.
 *
 * @category  ZFE
 */
class ZFE_View_Helper_Alerts extends Zend_View_Helper_Abstract
{
    /**
     * Вывести нотификации.
     *
     * @return string
     */
    public function alerts()
    {
        $alerts = [];

        $session = new Zend_Session_Namespace('Notices');

        if ( ! isset($session->events) || empty($session->events)) {
            return '';
        }

        foreach ($session->events as $event) {
            $alerts[] = $this->_makeAlert(
                $event['message'],
                isset($event['options']['type']) && ! empty($event['options']['type']) ? $event['options']['type'] : null
            );
        }
        $session->events = null;

        return implode('', $alerts);
    }

    /**
     * Составить блок нотификации.
     *
     * @param string $message
     * @param string $type
     *
     * @return string
     */
    protected function _makeAlert($message, $type = 'info')
    {
        return <<<HTML
<div class="alert alert-{$type} fade in" role="alert">
    <button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">×</span><span class="sr-only">Закрыть</span></button>
    {$message}
</div>
HTML;
    }
}
