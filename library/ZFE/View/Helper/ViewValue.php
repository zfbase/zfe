<?php

/*
 * ZFE – платформа для построения редакторских интерфейсов.
 */

/**
 * Получить значение поля для страницы просмотра записи.
 *
 * @property ZFE_View $view
 */
class ZFE_View_Helper_ViewValue extends Zend_View_Helper_Abstract
{
    public function viewValue(AbstractRecord $item, string $field)
    {
        $modelName = get_class($item);
        $viewFields = $modelName::getViewFields();
        if (!array_key_exists($field, $viewFields)) {
            return;
        }

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

        $html = '';

        if (!empty($options['prefix'])) {
            $html .= $options['prefix'];
        }

        if (!empty($options['viewHelper'])) {
            $html .= $this->view->{$options['viewHelper']}($item->{$options['field']});
        } elseif (!empty($options['viewMethod'])) {
            $html .= ($options['viewMethod'])($item);
        } elseif ($table->hasRelation($options['field'])) {
            $html .= $this->view->showTitles($item->{$options['field']});
        } elseif ($table->hasColumn($options['field'])) {
            $html .= ZFE::shortenText(
                $this->view->autoFormat(
                    $item->{$options['field']},
                    $options['field'],
                    $modelName
                ),
                400
            );
        }

        if (!empty($options['postfix'])) {
            $html .= $options['postfix'];
        }

        return $html;
    }
}
