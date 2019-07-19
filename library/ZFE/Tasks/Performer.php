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
     * @param string $name
     * 
     * @return ZFE_Tasks_Performer
     */
    public static function forge($code)
    {
        $performerClassName = self::class . '_' . $code;
        return new $performerClassName;
    }

    final public function __construct()
    {
    }

    /**
     * Получить код исполнителя.
     * Код исполнителя определяется как последняя часть имени класс по PSR-0:
     * Application_Performer_LazyPerson -> LazyPerson.
     *
     * @return string
     */
    final public function getCode()
    {
        $parts = explode('_', static::class);
        $code = array_pop($parts);
        return $code;
    }

    /**
     * Выполнить необходимые действия по задаче для указанной по id записи в БД.
     *
     * @param int $relatedItemId Идентификатор записи БД
     * @throw ZFE_Tasks_Performer_Exception
     */
    abstract public function perform(int $relatedItemId);
}
