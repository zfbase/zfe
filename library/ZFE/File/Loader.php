<?php
/**
 * Created by PhpStorm.
 * User: dezzpil
 * Date: 09.10.18
 * Time: 12:21
 */

/**
 * Class Helper_File_Loader
 *
 * @todo заменить Application_Exception на Helper_File_Exception
 */
final class Helper_File_Loader extends Helper_File_LoadableAccess
{
    /**
     * @var Zend_Config
     */
    protected $config;

    /**
     * @var string
     */
    protected $dataDir;

    /**
     * @var string
     */
    protected $method = 'rename';

    /**
     * Helper_File_Loader constructor.
     * @param Zend_Config $config
     * @param string $dataDir абсолютный путь к папке, хранящей файлы моделей
     * @throws Application_Exception
     */
    public function __construct(Zend_Config $config, string $dataDir)
    {
        if ($config->path && $config->url) {
            $this->config = $config;
        } else {
            throw new Application_Exception('Не задано одно или несколько обязательных значений: path, url');
        }
        if (!is_dir($dataDir) || !is_writable($dataDir)) {
            throw new Application_Exception($dataDir . ' не существует или не доступна для записи');
        }

        if (strpos($dataDir, '/') !== strlen($dataDir) - 1) {
            $dataDir .= '/';
        }
        $this->dataDir = $dataDir;
    }

    /**
     * @return Zend_Config
     */
    public function getConfig() : Zend_Config
    {
        return $this->config;
    }

    /**
     * @param string $resultPath
     * @return string
     */
    public function relateFilePath(string $resultPath) : string
    {
        if (strpos($resultPath, $this->dataDir) !== false) {
            $tmp = str_replace($this->dataDir, '', $resultPath);
        }

        return $tmp;
    }

    /**
     * @return string
     * @throws Application_Exception
     * @throws Doctrine_Record_Exception
     * @throws Zend_Exception
     */
    public function absFilePath() : string
    {
        $file = $this->getRecord();
        if (empty($file->get('path'))) {
            // костыль - выпилить при апгрейде
            // для совместимости с ранее загруженными файлами
            return $this->getResultPath();
        }
        return $this->dataDir . $file->get('path');
    }

    /**
     * @return string
     * @throws Application_Exception
     * @throws Zend_Exception
     */
    public function getBaseDir() : string
    {
        $path = $this->getConfig()->path;
        // realpath заменяет папку-симлинк на реальный путь
        //$realPath = realpath($path);
        $realPath = $path;
        if (!is_dir($realPath) || !is_readable($realPath)) {
            throw new Application_Exception($realPath . ' не существует или не доступно для чтения');
        }
        return $realPath;
    }

    /** Число разрядов для разбиения идентификатора */
    protected $div = 3;

    /**
     * Генерировать путь для файла
     *
     * @param string $basePath
     * @param integer|string $id
     * @param string $ext
     * @param boolean $isUrl
     * @param boolean $andRand
     * @return string
     */
    protected function generationPath($basePath, $id = 0, $ext = '', $isUrl = false, $andRand = false)
    {
//        if (! $isUrl) {
//            $basePath = realpath($basePath);
//        }


        if (strlen($id) > $this->div) {
            $strparts = str_split($id, $this->div);
        } else {
            return $basePath . '/' . $id;
        }

        $fileName = array_pop($strparts);
        $subPath = implode('/', $strparts);

        if (! $isUrl && ! file_exists($basePath . '/' . $subPath)) {
            self::makePath($basePath . '/' . $subPath);
            self::fixPath($basePath, $subPath);
        }

        return $basePath . '/' .
            (!empty($subPath) ? $subPath . '/' : '') .
            $fileName .
            (empty($ext) ? '' : '.' . $ext) .
            ($isUrl && $andRand ? '?r=' . rand() : '');
    }

    /**
     * Безопасно рекурсивно создать директорию
     * Если родительской директории нету – создать.
     *
     * @param $path
     * @throws Exception
     */
    protected function makePath($path)
    {
        if ( ! file_exists($path)) {
            if ( ! @mkdir($path, 0755, true)) {
                throw new Exception("Mkdir failed for path '{$path}'");
            }
        }
    }

    /**
     * Настроить права на директорию файла
     * Настраивать права на все папки от базовой до конкретной.
     *
     * @param $basePath
     * @param null $subPath
     * @throws Zend_Exception
     */
    public function fixPath($basePath, $subPath = null)
    {
        $uploadConfig = Zend_Registry::get('config')->forms->upload;

        $workPath = $basePath;
        $pathArr = explode('/', $subPath);
        foreach ($pathArr as $part) {
            $workPath .= '/' . $part;
            @chmod($workPath, 0777);
            @chown($workPath, $uploadConfig->default->owner);
            @chgrp($workPath, $uploadConfig->default->group);
        }
    }

    /**
     * @return string
     *
     * @throws Application_Exception
     * @throws Doctrine_Record_Exception
     * @throws Zend_Exception
     * @throws Helper_File_Exception
     */
    public function getResultPath() : string
    {
        $baseDir = $this->getBaseDir();
        $foreignKeyColumn = Helper_File_Loadable::KEY_TO_ITEM;
        $folderName = $this->getRecord()->get($foreignKeyColumn);

        // генерируем правильно
        //$path = ZFE_File::generationPath($baseDir, $folderName);
        // эти методы я перенес из ZFE_File, надо доработать

        $path = $this->generationPath($baseDir, $folderName);
        //var_dump($baseDir, $folderName, $path);die;
        $this->makePath($path);
        $this->fixPath($baseDir, $path);
        //var_dump($path, $this->getRecord()->toArray(0));die;
        if (is_writable($path)) {
            $resultPath = $path . '/' . $this->getRecord()->title;
            return $resultPath;
        }
        throw new Helper_File_Exception('Путь ' . $path . ' недоступен для записи');
    }

    /**
     * Копировать файлы в целевую директорию во время загрузки
     * @return $this
     */
    public function useCopy()
    {
        $this->method = 'copy';
        return $this;
    }

    /**
     * Перемещать файлы в целевую директорию во время загрузки
     * @return $this
     */
    public function useMove()
    {
        $this->method = 'rename';
        return $this;
    }

    /**
     * @param string $fromPath
     * @return Helper_File_Loadable
     *
     * @throws Application_Exception
     * @throws Doctrine_Record_Exception
     * @throws Zend_Exception
     */
    public function upload(string $fromPath = null) : Helper_File_Loadable
    {
        $file = $this->getRecord();
        if (!$fromPath) {
            $mapper = new Helper_File_PathMapper($file);
            if ($mapper->isMapped()) {
                $fromPath = $mapper->getMapped();
            }
        }
        if (empty($fromPath)) {
            throw new Application_Exception('Не указан путь, из которого надо переместить файл');
        }
        $resultPath = $this->getResultPath();

        if ($this->method == 'copy') {
            copy($fromPath, $resultPath);
        } else {
            rename($fromPath, $resultPath);
        }
        if (!file_exists($resultPath)) {
            throw new Application_Exception(
                sprintf('Не удалось переместить файл из %s в %s', $fromPath, $resultPath)
            );
        }

        // set result path to record
        $relResultPath = $this->relateFilePath($resultPath);
        $file->set('path', $relResultPath);
        return $file;
    }

    /**
     * @return bool
     * @throws Application_Exception
     * @throws Doctrine_Record_Exception
     * @throws Zend_Exception
     */
    public function erase() : bool
    {
        try {
            $resultPath = $this->getResultPath();
            return @unlink($resultPath);
        } catch (Application_Exception $e) {
            return true;
        }
    }

    /**
     * @return string
     */
    public function getBaseUrl() : string
    {
        $url = $this->getConfig()->url;
        return $url;
    }
}
