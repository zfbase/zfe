<?php

/*
 * ZFE – платформа для построения редакторских интерфейсов.
 */

/**
 * Коллекция схем (см. ZFE_File_Schema), про которые знает менеджер файлов.
 */
class ZFE_File_Schema_Collection implements IteratorAggregate
{
    protected $map = [];
    protected $required = 0;

    /**
     * {@inheritdoc}
     */
    public function getIterator()
    {
        return new ArrayIterator($this->map);
    }

    /**
     * @param ZFE_File_Schema $schema
     *
     * @return bool
     */
    protected function checkIsTypeUnic(ZFE_File_Schema $schema): bool
    {
        return !array_key_exists($schema->getFileTypeCode(), $this->map);
    }

    /**
     * @return int
     */
    public function count(): int
    {
        return count($this->map);
    }

    /**
     * @param ZFE_File_Schema $schema
     *
     * @throws ZFE_File_Exception
     *
     * @return ZFE_File_Schema_Collection
     */
    public function add(ZFE_File_Schema $schema): self
    {
        $code = $schema->getFileTypeCode();
        if ($this->checkIsTypeUnic($schema)) {
            $this->map[$code] = $schema;
            if ($schema->isRequired()) {
                $this->required++;
            }
        } else {
            throw new ZFE_File_Exception("Схема поля файла с кодом {$code} уже присутствует в коллекции");
        }
        return $this;
    }

    /**
     * @param ZFE_File_Schema $schema
     *
     * @return ZFE_File_Schema_Collection
     */
    public function remove(ZFE_File_Schema $schema): self
    {
        $code = $schema->getFileTypeCode();
        return $this->removeByCode($code);
    }

    /**
     * @param int $code
     *
     * @return ZFE_File_Schema_Collection
     */
    public function removeByCode(int $code): self
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
     *
     * @throws ZFE_File_Exception
     *
     * @return ZFE_File_Schema
     */
    public function get(int $typeCode): ZFE_File_Schema
    {
        if (array_key_exists($typeCode, $this->map)) {
            return $this->map[$typeCode];
        }
        throw new ZFE_File_Exception("Схемы поля файла с кодом {$typeCode} не найдено");
    }

    /**
     * @param Files $file
     *
     * @throws ZFE_File_Exception
     * @throws Doctrine_Record_Exception
     *
     * @return ZFE_File_Schema
     */
    public function getFor(Files $file): ZFE_File_Schema
    {
        return $this->get($file->get('type'));
    }

    /**
     * @return bool
     */
    public function hasRequired(): bool
    {
        return boolval($this->required);
    }

    /**
     * @throws ZFE_File_Exception
     *
     * @return ZFE_File_Schema_Collection
     */
    public function getRequired(): self
    {
        $subCollection = new self;
        foreach ($this->map as $code => $schema) {  /** @var ZFE_File_Schema $schema */
            if ($schema->isRequired()) {
                $subCollection->add($schema);
            }
        }
        return $subCollection;
    }
}
