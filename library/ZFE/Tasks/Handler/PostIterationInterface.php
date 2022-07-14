<?php

interface ZFE_Tasks_Handler_PostIterationInterface
{
    /**
     * @param array $performers перечень обработчиков для которых пройден цикл обработки
     */
    public static function iteration(array $performers = []);
}
