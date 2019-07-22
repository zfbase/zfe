<?php

/*
 * ZFE – платформа для построения редакторских интерфейсов.
 */

class ZFE_File_PathMapper
{
    const KEY_MAPPED_PATH = 'tmp_path';

    /**
     * @var Files
     */
    protected $file;

    /**
     * Helper_File_PathMapper constructor.
     *
     * @param Files $file
     */
    public function __construct(Files $file)
    {
        $this->file = $file;
    }

    /**
     * @param string $path
     *
     * @return ZFE_File_PathMapper
     */
    public function map(string $path): self
    {
        $key = static::KEY_MAPPED_PATH;
        $this->file->mapValue($key, $path);
        return $this;
    }

    /**
     * @return bool
     */
    public function isMapped(): bool
    {
        $key = static::KEY_MAPPED_PATH;
        return $this->file->hasMappedValue($key);
    }

    /**
     * @throws Doctrine_Record_Exception
     *
     * @return string
     */
    public function getMapped(): string
    {
        $key = static::KEY_MAPPED_PATH;
        return $this->file->get($key);
    }
}
