<?php

/*
 * ZFE – платформа для построения редакторских интерфейсов.
 */

/**
 * Помощник отправки файла средствами веб-сервера Apache через авторизацию приложения.
 *
 * @todo Переделать на получение параметром объекта соответствующего интерфейсу файла.
 */
class ZFE_Controller_Action_Helper_DownloadApache extends Zend_Controller_Action_Helper_Abstract
{
    /**
     * Отправить файл средствами сервера через авторизацию приложения.
     *
     * @param string $path путь до файла в файловой системе
     * @param string $name новое имя файла
     *
     * @throws Zend_Controller_Action_Exception
     */
    public function direct($path, $name)
    {
        if (file_exists($path)) {
            // сбрасываем буфер вывода PHP, чтобы избежать переполнения памяти выделенной под скрипт
            // если этого не сделать файл будет читаться в память полностью!
            if (ob_get_level()) {
                ob_end_clean();

                // заставляем браузер показать окно сохранения файла
                $response = $this->getResponse();
                $response->clearAllHeaders();
                $response->clearBody();
                $response->setHeader('Content-Description', 'File Transfer');
                $response->setHeader('Content-Type', 'application/octet-stream');
                $response->setHeader('Content-Disposition', 'attachment; filename="' . $name . '"');
                $response->setHeader('Content-Transfer-Encoding', 'binary');
                $response->setHeader('Expires', '0');
                $response->setHeader('Cache-Control', 'must-revalidate');
                $response->setHeader('Pragma', 'public');
                $response->setHeader('Content-Length', filesize($path));
                $response->sendResponse(); // читаем файл и отправляем его пользователю

                readfile($path);
                die;
            }
        } else {
            throw new Zend_Controller_Action_Exception('Файл не найден', 404);
        }
    }
}
