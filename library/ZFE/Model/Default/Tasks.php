<?php

/*
 * ZFE – платформа для построения редакторских интерфейсов.
 */

/**
 * Стандартная модель задач.
 */
abstract class ZFE_Model_Default_Tasks extends BaseTasks
{
    public static $defaultOrder = 'x.datetime_created DESC';
    public static $defaultOrderKey = 'datetime_created_desc';
    public static $nameSingular = 'Отложенная задача';
    public static $namePlural = 'Отложенные задачи';
    public static $nameFields = [
        'related_id' => 'Субъект',
        'performer_code' => 'Исполнитель',
    ];
    public static $saveHistory = true;

    /**
     * Сохранить как выполняющуюся.
     */
    public function perform(bool $autoSave = true): Tasks
    {
        $this->datetime_started = new Doctrine_Expression('NOW()');
        $this->datetime_done = null;
        $this->datetime_canceled = null;
        $this->return_code = null;
        $this->errors = null;

        if ($autoSave) {
            $this->save();
        }

        return $this;
    }

    /**
     * Сохранить как выполненную.
     */
    public function done(int $returnCode = 0, bool $autoSave = true): Tasks
    {
        // Более точно было бы время определять как можно раньше,
        // но лучше все время отмечать по времени БД

        $this->datetime_done = new Doctrine_Expression('NOW()');
        $this->return_code = $returnCode;

        if ($autoSave) {
            $this->save();
        }

        return $this;
    }

    /**
     * Сохранить как отмененную.
     */
    public function cancel(bool $autoSave = true): Tasks
    {
        $this->datetime_canceled = new Doctrine_Expression('NOW()');

        if ($autoSave) {
            $this->save();
        }

        return $this;
    }

    /**
     * Задача в ожидании исполнения?
     */
    public function inTodo(): bool
    {
        $this->refresh();
        return $this->datetime_canceled === null
            && $this->datetime_started === null
            && $this->datetime_done === null
            && $this->return_code === null
            && $this->errors === null;
    }

    /**
     * Задача исполняется?
     */
    public function isPerformed(): bool
    {
        $this->refresh();
        return $this->datetime_started !== null
            && $this->datetime_done === null
            && $this->return_code === null
            && $this->errors === null;
    }

    /**
     * Задача отменена?
     */
    public function isCanceled(): bool
    {
        $this->refresh();
        return $this->datetime_canceled !== null;
    }

    /**
     * Задача выполнена?
     */
    public function isDone(): bool
    {
        $this->refresh();
        return $this->datetime_done !== null
            || $this->return_code !== null
            || $this->errors !== null;
    }

    /**
     * Задача выполнена успешно?
     */
    public function isSuccess(): bool
    {
        $this->refresh();
        return $this->return_code == 0
            && $this->errors === null;
    }

    /**
     * Задача выполнена с ошибками?
     */
    public function isFailed(): bool
    {
        $this->refresh();
        return $this->return_code > 0
            || $this->errors !== null;
    }

    /**
     * Получить последнего потомка (крайний перезапуск).
     */
    public function getChild()
    {
        $q = Doctrine_Query::create()
            ->from(Tasks::class)
            ->whereIn('parent_id', [$this->id, $this->parent_id])
            ->andWhere('datetime_created > ?', $this->datetime_created)
            ->orderBy('datetime_created DESC')
            ->limit(1)
        ;
        return $q->fetchOne();
    }
}
