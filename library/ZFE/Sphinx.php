<?php

/*
 * ZFE – платформа для построения редакторских интерфейсов.
 */

use Foolz\SphinxQL\Drivers\Pdo\Connection;
use Foolz\SphinxQL\Drivers\ConnectionInterface;
use Foolz\SphinxQL\Drivers\ResultSetInterface;
use Foolz\SphinxQL\SphinxQL;

class ZFE_Sphinx
{
    protected static $_connection = null;
    protected static $_connectionLastUsedAt = 0;
    protected static $_connectionMaxAge = 5;

    /**
     * Соединение для общего индекса.
     *
     * Отдельное соединение позволяет вести транзакции в общем и в основном индексах независимо.
     *
     * @var null|ConnectionInterface
     */
    protected static $_commonIndexConnection;

    /**
     * Кеш схем RT-индексов по имени индекса.
     *
     * Разбор sphinx.conf занимает ~4 мс, а схема нужна на каждую строку при индексации,
     * поэтому результат кешируется в пределах процесса.
     *
     * @var array<string, array>
     */
    protected static $_schemas = [];

    public static function setConnectionMaxAge(int $seconds)
    {
        self::$_connectionMaxAge = $seconds;
    }

    /**
     * @deprecated
     *
     * @return Zend_Config
     */
    public static function config()
    {
        trigger_error('Метод ZFE_Sphinx::config() устарел. Используйте современное решение с config(). ', E_USER_DEPRECATED);
        return config('sphinx');
    }

    /**
     * @return ConnectionInterface
     */
    public static function newConnection()
    {
        $conn = new Connection();
        $conn->setParams(config('sphinx.conn')->toArray());
        return $conn;
    }

    /**
     * @return ConnectionInterface
     */
    public static function connection()
    {
        if (!self::$_connection || ((time() - self::$_connectionLastUsedAt) > self::$_connectionMaxAge)) {
            self::$_connection = self::newConnection();
        }
        self::$_connectionLastUsedAt = time();
        return self::$_connection;
    }

    /**
     * @param ConnectionInterface $connection
     *
     * @return SphinxQL
     */
    public static function query(?ConnectionInterface $connection = null)
    {
        return new SphinxQL($connection ?: self::connection());
    }

    /**
     * @param string      $table
     * @param array|int[] $ids
     * @param bool        $indexed
     *
     * @return ZFE_Query
     */
    public static function doctrineQuery($table, array $ids, $indexed = false)
    {
        $q = ZFE_Query::create()->setHard(true);

        if ($indexed) {
            $q->from("{$table} x INDEXBY x.id");
        } else {
            $q->from("{$table} x");
        }

        if (count($ids) > 0) {
            $q->andWhereIn('x.id', $ids);
            $q->orderByField('x.id', $ids);
        } else {
            $q->where('x.id = 0');
        }

        return $q;
    }

    /**
     * @param ResultSetInterface $resultSet
     *
     * @return array|int[]
     */
    public static function fetchIds(ResultSetInterface $resultSet)
    {
        return array_map(function ($item) {
            return (int) $item[0];
        }, $resultSet->fetchAllNum());
    }

    /**
     * @param SphinxQL $query
     *
     * @return null|int
     */
    public static function fetchOneId(SphinxQL $query)
    {
        $id = $query->execute()->fetchNum();
        return is_array($id) && count($id) > 0 ? (int) $id[0] : null;
    }

    /**
     * @param SphinxQL $query
     * @param string   $table
     *
     * @return ZFE_Model_AbstractRecord
     */
    public static function fetchOne(SphinxQL $query, $table)
    {
        $id = self::fetchOneId($query);
        if (!$id) {
            return null;
        }
        return ZFE_Query::create()
            ->from("{$table} x")
            ->setHard(true)
            ->andWhere('x.id = ?', $id)
            ->fetchOne()
        ;
    }

    /**
     * Получить схему RT-индекса (с кешированием в пределах процесса).
     *
     * @param string $indexName
     *
     * @throws ZFE_Exception
     *
     * @return array
     */
    public static function getRtIndexSchema($indexName)
    {
        if (!isset(self::$_schemas[$indexName])) {
            self::$_schemas[$indexName] = static::_loadRtIndexSchema($indexName);
        }

        return self::$_schemas[$indexName];
    }

    /**
     * Сбросить кеш схем RT-индексов.
     *
     * Нужен долгоживущим процессам, если sphinx.conf изменился после их запуска.
     */
    public static function clearRtIndexSchemaCache()
    {
        self::$_schemas = [];
    }

    /**
     * Прочитать схему RT-индекса из конфигурации Sphinx.
     *
     * @param string $indexName
     *
     * @throws ZFE_Exception
     *
     * @return array
     */
    protected static function _loadRtIndexSchema($indexName)
    {
        $config = config('sphinx.config');
        if (is_array($config)) {
            $plain_config = '';
            foreach ($config as $path) {
                $plain_config .= file_get_contents($path);
            }
        } else {
            $plain_config = file_get_contents(realpath($config));
        }

        $tokens = \LTDBeget\sphinx\Tokenizer::tokenize($plain_config);

        foreach ($tokens as $token) {
            if ('index' === $token['type'] && $token['name'] === $indexName) {
                $schema = ['id' => 'rt_attr_uint'];
                foreach ($token['options'] as $option) {
                    if ('rt_field' === $option['name'] || 'rt_attr_' === mb_substr($option['name'], 0, 8)) {
                        $schema[$option['value']] = $option['name'];
                    }
                }
                return $schema;
            }
        }

        if ($config instanceof Zend_Config) {
            $config = $config->toArray();
        }
        $source = is_array($config) ? implode(', ', $config) : $config;
        throw new ZFE_Exception("Индекс {$indexName} не найден в конфигурации {$source}.");
    }

    /**
     * Привести сырые данные к типам атрибутов индекса.
     *
     * Колонки, которых нет в схеме индекса, остаются без изменений.
     *
     * @param string $indexName
     * @param array  $data
     *
     * @return array
     */
    public static function filterIndexData($indexName, $data)
    {
        $schema = static::getRtIndexSchema($indexName);

        foreach ($data as $key => $value) {
            switch ($schema[$key] ?? null) {
                case 'rt_attr_multi':
                case 'rt_attr_multi_64':
                    $data[$key] = empty($data[$key]) ? [] : array_map('intval', explode(',', $data[$key]));
                    break;
                case 'rt_attr_uint':
                case 'rt_attr_bigint':
                case 'rt_attr_float':
                case 'rt_attr_bool':
                    $data[$key] = (null === $data[$key]) ? 0 : (int) $value;
                    break;
                case 'rt_field':
                case 'rt_attr_string':
                case 'rt_attr_timestamp':
                    $data[$key] = (null === $data[$key]) ? '' : $value;
                    break;
                case 'rt_attr_json':
            }
        }

        return $data;
    }

    /**
     * @param string              $indexName
     * @param array               $data
     * @param ConnectionInterface $conn
     *
     * @return ResultSetInterface
     */
    public static function replaceIndexData($indexName, $data, ?ConnectionInterface $conn = null)
    {
        return static::query($conn)
            ->replace()
            ->into($indexName)
            ->set($data)
            ->execute()
        ;
    }

    /**
     * Обновить записи в индексе одним запросом.
     *
     * Строки одной выборки имеют одинаковый набор колонок, но на всякий случай
     * они группируются по набору колонок: на каждую группу уходит свой запрос.
     *
     * @param string                   $indexName
     * @param array                    $rows      список строк (ассоциативных массивов)
     * @param null|ConnectionInterface $conn
     */
    public static function replaceIndexDataBatch($indexName, array $rows, ?ConnectionInterface $conn = null)
    {
        if (empty($rows)) {
            return;
        }

        $groups = [];
        foreach ($rows as $row) {
            $groups[implode(',', array_keys($row))][] = $row;
        }

        foreach ($groups as $group) {
            $columns = array_keys(reset($group));

            $query = static::query($conn)->replace()->into($indexName)->columns($columns);
            foreach ($group as $row) {
                $query->values(array_values($row));
            }
            $query->execute();
        }
    }

    /**
     * Получить соединение для общего индекса.
     *
     * @return ConnectionInterface
     */
    public static function commonIndexConnection()
    {
        if (self::$_commonIndexConnection === null) {
            self::$_commonIndexConnection = static::newConnection();
        }

        return self::$_commonIndexConnection;
    }

    /**
     * Собрать строку общего индекса по модели и данным индекса модели.
     *
     * Теги модели берутся из конфигурации `sphinx.tags.<Модель>`.
     *
     * @param string $model
     * @param array  $data
     *
     * @return array
     */
    public static function commonIndexRow($model, $data)
    {
        return [
            'id' => $data['id'],
            'attr_id' => $data['attr_id'],
            'attr_deleted' => $data['attr_deleted'],
            'model' => $model,
            'tags' => config("sphinx.tags.{$model}"),
            'title' => $data['title'],
            'code' => empty($data['code']) ? '' : $data['code'],
            'details' => empty($data['details']) ? '' : $data['details'],
            'persons' => empty($data['persons']) ? '' : $data['persons'],
            'id_number' => empty($data['id_number']) ? '' : $data['id_number'],
        ];
    }

    /**
     * Обновить запись в общем индексе (`sphinx.index.Common`).
     *
     * @param string $model
     * @param array  $data  данные индекса модели
     *
     * @return ResultSetInterface
     */
    public static function updateCommonIndexByModelAndData($model, $data)
    {
        return static::replaceIndexData(
            config('sphinx.index.Common'),
            static::commonIndexRow($model, $data),
            static::commonIndexConnection()
        );
    }

    /**
     * Обновить записи в общем индексе (`sphinx.index.Common`) одним запросом.
     *
     * @param string $model
     * @param array  $rows  список массивов данных индекса модели
     */
    public static function updateCommonIndexByModelAndDataBatch($model, array $rows)
    {
        $commonRows = [];
        foreach ($rows as $data) {
            $commonRows[] = static::commonIndexRow($model, $data);
        }

        static::replaceIndexDataBatch(
            config('sphinx.index.Common'),
            $commonRows,
            static::commonIndexConnection()
        );
    }
}
