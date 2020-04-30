<?php

/*
 * ZFE – платформа для построения редакторских интерфейсов.
 */

/**
 * Базовый класс отложенных задач, автоматически генерируемый Doctrine ORM Framework.
 *
 * @property integer $id Идентификатор
 * @property timestamp $datetime_created Дата и время добавления
 * @property timestamp $datetime_schedule Дата и время планового запуска
 * @property timestamp $datetime_started Дата и время начала исполнения
 * @property timestamp $datetime_done Дата и время окончания исполнения
 * @property timestamp $datetime_canceled Дата и время отменены
 * @property integer $priority Приоритет
 * @property string $performer_code Код исполнителя
 * @property integer $related_id Объект исполнения
 * @property integer $parent_id Родительская задача
 * @property integer $revision Номер попытки исполнения
 * @property integer $return_code Код результата исполнения
 * @property string $errors Ошибки
 */
class BaseTasks extends AbstractRecord
{
}
