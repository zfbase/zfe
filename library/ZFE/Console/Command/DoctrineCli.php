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

    /**
     * {@inheritdoc}
     */
    public function execute(array $params = [])
    {
        $cli = new Doctrine_Cli(Zend_Registry::get('config')->doctrine->toArray());
        $cli->run(array_merge(['doctrine-cli'], $params));
    }
}
