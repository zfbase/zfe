<?php

/*
 * ZFE – платформа для построения редакторских интерфейсов.
 */

/**
 * Валидатор: Дата не менее указанной.
 *
 * @see https://gist.github.com/jgornick/200256
 *
 * @author    Joe Gornick <joe@joegornick.com>
 * @author    Ilya Serdyuk <ilya@serdyuk.pro>
 */
class ZFE_Validate_Date_LessThan extends My_Validate_Date_Abstract
{
    const NOT_LESS = 'notLessThan';

    /**
     * Sets validator options.
     *
     * @param string|Zend_Date   $date
     * @param string             $format
     * @param string|Zend_Locale $locale
     * @param bool               $orEqual
     */
    public function __construct($date, $format = null, $locale = null, $orEqual = false)
    {
        $this->_messageTemplates[self::NOT_LESS] = "'%value%' is not less than '%date%'";
        parent::__construct($date, $format, $locale, $orEqual);
    }

    /**
     * Defined by Zend_Validate_Interface.
     *
     * Returns true if $value is a valid date and is greater than the specified
     * $date. If optional $format or $locale is set the date format is checked
     * according to Zend_Date, see Zend_Date::isDate()
     *
     * @param string|Zend_Date $value
     * @param array            $context
     *
     * @return bool
     */
    public function isValid($value, $context = null)
    {
        if ( ! $this->_setValue($value)) {
            return false;
        }
        if ( ! $this->_parseDate($context)) {
            return false;
        }

        $compare = $this->_value->compare($this->_date);
        $compare = ($this->_orEqual) ? ($compare <= 0) : ($compare < 0);

        if ( ! $compare) {
            $this->_error(self::NOT_LESS);
            return false;
        }

        return true;
    }
}
