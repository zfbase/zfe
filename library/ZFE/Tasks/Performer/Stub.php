<?php

/*
 * ZFE – платформа для построения редакторских интерфейсов.
 */

/**
 * Исполнитель отложенных задач для тестов, всегда исполняющийся «успешно».
 */
class ZFE_Tasks_Performer_Stub extends ZFE_Tasks_Performer
{
    public function perform(int $relatedId, ?Zend_Log $logger = null): int
    {
        return static::RETURN_CODE_SUCCESS;
    }

    public static function checkRelated(AbstractRecord $item): bool
    {
        return true;
    }
}
