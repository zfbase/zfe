<?php
/**
 * Created by PhpStorm.
 * User: dezzpil
 * Date: 09.10.18
 * Time: 15:21
 */

interface Helper_File_Processable extends Helper_File_Loadable
{
    /**
     * Возвращает описание допустимых обработок для файлов
     * @return Helper_File_Processor_Mapping
     */
    public function getProcessings($refresh = false) : Helper_File_Processor_Mapping;
}
