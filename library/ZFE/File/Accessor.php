<?php
/**
 * Created by PhpStorm.
 * User: dezzpil
 * Date: 16.10.18
 * Time: 14:33
 */

/**
 * Class Helper_File_Accessor
 */
abstract class ZFE_File_Accessor extends ZFE_File_ManageableAccess
{
    /**
     * @var Zend_Acl
     */
    protected $acl;

    /**
     * @var Editors
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
     * Helper_File_Arbiter constructor.
     * @param Zend_Acl $acl
     * @param Editors $user
     * @param string $role
     * @throws Zend_Auth_Exception
     */
    public function __construct(Zend_Acl $acl, ZFE_Model_Default_Editors $user, string $role = null)
    {
        $this->acl = $acl;
        $this->user = $user;
        $this->role = $role ?? $user->role;
    }

    /**
     * @return mixed
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param mixed $user
     */
    public function setUser($user): void
    {
        $this->user = $user;
    }

    protected function generateURL(string $action, Files $file = null)
    {
        $r = $this->getRecord();
        $rClass = get_class($r);
        $baseUrl = sprintf(
            "/%s/%s/m/%s/id/%d",
            $this->controller, $action, $rClass, $r->id
        );
        if ($file) {
            $baseUrl .= '/fid/' . $file->id;
        }
        return $baseUrl;
    }

    /**
     * Проверить прав на просмотр всех файлов записи списком
     * @return bool
     */
    function isAllowToView() : bool
    {
        return true;
    }

    /**
     * Получить ссылку на просмотр файлов записи списком
     * @return null|string
     */
    function getViewURL() : ?string
    {
        if ($this->isAllowToView()) {
            return $this->generateURL('view');
        }
        return null;
    }

    /**
     * Проверить прав на просмотр всех файлов записи списком
     * @return bool
     */
    function isAllowToControl() : bool
    {
        return true;
    }

    /**
     * Получить ссылку на просмотр файлов записи списком
     * @return null|string
     */
    function getControlURL() : ?string
    {
        if ($this->isAllowToControl()) {
            return $this->generateURL('control');
        }
        return null;
    }

    /**
     * Проверить права на удаление файла
     * @return bool
     */
    function isAllowToDelete() : bool
    {
        return true;
    }

    /**
     * Получить ссылку на удаление файла записи
     * @param Files $file
     * @return null|string
     */
    function getDeleteURL(Files $file) : ?string
    {
        if ($this->isAllowToDelete()) {
            return $this->generateURL('delete', $file);
        }
        return null;
    }

    /**
     * Проверить права на скачивание файла
     * @return bool
     */
    function isAllowToDownload() : bool
    {
        return true;
    }

    /**
     * Получить ссылку на скачивание файла записи
     * @param Files $file
     * @return null|string
     */
    function getDownloadURL(Files $file) : ?string
    {
        if ($this->isAllowToDownload()) {
            return $this->generateURL('download', $file);
        }
        return null;
    }

    /**
     * Проверить права на скачивание файлов одним архивом
     * @return bool
     */
    function isAllowToDownloadAll() : bool
    {
        return true;
    }

    /**
     * Получить ссылку на скачивание скачивание файлов записи одним архивом
     * @return null|string
     */
    function getDownloadAllURL() : ?string
    {
        if ($this->isAllowToDownloadAll()) {
            return $this->generateURL('download-all');
        }
        return null;
    }

    /**
     * Проверить права на скачивание файлов одним архивом
     * @return bool
     */
    function isAllowToProcess() : bool
    {
        return true;
    }

    /**
     * Получить ссылку на скачивание скачивание файлов записи одним архивом
     * @param Files $file
     * @return null|string
     */
    function getProcessURL(Files $file) : ?string
    {
        if ($this->isAllowToDownload()) {
            return $this->generateURL('process', $file);
        }
        return null;
    }

    /**
     * @param string $url
     * @return array
     */
    final function decomposeURL(string $url) : array
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
            'params' => $params
        ];
    }
}