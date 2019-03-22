<?php

/*
 * ZFE – платформа для построения редакторских интерфейсов.
 */

class ZFE_Console_Command_SphinxIndexer extends ZFE_Console_Command_Abstract
{
    protected static $_name = 'indexer';
    protected static $_description = 'Индексация Sphinx';
    protected static $_help =
        "При вызове без аргументов проиндексируются все модели.\n" .
        "Для индексации определенных моделей перечислите их через пробел.";

    /**
     * Помощник для рендеринга таблиц.
     *
     * @var ZFE_Console_Helper_Table
     */
    protected $_table;

    /**
     * {@inheritdoc}
     */
    public function execute(array $params = [])
    {
        if (count($params) && 'all' !== $params[0]) {
            $models = $params;
        } else {
            $models = $this->_getAllModels();
        }

        $timeStart = time();

        $maxLenModelName = 0;
        foreach ($models as $model) {
            $len = mb_strlen($model);
            if ($maxLenModelName < $len) {
                $maxLenModelName = $len;
            }
        }

        $header = ['Модель', 'Прогресс / Результат', 'Время, сек'];

        /** @var ZFE_Console_Helper_Table $table */
        $table = $this->_table = $this->getHelperBroker()->get('Table');
        $table->setHeaders($header);
        $table->setColumnWidth(0, $maxLenModelName);
        $table->setColumnWidth(1, 55);
        $table->setColumnAlign(2, ZFE_Console_Helper_Table::ALIGN_RIGHT);

        $table->prepare();
        echo $table->renderRowSeparator();
        echo $table->renderRow($header);
        echo $table->renderRowSeparator();
        foreach ($models as $model) {
            $this->_indexer($model);
        }
        echo $table->renderRowSeparator();
        echo 'Общее время индексации – ' . (time() - $timeStart) . " сек.\n";
    }

    /**
     * Получить список моделей для индексации.
     */
    protected function _getAllModels()
    {
        $indexes = array_keys(ZFE_Sphinx::config()->index->toArray());
        return array_diff($indexes, ['Common']); // Исключаем объединенный индекс
    }

    /**
     * Индексировать модель.
     */
    protected function _indexer(string $model)
    {
        $indexName = $model::getSphinxIndexName();
        $sqlPath = $model::getSphinxIndexSqlPath();
        $sql = file_get_contents($sqlPath);

        $query = ZFE_SqlManipulator::parseSql($sql);
        $query->andWhere('x.id > ?');
        $query->orderBy('x.id');
        $query->limit(1000);

        $conn = Doctrine_Manager::connection()->getDbh();
        $q = $conn->prepare($query->getSql());

        $total = (int) $conn->query("SELECT COUNT(*) FROM (${sql}) _base_query")->fetch()[0];

        /** @var ZFE_Console_Helper_ProgressBar $progressBar */
        $progressBar = $this->getHelperBroker()->get('ProgressBar');
        $progressBar->setTemplate('[placeholder] [percent]');
        $progressBar->setFinishTemplate('[value] из [max]');
        $progressBar->setPlaceholderWidth(50);

        echo trim($this->_table->renderRow([
            $model,
            $progressBar->start($total, null, null, false),
            (string) $progressBar->getTime(),
        ]));

        $id = 0;
        $prevId = 0;
        $done = 0;
        do {
            $prevId = $id;
            $q->execute([$id]);
            ZFE_Sphinx::query()->transactionBegin();
            //ZFE_Sphinx::query(Utils_Sphinx::commonIndexConnection())->transactionBegin();
            while ($row = $q->fetch(PDO::FETCH_ASSOC)) {
                $data = $model::filterIndexData($row);
                ZFE_Sphinx::replaceIndexData($indexName, $data);
                //Utils_Sphinx::updateCommonIndexByModelAndData($model, $data);
                $id = $row['attr_id'];
                echo "\r" . trim($this->_table->renderRow([
                    $model,
                    $progressBar->setValue(++$done, false),
                    (string) $progressBar->getTime(),
                ]));
            }
            //ZFE_Sphinx::query(Utils_Sphinx::commonIndexConnection())->transactionCommit();
            ZFE_Sphinx::query()->transactionCommit();
        } while ($prevId !== $id);

        echo "\r" . $this->_table->renderRow([
            $model,
            $progressBar->finish(false),
            (string) $progressBar->getTime(),
        ]);
        unset($progressBar);
    }
}
