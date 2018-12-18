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
class ZFE_Validate_LessThan extends Zend_Validate_Abstract
{
    const NOT_LESS        = 'notLessThan';
    const NOT_LESS_STRICT = 'notLessThanStrict';

    /**
     * Сообщения об ошибках.
     *
     * @var array
     */
    protected $_messageTemplates = [
        self::NOT_LESS        => "'%value%' is not less than '%max%'",
        self::NOT_LESS_STRICT => "'%value%' is not strictly less than '%max%'",
    ];

    /**
     * Словарь параметров сообщений.
     *
     * @var array
     */
    protected $_messageVariables = [
        'max' => '_max',
    ];

    /**
     * Maximum value.
     *
     * @var float|int
     */
    protected $_max;

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
     * Sets validator options.
     *
     * @param array|Zend_Config $options            Accepts the following option keys:
     * @param float|int         $options[max]       maximum border
     * @param bool              $options[inclusive] inclusive border values
     */
    public function __construct($options)
    {
        if ($options instanceof Zend_Config) {
            $options = $options->toArray();
        } elseif ( ! is_array($options)) {
            $options = func_get_args();
            $temp['max'] = array_shift($options);
            if ( ! empty($options)) {
                $temp['inclusive'] = array_shift($options);
            }

            $options = $temp;
        }

        if ( ! array_key_exists('max', $options)) {
            require_once 'Zend/Validate/Exception.php';
            throw new Zend_Validate_Exception("Missing option 'max'");
        }

        if ( ! array_key_exists('inclusive', $options)) {
            $options['inclusive'] = true;
        }

        $this->setMax($options['max'])
            ->setInclusive($options['inclusive'])
        ;
    }

    /**
     * Returns the max option.
     *
     * @return float|int
     */
    public function getMax()
    {
        return $this->_max;
    }

    /**
     * Sets the max option.
     *
     * @param float|int $max
     *
     * @return Zend_Validate_LessThan Provides a fluent interface
     */
    public function setMax($max)
    {
        $this->_max = $max;
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
     * Returns true if and only if $value is less than max option
     *
     * @param float|int $value
     *
     * @return bool
     */
    public function isValid($value)
    {
        $this->_setValue($value);

        if ($this->_inclusive) {
            if ($this->_max < $value) {
                $this->_error(self::NOT_LESS_STRICT);
                return false;
            }
        } else {
            if ($this->_max <= $value) {
                $this->_error(self::NOT_LESS);
                return false;
            }
        }
        return true;
    }
}
