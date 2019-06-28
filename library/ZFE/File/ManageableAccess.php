<?php

abstract class ZFE_File_ManageableAccess
{
    /**
     * @var ZFE_File_Manageable
     */
    protected $record;

    /**
     * @param ZFE_File_Manageable $record
     * @return $this
     */
    public function setRecord(ZFE_File_Manageable $record) : self
    {
        $this->record = $record;
        return $this;
    }

    /**
     * @return ZFE_File_Manageable|Doctrine_Record
     * @throws ZFE_File_Exception
     */
    public function getRecord() : ZFE_File_Manageable
    {
        if (empty($this->record)) {
            throw new ZFE_File_Exception('Объект не задан');
        }
        return $this->record;
    }
}