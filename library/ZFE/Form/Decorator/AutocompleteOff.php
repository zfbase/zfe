<?php

/*
 * ZFE – платформа для построения редакторских интерфейсов.
 */

/**
 * Декоратор, который решит проблему с autocomplete="off" там, где это потребуется.
 *
 * @category  ZFE
 */
class ZFE_Form_Decorator_AutocompleteOff extends Zend_Form_Decorator_Abstract
{
    /**
     * Добавить скрытый элемент для блокировки автозаполнения.
     *
     * @param string $content
     *
     * @return string
     */
    public function render($content)
    {
        $element = $this->getElement();

        if ('off' !== $element->getAttrib('autocomplete')) {
            return $content;
        }

        $name = $element->getName();
        $hidden = '<input type="text" name="' . $name . '" style="display:none" />';
        return $hidden . $content;
    }
}
