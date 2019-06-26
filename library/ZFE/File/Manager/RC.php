<?php
/**
 * Created by PhpStorm.
 * User: dezzpil
 * Date: 09.10.18
 * Time: 16:25
 */

class Helper_File_Manager_RC extends Helper_File_Manager
{
    const TYPE_TECHTASK = 10;
    const TYPE_CALEND = 20;
    const TYPE_OFFER = 30;

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
            ->setTooltip('В данное поле следует загрузить текстовый файл с техническим заданием');

        $calendar = new Helper_File_Schema;
        $calendar->setFieldName('cal')
            ->setFileTypeCode(static::TYPE_CALEND)
            ->setAccept(Helper_File_Schema::ACCEPTS_TEXTS)
            ->setTitle('Календарный план')
            ->setProcessing(new Textractings)
            ->setTooltip('В данное поле следует загрузить текстовый файл с календарным планом');

        $offer = new Helper_File_Schema;
        $offer->setFieldName('offer')
            ->setFileTypeCode(static::TYPE_OFFER)
            ->setAccept(Helper_File_Schema::ACCEPTS_SCANS)
            ->setTitle('Договор')
            ->setTooltip('В данное поле следует загрузить отсканированную версия договора');

        $extra = new Helper_File_Schema;
        $extra->setFieldName('extra')
            ->setFileTypeCode(40)
            ->setTitle('Дополнительные')
            ->setMultiple(true)
            ->setTooltip('В данное поле следует загружать: ТЭО, акты и другие документы, для которых нет специальных полей загрузки');


        // EBZ-534 Как Э я могу передать РИК на рассмотрение без файлов для работы с КТ
        /** @var Works $work */
        $work = $this->getRecord()->Works;
        if (!$work->isClassified()) {
            $tech->setRequired(true);
            $offer->setRequired(true);
            $calendar->setRequired(true);
        }

        $schemas->add($tech);
        $schemas->add($calendar);
        $schemas->add($offer);
        $schemas->add($extra);
        return $schemas;
    }

    /**
     * @inheritDoc
     */
    protected function getLoaderConfig(): Zend_Config
    {
        return new Zend_Config([
            "path" => APPLICATION_PATH . "/../data/files/rc",
            "url" => "/rc"
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
