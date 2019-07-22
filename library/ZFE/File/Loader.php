<?php

/**
 * Class ZFE_File_Loader
 */
final class ZFE_File_Loader extends ZFE_File_LoadableAccess
{
    /**
     * @var Zend_Config секция files
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
     * @var int Число разрядов для разбиения идентификатора
     */
    protected $div = 3;

    /**
     * ZFE_File_Loader constructor.
     * @param Zend_Config $config секция files
     * @throws ZFE_File_Exception
     */
    public function __construct(Zend_Config $config)
    {
        $this->config = $config;
        $dataDir = $config->path;
        if (!is_dir($dataDir) || !is_writable($dataDir)) {
            throw new ZFE_File_Exception($dataDir . ' не существует или не доступна для записи');
        }

        $this->dataDir = preg_replace('/\/+$/', '', $dataDir);
    }

    /**
     * @param string $resultPath
     * @return string
     */
    public function relateFilePath(string $resultPath) : string
    {
        if (strpos($resultPath, $this->dataDir) !== false) {
            $tmp = str_replace($this->dataDir, '', $resultPath);
        } else {
            throw new ZFE_File_Exception('Невозможно определить относительный путь файла для ' . $resultPath);
        }

        return $tmp;
    }

    /**
     * @return string
     * @throws ZFE_File_Exception
     * @throws Doctrine_Record_Exception
     * @throws ZFE_File_Exception
     * @throws Zend_Exception
     */
    public function absFilePath() : string
    {
        $file = $this->getRecord();
        return $this->dataDir . $file->get('path');
    }

    /**
     * @return string
     * @throws ZFE_File_Exception
     * @throws Zend_Exception
     */
    public function getBaseDir() : string
    {
        return $this->dataDir;
    }

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
    protected function generatePath($basePath, $id = 0, $ext = '', $isUrl = false, $andRand = false)
    {
        $basePath = preg_replace('/\/+$/', '', $basePath);
        if (!is_writable($basePath)) {
            throw new ZFE_File_Exception('Путь ' . $basePath . ' недоступен для записи');
        }

        if (strlen($id) > $this->div) {
            $parts = str_split($id, $this->div);
        } else {
            $parts = [$id];
        }

        $fileName = array_pop($parts);
        $subPath = implode('/', $parts);

        if (! $isUrl && ! file_exists($basePath . '/' . $subPath)) {
            $this->makePath($basePath . '/' . $subPath);
            $this->fixPath($basePath, $subPath);
        }

        $result = $basePath . '/' .
            (!empty($subPath) ? $subPath . '/' : '') .
            $fileName .
            (empty($ext) ? '' : '.' . $ext) .
            ($isUrl && $andRand ? '?r=' . rand() : '');

        return $result;
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
        $workPath = $basePath;
        $pathArr = explode('/', $subPath);
        foreach ($pathArr as $part) {
            $workPath .= '/' . $part;
            @chmod($workPath, 0777);
            @chown($workPath, $this->config->owner);
            @chgrp($workPath, $this->config->group);
        }
    }

    /**
     * @return string
     *
     * @throws ZFE_File_Exception
     * @throws Doctrine_Record_Exception
     * @throws Zend_Exception
     * @throws ZFE_File_Exception
     */
    public function getResultPath() : string
    {
        $baseDir = $this->getBaseDir();
        if (empty($this->record->path)) {
            $path = $this->generatePath($baseDir, $this->record->id);
        } else {
            $path = $baseDir . $this->record->path;
        }
        // if (is_writable($path)) {
            // $resultPath = $path . '/' . $this->getRecord()->title;
            // return $resultPath;
        //}
        return $path;
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
     * @return Files
     *
     * @throws ZFE_File_Exception
     * @throws Doctrine_Record_Exception
     * @throws Zend_Exception
     */
    public function upload(string $fromPath = null) : Files
    {
        $file = $this->getRecord();
        if (!$fromPath) {
            $mapper = new ZFE_File_PathMapper($file);
            if ($mapper->isMapped()) {
                $fromPath = $mapper->getMapped();
            }
        }
        if (empty($fromPath)) {
            throw new ZFE_File_Exception('Не указан путь, из которого надо переместить файл');
        }

        $resultPath = $this->getResultPath();

        if ($this->method == 'copy') {
            copy($fromPath, $resultPath);
        } else {
            rename($fromPath, $resultPath);
        }
        if (!file_exists($resultPath)) {
            throw new ZFE_File_Exception(
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
     * @throws ZFE_File_Exception
     * @throws Doctrine_Record_Exception
     * @throws Zend_Exception
     */
    public function erase() : bool
    {
        try {
            $resultPath = $this->getResultPath();
            return @unlink($resultPath);
        } catch (ZFE_File_Exception $e) {
            return true;
        }
    }
}
