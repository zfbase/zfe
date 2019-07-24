<?php

/*
 * ZFE – платформа для построения редакторских интерфейсов.
 */

/**
 * Заглушка определителя иконки файла.
 */
class ZFE_File_Icons_Bootstrap extends ZFE_File_Icons
{
    public function getFor(string $ext)
    {
        return 'glyphicon glyphicon-file';
    }
}
