<?php

/*
 * ZFE – платформа для построения редакторских интерфейсов.
 */

/**
 * Стандартная модель задач.
 */
abstract class ZFE_Model_Default_Tasks extends BaseTasks
{
    const STATE_TODO = 0;
    const STATE_CANCELED = 3;
    const STATE_PERFORM = 5;
    const STATE_DONE = 10;

    /**
     * Сохранить как отмененную.
     */
    public function cancel(): Tasks
    {
        if ($this->state == status::STATE_DONE) {
            throw new Application_Exception('Нельзя отменить уже выполненную задачу');
        }

        $this->datetime_done = new Doctrine_Expression('NOW()');
        $this->state = static::STATE_CANCELED;
        $this->save();

        return $this;
    }

    /**
     * Сохранить как выполняющуюся.
     */
    public function perform(): Tasks
    {
        /** @todo Подучать над добавлением поля для времени начала выполнения */

        $this->datetime_done = null;
        $this->state = static::STATE_PERFORM;
        $this->save();

        return $this;
    }

    /**
     * Сохранить как выполненную.
     */
    public function done(): Tasks
    {
        $this->datetime_done = new Doctrine_Expression('NOW()');
        $this->state = static::STATE_DONE;
        $this->save();

        return $this;
    }

    /**
     * Присутствуют ошибки исполнения?
     */
    public function hasErrors(): bool
    {
        return !!$this->errors;
    }
}
