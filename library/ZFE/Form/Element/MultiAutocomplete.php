<?php

/*
 * ZFE – платформа для построения редакторских интерфейсов.
 */

/**
 * Элемент формы текстовое поле на несколько значений с автодополнением.
 *
 * @category  ZFE
 */
class ZFE_Form_Element_MultiAutocomplete extends Zend_Form_Element_Xhtml
{
    /**
     * Помошник представления для элемента.
     *
     * @var string
     */
    public $helper = 'formMultiAutocomplete';
}
