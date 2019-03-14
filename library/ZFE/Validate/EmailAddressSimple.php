<?php

/*
 * ZFE – платформа для построения редакторских интерфейсов.
 */

/**
 * Валидатор адресов электронной почты.
 *
 * @author Ilya Serdyuk <ilya@serdyuk.pro>
 */
class ZFE_Validate_EmailAddressSimple extends Zend_Validate_Abstract
{
    const INVALID = 'emailAddressSimpleInvalid';

    /**
     * Сообщения об ошибках.
     *
     * @var array
     */
    protected $_messageTemplates = [
        self::INVALID => "'%value%' не является корректным адресом эл. почты",
    ];

    /**
     * Проверяет похожа ли переданная строка на адрес эл. почты.
     *
     * @param string $value
     *
     * @return bool
     */
    public function isValid($value)
    {
        if (false === filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $this->_error(self::INVALID);
            return false;
        }

        return true;
    }
}
