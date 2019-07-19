<?php

/**
 * Class ZFE_Tasks_Performer
 */
abstract class ZFE_Tasks_Performer
{
    static public function forge($code)
    {
        $performerClassName = ZFE_Tasks_Performer::class . '_' . $code;
        return new $performerClassName;
    }

    final public function __construct() {}

    /**
     * Получить код исполнителя
     * Код исполнителя определяется как последняя часть имени класс по PSR-0:
     * Application_Performer_LazyPerson -> LazyPerson
     * @return string
     */
    final public function getCode()
    {
        $parts = explode('_', static::class);
        $code = array_pop($parts);
        return $code;
    }

    /**
     * Выполнить необходимые действия по задаче для указанной по id записи в БД
     *
     * @param int $relatedItemId Идентификатор записи БД
     * @throw ZFE_Tasks_Performer_Exception
     */
    abstract public function perform(int $relatedItemId);
}
