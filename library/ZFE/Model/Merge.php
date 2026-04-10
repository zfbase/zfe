<?php

class ZFE_Model_Merge
{
    public function __construct(
        private string $modelName,
        private Doctrine_Collection $items,
        private array $fieldsMap,
        private array $inaccurate,
    ) {}

    public static function prepare(string $modelName, array $ids, array $fieldsMap = []): self
    {
        $tableInstance = Doctrine_Core::getTable($modelName);

        /** @var ZFE_Query $q */
        $q = ZFE_Query::create()
            ->select('x.*')
            ->from($modelName . ' x INDEXBY x.id')
            ->whereIn('x.id', $ids);

        if ($tableInstance->hasRelation('Editor')) {
            $q->addFrom('x.Editor e')->addSelect('e.*');
        }

        if ($tableInstance->hasRelation('Creator')) {
            $q->addFrom('x.Creator c')->addSelect('c.*');
        }

        /** @var Doctrine_collection<static|AbstractRecord>|Array<static|AbstractRecord> */
        $items = $modelName::calcWeightsEnrichQuery($q)->execute();

        $diff = [];
        $map = [];
        $serviceFields = $modelName::getServiceFields();
        $serviceFields[] = 'weight';
        $multiAutocompleteFields = array_keys($modelName::$multiAutocompleteCols);
        $multiCheckOrSelectCols = array_keys($modelName::$multiCheckOrSelectCols);
        foreach ($items as $item) {
            foreach ($item->toArray(false) as $field => $value) {
                if (
                    !in_array($field, $serviceFields) &&
                    !in_array($field, $multiAutocompleteFields) &&
                    !in_array($field, $multiCheckOrSelectCols)
                ) {
                    if (null !== $value) {
                        $map[$field][$item['id']] = $value;
                    }
                }
            }
        }

        $map = $map + $modelName::mergeFillMap($items);

        foreach ($map as $field => $data) {
            $data = array_udiff($data, [''], fn($a, $b) => $a === $b ? 0 : 1);
            $map[$field] = array_unique($data, SORT_REGULAR);
            if (count($map[$field]) > 1) {
                $diff[$field] = $map[$field];
            }
        }

        $inaccurate = array_diff(array_keys($diff), array_keys($fieldsMap), ['Editor', 'Creator']);

        return new self($modelName, $items, $fieldsMap, $inaccurate);
    }

    public function getItems(): Doctrine_Collection
    {
        return $this->items;
    }

    public function canMerge(): bool
    {
        return empty($this->inaccurate);
    }

    public function perform(): ?AbstractRecord
    {
        if (!$this->canMerge()) {
            return null;
        }

        $modelName = $this->modelName;
        $slaves = $this->items;
        $map = $this->fieldsMap;

        $tableInstance = Doctrine_Core::getTable($modelName);
        $serviceFields = $modelName::getServiceFields();
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
        if (count($slaveIds) === 0) {
            throw new ZFE_Model_Exception('Невозможно объединить: отсутствуют исходные записи');
        }
        $slavesStr = implode(',', $slaveIds);

        $conn = Doctrine_Manager::connection();
        $conn->beginTransaction(); // Оборачиваем весь процесс перераспределения связей в одну большую транзакцию

        // Создаем новую запись
        /** @var AbstractRecord */
        $master = new ($modelName)();
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

        $master->mergeUpdateRelations($slaves, $map);

        // Перепривязываем связанные записи
        $relations = $master->getTable()->getRelations();
        foreach ($relations as $relation) {
            if ($relation instanceof Doctrine_Relation_ForeignKey) {
                $table = $relation->getTable();
                $tableName = $table->getTableName();
                $foreign = $relation->getForeign();

                if ($slavesStr) {
                    // Изменяем связи со слейв-тегом объекта на связь с мастер-тегом
                    $q1 = <<<SQL
UPDATE IGNORE {$tableName}
SET {$foreign} = {$master->id}
WHERE {$foreign} IN ({$slavesStr})
SQL;
                    $stmt = $conn->prepare($q1);
                    $stmt->execute([]);
                }
            }
        }

        $slaves->delete();
        $conn->commit();

        return $master;
    }
}
