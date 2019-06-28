<?php

abstract class ZFE_File_Processor
{
    /**
     * @var Helper_File_Processing
     */
    protected $processing = null;

    /**
     * Helper_File_Processor constructor.
     * @param Helper_File_Processing $item
     */
    public function __construct(Helper_File_Processing $item)
    {
        $this->processing = $item;
    }

    /**
     * @return Helper_File_Processing
     * @throws ZFE_File_Exception
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
     * @return ZFE_File_Processor
     */
    abstract function plan(ZFE_File_Loadable $file) : ZFE_File_Processor;

    /**
     * Выполнить обработку. Обновляет запись обработи, созданную в методом plan(), для файла
     * Обработка осуществляется в фоновом режиме
     * Не сохраняет запись в БД!
     * @param ZFE_File_Loader $loader
     * @return ZFE_File_Processor
     */
    abstract function process(ZFE_File_Loader $loader) : ZFE_File_Processor;

    /**
     * @return string
     */
    abstract function getDesc() : string;

    /**
     * @return string
     */
    public function __toString()
    {
        return get_class($this);
    }


}
