<?php

/*
 * ZFE – платформа для построения редакторских интерфейсов.
 */

/**
 * Генератор HTML-тегов.
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
        $cleanedAttribs = array_filter(
            $attribs,
            fn ($val) => !in_array($val, [null, false], true),
        );
        $openTag = '<' . $name . $this->_htmlAttribs($cleanedAttribs) . '>';
        $closeTag = '</' . $name . '>';
        return $openTag . $content . $closeTag;
    }
}
