<?php

/*
 * ZFE – платформа для построения редакторских интерфейсов.
 */

/**
 * Обертка над doctrine-cli.
 */
class ZFE_Console_Command_DoctrineCli extends ZFE_Console_Command_Abstract
{
    public static function getName()
    {
        return 'doctrine';
    }

    protected static $_description = 'Обертка над doctrine-cli';
    protected static $_help =
        'help                       | Подробная справка по командам' . "\n" .
        'create-db                  | Создать все БД (только схемы). Если БД уже существует, ничего не происходит' . "\n" .
        'generate-models-db         | Создать модели по БД' . "\n" .
        'drop-db                    | Удалить все БД (вместе со схемами)' . "\n" .
        'generate-sql               | Создать SQL по моделям' . "\n" .
        'generate-migrations-diff   | Создать классы миграции на основе сгенерированной разницы между моделями и файлами схемы YAML' . "\n" .
        'compile                    | Собрать и минифицировать весь код Doctrine в один файл' . "\n" .
        'build-all                  | Вызывает generate-models-from-yaml, create-db, и create-tables' . "\n" .
        'build-all-reload           | Вызывает rebuild-db и load-data' . "\n" .
        'generate-migration         | Создать миграция. Требует указать имя миграции' . "\n" .
        'load-data                  | Загрузить данные из YAML (fixtures) в БД' . "\n" .
        'migrate                    | Применить миграции' . "\n" .
        'build-all-load             | Вызывает build-all и load-data' . "\n" .
        'generate-yaml-models       | Создать YAML по моделям' . "\n" .
        'create-tables              | Создать таблицы в БД. Если таблица существует, ничего не происходит' . "\n" .
        'generate-migrations-models | Создать миграции по моделям' . "\n" .
        'dump-data                  | Выгрузить данные из БД в YAML (fixtures)' . "\n" .
        'dql                        | Выполнить DQL и вывести результаты запроса' . "\n" .
        'generate-models-yaml       | Создать модели по YAML' . "\n" .
        'rebuild-db                 | Пересоздать БД' . "\n" .
        'generate-migrations-db     | Создать миграцию по БД (создает по файлу миграции создания каждой из таблиц)' . "\n" .
        'generate-yaml-db           | Создать YAML по БД';

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
        $cli = new Doctrine_Cli(config('doctrine')->toArray());
        $cli->run(array_merge(['doctrine-cli'], $params));
    }
}
