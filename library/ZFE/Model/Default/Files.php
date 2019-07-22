<?php


class ZFE_Model_Default_Files extends BaseFiles
{
    /**
     * Половая принадлежность записи.
     *
     * @var int
     */
    public static $sex = self::SEX_FEMALE;

    /**
     * Название записи в единственном числе.
     *
     * @var string
     */
    public static $nameSingular = 'Файл';

    /**
     * Название записи во множественном числе.
     *
     * @var string
     */
    public static $namePlural = 'Файлы';

    /**
     * Поле (выражение) названия записи.
     *
     * @var string
     */
    public static $titleField = "x.title_original";

    /**
     * Сортировка записей по умолчанию.
     *
     * @var string
     */
    public static $defaultOrder = "x.model_name ASC, x.item_id ASC, x.id ASC";

    /**
     * Ключ сортировки по умолчанию.
     *
     * @var string
     */
    public static $defaultOrderKey = 'title_original_asc';

    /**
     * Имена полей записи модели.
     *
     * @var array
     */
    public static $nameFields = [
        // 'title' => 'Название',
        'title_original' => 'Название',
    ];

    /**
     * Имя контроллера управляющего записями модели.
     *
     * @var string
     */
    public static $controller = 'files';

    /**
     * Записи могут быть объединены стандартным способом?
     *
     * @var bool
     */
    public static $mergeable = false;

    /**
     * Инициализировать список по умолчанию служебных полей модели.
     */
    protected static function _initServiceFields()
    {
        static::_setServiceFields([
            'id',
            'creator_id',
            'editor_id',
            'model_name',
            'item_id',
            'title',
            'path',
            'type',
        ]);
    }

    /**
     * Получить запись, для который был загружен данный файл
     * @return ZFE_File_Manageable|null
     */
    public function getManageableItem() : ?ZFE_File_Manageable
    {
        $modelName = $this->model_name;
        $itemId = $this->item_id;
        $q = ZFE_Query::create()->setHard(true)
            ->select()
            ->from($modelName)
            ->where('id = ?', $itemId);
        return $q->fetchOne() ?: null;
    }

    /**
     * Возвращает описание допустимых обработок для файлов
     * @param bool $refresh Загрузить коллекцию по-новой?
     * @return ZFE_File_Processor_Mapping|null
     */
    public function getProcessings($refresh = false) : ZFE_File_Processor_Mapping
    {
        $mapping = new ZFE_File_Processor_Mapping($this);
        return $mapping;
    }
}