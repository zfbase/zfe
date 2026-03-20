<?php

/*
 * ZFE – платформа для построения редакторских интерфейсов.
 */

/**
 * Адаптер авторизации для Doctrine.
 *
 */
class ZFE_Auth_Adapter_Doctrine2026 implements Zend_Auth_Adapter_Interface
{

    private ?Doctrine_Record $row = null;

    public function __construct(
        private Doctrine_Connection $conn,
        private string $tableName,
        private string $identityColumn,
        private string $credentialColumn,
        private ?string $treatmentColumn,
        private string $identity,
        private string $credential,
    ) {}

    private function hashPassword(string $password): string
    {
        return password_hash($password, PASSWORD_ARGON2ID);
    }

    private function hasNewPassword(Doctrine_Record $row): bool
    {
        return empty($this->treatmentColumn) || empty($row->{$this->treatmentColumn});
    }

    private function checkOldPassword(Doctrine_Record $row): bool
    {
        if ($this->hasNewPassword($row)) {
            return false;
        }
        return md5($this->credential . $row->{$this->treatmentColumn}) === $row->{$this->credentialColumn};
    }

    private function updatePassword(Doctrine_Record $row): void
    {

        $row->{$this->credentialColumn} = $this->hashPassword($this->credential);
        if ($this->treatmentColumn) {
            $row->{$this->treatmentColumn} = null;
        }
        $row->save();
    }

    private function checkNewPassword(Doctrine_Record $row): bool
    {
        return password_verify($this->credential, $row->{$this->credentialColumn} ?? '');
    }

    private function checkUpgradePassword(Doctrine_Record $row): bool
    {
        if ($this->hasNewPassword($row)) {
            return $this->checkNewPassword($row);
        }

        $valid = $this->checkOldPassword($row);

        if ($valid) {
            $this->updatePassword($row);
        }

        return $valid;
    }

    public function authenticate(): Zend_Auth_Result
    {
        $this->row = null;

        $row = Doctrine_Query::create($this->conn)
            ->from($this->tableName)
            ->andWhere($this->identityColumn . ' = ?', $this->identity)
            ->andWhere('status = 0 AND deleted = 0')
            ->limit(1)
            ->fetchOne();

        $valid = $row ? $this->checkUpgradePassword($row) : false;

        if (!$valid) {
            return new Zend_Auth_Result(Zend_Auth_Result::FAILURE_CREDENTIAL_INVALID, $this->identity);
        }

        $row->{$this->credentialColumn} = null;
        if ($this->treatmentColumn) {
            $row->{$this->treatmentColumn} = null;
        }
        $this->row = $row;

        return new Zend_Auth_Result(Zend_Auth_Result::SUCCESS, $this->identity);
    }

    public function getResultRowObject(): Doctrine_Record
    {
        if (!$this->row) {
            throw new Exception('Result row object is empty');
        }
        return $this->row;
    }
}
