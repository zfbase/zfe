<?php

/*
 * ZFE – платформа для построения редакторских интерфейсов.
 */

/**
 * Расширение группы элементов.
 */
class ZFE_Form_Decorator_Fieldset extends Zend_Form_Decorator_Fieldset
{
    public function getOptions()
    {
        $options = parent::getOptions();
        if (null !== ($element = $this->getElement())) {
            $description = $element->getDescription();
            $options['description'] = $description;
            $this->setOptions($options);
        }
        return $options;
    }
}
