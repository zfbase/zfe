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
            $config = ZFE_Sphinx::config();
            if (!isset($config->index->{$model})) {
                throw new ZFE_Model_Exception("Индекс {$model} не найден в конфигурации.");
            }
            static::$_sphinxIndexName[$model] = $config->index->{$model};
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

        $query = ZFE_SqlManipulator::parseSql($sql);
        $query->andWhere('x.id = ?');

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
            $config = ZFE_Sphinx::config();
            if (empty($config->sqlQuery->{$model})) {
                $path = $config->sqlPath . DIRECTORY_SEPARATOR . $indexName . '.sql';
            } else {
                $path = $config->sqlQuery->{$model};
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
