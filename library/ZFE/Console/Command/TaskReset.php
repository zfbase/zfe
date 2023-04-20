<?php

/*
 * ZFE – платформа для построения редакторских интерфейсов.
 */

class ZFE_Console_Command_TaskReset extends ZFE_Console_Command_Abstract
{
    protected static $_description = 'Перезапустить начатые, но не завершенные отложенные задачи';
    protected static $_help = 'Команда ожидает коды отложенных задач или all для задач всех типов';

    /**
     * {@inheritdoc}
     */
    public function execute(array $params = [])
    {
        /** @var Zend_Config|null */
        $config = config('tasks.performers');
        if (!$config) {
            echo "Обработчики отложенных задач не определены.\n";
            return;
        }

        $performerCodes = array_map(
            fn ($performerName) => $performerName::getCode(),
            $config->toArray(),
        );

        if (empty($params)) {
            echo "Для перезапуска отложенных задач необходимо указать коды задач или all для задач всех типов.\n";
            echo "Список все поддерживаемых кодов:\n";
            echo implode("\n", $performerCodes) . "\n";
            return;
        }

        if (count($params) > 1 || $params[0] !== 'all') {
            $hasUnknown = 0 < count(array_diff($params, $performerCodes));
            if ($hasUnknown) {
                if (in_array('all', $params)) {
                    echo "Параметр all не может быть указан одновременно с конкретными кодами.\n";
                }

                $unknownList = array_diff($params, $performerCodes, ['all']);
                if (count($unknownList) > 0) {
                    echo 'Указан неизвестный код(ы) исполнителя отложенных задач: ';
                    echo implode(', ', $unknownList) . "\n";
                }

                echo "Перезапуск отменен.\n";
                return;
            }
        }

        $q = ZFE_Query::create()
            ->update(Tasks::class)
            ->where('datetime_started IS NOT NULL')
            ->addWhere('datetime_done IS NULL')
            ->addWhere('datetime_canceled IS NULL')
            ->addWhere('return_code IS NULL')
            ->addWhere('errors IS NULL')
            ->set('datetime_started', new Doctrine_Expression('NULL'))
        ;

        if ($params[0] !== 'all') {
            $q->andWhereIn('performer_code', $params);
        }

        $updatedCount = $q->execute();

        $msgForms = ['Перезапущена %d задача', 'Перезапущено %d задачи', 'Перезапущено %d задач'];
        printf(ZFE_Utilities::plural($updatedCount, $msgForms) . ".\n", $updatedCount);
    }
}
