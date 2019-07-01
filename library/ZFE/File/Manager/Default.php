<?php

/**
 * Class ZFE_File_Manager_Default
 * Стандартный менеджер с единственным полем для загрузки любых файлов
 */
class ZFE_File_Manager_Default extends ZFE_File_Manager
{
    const TYPE_COMMON = 10;

    /**
     * @inheritdoc
     */
    public function getFieldsSchemas() : ZFE_File_Schema_Collection
    {
        $schemas = new ZFE_File_Schema_Collection;

        $common = new ZFE_File_Schema;
        $common->setFieldName('common')
            ->setFileTypeCode(static::TYPE_COMMON)
            ->setTitle('Файлы')
            ->setMultiple(true)
            ->setRequired(false)
            ->setTooltip('Приложите файлы для записи, если это необходимо');
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
