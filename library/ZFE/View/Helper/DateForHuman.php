<?php

/*
 * ZFE – платформа для построения редакторских интерфейсов.
 */

/**
 * Человеческое написание даты.
 */
class ZFE_View_Helper_DateForHuman extends Zend_View_Helper_Abstract
{
    /**
     * Словарь именованных дат
     *
     * @var array[string]
     */
    protected static $_daysOfWeek = [
        0 => 'в воскресенье',
        1 => 'в понедельник',
        2 => 'во вторник',
        3 => 'в среду',
        4 => 'в четверг',
        5 => 'в пятницу',
        6 => 'в субботу',
    ];

    /**
     * Человеческое написание даты.
     *
     * @param DateTime|string $date
     *
     * @return string
     */
    public function dateForHuman($date)
    {
        if (!$date || !$timestamp = strtotime($date)) {
            return '';
        }

        $day = strtotime('midnight', $timestamp);
        $days_ago = floor((time() - $day) / 86400);

        if (-2 === $days_ago) {
            return 'послезавтра';
        }

        if (-1 === $days_ago) {
            return 'завтра';
        }

        if (0 === $days_ago) {
            return 'сегодня';
        }

        if (1 === $days_ago) {
            return 'вчера';
        }

        if (2 === $days_ago) {
            return 'позавчера';
        }

        if (7 > $days_ago) {
            return self::$_daysOfWeek[date('w', $timestamp)];
        }

        return date('d.m.Y', $timestamp);
    }
}
