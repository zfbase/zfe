<?php

/*
 * ZFE – платформа для построения редакторских интерфейсов.
 */

class ZFE_Form_Decorator_HorizontalControls extends Twitter_Bootstrap3_Form_Decorator_HorizontalControls
{
    public function render($content)
    {
        $element = $this->getElement();
        $class = ' ' . $this->getOption('class');

        if (in_array(mb_substr($element->getType(), -10), ['_FileImage', '_FileAudio'])) {
            $class .= ' form-control-static';
        }

        $class = trim($class);
        if (!empty($class)) {
            $this->setOption('class', $class);
        }

        return parent::render($content);
    }
}
