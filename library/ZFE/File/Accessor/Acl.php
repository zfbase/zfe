<?php

class ZFE_File_Accessor_Acl extends ZFE_File_Accessor
{
    /**
     * Проверить прав на просмотр всех файлов записи списком
     * @return bool
     */
    function isAllowToView() : bool
    {
        return $this->acl->isAllowed($this->role, $this->controller, 'view');
    }

    /**
     * Проверить права на управление файлами
     * @return bool
     */
    function isAllowToControl(): bool
    {
        return $this->acl->isAllowed($this->role, $this->controller, 'control');
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