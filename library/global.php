<?php

/**
 * Помощник для получения значения параметра окружения.
 */
function env(string $name, $default = null)
{
    $value = isset($_ENV[$name]) ? $_ENV[$name] : false;

    if ($value === false) {
        return $default;
    }
    
    switch (strtolower($value)) {
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
 */
function config(string $name, $default = null)
{
  $parts = explode('.', $name);
  $config = Zend_Registry::get('config');
  foreach ($parts as $part) {
    if ($config->isRegistered($part)) {
      $config = $config->$part;
    } else {
      return $default;
    }
  }
  return $config;
}
