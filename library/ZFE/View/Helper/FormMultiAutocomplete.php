<?php

/*
 * ZFE – платформа для построения редакторских интерфейсов.
 */

/**
 * Элемент формы автокомплита нескольких значений.
 *
 * Для использование своего JS-обработчика на клиенте, необходимо передать класс custom-engine.
 */
class ZFE_View_Helper_FormMultiAutocomplete extends Zend_View_Helper_FormElement
{
    /**
     * Сгенерировать элемент автокомплита нескольких значений.
     *
     * @param string $name
     * @param array  $value
     * @param array  $attribs
     *
     * @return string
     */
    public function formMultiAutocomplete($name, $value = null, $attribs = null)
    {
        $info = $this->_getInfo($name, $value, $attribs);
        extract($info);

        // Определяем возможность создания новых элементов
        if (isset($attribs['canCreate'])) {
            $canCreate = $attribs['canCreate'];
            unset($attribs['canCreate']);
        } else {
            $canCreate = true;
        }
        $attribs['data-create'] = $canCreate ? 'allow' : 'deny';

        // Определяем источник данных
        $attribs['data-source'] = $attribs['source'];
        unset($attribs['source']);

        // Определяем минимальное число привязанных записей
        if (isset($attribs['min'])) {
            if (null !== $attribs['min']) {
                $attribs['data-min'] = $attribs['min'];
            }
            unset($attribs['min']);
        }

        // Определяем максимальное число привязанных записей
        if (isset($attribs['max'])) {
            if (null !== $attribs['max']) {
                $attribs['data-max'] = $attribs['max'];
            }
            unset($attribs['max']);
        }

        // Определяем состояние флага отключения элемента
        $disabled = '';
        if ($disable) {
            $disabled = ' disabled="disabled"';
        }

        // Определяем перечень классов
        if (isset($attribs['class'])) {
            $classes = explode(' ', $attribs['class']);
            if ( ! in_array('multiac', $classes, true)) {
                array_unshift($classes, 'multiac');
            }
            if ( ! in_array('form-control', $classes, true)) {
                array_unshift($classes, 'form-control');
            }
            $attribs['class'] = implode(' ', $classes);
        } else {
            $attribs['class'] = 'form-control multiac';
        }

        $editUrl = false;
        if (isset($attribs['editUrl'])) {
            $editUrl = $attribs['editUrl'];
            $attribs['data-edit-url'] = $editUrl;
            unset($attribs['editUrl']);
        }

        // Определяем, нужны ли ссылки с выбранных вариантов на их страницы
        $hasFormBtn = isset($attribs['data-itemform']);
        if ($hasFormBtn) {
            $itemFormUrl = ' data-itemform="' . $attribs['data-itemform'] . '"';
        } else {
            $itemFormUrl = '';
        }

        // Очищаем лишнее
        if (isset($attribs['relAlias'])) {
            unset($attribs['relAlias']);
        }

        $xhtml = '';

        if (is_array($value)) {
            $i = 1;
            $relModel = $attribs['relModel'];
            foreach ($value as $item) {
                $item_xhtml = '';
                foreach ($item as $key => $val) {
                    if ('_url' !== $key) {
                        $item_xhtml .= $this->_hidden($name . '[' . $i . '][' . $key . ']', $val);
                    }
                }
                ++$i;

                $title = isset($item['title'])
                    ? $item['title']
                    : 'Без названия';

                $editBtn = $editUrl ? '<div class="btn btn-edit">...</div>' : '';
                $formBtn = $hasFormBtn && ! $editBtn ? '<a href="' . sprintf($attribs['data-itemform'], $item['id']) . '" target="_blank" class="btn btn-form"><span class="glyphicon glyphicon-share-alt"></span></a>' : '';
                $removeBtn = $disable ? '' : '<div class="btn btn-remove"><span class="glyphicon glyphicon-remove"></span></div>';

                $item_attrs = [];

                foreach ($relModel::$autocompleteSelectCols as $col) {
                    $item_attrs["data-{$col}"] = $item[$col];
                }

                $xhtml .= '<div class="linked-entity" ' . $this->_htmlAttribs($item_attrs) . '>'
                    . '<div class="inputs">' . $item_xhtml . '</div>'
                    . '<div class="title">' . $title . '</div>'
                    . $formBtn
                    . $editBtn
                    . $removeBtn
                    . '</div>';
            }
        }

        $class = 'multiac-linked-wrap';
        if ($disable) {
            $class .= ' disabled';
        }

        $xhtml = '<div class="' . $class . '" data-name="' . $name . '"' . $itemFormUrl . '>' . $xhtml . '</div>'
               . '<div class="tt-icon-right">'
               . '<i class="glyphicon glyphicon-search"></i>'
               . '<input type="text"'
               . ' id="' . $this->view->escape($id) . '"'
               . ' name="' . $this->view->escape($name) . '"'
               . $disabled
               . $this->_htmlAttribs($attribs)
               . $this->getClosingBracket()
               . '</div>';

        return '<div class="multiac-wrap">' . $xhtml . '</div>';
    }
}
