<?php

/*
 * ZFE – платформа для построения редакторских интерфейсов.
 */

/**
 * Элемент формы для продолжительности в секундах.
 *
 * @category  ZFE
 */
class ZFE_View_Helper_FormDuration extends Twitter_Bootstrap3_View_Helper_FormText
{
    public function formDuration($name, $value = null, $attribs = null)
    {
        $attribs['placeholder'] = '00:00:00';
        $attribs['data-inputmask-mask'] = '99:99:99';

        return $this->_formText('text', $name, $value, $attribs);
    }
}
