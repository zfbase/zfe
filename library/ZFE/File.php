<?php

/*
 * ZFE – платформа для построения редакторских интерфейсов.
 */

/**
 * Библиотека по работе с файлами.
 */
class ZFE_File
{
    /**
     * Имя файла.
     *
     * @var string
     */
    protected $_name;

    /**
     * Указать имя файла.
     *
     * @param string $name
     *
     * @return ZFE_File
     */
    public function setName($name)
    {
        $this->_name = $name;
        return $this;
    }

    /**
     * Получить имя файла.
     *
     * @return string
     */
    public function getName()
    {
        return $this->_name;
    }

    /**
     * Размер файла в байтах.
     *
     * @var int
     */
    protected $_size;

    /**
     * Указать размер файла.
     *
     * @param int $size
     *
     * @return ZFE_File
     */
    public function setSize($size)
    {
        $this->_size = $size;
        return $this;
    }

    /**
     * Получить размер файла.
     *
     * @return int
     */
    public function getSize()
    {
        return $this->_size;
    }

    /**
     * Контрольная сумма файла.
     *
     * @var string
     */
    protected $_hash;

    /**
     * Указать контрольную сумму файла.
     *
     * @param string $hash
     *
     * @return ZFE_File
     */
    public function setHash($hash)
    {
        $this->_hash = $hash;
        return $this;
    }

    /**
     * Получить контрольную сумму файла.
     *
     * @return string
     */
    public function getHash()
    {
        return $this->_hash;
    }

    /**
     * Класс иконки файла.
     *
     * @var string
     */
    protected $_iconClass;

    /**
     * Указать класс иконки файла.
     *
     * @param string $url
     *
     * @return ZFE_File
     */
    public function setIconClass($url)
    {
        $this->_iconClass = $url;
        return $this;
    }

    /**
     * Получить класс иконки файла.
     *
     * @return string
     */
    public function getIconClass()
    {
        $iconClass = $this->_iconClass;

        if (empty($iconClass)) {
            $iconClass = self::getIconByFileName($this->getName());
        }

        return $iconClass;
    }

    /**
     * Адрес картинки предпросмотра.
     *
     * @var string
     */
    protected $_urlPreview;

    /**
     * Указать адрес предпросмотра файла.
     *
     * @param string $url
     *
     * @return ZFE_File
     */
    public function setPreviewUrl($url)
    {
        $this->_urlPreview = $url;
        return $this;
    }

    /**
     * Получить адрес предпросмотра файла.
     *
     * @return null|string
     */
    public function getPreviewUrl()
    {
        return $this->_urlPreview;
    }

    /**
     * Адрес для скачивания файла.
     *
     * @var string
     */
    protected $_urlDownload;

    /**
     * Указать адрес для скачивая файла.
     *
     * @param string $url
     *
     * @return ZFE_File
     */
    public function setDownloadUrl($url)
    {
        $this->_urlDownload = $url;
        return $this;
    }

    /**
     * Получить адрес для скачивания файла.
     *
     * @return null|string
     */
    public function getDownloadUrl()
    {
        return $this->_urlDownload;
    }

    /**
     * Заголовок ссылки на скачивание файла.
     *
     * @var string
     */
    protected $_labelDownload = 'Скачать';

    /**
     * Указать заголовок ссылки для скачивая файла.
     *
     * @param string $label
     *
     * @return ZFE_File
     */
    public function setDownloadLabel($label)
    {
        $this->_labelDownload = $label;
        return $this;
    }

    /**
     * Получить заголовок ссылки для скачивания файла.
     *
     * @return null|string
     */
    public function getDownloadLabel()
    {
        return $this->_labelDownload;
    }

    /**
     * Адрес для просмотра карточки файла.
     *
     * @var string
     */
    protected $_urlView;

    /**
     * Указать адрес для просмотра карточки файла.
     *
     * @param string $url
     *
     * @return ZFE_File
     */
    public function setViewUrl($url)
    {
        $this->_urlView = $url;
        return $this;
    }

    /**
     * Получить адрес для просмотра карточки файла.
     *
     * @return null|string
     */
    public function getViewUrl()
    {
        return $this->_urlView;
    }

    /**
     * Заголовок ссылки для просмотра карточки файла.
     *
     * @var string
     */
    protected $_labelView = 'Показать карточку';

    /**
     * Указать заголовок ссылки для просмотра карточки файла.
     *
     * @param string $label
     *
     * @return ZFE_File
     */
    public function setViewLabel($label)
    {
        $this->_labelView = $label;
        return $this;
    }

    /**
     * Получить заголовок ссылки для просмотра карточки файла.
     *
     * @return null|string
     */
    public function getViewLabel()
    {
        return $this->_labelView;
    }

    /**
     * Адрес для редактирования карточки файла.
     *
     * @var string
     */
    protected $_urlEdit;

    /**
     * Указать адрес для редактирования карточки файла.
     *
     * @param string $url
     *
     * @return ZFE_File
     */
    public function setEditUrl($url)
    {
        $this->_urlEdit = $url;
        return $this;
    }

    /**
     * Получить адрес для редактирования карточки файла.
     *
     * @return null|string
     */
    public function getEditUrl()
    {
        return $this->_urlEdit;
    }

    /**
     * Заголовок ссылки для редактирования карточки файла.
     *
     * @var string
     */
    protected $_labelEdit = 'Редактировать карточку';

    /**
     * Указать заголовок ссылки для редактирования карточки файла.
     *
     * @param string $label
     *
     * @return ZFE_File
     */
    public function setEditLabel($label)
    {
        $this->_labelEdit = $label;

        return $this;
    }

    /**
     * Получить заголовок ссылки для редактирования карточки файла.
     *
     * @return null|string
     */
    public function getEditLabel()
    {
        return $this->_labelEdit;
    }

    /**
     * Адрес для удаления файла.
     *
     * @var string
     */
    protected $_urlDelete;

    /**
     * Указать адрес для удаления файла.
     *
     * @param string $url
     *
     * @return ZFE_File
     */
    public function setDeleteUrl($url)
    {
        $this->_urlDelete = $url;
        return $this;
    }

    /**
     * Получить адрес для удаления файла.
     *
     * @return null|string
     */
    public function getDeleteUrl()
    {
        return $this->_urlDelete;
    }

    /**
     * Заголовок ссылки для удаления файла.
     *
     * @var string
     */
    protected $_labelDelete = 'Удалить';

    /**
     * Указать заголовок ссылки для удаления файла.
     *
     * @param string $label
     *
     * @return ZFE_File
     */
    public function setDeleteLabel($label)
    {
        $this->_labelDelete = $label;
        return $this;
    }

    /**
     * Получить заголовок ссылки для удаления файла.
     *
     * @return null|string
     */
    public function getDeleteLabel()
    {
        return $this->_labelDelete;
    }

    /**
     * Отображать иконку?
     *
     * @var bool
     */
    protected $_hasIcon = true;

    /**
     * Отображать иконку?
     *
     * Если передан булев параметр, то установить значение, в противном вернуть.
     *
     * @param bool $has
     *
     * @return bool
     */
    public function hasIcon($has = null)
    {
        if (is_bool($has)) {
            $this->_hasIcon = $has;
        }

        if (null === $this->_hasIcon) {
            $iconClass = $this->getIconClass();
            $this->_hasIcon = !empty($iconClass);
        }

        return $this->_hasIcon;
    }

    /**
     * Отображать превьюшку?
     *
     * @var bool
     */
    protected $_hasPreview;

    /**
     * Отображать превьюшку?
     *
     * Если передан булев параметр, то установить значение, в противном вернуть.
     *
     * @param bool $has
     *
     * @return bool
     */
    public function hasPreview($has = null)
    {
        if (is_bool($has)) {
            $this->_hasPreview = $has;
        }

        if (null === $this->_hasPreview) {
            $url = $this->getPreviewUrl();
            $this->_hasPreview = !empty($url);
        }

        return $this->_hasPreview;
    }

    /**
     * Файл можно скачивать?
     *
     * @var bool
     */
    protected $_canDownload;

    /**
     * Файл можно скачивать?
     *
     * Если передан булев параметр, то установить значение, в противном вернуть.
     *
     * @param bool $can
     *
     * @return bool
     */
    public function canDownload($can = null)
    {
        if (is_bool($can)) {
            $this->_canDownload = $can;
        }

        if (null === $this->_canDownload) {
            $url = $this->getDownloadUrl();
            $this->_canDownload = !empty($url);
        }

        return $this->_canDownload;
    }

    /**
     * Файл можно скачивать?
     *
     * @var bool
     */
    protected $_canView;

    /**
     * Файл можно скачивать?
     *
     * Если передан булев параметр, то установить значение, в противном вернуть.
     *
     * @param bool $can
     *
     * @return bool
     */
    public function canView($can = null)
    {
        if (is_bool($can)) {
            $this->_canView = $can;
        }

        if (null === $this->_canView) {
            $url = $this->getViewUrl();
            $this->_canView = !empty($url);
        }

        return $this->_canView;
    }

    /**
     * Допускается редактирование карточки файла?
     *
     * @var bool
     */
    protected $_canEdit;

    /**
     * Допускается редактирование карточки файла?
     *
     * Если передан булев параметр, то установить значение, в противном вернуть.
     *
     * @param bool $can
     *
     * @return bool
     */
    public function canEdit($can = null)
    {
        if (is_bool($can)) {
            $this->_canEdit = $can;
        }

        if (null === $this->_canEdit) {
            $url = $this->getEditUrl();
            $this->_canEdit = !empty($url);
        }

        return $this->_canEdit;
    }

    /**
     * Файл можно удалить?
     *
     * @var bool
     */
    protected $_canDelete;

    /**
     * Файл можно удалить?
     *
     * Если передан булев параметр, то установить значение, в противном вернуть.
     *
     * @param bool $can
     *
     * @return bool
     */
    public function canDelete($can = null)
    {
        if (is_bool($can)) {
            $this->_canDelete = $can;
        }

        if (null === $this->_canDelete) {
            $url = $this->getDeleteUrl();
            $this->_canDelete = !empty($url);
        }

        return $this->_canDelete;
    }

    /**
     * Конструктор файла.
     *
     * @param array $params
     */
    public function __construct($params)
    {
        if (isset($params['name']) && is_string($params['name'])) {
            $this->setName($params['name']);
        }

        if (isset($params['size']) && is_int($params['size'])) {
            $this->setSize($params['size']);
        }

        if (isset($params['hash']) && is_string($params['hash'])) {
            $this->setHash($params['hash']);
        }

        if (isset($params['iconClass']) && is_string($params['iconClass'])) {
            $this->setIconClass($params['iconClass']);
        }

        if (isset($params['previewUrl']) && is_string($params['previewUrl'])) {
            $this->setPreviewUrl($params['previewUrl']);
        }

        if (isset($params['downloadUrl']) && is_string($params['downloadUrl'])) {
            $this->setDownloadUrl($params['downloadUrl']);
        }

        if (isset($params['downloadLabel']) && is_string($params['downloadLabel'])) {
            $this->setDownloadLabel($params['downloadLabel']);
        }

        if (isset($params['viewUrl']) && is_string($params['viewUrl'])) {
            $this->setViewUrl($params['viewUrl']);
        }

        if (isset($params['viewLabel']) && is_string($params['viewLabel'])) {
            $this->setViewLabel($params['viewLabel']);
        }

        if (isset($params['editUrl']) && is_string($params['editUrl'])) {
            $this->setEditUrl($params['editUrl']);
        }

        if (isset($params['editLabel']) && is_string($params['editLabel'])) {
            $this->setEditLabel($params['editLabel']);
        }

        if (isset($params['deleteUrl']) && is_string($params['deleteUrl'])) {
            $this->setDeleteUrl($params['deleteUrl']);
        }

        if (isset($params['deleteLabel']) && is_string($params['deleteLabel'])) {
            $this->setDeleteLabel($params['deleteLabel']);
        }

        if (isset($params['hasIcon']) && is_bool($params['hasIcon'])) {
            $this->hasIcon($params['hasIcon']);
        }

        if (isset($params['hasPreview']) && is_bool($params['hasPreview'])) {
            $this->hasPreview($params['hasPreview']);
        }

        if (isset($params['canDownload']) && is_bool($params['canDownload'])) {
            $this->canDownload($params['canDownload']);
        }

        if (isset($params['canView']) && is_bool($params['canView'])) {
            $this->canView($params['canView']);
        }

        if (isset($params['canEdit']) && is_bool($params['canEdit'])) {
            $this->canEdit($params['canEdit']);
        }

        if (isset($params['canDelete']) && is_bool($params['canDelete'])) {
            $this->canDelete($params['canDelete']);
        }
    }

    //
    // Статические методы для работы с файлами
    //

    /** Число разрядов для разбиения идентификатора */
    public static $div = 3;

    /** Запрещенные расширения */
    protected static $_blackExtensions = [
        'php', 'phtml',
        'sh',
    ];

    /**
     * Безопасно рекурсивно создать директорию.
     *
     * Если родительской директории нету – создать.
     *
     * @param string $path
     */
    public static function makePath($path)
    {
        if (!file_exists($path)) {
            if (!@mkdir($path, 0755, true)) {
                throw new Exception("Mkdir failed for path '{$path}'");
            }
        }
    }

    /**
     * Настроить права на директорию файла.
     *
     * Настраивать права на все папки от базовой до конкретной.
     *
     * @param string $basePath базовая директория
     * @param string $subPath  конкретная директория
     */
    public static function fixPath($basePath, $subPath = null)
    {
        $uploadConfig = Zend_Registry::get('config')->forms->upload;

        $workPath = $basePath;
        $pathArr = explode('/', $subPath);
        foreach ($pathArr as $part) {
            $workPath .= '/' . $part;
            @chmod($workPath, 0777);
            @chown($workPath, $uploadConfig->default->owner);
            @chgrp($workPath, $uploadConfig->default->group);
        }
    }

    /**
     * Преобразовать имя файла к безопасному.
     *
     * @param string     $filename
     * @param null|mixed $ext
     *
     * @return string
     */
    public static function safeFilename($filename, $ext = null)
    {
        $tr = [
            'А' => 'A',   'Б' => 'B',    'В' => 'V',   'Г' => 'G',   'Д' => 'D',
            'Е' => 'E',   'Ё' => 'E',    'Ж' => 'J',   'З' => 'Z',   'И' => 'I',
            'Й' => 'Y',   'К' => 'K',    'Л' => 'L',   'М' => 'M',   'Н' => 'N',
            'О' => 'O',   'П' => 'P',    'Р' => 'R',   'С' => 'S',   'Т' => 'T',
            'У' => 'U',   'Ф' => 'F',    'Х' => 'H',   'Ц' => 'TS',  'Ч' => 'CH',
            'Ш' => 'SH',  'Щ' => 'SCH',  'Ъ' => '',    'Ы' => 'YI',  'Ь' => '',
            'Э' => 'E',   'Ю' => 'YU',   'Я' => 'YA',
            'а' => 'a',   'б' => 'b',    'в' => 'v',   'г' => 'g',   'д' => 'd',
            'е' => 'e',   'ё' => 'e',    'ж' => 'j',   'з' => 'z',   'и' => 'i',
            'й' => 'y',   'к' => 'k',    'л' => 'l',   'м' => 'm',   'н' => 'n',
            'о' => 'o',   'п' => 'p',    'р' => 'r',   'с' => 's',   'т' => 't',
            'у' => 'u',   'ф' => 'f',    'х' => 'h',   'ц' => 'ts',  'ч' => 'ch',
            'ш' => 'sh',  'щ' => 'sch',  'ъ' => 'y',   'ы' => 'yi',  'ь' => '',
            'э' => 'e',   'ю' => 'yu',   'я' => 'ya',
        ];

        if ($ext) {
            $name = $filename;
        } else {
            $name = pathinfo($filename, PATHINFO_FILENAME);
            $ext = pathinfo($filename, PATHINFO_EXTENSION);
        }

        if ($ext) {
            if (in_array($ext, self::$_blackExtensions)) {
                $ext = '_' . $ext;
            }

            $filename = $name . '.' . $ext;
        }

        return preg_replace('/[^a-zA-Z0-9_\-\.]+/', '_', strtr($filename, $tr));
    }

    /**
     * Генерировать путь для файла.
     *
     * @param string $basePath
     * @param int    $id
     * @param string $ext
     * @param bool   $isUrl
     * @param bool   $andRand
     *
     * @return string
     */
    public static function generationPath($basePath, $id = 0, $ext = '', $isUrl = false, $andRand = false)
    {
        if (!$isUrl) {
            $basePath = realpath($basePath);
        }

        $strparts = str_split($id, self::$div);
        $fileName = array_pop($strparts);
        $subPath = implode('/', $strparts);

        if (!$isUrl && !file_exists($basePath . '/' . $subPath)) {
            self::makePath($basePath . '/' . $subPath);
            self::fixPath($basePath, $subPath);
        }

        return $basePath . '/' .
            ($subPath ? $subPath . '/' : '') .
            $fileName .
            (empty($ext) ? '' : '.' . $ext) .
            ($isUrl && $andRand ? '?r=' . mt_rand() : '');
    }

    /**
     * Получить класс иконки типа файла по названию.
     *
     * @param string $name
     *
     * @return null|string
     */
    public static function getIconByFileName($name)
    {
        if (!is_string($name) || empty($name)) {
            return null;
        }

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
            'gz' =>   'fa fa-file-archive-o',
            '7z' =>   'fa fa-file-archive-o',
            'dmg' =>  'fa fa-file-archive-o',

            // Other
            'pdf' =>  'fa fa-file-pdf-o',
        ];

        $ext = mb_strtolower(pathinfo($name, PATHINFO_EXTENSION));
        return $matrix[$ext] ?? 'fa fa-file-o';
    }

    public static function humanFileSize($bytes, $precision = 2)
    {
        $base = log($bytes, 1024);
        $suffixes = ['байт', 'КБ', 'МБ', 'ГБ', 'ТБ'];
        return round(1024 ** ($base - floor($base)), $precision) . ' ' . $suffixes[floor($base)];
    }
}
