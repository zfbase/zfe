<?php

/*
 * ZFE – платформа для построения редакторских интерфейсов.
 */

/**
 * Элемент формы для перечисления дат.
 */
class ZFE_View_Helper_FormDatelist extends Zend_View_Helper_FormElement
{
    public function formDatelist($name, $value = null, $attribs = null)
    {
        $info = $this->_getInfo($name, $value, $attribs);
        extract($info);

        // Определяем состояние флага отключения элемента
        $disabled = '';
        if ($disable) {
            $disabled = ' disabled="disabled"';
        }

        $readonly = !empty($attribs['readonly']);

        // Определяем перечень классов
        if (isset($attribs['class'])) {
            $classes = explode(' ', $attribs['class']);
            if (!in_array('form-control', $classes)) {
                array_unshift($classes, 'form-control');
            }
            $attribs['class'] = implode(' ', $classes);
        } else {
            $attribs['class'] = 'form-control';
        }

        $curValues = '';

        if (is_array($value)) {
            foreach ($value as $item) {
                if (empty($item)) {
                    continue;
                }

                $date = strtotime($item);
                $hidden = $disable || $readonly ? '' : $this->_hidden($name . '[]', $item);
                $label = '<div class="title">' . ($date ? date('d.m.Y', $date) : $item) . '</div>';
                $removeBtn = $disable || $readonly ? '' : '<div class="btn btn-remove"><span class="glyphicon glyphicon-remove"></span></div>';
                $curValues .= '<div class="linked-entity">' . $hidden . $label . $removeBtn . '</div>';
            }
        }

        $input = '<input type="date"'
               . ' id="' . $this->view->escape($id) . '"'
               . $disabled
               . $this->_htmlAttribs($attribs)
               . $this->getClosingBracket();

        $btnSet = '<span class="input-group-btn">'
                . '<button class="btn btn-default btn-data-add" type="button">'
                . '<span class="glyphicon glyphicon-plus"></span>'
                . '</button>'
                . '</span>';

        return '<div class="datelist">'
             . ($disable || $readonly ? '' : '<input type="hidden" name="' . $name . '[]" />')  // Псевдо значение для передачи данных о наличии элемента в форме
             . '<div class="datelist-entities">' . $curValues . '</div>'
             . ($disable || $readonly ? '' : '<div class="input-group">' . $input . $btnSet . '</div>')
             . '</div>';
    }
}
