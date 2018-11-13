<?php

/*
 * ZFE – платформа для построения редакторских интерфейсов.
 */

/**
 * Элемент формы загрузка файла.
 *
 * @category  ZFE
 */
class ZFE_Form_Element_File extends Zend_Form_Element_File
{
    public function getAttribs()
    {
        $attribs = parent::getAttribs();

        if ( ! array_key_exists('data-type', $attribs)) {
            $dateType = $this->getDataType();
            if ($dateType) {
                $attribs['data-type'] = $dateType;
            }
        }

        if ( ! array_key_exists('accept', $attribs)) {
            $allowExtensions = $this->getAllowExtensions();
            $allowMimeTypes = $this->getAllowMimeTypes();
            if ($allowExtensions || $allowMimeTypes) {
                $attribs['accept'] = implode(',', array_merge($allowExtensions, $allowMimeTypes));
            }
        }

        return $attribs;
    }

    //
    // Тип элемента загрузки файлов
    //

    protected $_dataType;
    protected $_dateTypesValid = [
        'image',
        'audio',
        // 'video', @todo сделать стандартный загрузчик для видеофайлов
    ];

    /**
     * @param string $dateType
     *
     * @throws ZFE_Form_Exception
     *
     * @return ZFE_Form_Element_File
     */
    public function setDateType($dateType)
    {
        if ( ! in_array($dateType, $this->dateTypesValid, true)) {
            throw new ZFE_Form_Exception('Не допустимый тип данных элемента загрузки файлов.');
        }

        $this->_dataType = $dateType;
        return $this;
    }

    /**
     * @return string
     */
    public function getDataType()
    {
        return $this->_dataType;
    }

    //
    // Ограничения по расширениям файлов
    //

    protected $_allowExtensions = [];

    /**
     * @param string $extension
     *
     * @return ZFE_Form_Element_File
     */
    public function addAllowExtension($extension)
    {
        if ( ! in_array($extension, $this->_allowExtensions, true)) {
            if ('.' !== $extension[0]) {
                throw new ZFE_Form_Exception('Разрешенные расширения должны начинаться с точки.');
            }

            $this->_allowExtensions[] = $extension;
        }

        return $this;
    }

    /**
     * @param string $extension
     *
     * @return ZFE_Form_Element_File
     */
    public function removeAllowExtension($extension)
    {
        $this->_allowExtensions = array_diff($this->_allowExtensions, [$extension]);

        return $this;
    }

    /**
     * @param array|string[] $extensions
     *
     * @return ZFE_Form_Element_File
     */
    public function setAllowExtensions(array $extensions)
    {
        $this->clearAllowExtensions();
        foreach ($extensions as $extension) {
            $this->addAllowExtension($extension);
        }
        return $this;
    }

    /**
     * @return ZFE_Form_Element_File
     */
    public function clearAllowExtensions()
    {
        $this->_allowExtensions = [];
        return $this;
    }

    /**
     * @return array|string[]
     */
    public function getAllowExtensions()
    {
        return $this->_allowExtensions;
    }

    //
    // Ограничения по MIME-типам
    //

    protected $_allowMimeTypes = [];

    /**
     * @param string $mimeType
     *
     * @return ZFE_Form_Element_File
     */
    public function addAllowMimeType($mimeType)
    {
        if ( ! in_array($mimeType, $this->_allowMimeTypes, true)) {
            $this->_allowMimeTypes[] = $mimeType;
        }

        return $this;
    }

    /**
     * @param string $mimeType
     *
     * @return ZFE_Form_Element_File
     */
    public function removeAllowMimeType($mimeType)
    {
        $this->_allowMimeTypes = array_diff($this->_allowMimeTypes, [$mimeType]);

        return $this;
    }

    /**
     * @param array|string[] $mimeTypes
     *
     * @return ZFE_Form_Element_File
     */
    public function setAllowMimeTypes(array $mimeTypes)
    {
        $this->_allowMimeTypes = $mimeTypes;
        return $this;
    }

    /**
     * @return ZFE_Form_Element_File
     */
    public function clearAllowMimeTypes()
    {
        $this->_allowMimeTypes = [];
        return $this;
    }

    /**
     * @return array|string[]
     */
    public function getAllowMimeTypes()
    {
        return $this->_allowMimeTypes;
    }

    //
    // Для элемента загрузки одного файла
    //

    /**
     * Загруженный файл.
     *
     * @var ZFE_File
     */
    protected $_file;

    /**
     * Задать загруженный файл.
     *
     * @param ZFE_File $file
     *
     * @return Zend_Form_Element_File
     */
    public function setFile(ZFE_File $file)
    {
        $this->_file = $file;
        return $this;
    }

    /**
     * Получить загруженный файл.
     *
     * @return null|ZFE_File
     */
    public function getFile()
    {
        return $this->_file;
    }

    //
    // Для элемента загрузки нескольких файлов
    //

    /**
     * Загруженные файлы.
     *
     * @var array|ZFE_File[]
     */
    protected $_files = [];

    /**
     * Добавить загруженный файл.
     *
     * @param ZFE_File $file
     *
     * @return Zend_Form_Element_File
     */
    public function addFile(ZFE_File $file)
    {
        $this->_files[] = $file;
        return $this;
    }

    /**
     * Получить загруженные файлы.
     *
     * @return array|ZFE_File[]
     */
    public function getFiles()
    {
        return $this->_files;
    }
}
