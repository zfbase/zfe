<?php

/*
 * ZFE – платформа для построения редакторских интерфейсов.
 */

/**
 * Фильтрация времени.
 *
 * Необходима для работы элемента формы HTML5.
 */
class ZFE_Filter_Time implements Zend_Filter_Interface
{
    /**
     * Фильтровать время для HTML5 элемента.
     *
     * @param string $value
     *
     * @return null|string
     */
    public function filter($value)
    {
        if (preg_match('/^[0-2]?[0-9]:[0-5][0-9]$/', $value)) {
            return $value . ':00';
        }

        if (preg_match('/^[0-2]?[0-9]:[0-5][0-9]:[0-5][0-9]$/', $value)) {
            return $value;
        }

        return null;
    }
}
