<?php

/*
 * ZFE – платформа для построения редакторских интерфейсов.
 */

trait ZFE_Model_AbstractRecord_Autocomplete_Searcher
{
    /**
     * Максимальное число вариантов.
     *
     * @var null|int
     */
    public static $acLimit = 7;

    /**
     * Максимальная длина для ограничения величины выдачи.
     *
     * @var int
     */
    public static $acMaxLengthForLimit = 7;

    /**
     * ID в индексе сфинкса.
     */
    protected static $acSphinxId = 'id';

    /**
     * Получить список для автокомплита.
     *
     * @param array $params
     *
     * @return array
     */
    public static function autocomplete(array $params)
    {
        $q = static::_getDoctrineQueryForAutocomplete($params);
        return array_map(
            [static::class, 'mapAutocompleteResultRow'],
            $q->execute()
        );
    }

    /**
     * Получить список для автокомплита через Sphinx.
     *
     * @param array $params
     *
     * @return array
     */
    public static function sphinxAutocomplete(array $params)
    {
        $sphinxQuery = static::_getSphinxQueryForAutocomplete($params);
        $ids = ZFE_Sphinx::fetchIds($sphinxQuery->execute());
        if (!count($ids)) {
            return [];
        }

        $doctrineQuery = static::_getDoctrineQueryForAutocomplete();
        $doctrineQuery->andWhereIn('x.id', $ids);
        $doctrineQuery->removeDqlQueryPart('orderby');
        $doctrineQuery->orderByField('x.id', $ids);
        return array_map(
            [static::class, 'mapAutocompleteResultRow'],
            $doctrineQuery->execute()
        );
    }

    /**
     * Собрать Sphinx-запрос для автокомплита.
     *
     * @param array $params
     *
     * @return \Foolz\SphinxQL\SphinxQL
     */
    protected static function _getSphinxQueryForAutocomplete(array $params = [])
    {
        /** @var \Foolz\SphinxQL\SphinxQL $q */
        $q = ZFE_Sphinx::query()->select(static::$acSphinxId)
            ->from(static::getSphinxIndexName())
            ->limit(static::$acLimit)
        ;

        $table = Doctrine_Core::getTable(static::class);
        if (static::$_excludeByStatus && $table->hasField('status')) {
            $q->where('attr_status', 0);
        }
        if ($table->hasField('deleted')) {
            $q->where('attr_deleted', 0);
        }

        if (!empty($params['term']) && $term = trim($params['term'])) {
            $q->match('*', $term);
        } else {
            $term = null;
        }

        if (isset($params['exclude']) && !empty($params['exclude'])) {
            $exclude = [];
            foreach (explode(',', $params['exclude']) as $id) {
                $exclude[] = (int) $id;
            }
            $q->where('id', 'NOT IN', $exclude);
        }

        return $q;
    }

    /**
     * Собрать Doctrine-запрос для автокомплита.
     *
     * @param array $params
     *
     * @return ZFE_Query
     */
    protected static function _getDoctrineQueryForAutocomplete(array $params = [])
    {
        // Base query
        $q = ZFE_Query::create()
            ->select('x.id')
            ->addSelect('x.title' === static::$titleField ? static::$titleField : '(' . static::$titleField . ') title')
            ->from(static::class . ' x')
            ->where(static::$titleField . ' IS NOT NULL')
            ->setHydrationMode(Doctrine_Core::HYDRATE_ARRAY)
        ;

        // Add check Status
        $table = Doctrine_Core::getTable(static::class);
        if (static::$_excludeByStatus && $table->hasField('status')) {
            $q->addWhere('x.status = 0');
        }

        // Add check Term
        $term = empty($params['term']) ? '' : trim($params['term']);
        if ($term) {
            $safeTerm = addcslashes($term, '%_\'\\');

            $q->addWhere(static::$titleField . " LIKE ? ESCAPE '\\\\'", '%' . $safeTerm . '%');
            // Кажется, парсер Доктрины неверно обрабатывает параметр со скобкой.
            // Например, LIKE '(%' превращается в LIKE '(%)
            // скобка вместо кавычки
            if (false === mb_strpos($safeTerm, '(')) {
                $q->orderBy("CASE WHEN title LIKE '{$safeTerm}%' ESCAPE '\\\\' THEN 0 ELSE 1 END");
            }
            $q->addOrderBy('title ASC');
        } else {
            $q = static::_modifyDoctrineQueryForEmptyAutocomplete($q, $params);
        }

        // Add Limit
        if (null !== static::$acLimit && static::$acMaxLengthForLimit >= mb_strlen($term)) {
            $q->limit(static::$acLimit);
        }

        // Add check Exclude ids
        if (isset($params['exclude']) && !empty($params['exclude'])) {
            $q->andWhereIn('x.id', explode(',', $params['exclude']), true);
        }

        // Add select custom fields
        foreach (static::$autocompleteSelectCols as $col) {
            if ($table->hasField($col)) {
                $q->addSelect("x.{$col}");
            }
        }

        return $q;
    }

    /**
     * Дополнение запроса для выбора значений по умолчанию.
     *
     * @param ZFE_Query $q
     * @param array     $params
     *
     * @return ZFE_Query
     */
    protected static function _modifyDoctrineQueryForEmptyAutocomplete(ZFE_Query $q, array $params = [])
    {
        $q->orderBy('title ASC');
        return $q;
    }

    /**
     * @param array $row
     *
     * @return array
     */
    public static function mapAutocompleteResultRow($row)
    {
        $data = [
            'key' => $row['id'],
            'value' => $row['title'],
        ];
        foreach (static::$autocompleteSelectCols as $col) {
            $data[$col] = $row[$col] ?? null;
        }
        return $data;
    }
}
