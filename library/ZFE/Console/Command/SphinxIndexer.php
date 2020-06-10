<?php

/*
 * ZFE – платформа для построения редакторских интерфейсов.
 */

class ZFE_Console_Command_SphinxIndexer extends ZFE_Console_Command_Abstract
{
    protected static $_name = 'indexer';
    protected static $_description = 'Индексация Sphinx';
    protected static $_help =
        'При вызове без аргументов проиндексируются все модели.' . "\n" .
        'Для индексации определенных моделей перечислите их через пробел.' . "\n" .
        'Для отключения прогресс-бара добавьте аргумент _noProgress.';

    /**
     * Помощник для рендеринга таблиц.
     *
     * @var ZFE_Console_Helper_Table
     */
    protected $_table;

    /**
     * Использовать простой общий индекс?
     *
     * @var bool
     */
    protected $_useSimpleCommonIndex = false;

    /**
     * Показывать прогресс-бар индексации?
     *
     * @var bool
     */
    protected $_showProgress = true;

    /**
     * {@inheritdoc}
     */
    public function execute(array $params = [])
    {
        $noProgressIndex = array_search('_noProgress', $params);
        if ($noProgressIndex !== false) {
            $this->_showProgress = false;
            unset($params[$noProgressIndex]);
        }

        if (empty($params)) {
            /** @var ZFE_Console_Helper_Table $table */
            $table = $this->_table = $this->getHelperBroker()->get('Table');
            $table->setHeaders(['Код', 'Модель']);
            $models = $this->_getAllModels();
            foreach ($models as $i => $model) {
                $table->addRow([$i, $model]);
            }
            $table->render();

            $example = (array_shift($models) ?? 'Tags') . ' ' . (array_shift($models) ?? 'Items');
            echo "Для индексации всех моделей передайте параметром all:\n";
            echo "  > <info>composer tool indexer all</info>\n";

            echo "Для индексации конкретных моделей перечислите их названия или коды через пробел:\n";
            echo "  > <info>composer tool indexer {$example}</info>\n";
            echo "  > <info>composer tool indexer 1 4</info>\n";
            return;
        }

        if (in_array('all', $params)) {
            $models = $this->_getAllModels();
        } else {
            $models = $params;
        }

        $timeStart = time();

        $allModels = $this->_getAllModels();
        $allModelsMap = array_flip(array_map('mb_strtolower', $allModels));
        $maxLenModelName = 0;
        foreach ($models as $i => $model) {
            if (!is_numeric($model)) {
                $model = $allModelsMap[mb_strtolower($model)];
            }

            $models[$i] = $allModels[$model];
            $len = mb_strlen($models[$i]);
            if ($maxLenModelName < $len) {
                $maxLenModelName = $len;
            }
        }

        if ($this->_showProgress) {
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
        }

        foreach ($models as $model) {
            $this->_indexer($model);
        }

        if ($this->_showProgress) {
            echo $table->renderRowSeparator();
        }

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

        $startTime = microtime(true);
        if ($this->_showProgress) {
            /** @var ZFE_Console_Helper_ProgressBar $progressBar */
            $progressBar = $this->getHelperBroker()->get('ProgressBar');
            $progressBar->setTemplate('[placeholder] [percent]');
            $progressBar->setFinishTemplate('[value] из [max]');
            $progressBar->setPlaceholderWidth(50);

            echo trim($this->_table->renderRow([$model, '', '0.000']));

            $total = (int) $conn->query("SELECT COUNT(*) FROM (${sql}) _base_query")->fetch()[0];
            $progressBar->start($total, null, null, $startTime, false);
            $this->_updateRow($model, $progressBar);
        } else {
            echo "Индексация {$model} ... ";
        }

        $id = 0;
        $prevId = 0;
        $done = 0;
        do {
            $prevId = $id;
            $q->execute([$id]);
            ZFE_Sphinx::query()->transactionBegin();
            if ($this->_useSimpleCommonIndex) {
                ZFE_Sphinx::query(Utils_Sphinx::commonIndexConnection())->transactionBegin();
            }
            while ($row = $q->fetch(PDO::FETCH_ASSOC)) {
                $data = $model::filterIndexData($row);
                ZFE_Sphinx::replaceIndexData($indexName, $data);
                if ($this->_useSimpleCommonIndex) {
                    Utils_Sphinx::updateCommonIndexByModelAndData($model, $data);
                }
                $id = $row['attr_id'];

                if ($this->_showProgress) {
                    $this->_updateRow($model, $progressBar, ++$done);
                }
            }
            if ($this->_useSimpleCommonIndex) {
                ZFE_Sphinx::query(Utils_Sphinx::commonIndexConnection())->transactionCommit();
            }
            ZFE_Sphinx::query()->transactionCommit();
        } while ($prevId !== $id);

        if ($this->_showProgress) {
            $progressBar->finish(false);
            $this->_updateRow($model, $progressBar);
            unset($progressBar);
        } else {
            printf('выполнена за %.3f сек.', microtime(true) - $startTime);
        }

        echo "\n";
    }

    /**
     * Обновить последнюю строку таблицы с прогрессом индексации модели.
     *
     * @param string                         $model
     * @param ZFE_Console_Helper_ProgressBar $progressBar
     * @param float|null                     $done
     */
    protected function _updateRow(string $model, ZFE_Console_Helper_ProgressBar $progressBar, ?float $done = null)
    {
        echo "\r" . trim($this->_table->renderRow([
            $model,
            $done ? $progressBar->setValue($done, false) : $progressBar->render(false),
            sprintf('%.3f', $progressBar->getTime()),
        ]));
    }
}
