<?php

/*
 * ZFE – платформа для построения редакторских интерфейсов.
 */

abstract class ZFE_Form_InlinePlus extends ZFE_Form
{
    public const DISPOSITION_INLINE_PLUS = 'inline-plus';

    public static $_dispositionClasses = [
        self::DISPOSITION_HORIZONTAL => 'form-horizontal',
        self::DISPOSITION_VERTICAL => 'form-vertical',
        self::DISPOSITION_INLINE => 'form-inline',
        self::DISPOSITION_INLINE_PLUS => 'form-inline-plus',
    ];

    protected $_disposition = self::DISPOSITION_INLINE_PLUS;
}
