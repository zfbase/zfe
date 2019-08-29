<?php

/*
 * ZFE – платформа для построения редакторских интерфейсов.
 */

/**
 * @method void AbstractView(string $action = null, string $controller = null)                                  Использовать абстрактные вьюшки, если нету собственных
 * @method void Download(string $path, string $url, string $name)                                               Отправить файл средствами сервера через авторизацию приложения
 * @method void DownloadApache(string $path, string $name)                                                      Отправить файл средствами сервера через авторизацию приложения
 * @method void DownloadNginx(string $path, string $url, string $name)                                          Отправить файл средствами nginx через авторизацию приложения
 * @method void PostToGet(array $ignore = ['submit'])                                                           Преобразовать POST- данные в ЧПУ
 * @method void SendExcel2007(PHPExcel $excel, string $fileName)                                                Отправить файл Excel из объекта PHPExcel
 * @method void SendWord2007(\PhpOffice\PhpWord\PhpWord $word, string $name)                                    Отправить файл Word из объекта PHPWord
 * @method void SendWord2007Template(\PhpOffice\PhpWord\TemplateProcessor $templateProcessor, string $fileName) Отправить файл Word из шаблона PHPWord
 */
class ZFE_Controller_Action_HelperBroker extends Zend_Controller_Action_HelperBroker
{
}
