<?php
/**
 * Created by PhpStorm.
 * User: dezzpil
 * Date: 11.10.18
 * Time: 14:42
 */

class ZFE_File_Processor_Textract extends ZFE_File_Processor
{
    function getDesc() : string
    {
        return 'Извлечение текста';
    }

    function plan(ZFE_File_Loadable $file) : ZFE_File_Processor
    {
        $item = $this->getProcessing();

        // заполняем задание данными по умолчанию
        $item->created_at = date('Y-m-d H:i:s');
        $item->linkFile($file);
        $item->len = 0;
        $item->elapsed = 0;
        $item->code = -1;

        return $this;
    }

    function process(ZFE_File_Loader $loader) : ZFE_File_Processor
    {
        $item = $this->getProcessing();
        if (!$item->exists() && $item->isPlanned()) {
            throw new ZFE_File_Exception('Запись не сохранена в БД. Обработать файл невозможно');
        }

        if ($item->isCompleted()) {
            throw new ZFE_File_Exception('Обработка уже была произведена ранее');
        }

        $file = $item->getLinkedFile();
        // получим абсолютный путь для скрипта
        $filePath = $loader->setRecord($file)->absFilePath();

        $e = null;
        $textractor = new Helper_Textractor($filePath);
        try {
            $textractor->exec(['pdf', 'doc', 'docx', 'txt', 'rtf', 'md', 'odt', 'html']);

            $item->len = $textractor->getLen();
            $item->elapsed = $textractor->getElapsed();
            $item->text = $textractor->getText();
            $item->code = $textractor->getCode();
        } catch (ZFE_File_Exception $e) {
            // ...
        }

        $this->clarifyErrors($textractor, $e);
        $item->completed_at = date('Y-m-d H:i:s');
        return $this;
    }

    /**
     * Разобраться с ошибками
     * @param Helper_Textractor $textractor
     * @param Exception|null $e
     * @throws ZFE_File_Exception
     */
    protected function clarifyErrors(Helper_Textractor $textractor, Exception $e = null)
    {
        $item = $this->getProcessing();
        //$modelName = get_class($item);
        $file = $item->getLinkedFile();

        if ($textractor->getCode() < 0) {
            $item->setError(Textractings::ERROR_FORMAT);
        }

        if ($textractor->getLen() == 1 && $file->ext == 'pdf') {
            $item->setError(Textractings::ERROR_EMPTYTEXT);
        }

        if ($e) {
            $item->setError(Textractings::ERROR_INTERNAL, $e->getMessage());
        }
    }

}
