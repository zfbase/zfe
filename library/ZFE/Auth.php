<?php

class ZFE_Auth
{
    /**
     * Дополнительное условие проверки учетных данных.
     *
     * Ограничение по delete не обязательно, т.к. Doctrine и так добавляет его во все запросы,
     * но учитывая, что это дополнение запросов может отключаться, лучше его указывать явно
     *
     * @var string
     */
    protected static $credentialTreatmentAdditional = 'AND status = 0 AND deleted = 0';

    /**
     * Получить настроенный адаптер авторизации.
     *
     * @param array $data
     *
     * @return Zend_Auth_Adapter_Interface
     */
    public static function getAdapter($data)
    {
        $tableConn = Doctrine_Core::getConnectionByTableName('editors');

        if (Editors::$useDoctrine2026Adapter) {
            return  new ZFE_Auth_Adapter_Doctrine2026(
                $tableConn,
                'editors',
                Editors::$identityColumn,
                'password',
                'password_salt',
                $data['login'],
                $data['password'],
            );
        }

        $authAdapter = new ZFE_Auth_Adapter_Doctrine($tableConn);
        $authAdapter->setTableName('editors')
            ->setIdentityColumn(Editors::$identityColumn)
            ->setCredentialColumn('password')
            ->setCredentialTreatment(Editors::$credentialTreatment . ' ' . self::$credentialTreatmentAdditional)
            ->setIdentity($data['login'])
            ->setCredential($data['password'])
        ;

        return $authAdapter;
    }

    public static function authenticate(string $login, string $password): bool
    {
        $authAdapter = self::getAdapter(
            [
                'login' => $login,
                'password' => $password
            ]
        );
        $result = $authAdapter->authenticate();
        return $result->isValid();
    }
}
