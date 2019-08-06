<?php

/*
 * ZFE – платформа для построения редакторских интерфейсов.
 */

/**
 * Менеджер файлов для модели, позволяет осуществить все необходимое для управления файлами модели,
 * начиная с их загрузки и заканчивая их удалением.
 * См. Интерфейс ZFE_File_Manageable, который определяет, что для модели существует ее менеджер,
 * и т.о. для модели можно управлять файлами через ее менеджера.
 */
abstract class ZFE_File_Manager extends ZFE_File_ManageableAccess
{
    /**
     * Шорткат для примера.
     *
     * @param ZFE_File_Manageable $record
     * @param bool                $accessControl
     * @param Editors|null        $user
     *
     * @throws ZFE_File_Exception
     * @throws Zend_Auth_Exception
     * @throws Zend_Exception
     *
     * @return ZFE_File_Manager_Default
     */
    public static function getDefault(ZFE_File_Manageable $record, $accessControl = true, Editors $user = null)
    {
        $fm = new ZFE_File_Manager_Default($record, Zend_Registry::get('config')->files);
        if ($accessControl) {
            $fm->initAccessControl(Zend_Registry::get('acl'), $user ?? Zend_Registry::get('user')->data);
        }
        return $fm;
    }

    /**
     * Запрещенные расширения.
     */
    protected $blackExtensions = [
        'php', 'phtml', 'sh',
    ];

    /**
     * @var ZFE_File_Loader
     */
    protected $loader;

    /**
     * @var Editors
     */
    protected $user;

    /**
     * @var ZFE_File_Accessor
     */
    protected $accessor;

    /**
     * @var Zend_Config секция files
     */
    protected $config;

    /**
     * @param ZFE_File_Manageable $record
     * @param Zend_Config         $config
     *
     * @throws ZFE_File_Exception
     */
    public function __construct(ZFE_File_Manageable $record, Zend_Config $config)
    {
        $this->record = $record;
        $this->config = $config;
        $this->loader = new ZFE_File_Loader($config);
    }

    /**
     * @param Zend_Acl                  $acl
     * @param ZFE_Model_Default_Editors $user
     *
     * @throws ZFE_File_Exception
     */
    public function initAccessControl(Zend_Acl $acl, ZFE_Model_Default_Editors $user)
    {
        $this->accessor = $this->initAccessor($acl, $user);
        $this->accessor->setRecord($this->getRecord());
    }

    /**
     * @return ZFE_File_Loader
     */
    public function getLoader(): ZFE_File_Loader
    {
        return $this->loader;
    }

    /**
     * @return ZFE_File_Accessor
     */
    public function getAccessor(): ?ZFE_File_Accessor
    {
        return $this->accessor;
    }

    /**
     * @var array
     */
    protected $errors = [];

    /**
     * @return array
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * Сохранить файлы для записи
     * Метод выполняет все операции, обусловленные схемой поля, указанной с помощью typeCode.
     *
     * @param array $tmpPaths пути откуда забрать файлы
     * @param int   $typeCode код схемы файла
     *
     * @throws ZFE_File_Exception
     * @throws Doctrine_Exception
     * @throws Zend_Exception
     */
    public function manage(array $tmpPaths, $typeCode)
    {
        $schemas = $this->getFieldsSchemas();
        $schema = $schemas->get($typeCode);

        $loader = $this->getLoader();

        /** @var ZFE_File_Processor $processor */
        $processor = $schema->getProcessor();

        // find records of existed files with same types
        $toDeleteColl = null;
        if (!$schema->isMultiple()) {
            // для мультизагрузки не надо затирать файлы, надо добавлять
            $toDeleteColl = $this->findFiles($typeCode);
        }

        $toSaveColl = new Doctrine_Collection(Files::class);
        $processings = [];

        $conn = Doctrine_Manager::connection();
        $conn->beginTransaction();

        foreach ($tmpPaths as $tmpPath) {  /** @var Files $file */
            $file = $this->createFile($tmpPath, $typeCode);
            $file->save();  // сохраним сразу, чтобы получить id

            $loader->setRecord($file);
            $file = $loader->upload();  // получили значение в поле path
            $toSaveColl->add($file);  // нужно пересохранить

            if ($processor) {  // если определен процессор для файла
                $processor->plan($file);
                $processings[] = $processor->getProcessing();
            }
        }

        // unit of work

        if ($toDeleteColl && $toDeleteColl->count()) {
            foreach ($toDeleteColl as $item) {
                $loader->setRecord($item)->erase();
            }
            $toDeleteColl->delete();
        }

        $toSaveColl->save();

        if (!empty($processings)) {
            foreach ($processings as $processing) {
                $processing->save();
            }
        }

        $conn->commit();
    }

    /**
     * Преобразовать имя файла к безопасному.
     *
     * @param string     $filename
     * @param null|mixed $ext
     *
     * @return string
     */
    protected function safeFileName($filename, $ext = null)
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

        $filename = strtr($filename, $tr);
        $filename = preg_replace('/[^a-zA-Z0-9_\-\.]+/', '_', $filename);

        if ($ext) {
            $name = $filename;
        } else {
            $name = pathinfo($filename, PATHINFO_FILENAME);
            $ext = pathinfo($filename, PATHINFO_EXTENSION);
        }

        if (in_array($ext, $this->blackExtensions, true)) {
            $ext = '_' . $ext;
        }

        $extLen = mb_strlen($ext);
        $name = mb_substr($name, 0, 255 - $extLen);
        return $name . '.' . $ext;
    }

    /**
     * Создать запись файла.
     *
     * @param string $path
     * @param int    $typeCode
     *
     * @throws ZFE_File_Exception
     * @throws Doctrine_Exception
     * @throws Zend_Exception
     *
     * @return Files
     */
    protected function createFile(string $path, int $typeCode = 0): Files
    {
        if (!file_exists($path)) {
            throw new ZFE_File_Exception('Файла ' . $path . ' не существует');
        }
        if (!is_readable($path)) {
            throw new ZFE_File_Exception('Файл ' . $path . ' не доступен для чтения');
        }

        $file = new Files;
        $file->set('model_name', get_class($this->record));
        $file->set('item_id', $this->record->id);
        $file->set('type', $typeCode);

        $name = mb_substr(mb_strrchr($path, '/'), 1);
        $file->set('title_original', $name);
        $file->set('title', $this->safeFilename($name));

        $size = filesize($path);
        $file->set('size', $size);

        $hash = hash_file('crc32', $path) ?? 'empty';
        $file->set('hash', $hash);

        $file->set('ext', mb_strtolower(pathinfo($path, PATHINFO_EXTENSION)));

        $file->set('created_at', date('Y-m-d H:i:s'));
        if ($this->user) {
            $file->set('creator_id', $this->user->id);
        }

        $file->set('path', '');

        $mapper = new ZFE_File_PathMapper($file);
        $mapper->map($path);

        $file->clearRelated();
        return $file;
    }

    /**
     * Получить список агентов по файлам, для указанных схемами полей файлов, если они были загружены.
     *
     * @param ZFE_File_Schema_Collection $schemas
     * @param bool                       $byCode  группировать агентов в группу по код поля, а не по названию?
     *
     * @throws ZFE_File_Exception
     * @throws Doctrine_Connection_Exception
     * @throws Doctrine_Query_Exception
     * @throws Doctrine_Record_Exception
     *
     * @return array
     */
    public function getAgents(ZFE_File_Schema_Collection $schemas, $byCode = false): array
    {
        $list = [];
        $uploaded = $this->getFiles();
        foreach ($schemas as $schema) {  /** @var ZFE_File_Schema $schema */
            $key = $byCode ? $schema->getFileTypeCode() : $schema->getTitle();
            $list[$key] = [];
            $typeCode = $schema->getFileTypeCode();
            foreach ($uploaded as $file) {  /** @var Files $file */
                if ($file->type == $typeCode) {
                    $agent = $this->createFileAgent($file);
                    $list[$key][] = $agent;
                }
            }
        }
        return $list;
    }

    /**
     * Проверить наличие файлов по обязательным полям (если такие есть).
     * Возвращает массив с текстами о проблемах (если возникли).
     *
     * @param bool $strict проверить наличие файла в ФС
     *
     * @throws ZFE_File_Exception
     * @throws Doctrine_Query_Exception
     * @throws Doctrine_Record_Exception
     * @throws Zend_Exception
     *
     * @return array
     */
    public function checkRequired($strict = false): array
    {
        $files = $this->getFiles('type');
        $schemas = $this->getFieldsSchemas();

        $problems = [];
        if ($schemas->hasRequired()) {
            if ($files->count() == 0) {
                $message = 'Необходимо загрузить файлы в обязательные поля';
                $problems[] = $message;
            } else {
                $message = 'Необходимо загрузить файл(ы) в обязательное поле "%s"';
                $loader = $this->getLoader();
                foreach ($schemas->getRequired() as $schema) {  /** @var ZFE_File_Schema $schema */
                    $file = $files->get($schema->getFileTypeCode());
                    if ($file && $file->exists()) {
                        if ($strict) {
                            // дополнительно проверяем наличие файла в ФС
                            // $path = $loader->setRecord($file)->getResultPath();
                            $path = $loader->setRecord($file)->absFilePath();
                            if (!file_exists($path)) {
                                $problems[] = sprintf($message, $schema->getTitle());
                            }
                        }

                        // NO PROBLEMO!
                    } else {
                        $problems[] = sprintf($message, $schema->getTitle());
                    }
                }
            }
        }

        return $problems;
    }

    /**
     * Получить коллекцию файлов для данной записи.
     * @deprecated использовать findFiles()!
     * @param string $indexBy
     * @throws Doctrine_Query_Exception
     * @return Doctrine_Collection|null
     */
    public function getFiles($indexBy = 'id'): ?Doctrine_Collection
    {
        $files = $this->findFiles();
        $rows = clone $files;
        $res = [];
        foreach ($rows as $row) {
            $res[$row->{$indexBy}] = $row;
        }
        $rows->setKeyColumn($indexBy);
        $rows->setData($res);

        return $rows;
    }

    /**
     * @param null $typeCode Код схемы поля
     * @param string $indexBy Поля для индексирования, осторожнее с полем type!
     * @return Doctrine_Collection
     * @throws Doctrine_Query_Exception
     */
    public function findFiles($typeCode = null, $indexBy = 'id'): Doctrine_Collection
    {
        $itemId = $this->record->id;
        $modelName = get_class($this->record);

        $q = ZFE_Query::create()
            ->select('x.*')
            ->from(Files::class . ' x INDEXBY id')
            ->where('x.item_id = ?', $itemId)
            ->addWhere('x.model_name = ?', $modelName)
        ;
        if ($typeCode !== null) {
            $q->addWhere('x.type = ?', $typeCode);
        }
        $files = $q->execute();

        if ($indexBy !== 'id') {
            $items = clone $files;
            $result = [];
            foreach ($items as $item) {
                // проблема в случае индексирования по type при наличии мультизагрузочных полей
                // может быть несколько файлов с одинаковым type - они потеряются, останется только последний их них
                $result[$item->{$indexBy}] = $item;
            }
            $items->setKeyColumn($indexBy);
            $items->setData($result);
            return $items;
        }

        return $files;
    }

    /**
     * Возвращает кол-во файлов по типам (если указаны) или всего.
     *
     * @param array $types
     *
     * @throws ZFE_File_Exception
     * @throws Doctrine_Query_Exception
     *
     * @return int
     */
    public function getFilesCount($types = []): int
    {
        /** @var ZFE_Model_AbstractRecord $record */
        $record = $this->getRecord();
        $q = Doctrine_Query::create()
            ->select('COUNT(*)')
            ->from(Files::class)
            ->where('item_id = ?', $record->id)
        ;
        if (!empty($types)) {
            $q->andWhereIn('type', $types);
        }
        return (int) $q->execute([], Doctrine_Core::HYDRATE_SINGLE_SCALAR);
    }

    /**
     * @return ZFE_File_Schema_Collection
     */
    abstract public function getFieldsSchemas(): ZFE_File_Schema_Collection;

    /**
     * @param Zend_Acl                  $acl
     * @param ZFE_Model_Default_Editors $user
     *
     * @return ZFE_File_Accessor
     */
    abstract protected function initAccessor(Zend_Acl $acl, ZFE_Model_Default_Editors $user): ZFE_File_Accessor;

    /**
     * Создание агента файла, можно переопределить в дочернем менеджере для модели.
     * Используется для получения списка представителей для файлов модели (getAgents).
     *
     * @param Files $file
     *
     * @throws ZFE_File_Exception
     * @throws Doctrine_Connection_Exception
     * @throws Doctrine_Record_Exception
     *
     * @return ZFE_File_Agent
     */
    protected function createFileAgent(Files $file): ZFE_File_Agent
    {
        $agent = new ZFE_File_Agent($file);
        if ($this->accessor) {
            $agent->useAccessor($this->accessor);
        }
        return $agent;
    }

    /**
     * Удалить все файлы из ФС и записи о файлах в БД.
     * Или указать код схемы поля для удаление файлов с нужным типом
     *
     * @param mixed $typeCode Код схемы поля для удаления
     *
     * @throws ZFE_File_Exception
     * @throws Doctrine_Query_Exception
     * @throws Doctrine_Record_Exception
     * @throws Zend_Exception
     */
    public function purge($typeCode = null)
    {
        $existedFiles = $this->findFiles($typeCode);
        if ($existedFiles->count()) {
            foreach ($existedFiles as $file) {  /** @var Files $file */
                $this->getLoader()->setRecord($file)->erase();
                $file->clearRelated();
                $file->delete();
            }
        }
    }
}
