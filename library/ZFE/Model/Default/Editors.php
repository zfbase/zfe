<?php

/*
 * ZFE – платформа для построения редакторских интерфейсов.
 */

/**
 * Стандартная модель редакторов.
 */
abstract class ZFE_Model_Default_Editors extends BaseEditors
{
    /** {@inheritdoc} */
    public static $sex = self::SEX_MALE;

    /** {@inheritdoc} */
    public static $nameSingular = 'Редактор';

    /** {@inheritdoc} */
    public static $namePlural = 'Редакторы';

    /** {@inheritdoc} */
    public static $titleField = "CONCAT_WS(' ', x.second_name, x.first_name, x.middle_name)";

    /** {@inheritdoc} */
    public static $defaultOrder = "CONCAT_WS(' ', x.second_name, x.first_name, x.middle_name) ASC";

    /** {@inheritdoc} */
    public static $defaultOrderKey = 'title_asc';

    /** {@inheritdoc} */
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

    /** {@inheritdoc} */
    protected static $_dictionaryFields = [
        'status' => ['status', 'sex'],
        'role' => ['roles'],
    ];

    // Статусы
    const STATUS_ENABLE   = '0';
    const STATUS_DISABLED = '1';

    /** {@inheritdoc} */
    public static $status = [
        self::SEX_MALE => [
            self::STATUS_ENABLE   => 'Включен',
            self::STATUS_DISABLED => 'Отключен',
        ],
    ];

    /** {@inheritdoc} */
    public static $statusColor = [
        self::STATUS_ENABLE   => 'green',
        self::STATUS_DISABLED => 'red',
    ];

    /** {@inheritdoc} */
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

    /** {@inheritdoc} */
    public function fromArray(array $array, $deep = true)
    {
        if (!empty($array['password'])) {
            $salt = $array['password_salt'] ?? null;
            $this->setPassword($array['password'], $salt);
        }
        unset($array['password'], $array['password_salt']);
        parent::fromArray($array, $deep);
    }

    /** {@inheritdoc} */
    public function toArray($deep = true, $prefixKey = false)
    {
        $array = parent::toArray($deep, $prefixKey);
        unset($array['password'], $array['password_salt']);
        return $array;
    }

    /**
     * Установить новый пароль.
     *
     * @param string $password Пароль в открытом виде
     * @param string $salt     Соль (если не указана, будет создана автоматически)
     */
    public function setPassword($password, $salt = null)
    {
        $salt = $salt ?: $this->password_salt ?: uniqid();
        $pwdEscape = Doctrine_Manager::connection()->quote($password);
        $pwdExpStr = str_replace('?', $pwdEscape, Editors::$credentialTreatment);
        $pwdExpStr = str_replace('password_salt', "'{$salt}'", $pwdExpStr);
        $this->password = new Doctrine_Expression($pwdExpStr);
        $this->password_salt = $salt;
    }

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

        if (!empty($this->email)) {
            $name .= ' (' . $this->email . ')';
        } elseif (!empty($this->phone)) {
            $name .= ' (' . $this->phone . ')';
        }

        return $name;
    }

    /** {@inheritdoc} */
    protected static function _initServiceFields()
    {
        parent::_initServiceFields();
        static::_addServiceFields([
            'password_salt',
        ]);
    }

    /** {@inheritdoc} */
    protected static function _initHistoryHiddenFields()
    {
        parent::_initHistoryHiddenFields();
        static::_addHistoryHiddenFields([
            'password',
        ]);
    }
}
