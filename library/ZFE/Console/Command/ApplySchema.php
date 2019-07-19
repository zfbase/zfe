<?php

/*
 * ZFE – платформа для построения редакторских интерфейсов.
 */

/**
 * Скрипт для актуализации описания моделей в соотв. со YAML-схемой.
 */
class ZFE_Console_Command_ApplySchema extends ZFE_Console_Command_Abstract
{
    protected static $_name = 'apply-schema';
    protected static $_description = 'Скрипт для актуализации описания моделей';
    protected static $_help =
        'Скрипт берет $config->yaml_schema_path (schema.yml) как актуальное описание схемы и:' . "\n" .
        '1. генерирует файл миграции по разнице между описанием в схеме и описаниями в существующих моделях с помощью задачи generate-migrations-diff' . "\n" .
        '2. и обновляет описание моделей, приводя их в соответствие с описанием в схеме, с помощью задачи generate-models-yaml';
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
        $config = Zend_Registry::get('config')->doctrine;
        if (null === $config->get('rethrow_exceptions')) {
            throw new ZFE_Console_Exception('Параметр rethrow_exceptions не указан в doctrine.ini, запуск без него чреват нарушением порядка действий!');
        }

        $cmd = ZFE_Console_CommandBroker::getInstance()
            ->getCommand('doctrine')
        ;

        try {
            $cmd->execute(array_merge(['generate-migrations-diff'], $params));
        } catch (Doctrine_Task_Exception $e) {
            echo "Описания моделей соответствует описанию схемы\n";
            return;
        }

        $cmd->execute(array_merge(['generate-models-yaml'], $params));
    }
}
