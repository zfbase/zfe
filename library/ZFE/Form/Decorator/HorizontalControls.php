<?php

/*
 * ZFE – платформа для построения редакторских интерфейсов.
 */

class ZFE_Form_Decorator_HorizontalControls extends Twitter_Bootstrap5_Form_Decorator_HorizontalControls
{
    /**
     * Отступ поля файла по шкале отступов темы ZFE (7.5px, как .form-control-static в BS3).
     *
     * @var string
     */
    protected $_fileControlsClass = 'pt-3';

    protected function _isFileElement($element)
    {
        return parent::_isFileElement($element)
            || in_array(mb_substr($element->getType(), -10), ['_FileImage', '_FileAudio']);
    }
}
