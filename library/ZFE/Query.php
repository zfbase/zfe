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
     * Выполнять "жесткий" запрос (без учета истории)?
     *
     * @return bool
     */
    public function isHard()
    {
        return $this->_hard;
    }

    /**
     * Нужно выполнять "жесткий" запрос (без учета истории)?
     *
     * @param null|bool $hard
     *
     * @return bool
     */
    public function setHard($hard = null)
    {
        if (is_bool($hard)) {
            $this->_hard = $hard;
        } elseif (null !== $hard) {
            throw new ZFE_Exception('Не верный аргумент в ZFE_Query::setHard(' . gettype($hard) . ') – допустимо: bool/null');
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
