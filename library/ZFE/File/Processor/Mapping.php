<?php

/*
 * ZFE – платформа для построения редакторских интерфейсов.
 */

/**
 * Описание допустимых обработок для записи файла.
 */
class ZFE_File_Processor_Mapping extends ZFE_File_LoadableAccess implements IteratorAggregate
{
    /**
     * @var array
     */
    protected $map = [];

    /**
     * @param Files $file
     */
    public function __construct(Files $file)
    {
        $this->setRecord($file);
    }

    /**
     * @param string $processingModelName
     * @param bool   $refresh
     *
     * @throws ZFE_File_Exception
     *
     * @return $this
     */
    public function add(string $processingModelName, $refresh = false)
    {
        $model = new $processingModelName;
        if (!($model instanceof ZFE_File_Processing) || !($model instanceof Doctrine_Record)) {
            throw new ZFE_File_Exception(
                sprintf(
                    'Модель обработки %s должна реализовывать интерфейс %s',
                    $processingModelName,
                    ZFE_File_Processing::class
                ),
                10
            );
        }

        /** @var Doctrine_Record $record */
        $record = $this->getRecord();
        if (!$record->hasRelation($processingModelName)) {
            throw new ZFE_File_Exception(
                sprintf(
                    'Модель файла должна иметь связь с моделью обработки %s',
                    $processingModelName
                ),
                20
            );
        }

        if ($refresh) {
            $record->refreshRelated($processingModelName);
        }

        $this->map[$processingModelName] = $record->get($processingModelName);
        return $this;
    }

    public function get($modelName): ?Doctrine_Collection
    {
        if (array_key_exists($modelName, $this->map)) {
            return $this->map[$modelName];
        }
        return null;
    }

    /**
     * @return IteratorIterator|Traversable
     */
    public function getIterator()
    {
        return new ArrayIterator($this->map);
    }
}
