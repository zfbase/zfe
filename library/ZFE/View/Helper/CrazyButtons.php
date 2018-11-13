<?php

/*
 * ZFE – платформа для построения редакторских интерфейсов.
 */

/**
 * Помощник вывода кнопки, объединяющей несколько кнопок.
 *
 * @category  ZFE
 */
class ZFE_View_Helper_CrazyButtons extends Zend_View_Helper_Abstract
{
    /**
     * Вывести кнопки.
     *
     * В зависимости от переданного числа конфигураций кнопок будет отображена
     * (1) группа кнопок, где первая отображается всегда, а остальные выпадают,
     * (2) одна обычная кнопка,
     * (3) или будет ничего не выведено.
     *
     * @param array  $buttons
     * @param string $buttons[][label] название ссылки
     * @param string $buttons[][url]   адрес ссылки
     * @param string $buttons[][ico]   класс иконки (если не указан иконки не будет добавлено)
     * @param mixed  $class
     *
     * @return string
     */
    public function crazyButtons($buttons, $class = 'btn btn-default')
    {
        if (count($buttons) > 1) {
            return $this->multi($buttons, $class);
        }

        if (1 === count($buttons)) {
            return $this->one($buttons[0], $class);
        }

        return '';
    }

    /**
     * Вывести кнопку с выпадающими дополнительными кнопками.
     *
     * @param array  $buttons
     * @param string $class
     *
     * @return string
     */
    public function multi($buttons, $class = 'btn btn-default')
    {
        $parentBtn = $this->one(array_shift($buttons));

        $caret = $this->view->tag('span', ['class' => 'caret']);
        $label = $this->view->tag('span', ['class' => 'sr-only'], 'развернуть/свернуть');

        $dropdownBtn = $this->view->tag('div', [
            'class' => $class . ' dropdown-toggle',
            'data-toggle' => 'dropdown',
            'aria-haspopup' => 'true',
            'aria-expanded' => 'false',
        ], $caret . $label);

        $childrenBtns = [];
        foreach ($buttons as $button) {
            $childrenBtns[] = $this->view->tag(
                'li',
                ['class' => isset($button['class']) ? $button['class'] : null],
                $this->one($button, '')
            );
        }

        $dropdownMenu = $this->view->tag('ul', ['class' => 'dropdown-menu'], implode('', $childrenBtns));

        return $this->view->tag(
            'div',
            ['class' => 'btn-group'],
            $parentBtn . $dropdownBtn . $dropdownMenu
        );
    }

    /**
     * Вывести обычную кнопку.
     *
     * @param array  $button
     * @param string $button[label] название ссылки
     * @param string $button[url]   адрес ссылки
     * @param string $button[ico]   класс иконки (если не указан иконки не будет добавлено)
     * @param string $class
     *
     * @return string
     */
    public function one(array $button, $class = 'btn btn-default')
    {
        $label = $button['label'];

        if ( ! empty($button['ico'])) {
            $ico = $this->view->tag('span', ['class' => $button['ico']]);
        } else {
            $ico = '';
        }

        return $this->view->tag('a', [
            'href' => $button['url'],
            'class' => $class,
        ], $ico . ' ' . $label);
    }
}
