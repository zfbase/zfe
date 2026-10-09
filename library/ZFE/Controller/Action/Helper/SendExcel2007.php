<?php

/*
 * ZFE – платформа для построения редакторских интерфейсов.
 */

use PhpOffice\PhpSpreadsheet\Spreadsheet as PHPSpreadsheet;

/**
 * Помощник отправки файла Excel из объекта PHPExcel.
 */
class ZFE_Controller_Action_Helper_SendExcel2007 extends Zend_Controller_Action_Helper_Abstract
{
    /**
     * Отправить файл Excel из объекта PHPExcel / PHPSpreadsheet.
     *
     * @param PHPExcel|PHPSpreadsheet $excel
     * @param string                  $fileName
     *
     * @throws Zend_Controller_Action_Exception
     */
    public function direct($excel, ?string $fileName = null)
    {
        if ($excel instanceof PHPSpreadsheet) {
            if ($fileName === null) {
                $fileName = $excel->getProperties()->getTitle() . '.xlsx';
            }
            $this->sendPhpSpreadsheet($excel, $fileName);
        } elseif ($excel instanceof PHPExcel) {
            $this->sendPhpExcel($excel, $fileName ?? 'Spreadsheet');
        } else {
            throw new Zend_Controller_Action_Exception('Неподдерживаемый тип документа Excel', 500);
        }
    }

    public function sendPhpExcel(PHPExcel $excel, string $fileName)
    {
        if ($err = error_get_last()) {
            Zend_Debug::dump($err);
            exit();
        }

        $response = $this->getResponse();
        $response->clearAllHeaders();
        $response->clearBody();

        $response->setHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $response->setHeader('Content-Transfer-Encoding', 'binary');
        $response->setHeader('Content-Disposition', "attachment; filename*=UTF-8''" . rawurlencode($fileName . '.xlsx'));
        $response->setHeader('Expires', '0');
        $response->setHeader('Cache-Control', 'max-age=0');
        PHPExcel_IOFactory::createWriter($excel, 'Excel2007')->save('php://output');

        $response->sendResponse();
        exit();
    }

    public function sendPhpSpreadsheet(PHPSpreadsheet $spreadsheet, string $fileName)
    {
        if ($err = error_get_last()) {
            Zend_Debug::dump($err);
            exit();
        }

        $response = $this->getResponse();
        $response->clearAllHeaders();
        $response->clearBody();

        $response->setHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $response->setHeader('Content-Transfer-Encoding', 'binary');
        $response->setHeader('Content-Disposition', "attachment; filename*=UTF-8''" . rawurlencode($fileName . '.xlsx'));
        $response->setHeader('Expires', '0');
        $response->setHeader('Cache-Control', 'max-age=0');

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer->save('php://output');

        $response->sendResponse();
        exit();
    }
}
