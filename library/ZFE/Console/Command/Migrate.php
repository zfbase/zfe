<?php

/*
 * ZFE – платформа для построения редакторских интерфейсов.
 */

/**
 * Миграция БД.
 */
class ZFE_Console_Command_Migrate extends ZFE_Console_Command_Abstract
{
    protected static $_description = 'Миграция БД';
    protected static $_allowInApp = false;

    public function __construct()
    {
        ZFE_Model_AbstractRecord::$migrationMode = true;
    }

    /**
     * {@inheritdoc}
     */
    public function execute(array $params = [])
    {
        $migration = new Doctrine_Migration(Zend_Registry::get('config')->doctrine->migrations_path);
        try {
            $migration->migrate($params[0] ?? null);
        } catch (Doctrine_Exception $e) {
            $message = $e->getMessage();
            if (false !== mb_strpos($e->getMessage(), 'Already at')) {
                echo $message;
                return;
            }
            throw new ZFE_Console_Exception($message);
        }
    }
}
