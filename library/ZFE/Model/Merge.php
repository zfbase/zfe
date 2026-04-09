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
        foreach ($map as $field => $data) {
            // Тушение предупреждений плохо, но тут действительно вполне может быть и строка и массив
            // и это норма, а не исключение, и собачка лучше чем раздувать код
            $data = @array_diff($data, ['']);
            $map[$field] = array_unique($data, SORT_REGULAR);
            if (1 < count($map[$field])) {
                $diff[$field] = $map[$field];
            }
        }

        $first = $items->getFirst();
        if ($first && $first instanceof ZfeFiles_Manageable) {
            $schemas = $first->getFileSchemas();
            foreach ($schemas as $schema) {
                if ($schema->isHidden() || !$schema->getMultiple()) {
                    continue;
                }
                foreach ($items as $item) {
                    $map[$schema->getCode()][$item['id']] = $item->getAgents($schema);
                }
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
        if ($this->canMerge()) {
            return ($this->modelName)::advancedMerge($this->items, $this->fieldsMap);
        }
        return null;
    }
}
