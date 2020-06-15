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
        if ($disable) {
            $attribs['disabled'] = 'disabled';
        }

        // Определяем перечень классов
        if (isset($attribs['class'])) {
            $classes = explode(' ', $attribs['class']);
            if (!in_array('multiac', $classes)) {
                array_unshift($classes, 'multiac');
            }
            if (!in_array('form-control', $classes)) {
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

        $type = 'default';
        if (isset($attribs['type'])) {
            $type = $attribs['type'];
            unset($attribs['type']);
        }

        // Определяем, нужны ли ссылки с выбранных вариантов на их страницы
        $hasFormBtn = isset($attribs['data-item-form']);
        if ($hasFormBtn) {
            $itemFormUrl = ' data-item-form="' . $attribs['data-item-form'] . '"';
        } else {
            $itemFormUrl = '';
        }

        // Очищаем лишнее
        if (isset($attribs['relAlias'])) {
            unset($attribs['relAlias']);
        }

        if (isset($attribs['relModel'])) {
            $attribs['data-limit'] = ($attribs['relModel'])::$acLimit;
        }

        $class = 'multiac-linked-wrap';
        if ($disable) {
            $class .= ' disabled';
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

                $editBtn = $editUrl && !$disable ? '<div class="btn btn-edit">...</div>' : '';
                $formBtn = $hasFormBtn && !$editBtn ? '<a href="' . sprintf($attribs['data-item-form'], $item['id']) . '" target="_blank" class="btn btn-form"><span class="glyphicon glyphicon-share-alt"></span></a>' : '';
                $removeBtn = $disable ? '' : '<div class="btn btn-remove"><span class="glyphicon glyphicon-remove"></span></div>';

                $item_attrs = [];

                foreach ($relModel::$autocompleteSelectCols as $col) {
                    $item_attrs["data-{$col}"] = $item[$col];
                }

                $xhtml .= '<div class="linked-entity" ' . $this->_htmlAttribs($item_attrs) . '>'
                    . '<div class="inputs">' . $item_xhtml . '</div>'
                    . '<div class="title">' . ($item['title'] ?? 'Без названия') . '</div>'
                    . $formBtn
                    . $editBtn
                    . $removeBtn
                    . '</div>';
            }
        }
        $xhtml = '<div class="' . $class . '" data-name="' . $name . '"' . $itemFormUrl . '>' . $xhtml . '</div>';

        $searchIcon = $this->view->tag('i', ['class' => 'glyphicon glyphicon-menu-down']);
        $separator = $this->view->tag('i', ['class' => 'tt-separator']);
        $searchInput = $this->view->tag('input', $attribs + [
            'type' => 'text',
            'id' => $id,
            'name' => $name,
        ]);
        $searchPackClass = 'tt-icon-right' . ($disable ? ' tt-disabled' : '');
        $searchPack = $this->view->tag('div', ['class' => $searchPackClass], $separator . $searchIcon . $searchInput);

        switch ($type) {
            case 'linked-left':
                return '<div class="multiac-wrap multiac-left">' . $searchPack . $xhtml . '</div>';
            case 'linked-right':
                return '<div class="multiac-wrap multiac-right">' . $searchPack . $xhtml . '</div>';
            default:
                return '<div class="multiac-wrap">' . $xhtml . $searchPack . '</div>';
        }
    }
}
