<?php
/**
 * Created by PhpStorm.
 * User: dezzpil
 * Date: 16.10.18
 * Time: 14:33
 */

class Helper_File_Accessor_Acl extends Helper_File_Accessor
{
    /**
     * Проверить прав на просмотр всех файлов записи списком
     * @return bool
     */
    function isAllowToList() : bool
    {
        return $this->acl->isAllowed($this->role, $this->controller, 'list');
    }

    /**
     * Проверить права на удаление файла
     * @return bool
     */
    function isAllowToDelete() : bool
    {
        return $this->acl->isAllowed($this->role, $this->controller, 'delete');
    }

    /**
     * Проверить права на скачивание файла
     * @return bool
     */
    function isAllowToDownload() : bool
    {
        return $this->acl->isAllowed($this->role, $this->controller,'download');
    }

    /**
     * Проверить права на скачивание файлов одним архивом
     * @return bool
     */
    function isAllowToDownloadAll() : bool
    {
        return $this->acl->isAllowed($this->role, $this->controller,'download-all');
    }

    /**
     * Проверить права на скачивание файлов одним архивом
     * @return bool
     */
    function isAllowToProcess() : bool
    {
        return $this->acl->isAllowed($this->role, $this->controller,'process');
    }


}