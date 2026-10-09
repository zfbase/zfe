<?php

/*
 * ZFE – платформа для построения редакторских интерфейсов.
 */

/**
 * Ошибка исполнителя отложенных задач.
 * Повторные попытки выполнить задачу не должны предприниматься.
 */
class ZFE_Tasks_Performer_Exception_Permanent extends ZFE_Tasks_Exception
{
}
