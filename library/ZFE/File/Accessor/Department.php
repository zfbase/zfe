<?php
/**
 * Created by PhpStorm.
 * User: dezzpil
 * Date: 16.10.18
 * Time: 14:35
 */

class Helper_File_Accessor_Department extends Helper_File_Accessor_Acl
{
    /**
     * Пользователь из департамента, который владеет записью ?
     * @return bool
     */
    protected function isSameDepartment() : bool
    {
        if (array_search($this->role, [Editors::ROLE_COHR, Editors::ROLE_CNTR]) !== false) {
            return $this->record->type == Interface_WorkType::OHR && Editors::isAllowedOHR()
                || $this->record->type == Interface_WorkType::NTR && Editors::isAllowedNTR();
        }

        $depId = $this->record->contains('department_id') ? $this->record->department_id : 0;
        return $depId == $this->user->department_id;
    }

    /**
     * Пользователь является ФЗ ?
     * @return bool
     */
    protected function isFunctional() : bool
    {
        return array_search($this->role, [Editors::ROLE_FUNC, Editors::ROLE_COHR, Editors::ROLE_CNTR]) !== false;
    }

    /**
     * @inheritdoc
     */
    function isAllowToList(): bool
    {
        if ($this->isFunctional()) {
            return $this->isSameDepartment() ;
        }
        return parent::isAllowToList();
    }

    /**
     * @inheritdoc
     */
    function isAllowToDelete(): bool
    {
        if ($this->isFunctional()) {
            return $this->isSameDepartment() ;
        }
        return parent::isAllowToDelete();
    }

    /**
     * @inheritdoc
     */
    function isAllowToDownload(): bool
    {
        if ($this->isFunctional()) {
            return $this->isSameDepartment() ;
        }
        return parent::isAllowToDownload();
    }

    /**
     * @inheritdoc
     */
    function isAllowToDownloadAll(): bool
    {
        if ($this->isFunctional()) {
            return $this->isSameDepartment() ;
        }
        return parent::isAllowToDownloadAll();
    }

}