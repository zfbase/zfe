<?php

/*
 * ZFE – платформа для построения редакторских интерфейсов.
 */

/**
 * Поддержка multiCheckbox и multiSelect.
 *
 * @category  ZFE
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
     *
     * @return array|bool
     */
    public static function getMultiCheckOrSelectOptions($field)
    {
        // Проверяем, является ли поле автокомплитом
        if ( ! array_key_exists($field, static::$multiCheckOrSelectCols)) {
            return false;
        }

        $options = static::$multiCheckOrSelectCols[$field];

        if (empty($options['relAlias'])) {
            $options['relAlias'] = ucfirst($field);
        }

        $alias = $options['relAlias'];
        $options['multiOptions'] = $alias::getKeyValueList();

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
