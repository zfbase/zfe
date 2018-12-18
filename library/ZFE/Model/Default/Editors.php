<?php

/*
 * ZFE – платформа для построения редакторских интерфейсов.
 */

/**
 * Стандартная модель редакторов.
 */
abstract class ZFE_Model_Default_Editors extends BaseEditors
{
    // Переопределяем AbstractRecords
    /**
     * Половая принадлежность записи.
     *
     * @var int
     */
    public static $sex = self::SEX_MALE;

    /**
     * Название записи в единственном числе.
     *
     * @var string
     */
    public static $nameSingular = 'Редактор';

    /**
     * Название записи во множественном числе.
     *
     * @var string
     */
    public static $namePlural = 'Редакторы';

    /**
     * Поле (выражение) названия записи.
     *
     * @var string
     */
    public static $titleField = "CONCAT_WS(' ', x.second_name, x.first_name, x.middle_name)";

    /**
     * Сортировка записей по умолчанию.
     *
     * @var string
     */
    public static $defaultOrder = "CONCAT_WS(' ', x.second_name, x.first_name, x.middle_name) ASC";

    /**
     * Ключ сортировки по умолчанию.
     *
     * @var string
     */
    public static $defaultOrderKey = 'title_asc';

    /**
     * Имена полей записи модели.
     *
     * @var array
     */
    public static $nameFields = [
        'title'       => 'Полное имя',
        'second_name' => 'Фамилия',
        'first_name'  => 'Имя',
        'middle_name' => 'Отчество',
        'department'  => 'Подразделение',
        'phone'       => 'Телефон',
        'email'       => 'Эл. почта',
        'login'       => 'Логин',
        'password'    => 'Пароль',
        'role'        => 'Роль',
        'comment'     => 'Примечание',
        'request_password_change' => 'Запросить смену пароля при первом входе',
    ];

    /**
     * Словарные поля записи.
     *
     * @var array
     */
    protected static $_dictionaryFields = [
        'status' => ['status', 'sex'],
        'role' => ['roles'],
    ];

    // Статусы
    const STATUS_ENABLE   = '0';
    const STATUS_DISABLED = '1';

    /**
     * Статусы записей модели в зависимости от половой принадлежности записи.
     *
     * @var array
     */
    public static $status = [
        self::SEX_MALE => [
            self::STATUS_ENABLE   => 'Включен',
            self::STATUS_DISABLED => 'Отключен',
        ],
    ];

    /**
     * Цвета статусов.
     *
     * @var array
     */
    public static $statusColor = [
        self::STATUS_ENABLE   => 'green',
        self::STATUS_DISABLED => 'red',
    ];

    /**
     * Список полей, принимающихся только значения да/нет
     *
     * @var array|string[]
     */
    public static $booleanFields = [
        'request_password_change',
    ];

    /**
     * Использовать в качестве логина поле.
     *
     * @var string
     */
    public static $identityColumn = 'login';

    /**
     * Выражение для кодирования пароля.
     *
     * @var string
     */
    public static $credentialTreatment = 'MD5(CONCAT(?, password_salt))';

    /**
     * Получить название записи.
     *
     * @return string
     */
    public function getTitle()
    {
        if ($this->exists()) {
            return $this->getFullName();
        }

        return static::getNewTitle();
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
        if ( ! empty($array['password'])) {
            $salt = $array['password_salt'] ?? null;
            $this->setPassword($array['password'], $salt);
        }
        unset($array['password'], $array['password_salt']);
        parent::fromArray($array, $deep);
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
        unset($array['password'], $array['password_salt']);
        return $array;
    }

    public function setPassword($password, $salt = null)
    {
        $salt = $salt ?: $this->password_salt ?: uniqid();
        $pwdEscape = Doctrine_Manager::connection()->quote($password);
        $pwdExpStr = str_replace('?', $pwdEscape, Editors::$credentialTreatment);
        $pwdExpStr = str_replace('password_salt', "'{$salt}'", $pwdExpStr);
        $this->password = new Doctrine_Expression($pwdExpStr);
        $this->password_salt = $salt;
    }

    // Дополняем AbstractRecords

    /**
     * Роли пользователей.
     *
     * @var array
     */
    public static $roles = [
        'admin'  => 'Администратор',
        'editor' => 'Редактор',
    ];

    /**
     * Получить пользователя, но только если он может входить в систему.
     *
     * @param int $id
     *
     * @return Doctrine_Record
     */
    public static function findForAuth($id)
    {
        return ZFE_Query::create()
            ->select('e.*')
            ->from('Editors e')
            ->addWhere('e.id = ?', $id)
            ->addWhere('e.deleted = 0')
            ->addWhere('e.status = 0')
            ->fetchOne()
       ;
    }

    /**
     * Получить сокращенное имя пользователя.
     *
     * @return string
     */
    public function getShortName()
    {
        $name = $this->second_name . ' ';

        if ( ! empty($this->first_name)) {
            $name .= mb_substr($this->first_name, 0, 1) . '.';
        }

        if ( ! empty($this->middle_name)) {
            $name .= mb_substr($this->middle_name, 0, 1) . '.';
        }

        return trim($name);
    }

    /**
     * Получить полное имя пользователя.
     *
     * @return string
     */
    public function getFullName()
    {
        $name = $this->second_name;

        if ( ! empty($this->first_name)) {
            $name .= ' ' . $this->first_name;
        }

        if ( ! empty($this->middle_name)) {
            $name .= ' ' . $this->middle_name;
        }

        return $name;
    }

    /**
     * Получить полное имя пользователя с контактными данными.
     *
     * @return string
     */
    public function getNameWithContactInfo()
    {
        $name = $this->getFullName();

        if ( ! empty($this->email)) {
            $name .= ' (' . $this->email . ')';
        } elseif ( ! empty($this->phone)) {
            $name .= ' (' . $this->phone . ')';
        }

        return $name;
    }

    /**
     * Получить массив с заданными ключами и значениями из текущей таблицы.
     *
     * @param string       $keyField       поле для ключа
     * @param string       $valueField     поле для значения
     * @param array|string $where          фильтр: ['status = ? or status = ?', 1, 2];
     * @param string       $order          сортировка
     * @param string       $groupby        группирует списки по третьему полю (для формирования списка зависимого от другого, напр. список городов с группировкой по регионам)
     * @param null|bool    $filterByStatus
     *
     * @return array
     */
    public static function getKeyValueList($keyField = 'id', $valueField = "CONCAT_WS(' ', second_name, first_name, middle_name)", $where = null, $order = 'KEY_FIELD ASC', $groupby = null, $filterByStatus = null)
    {
        return parent::getKeyValueList($keyField, $valueField, $where, $order, $groupby);
    }

    /**
     * Инициализировать список по умолчанию служебных полей модели.
     */
    protected static function _initServiceFields()
    {
        parent::_initServiceFields();
        static::_addServiceFields([
            'password_salt',
        ]);
    }

    /**
     * Инициализировать список по умолчанию полей модели, для которых в истории скрываются значения.
     */
    protected static function _initHistoryHiddenFields()
    {
        parent::_initHistoryHiddenFields();
        static::_addHistoryHiddenFields([
            'password',
        ]);
    }
}
