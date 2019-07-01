<?php

class ZFE_File_Schema_Collection implements IteratorAggregate
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
     * @param ZFE_File_Schema $schema
     * @return bool
     */
    protected function checkIsTypeUnic(ZFE_File_Schema $schema) : bool
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
     * @param ZFE_File_Schema $schema
     * @return ZFE_File_Schema_Collection
     * @throws ZFE_File_Exception
     */
    public function add(ZFE_File_Schema $schema) : self
    {
        $code = $schema->getFileTypeCode();
        if ($this->checkIsTypeUnic($schema)) {
            $this->map[$code] = $schema;
            if ($schema->isRequired()) {
                $this->required++;
            }
        } else {
            throw new ZFE_File_Exception(
                'Схема поля файла с кодом ' . $code . ' уже присутствует в коллекции'
            );
        }
        return $this;
    }

    /**
     * @param ZFE_File_Schema $schema
     * @return ZFE_File_Schema_Collection
     */
    public function remove(ZFE_File_Schema $schema) : self
    {
        $code = $schema->getFileTypeCode();
        return $this->removeByCode($code);
    }

    /**
     * @param int $code
     * @return ZFE_File_Schema_Collection
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
     * @return ZFE_File_Schema
     * @throws ZFE_File_Exception
     */
    public function get(int $typeCode) : ZFE_File_Schema
    {
        if (array_key_exists($typeCode, $this->map)) {
            return $this->map[$typeCode];
        }
        throw new ZFE_File_Exception('Схемы поля файла с кодом ' . $typeCode . ' не найдено');
    }

    /**
     * @param Files $file
     * @return ZFE_File_Schema
     * @throws ZFE_File_Exception
     * @throws Doctrine_Record_Exception
     */
    public function getFor(Files $file) : ZFE_File_Schema
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
     * @return ZFE_File_Schema_Collection
     * @throws ZFE_File_Exception
     */
    public function getRequired() : ZFE_File_Schema_Collection
    {
        $subCollection = new ZFE_File_Schema_Collection;
        foreach ($this->map as $code => $schema) {
            /* @var $schema ZFE_File_Schema */
            if ($schema->isRequired()) {
                $subCollection->add($schema);
            }
        }
        return $subCollection;
    }
}
