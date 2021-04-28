<?php

/*
 * ZFE – платформа для построения редакторских интерфейсов.
 */

class ZFE_Console_Command_TaskRevision extends ZFE_Console_Command_Abstract
{
    protected static $_description = 'Перезапланировать отложенную задачу';
    protected static $_help = 'Команда ожидает один параметр – ID задачи для перезапуска';

    /**
     * {@inheritdoc}
     */
    public function execute(array $params = [])
    {
        if (count($params) !== 1) {
            echo '<error>' . static::$_help . "</error>\n";
            return;
        }

        $id = $params[0];
        $task = Tasks::hardFind($id);
        if (!$task) {
            echo "<error>Задача с ID #{$id} не найдена</error>\n";
        }

        $reTask = ZFE_Tasks_Manager::getInstance()->revision($task);
        echo "<info>Добавлена новая задача с ID #{$reTask->id}</info>\n";
    }
}
