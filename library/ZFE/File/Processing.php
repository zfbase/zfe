<?php

/**
 * Interface ZFE_File_Processing
 * Интерфейс для модели, которая хранит данные обработки файлов
 */
interface ZFE_File_Processing
{
    /**
     * Получить процессор, соответств. модели
     * @return ZFE_File_Processor
     */
    function getProcessor() : ZFE_File_Processor;


    function isPlanned() : bool;

    function isCompleted() : bool;


    function linkFile(Files $file) : ZFE_File_Processing;

    function getLinkedFile() : Files;


    function setError(int $code, string $message = null) : ZFE_File_Processing;

    function hasError() : bool;

    function getErrorDesc($withMsg = false);
}
