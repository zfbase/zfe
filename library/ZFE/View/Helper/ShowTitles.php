<?php

/*
 * ZFE – платформа для построения редакторских интерфейсов.
 */

/**
 * Генератор списка значений поля коллекции записей.
 */
class ZFE_View_Helper_ShowTitles
{
    /**
     * Вывести список значений поля коллекции записей.
     *
     * @param array|Doctrine_Collection|Traversable $items
     * @param string                                $field       используемое поле для элементов
     * @param string                                $separator   разделитель
     * @param int                                   $maxElements максимальное число выводимых элементов
     * @param callback|string                       $linkMethod  генератор адреса ссылки
     *
     * @return string
     */
    public function showTitles($items, $field = null, $separator = ', ', $maxElements = 0, $linkMethod = null)
    {
        $i = 0;
        $arr = [];
        foreach ($items as $item) { /** @var Doctrine_Record $item */
            if ( ! $item) {
                continue;
            }
            if ($field && $item->contains($field)) {
                $title = $item->{$field};
            } elseif ($field && method_exists($item, $field)) {
                $title = $item->{$field}();
            } else {
                $title = $item->getTitle();
            }

            if (is_string($linkMethod)) {
                $arr[] = sprintf('<a href="%s">%s</a>', $item->{$linkMethod}(), $title);
            } elseif (is_callable($linkMethod)) {
                $arr[] = sprintf('<a href="%s">%s</a>', $linkMethod($item), $title);
            } else {
                $arr[] = $title;
            }

            if ($maxElements > 0 && ++$i === $maxElements) {
                $arr[] = '...';

                break;
            }
        }
        return implode($separator, $arr);
    }
}
