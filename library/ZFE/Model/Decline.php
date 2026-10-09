<?php

/*
 * ZFE – платформа для построения редакторских интерфейсов.
 */

/**
 * Склонения сообщений.
 */
trait ZFE_Model_Decline
{
    public static function decline($male, $female, $neuter, $plural = false)
    {
        switch (static::$gender) {
            case static::GENDER_MASCULINE:
                $format = $male;
                break;
            case static::GENDER_FEMININE:
                $format = $female;
                break;
            case static::GENDER_NEUTER:
                $format = $neuter;
                break;
            default:
                throw new ZFE_Model_Exception('Попытка склонения по не известному роду');
        }

        return sprintf($format, $plural ? static::$namePlural : static::$nameSingular);
    }
}
