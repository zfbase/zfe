<?php

/*
 * ZFE – платформа для построения редакторских интерфейсов.
 */

/**
 * Улучшенная версия Zend_Validate_LessThan.
 *
 * Добавлена поддержка параметра "включительно".
 *
 * @see Zend/Validate/LessThan.php
 * @see Zend/Validate/Between.php
 */
class ZFE_Validate_GreaterThan extends Zend_Validate_Abstract
{
    public const NOT_GREATER        = 'notGreaterThan';
    public const NOT_GREATER_STRICT = 'notGreaterThanStrict';

    /**
     * Сообщения об ошибках.
     *
     * @var array
     */
    protected $_messageTemplates = [
        self::NOT_GREATER        => "'%value%' is not greater than '%min%'",
        self::NOT_GREATER_STRICT => "'%value%' is not strictly greater than '%min%'",
    ];

    /**
     * Словарь параметров сообщений.
     *
     * @var array
     */
    protected $_messageVariables = [
        'min' => '_min',
    ];

    /**
     * Minimum value.
     *
     * @var float|int
     */
    protected $_min;

    /**
     * Whether to do inclusive comparisons, allowing equivalence to min and/or max.
     *
     * If false, then strict comparisons are done, and the value may equal neither
     * the min nor max options
     *
     * @var bool
     */
    protected $_inclusive;

    /**
     * Sets validator options
     * Accepts the following option keys:
     *   'min' => scalar, minimum border
     *   'inclusive' => boolean, inclusive border values.
     *
     * @param array|Zend_Config $options
     */
    public function __construct($options)
    {
        if ($options instanceof Zend_Config) {
            $options = $options->toArray();
        } elseif ( ! is_array($options)) {
            $options = func_get_args();
            $temp['min'] = array_shift($options);
            if ( ! empty($options)) {
                $temp['inclusive'] = array_shift($options);
            }

            $options = $temp;
        }

        if ( ! array_key_exists('min', $options)) {
            require_once 'Zend/Validate/Exception.php';
            throw new Zend_Validate_Exception("Missing option 'min'");
        }

        if ( ! array_key_exists('inclusive', $options)) {
            $options['inclusive'] = false;
        }

        $this->setMin($options['min'])
            ->setInclusive($options['inclusive'])
        ;
    }

    /**
     * Returns the min option.
     *
     * @return float|int
     */
    public function getMin()
    {
        return $this->_min;
    }

    /**
     * Sets the min option.
     *
     * @param float|int $min
     *
     * @return Zend_Validate_GreaterThan Provides a fluent interface
     */
    public function setMin($min)
    {
        $this->_min = $min;
        return $this;
    }

    /**
     * Returns the inclusive option.
     *
     * @return bool
     */
    public function getInclusive()
    {
        return $this->_inclusive;
    }

    /**
     * Sets the inclusive option.
     *
     * @param bool $inclusive
     *
     * @return Zend_Validate_Between Provides a fluent interface
     */
    public function setInclusive($inclusive)
    {
        $this->_inclusive = $inclusive;
        return $this;
    }

    /**
     * Defined by Zend_Validate_Interface.
     *
     * Returns true if and only if $value is greater than min option
     *
     * @param float|int $value
     *
     * @return bool
     */
    public function isValid($value)
    {
        $this->_setValue($value);

        if ($this->_inclusive) {
            if ($this->_min > $value) {
                $this->_error(self::NOT_GREATER_STRICT);
                return false;
            }
        } else {
            if ($this->_min >= $value) {
                $this->_error(self::NOT_GREATER);
                return false;
            }
        }
        return true;
    }
}
