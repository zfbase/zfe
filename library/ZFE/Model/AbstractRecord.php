<?php

/*
 * ZFE – платформа для построения редакторских интерфейсов.
 */

/**
 * Базовая модель ресурса.
 *
 * Определяет основные и наиболее часто используемые операции над записями.
 *
 * @category  ZFE
 */
abstract class ZFE_Model_AbstractRecord extends Doctrine_Record
{
    // Подключаем сгрупированные по типам методы базовой модели
    use ZFE_Model_AbstractRecord_Urls;                 // Геттеры адресов модели
    use ZFE_Model_AbstractRecord_Files;                // Средства связи записей с их файлами
    use ZFE_Model_AbstractRecord_Getters;              // Геттеры данных модели и записи
    use ZFE_Model_AbstractRecord_HotSelects;           // Статические методы для наиболее частых выборок
    use ZFE_Model_AbstractRecord_Autocomplete;         // Средства автодополнения
    use ZFE_Model_AbstractRecord_MultiCheckOrSelect;   // Поддержка multiCheckbox и multiSelect
    use ZFE_Model_AbstractRecord_ServiceFields;        // Служебные поля модели
    use ZFE_Model_AbstractRecord_HistoryHiddenFields;  // Поля модели, для которых в истории скрываются значения
    use ZFE_Model_AbstractRecord_Duplicates;           // Поиск и объединение дубликатов
    use ZFE_Model_AbstractRecord_Sphinx;               // Функционал для Sphinx
    use ZFE_Model_Decline;                             // Склонения сообщений

    // Пол записи (допустимые варианты)
    const SEX_MALE = 1;
    const SEX_FEMALE = 2;
    const SEX_NEUTER = 3;

    /**
     * Названия новой записи в зависимости от половой пренадлежности записи.
     *
     * @var array
     */
    protected static $_newTitle = [
        self::SEX_MALE =>   'Новый',
        self::SEX_FEMALE => 'Новая',
        self::SEX_NEUTER => 'Новое',
    ];

    /**
     * Половая пренадлежность записи.
     *
     * @var int
     */
    public static $sex = self::SEX_MALE;

    /**
     * Название записи в единственном числе.
     *
     * @var string
     */
    public static $nameSingular = 'Запись';

    /**
     * Название записи во множественном числе.
     *
     * @var string
     */
    public static $namePlural = 'Записи';

    /**
     * Сохранять историю.
     *
     * @var bool
     */
    public static $saveHistory = true;

    /**
     * Имена полей записи модели.
     *
     * Дополняет self::$_nameBaseFields.
     *
     * @var array
     */
    public static $nameFields = [];

    /**
     * Поле (выражение) названия записи.
     *
     * @var string
     */
    public static $titleField = 'x.title';

    /**
     * Сортировка записей по умолчанию.
     *
     * @var string
     */
    public static $defaultOrder = 'x.datetime_edited DESC';

    /**
     * Ключ сортировки по умолчанию.
     *
     * @var string
     */
    public static $defaultOrderKey = 'datetime_edited_desc';

    /**
     * Имя контроллера управляющего записями модели.
     *
     * @var string
     */
    public static $controller;

    /**
     * Записи могут быть объеденены стандартным способом?
     *
     * @var bool
     */
    public static $mergeable = false;

    // Статусы записей модели
    const STATUS_PUBLISHED = 0;
    const STATUS_NOT_PUBLISHED = 1;
    const STATUS_READY_TO_PUBLISH = 2;
    const STATUS_UNPUBLISHED = 3;

    /**
     * Список полей, принимающихся только значения да/нет
     *
     * @var array|string[]
     */
    public static $booleanFields = [];

    /**
     * Статусы записей модели в зависимости от половой принадлежности записи.
     *
     * @var array
     */
    public static $status = [
        self::SEX_MALE => [
            self::STATUS_PUBLISHED => 'Опубликован',
            self::STATUS_NOT_PUBLISHED => 'Не опубликован',
            self::STATUS_READY_TO_PUBLISH => 'Готов к публикации',
            self::STATUS_UNPUBLISHED => 'Снят с публикации',
        ],
        self::SEX_FEMALE => [
            self::STATUS_PUBLISHED => 'Опубликована',
            self::STATUS_NOT_PUBLISHED => 'Не опубликована',
            self::STATUS_READY_TO_PUBLISH => 'Готова к публикации',
            self::STATUS_UNPUBLISHED => 'Снята с публикации',
        ],
        self::SEX_NEUTER => [
            self::STATUS_PUBLISHED => 'Опубликовано',
            self::STATUS_NOT_PUBLISHED => 'Не опубликовано',
            self::STATUS_READY_TO_PUBLISH => 'Готово к публикации',
            self::STATUS_UNPUBLISHED => 'Снято с публикации',
        ],
    ];

    /**
     * Цвета статусов.
     *
     * @var array
     */
    public static $statusColor = [
        self::STATUS_PUBLISHED => 'green',
        self::STATUS_NOT_PUBLISHED => 'red',
        self::STATUS_READY_TO_PUBLISH => 'orange',
        self::STATUS_UNPUBLISHED => 'grey',
    ];

    /**
     * Словарные поля записи.
     *
     * Ключ – словарное поле. Значение – словарь.
     * Каждый элемент массива значения указывает на словарь измерения словаря.
     * Для работы со словарными полями используйте функции:
     * # static::isDictionaryField($field)
     * # static::getDictionaryField($field, $value)
     *
     * @var array
     */
    protected static $_dictionaryFields = [
        'status' => ['status', 'sex'],
    ];

    /**
     * Имена общих полей записей модели.
     *
     * @var array
     */
    protected static $_nameBaseFields = [
        'id'           => 'ID',
        'title'        => 'Название',
        'status'       => 'Статус',
        'priority'     => 'Приоритет',
        'comment'      => 'Комментарий',
        'body'         => 'Текст',
        'datetime_rec' => 'Дата публикации',
    ];

    /**
     * Исключать из результатов автокомплитов и getKeyValueList
     * записи со статусом отличным от нуля?
     *
     * @var bool
     */
    protected static $_excludeByStatus = true;

    /**
     * Привести к строке.
     *
     * @return string
     */
    public function __toString()
    {
        return (string) $this->getTitle();
    }

    /**
     * Экспортировать данные в массив.
     *
     * @param bool $deep
     * @param bool $prefixKey
     *
     * @return array
     */
    public function toArray($deep = true, $prefixKey = false)
    {
        $array = parent::toArray($deep, $prefixKey);

        if ( ! is_array($array)) {
            return $array;
        }

        if (is_array($array)) {
            foreach ($array as $key => $value) {
                if ('date' === $key || 'date_' === substr($key, 0, 5)) {
                    if (empty($value) || '0000-00-00' === $value) {
                        $array[$key] = '';
                    }
                    continue;
                }

                if ('datetime' === $key || 'datetime_' === substr($key, 0, 9)) {
                    if (empty($value) || '0000-00-00 00:00:00' === $value) {
                        $array[$key] = '';
                    }
                    continue;
                }

                if ('time' === $key || 'time_' === substr($key, 0, 5)) {
                    if (empty($value) || '00:00:00' === $value) {
                        $array[$key] = '';
                    }
                    continue;
                }

                if ('month' === $key || 'month_' === substr($key, 0, 6)) {
                    if (empty($value) || '0000-00-00' === $value) {
                        $array[$key] = '';
                    } else {
                        $array[$key] = substr($value, 0, 7);
                    }
                    continue;
                }

                if (null === $array[$key]) {
                    $array[$key] = '';
                    continue;
                }
            }
        }

        $array = $this->_autocompleteToArray($array);
        $array = $this->_multiAutocompleteToArray($array);
        $array = $this->_multiCheckOrSelectToArray($array);
        return $array;
    }

    /**
     * Импортировать данные из массива.
     *
     * @see http://www.doctrine-project.org/documentation/manual/1_1/en/working-with-models
     *
     * @param array $array
     * @param bool  $deep
     */
    public function fromArray(array $array, $deep = true)
    {
        foreach ($array as $key => $value) {
            if ('month' === $key || 'month_' === substr($key, 0, 6)) {
                if ( ! empty($array[$key]) && preg_match('/[0-9]{4}\-[0-1][0-9]/', $array[$key])) {
                    $array[$key] .= '-01';
                } else {
                    $array[$key] = null;
                }
                continue;
            }

            if ('' === $value) {
                $array[$key] = null;
                continue;
            }
        }

        $array = $this->_autocompleteFromArray($array);
        $array = $this->_multiAutocompleteFromArray($array);
        $array = $this->_multiCheckOrSelectFromArray($array);
        parent::fromArray($array, $deep);
    }

    /**
     * Записи могут быть удалены в корзину?
     *
     * @return bool
     */
    public static function isRemovable()
    {
        return Doctrine_Core::getTable(get_called_class())->hasField('deleted');
    }

    /**
     * Записи могут быть объеденены стандартным способом?
     *
     * @return bool
     */
    public static function isMergeable()
    {
        return static::$mergeable;
    }

    /**
     * Запись удалена?
     *
     * @return bool
     */
    public function isDeleted()
    {
        if ( ! $this->contains('deleted')) {
            return false;
        }

        return 1 === $this->deleted;
    }

    /**
     * Записи модели можно восстановливать после удаления?
     *
     * @return bool
     */
    public function canUndeleted()
    {
        $config = Zend_Registry::get('config');

        return $config->saveHistory && static::$saveHistory &&
            $this->_table->hasColumn('deleted');
    }

    /**
     * setUp.
     */
    public function setUp()
    {
        parent::setUp();

        $config = Zend_Registry::get('config');

        if ($config->saveHistory && static::$saveHistory) {
            $this->actAs(new ZFE_Model_Template_History());
        }
    }
}
