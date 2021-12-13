<?php

/*
 * ZFE – платформа для построения редакторских интерфейсов.
 */

/**
 * Функционал для Sphinx.
 */
trait ZFE_Model_AbstractRecord_Sphinx
{
    /**
     * Имя Sphinx-индекса.
     *
     * @var array<string>|string[]
     */
    protected static $_sphinxIndexName = [];

    /**
     * Адрес до SQL-файла для загрузки данных в Sphinx-индекс
     *
     * @var array<string>|string[]
     */
    protected static $_sphinxIndexSqlPath = [];

    /**
     * Обновить запись в RT-индексе Sphinx.
     */
    public function updateSphinxRtIndex()
    {
        $indexName = static::getSphinxIndexName();
        $data = $this->getDataForSphinxIndex();
        ZFE_Sphinx::replaceIndexData($indexName, $data);
    }

    /**
     * Получить индекс, соответствующей модели.
     *
     * @throws ZFE_Model_Exception
     *
     * @return string
     */
    public static function getSphinxIndexName()
    {
        $model = static::class;
        if (empty(static::$_sphinxIndexName[$model])) {
            $index = config("sphinx.index.{$model}");
            if (!isset($index)) {
                throw new ZFE_Model_Exception("Индекс {$model} не найден в конфигурации.");
            }
            static::$_sphinxIndexName[$model] = $index;
        }

        return static::$_sphinxIndexName[$model];
    }

    /**
     * Получить массив данных для Sphinx-индекса.
     *
     * @return array
     */
    public function getDataForSphinxIndex()
    {
        $sqlPath = static::getSphinxIndexSqlPath();
        $sql = file_get_contents($sqlPath);
        $model = static::class;
        $alias = config("sphinx.alias.{$model}", 'x');

        $query = ZFE_SqlManipulator::parseSql($sql);
        $query->andWhere("{$alias}.id = ?");

        $conn = Doctrine_Manager::connection()->getDbh();
        $q = $conn->prepare($query->getSql());
        $q->execute([$this->id]);
        $row = $q->fetch(PDO::FETCH_ASSOC);

        return static::filterIndexData($row);
    }

    /**
     * Получить адрес до SQL-файла для загрузки данных в Sphinx-индекс
     *
     * @throws ZFE_Exception
     *
     * @return string
     */
    public static function getSphinxIndexSqlPath()
    {
        $model = static::class;
        if (empty(static::$_sphinxIndexSqlPath[$model])) {
            $indexName = static::getSphinxIndexName();
            $path = config("sphinx.sqlQuery.{$model}");
            if (empty($path)) {
                $path = realpath(config('sphinx.sqlPath')) . DIRECTORY_SEPARATOR . $indexName . '.sql';
            }
            if (!file_exists($path)) {
                throw new ZFE_Exception("SQL-запрос {$path} не найден.");
            }
            static::$_sphinxIndexSqlPath[$model] = $path;
        }

        return static::$_sphinxIndexSqlPath[$model];
    }

    /**
     * Подготовить сырые данные для Sphinx-индекса для загрузки в Sphinx.
     *
     * @param array $data
     *
     * @return array
     */
    public static function filterIndexData(array $data)
    {
        $indexName = static::getSphinxIndexName();
        return ZFE_Sphinx::filterIndexData($indexName, $data);
    }
}
