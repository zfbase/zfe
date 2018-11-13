<?php

/*
 * ZFE – платформа для построения редакторских интерфейсов.
 */

/**
 * Clearfix для использования в формах.
 */
class ZFE_View_Helper_FormClearfix extends Zend_View_Helper_FormElement
{
    public function formClearfix($name, $value = null, $attribs = null)
    {
        return '<div class="clearfix"></div>';
    }
}
