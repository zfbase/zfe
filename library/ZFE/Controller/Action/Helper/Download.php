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
     *
     * @throws Zend_Controller_Action_Exception
     */
    public function direct($path, $url, $name)
    {
        /** @var Zend_Config $config */
        $config = Zend_Registry::get('config');

        if (!$config->webserver) {
            throw new Zend_Controller_Action_Exception('В конфигурации не указан используемый веб-сервер (параметр webserver)');
        }

        $helpersMap = [
            'nginx' => 'DownloadNginx',
            'apache' => 'DownloadApache',
            'php' => 'DownloadPhp',
        ];
        if (array_key_exists($config->webserver, $helpersMap)) {
            Zend_Controller_Action_HelperBroker::getStaticHelper($helpersMap[$config->webserver])
                ->direct($path, $url, $name)
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
     *
     * @return Zend_Controller_Response_Abstract
     */
    protected function factoryResponse($path, $name)
    {
        $response = $this->getResponse();
        $response
            ->clearAllHeaders()
            ->clearBody()
        ;
        $response
            ->setHeader('Content-Description', 'File Transfer')
            ->setHeader('Content-Type', mime_content_type($path) ?: 'application/octet-stream')
            ->setHeader('Content-Disposition', 'attachment; filename="' . $name . '"')
            ->setHeader('Content-Transfer-Encoding', 'binary')
            ->setHeader('Expires', '0')
            ->setHeader('Cache-Control', 'must-revalidate')
            ->setHeader('Pragma', 'public')
            ->setHeader('Content-Length', filesize($path))
        ;
        return $response;
    }
}
