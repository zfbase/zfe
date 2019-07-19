<?php

/*
 * ZFE – платформа для построения редакторских интерфейсов.
 */

/**
 * Исполнитель отложенных задач для тестов, всегда бросающий ошибку.
 */
class ZFE_Tasks_Performer_ErrorStub extends ZFE_Tasks_Performer
{
    public function perform(int $relatedItemId)
    {
        throw new ZFE_Tasks_Performer_Exception('error');
    }
}
