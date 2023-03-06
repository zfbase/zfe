<?php

/*
 * ZFE – платформа для построения редакторских интерфейсов.
 */

/**
 * Помощник отправки файла средствами встроенного в PHP веб-сервера через авторизацию приложения.
 *
 * @see https://habr.com/post/151795/
 */
class ZFE_Controller_Action_Helper_DownloadPhp extends ZFE_Controller_Action_Helper_Download
{
    /**
     * Отправить файл средствами встроенного в PHP через авторизацию приложения.
     *
     * @param string $path путь до файла в файловой системе
     * @param string $url  защищенный виртуальный URL
     * @param string $name новое имя файла
     * @param bool $download Отключение принудительной загрузки
     *
     * @throws Zend_Controller_Action_Exception
     */
    public function direct($path, $url, $name, $download)
    {
        if (file_exists($path)) {
            // если этого не сделать файл будет читаться в память полностью!
            if (ob_get_level()) {
                ob_end_clean();
            }

            $response = $this->factoryResponse($path, $name, $download);
            $response->sendResponse();

            readfile($path);
            exit;
        }

        throw new Zend_Controller_Action_Exception('Файл не найден', 404);
    }
}
