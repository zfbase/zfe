<?php
/**
 * Created by PhpStorm.
 * User: Dezzpil
 * Date: 20.10.2018
 * Time: 17:59
 */

class Helper_File_Processor_Mapping extends Helper_File_LoadableAccess implements IteratorAggregate
{
    /**
     * @var array
     */
    protected $map = [];

    /**
     * Helper_File_Processor_Mapping constructor.
     * @param Helper_File_Processable $record
     */
    public function __construct(Helper_File_Processable $record)
    {
        $this->setRecord($record);
    }

    /**
     * @param string $processingModelName
     * @param bool $refresh
     * @return $this
     * @throws Application_Exception
     */
    function add(string $processingModelName, $refresh = false)
    {
        $model = new $processingModelName;
        if (!($model instanceof Helper_File_Processing) || !($model instanceof Doctrine_Record)) {
            throw new Application_Exception(
                sprintf(
                'Модель обработки %s должна реализовывать интерфейс %s',
                    $processingModelName, Helper_File_Processing::class
                ), 10
            );
        }

        /* @var $record Doctrine_Record */
        $record = $this->getRecord();
        if (!$record->hasRelation($processingModelName)) {
            throw new Application_Exception(
                sprintf(
                    'Модель файла должна иметь связь с моделью обработки %s',
                    $processingModelName
                ), 20
            );
        }

        if ($refresh) $record->refreshRelated($processingModelName);
        $this->map[$processingModelName] = $record->get($processingModelName);
        return $this;
    }

    function get($modelName) : ?Doctrine_Collection
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
