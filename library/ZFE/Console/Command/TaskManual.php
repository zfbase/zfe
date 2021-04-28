<?php

/*
 * ZFE – платформа для построения редакторских интерфейсов.
 */

class ZFE_Console_Command_TaskManual extends ZFE_Console_Command_Abstract
{
    protected static $_description = 'Выполнить отложенную задачу';
    protected static $_help =
        'Команда ожидает указание ID задачи (параметр id) или код исполнителя (performer) с ID объекта исполнения (rel).' . "\n" .
        'Примеры: `task-manual id 1`, `task-manual performer MakeProxy rel 12`';

    // Возвращаемые коды ошибок
    const ERROR_INCORRECT_TASK_ID = 1;
    const ERROR_REMOTE_TASK_ID = 2;
    const ERROR_INCORRECT_PARAMS = 3;
    const ERROR_INCORRECT_PARAMS_4 = 4;
    const ERROR_INCORRECT_TASK_META = 5;
    const ERROR_TASK_CANCELED = 6;
    const ERROR_RACING = 7;
    const ERROR = 10;

    /**
     * {@inheritdoc}
     */
    public function execute(array $params = [])
    {
        $manager = ZFE_Tasks_Manager::getInstance();
        $numParams = count($params);


        if ($numParams == 2 && $params[0] == 'id' && is_numeric($params[1])) {
            $task = Tasks::hardFind($params[1]);

            if (!$task) {
                echo "<error>Задача с ID #{$params[1]} не найдена в базе.</error>\n";
                return static::ERROR_INCORRECT_TASK_ID;
            }

            if ($task->isDeleted()) {
                echo "<error>Задача с ID #{$params[1]} не может быть выполнена – она удалена.</error>\n";
                return static::ERROR_REMOTE_TASK_ID;
            }
        } elseif ($numParams == 4 || $numParams == 5) {
            if ($params[0] == 'performer' && $params[2] == 'rel' && is_numeric($params[3])) {
                $performerCode = $params[1];
                $relatedId = $params[3];
            } elseif ($params[0] == 'rel' && is_numeric($params[1]) && $params[2] === 'performer') {
                $performerCode = $params[3];
                $relatedId = $params[1];
            } else {
                echo "<error>Не корректные параметры.</error>\n";
                echo static::$_help . "\n";
                return static::ERROR_INCORRECT_PARAMS_4;
            }

            $task = $manager->findOnePlanned($performerCode, $relatedId);
            if (!$task) {
                echo "<error>Задача для исполнителя {$performerCode} и объекта #{$relatedId} не найдена в плане исполнения.</error>\n";
                return static::ERROR_INCORRECT_TASK_META;
            }
        } else {
            echo "<error>Не корректные параметры.</error>\n";
            echo static::$_help . "\n";
            return static::ERROR_INCORRECT_PARAMS;
        }


        if ($manager->manage([$task])) {
            echo "<info>Задача #{$task->id} выполнена.</info>\n";
            return 0;
        }

        if ($task->isCanceled()) {
            echo "<error>Задача была отменена в {$task->datetime_canceled}.</error>\n";
            return static::ERROR_TASK_CANCELED;
        }

        if ($task->isDone()) {
            echo "<error>Задача была выполнена ранее или параллельным процессом в {$task->datetime_done}.</error>\n";
            echo "Для повторного исполнения необходимо перезапустить её.\n";
            return static::ERROR_RACING;
        }

        return static::ERROR;
    }
}
