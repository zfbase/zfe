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
    /**
     * @param string|array<string|callable> $field
     *
     * @see ZFE_Model_AbstractRecord::getViewFields()
     */
    public function viewValue(AbstractRecord $item, $field)
    {
        $modelName = get_class($item);
        $table = $item->getTable();

        switch (gettype($field)) {
            case 'string':
                $viewFields = $modelName::getViewFields();
                if (!array_key_exists($field, $viewFields)) {
                    return;
                }

                if (is_string($viewFields[$field])) {
                    $options = ['field' => $field];
                } else {
                    $options = $viewFields[$field];
                    if (!array_key_exists('field', $options)) {
                        $options['field'] = $field;
                    }
                }
            break;
            case 'array':
                $options = $field;
            break;
            default:
                throw new ZFE_View_Helper_Exception('$field должен быть строкой (названием поля) или массивом настроек');
        }

        if (array_key_exists('hasValue', $options)) {
            if (!($options['hasValue'])($item)) {
                return;
            }
        } elseif (array_key_exists('field', $options)) {
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
        } else {
            throw new ZFE_View_Helper_Exception('Необходимо указать базовое поле (field) или определить функцию проверки заполненности (hasValue)');
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
            $html .= ZFE_Utilities::shortenText(
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
