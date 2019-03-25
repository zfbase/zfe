<?php

/*
 * ZFE – платформа для построения редакторских интерфейсов.
 */

/**
 * Скрипт для актуализации описания моделей.
 */
class ZFE_Console_Command_ApplySchema extends ZFE_Console_Command_Abstract
{
    protected static $_name = 'apply-schema';
    protected static $_description = 'Скрипт для актуализации описания моделей';
    protected static $_help =
        "Скрипт берет \$config->yaml_schema_path (schema.yml) как актуальное описание схемы и:\n".
        "1. генерирует файл миграции по разнице между описанием в схеме и описаниями в существующих моделях с помощью задачи generate-migrations-diff\n".
        "2. и обновляет описание моделей, приводя их в соответствие с описанием в схеме, с помощью задачи generate-models-yaml";

    /**
     * {@inheritdoc}
     */
    public function execute(array $params = [])
    {
        $config = Zend_Registry::get('config')->doctrine;
        if ($config->get('rethrow_exceptions') === null) {
            throw new ZFE_Console_Exception('Параметр rethrow_exceptions не указан в doctrine.ini, запуск без него чреват нарушением порядка действий!');
        }

        /**
         * Эти файлы хранятся в library/doctrine1/lib/Doctrine/Parser/sfYaml
         * Они не поддерживают общий для Doctrine PSR-0 и не имеют пространства имен.
         * В Doctrine_Core::autoload есть специальный блок для обработки этих файлов, что намекает,
         * что эти файлы из какой-то сторонней библиотеки.
         * В любом случае, я не знаю, почему они не подгружаются автоматически, но они нужны для задачи generate-migrations-diff
         */
        Doctrine_Core::autoload('sfYaml');
        Doctrine_Core::autoload('sfYamlDumper');
        Doctrine_Core::autoload('sfYamlParser');

        try {
            $cli = new Doctrine_Cli($config->toArray());
            $cli->run(array_merge(['doctrine-cli', 'generate-migrations-diff'], $params));
        } catch (Doctrine_Task_Exception $e) {
            throw new ZFE_Console_Exception(implode('', [$e->getMessage(), 'Описания моделей соответствует описанию схемы']));
        }
        
        $cli->run([__FILE__, 'generate-models-yaml']);
    }
}
