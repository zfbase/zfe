<?php

/*
 * ZFE – платформа для построения редакторских интерфейсов.
 */

/**
 * Интерфейс для модели, которая хранит данные обработки файлов.
 */
interface ZFE_File_Processing
{
    /**
     * Получить процессор, соответствующий модели.
     *
     * @return ZFE_File_Processor
     */
    public function getProcessor(): ZFE_File_Processor;

    public function isPlanned(): bool;

    public function isCompleted(): bool;

    public function linkFile(Files $file): self;

    public function getLinkedFile(): Files;

    public function setError(int $code, string $message = null): self;

    public function hasError(): bool;

    public function getErrorDesc($withMsg = false);
}
