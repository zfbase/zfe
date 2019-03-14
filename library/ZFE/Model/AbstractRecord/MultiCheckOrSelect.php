<?php

/*
 * ZFE – платформа для построения редакторских интерфейсов.
 */

/**
 * Поддержка multiCheckbox и multiSelect.
 */
trait ZFE_Model_AbstractRecord_MultiCheckOrSelect
{
    /**
     * Поля для выбора нескольких вариантов из списка (multiCheckbox, multiSelect).
     *
     * @todo Реализовать обработку параметров для автогенераторов форм
     *
     * @example <code>
     * public static $multiCheckOrSelectCols = [
     *     'categories' => [                      // имя элемента формы
     *         'label' => 'Категории',            // название (подпись) элемента
     *         'relAlias' => 'Categories',        // псевдоним связи
     *         'formElement' => 'multiCheckbox',  // тип элемента формы
     *     ],
     * ];
     * </code>
     *
     * @var array
     */
    public static $multiCheckOrSelectCols = [];

    /**
     * Получить настройки поля выбора нескольких записей другой таблицы из списка.
     *
     * @param string $field
     * @param array  $multiOptions опционально заданный список значений списка, по умолчанию - $alias::getKeyValueList()
     *
     * @return array|bool
     */
    public static function getMultiCheckOrSelectOptions($field, $multiOptions = [])
    {
        // Проверяем, является ли поле автокомплитом
        if ( ! key_exists($field, static::$multiCheckOrSelectCols)) {
            return false;
        }

        $options = static::$multiCheckOrSelectCols[$field];

        if (empty($options['relAlias'])) {
            $options['relAlias'] = ucfirst($field);
        }

        $alias = $options['relAlias'];

        // не будем пересобирать варианты, если они уже собраны
        if ( ! empty($multiOptions)) {
            $options['multiOptions'] = $multiOptions;
        } else {
            $options['multiOptions'] = $alias::getKeyValueList();
        }

        return $options;
    }

    protected function _multiCheckOrSelectToArray(array $array)
    {
        foreach (static::$multiCheckOrSelectCols as $key => $options) {
            $alias = $options['relAlias'];
            $array[$key] = [];
            foreach ($this->{$alias} as $item) {
                $array[$key][] = $item->id;
            }
        }

        return $array;
    }

    protected function _multiCheckOrSelectFromArray(array $array)
    {
        foreach (static::$multiCheckOrSelectCols as $key => $options) {
            if ( ! isset($array[$key])) {
                $array[$key] = [];
            }

            $alias = $options['relAlias'];

            $ids = [];
            foreach ($array[$key] as $id) {
                $ids[] = $id;
            }

            $this->_linkIds($alias, $ids);

            unset($array[$key]);
        }

        return $array;
    }
}
