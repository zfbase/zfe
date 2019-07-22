<?php

/*
 * ZFE – платформа для построения редакторских интерфейсов.
 */

/**
 * Класс управления записью модели для управления ее файлами.
 */
abstract class ZFE_File_ManageableAccess
{
    /**
     * @var ZFE_File_Manageable
     */
    protected $record;

    /**
     * @param ZFE_File_Manageable $record
     *
     * @return $this
     */
    public function setRecord(ZFE_File_Manageable $record): self
    {
        $this->record = $record;
        return $this;
    }

    /**
     * @throws ZFE_File_Exception
     *
     * @return ZFE_File_Manageable|Doctrine_Record
     */
    public function getRecord(): ZFE_File_Manageable
    {
        if (empty($this->record)) {
            throw new ZFE_File_Exception('Объект не задан');
        }
        return $this->record;
    }
}
