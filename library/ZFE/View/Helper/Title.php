<?php

/*
 * ZFE – платформа для построения редакторских интерфейсов.
 */

/**
 * Задать заголовок страницы.
 * 
 * @property ZFE_View $view
 */
class ZFE_View_Helper_Title extends Zend_View_Helper_Abstract
{
    /**
     * Задать заголовок страницы.
     *
     * @param string $title
     *
     * @return string
     */
    public function title($title)
    {
        $this->view->headTitle($title);
        return $title;
    }
}
