<?php

/*
 * ZFE – платформа для построения редакторских интерфейсов.
 */

/**
 * Валидатор отсутствия в БД записи средствами Doctrine.
 *
 * @category  ZFE
 */
class ZFE_Validate_Db_NoRecordExists extends ZFE_Validate_Db_Abstract
{
    /**
     * Отвечает на вопрос: Значение отсутствует в БД?
     *
     * @param mixed $value
     *
     * @return bool
     */
    public function isValid($value)
    {
        $valid = true;
        $this->_setValue($value);

        if ($this->_getCount($value) > 0) {
            $valid = false;
            $this->_error(self::ERROR_RECORD_FOUND);
        }

        return $valid;
    }
}
