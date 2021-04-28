<?php

/*
 * ZFE – платформа для построения редакторских интерфейсов.
 */

/**
 * Базовый класс пользователей, автоматически генерируемый Doctrine ORM Framework.
 *
 * @property int       $id
 * @property int       $version
 * @property int       $creator_id
 * @property int       $editor_id
 * @property timestamp $datetime_created
 * @property timestamp $datetime_edited
 * @property int       $deleted
 * @property int       $status
 * @property string    $second_name
 * @property string    $first_name
 * @property string    $middle_name
 * @property string    $phone
 * @property string    $email
 * @property string    $login
 * @property string    $password
 * @property string    $password_salt
 * @property string    $role
 * @property string    $department
 * @property string    $comment
 * @property int       $request_password_change
 */
class BaseEditors extends AbstractRecord
{
}
