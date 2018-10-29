<?php

/*
 * ZFE – платформа для построения редакторских интерфейсов.
 */

error_reporting(E_ALL);
ini_set('display_errors', 'On');

require_once '../vendor/autoload.php';

date_default_timezone_set('Europe/Moscow');
mb_internal_encoding('UTF-8');

define('RESOURCES_PATH', realpath(__DIR__ . '/resources'));
