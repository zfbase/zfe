<?php

/*
 * ZFE – платформа для построения редакторских интерфейсов.
 */

/**
 * Помощник для сокращения стандартного геттера хеша для возврата к результатам поиска.
 * 
 * @property ZFE_View $view
 */
class ZFE_View_Helper_BackToSearchHash extends Zend_View_Helper_Abstract
{
    public function backToSearchHash(string $label = 'К результатам поиска'): string
    {
        return $this->view->hopsHistory()->getDownHash($label, '?', ['totalResults' => ZFE_Paginator::getInstance()->getNumResults()]);
    }
}
