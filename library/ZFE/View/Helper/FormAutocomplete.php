<?php

/*
 * ZFE – платформа для построения редакторских интерфейсов.
 */

/**
 * Элемент формы автокомплита одного значения.
 *
 * Для использование своего JS-обработчика на клиенте, необходимо передать класс custom-engine.
 */
class ZFE_View_Helper_FormAutocomplete extends Zend_View_Helper_FormElement
{
    /**
     * Сгенерировать элемент автокомплита одного значения.
     *
     * @param string $name
     * @param array  $value
     * @param array  $attribs
     *
     * @return string
     */
    public function formAutocomplete($name, $value = null, $attribs = null)
    {
        $info = $this->_getInfo($name, $value, $attribs);
        extract($info);

        // Определяем возможность создания новых элементов
        if (isset($attribs['canCreate'])) {
            $canCreate = (bool) $attribs['canCreate'];
            unset($attribs['canCreate']);
        } else {
            $canCreate = true;
        }
        $create = $canCreate ? 'allow' : 'deny';

        // Определяем источник данных
        if (empty($attribs['source']) && !empty($attribs['relModel'])) {
            $attribs['source'] = $attribs['relModel']::getAutocompleteUrl();
        }
        if (empty($attribs['data-source']) && !empty($attribs['source'])) {
            $attribs['data-source'] = $attribs['source'];
            unset($attribs['source']);
        }

        // Определяем состояние флага отключения элемента
        if ($disable) {
            $attribs['disabled'] = 'disabled';
        }

        // Определяем перечень классов
        if (isset($attribs['class'])) {
            $classes = explode(' ', $attribs['class']);
            if (!in_array('autocomplete', $classes)) {
                array_unshift($classes, 'autocomplete');
            }
            if (!in_array('form-control', $classes)) {
                array_unshift($classes, 'form-control');
            }
            $attribs['class'] = implode(' ', $classes);
        } else {
            $attribs['class'] = 'form-control autocomplete';
        }

        $storage = [];
        $fields = ['id', 'title'];
        if (isset($attribs['relModel']) && class_exists($attribs['relModel'])) {
            $fields = array_merge($fields, ($attribs['relModel'])::$autocompleteSelectCols);
        } else if (isset($attribs['relAlias']) && class_exists($attribs['relAlias'])) {
            $fields = array_merge($fields, ($attribs['relAlias'])::$autocompleteSelectCols);
        }
        foreach ($fields as $field) {
            $storage[] = $this->_hidden($name . '[' . $field  . ']', $value[$field] ?? null);
        }

        // Очищаем лишнее
        if (isset($attribs['relAlias'])) {
            unset($attribs['relAlias']);
        }

        if (isset($attribs['relModel'])) {
            $attribs['data-limit'] = ($attribs['relModel'])::$acLimit;
        }

        $searchIcon = $this->view->tag('i', ['class' => 'glyphicon glyphicon-menu-down']);
        $separator = $this->view->tag('i', ['class' => 'tt-separator']);
        $clearIcon = $this->view->tag('i', ['class' => 'glyphicon glyphicon-remove clear']);
        $searchInput = $this->view->tag('input', $attribs + [
            'type' => 'text',
            'id' => $id,
            'name' => $name,
            'value' => $value['title'],
            'data-create' => $create,
        ]);
        $searchPackClass = 'tt-icon-right' . ($disable ? ' tt-disabled' : '');
        $searchPack = $this->view->tag('div', ['class' => $searchPackClass], $clearIcon . $separator . $searchIcon . $searchInput);

        $helpIcon = $this->view->tag('i', ['class' => 'glyphicon glyphicon-warning-sign']);
        $helpBlock = $this->view->tag(
            'span',
            ['class' => 'help-block will-be-created'],
            $helpIcon . ' Будет создана запись'
        );

        return $this->view->tag(
            'div',
            ['id' => $id . '-wrap', 'class' => 'autocomplete-wrap'],
            implode('', $storage) . $searchPack . $helpBlock
        );
    }
}
