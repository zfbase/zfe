<?php

/*
 * ZFE – платформа для построения редакторских интерфейсов.
 */

/**
 * Проверки прав на основные операции с файлами.
 */
class ZFE_File_Accessor_Acl extends ZFE_File_Accessor
{
    /**
     * Проверить права на просмотр всех файлов записи списком.
     *
     * @return bool
     */
    public function isAllowToView(): bool
    {
        return $this->acl->isAllowed($this->role, $this->controller, 'view');
    }

    /**
     * Проверить права на управление файлами.
     *
     * @return bool
     */
    public function isAllowToControl(): bool
    {
        return $this->acl->isAllowed($this->role, $this->controller, 'control');
    }

    /**
     * Проверить права на удаление файла.
     *
     * @return bool
     */
    public function isAllowToDelete(): bool
    {
        return $this->acl->isAllowed($this->role, $this->controller, 'delete');
    }

    /**
     * Проверить права на скачивание файла.
     *
     * @return bool
     */
    public function isAllowToDownload(): bool
    {
        return $this->acl->isAllowed($this->role, $this->controller, 'download');
    }

    /**
     * Проверить права на скачивание файлов одним архивом.
     *
     * @return bool
     */
    public function isAllowToDownloadAll(): bool
    {
        return $this->acl->isAllowed($this->role, $this->controller, 'download-all');
    }

    /**
     * Проверить права на скачивание файлов одним архивом.
     *
     * @return bool
     */
    public function isAllowToProcess(): bool
    {
        return $this->acl->isAllowed($this->role, $this->controller, 'process');
    }
}
