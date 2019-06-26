<?php
/**
 * Created by PhpStorm.
 * User: dezzpil
 * Date: 19.10.18
 * Time: 16:10
 */

class Helper_File_Icons
{
    public function getFor(string $ext)
    {
        $matrix = [
            // Microsoft Office
            'doc' =>  'fa fa-file-word-o',
            'docx' => 'fa fa-file-word-o',
            'xls' =>  'fa fa-file-excel-o',
            'xlsx' => 'fa fa-file-excel-o',
            'ppt' =>  'fa fa-file-powerpoint-o',
            'pptx' => 'fa fa-file-powerpoint-o',

            // Audio
            'mp3' =>  'fa fa-file-audio-o',
            'wma' =>  'fa fa-file-audio-o',

            // Video
            'avi' =>  'fa fa-file-video-o',
            'mp4' =>  'fa fa-file-video-o',
            'wmv' =>  'fa fa-file-video-o',

            // Image
            'bmp' =>  'fa fa-file-image-o',
            'gif' =>  'fa fa-file-image-o',
            'png' =>  'fa fa-file-image-o',
            'jpg' =>  'fa fa-file-image-o',
            'jpeg' => 'fa fa-file-image-o',
            'tif' =>  'fa fa-file-image-o',
            'tiff' => 'fa fa-file-image-o',
            'svg' =>  'fa fa-file-image-o',

            // Archive
            'zip' =>  'fa fa-file-archive-o',
            'rar' =>  'fa fa-file-archive-o',
            'gz'=>    'fa fa-file-archive-o',
            '7z'=>    'fa fa-file-archive-o',
            'dmg' =>  'fa fa-file-archive-o',

            // Other
            'pdf' =>  'fa fa-file-pdf-o',
        ];


        return isset($matrix[$ext])
            ? $matrix[$ext]
            : 'fa fa-file-o';
    }
}