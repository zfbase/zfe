<?php
/**
 * Created by PhpStorm.
 * User: dezzpil
 * Date: 11.10.18
 * Time: 16:22
 */

class Helper_File_Schema_Collection implements IteratorAggregate
{
    protected $map = [];
    protected $required = 0;

    /**
     * @inheritDoc
     */
    public function getIterator()
    {
        return new ArrayIterator($this->map);
    }

    /**
     * @param Helper_File_Schema $schema
     * @return bool
     */
    protected function checkIsTypeUnic(Helper_File_Schema $schema) : bool
    {
        return !array_key_exists($schema->getFileTypeCode(), $this->map);
    }

    /**
     * @return int
     */
    public function count() : int
    {
        return count($this->map);
    }

    /**
     * @param Helper_File_Schema $schema
     * @return Helper_File_Schema_Collection
     * @throws Application_Exception
     */
    public function add(Helper_File_Schema $schema) : self
    {
        $code = $schema->getFileTypeCode();
        if ($this->checkIsTypeUnic($schema)) {
            $this->map[$code] = $schema;
            if ($schema->isRequired()) {
                $this->required++;
            }
        } else {
            throw new Application_Exception(
                'Схема поля файла с кодом ' . $code . ' уже присутствует в коллекции'
            );
        }
        return $this;
    }

    /**
     * @param Helper_File_Schema $schema
     * @return Helper_File_Schema_Collection
     */
    public function remove(Helper_File_Schema $schema) : self
    {
        $code = $schema->getFileTypeCode();
        return $this->removeByCode($code);
    }

    /**
     * @param int $code
     * @return Helper_File_Schema_Collection
     */
    public function removeByCode(int $code) : self
    {
        if (array_key_exists($code, $this->map)) {
            if ($this->map[$code]->isRequired()) {
                $this->required--;
            }
            unset($this->map[$code]);
        }
        return $this;
    }

    /**
     * @param int $typeCode
     * @return Helper_File_Schema
     * @throws Application_Exception
     */
    public function get(int $typeCode) : Helper_File_Schema
    {
        if (array_key_exists($typeCode, $this->map)) {
            return $this->map[$typeCode];
        }
        throw new Application_Exception('Схемы поля файла с кодом ' . $typeCode . ' не найдено');
    }

    /**
     * @param Helper_File_Loadable $file
     * @return Helper_File_Schema
     * @throws Application_Exception
     * @throws Doctrine_Record_Exception
     */
    public function getFor(Helper_File_Loadable $file) : Helper_File_Schema
    {
        /* @var $file Doctrine_Record */
        return $this->get($file->get('type'));
    }

    /**
     * @return bool
     */
    public function hasRequired() : bool
    {
        return boolval($this->required);
    }

    /**
     * @return Helper_File_Schema_Collection
     * @throws Application_Exception
     */
    public function getRequired() : Helper_File_Schema_Collection
    {
        $subCollection = new Helper_File_Schema_Collection;
        foreach ($this->map as $code => $schema) {
            /* @var $schema Helper_File_Schema */
            if ($schema->isRequired()) {
                $subCollection->add($schema);
            }
        }
        return $subCollection;
    }
}
