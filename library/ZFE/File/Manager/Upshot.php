<?php
/**
 * Created by PhpStorm.
 * User: dezzpil
 * Date: 09.10.18
 * Time: 16:25
 */

class Helper_File_Manager_Upshot extends Helper_File_Manager
{
    const TYPE_CONCL = 10;
    const NAME_CONCL = 'Заключение РГ';
    const TYPE_ATTACH = 20;
    const NAME_ATTACH = 'Приложение';

    /**
     * @inheritdoc
     */
    public function getFieldsSchemas() : Helper_File_Schema_Collection
    {
        $schemas = new Helper_File_Schema_Collection;

        $concl = new Helper_File_Schema;
        $concl->setFieldName('fileconcl')
            ->setFileTypeCode(static::TYPE_CONCL)
            ->setTitle(static::NAME_CONCL)
            ->setAccept(Helper_File_Schema::ACCEPTS_TEXTS)
            //->setTooltip('В данное поле необходимо загрузить скан бланка регистрации с печатью')
            ->setRequired(false);
        $schemas->add($concl);

        $concl = new Helper_File_Schema;
        $concl->setFieldName('fileattach')
            ->setFileTypeCode(static::TYPE_ATTACH)
            ->setTitle(static::NAME_ATTACH)
            ->setAccept(Helper_File_Schema::ACCEPTS_TABLES)
            //->setTooltip('В данное поле необходимо загрузить скан бланка регистрации с печатью')
            ->setRequired(false);
        $schemas->add($concl);

        return $schemas;
    }

    /**
     * @inheritDoc
     */
    protected function getLoaderConfig(): Zend_Config
    {
        return new Zend_Config([
            "path" => APPLICATION_PATH . "/../data/files/upshots",
            "url" => "/upshots"
        ]);
    }

    /**
     * @inheritdoc
     */
    protected function initAccessor(Zend_Acl $acl, Editors $user) : Helper_File_Accessor
    {
        return new Helper_File_Accessor_Acl($acl, $user);
    }

}
