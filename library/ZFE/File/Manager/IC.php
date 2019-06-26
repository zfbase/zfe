<?php
/**
 * Created by PhpStorm.
 * User: dezzpil
 * Date: 11.10.18
 * Time: 18:28
 */

class Helper_File_Manager_IC extends Helper_File_Manager
{
    const TYPE_BLANK = 20;
    const NAME_BLANK = 'Бланк регистрации';

    /**
     * @inheritdoc
     */
    protected function getLoaderConfig(): Zend_Config
    {
        return new Zend_Config([
            "path" => APPLICATION_PATH . "/../data/files/ic",
            "url" => "/ic"
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
            ->setMultiple(true)
            ->setFieldName('base')
            ->setTitle('Отчетные материалы')
            ->setTooltip('В данное поле следует загружать отчетные материалы работы, а также прочие файлы: доп. соглашения, акты и другие');

        $blank = new Helper_File_Schema;
        $blank->setFieldName('blank')
            ->setFileTypeCode(static::TYPE_BLANK)
            ->setTitle(static::NAME_BLANK)
            ->setAccept(Helper_File_Schema::ACCEPTS_SCANS)
            ->setTooltip('В данное поле необходимо загрузить скан бланка регистрации с печатью')
            ->setRequired(false);

        // EBZ-534 Как Э я могу передать РИК на рассмотрение без файлов для работы с КТ
        /** @var Works $work */
        $work = $this->getRecord()->Works;
        if (!$work->isClassified()) {
            $common->setRequired(true);
        }

        $schemas->add($common);
        $schemas->add($blank);

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
