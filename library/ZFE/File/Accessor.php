<?php

/*
 * ZFE – платформа для построения редакторских интерфейсов.
 */

/**
 * Стандартный контролер управления файлами.
 */
abstract class ZFE_File_Accessor extends ZFE_File_ManageableAccess
{
    /**
     * @var Zend_Acl
     */
    protected $acl;

    /**
     * @var ZFE_Model_Default_Editors
     */
    protected $user;

    /**
     * @var string
     */
    protected $role;

    /**
     * @var string
     */
    protected $controller = 'files';

    /**
     * @param Zend_Acl $acl
     * @param Editors  $user
     * @param string   $role
     *
     * @throws Zend_Auth_Exception
     */
    public function __construct(Zend_Acl $acl, ZFE_Model_Default_Editors $user, string $role = null)
    {
        $this->acl = $acl;
        $this->user = $user;
        $this->role = $role ?? $user->role;
    }

    /**
     * @return ZFE_Model_Default_Editors
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param ZFE_Model_Default_Editors $user
     */
    public function setUser(ZFE_Model_Default_Editors $user): void
    {
        $this->user = $user;
    }

    protected function generateURL(string $action, Files $file = null)
    {
        $r = $this->getRecord();
        $rClass = get_class($r);
        $baseUrl = sprintf(
            '/%s/%s/m/%s/id/%d',
            $this->controller,
            $action,
            $rClass,
            $r->id
        );
        if ($file) {
            $baseUrl .= '/fid/' . $file->id;
        }
        return $baseUrl;
    }

    /**
     * Проверить прав на просмотр всех файлов записи списком
     *
     * @return bool
     */
    public function isAllowToView(): bool
    {
        return true;
    }

    /**
     * Получить ссылку на просмотр файлов записи списком
     *
     * @return null|string
     */
    public function getViewURL(): ?string
    {
        if ($this->isAllowToView()) {
            return $this->generateURL('view');
        }
        return null;
    }

    /**
     * Проверить прав на просмотр всех файлов записи списком
     *
     * @return bool
     */
    public function isAllowToControl(): bool
    {
        return true;
    }

    /**
     * Получить ссылку на просмотр файлов записи списком.
     *
     * @return null|string
     */
    public function getControlURL(): ?string
    {
        if ($this->isAllowToControl()) {
            return $this->generateURL('control');
        }
        return null;
    }

    /**
     * Проверить права на удаление файла.
     *
     * @return bool
     */
    public function isAllowToDelete(): bool
    {
        return true;
    }

    /**
     * Получить ссылку на удаление файла записи.
     *
     * @param Files $file
     *
     * @return null|string
     */
    public function getDeleteURL(Files $file): ?string
    {
        if ($this->isAllowToDelete()) {
            return $this->generateURL('delete', $file);
        }
        return null;
    }

    /**
     * Проверить права на скачивание файла.
     *
     * @return bool
     */
    public function isAllowToDownload(): bool
    {
        return true;
    }

    /**
     * Получить ссылку на скачивание файла записи.
     *
     * @param Files $file
     *
     * @return null|string
     */
    public function getDownloadURL(Files $file): ?string
    {
        if ($this->isAllowToDownload()) {
            return $this->generateURL('download', $file);
        }
        return null;
    }

    /**
     * Проверить права на скачивание файлов одним архивом.
     *
     * @return bool
     */
    public function isAllowToDownloadAll(): bool
    {
        return true;
    }

    /**
     * Получить ссылку на скачивание скачивание файлов записи одним архивом.
     *
     * @return null|string
     */
    public function getDownloadAllURL(): ?string
    {
        if ($this->isAllowToDownloadAll()) {
            return $this->generateURL('download-all');
        }
        return null;
    }

    /**
     * Проверить права на скачивание файлов одним архивом.
     *
     * @return bool
     */
    public function isAllowToProcess(): bool
    {
        return true;
    }

    /**
     * Получить ссылку на скачивание скачивание файлов записи одним архивом.
     *
     * @param Files $file
     *
     * @return null|string
     */
    public function getProcessURL(Files $file): ?string
    {
        if ($this->isAllowToDownload()) {
            return $this->generateURL('process', $file);
        }
        return null;
    }

    /**
     * @param string $url
     *
     * @return array
     */
    final public function decomposeURL(string $url): array
    {
        $parts = explode('/', $url);
        $action = $parts[2];
        $params = ['m' => $parts[4], 'id' => $parts[6]];
        if (count($parts) > 7) {
            $params['fid'] = $parts['8'];
        }
        return [
            'module' => null,
            'controller' => $this->controller,
            'action' => $action,
            'params' => $params,
        ];
    }
}
