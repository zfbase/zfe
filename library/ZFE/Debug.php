<?php

/*
 * ZFE – платформа для построения редакторских интерфейсов.
 */

/**
 * Расширение Zend_Debug для сворачивая вложенных уровней кода.
 */
class ZFE_Debug extends Zend_Debug
{
    /**
     * Форматирует и оборачивает результат работы var_dump().
     *
     * @param mixed  $var   Переменная для дампа
     * @param string $label Название переменной
     * @param bool   $echo  Вывести на экран вместо возвращения?
     *
     * @return string
     */
    public static function dump($var, $label = null, $echo = true)
    {
        $output = parent::dump($var, $label, false);

        if ('cli' !== self::getSapi() && ! extension_loaded('xdebug')) {
            $output = preg_replace('/^<pre>/', '<pre class="zfe-dump"><code>', $output);
            $output = preg_replace('/<\/pre>$/', '</code></pre>', $output);
        }

        if ($echo) {
            echo $output;
        }

        return $output;
    }

    /**
     * Форматирует и оборачивает SQL-запрос
     *
     * @param string|ZFE_Query $sql  Строка запроса
     * @param bool             $echo Вывести на экран вместо возвращения?
     *
     * @return string
     */
    public static function sql($sql, $echo = true)
    {
        if ($sql instanceof ZFE_Query) {
            $sql = $sql->getSqlQuery();
        }

        $output = SqlFormatter::format($sql);

        if ($echo) {
            echo $output;
        }

        return $output;
    }
}
