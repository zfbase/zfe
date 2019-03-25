<?php

/*
 * ZFE – платформа для построения редакторских интерфейсов.
 */

/**
 * Обертка над doctrine-cli.
 */
class ZFE_Console_Command_DoctrineCli extends ZFE_Console_Command_Abstract
{
    protected static $_name = 'doctrine';
    protected static $_description = 'Обертка над doctrine-cli';

    public function __construct()
    {
        /**
         * Эти файлы хранятся в library/doctrine1/lib/Doctrine/Parser/sfYaml
         * Они не поддерживают общий для Doctrine PSR-0 и не имеют пространства имен.
         * В Doctrine_Core::autoload есть специальный блок для обработки этих файлов.
         * В любом случае, я не знаю, почему они не подгружаются автоматически, но они нужны для задач:
         * generate-migrations-diff
         * generate-yaml-models
         * и возможно каких-то других...
         */
        Doctrine_Core::autoload('sfYaml');
        Doctrine_Core::autoload('sfYamlDumper');
        Doctrine_Core::autoload('sfYamlParser');
    }

    /**
     * {@inheritdoc}
     */
    public function execute(array $params = [])
    {
        $cli = new Doctrine_Cli(Zend_Registry::get('config')->doctrine->toArray());
        $cli->run(array_merge(['doctrine-cli'], $params));
    }
}
