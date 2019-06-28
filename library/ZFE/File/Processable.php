<?php
/**
 * Created by PhpStorm.
 * User: dezzpil
 * Date: 09.10.18
 * Time: 15:21
 */

interface ZFE_File_Processable extends ZFE_File_Loadable
{
    /**
     * Возвращает описание допустимых обработок для файлов
     * @return ZFE_File_Processor_Mapping
     */
    public function getProcessings($refresh = false) : ZFE_File_Processor_Mapping;
}
