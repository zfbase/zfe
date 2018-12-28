<?php

/*
 * ZFE – платформа для построения редакторских интерфейсов.
 */

/**
 * Задать заголовок страницы.
 */
class ZFE_View_Helper_Title extends Zend_View_Helper_Abstract
{
    /**
     * Задать заголовок страницы.
     *
     * @param mixed $title
     *
     * @return string
     */
    public function title($title)
    {
        $this->view->headTitle($title);
        return $title;
    }
}
