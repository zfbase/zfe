<?php
/**
 * Created by PhpStorm.
 * User: dezzpil
 * Date: 09.10.18
 * Time: 14:15
 */

class Helper_File_Schema
{
    const ACCEPTS_TEXTS = '.doc, .docx, .txt, .pdf, .rtf, .odt, .html';
    const ACCEPTS_TABLES = '.xls, .xlsx, .csv, .tsv';
    const ACCEPTS_SCANS = '.pdf';

    protected $fieldName = 'file';
    protected $fileTypeCode = 0;
    protected $processor = null;
    protected $multiple = false;
    protected $accept = '*'; //'.xls, .xlsx';
    protected $title = 'Файл';
    protected $required = false;
    protected $tooltip = null;

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @param string $title
     * @return Helper_File_Schema
     */
    public function setTitle(string $title): Helper_File_Schema
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
     * @return Helper_File_Schema
     */
    public function setMultiple(bool $multiple): Helper_File_Schema
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
     * @return Helper_File_Schema
     */
    public function setAccept(string $accept): Helper_File_Schema
    {
        $this->accept = $accept;
        return $this;
    }

    /**
     * @param string $fieldName
     * @return Helper_File_Schema
     */
    public function setFieldName(string $fieldName): Helper_File_Schema
    {
        $this->fieldName = $fieldName;
        return $this;
    }

    /**
     * @param int $fileTypeCode
     * @return Helper_File_Schema
     */
    public function setFileTypeCode(int $fileTypeCode): Helper_File_Schema
    {
        $this->fileTypeCode = $fileTypeCode;
        return $this;
    }

    /**
     * @deprecated
     * use setProcessing
     * @param Helper_File_Processor $processor
     * @return Helper_File_Schema
     */
    public function setProcessor(Helper_File_Processor $processor)
    {
        $this->processor = $processor;
        return $this;
    }

    /**
     * @param Helper_File_Processing $processing
     * @return $this
     */
    public function setProcessing(Helper_File_Processing $processing)
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

    /**
     * @return null
     */
    public function getProcessor() : ?Helper_File_Processor
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
     * @return Helper_File_Schema
     */
    public function setRequired(bool $required): Helper_File_Schema
    {
        $this->required = $required;
        return $this;
    }

    /**
     * @return null
     */
    public function getTooltip()
    {
        return $this->tooltip;
    }

    /**
     * @param null $tooltip
     * @return Helper_File_Schema
     */
    public function setTooltip($tooltip)
    {
        $this->tooltip = $tooltip;
        return $this;
    }

}
