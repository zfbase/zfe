<?php

/*
 * ZFE – платформа для построения редакторских интерфейсов.
 */

/**
 * Схема файла - это описание параметров и уникального кода для загрузки файла/файлов и их разделения между собой по смыслу.
 * Для любой модели, для которой существует менеджер, управляющий ее файлами, может быть определена коллекция схем файлов.
 */
class ZFE_File_Schema
{
    const ACCEPTS_TEXTS = '.doc, .docx, .txt, .pdf, .rtf, .odt, .html';
    const ACCEPTS_TABLES = '.xls, .xlsx, .csv, .tsv';
    const ACCEPTS_SCANS = '.pdf';

    protected $fieldName = 'file';
    protected $fileTypeCode = 0;
    protected $processor;
    protected $multiple = false;
    protected $accept = '*'; //'.xls, .xlsx';
    protected $title = 'Файл';
    protected $required = false;
    protected $tooltip;

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @param string $title
     *
     * @return ZFE_File_Schema
     */
    public function setTitle(string $title): self
    {
        $this->title = $title;
        return $this;
    }

    /**
     * @return bool
     */
    public function isMultiple(): bool
    {
        return $this->multiple;
    }

    /**
     * @param bool $multiple
     *
     * @return ZFE_File_Schema
     */
    public function setMultiple(bool $multiple): self
    {
        $this->multiple = $multiple;
        return $this;
    }

    /**
     * @return string
     */
    public function getAccept(): string
    {
        return $this->accept;
    }

    /**
     * @param string $accept
     *
     * @return ZFE_File_Schema
     */
    public function setAccept(string $accept): self
    {
        $this->accept = $accept;
        return $this;
    }

    /**
     * @param string $fieldName
     *
     * @return ZFE_File_Schema
     */
    public function setFieldName(string $fieldName): self
    {
        $this->fieldName = $fieldName;
        return $this;
    }

    /**
     * @param int $fileTypeCode
     *
     * @return ZFE_File_Schema
     */
    public function setFileTypeCode(int $fileTypeCode): self
    {
        $this->fileTypeCode = $fileTypeCode;
        return $this;
    }

    /**
     * @param ZFE_File_Processor $processor
     *
     * @return ZFE_File_Schema
     *
     *@deprecated
     * use setProcessing
     */
    public function setProcessor(ZFE_File_Processor $processor)
    {
        $this->processor = $processor;
        return $this;
    }

    /**
     * @param ZFE_File_Processing $processing
     *
     * @return $this
     */
    public function setProcessing(ZFE_File_Processing $processing)
    {
        $this->processor = $processing->getProcessor();
        return $this;
    }

    /**
     * @return string
     */
    public function getFieldName(): string
    {
        return $this->fieldName;
    }

    /**
     * @return int
     */
    public function getFileTypeCode(): int
    {
        return $this->fileTypeCode;
    }

    public function getProcessor(): ?ZFE_File_Processor
    {
        return $this->processor;
    }

    /**
     * @return bool
     */
    public function isRequired(): bool
    {
        return $this->required;
    }

    /**
     * @param bool $required
     *
     * @return ZFE_File_Schema
     */
    public function setRequired(bool $required): self
    {
        $this->required = $required;
        return $this;
    }

    public function getTooltip()
    {
        return $this->tooltip;
    }

    /**
     * @param null $tooltip
     *
     * @return ZFE_File_Schema
     */
    public function setTooltip($tooltip)
    {
        $this->tooltip = $tooltip;
        return $this;
    }
}
