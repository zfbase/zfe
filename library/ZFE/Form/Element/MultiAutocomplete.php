<?php

/*
 * ZFE – платформа для построения редакторских интерфейсов.
 */

/**
 * Элемент формы текстовое поле на несколько значений с автодополнением.
 */
class ZFE_Form_Element_MultiAutocomplete extends Zend_Form_Element_Xhtml
{
    /**
     * Помощник представления для элемента.
     *
     * @var string
     */
    public $helper = 'formMultiAutocomplete';
}
