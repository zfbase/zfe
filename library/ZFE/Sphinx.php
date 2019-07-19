<?php

/*
 * ZFE – платформа для построения редакторских интерфейсов.
 */

use Foolz\SphinxQL\Connection;
use Foolz\SphinxQL\Drivers\ConnectionInterface;
use Foolz\SphinxQL\Drivers\ResultSetInterface;
use Foolz\SphinxQL\SphinxQL;

class ZFE_Sphinx
{
    protected static $_connection = null;
    protected static $_config = null;

    /**
     * @return Zend_Config
     */
    public static function config()
    {
        if (!self::$_config) {
            self::$_config = Zend_Registry::get('config')->sphinx;
        }
        return self::$_config;
    }

    /**
     * @return ConnectionInterface
     */
    public static function newConnection()
    {
        $config = self::config();
        $params = $config->conn->toArray();
        $conn = new Connection();
        $conn->setParams($params);
        return $conn;
    }

    /**
     * @return ConnectionInterface
     */
    public static function connection()
    {
        if (null === self::$_connection) {
            self::$_connection = self::newConnection();
        }
        return self::$_connection;
    }

    /**
     * @param ConnectionInterface $connection
     *
     * @return SphinxQL
     */
    public static function query(ConnectionInterface $connection = null)
    {
        return SphinxQL::create($connection ?: self::connection());
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
     * @param string $indexName
     *
     * @throws ZFE_Exception
     *
     * @return array
     */
    public static function getRtIndexSchema($indexName)
    {
        $config = static::config();
        $config_path = realpath($config->config);
        if (is_array($config_path)) {
            $plain_config = '';
            foreach ($config_path as $path) {
                $plain_config .= file_get_contents($config_path);
            }
        } else {
            $plain_config = file_get_contents($config_path);
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

        throw new ZFE_Exception("Индекс {$indexName} не найден в конфигурации {$config_path}.");
    }

    /**
     * @param string $indexName
     * @param array  $data
     *
     * @return array
     */
    public static function filterIndexData($indexName, $data)
    {
        $schema = static::getRtIndexSchema($indexName);

        foreach ($data as $key => $value) {
            switch ($schema[$key]) {
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
    public static function replaceIndexData($indexName, $data, ConnectionInterface $conn = null)
    {
        return static::query($conn)
            ->replace()
            ->into($indexName)
            ->set($data)
            ->execute()
        ;
    }
}
