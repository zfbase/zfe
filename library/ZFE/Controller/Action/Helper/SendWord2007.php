<?php

/*
 * ZFE – платформа для построения редакторских интерфейсов.
 */

/**
 * Помощник отправки файла Word из объекта PHPWord.
 */
class ZFE_Controller_Action_Helper_SendWord2007 extends Zend_Controller_Action_Helper_Abstract
{
    /**
     * Отправить файл Word из объекта PHPWord.
     *
     * @param PhpOffice\PhpWord\PhpWord $word
     * @param string                    $fileName
     */
    public function direct(PhpOffice\PhpWord\PhpWord $word, $fileName)
    {
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
        \PhpOffice\PhpWord\IOFactory::createWriter($word, 'Word2007')->save('php://output');

        $response->sendResponse();
        exit;
    }
}
