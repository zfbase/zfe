<?php
/**
 * Created by PhpStorm.
 * User: dezzpil
 * Date: 09.10.18
 * Time: 12:08
 */

interface Helper_File_Manageable
{
    public function getFileManager($accessControl = true) : Helper_File_Manager;

    public function getFilesRelationName() : string;
}