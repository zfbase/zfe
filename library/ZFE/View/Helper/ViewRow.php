<?php

/*
 * ZFE – платформа для построения редакторских интерфейсов.
 */

/**
 * Собрать строку отображения поля для страницы просмотра записи.
 *
 * @property ZFE_View $view
 */
class ZFE_View_Helper_ViewRow extends Zend_View_Helper_Abstract
{
    /**
     * @param string|array<string|callable> $field
     *
     * @see ZFE_Model_AbstractRecord::getViewFields()
     */
    public function viewRow(?AbstractRecord $item, $field, string $class = null)
    {
        if ($item === null) {
            return;
        }

        $modelName = get_class($item);

        if (is_string($field)) {
            $viewFields = $modelName::getViewFields();
            if (!array_key_exists($field, $viewFields)) {
                return;
            }

            $options = is_string($viewFields[$field])
                ? ['field' => $field]
                : $viewFields[$field];
        } elseif (is_array($field)) {
            $options = $field;
        } else {
            throw new ZFE_View_Helper_Exception('$field должен быть строкой (названием поля) или массивом настроек');
        }

        $table = $item->getTable();

        if ($table->hasRelation($options['field'])) {
            if (!$item->{$options['field']}->count()) {
                return;
            }
        }

        if ($table->hasColumn($options['field'])) {
            if (empty($item->{$options['field']})) {
                return;
            }
        }

        $html  = "<tr class=\"{$class}\"><th>";
        $html .= $options['title'] ?? $modelName::getFieldName($options['field']);
        $html .= '</th><td>';
        $html .= $this->view->viewValue($item, $field);
        $html .= '</td></tr>';
        return $html;
    }
}