<?php

/*
 * ZFE – платформа для построения редакторских интерфейсов.
 */

/**
 * Расширение подгрузки правил разделения доступа.
 *
 * Основано на примере:
 * http://stackoverflow.com/questions/5209671/zend-framework-need-typical-example-of-acl
 */
class ZFE_Query extends Doctrine_Query
{
    protected static $_driverName = null;

    /**
     * Это "жесткий" запрос (без учета истории)?
     *
     * @var bool
     */
    protected $_hard = false;

    /**
     * Это "жесткий" запрос (без учета истории) только для базовой модели, а для остальных обычный?
     *
     * @var bool
     */
    protected $_middleHard = false;

    /**
     * Выполнять "жесткий" запрос (без учета истории)?
     *
     * @return bool
     */
    public function isHard()
    {
        return $this->_hard;
    }

    /**
     * Выполнять "жесткий" запрос (без учета истории) только для базовой модели, а для остальных обычный?
     *
     * @return bool
     */
    public function isMiddleHard()
    {
        return $this->_middleHard;
    }

    /**
     * Нужно выполнять "жесткий" запрос (без учета истории)?
     *
     * @param bool $hard
     *
     * @return ZFE_Query
     */
    public function setHard($hard = null)
    {
        if (is_bool($hard)) {
            $this->_hard = $hard;
        } elseif (null !== $hard) {
            throw new ZFE_Exception('Не верный аргумент в ZFE_Query::setHard(' . gettype($hard) . ') – допустимы true/false');
        }

        return $this;
    }

    /**
     * Выполнить запрос без учета флага удаленности для базовой модели.
     * 
     * Проверки на deleted == 0 будут производится только для JOIN таблиц. 
     *
     * @param bool $hard
     *
     * @return ZFE_Query
     */
    public function setMiddleHard($hard)
    {
        if (is_bool($hard)) {
            $this->_middleHard = $hard;
        } elseif (null !== $hard) {
            throw new ZFE_Exception('Не верный аргумент в ZFE_Query::setHardOnlyBase(' . gettype($hard) . ') – допустимы true/false');
        }

        return $this;
    }

    public static function getDriverName()
    {
        if (null === self::$_driverName) {
            self::$_driverName = Doctrine_Manager::connection()->getDriverName();
        }
        return self::$_driverName;
    }

    public static function isPgsql()
    {
        return 'Pgsql' === self::getDriverName();
    }

    public function orderByField($col, array $ids)
    {
        if (self::isPgsql()) {
            $ids = array_values($ids);
            $order = array_map(function ($index) use ($ids, $col) {
                return "WHEN {$col} = {$ids[$index]} THEN {$index}";
            }, array_keys($ids));
            return $this->orderBy('CASE ' . implode(' ', $order) . ' ELSE ' . count($ids) . ' END');
        }
        return $this->orderBy('FIELD(' . $col . ', ' . implode(',', $ids) . ')');
    }

    public function setDqlQueryPart($queryPartName, array $queryPart)
    {
        $this->_dqlParts[$queryPartName] = $queryPart;
        $this->_state = Doctrine_Query::STATE_DIRTY;
        return $this;
    }

//    public function delete($from = null)
//    {
//        if ($this->isHard()) {
//            return parent::delete($from);
//        }
//
//        throw new Exception('В настоящее время невозможно автоматически определить является запрос "мягким" или "жестким". ' .
//                            'Необходимо выбор типа запроса осуществлять уровнем выше.');
//    }
}
