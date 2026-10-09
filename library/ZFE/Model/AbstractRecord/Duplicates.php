<?php

/*
 * ZFE – платформа для построения редакторских интерфейсов.
 */

/**
 * Средство поиска и объединения дубликатов.
 */
trait ZFE_Model_AbstractRecord_Duplicates
{
    public static function getDuplicatesGroups()
    {
        $groups = [];

        $duplicates = static::_getDuplicates();
        foreach ($duplicates as $duplicate) {
            $groups[] = static::_getGroupsByDuplicate($duplicate);
        }

        return $groups;
    }

    protected static function _getDuplicatesQuery()
    {
        return ZFE_Query::create()
            ->select(('x.title' === static::$titleField) ? 'x.title' : (static::$titleField . ' AS title'))
            ->from(static::class . ' x')
            ->addSelect('COUNT(*) cnt')
            ->groupBy('title')
            ->having('cnt > 1')
            ->orderBy('cnt DESC')
        ;
    }

    public static function _getDuplicates()
    {
        $q = self::_getDuplicatesQuery();
        $q->limit(10);

        return ZFE_Paginator::execute($q);
    }

    public static function getCountDuplicates()
    {
        $q = self::_getDuplicatesQuery();

        return $q->count();
    }

    protected static function _getGroupsByDuplicate($duplicate)
    {
        $tableInstance = Doctrine_Core::getTable(static::class);

        /** @var ZFE_Query $q */
        $q = ZFE_Query::create()
            ->select('x.*')
            ->from(static::class . ' x')
            ->where(static::$titleField . ' = ?', $duplicate['title'])
            ->orderBy('weight DESC');

        if ($tableInstance->hasRelation('Editor')) {
            $q->addFrom('x.Editor e')->addSelect('e.*');
        }

        if ($tableInstance->hasRelation('Creator')) {
            $q->addFrom('x.Creator c')->addSelect('c.*');
        }

        return static::calcWeightsEnrichQuery($q)->execute();
    }

    /**
     * Подсчитать число связей у результатов выборки.
     */
    public static function calcWeightsEnrichQuery(ZFE_Query $q)
    {
        $weights = [];
        $relations = Doctrine_Core::getTable(static::class)->getRelations();
        foreach ($relations as $relation) {
            if ($relation instanceof Doctrine_Relation_ForeignKey) {
                $col = $relation->getForeignColumnName();
                $table = $relation->getTable()->getTableName();
                $weights[] = '(select count(*) from ' . $table . ' where ' . $col . ' = x.id)';
            }
        }

        if (!empty($weights)) {
            $q->addSelect((new Doctrine_Expression('(' . implode(' + ', $weights) . ')')) . ' AS weight');
        } else {
            $q->addSelect('0 weight');
        }

        return $q;
    }

    public static function mergeFillMap(Doctrine_Collection $items): array
    {
        $res = [];
        $first = $items->getFirst();
        if ($first instanceof ZfeFiles_Manageable) {
            $schemas = $first->getFileSchemas();
            foreach ($schemas as $schema) {
                if ($schema->isHidden() || !$schema->getMultiple()) {
                    continue;
                }
                foreach ($items as $item) {
                    $res[$schema->getCode()][$item['id']] = $item->getAgents($schema);
                }
            }
        }
        return $res;
    }

    public function mergeUpdateRelations(Doctrine_Collection $items, array $map): void
    {
        // Перепривязываем связанные файлы
        if ($this instanceof ZfeFiles_Manageable) {
            $schemas = $this->getFileSchemas();
            foreach ($schemas as $schema) {
                /** @var ZfeFiles_Manager_Interface */
                $manager = ($schema->getModel())::getManager();
                $schemaCode = $schema->getCode();
                $isMultiple = $schema->getMultiple();
                $selected = array_key_exists($schemaCode, $map) ? $map[$schemaCode] : null;
                foreach ($items as $slave) {
                    if ($isMultiple || $selected == $slave->id) {
                        /** @var ZfeFiles_Agent_Interface[] */
                        $slaveAgents = $slave->getAgents($schema);
                        foreach ($slaveAgents as $slaveAgent) {
                            $agent = $manager->getAgentByFile($slaveAgent->getFile());
                            $agent->linkManageableItem($schemaCode, $this, $slaveAgent->getData());
                            $agent->save();
                            $this->addAgent($schemaCode, $agent);
                        }
                    }
                }
            }
        }
    }
}
