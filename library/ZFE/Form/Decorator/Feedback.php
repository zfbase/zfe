<?php

/*
 * ZFE – платформа для построения редакторских интерфейсов.
 */

/**
 * Отключил отображение иконки статуса валидации.
 */
class ZFE_Form_Decorator_Feedback extends Twitter_Bootstrap3_Form_Decorator_Feedback
{
    /**
     * {@inheritdoc}
     */
    public function render($content)
    {
        $content = parent::render($content);

        $element = $this->getElement();
        $container = $element->getDecorator('Container');
        if (!empty($container)) {
            $classes = explode(' ', $container->getOption('class'));
            $container->setOption('class', implode(' ', array_diff($classes, ['has-feedback'])));
        }

        return $content;
    }
}
