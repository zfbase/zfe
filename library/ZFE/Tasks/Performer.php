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
     * Код успешного выполнения задачи.
     */
    const RETURN_CODE_SUCCESS = 0;

    /**
     * Логгер.
     *
     * @var Zend_Log|null
     */
    protected $logger;

    /**
     * Создать экземпляр исполнителя.
     */
    public static function factory(): self
    {
        return new static;
    }

    /**
     * Указать логгер.
     */
    public function setLogger(?Zend_Log $logger)
    {
        $this->logger = $logger;
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
