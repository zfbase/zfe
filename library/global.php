<?php

/*
 * ZFE – платформа для построения редакторских интерфейсов.
 */

/**
 * Помощник для получения значения параметра окружения.
 *
 * @param null|mixed $default
 */
function env(string $name, $default = null)
{
    $value = $_ENV[$name] ?? false;

    if ($value === false) {
        return $default;
    }

    switch (mb_strtolower($value)) {
        case 'true':
            return true;
        case 'false':
            return false;
        case 'empty':
            return '';
        case 'null':
            return null;
        default:
            return $value;
    }
}

/**
 * Помощник для получения значения параметра конфигурации.
 *
 * @param null|mixed $default
 */
function config(string $name = null, $default = null)
{
    /** @var Zend_Config $config */
    $config = Zend_Registry::get('config');

    if ($name === null) {
        return $config;
    }

    $parts = explode('.', $name);
    foreach ($parts as $part) {
        $config = $config->get($part);
        if ($config === null) {
            return $default;
        }
    }

    return $config;
}
