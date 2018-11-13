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
        switch (static::$sex) {
            case static::SEX_MALE: $format = $male; break;
            case static::SEX_FEMALE: $format = $female; break;
            case static::SEX_NEUTER: $format = $neuter; break;
            default: throw new ZFE_Model_Exception('Попытка склонения по не известному роду');
        }

        return sprintf($format, $plural ? static::$namePlural : static::$nameSingular);
    }
}
