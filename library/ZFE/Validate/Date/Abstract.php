<?php

/*
 * ZFE – платформа для построения редакторских интерфейсов.
 */

/**
 * Абстрактный валидатор сравнения дат.
 *
 * @see https://gist.github.com/jgornick/200256
 *
 * @author    Joe Gornick <joe@joegornick.com>
 * @author    Ilya Serdyuk <ilya@serdyuk.pro>
 */
class ZFE_Validate_Date_Abstract extends Zend_Validate_Abstract
{
    // Zend_Validate_Date Message Constants
    const INVALID = 'dateInvalid';
    const NOT_YYYY_MM_DD = 'dateNotYYYY-MM-DD';
    const INVALID_DATE = 'dateInvalidDate';
    const FALSEFORMAT = 'dateFalseFormat';

    // Zend_Validate_Date Custom Message Constants
    const FIELD_INVALID = 'dateInvalidField';
    const FIELD_NOT_YYYY_MM_DD = 'dateNotYYYY-MM-DDField';
    const FIELD_INVALID_DATE = 'dateInvalidDateField';
    const FIELD_FALSEFORMAT = 'dateFalseFormatField';

    /**
     * Validation failure message template definitions.
     *
     * @var array
     */
    protected $_messageTemplates = [
        // Zend_Validate_Date Messages
        self::INVALID => 'Invalid type given, value should be string, integer, array or Zend_Date',
        self::NOT_YYYY_MM_DD => "'%value%' is not of the format YYYY-MM-DD",
        self::INVALID_DATE => "'%value%' does not appear to be a valid date",
        self::FALSEFORMAT => "'%value%' does not fit given date format",

        // Zend_Validate_Date Custom Messages
        self::FIELD_INVALID => 'Invalid type given, date should be string, integer, array or Zend_Date',
        self::FIELD_NOT_YYYY_MM_DD => "'%date%' is not of the format YYYY-MM-DD",
        self::FIELD_INVALID_DATE => "'%date%' does not appear to be a valid date",
        self::FIELD_FALSEFORMAT => "'%date%' does not fit given date format",
    ];

    /**
     * Validation failure message variable mappings.
     *
     * @var array
     */
    protected $_messageVariables = [
        'date' => '_date',
        'format' => '_format',
        'locale' => '_locale',
    ];

    /**
     * Date value used to compare.  When specified as a string, it can either be
     * a date (to be converted to Zend_Date) or a field name to lookup in the
     * context of the isValid method.
     *
     * @var string|Zend_Date
     */
    protected $_date;

    /**
     * Optional format.
     *
     * @var null|string
     */
    protected $_format;

    /**
     * Optional locale.
     *
     * @var null|string|Zend_Locale
     */
    protected $_locale;

    /**
     * Optional or equal which allows us to say less than or equal and vice
     * versa.
     *
     * @var bool false
     */
    protected $_orEqual;

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
        $this->setDate($date);

        $this->setFormat($format);

        if (null === $locale) {
            require_once 'Zend/Registry.php';
            if (Zend_Registry::isRegistered('Zend_Locale')) {
                $locale = Zend_Registry::get('Zend_Locale');
            }
        }

        if (null !== $locale) {
            $this->setLocale($locale);
        }

        $this->setOrEqual($orEqual);
    }

    /**
     * Returns the date option.
     *
     * @return string|Zend_Date
     */
    public function getDate()
    {
        return $this->_date;
    }

    /**
     * Sets the date option.
     *
     * @param string|Zend_Locale $date
     *
     * @return My_Validate_Date_Abstract
     */
    public function setDate($date)
    {
        require_once 'Zend/Date.php';
        if ( ! $date instanceof Zend_Date) {
            if (Zend_Date::isDate($date, $this->_format, $this->_locale)) {
                $date = new Zend_Date($date, $this->_format, $this->_locale);
            }
        }

        $this->_date = $date;

        return $this;
    }

    /**
     * Returns the locale option.
     *
     * @return null|string|Zend_Locale
     */
    public function getLocale()
    {
        return $this->_locale;
    }

    /**
     * Sets the locale option.
     *
     * @param string|Zend_Locale $locale
     *
     * @return My_Validate_Date_Abstract
     */
    public function setLocale($locale = null)
    {
        require_once 'Zend/Locale.php';
        $this->_locale = Zend_Locale::findLocale($locale);

        if ($this->_date instanceof Zend_Date) {
            $this->_date->setLocale($this->_locale);
        }

        return $this;
    }

    /**
     * Returns the format option.
     *
     * @return null|string
     */
    public function getFormat()
    {
        return $this->_format;
    }

    /**
     * Sets the format option.
     *
     * @param string $format
     *
     * @return My_Validate_Date_Abstract
     */
    public function setFormat($format = null)
    {
        $this->_format = $format;
        return $this;
    }

    /**
     * Sets the orEqual option.
     *
     * @param bool $orEqual
     *
     * @return My_Validate_Date_Abstract
     */
    public function setOrEqual($orEqual = false)
    {
        $this->_orEqual = $orEqual;
        return $this;
    }

    /**
     * Returns the orEqual option.
     *
     * @return bool
     */
    public function getOrEqual()
    {
        return $this->_orEqual;
    }

    /**
     * Sets the value to be validated and clears the messages and errors arrays.
     * This method will also validate the value as a date.
     *
     * @param mixed $value
     *
     * @return bool
     */
    protected function _setValue($value)
    {
        if ( ! $value instanceof Zend_Date) {
            // Before we convert our value to Zend_Date, let's make sure it's valid.
            require_once 'Zend/Validate/Date.php';
            $validator = new Zend_Validate_Date($this->_format, $this->_locale);
            if (false === $validator->isValid($value)) {
                $errorMessageCodes = $validator->getErrors();
                $this->_error($errorMessageCodes[0], $value);
                return false;
            }

            $value = new Zend_Date($value, $this->_format, $this->_locale);
        }

        parent::_setValue($value);
        return true;
    }

    /**
     * Parses our specified date value. This method also determines if a field
     * name was passed in for the date and we will grab the value from the field
     * in the context array.
     *
     * @param array $context[optional]
     *
     * @return bool
     */
    protected function _parseDate($context = null)
    {
        // If our $date is a string and exists in our context array, then this means
        // the user passed in a field name as the date to parse.  Let's get the value
        // from the field and convert it to a Zend_Date
        if ( ! $this->_date instanceof Zend_Date) {
            if (is_array($context) && array_key_exists($this->_date, $context)) {
                // Get the field value from the context array
                $date = $context[$this->_date];

                // Before we set our date, let's make sure the fields value is a valid
                // date format
                require_once 'Zend/Validate/Date.php';
                $validator = new Zend_Validate_Date($this->_format, $this->_locale);
                if (false === $validator->isValid($date)) {
                    $errorMessageCodes = $validator->getErrors();
                    $errorCode = $errorMessageCodes[0] . 'Field';
                    $this->_error($errorCode, $date);

                    return false;
                }

                $this->setDate($date);
            } else {
                // The $date isn't found in the context and isn't a Zend_Date instance.
                $this->_error(self::FIELD_INVALID_DATE, $this->_date);
                return false;
            }
        }
        return true;
    }

    /**
     * Defined by Zend_Validate_Interface.
     *
     * Returns true if $value is a valid date and is greater than the specified
     * $date. If optional $format or $locale is set the date format is checked
     * according to Zend_Date, see Zend_Date::isDate()
     *
     * @param string|Zend_Date $value
     * @param array            $context[optional]
     *
     * @return bool
     */
    public function isValid($value, $context = null)
    {
    }
}
