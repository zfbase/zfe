<?php

/*
 * ZFE – платформа для построения редакторских интерфейсов.
 */

/**
 * Утилиты.
 */
class ZFE_Utilites
{
    /**
     * Укоротить текст до определенного размера.
     *
     * @param string $text    исходный текст
     * @param string $max_len максимальная длина
     *
     * @return string сокращенный текст
     */
    public static function shortenText($text, $max_len = 100)
    {
        return \HtmlTruncator\Truncator::truncate($text, $max_len, ['length_in_chars' => true]);
    }

    /**
     * Trim characters from either (or both) ends of a string in a way that is
     * multibyte-friendly.
     *
     * Mostly, this behaves exactly like trim() would: for example supplying 'abc' as
     * the charlist will trim all 'a', 'b' and 'c' chars from the string, with, of
     * course, the added bonus that you can put unicode characters in the charlist.
     *
     * We are using a PCRE character-class to do the trimming in a unicode-aware
     * way, so we must escape ^, \, - and ] which have special meanings here.
     * As you would expect, a single \ in the charlist is interpretted as
     * "trim backslashes" (and duly escaped into a double-\ ). Under most circumstances
     * you can ignore this detail.
     *
     * As a bonus, however, we also allow PCRE special character-classes (such as '\s')
     * because they can be extremely useful when dealing with UCS. '\pZ', for example,
     * matches every 'separator' character defined in Unicode, including non-breaking
     * and zero-width spaces.
     *
     * It doesn't make sense to have two or more of the same character in a character
     * class, therefore we interpret a double \ in the character list to mean a
     * single \ in the regex, allowing you to safely mix normal characters with PCRE
     * special classes.
     *
     * *Be careful* when using this bonus feature, as PHP also interprets backslashes
     * as escape characters before they are even seen by the regex. Therefore, to
     * specify '\\s' in the regex (which will be converted to the special character
     * class '\s' for trimming), you will usually have to put *4* backslashes in the
     * PHP code - as you can see from the default value of $charlist.
     *
     * @param string $string
     * @param string $charlist charlist list of characters to remove from the ends of this string
     * @param bool   $ltrim    trim the left?
     * @param bool   $rtrim    trim the right?
     *
     * @return string
     */
    public static function mb_trim($string, $charlist = '\\\\s', $ltrim = true, $rtrim = true)
    {
        $both_ends = $ltrim && $rtrim;

        $char_class_inner = preg_replace(
            ['/[\^\-\]\\\]/S', '/\\\{4}/S'],
            ['\\\\\\0', '\\'],
            $charlist
        );

        $work_horse = '[' . $char_class_inner . ']+';
        $ltrim && $left_pattern = '^' . $work_horse;
        $rtrim && $right_pattern = $work_horse . '$';

        if ($both_ends) {
            $pattern_middle = $left_pattern . '|' . $right_pattern;
        } elseif ($ltrim) {
            $pattern_middle = $left_pattern;
        } else {
            $pattern_middle = $right_pattern;
        }

        return preg_replace("/${pattern_middle}/usSD", '', $string);
    }

    /**
     * Преобразует первый символ строки в верхний регистр с поддержкой UTF-8.
     *
     * @param string $string
     * @param string $encoding
     *
     * @return string
     */
    public static function mb_ucfirst($string, $encoding = 'UTF-8')
    {
        return mb_strtoupper(mb_substr($string, 0, 1, $encoding), $encoding)
             . mb_substr($string, 1, mb_strlen($string), $encoding);
    }

    /**
     * Загрузить короткий псевдоним класса.
     */
    public static function loadShortAlias()
    {
        include_once realpath(__DIR__ . '/../ZFE.php');
    }

    /**
     * Отформатировать размер
     *
     * @param int $bytes
     * @param int $precision
     *
     * @return string
     */
    public static function formatSize($bytes, $precision = 2)
    {
        $labels = ['К', 'М', 'Г', 'Т', 'П', 'Э', 'З', 'И'];
        for ($i = count($label); $i >= 1; --$i) {
            if (bccomp($bytes, bcpow(1024, $i)) >= 0) {
                return bcdiv($bytes, bcpow(1024, $i), $precision) . ' ' . $labels[$i - 1] . 'Б';
            }
        }

        return $bytes . ' Б';
    }

    /**
     * Помощник для склонения существительных.
     *
     * @param int            $n
     * @param array|string[] $forms
     *
     * @return string
     */
    public static function plural($n, array $forms)
    {
        return 1 === $n % 10 && 11 !== $n % 100 ? $forms[0] : ($n % 10 >= 2 && $n % 10 <= 4 && ($n % 100 < 10 || $n % 100 >= 20) ? $forms[1] : $forms[2]);
    }

    /**
     * Разбить словосочетание в венгерской нотации на отдельные слова.
     *
     * @see https://stackoverflow.com/questions/4519739/split-camelcase-word-into-words-with-php-preg-match-regular-expression#7729790
     *
     * @param string $string
     *
     * @return array|string[]
     */
    public static function splitCamelCase($string)
    {
        $pattern = '/(?#! splitCamelCase Rev:20140412)
            # Split camelCase "words". Two global alternatives. Either g1of2:
              (?<=[a-z])      # Position is after a lowercase,
              (?=[A-Z])       # and before an uppercase letter.
            | (?<=[A-Z])      # Or g2of2; Position is after uppercase,
              (?=[A-Z][a-z])  # and before upper-then-lower case.
            /x';
        return preg_split($pattern, $string);
    }

    /**
     * Преобразовать число секунд во время (чч:мм:сс).
     *
     * @param int $seconds
     *
     * @return string
     */
    public static function secToTime($seconds)
    {
        if (null === $seconds) {
            return null;
        }

        return sprintf('%02d', floor($seconds / 3600)) . gmdate(':i:s', $seconds);
    }

    /**
     * Преобразовать время (чч:мм:сс) в число секунд.
     *
     * @param string $time
     *
     * @return int
     */
    public static function timeToSec($time)
    {
        if (null === $time || '' === $time) {
            return null;
        }

        $parts = explode(':', $time);
        $seconds = 0;
        $multiplier = 1;
        while (count($parts) > 0) {
            $seconds += (int) array_pop($parts) * $multiplier;
            $multiplier *= 60;
        }

        return $seconds;
    }

    /**
     * Сформировать аббревиатуру из текста.
     *
     * @param string $text
     *
     * @return string
     */
    public static function makeAbbr(string $text)
    {
        if (preg_match_all('/\b(\w)/u', mb_strtoupper($text), $m)) {
            return implode('', $m[1]);
        } else {
            return $text;
        }
    }
}
