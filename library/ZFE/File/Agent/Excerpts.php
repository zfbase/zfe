<?php
/**
 * Created by PhpStorm.
 * User: dezzpil
 * Date: 24.10.18
 * Time: 14:42
 */

class ZFE_File_Agent_Excerpts extends ZFE_File_Agent
{
    protected $excerpts;

    public function setExcerpts(string $string)
    {
        $this->excerpts = $string;
    }

    public function getExcerpts()
    {
        return $this->excerpts;
    }
}