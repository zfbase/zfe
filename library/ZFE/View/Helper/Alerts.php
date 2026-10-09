<?php

/*
 * ZFE – платформа для построения редакторских интерфейсов.
 */

/**
 * Помощник вывода нотификаций.
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

        foreach (ZFE_Notices::getAll() as $event) {
            $alerts[] = $this->_makeAlert(
                $event['message'],
                isset($event['options']['type']) && !empty($event['options']['type']) ? $event['options']['type'] : null
            );
        }
        ZFE_Notices::clear();

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
<div class="alert alert-{$type} fade show" role="alert">
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Закрыть"></button>
    {$message}
</div>
HTML;
    }
}
