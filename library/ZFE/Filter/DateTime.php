<?php

/*
 * ZFE – платформа для построения редакторских интерфейсов.
 */

/**
 * Фильтрация даты со временем.
 *
 * Приводит значение HTML5 элемента datetime-local (2026-10-18T21:00, 2026-10-18T21:00:30.5)
 * к формату SQL (2026-10-18 21:00:00). Прочие значения не изменяются, чтобы их отверг валидатор.
 */
class ZFE_Filter_DateTime implements Zend_Filter_Interface
{
    /**
     * Фильтровать дату со временем для HTML5 элемента.
     *
     * @param mixed $value
     *
     * @return mixed
     */
    public function filter($value)
    {
        if (is_string($value)
            && preg_match('/^(\d+-\d{2}-\d{2})[T ](\d{2}:\d{2})(:\d{2})?(\.\d+)?$/', $value, $matches)
        ) {
            return $matches[1] . ' ' . $matches[2] . (!empty($matches[3]) ? $matches[3] : ':00');
        }

        return $value;
    }
}
