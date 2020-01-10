<?php

/*
 * ZFE – платформа для построения редакторских интерфейсов.
 */

/**
 * Базовый класс пользователей, автоматически генерируемый Doctrine ORM Framework.
 *
 * @property integer $id
 * @property integer $version
 * @property integer $creator_id
 * @property integer $editor_id
 * @property timestamp $datetime_created
 * @property timestamp $datetime_edited
 * @property integer $deleted
 * @property integer $status
 * @property string $second_name
 * @property string $first_name
 * @property string $middle_name
 * @property string $phone
 * @property string $email
 * @property string $login
 * @property string $password
 * @property string $password_salt
 * @property string $role
 * @property string $department
 * @property string $comment
 * @property integer $request_password_change
 */
class BaseEditors extends AbstractRecord
{
}
