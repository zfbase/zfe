<?php

/*
 * ZFE – платформа для построения редакторских интерфейсов.
 */

class ZFE_Form_Element_FileImage extends ZFE_Form_Element_File
{
    protected $_dataType = 'image';
    protected $_allowExtensions = [
        '.jpg',
        '.jpeg',
        '.tiff',
        '.png',
        '.gif',
        '.bmp',
        '.psd',
    ];
    protected $_allowMimeTypes = [
        'image/jpeg',
        'image/tiff',
        'image/png',
        'image/gif',
    ];
}
