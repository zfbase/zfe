<?php

/*
 * ZFE – платформа для построения редакторских интерфейсов.
 */

/**
 * Помощник отправки файла средствами php-сервера через авторизацию приложения.
 *
 * @see https://habr.com/post/151795/
 */
class ZFE_Controller_Action_Helper_DownloadPhp extends Zend_Controller_Action_Helper_Abstract
{
    /**
     * Отправить файл средствами php-сервера через авторизацию приложения.
     *
     * @param string $path путь до файла в файловой системе
     * @param string $name новое имя файла
     */
    public function direct($path, $name)
    {
        if (file_exists($path)) {
            // если этого не сделать файл будет читаться в память полностью!
            if (ob_get_level()) {
                ob_end_clean();
            }

            // заставляем браузер показать окно сохранения файла
            header('Content-Description: File Transfer');

            $mime = mime_content_type($path);
            if (false === $mime) {
                $mime = 'application/octet-stream';
            }
            header('Content-Type: ' . $mime);

            header('Content-Disposition: attachment; filename=' . $name);
            header('Content-Transfer-Encoding: binary');
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . filesize($path));

            // читаем файл и отправляем его пользователю
            readfile($path);
            exit;
        }

        throw new Zend_Controller_Action_Exception('Файл не найден:' . $path, 404);
    }
}
