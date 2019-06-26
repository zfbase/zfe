<?php
/**
 * Created by PhpStorm.
 * User: dezzpil
 * Date: 11.10.18
 * Time: 18:28
 */

class Helper_File_Manager_Archived extends Helper_File_Manager
{
    const TYPE_BASE = 10;

    /**
     * @inheritdoc
     */
    protected function getLoaderConfig(): Zend_Config
    {
        return new Zend_Config([
            "path" => APPLICATION_PATH . "/../data/files/archived",
            "url" => "/archived"
        ]);
    }

    /**
     * @inheritdoc
     */
    public function getFieldsSchemas(): Helper_File_Schema_Collection
    {
        $schemas = new Helper_File_Schema_Collection;

        $common = new Helper_File_Schema;
        $common->setFileTypeCode(static::TYPE_BASE)
            ->setRequired(true)
            ->setMultiple(true)
            ->setFieldName('base')
            ->setTitle('Пакет документов');

        $schemas->add($common);
        return $schemas;
    }

    /**
     * @inheritdoc
     */
    protected function initAccessor(Zend_Acl $acl, Editors $user) : Helper_File_Accessor
    {
        return new Helper_File_Accessor_Department($acl, $user);
    }

    /**
     * @inheritdoc
     */
    protected function createFileAgent(Helper_File_Loadable $file) : Helper_File_Agent
    {
        $agent = new Helper_File_Agent($file);
        //$agent->useIconsSet(new Helper_File_Icons_Bootstrap);
        $agent->switchHandlyProcessing(true); // запуск обработки файлов вручную
        if ($this->accessor) $agent->useAccessor($this->accessor);
        return $agent;
    }
}
