<?php

/*
 * ZFE – платформа для построения редакторских интерфейсов.
 */

/**
 * Исполнитель отложенных задач для тестов, всегда исполняющийся «успешно».
 */
class ZFE_Tasks_Performer_Stub extends ZFE_Tasks_Performer
{
    public function perform(int $relatedItemId)
    {
        return $this;
    }
}
