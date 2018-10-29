<?php

/*
 * ZFE – платформа для построения редакторских интерфейсов.
 */

/**
 * Группа элементов формы.
 *
 * @category  ZFE
 */
class ZFE_View_Helper_Fieldset extends Zend_View_Helper_FormElement
{
    /**
     * Render HTML form.
     *
     * @param string $name    Form name
     * @param string $content Form content
     * @param array  $attribs HTML form attributes
     *
     * @return string
     */
    public function fieldset($name, $content, $attribs = null)
    {
        $info = $this->_getInfo($name, $content, $attribs);
        extract($info);

        // get legend
        $legend = '';
        if (isset($attribs['legend'])) {
            $legendString = trim($attribs['legend']);
            if ( ! empty($legendString)) {
                $legend = (empty($attribs['legend_class']) ? '<legend>' : '<legend class="' . $attribs['legend_class'] . '">')
                        . (($escape) ? $this->view->escape($legendString) : $legendString)
                        . (empty($attribs['description']) ? '' : ' <small>' . $this->view->escape($attribs['description']) . '</small>')
                        . '</legend>' . PHP_EOL;
            }
            unset($attribs['legend']);
        }

        if ( ! empty($id)) {
            $attribs['id'] = $id;
        }

        return $this->view->tag('fieldset', $attribs, $legend . $content);
    }
}
