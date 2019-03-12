<?php

/*
 * ZFE – платформа для построения редакторских интерфейсов.
 */

/**
 * Абстрактный валидатор наличия записи в БД через Doctrine.
 */
abstract class ZFE_Validate_Db_Abstract extends Zend_Validate_Abstract
{
    // Константы ошибок
    const ERROR_NO_RECORD_FOUND = 'noRecordFound';
    const ERROR_RECORD_FOUND    = 'recordFound';

    /**
     * Массив сообщений.
     *
     * @var array
     */
    protected $_messageTemplates = [
        self::ERROR_NO_RECORD_FOUND => "Значение '%value%' в базе не найдено",
        self::ERROR_RECORD_FOUND    => "Значение '%value%' найдено в базе",
    ];

    /**
     * Модель.
     *
     * @var string
     */
    protected $_model = '';

    /**
     * Поисковое поле.
     *
     * @var string
     */
    protected $_field = '';

    /**
     * Дополнительные условия.
     *
     * @var array
     */
    protected $_where = [];

    /**
     * Конструктор
     *
     * @param array|Zend_Config $options – Опции валидации
     */
    public function __construct($options)
    {
        if ($options instanceof Zend_Config) {
            $options = $options->toArray();
        } elseif (func_num_args() > 1) {
            $options = func_get_args();
            $temp['model'] = array_shift($options);
            $temp['field'] = array_shift($options);
            if ( ! empty($options)) {
                $temp['where'] = array_shift($options);
            }

            $options = $temp;
        } elseif ( ! is_array($options)) {
            throw new Zend_Validate_Exception('Не допустимый параметр!');
        }

        if (key_exists('model', $options)) {
            $this->setModel($options['model']);
        } else {
            throw new Zend_Validate_Exception('Модель не определена!');
        }

        if (key_exists('field', $options)) {
            $this->setField($options['field']);
        } else {
            throw new Zend_Validate_Exception('Поле для проверки не определено!');
        }

        if (key_exists('where', $options)) {
            $this->setWhere($options['where']);
        }
    }

    /**
     * Получить модель.
     *
     * @return string
     */
    public function getModel()
    {
        return $this->_model;
    }

    /**
     * Установить модель.
     *
     * @param string $model
     *
     * @return Validate_Db_Abstract
     */
    public function setModel($model)
    {
        $this->_model = ucfirst($model);
        return $this;
    }

    /**
     * Получить поисковое поле.
     *
     * @return array|string
     */
    public function getField()
    {
        return $this->_field;
    }

    /**
     * Установить поисковое поле.
     *
     * @param string $field
     *
     * @return Validate_Db_Abstract
     */
    public function setField($field)
    {
        $this->_field = (string) $field;
        return $this;
    }

    /**
     * Получить дополнительные условия.
     *
     * @return array
     */
    public function getWhere()
    {
        return $this->_where;
    }

    /**
     * Установить дополнительные условия.
     *
     * @param null|array|string $where
     *
     * @return ZFE_Validate_Db_Abstract
     *
     * @example $validator->setWhere('logged IS NULL');
     * @example $validator->setWhere(['email NOT LIKE ?', '%mailforspam.com']);
     * @example $validator->setWhere([['logged IS NULL'], ['email NOT LIKE ?', '%mailforspam.com']]);
     */
    public function setWhere($where)
    {
        if (is_string($where)) {
            $where = [$where];
        } elseif ( ! is_array($where) && null !== $where) {
            throw new Zend_Validate_Exception('Не верный формат дополнительных условий');
        }

        $this->_where = [];

        if (null !== $where) {
            $whereArr = is_string($where[0])
                ? [$where]
                : $where;
            foreach ($whereArr as $where) {
                if (empty($where[1])) {
                    $where[1] = [];
                }
                $this->addWhere($where[0], $where[1]);
            }
        }

        return $this;
    }

    /**
     * Добавить условие.
     *
     * @param string $where
     * @param array  $params
     *
     * @return ZFE_Validate_Db_Abstract
     */
    public function addWhere($where, $params = [])
    {
        $this->_where[] = [$where, $params];

        return $this;
    }

    /**
     * Выполнить запрос и вернуть результаты.
     *
     * @param string $value
     *
     * @return array when matches are found
     */
    protected function _getCount($value)
    {
        $q = ZFE_Query::create()
            ->select('COUNT(*) cnt')
            ->from($this->_model)
            ->where($this->_field . ' = ?', $value)
        ;

        foreach ($this->_where as $where) {
            $q = $q->andWhere($where[0], $where[1]);
        }

        return $q->execute([], Doctrine_Core::HYDRATE_SINGLE_SCALAR);
    }
}
