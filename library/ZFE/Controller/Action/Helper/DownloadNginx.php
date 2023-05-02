<?php

/*
 * ZFE – платформа для построения редакторских интерфейсов.
 */

/**
 * Помощник отправки файла средствами веб-сервера nginx через авторизацию приложения.
 *
 * @todo Переделать на получение параметром объекта соответствующего интерфейсу файла.
 *
 * @see http://habrahabr.ru/post/37686/
 */
class ZFE_Controller_Action_Helper_DownloadNginx extends ZFE_Controller_Action_Helper_Download
{
    /**
     * Отправить файл средствами веб-сервера nginx через авторизацию приложения.
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
            $response = $this->factoryResponse($path, $name, $download);
            $response->setHeader('X-Accel-Redirect', $url);
            $response->sendResponse();
            exit;
        }

        throw new Zend_Controller_Action_Exception('Файл не найден', 404);
    }
}
