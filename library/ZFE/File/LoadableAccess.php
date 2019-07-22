<?php

/*
 * ZFE – платформа для построения редакторских интерфейсов.
 */

/**
 * Класс управления записью файла, для которой происходит работа с ФС.
 */
abstract class ZFE_File_LoadableAccess
{
    /**
     * @var Files
     */
    protected $record;

    /**
     * @param Files $record
     *
     * @return $this
     */
    public function setRecord(Files $record): self
    {
        $this->record = $record;
        return $this;
    }

    /**
     * @throws ZFE_File_Exception
     *
     * @return Files|Doctrine_Record
     */
    public function getRecord(): Files
    {
        if (empty($this->record)) {
            throw new ZFE_File_Exception('Объект не задан');
        }
        return $this->record;
    }
}
