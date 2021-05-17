<?php

/*
 * ZFE – платформа для построения редакторских интерфейсов.
 */

/**
 * Получить используемую конфигурацию.
 */
class ZFE_Console_Command_Config extends ZFE_Console_Command_Abstract
{
    protected static $_description = 'Получить используемую конфигурацию';
    protected static $_help = 'При указании аргументом подсекции, будет отображено только её содержание';

    public function execute(array $params = [])
    {
        if (!empty($params[0])) {
            $section = config($params[0]);
            $config = new Zend_Config([], true);
            $config->{$params[0]} = $section;
        } else {
            $config = config();
        }
        $writer = new Zend_Config_Writer_Yaml(['config' => $config]);
        echo $writer->render();
    }
}
