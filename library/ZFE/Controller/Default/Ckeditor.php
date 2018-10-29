<?php

/*
 * ZFE – платформа для построения редакторских интерфейсов.
 */

/**
 * Cтандартный контроллер Ajax-методов CkeditorCkeditor.
 *
 * @category  ZFE
 */
class ZFE_Controller_Default_Ckeditor extends Controller_Abstract
{
    /**
     * Загрузить изображение.
     */
    public function uploadAction()
    {
        $config = Zend_Registry::get('config');
        $baseDir = realpath($config->forms->upload->ckeditor->path);
        $baseUrl = $config->forms->upload->ckeditor->webPath;

        $adapter = new Zend_File_Transfer_Adapter_Http();

        $hash = $adapter->getHash();
        $localDir = implode('/', str_split($hash, 4));
        $fullDir = $baseDir . '/' . $localDir;
        ZFE_File::makePath($fullDir);
        ZFE_File::fixPath($baseUrl, $localDir);
        $adapter->setDestination($fullDir);
        $fileName = $adapter->getFileName('upload', false);

        $responce = [
            'uploaded' => 1,
            'fileName' => $fileName,
            'url' => $baseUrl . '/' . $localDir . '/' . $fileName,
        ];

        if ( ! $adapter->receive()) {
            $responce['error'] = [
                'message' => implode("\n", $adapter->getMessages()),
            ];
        }

        $this->_helper->json($responce);
    }
}
