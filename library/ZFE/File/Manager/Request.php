<?php
/**
 * Created by PhpStorm.
 * User: dezzpil
 * Date: 09.10.18
 * Time: 16:25
 */

class Helper_File_Manager_Request extends Helper_File_Manager
{
    const TYPE_TECHTASK = 10;
    const TYPE_ECONOMIC = 30;

    /**
     * @inheritdoc
     */
    public function getFieldsSchemas() : Helper_File_Schema_Collection
    {
        $schemas = new Helper_File_Schema_Collection;

        $tech = new Helper_File_Schema;
        $tech->setFieldName('tech')
            ->setFileTypeCode(static::TYPE_TECHTASK)
            ->setTitle('Техническое задание')
            ->setAccept(Helper_File_Schema::ACCEPTS_TEXTS)
            ->setProcessing(new Textractings)
            ->setRequired(true);
        $schemas->add($tech);

        $calendar = new Helper_File_Schema;
        $calendar->setFieldName('cal')
            ->setFileTypeCode(20)
            ->setAccept(Helper_File_Schema::ACCEPTS_TEXTS)
            ->setTitle('Календарный план');
        $schemas->add($calendar);

        $teo = new Helper_File_Schema;
        $teo->setFieldName('teo')
            ->setFileTypeCode(static::TYPE_ECONOMIC)
            ->setAccept(Helper_File_Schema::ACCEPTS_TEXTS)
            ->setTitle('ТЭО')
            ->setProcessing(new Textractings);
        $schemas->add($teo);

        $calc = new Helper_File_Schema;
        $calc->setFieldName('calc')
            ->setFileTypeCode(40)
            ->setTitle('Калькуляция');
        $schemas->add($calc);

        $other = new Helper_File_Schema;
        $other->setFieldName('other')
            ->setFileTypeCode(50)
            ->setTitle('Дополнительные')
            ->setMultiple(true)
            ->setTooltip('Например: обоснование необходимости выполнения работы, расчет начальной (максимальной) цены и др.');
        $schemas->add($other);

        return $schemas;
    }

    /**
     * @inheritDoc
     */
    protected function getLoaderConfig(): Zend_Config
    {
        return new Zend_Config([
            "path" => APPLICATION_PATH . "/../data/files/requests",
            "url" => "/requests"
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
