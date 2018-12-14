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
     */
    public function direct($path, $url, $name)
    {
        /** @var Zend_Config $config */
        $config = Zend_Registry::get('config');

        if ( ! $config->webserver) {
            $this->abort(500, 'В конфигурации не указан используемый веб-сервер (параметр webserver)');
        }

        switch ($config->webserver) {
            case 'nginx':
                Zend_Controller_Action_HelperBroker::getStaticHelper('DownloadNginx')->direct($path, $url, $name);
                break;
            case 'apache':
                Zend_Controller_Action_HelperBroker::getStaticHelper('DownloadApache')->direct($path, $name);
                break;
            default:
                $this->abort(500, 'В конфигурации не указан не поддерживаемый веб-сервер');
        }
    }
}
