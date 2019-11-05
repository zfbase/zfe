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
     * {@inheritdoc}
     */
    public function preInsert($event)
    {
        parent::preInsert($event);
        $this->datetime_created = new Doctrine_Expression('NOW()');
    }

    /**
     * @return string
     */
    public function getPerformerCode()
    {
        return $this->performer_code;
    }

    /**
     * @param string $code
     *
     * @return ZFE_Model_Default_Tasks
     */
    public function setPerformerCode(string $code)
    {
        $this->performer_code = $code;
        return $this;
    }

    /**
     * @return int
     */
    public function getRelatedId()
    {
        return $this->related_id;
    }

    /**
     * @param int $itemId
     *
     * @return ZFE_Model_Default_Tasks
     */
    public function setRelatedId(int $itemId)
    {
        $this->related_id = $itemId;
        return $this;
    }

    /**
     * @return int
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * @param int $state
     *
     * @return ZFE_Model_Default_Tasks
     */
    public function setState(int $state)
    {
        $this->state = $state;
        return $this;
    }

    /**
     * @return int
     */
    public function getScheduledAt()
    {
        return $this->scheduled_at;
    }

    /**
     * @param DateTime $dt
     *
     * @return ZFE_Model_Default_Tasks
     */
    public function setScheduledAt(DateTime $dt)
    {
        $this->scheduled_at = $dt->format('Y-m-d');
        return $this;
    }

    /**
     * @return string
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * @param string $errors
     *
     * @return ZFE_Model_Default_Tasks
     */
    public function setErrors(string $errors)
    {
        $this->errors = $errors;
        return $this;
    }

    /**
     * @return string
     */
    public function getDoneAt()
    {
        return $this->done_at;
    }

    /**
     * @param mixed $done_at
     * @param mixed $doneAt
     *
     * @return ZFE_Model_Default_Tasks
     */
    public function setDoneAt($doneAt)
    {
        $this->done_at = $doneAt;
        return $this;
    }

    /**
     * @return int
     */
    public function getRootId(): ?int
    {
        return $this->root_id;
    }

    /**
     * @return ZFE_Model_Default_Tasks
     */
    public function saveAsRoot()
    {
        $this->datetime_created = new Doctrine_Expression('NOW()');
        $this->save();

        $this->root_id = $this->id;
        $tree = Doctrine_Core::getTable(Tasks::class)->getTree();
        $tree->createRoot($this);

        return $this;
    }

    /**
     * @param ZFE_Model_Default_Tasks $task
     *
     * @return ZFE_Model_Default_Tasks
     */
    public function saveAsChildOf(self $task)
    {
        $this->datetime_created = new Doctrine_Expression('NOW()');
        $this->related_id = $task->getRelatedId();
        $this->performer_code = $task->getPerformerCode();

        $this->root_id = $task->getRootId() ?? $task->id;

        $this->getNode()->insertAsLastChildOf($task);
        return $this;
    }
}
