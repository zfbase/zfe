<?php

/*
 * ZFE – платформа для построения редакторских интерфейсов.
 */

/**
 * Интерфейс, для моделей, для которых возможна работа с файлами.
 */
interface ZFE_File_Manageable
{
    /**
     * Получить ФМ для объекта модели.
     *
     * @example return ZFE_File_Manager::getDefault($this, $accessControl, $user);
     *
     * @param bool         $accessControl управлять доступом к файлам для пользователя?
     * @param Editors|null $user          обязателен, если $accessControl = true
     *
     * @return ZFE_File_Manager
     */
    public function getFileManager($accessControl, Editors $user = null): ZFE_File_Manager;
}
