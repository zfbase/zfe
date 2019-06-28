<?php
/**
 * Created by PhpStorm.
 * User: dezzpil
 * Date: 11.10.18
 * Time: 14:45
 */

interface Helper_File_Processing
{
    /**
     * Получить процессор, соответств. модели
     * @return ZFE_File_Processor
     */
    function getProcessor() : ZFE_File_Processor;


    function isPlanned() : bool;

    function isCompleted() : bool;


    function linkFile(ZFE_File_Loadable $file) : Helper_File_Processing;

    function getLinkedFile() : ZFE_File_Loadable;


    function setError(int $code, string $message = null) : Helper_File_Processing;

    function hasError() : bool;

    function getErrorDesc($withMsg = false);
}
