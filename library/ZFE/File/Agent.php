<?php
/**
 * Created by PhpStorm.
 * User: dezzpil
 * Date: 19.10.18
 * Time: 15:40
 */

/**
 * Class Helper_File_Agent
 *
 * @property int $id
 * @property int $creator_id
 * @property int $created_at
 * @property string $title
 * @property int $item_id
 * @property string $hash
 * @property int $size
 * @property int $type
 * @property string $ext
 * @property string $path
 *
 * @method canDelete
 * @method getDeleteUrl
 *
 * @method canDownload
 * @method getDownloadUrl
 *
 * @method canDownloadAll
 * @method getDownloadAllUrl
 *
 * @method canProcess
 * @method getProcessUrl
 */
class Helper_File_Agent
{
    /**
     * @var Helper_File_Loadable
     */
    protected $file;

    /**
     * @var Helper_File_Accessor
     */
    protected $accessor;

    /**
     * @var Helper_File_Icons
     */
    protected $iconsSet;

    /**
     * @var null|Helper_File_Processor_Mapping
     */
    protected $processings = null;

    /**
     * @var bool
     */
    protected $processHandlyPlan = false;


    /**
     * Helper_File_Agent constructor.
     * @param Helper_File_Loadable $file
     *
     * @throws Doctrine_Connection_Exception
     * @throws Doctrine_Record_Exception
     */
    public function __construct(Helper_File_Loadable $file)
    {
        $this->file = $file;
        $this->useAccessor(new Helper_File_Accessor_Acl(Zend_Registry::get('acl'), new Editors));
        $this->useIconsSet(new Helper_File_Icons);

        if ($file instanceof Helper_File_Processable) {
            $this->processings = $file->getProcessings();
        }
    }

    /**
     * Определить управление доступом
     * @param Helper_File_Accessor $accessor
     * @return $this
     */
    public function useAccessor(Helper_File_Accessor $accessor)
    {
        $this->accessor = $accessor;
        try {
            $accessor->getRecord();
        } catch (Application_Exception $e) {
            $accessor->setRecord($this->file->getManageableItem());
        }
        //$this->accessor->setRecord($this->file);
        return $this;
    }

    public function getAccesssor()
    {
        return $this->accessor;
    }

    /**
     * Определить перечень иконок для файла
     * @param Helper_File_Icons $set
     * @return $this
     */
    public function useIconsSet(Helper_File_Icons $set)
    {
        $this->iconsSet = $set;
        return $this;
    }

    /**
     * @param $name
     * @param $arguments
     * @return mixed
     */
    public function __call($name, $arguments)
    {
        // proxy calls to accesssor

        if (strpos($name, 'can') === 0) {
            $accessorMethod = 'isAllowTo' . substr($name, 3);
            return $this->accessor->$accessorMethod();
        }

        // strrev('Url') -> lrU
        if (strpos(strrev($name), 'lrU') === 0) {
            return $this->accessor->$name($this->file);
        }

        throw new BadFunctionCallException();
    }

    /**
     * @param $name
     * @return mixed
     */
    public function __get($name)
    {
        // proxy calls to file
        return $this->file->get($name);
    }

    /**
     * Получить читаемый размер файла
     * @return string
     */
    public function getSize() : string
    {
        return ZFE_File::humanFileSize(intval($this->file->size));
    }

    /**
     * Получить оформленное название записи файла
     * @return string
     */
    public function getName() : string
    {
        return $this->file->getTitle();
    }

    /**
     * Получить иконку для файла
     * @return string
     */
    public function getIconClass() : string
    {
        return $this->iconsSet->getFor($this->file->ext);
    }

    /**
     * Файл можно обработать?
     * @return bool
     */
    public function isProcessable() : bool
    {
        // TODO каждый доступный процессор должен сообщать о наборе расширений, который он способен обрабатывать
        if (array_search($this->file->ext, ['zip', 'rar', 'exe', 'tar']) !== false) {
            return false;
        }

        return $this->processings !== null;
    }

    /**
     * Получить обработки для файла
     * @return Helper_File_Processor_Mapping
     */
    public function getProcessings() : Helper_File_Processor_Mapping
    {
        return $this->processings;
    }

    /**
     * Определить возможность ручного планирования обработки для файла
     * @param bool $val
     * @return Helper_File_Agent
     */
    public function switchHandlyProcessing(bool $val) : self
    {
        $this->processHandlyPlan = $val;
        return $this;
    }

    /**
     * Можно запланировать обработку вручную?
     * @return bool
     */
    public function isHandlyProcessingSwitched() : bool
    {
        return $this->processHandlyPlan;
    }
}
