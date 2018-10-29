<?php

/*
 * ZFE – платформа для построения редакторских интерфейсов.
 */

class ZFE_View_Helper_Webpack
{
    protected static $manifest = null;

    protected static function getManifest()
    {
        if (null === self::$manifest) {
            $filename = implode(DIRECTORY_SEPARATOR, [
                APPLICATION_PATH,
                '..',
                'public',
                'build',
                'manifest.json',
            ]);
            $contents = @file_get_contents($filename);
            self::$manifest = json_decode($contents, true) ?: [];
        }
        return self::$manifest;
    }

    public function webpack($filename)
    {
        $manifest = self::getManifest();
        return '/build/' . (isset($manifest[$filename]) ? $manifest[$filename] : $filename);
    }
}
