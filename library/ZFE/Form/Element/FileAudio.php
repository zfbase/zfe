<?php

/*
 * ZFE – платформа для построения редакторских интерфейсов.
 */

class ZFE_Form_Element_FileAudio extends ZFE_Form_Element_File
{
    protected $_dataType = 'audio';
    protected $_allowExtensions = [
        '.wav',
        '.aif',
        '.mp3',
        '.mid',
        '.m4a',
        '.mpa',
        '.wma',
    ];
    protected $_allowMimeTypes = [
        'audio/mp3',
        'audio/mp4',
        'audio/aac',
        'audio/mpeg',
        'audio/x-ms-wma',
        'audio/webm',
    ];
}
