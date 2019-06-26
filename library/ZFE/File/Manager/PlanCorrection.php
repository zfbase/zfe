<?php
/**
 * Created by PhpStorm.
 * User: dezzpil
 * Date: 11.10.18
 * Time: 18:28
 */

class Helper_File_Manager_PlanCorrection extends Helper_File_Manager
{
    const TYPE_ORDER = 100;
    const TYPE_UPDATE = 200;
    const TITLE_UPDATE = 'Новая версия плана';

    /**
     * @inheritdoc
     */
    protected function getLoaderConfig(): Zend_Config
    {
        return new Zend_Config([
            "path" => APPLICATION_PATH . "/../data/files/plans",
            "url" => "/plans"
        ]);
    }

    /**
     * @inheritdoc
     */
    public function getFieldsSchemas(): Helper_File_Schema_Collection
    {
        $schemas = new Helper_File_Schema_Collection;

        $order = new Helper_File_Schema;
        $order->setFileTypeCode(static::TYPE_ORDER)
            ->setRequired(true)
            ->setMultiple(false)
            ->setFieldName('order')
            ->setTitle('Распоряжение');
        $update = new Helper_File_Schema;
        $update->setFileTypeCode(static::TYPE_UPDATE)
            ->setRequired(true)
            ->setMultiple(false)
            ->setAccept(Helper_File_Schema::ACCEPTS_TABLES)
            ->setFieldName('update')
            ->setTitle(static::TITLE_UPDATE);

        $schemas->add($order)->add($update);
        return $schemas;
    }

    /**
     * @inheritdoc
     */
    protected function initAccessor(Zend_Acl $acl, Editors $user) : Helper_File_Accessor
    {
        return new Helper_File_Accessor_Acl($acl, $user);
    }
}
