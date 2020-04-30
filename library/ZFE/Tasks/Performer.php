<?php

/*
 * ZFE – платформа для построения редакторских интерфейсов.
 */

/**
 * Абстрактный исполнитель отложенных задач.
 */
abstract class ZFE_Tasks_Performer
{
    /**
     * Создать экземпляр исполнителя.
     */
    public static function factory(): ZFE_Tasks_Performer
    {
        return new static;
    }

    /**
     * Получить код исполнителя.
     *
     * Код исполнителя определяется как последняя часть имени класс по PSR-0:
     * Application_Performer_LazyPerson -> LazyPerson.
     */
    public static function getCode(): string
    {
        $parts = explode('_', static::class);
        $code = array_pop($parts);
        return $code;
    }

    /**
     * Выполнить необходимые действия по задаче для объекта с указанным ID.
     *
     * @throws ZFE_Tasks_Performer_Exception
     */
    abstract public function perform(int $relatedId, ?Zend_Log $logger = null): int;

    /**
     * Метод для проверки, что передаётся допустимый объект исполнения.
     */
    abstract public static function checkRelated(AbstractRecord $item): bool;
}
