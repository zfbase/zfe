<?php

/*
 * ZFE – платформа для построения редакторских интерфейсов.
 */

class ZFE_Model_Default_Files extends BaseFiles
{
    /** {@inheritdoc} */
    public static $sex = self::SEX_FEMALE;

    /** {@inheritdoc} */
    public static $nameSingular = 'Файл';

    /** {@inheritdoc} */
    public static $namePlural = 'Файлы';

    /** {@inheritdoc} */
    public static $titleField = 'x.title_original';

    /** {@inheritdoc} */
    public static $defaultOrder = 'x.model_name ASC, x.item_id ASC, x.id ASC';

    /** {@inheritdoc} */
    public static $defaultOrderKey = 'title_original_asc';

    /** {@inheritdoc} */
    public static $nameFields = [
        // 'title' => 'Название',
        'title_original' => 'Название',
    ];

    /** {@inheritdoc} */
    public static $controller = 'files';

    /** {@inheritdoc} */
    public static $mergeable = false;

    /** {@inheritdoc} */
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
}
