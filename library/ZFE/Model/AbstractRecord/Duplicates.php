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

    protected static function _getDuplicates()
    {
        $q = ZFE_Query::create()
            ->select(('x.title' === static::$titleField) ? 'x.title' : (static::$titleField . ' AS title'))
            ->from(static::class . ' x')
            ->addSelect('COUNT(*) cnt')
            ->groupBy('title')
            ->having('cnt > 1')
            ->orderBy('cnt DESC')
            ->limit(10)
            ;
        return $q->execute();
    }

    protected static function _getGroupsByDuplicate($duplicate)
    {
        $tableInstance = Doctrine_Core::getTable(static::class);

        /** @var ZFE_Query $q */
        $q = ZFE_Query::create()
            ->select('x.*')
            ->from(static::class . ' x')
            ->where(static::$titleField . ' = ?', $duplicate['title'])
            ->orderBy('weight DESC')
            ;

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

    /**
     * @param Doctrine_Collection<static|AbstractRecord> $slaves
     */
    public static function advancedMerge(Doctrine_Collection $slaves, array $map = [])
    {
        $tableInstance = Doctrine_Core::getTable(static::class);
        $serviceFields = self::getServiceFields();
        $columnNames = array_diff($tableInstance->getColumnNames(), $serviceFields);

        // Дополняем карту
        $missingColumns = array_diff($columnNames, array_keys($map));
        foreach ($missingColumns as $columnName) {
            $values = [];

            foreach ($slaves as $slave) {
                if (null !== $slave->{$columnName}) {
                    $values[] = $slave->{$columnName};
                    $map[$columnName] = $slave->id;
                }
            }

            $unique = array_unique($values);
            if (count($unique) > 1) {
                new ZFE_Model_Exception('Невозможно объединить: не выбран правильный вариант');
            }
        }

        // Переставляем устаревающие записи, что бы индексы совпадали с id и создаем массив их id-шников
        $slavesIbi = []; // $slavesIndexById
        $slaveIds = [];
        foreach ($slaves as $slave) {
            $slavesIbi[$slave->id] = $slave;
            $slaveIds[] = $slave->id;
        }
        $slavesStr = implode(',', $slaveIds);

        $conn = Doctrine_Manager::connection();
        $conn->beginTransaction(); // Оборачиваем весь процесс перераспределения связей в одну большую транзакцию

        // Создаем новую запись
        $master = new static();
        foreach ($map as $columnName => $slaveId) {
            if ($tableInstance->hasField($columnName)) {
                $master->{$columnName} = $slavesIbi[$slaveId]->{$columnName};
            }
        }
        $master->saveHistory(false, true);
        $master->save();
        $master->saveHistory(true, true);

        // Пишем историю
        $user = Zend_Registry::get('user')->data;
        $history = new History();
        $history->table_name = $master->getTableName();
        $history->action_type = History::ACTION_TYPE_MERGE;
        $history->content_id = $master->id;
        $history->content_old = $slavesStr;
        $history->user_id = $user ? $user->id : null;
        $history->datetime_action = new Doctrine_Expression('NOW()');
        $history->content_version = 1;
        $history->save();

        // Перепривязываем связанные файлы
        if ($master instanceof ZfeFiles_Manageable) {
            $schemas = $master->getFileSchemas();
            foreach ($schemas as $schema) {
                /** @var ZfeFiles_Manager_Interface */
                $manager = ($schema->getModel())::getManager();
                $schemaCode = $schema->getCode();
                $isMultiple = $schema->getMultiple();
                $selected = array_key_exists($schemaCode, $map) ? $map[$schemaCode] : null;
                foreach ($slaves as $slave) {
                    if ($isMultiple || $selected == $slave->id) {
                        /** @var ZfeFiles_Agent_Interface[] */
                        $slaveAgents = $slave->getAgents($schema);
                        foreach ($slaveAgents as $slaveAgent) {
                            $agent = $manager->getAgentByFile($slaveAgent->getFile());
                            $agent->linkManageableItem($schemaCode, $master, $slaveAgent->getData());
                            $agent->save();
                            $master->addAgent($schemaCode, $agent);
                        }
                    }
                }
            }
        }

        // Перепривязываем связанные записи
        $relations = $master->getTable()->getRelations();
        foreach ($relations as $relation) {
            if ($relation instanceof Doctrine_Relation_ForeignKey) {
                $table = $relation->getTable();
                $tableName = $table->getTableName();
                $foreign = $relation->getForeign();

                // Изменяем связи со слейв-тегом объекта на связь с мастер-тегом
                $q1 = <<<SQL
UPDATE IGNORE {$tableName}
SET {$foreign} = {$master->id}
WHERE {$foreign} IN (${slavesStr})
SQL;
                $stmt = $conn->prepare($q1);
                $stmt->execute([]);

                // Удаляем оставшиеся связи со слейв-тегом объекта
                // К сожалению, на уровне запроса определить поддержку мягкого удаления не возможно
                $q2 = ZFE_Query::create($conn)
                    ->from($relation->getClass())
                    ->whereIn($foreign, $slaveIds)
                ;
                if ($table->hasColumn('deleted')) {
                    $q2->update()->set('deleted', '1');
                } else {
                    $q2->setHard(true)->delete();
                }
                $q2->execute();
            }
        }

        $slaves->delete();

        static::_afterMerge($master);

        $conn->commit();

        return $master;
    }

    /**
     * Функция, выполняющаяся после объединения записи.
     *
     * @param Doctrine_Record $master
     */
    protected static function _afterMerge(Doctrine_Record $master)
    {
    }
}
