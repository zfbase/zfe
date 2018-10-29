<?php

/*
 * ZFE – платформа для построения редакторских интерфейсов.
 */

/**
 * Элемент формы текстовое поле с автодополнением.
 *
 * @category  ZFE
 */
class ZFE_Form_Element_Autocomplete extends Zend_Form_Element_Xhtml
{
    /**
     * Помошник представления для элемента.
     *
     * @var string
     */
    public $helper = 'formAutocomplete';

    /**
     * Set element value.
     *
     * @param array $value
     *
     * @return Zend_Form_Element
     */
    public function setValue($value)
    {
        if ( ! is_array($value)) {
            return $this;
        }

        // Допускаются только значения с заголовками
        if ( ! empty($value['title'])) {
            // Значения без ID допускаются, только если
            if ( ! empty($value['id']) || $this->getAttrib('canCreate')) {
                $this->_value = $value;
            }
        }

        return $this;
    }
}
