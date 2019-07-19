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
    use ZFE_Model_Default_PersonTrait;

    const ROLE_ADMIN = 'admin';
    const ROLE_EDITOR = 'editor';

    /**
     * Роли пользователей.
     *
     * @var array
     */
    public static $roles = [
        self::ROLE_ADMIN  => 'Администратор',
        self::ROLE_EDITOR => 'Редактор',
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
