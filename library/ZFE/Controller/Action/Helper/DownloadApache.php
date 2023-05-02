<?php

/*
 * ZFE – платформа для построения редакторских интерфейсов.
 */

/**
 * Помощник отправки файла средствами веб-сервера Apache через авторизацию приложения.
 *
 * @todo Переделать на получение параметром объекта соответствующего интерфейсу файла.
 */
class ZFE_Controller_Action_Helper_DownloadApache extends ZFE_Controller_Action_Helper_Download
{
    /**
     * Отправить файл средствами веб-сервера Apache через авторизацию приложения.
     *
     * @param string $path путь до файла в файловой системе
     * @param string $url  защищенный виртуальный URL
     * @param string $name новое имя файла
     * @param bool $download отключение принудительной загрузки
     *
     * @throws Zend_Controller_Action_Exception
     */
    public function direct($path, $url, $name, $download = true)
    {
        if (file_exists($path)) {
            // сбрасываем буфер вывода PHP, чтобы избежать переполнения памяти выделенной под скрипт
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
