<?php
/**
 * Created by PhpStorm.
 * User: dezzpil
 * Date: 09.10.18
 * Time: 16:25
 */

class ZFE_File_Manager_Default extends ZFE_File_Manager
{
    const TYPE_COMMON = 10;
    const TYPE_MAIN = 20;

    /**
     * @inheritdoc
     */
    public function getFieldsSchemas() : ZFE_File_Schema_Collection
    {
        $schemas = new ZFE_File_Schema_Collection;

        $main = new ZFE_File_Schema;
        $main->setFieldName('main')
            ->setFileTypeCode(static::TYPE_MAIN)
            ->setTitle('Файлы')
            ->setMultiple(true)
            ->setRequired(true);
        $schemas->add($main);

        $common = new ZFE_File_Schema;
        $common->setFieldName('common')
            ->setFileTypeCode(static::TYPE_COMMON)
            ->setTitle('Второстепенные')
            ->setMultiple(true)
            ->setRequired(false);
        $schemas->add($common);

        return $schemas;
    }

    /**
     * @inheritdoc
     */
    protected function initAccessor(Zend_Acl $acl, ZFE_Model_Default_Editors $user) : ZFE_File_Accessor
    {
        return new ZFE_File_Accessor_Acl($acl, $user);
    }

}
