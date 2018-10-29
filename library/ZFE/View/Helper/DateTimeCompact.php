<?php

/*
 * ZFE – платформа для построения редакторских интерфейсов.
 */

/**
 * Помощник преобразования системной даты и времени в удобную для человека короткую запись.
 *
 * @category  ZFE
 */
class ZFE_View_Helper_DateTimeCompact extends Zend_View_Helper_Abstract
{
    /**
     * Преобразовать дату в короткий формат.
     *
     * @param string $dateTime
     *
     * @return string
     */
    public function dateTimeCompact($dateTime)
    {
        if ( ! $dateTime || ! $timestamp = strtotime($dateTime)) {
            return '';
        }

        $dateTag = $this->view->tag('div', ['class' => 'date'], $this->view->dateForHuman($dateTime));
        $timeTag = $this->view->tag('div', ['class' => 'time'], date('H:i:s', $timestamp));

        return $this->view->tag('div', [
            'class' => 'date-time-compact',
            'title' => $this->view->dateTime($dateTime),
        ], $dateTag . $timeTag);
    }
}
