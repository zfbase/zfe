<?php

/*
 * ZFE – платформа для построения редакторских интерфейсов.
 */

/**
 * Стандартная модель задач.
 */
abstract class ZFE_Model_Default_Tasks extends BaseTasks
{
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
     * Задача новая?
     */
    public function isNew(): bool
    {
        return $this->datetime_started === NULL
            && $this->datetime_done === NULL
            && $this->return_code === NULL
            && $this->errors === NULL
            && $this->cancel == 0;
    }

    /**
     * Задача исполняется?
     */
    public function isPerformed(): bool
    {
        return $this->datetime_started !== NULL
            && $this->datetime_done === NULL
            && $this->return_code === NULL
            && $this->errors === NULL
            && $this->cancel == 0;
    }

    /**
     * Задача выполнена?
     */
    public function isDone(): bool
    {
        return $this->datetime_done !== NULL
            || $this->return_code !== NULL
            || $this->errors !== NULL;
    }

    /**
     * Задача выполнена успешно?
     */
    public function isSuccess(): bool
    {
        return $this->return_code == 0
            && $this->errors === NULL;
    }

    /**
     * Задача выполнена с ошибками?
     */
    public function isFailed(): bool
    {
        return $this->return_code > 0
            || $this->errors !== NULL;
    }
}
