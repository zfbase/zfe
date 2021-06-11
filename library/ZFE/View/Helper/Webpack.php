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
            $pathParts = array_diff([
                APPLICATION_PATH,
                '..',
                'public',
                config('manifestDir', 'build'),
                'manifest.json',
            ], [null, '']);
            $filename = implode(DIRECTORY_SEPARATOR, $pathParts);
            $contents = @file_get_contents($filename);
            self::$manifest = json_decode($contents, true) ?: [];
        }
        return self::$manifest;
    }

    /**
     * Получить путь до манифеста.
     *
     * @param string $filename
     */
    public function webpack($filename)
    {
        $manifest = self::getManifest();
        $directory = config('manifestDir', 'build');
        return '/' . (empty($directory) ? '' : $directory . '/') . ($manifest[$filename] ?? $filename);
    }
}
