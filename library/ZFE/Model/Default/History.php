<?php

/*
 * ZFE – платформа для построения редакторских интерфейсов.
 */

/**
 * Стандартная модель истории.
 */
abstract class ZFE_Model_Default_History extends BaseHistory
{
    /** {@inheritdoc} */
    public static $sex = self::SEX_FEMALE;

    /** {@inheritdoc} */
    public static $namePlural = 'История';

    /** {@inheritdoc} */
    public static $defaultOrder = 'datetime_action DESC';

    /** {@inheritdoc} */
    public static $defaultOrderKey = 'datetime_action_desc';

    /** {@inheritdoc} */
    public static $saveHistory = false;

    // Типы событий истории
    const ACTION_TYPE_INSERT   = '0';
    const ACTION_TYPE_UPDATE   = '1';
    const ACTION_TYPE_LINK     = '2';
    const ACTION_TYPE_DELETE   = '3';
    const ACTION_TYPE_UNLINK   = '4';
    const ACTION_TYPE_UNDELETE = '5';
    const ACTION_TYPE_RESTORE  = '6';
    const ACTION_TYPE_MERGE    = '7';

    /**
     * Типы событий истории.
     *
     * @var array
     */
    public static $actionTypes = [
        self::ACTION_TYPE_INSERT   => 'Создание',
        self::ACTION_TYPE_UPDATE   => 'Изменение',
        self::ACTION_TYPE_LINK     => 'Привязка',
        self::ACTION_TYPE_UNLINK   => 'Отвязка',
        self::ACTION_TYPE_DELETE   => 'Удаление',
        self::ACTION_TYPE_UNDELETE => 'Восстановление',
        self::ACTION_TYPE_RESTORE  => 'Откат изменений',
        self::ACTION_TYPE_MERGE    => 'Объединение',
    ];

    /**
     * Глобальное свойство записи истории.
     *
     * Свойство предназначено для временной остановки любых записей истории.
     * Убедительная рекомендация использовать только в случае крайней необходимости
     * и при тщательном тестировании.
     * Использование свойства может повредить целостность данных!
     *
     * @var bool
     */
    public static $globalRealtimeWhiteHistory = true;

    /**
     * Получить список версий для определенного объекта.
     *
     * @param ZFE_Model_AbstractRecord $item
     *
     * @return array
     */
    public static function getVersionsListFor(ZFE_Model_AbstractRecord $item)
    {
        $q = ZFE_Query::create()
            ->select('x.*, e.*')
            ->from('History x, x.Editors e')
            ->addWhere('x.table_name = ?', $item->getTableName())
            ->addWhere('x.content_id = ?', $item->id)
            ->groupBy('x.user_id, x.content_version, x.action_type')
            ->orderBy('x.datetime_action ASC, x.content_version ASC')
        ;
        $milestones = [];
        $rows = $q->execute();
        foreach ($rows as $row) {
            $milestones[] = [
                'version' => $row->content_version,
                'datetime' => $row->datetime_action,
                'action' => $row->action_type,
                'user' => $row->Editors,
            ];
        }
        return $milestones;
    }

    /**
     * Получить ссылку на сравнение версий определенной записи.
     *
     * @return string
     */
    public function getDiffUrl()
    {
        $rightVersion = $this->content_version;
        $leftVersion = $rightVersion > 1
            ? $rightVersion - 1
            : 1;

        return
            '/history/diff' .
            '/resource/' . $this->table_name .
            '/id/' . $this->content_id .
            '/left/' . $leftVersion .
            '/right/' . $rightVersion;
    }
}
