<?php

/*
 * ZFE – платформа для построения редакторских интерфейсов.
 */

/**
 * Строгий валидатор даты (или даты со временем) в формате SQL.
 *
 * Zend_Validate_Date пропускает, например, 202621-10-18 и 2026-10-18 extra,
 * а такие значения потом отвергает СУБД.
 *
 * Дата: YYYY-MM-DD, дата со временем: YYYY-MM-DD HH:MM:SS
 * (к этому виду значение HTML5 элемента datetime-local приводит фильтр ZFE_Filter_DateTime).
 */
class ZFE_Validate_SqlDate extends Zend_Validate_Abstract
{
    const INVALID = 'sqlDateInvalid';
    const FALSE_FORMAT = 'sqlDateFalseFormat';
    const INVALID_DATE = 'sqlDateInvalidDate';
    const OUT_OF_RANGE = 'sqlDateOutOfRange';

    /**
     * Минимальный допустимый год по умолчанию.
     */
    const MIN_YEAR = 1000;

    /**
     * На сколько лет вперед от текущего по умолчанию допускается дата.
     */
    const MAX_YEARS_AHEAD = 50;

    /**
     * Сообщения об ошибках.
     *
     * @var array
     */
    protected $_messageTemplates = [
        self::INVALID => 'Некорректный тип. Должна быть строка',
        self::FALSE_FORMAT => "'%value%' не является датой",
        self::INVALID_DATE => "'%value%' – несуществующая дата",
        self::OUT_OF_RANGE => 'Год должен быть от %minYear% до %maxYear%',
    ];

    /**
     * @var array
     */
    protected $_messageVariables = [
        'minYear' => '_minYear',
        'maxYear' => '_maxYear',
    ];

    /**
     * Проверять ли время.
     *
     * @var bool
     */
    protected $_time = false;

    /**
     * @var int
     */
    protected $_minYear;

    /**
     * @var int
     */
    protected $_maxYear;

    /**
     * @param array $options time, minYear, maxYear
     */
    public function __construct(array $options = [])
    {
        $this->_time = !empty($options['time']);
        $this->_minYear = (int) ($options['minYear'] ?? static::getDefaultMinYear());
        $this->_maxYear = (int) ($options['maxYear'] ?? static::getDefaultMaxYear());
    }

    /**
     * Минимальный допустимый год по умолчанию.
     *
     * @return int
     */
    public static function getDefaultMinYear()
    {
        return static::MIN_YEAR;
    }

    /**
     * Максимальный допустимый год по умолчанию.
     *
     * @return int
     */
    public static function getDefaultMaxYear()
    {
        return (int) date('Y') + static::MAX_YEARS_AHEAD;
    }

    /**
     * Отвечает на вопрос: переданное значение является допустимой датой?
     *
     * @param string $value
     *
     * @return bool
     */
    public function isValid($value)
    {
        if (!is_string($value)) {
            $this->_error(self::INVALID);
            return false;
        }

        $this->_setValue($value);

        $pattern = $this->_time
            ? '/^(\d{4})-(\d{2})-(\d{2}) (\d{2}):(\d{2}):(\d{2})$/'
            : '/^(\d{4})-(\d{2})-(\d{2})$/';
        if (!preg_match($pattern, $value, $matches)) {
            $this->_error(self::FALSE_FORMAT);
            return false;
        }

        $year = (int) $matches[1];
        if (!checkdate((int) $matches[2], (int) $matches[3], $year)) {
            $this->_error(self::INVALID_DATE);
            return false;
        }

        if ($this->_time && ((int) $matches[4] > 23 || (int) $matches[5] > 59 || (int) $matches[6] > 59)) {
            $this->_error(self::INVALID_DATE);
            return false;
        }

        if ($year < $this->_minYear || $year > $this->_maxYear) {
            $this->_error(self::OUT_OF_RANGE);
            return false;
        }

        return true;
    }
}
