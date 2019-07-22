<?php

/*
 * ZFE – платформа для построения редакторских интерфейсов.
 */

/**
 * Class ZFE_File_Agent.
 *
 * Агент представляет файл:
 * презентация/отображение файла, getName, getSize и т.д.
 * отвечает за доступ к нему с помощью методов isAllowedTo*
 * предоставляет карту возможных и проведенных обработок файла
 *
 * @property int    $id
 * @property int    $creator_id
 * @property int    $created_at
 * @property string $title
 * @property string $hash
 * @property int    $size
 * @property int    $type
 * @property string $ext
 * @property string $path
 *
 * @method canDelete
 * @method getDeleteUrl
 * @method canDownload
 * @method getDownloadUrl
 * @method canDownloadAll
 * @method getDownloadAllUrl
 * @method canProcess
 * @method getProcessUrl
 */
class ZFE_File_Agent
{
    /**
     * @var Files
     */
    protected $file;

    /**
     * @var ZFE_File_Accessor
     */
    protected $accessor;

    /**
     * @var ZFE_File_Icons
     */
    protected $iconsSet;

    /**
     * @var null|ZFE_File_Processor_Mapping
     */
    protected $processings;

    /**
     * @var bool
     */
    protected $processHandlyPlan = false;

    /**
     * Helper_File_Agent constructor.
     *
     * @param Files $file
     *
     * @throws Doctrine_Connection_Exception
     * @throws Doctrine_Record_Exception
     */
    public function __construct(Files $file)
    {
        $this->file = $file;
        $this->useAccessor(new ZFE_File_Accessor_Acl(Zend_Registry::get('acl'), new Editors));
        $this->useIconsSet(new ZFE_File_Icons);
        $this->processings = $file->getProcessings();
    }

    /**
     * Определить управление доступом
     *
     * @param ZFE_File_Accessor $accessor
     *
     * @return $this
     */
    public function useAccessor(ZFE_File_Accessor $accessor)
    {
        $this->accessor = $accessor;
        try {
            $accessor->getRecord();
        } catch (ZFE_File_Exception $e) {
            $accessor->setRecord($this->file->getManageableItem());
        }
        return $this;
    }

    /**
     * @return ZFE_File_Accessor
     */
    public function getAccesssor()
    {
        return $this->accessor;
    }

    /**
     * Определить перечень иконок для файла.
     *
     * @param ZFE_File_Icons $set
     *
     * @return $this
     */
    public function useIconsSet(ZFE_File_Icons $set)
    {
        $this->iconsSet = $set;
        return $this;
    }

    /**
     * @param $name
     * @param $arguments
     *
     * @return mixed
     */
    public function __call($name, $arguments)
    {
        // proxy calls to accesssor

        if (mb_strpos($name, 'can') === 0) {
            $accessorMethod = 'isAllowTo' . mb_substr($name, 3);
            return $this->accessor->{$accessorMethod}();
        }

        // strrev('Url') -> lrU
        if (mb_strpos(strrev($name), 'lrU') === 0) {
            return $this->accessor->{$name}($this->file);
        }

        throw new BadFunctionCallException();
    }

    /**
     * @param $name
     *
     * @return mixed
     */
    public function __get($name)
    {
        // proxy calls to file
        return $this->file->get($name);
    }

    /**
     * Получить читаемый размер файла.
     *
     * @return string
     */
    public function getSize(): string
    {
        return ZFE_File::humanFileSize(intval($this->file->size));
    }

    /**
     * Получить оформленное название записи файла.
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->file->getTitle();
    }

    /**
     * Получить иконку для файла.
     *
     * @return string
     */
    public function getIconClass(): string
    {
        return $this->iconsSet->getFor($this->file->ext);
    }

    /**
     * Файл можно обработать?
     *
     * @return bool
     */
    public function isProcessable(): bool
    {
        // TODO каждый доступный процессор должен сообщать о наборе расширений, который он способен обрабатывать
        if (array_search($this->file->ext, ['zip', 'rar', 'exe', 'tar']) !== false) {
            return false;
        }

        return $this->processings !== null;
    }

    /**
     * Получить обработки для файла.
     *
     * @return ZFE_File_Processor_Mapping
     */
    public function getProcessings(): ZFE_File_Processor_Mapping
    {
        return $this->processings;
    }

    /**
     * Определить возможность ручного планирования обработки для файла.
     *
     * @param bool $val
     *
     * @return ZFE_File_Agent
     */
    public function switchHandlyProcessing(bool $val): self
    {
        $this->processHandlyPlan = $val;
        return $this;
    }

    /**
     * Можно запланировать обработку вручную?
     *
     * @return bool
     */
    public function isHandlyProcessingSwitched(): bool
    {
        return $this->processHandlyPlan;
    }
}
