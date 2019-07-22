<?php

/*
 * ZFE – платформа для построения редакторских интерфейсов.
 */

/**
 * Абстрактный класс, скрывающий реализации для любых процессоров обработки файлов
 *
 * Процессор предоставляет 2 самых важных метода: plan и process
 * Plan - создает запись в таблице обработки (реализации класс ZFE_File_Processing)
 * Process - обновляет запись в таблице обработки
 */
abstract class ZFE_File_Processor
{
    /**
     * @var ZFE_File_Processing
     */
    protected $processing;

    /**
     * Helper_File_Processor constructor.
     *
     * @param ZFE_File_Processing $item
     */
    public function __construct(ZFE_File_Processing $item)
    {
        $this->processing = $item;
    }

    /**
     * @throws ZFE_File_Exception
     *
     * @return ZFE_File_Processing
     */
    public function getProcessing()
    {
        if ($this->processing === null) {
            throw new ZFE_File_Exception('Запись обработки не задана');
        }
        return $this->processing;
    }

    /**
     * Запланировать обработку. Создает запись обработки для файла
     * Запись на обработку создается при загрузке файла
     * Не сохраняет запись в БД!
     *
     * @return ZFE_File_Processor
     */
    abstract public function plan(Files $file): self;

    /**
     * Выполнить обработку. Обновляет запись обработи, созданную в методом plan(), для файла
     * Обработка осуществляется в фоновом режиме
     * Не сохраняет запись в БД!
     *
     * @param ZFE_File_Loader $loader
     *
     * @return ZFE_File_Processor
     */
    abstract public function process(ZFE_File_Loader $loader): self;

    /**
     * @return string
     */
    abstract public function getDesc(): string;

    /**
     * @return string
     */
    public function __toString()
    {
        return get_class($this);
    }
}
