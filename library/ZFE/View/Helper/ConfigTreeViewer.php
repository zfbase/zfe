<?php

/*
 * ZFE – платформа для построения редакторских интерфейсов.
 */

/**
 * Отобразить дерево со сворачивающимися уровнями.
 */
class ZFE_View_Helper_ConfigTreeViewer extends Zend_View_Helper_Abstract
{
    /**
     * Отобразить дерево со сворачивающимися уровнями.
     *
     * @param array $tree
     *
     * @return string
     */
    public function configTreeViewer(array $tree)
    {
        return $this->view->tag('ul', ['id' => 'DevelConfigViewer_Tree'], $this->level($tree));
    }

    protected function level(array $config)
    {
        $rows = [];
        foreach ($config as $name => $value) {
            $rows[$name] = $this->view->tag('li', [], $this->name($name) . ' ' . $this->value($value));
        }
        ksort($rows);
        return implode('', $rows);
    }

    protected function name(string $name)
    {
        return $this->view->tag('label', [], $name);
    }

    protected function value($value)
    {
        if (is_array($value)) {
            if (count($value) !== 1) {
                return $this->view->tag('ul', [], $this->level($value));
            }
            
            foreach ($value as $name => $val) {
                return $this->name($name) . ' ' . $this->value($val);
            }
        }

        return ($value === null)
            ? $this->view->tag('pre', [], 'NULL')
            : $this->view->tag('span', [], $this->type($value)) . ' ' . $this->view->tag('pre', [], $value);
    }

    protected function type($value)
    {
        if (is_string($value)) {
            return 'string(' . strlen($value) . ')';
        }

        return gettype($value);
    }
}
