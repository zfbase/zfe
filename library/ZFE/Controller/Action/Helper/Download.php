<?php

/*
 * ZFE – платформа для построения редакторских интерфейсов.
 */

/**
 * Помощник отправки файла средствами веб-сервера через авторизацию приложения.
 */
class ZFE_Controller_Action_Helper_Download extends Zend_Controller_Action_Helper_Abstract
{
    /**
     * Отправить файл средствами сервера через авторизацию приложения.
     *
     * @param string $path путь до файла в файловой системе
     * @param string $url  защищенный виртуальный URL
     * @param string $name новое имя файла
     * @param bool $download Отключение принудительной загрузки
     *
     * @throws Zend_Controller_Action_Exception
     */
    public function direct($path, $url, $name, $download = true)
    {
        $webserver = config('webserver');
        if (!$webserver) {
            throw new Zend_Controller_Action_Exception('В конфигурации не указан используемый веб-сервер (параметр webserver)');
        }

        $helpersMap = [
            'nginx' => 'DownloadNginx',
            'apache' => 'DownloadApache',
            'php' => 'DownloadPhp',
        ];
        if (array_key_exists($webserver, $helpersMap)) {
            Zend_Controller_Action_HelperBroker::getStaticHelper($helpersMap[$webserver])
                ->direct($path, $url, $name, $download)
            ;
        } else {
            throw new Zend_Controller_Action_Exception('В конфигурации не указан не поддерживаемый веб-сервер', 500);
        }
    }

    /**
     * Сформировать заголовки ответа для отправки файла с принудительным скачиванием.
     *
     * @param string $path
     * @param string $name
     * @param bool $download
     *
     * @return Zend_Controller_Response_Abstract
     */
    protected function factoryResponse($path, $name, $download = true)
    {
        $response = $this->getResponse();
        $response
            ->clearAllHeaders()
            ->clearBody()
        ;
        $response
            ->setHeader('Content-Description', 'File Transfer')
            ->setHeader('Content-Type', mime_content_type($path) ?: 'application/octet-stream')
            ->setHeader('Content-Transfer-Encoding', 'binary')
            ->setHeader('Expires', '0')
            ->setHeader('Cache-Control', 'must-revalidate')
            ->setHeader('Pragma', 'public')
            ->setHeader('Content-Length', filesize($path))
        ;

        if ($download) {
            $response->setHeader('Content-Disposition', 'attachment; filename="' . $name . '"');
        }

        return $response;
    }
}
