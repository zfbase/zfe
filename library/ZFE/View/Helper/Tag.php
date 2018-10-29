<?php

/*
 * ZFE – платформа для построения редакторских интерфейсов.
 */

/**
 * Генератор HTML-тегов.
 *
 * @category  ZFE
 */
class ZFE_View_Helper_Tag extends Zend_View_Helper_HtmlElement
{
    /**
     * Render HTML tags.
     *
     * @param string         $name
     * @param array|string[] $attribs
     * @param string         $content
     *
     * @return string
     */
    public function tag($name, $attribs = [], $content = '')
    {
        $openTag = '<' . $name . $this->_htmlAttribs($attribs) . '>';
        $closeTag = '</' . $name . '>';
        return $openTag . $content . $closeTag;
    }
}
