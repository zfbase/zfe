<?php

/*
 * ZFE – платформа для построения редакторских интерфейсов.
 */

/**
 * Базовый класс отложенных задач, автоматически генерируемый Doctrine ORM Framework.
 *
 * @property integer $id Идентификатор
 * @property timestamp $datetime_created Дата добавления
 * @property timestamp $datetime_schedule Плановое время запуска
 * @property timestamp $datetime_done Окончание исполнения
 * @property string $performer_code Код исполнителя
 * @property integer $related_id Объект исполнения
 * @property integer $parent_id Родительская задача
 * @property integer $revision Номер попытки исполнения
 * @property integer $state Состояние
 * @property string $errors Ошибки
 */
class BaseTasks extends AbstractRecord
{
}
