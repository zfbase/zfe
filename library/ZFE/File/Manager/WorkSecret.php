<?php
/**
 * Created by PhpStorm.
 * User: dezzpil
 * Date: 09.10.18
 * Time: 16:25
 */

class Helper_File_Manager_WorkSecret extends Helper_File_Manager
{
    /**
     * @inheritdoc
     */
    public function getFieldsSchemas() : Helper_File_Schema_Collection
    {
        $schemas = new Helper_File_Schema_Collection;

        $blank = new Helper_File_Schema;
        $blank->setFieldName('secret')
            ->setFileTypeCode(10)
            ->setTitle('Подтверждающие документы')
            ->setMultiple(true)
            ->setRequired(true);
        $schemas->add($blank);

        return $schemas;
    }

    /**
     * @inheritDoc
     */
    protected function getLoaderConfig(): Zend_Config
    {
        return new Zend_Config([
            "path" => APPLICATION_PATH . "/../data/files/workssec/",
            "url" => "/workssec"
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
