<?php

/*
 * ZFE – платформа для построения редакторских интерфейсов.
 */

/**
 * Средства связи записей с их файлами.
 */
trait ZFE_Model_AbstractRecord_Files
{
    /**
     * Получить файл колонки (поля, элемента формы).
     *
     * @param string $name
     *
     * @return null|ZFE_File
     */
    public function getFileColumn($name)
    {
        return null;
    }

    /**
     * Получить файлы колонки (поля, элемента формы).
     *
     * @param string $name
     *
     * @return null|ZFE_File
     */
    public function getFilesColumn($name)
    {
        return null;
    }
}
