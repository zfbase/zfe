<?php
/**
 * Created by PhpStorm.
 * User: dezzpil
 * Date: 09.10.18
 * Time: 15:42
 */

class Helper_File_PathMapper
{
    const KEY_MAPPED_PATH = 'tmp_path';

    /**
     * @var Doctrine_Record
     */
    protected $record;

    /**
     * Helper_File_PathMapper constructor.
     * @param Helper_File_Loadable $record
     */
    public function __construct(Helper_File_Loadable $record)
    {
        $this->record = $record;
    }

    /**
     * @param string $path
     * @return Helper_File_PathMapper
     */
    public function map(string $path) : self
    {
        $key = static::KEY_MAPPED_PATH;
        $this->record->mapValue($key, $path);
        return $this;
    }

    /**
     * @return bool
     */
    public function isMapped() : bool
    {
        $key = static::KEY_MAPPED_PATH;
        return $this->record->hasMappedValue($key);
    }

    /**
     * @return string
     * @throws Doctrine_Record_Exception
     */
    public function getMapped() : string
    {
        $key = static::KEY_MAPPED_PATH;
        return $this->record->get($key);
    }
}