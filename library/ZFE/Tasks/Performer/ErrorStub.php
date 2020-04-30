<?php

/*
 * ZFE – платформа для построения редакторских интерфейсов.
 */

/**
 * Исполнитель отложенных задач для тестов, всегда бросающий ошибку.
 */
class ZFE_Tasks_Performer_ErrorStub extends ZFE_Tasks_Performer
{
    public function perform(int $relatedId, ?Zend_Log $logger = null): int
    {
        throw new ZFE_Tasks_Performer_Exception('error');
    }

    public static function checkRelated(AbstractRecord $item): bool
    {
        return true;
    }
}
