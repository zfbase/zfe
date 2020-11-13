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
    public function viewRow(?AbstractRecord $item, string $field, string $class = null)
    {
        if ($item === null) {
            return;
        }

        $modelName = get_class($item);
        $viewFields = $modelName::getViewFields();
        if (array_key_exists($field, $viewFields)) {
            $table = $item->getTable();
            $options = is_string($viewFields[$field])
                ? ['field' => $field]
                : $viewFields[$field];

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
}