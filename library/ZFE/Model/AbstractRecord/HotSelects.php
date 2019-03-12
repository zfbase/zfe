<?php

/*
 * ZFE – платформа для построения редакторских интерфейсов.
 */

/**
 * Статические методы для наиболее частых выборок.
 */
trait ZFE_Model_AbstractRecord_HotSelects
{
    /**
     * Найти запись по идентификатору.
     *
     * Обертка для Doctrine_Table::find().
     *
     * Если надо найти удаленную запись, воспользуйтесь self::hardFind().
     * Интерфейс метода self::hardFind() идентичен методу self::find().
     *
     * <code>
     * Editors::find(100);
     * Days::find('2013-02-12');
     * Tokens::find('EX-eeb8ho4g');
     * EditorsRoles::find([100, 4]);
     * Tags::find(4, Doctrine_Core::HYDRATE_RECORD);
     * </code>
     *
     * @see Doctrine_Table::find()
     * @see ZFE_Model_AbstractRecord_HotSelects::hardFind()
     *
     * @return static
     */
    public static function find()
    {
        return call_user_func_array([Doctrine_Core::getTable(static::class), 'find'], func_get_args());
    }

    /**
     * Получить все записи хранящиеся в таблице.
     *
     * Обертка для Doctrine_Table::findAll().
     *
     * @param int $hydrationMode формат результата: Doctrine_Core::HYDRATE_ARRAY или Doctrine_Core::HYDRATE_RECORD
     *
     * @return array|Doctrine_Collection
     */
    public static function findAll($hydrationMode = null)
    {
        return Doctrine_Core::getTable(static::class)->findAll($hydrationMode);
    }

    /**
     * Найти записи в текущей таблице соответствующие указанному SQL-запросу.
     *
     * Обертка для Doctrine_Table::findBySql().
     *
     * ВНИМАНИЕ: This actually takes DQL, not SQL, but it requires column names
     * instead of field names. This should be fixed to use raw SQL instead.
     *
     * @param string $dql           DQL фрагмент для WHERE
     * @param array  $params        параметры запроса (а-ля PDO)
     * @param int    $hydrationMode формат результата: Doctrine_Core::HYDRATE_ARRAY или Doctrine_Core::HYDRATE_RECORD
     *
     * @return array|Doctrine_Collection
     */
    public static function findBySql($dql, $params = [], $hydrationMode = null)
    {
        return Doctrine_Core::getTable(static::class)->findBySql($dql, $params, $hydrationMode);
    }

    /**
     * Найти записи в текущей таблице соответствующие указанному DQL-запросу.
     *
     * Обертка для Doctrine_Table::findByDql().
     *
     * @param string $dql           DQL фрагмент для WHERE
     * @param array  $params        параметры запроса (а-ля PDO)
     * @param int    $hydrationMode формат результата: Doctrine_Core::HYDRATE_ARRAY или Doctrine_Core::HYDRATE_RECORD
     *
     * @return Doctrine_Collection<static>
     */
    public static function findByDql($dql, $params = [], $hydrationMode = null)
    {
        return Doctrine_Core::getTable(static::class)->findByDql($dql, $params, $hydrationMode);
    }

    /**
     * Найти записи по произвольному полю.
     *
     * Обертка для Doctrine_Table::findBy().
     *
     * @param string $fieldName     поле для поиска
     * @param string $value         искомое значение
     * @param int    $hydrationMode формат результата: Doctrine_Core::HYDRATE_ARRAY или Doctrine_Core::HYDRATE_RECORD
     *
     * @return Doctrine_Collection|static[]
     */
    public static function findBy($fieldName, $value, $hydrationMode = null)
    {
        return Doctrine_Core::getTable(static::class)->findBy($fieldName, $value, $hydrationMode);
    }

    /**
     * Найти первую запись по произвольному полю.
     *
     * Обертка для Doctrine_Table::findOneBy().
     *
     * @param string $fieldName     поле для поиска
     * @param string $value         искомое значение
     * @param int    $hydrationMode формат результата: Doctrine_Core::HYDRATE_ARRAY или Doctrine_Core::HYDRATE_RECORD
     *
     * @return Doctrine_Collection|static[]
     */
    public static function findOneBy($fieldName, $value, $hydrationMode = null)
    {
        return Doctrine_Core::getTable(static::class)->findOneBy($fieldName, $value, $hydrationMode);
    }

    /**
     * Найти запись по идентификатору даже если она удалена (deleted = 1).
     *
     * Обертка для Doctrine_Table::find(), учитывая удаленные записи.
     *
     * @see Doctrine_Table::find()
     * @see ZFE_Model_AbstractRecord_HotSelects::find()
     *
     * @return static
     */
    public static function hardFind()
    {
        return call_user_func_array([Doctrine_Core::getTable(static::class), 'hardFind'], func_get_args());
    }

    /**
     * Поиск записи по нескольким параметрам
     *
     * @param array $filters       массив параметров
     * @param int   $hydrationMode формат результата: Doctrine_Core::HYDRATE_ARRAY или Doctrine_Core::HYDRATE_RECORD
     * @param bool  $returnOne     вернуть только одну запись
     *
     * @return Doctrine_Collection|static|static[]
     */
    public static function findBySeveralParams(array $filters = [], $hydrationMode = null, $returnOne = false)
    {
        $query = ZFE_Query::create()
            ->select('x.*')
            ->from(static::class . ' x')
        ;

        foreach ($filters as $param => $value) {
            if (is_array($value)) {
                $query = $query->andWhereIn($param, $value);
            } elseif (null === $value) {
                $query = $query->andWhere($param . ' IS NULL');
            } else {
                $query = $query->andWhere($param . ' = ?', $value);
            }
        }

        if ($returnOne) {
            return $query->fetchOne([], $hydrationMode);
        }

        return $query->execute([], $hydrationMode);
    }

    /**
     * Поиск одной записи по нескольким параметрам
     *
     * @param array $filters       массив параметров
     * @param int   $hydrationMode формат результата: Doctrine_Core::HYDRATE_ARRAY или Doctrine_Core::HYDRATE_RECORD
     *
     * @return static
     */
    public static function findOneBySeveralParams(array $filters = [], $hydrationMode = null)
    {
        return static::findBySeveralParams($filters, $hydrationMode, true);
    }

    /**
     * Поддержка магических методов таблиц для статического вызова.
     *
     * @param string $method
     * @param array  $arguments
     */
    public static function __callStatic($method, $arguments)
    {
        return call_user_func_array([Doctrine_Core::getTable(static::class), $method], $arguments);
    }

    /**
     * Получить массив с заданными ключами и значениями из текущей таблицы.
     *
     * @param string       $keyField       поле для ключа
     * @param string       $valueField     поле для значения
     * @param array|string $where          фильтр: ['status = ? or status = ?', 1, 2];
     * @param string       $order          сортировка
     * @param string       $groupby        группирует списки по третьему полю (для формирования списка зависимого от другого, напр. список городов с группировкой по регионам)
     * @param bool|int     $filterByStatus фильтровать по статусу
     *
     * @return array
     */
    public static function getKeyValueList(
        $keyField = 'x.id',
        $valueField = null,
        $where = null,
        $order = 'VAL_FIELD ASC',
        $groupby = null,
        $filterByStatus = null
    ) {
        $groupby = $groupby ? ", ${groupby} AS GROUP_FIELD" : '';
        if (null === $filterByStatus) {
            $filterByStatus = static::$_excludeByStatus;
            if (true === $filterByStatus) {
                $filterByStatus = 0;
            }
        }

        $map = [];
        $params = [];

        $schema = Zend_Registry::get('config')->doctrine->schema;

        if (null === $valueField) {
            $valueField = static::$titleField;
        }

        $model = new static();
        $class = $model->getTableName();

        $query = "SELECT ${keyField} AS KEY_FIELD, ${valueField} AS VAL_FIELD" . ($groupby ? ", ${groupby} AS GROUP_FIELD" : '');
        $query .= ZFE_Query::isPgsql() ? " FROM \"${schema}\".\"${class}\" x" : " FROM `${schema}`.`${class}` x";

        $conds = [];

        if ($model->contains('deleted')) {
            $conds[] = 'x.deleted = 0';
        }

        if ($model->contains('status') && false !== $filterByStatus) {
            $conds[] = 'x.status = ' . (int) $filterByStatus;
        }

        if ($where) {
            if (is_array($where) && count($where) > 1) {
                $conds[] = array_shift($where);
                $params = $where;
            } elseif (is_string($where)) {
                $conds[] = $where;
            }
        }

        if (count($conds)) {
            $query .= ' WHERE ' . implode(' AND ', $conds);
        }

        $query .= ' ORDER BY ' . $order;

        $conn = Doctrine_Manager::connection();
        $stmt = $conn->prepare($query);
        $stmt->execute($params);
        $rows = $stmt->fetchAll();

        if ($rows && is_array($rows)) {
            foreach ($rows as $row) {
                $row = array_change_key_case($row, CASE_UPPER);
                if ($groupby) {
                    $map[$row['GROUP_FIELD']][$row['KEY_FIELD']] = $row['VAL_FIELD'];
                } else {
                    $map[$row['KEY_FIELD']] = $row['VAL_FIELD'];
                }
            }
        }

        return $map;
    }
}
