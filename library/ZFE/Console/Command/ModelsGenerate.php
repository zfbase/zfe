<?php

/*
 * ZFE – платформа для построения редакторских интерфейсов.
 */

class ZFE_Console_Command_ModelsGenerate extends ZFE_Console_Command_Abstract
{
    protected static $_name = 'models';
    protected static $_description = 'Сгенерировать модели Doctrine по БД';

    /**
     * {@inheritdoc}
     */
    public function execute(array $params = [])
    {
        $cli = new Doctrine_Cli(Zend_Registry::get('config')->doctrine->toArray());
        $cli->run(array_merge(['doctrine-cli', 'generate-models-db'], $params));
    }
}
