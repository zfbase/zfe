<?php
/**
 * Created by PhpStorm.
 * User: dezzpil
 * Date: 16.10.18
 * Time: 14:35
 */

class Helper_File_Accessor_Customers extends Helper_File_Accessor_Department
{
    /**
     * Пользователь из департамента, который владеет записью ?
     * @return bool
     */
    protected function isSameDepartment() : bool
    {
        $depId = $this->record->contains('department_id') ?? 0;
        return $depId == $this->user->department_id;
    }

}