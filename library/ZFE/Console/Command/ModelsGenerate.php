<?php

/*
 * ZFE – платформа для построения редакторских интерфейсов.
 */

class ZFE_Console_Command_ModelsGenerate extends ZFE_Console_Command_Abstract
{
    public static function getName()
    {
        return 'models';
    }

    protected static $_description = 'Сгенерировать модели Doctrine по БД';
    protected static $_allowInApp = false;

    /**
     * {@inheritdoc}
     */
    public function execute(array $params = [])
    {
        ZFE_Console_CommandBroker::getInstance()
            ->getCommand('doctrine')
            ->execute(array_merge(['generate-models-db'], $params))
        ;
    }
}
