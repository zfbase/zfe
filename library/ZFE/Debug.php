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
        [$place] = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1);

        if ('cli' !== self::getSapi() && !extension_loaded('xdebug')) {
            $preOpen = '<pre class="zfe-dump" data-place="' . $place['file'] . ':' . $place['line'] . '">';

            $output = preg_replace('/^<pre>/', $preOpen . '<code>', $output);
            $output = preg_replace('/<\/pre>$/', '</code></pre>', $output);
        } else {
            $output = $place['file'] . ':' . $place['line'] . $output;
        }

        if ($echo) {
            echo $output;
        }

        return $output;
    }

    /**
     * Форматировать и отправить в консоль результат работы var_dump().
     *
     * @param mixed  $var   Переменная для дампа
     * @param string $label Название переменной
     */
    public static function console($var = null, $label = null)
    {
        $label = ($label === null) ? '' : rtrim($label) . PHP_EOL;

        if ($var !== null || $label !== '') {
            ob_start();
            var_dump($var);
            $output = ob_get_clean();
            $output = preg_replace("/\]\=\>\n(\s+)/m", '] => ', $output);
        } else {
            $output = '';
        }

        [$place] = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1);
        $callPoint = $place['file'] . ':' . $place['line'] . PHP_EOL;

        error_log($callPoint . $label . $output);
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

    /**
     * Помощник для распечатки текущей строчки.
     *
     * @param bool $echo Вывести на экран вместо возвращения?
     * @param bool $exit Завершить выполнение?
     *
     * @return string
     */
    public static function trace($echo = true, $exit = false)
    {
        [$place] = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1);

        if ('cli' === self::getSapi()) {
            $output = PHP_EOL . $place['file'] . ':' . $place['line'] . PHP_EOL;
        } else {
            $output = '<pre>' . $place['file'] . ':' . $place['line'] . '</pre>';
        }

        if ($echo) {
            echo $output;
        }

        if ($exit) {
            exit;
        }

        return $output;
    }

    /**
     * Помощник для лога вызовов.
     *
     * @param bool $echo распечатать?
     * @param bool $html Форматировать в HTML?
     *
     * @return string
     */
    public static function backtrace($echo = true, $html = true)
    {
        $list = [];
        $steps = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
        foreach ($steps as $step) {
            $list[] = sprintf('%s:%d', $step['file'], $step['line']);
        }

        if ('cli' === self::getSapi() || !$html) {
            $output = PHP_EOL;
            foreach ($steps as $i => $step) {
                $output .= sprintf('%d. %s:%d', $i, $step['file'], $step['line']) . PHP_EOL;
            }
        } else {
            $output = '<ol>';
            foreach ($steps as $step) {
                $output .= sprintf('<li>%s:%d</li>', $step['file'], $step['line']);
            }
        }

        if ($echo) {
            echo $output;
        }

        return $output;
    }
}
