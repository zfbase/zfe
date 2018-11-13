<?php

/*
 * ZFE – платформа для построения редакторских интерфейсов.
 */

/**
 * Генераторы адресов модели.
 */
trait ZFE_Model_AbstractRecord_Urls
{
    /**
     * Получить адрес для создания новой записи.
     *
     * @return string
     */
    public static function getNewUrl()
    {
        return '/' . static::getControllerName() . '/edit';
    }

    /**
     * Получить адрес на index action.
     *
     * @return string
     */
    public static function getIndexUrl()
    {
        return '/' . static::getControllerName() . '/index';
    }

    /**
     * Получить адрес на список записей.
     *
     * @return string
     */
    public static function getListUrl()
    {
        return static::getIndexUrl();
    }

    /**
     * Получить адрес на список записей по ID.
     *
     * @param array $ids
     */
    public static function getListIdsUrl(array $ids)
    {
        return static::getListUrl() . '?ids=' . implode(',', $ids);
    }

    /**
     * Получить адрес на редактирование записи.
     *
     * @return string
     */
    public function getEditUrl()
    {
        return '/' . static::getControllerName() . '/edit/id/' . $this->id;
    }

    /**
     * Получить адрес на просмотр записи.
     *
     * @return string
     */
    public function getViewUrl()
    {
        if ($this->isDeleted()) {
            return $this->getEditUrl();
        }

        return '/' . static::getControllerName() . '/view/id/' . $this->id;
    }

    /**
     * Получить основной адрес записи.
     * По умолчанию возвращает адрес на редактирование записи.
     * Для моделей со страницей просмотра стоит переопределять метод.
     *
     * @return string
     */
    public function getUrl()
    {
        return $this->getEditUrl();
    }

    /**
     * Получить адрес на удаление записи.
     *
     * @return string
     */
    public function getDeleteUrl()
    {
        return '/' . static::getControllerName() . '/delete/id/' . $this->id;
    }

    /**
     * Получить адрес на восстановление (из удаленных) записи.
     *
     * @return string
     */
    public function getUndeleteUrl()
    {
        return '/' . static::getControllerName() . '/undelete/id/' . $this->id;
    }

    /**
     * Получить адрес на историю изменений записи.
     *
     * @return string
     */
    public function getHistoryUrl()
    {
        return '/' . static::getControllerName() . '/history/id/' . $this->id;
    }

    /**
     * Получить адрес на сравнение версий записи.
     *
     * @param int $rightVersion Версия для сравнения справа
     * @param int $leftVersion  Версия для сравнения слева
     *
     * @return string
     */
    public function getHistoryDiffUrl($rightVersion = null, $leftVersion = null)
    {
        $rightVersion = (int) $rightVersion;
        $leftVersion = (int) $leftVersion;

        if ( ! $rightVersion) {  // По умолчанию, текущая версия
            $rightVersion = $this->version;
        }

        if ( ! $leftVersion) {  // По умолчанию, предыдущая перед правой
            $leftVersion = $rightVersion > 1
                ? $rightVersion - 1
                : 1;
        }

        return '/' . static::getControllerName() . '/diff'
             . '/id/' . $this->id
             . '/right/' . $rightVersion
             . '/left/' . $leftVersion;
    }

    /**
     * Получить адрес на откат записи к указанной версии.
     *
     * @param int $version
     *
     * @return string
     */
    public function getRestoreToVersion($version)
    {
        return '/' . static::getControllerName() . '/restore/id/' . $this->id . '/version/' . (int) $version;
    }

    /**
     * Получить адрес для данных автодополнения.
     *
     * @return string
     */
    public static function getAutocompleteUrl()
    {
        return '/' . static::getControllerName() . '/autocomplete';
    }

    /**
     * Получить адрес для системы объединения записей.
     *
     * @return string
     */
    public static function getMergingUrl()
    {
        return '/' . static::getControllerName() . '/merge';
    }

    /**
     * Получить адрес для поиска системы объединения записей.
     *
     * @return string
     */
    public static function getMergeSearchUrl()
    {
        return '/' . static::getControllerName() . '/merge-search';
    }

    /**
     * Получить адрес для поиска и объединения дубликатов.
     *
     * @return string
     */
    public static function getSearchDuplicatesUrl()
    {
        return '/' . static::getControllerName() . '/search-duplicates/';
    }

    /**
     * Получить адрес страницы выбора правильных полей при объединении.
     *
     * @return string
     */
    public static function getMergeHelperUrl()
    {
        return '/' . static::getControllerName() . '/merge-helper/';
    }

    /**
     * Получить адрес просмотра объединенных записей для создания текущей.
     *
     * @param int $hid
     *
     * @return string
     */
    public function getHistoryMergeUrl($hid)
    {
        return '/' . static::getControllerName() . '/history-merge/id/' . $this->id . '/hid/' . $hid;
    }

    /**
     * Получить адрес редактирования записи в модальном окне.
     *
     * @param null|int $id
     *
     * @return string
     */
    public static function getEditModalUrl($id = null)
    {
        return '/' . static::getControllerName() . '/edit-modal' . ($id ? "/id/{$id}" : '');
    }
}
