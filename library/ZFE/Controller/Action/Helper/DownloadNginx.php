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
class ZFE_Controller_Action_Helper_DownloadNginx extends Zend_Controller_Action_Helper_Abstract
{
    /**
     * Отправить файл средствами nginx через авторизацию приложения.
     *
     * @param string $path путь до файла в файловой системе
     * @param string $url  защищенный виртуальный URL
     * @param string $name новое имя файла
     */
    public function direct($path, $url, $name)
    {
        if (file_exists($path)) {
            $response = $this->getResponse();
            $response->clearAllHeaders();
            $response->clearBody();

            $mime = mime_content_type($path);
            if ($mime === false) {
                $mime = 'application/octet-stream';
            }

            $response->setHeader('Content-Description', 'File Transfer');
            $response->setHeader('Content-Type', $mime);

            // @see https://stackoverflow.com/q/7285372
            // $response->setHeader('Content-Transfer-Encoding', 'binary');

            $response->setHeader('Content-Disposition', 'attachment; filename="' . $name . '"');
            $response->setHeader('Expires', '0');
            $response->setHeader('Cache-Control', 'must-revalidate');
            $response->setHeader('Pragma', 'public');
            $response->setHeader('Content-Length', filesize($path));
            $response->setHeader('X-Accel-Redirect', $url);
            $response->sendResponse();
            exit;
        }

        $this->abort(404, 'Файл не найден');
    }
}
