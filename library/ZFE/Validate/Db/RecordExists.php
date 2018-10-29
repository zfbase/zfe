<?php

/*
 * ZFE – платформа для построения редакторских интерфейсов.
 */

/**
 * Валидатор наличия в БД записи средствами Doctrine.
 *
 * @category  ZFE
 */
class ZFE_Validate_Db_RecordExists extends ZFE_Validate_Db_Abstract
{
    /**
     * Отвечает на вопрос: Значение присутствует в БД?
     *
     * @param mixed $value
     *
     * @return bool
     */
    public function isValid($value)
    {
        $valid = true;
        $this->_setValue($value);

        if ($this->_getCount($value) < 1) {
            $valid = false;
            $this->_error(self::ERROR_NO_RECORD_FOUND);
        }

        return $valid;
    }
}
