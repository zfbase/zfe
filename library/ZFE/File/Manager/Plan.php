<?php
/**
 * Created by PhpStorm.
 * User: dezzpil
 * Date: 11.10.18
 * Time: 18:28
 */

class Helper_File_Manager_Plan extends Helper_File_Manager
{
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

        $common = new Helper_File_Schema;
        $common->setFileTypeCode(10)
            ->setRequired(false)
            ->setMultiple(false)
            ->setFieldName('import')
            ->setTitle('Импорт');

        $schemas->add($common);
        return $schemas;
    }

    /**
     * @inheritdoc
     */
    protected function initAccessor(Zend_Acl $acl, Editors $user) : Helper_File_Accessor
    {
        return new Helper_File_Accessor_Acl($acl, $user);
    }

    /**
     * @inheritdoc
     */
    protected function createFileAgent(Helper_File_Loadable $file) : Helper_File_Agent
    {
        $agent = new Helper_File_Agent($file);
        //$agent->useIconsSet(new Helper_File_Icons_Bootstrap);
        //$agent->switchHandlyProcessing(true); // запуск обработки файлов вручную
        if ($this->accessor) $agent->useAccessor($this->accessor);
        return $agent;
    }
}
