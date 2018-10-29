<?php

/*
 * ZFE – платформа для построения редакторских интерфейсов.
 */

/**
 * Помощник отправки файла Word из шаблона PHPWord.
 *
 * @category  ZFE
 */
class ZFE_Controller_Action_Helper_SendWord2007Template extends Zend_Controller_Action_Helper_Abstract
{
    /**
     * Отправить файл Word из шаблона PHPWord.
     *
     * @param PhpOffice\PhpWord\TemplateProcessor $templateProcessor
     * @param string                              $fileName
     */
    public function direct(PhpOffice\PhpWord\TemplateProcessor $templateProcessor, $fileName)
    {
        $this->getActionController()->getHelper('layout')->disableLayout();
        $this->getActionController()->getHelper('viewRenderer')->setNoRender(true);

        if ($err = error_get_last()) {
            Zend_Debug::dump($err);
            die;
        }

        $response = $this->getResponse();
        $response->clearAllHeaders();
        $response->clearBody();

        $response->setHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document');
        $response->setHeader('Content-Transfer-Encoding', 'binary');
        $response->setHeader('Content-Disposition', 'attachment; filename="' . $fileName . '.docx"');
        $response->setHeader('Expires', '0');
        $response->setHeader('Cache-Control', 'max-age=0');
        $response->sendHeaders();

        echo file_get_contents($templateProcessor->save());
        exit;
    }
}
