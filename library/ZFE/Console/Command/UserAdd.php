<?php

/*
 * ZFE – платформа для построения редакторских интерфейсов.
 */

/**
 * Команда добавления нового пользователя.
 */
class ZFE_Console_Command_UserAdd extends ZFE_Console_Command_Abstract
{
    protected static $_name = 'useradd';
    protected static $_description = 'Добавить пользователя';
    protected static $_help = 'Добавляет администратора с логином из обязательного параметра.';

    /**
     * {@inheritdoc}
     */
    public function execute(array $params = [])
    {
        if (count($params) != 1) {
            echo "<error>Не указан обязательный параметр – логин пользователя.</error>\n";
            return;
        }

        $login = array_shift($params);

        if (Editors::findOneBy('login', $login)) {
            echo "<error>Пользователь с таким логином уже существует.</error>\n";
            return;
        }

        $password = mb_substr(uniqid('', true), 0, 8);

        $item = new Editors();
        $item->second_name = $login;
        $item->login = $login;
        $item->role = 'admin';
        $item->setPassword($password);
        $item->save();

        echo "Добавлен пользователь c логином <info>{$login}</info> и паролем <info>{$password}</info>.\n";
    }
}
