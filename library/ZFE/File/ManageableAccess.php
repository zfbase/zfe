<?php
/**
 * Created by PhpStorm.
 * User: dezzpil
 * Date: 09.10.18
 * Time: 15:59
 */

abstract class Helper_File_ManageableAccess
{
    /**
     * @var Helper_File_Manageable
     */
    protected $record;

    /**
     * @param Helper_File_Manageable $record
     * @return $this
     */
    public function setRecord(Helper_File_Manageable $record) : self
    {
        $this->record = $record;
        return $this;
    }

    /**
     * @return Helper_File_Manageable|Doctrine_Record
     * @throws Application_Exception
     */
    public function getRecord() : Helper_File_Manageable
    {
        if (empty($this->record)) {
            throw new Application_Exception('Объект не задан');
        }
        return $this->record;
    }
}