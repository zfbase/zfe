<?php
/**
 * Created by PhpStorm.
 * User: dezzpil
 * Date: 09.10.18
 * Time: 16:25
 */

class Helper_File_Manager_Work extends Helper_File_Manager
{
    const TYPE_BLANK = 10;
    const NAME_BLANK = 'Бланк регистрации';

    /**
     * @inheritdoc
     */
    public function getFieldsSchemas() : Helper_File_Schema_Collection
    {
        $schemas = new Helper_File_Schema_Collection;

//        $blank = new Helper_File_Schema;
//        $blank->setFieldName('blank')
//            ->setFileTypeCode(static::TYPE_BLANK)
//            ->setTitle(static::NAME_BLANK)
//            ->setAccept(Helper_File_Schema::ACCEPTS_SCANS)
//            ->setTooltip('В данное поле необходимо загрузить скан бланка регистрации с печатью')
//            ->setRequired(true);
//        $schemas->add($blank);

        $correspond = new Helper_File_Schema;
        $correspond->setFieldName('corr')
            ->setTitle('История переписки')
            ->setFileTypeCode(20)
            ->setRequired(false)
            ->setMultiple(true);

        $extra = new Helper_File_Schema;
        $extra->setMultiple(true)
            ->setTitle('Дополнительные')
            ->setFileTypeCode(30)
            ->setRequired(false);

        $schemas->add($correspond)->add($extra);

        return $schemas;
    }

    /**
     * @inheritDoc
     */
    protected function getLoaderConfig(): Zend_Config
    {
        return new Zend_Config([
            "path" => APPLICATION_PATH . "/../data/files/works",
            "url" => "/works"
        ]);
    }

    /**
     * @inheritdoc
     */
    protected function initAccessor(Zend_Acl $acl, Editors $user) : Helper_File_Accessor
    {
        return new Helper_File_Accessor_Department($acl, $user);
    }

}
