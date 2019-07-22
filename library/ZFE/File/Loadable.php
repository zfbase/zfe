<?php
/**
 * Created by PhpStorm.
 * User: dezzpil
 * Date: 09.10.18
 * Time: 15:21
 */

interface Files
{
    const KEY_TO_ITEM = 'item_id';

    public function getManageableItem() : ZFE_File_Manageable;
}