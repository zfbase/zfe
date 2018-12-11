<?php

/*
 * ZFE – платформа для построения редакторских интерфейсов.
 */

/**
 * Валидатор URL.
 *
 * @author    Diego Perini <diego.perini@gmail.com>
 * @author    Ilya Serdyuk <ilya@serdyuk.pro>
 */
class ZFE_Validate_Url extends Zend_Validate_Abstract
{
    public const INVALID = 'urlInvalid';

    /**
     * Сообщения об ошибках.
     *
     * @var array
     */
    protected $_messageTemplates = [
        self::INVALID => "'%value%' не является корректным URL",
    ];

    /**
     * Возвращает true, если передан URL и false в остальных случаях.
     *
     * @param mixed $value
     *
     * @return bool
     */
    public function isValid($value)
    {
        // https://gist.github.com/dperini/729294
        $pattern = '_^'
                   // protocol identifier
                 . '(?:(?:https?|ftp)://)'
                   // user:pass authentication
                 . '(?:\S+(?::\S*)?@)?'
                 . '(?:'
                   // IP address exclusion
                   // private & local networks
                 . '(?!(?:10|127)(?:\.\d{1,3}){3})'
                 . '(?!(?:169\.254|192\.168)(?:\.\d{1,3}){2})'
                 . '(?!172\.(?:1[6-9]|2\d|3[0-1])(?:\.\d{1,3}){2})'
                   // IP address dotted notation octets
                   // excludes loopback network 0.0.0.0
                   // excludes reserved space >= 224.0.0.0
                   // excludes network & broadcast addresses
                   // (first & last IP address of each class)
                 . '(?:[1-9]\d?|1\d\d|2[01]\d|22[0-3])'
                 . '(?:\.(?:1?\d{1,2}|2[0-4]\d|25[0-5])){2}'
                 . '(?:\.(?:[1-9]\d?|1\d\d|2[0-4]\d|25[0-4]))'
                 . '|'
                   // host name
                 . '(?:(?:[a-z\x{00a1}-\x{ffff}0-9]-*)*[a-z\x{00a1}-\x{ffff}0-9]+)'
                   // domain name
                 . '(?:\.(?:[a-z\x{00a1}-\x{ffff}0-9]-*)*[a-z\x{00a1}-\x{ffff}0-9]+)*'
                   // TLD identifier
                 . '(?:\.(?:[a-z\x{00a1}-\x{ffff}]{2,}))'
                 . ')'
                   // port number
                 . '(?::\d{2,5})?'
                   // resource path
                 . '(?:/\S*)?'
                 . '$_iuS';
        if ( ! preg_match($pattern, $value)) {
            $this->_setValue((string) $value);
            $this->_error(self::INVALID);
            return false;
        }

        return true;
    }
}
