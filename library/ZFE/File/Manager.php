<?php
/**
 * Created by PhpStorm.
 * User: dezzpil
 * Date: 09.10.18
 * Time: 11:43
 */

/**
 * Class Helper_File_Loader
 * TODO случаи и примеры использования
 */
abstract class Helper_File_Manager extends Helper_File_ManageableAccess
{
    /*
    CREATE TABLE `*_files` (
        `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
        `creator_id` int(10) unsigned DEFAULT NULL,
        `created_at` timestamp NULL DEFAULT NULL,
        `item_id` int(10) unsigned NOT NULL,
        `title` varchar(100) NOT NULL,
        `size` int(10) unsigned DEFAULT NULL,
        `hash` varchar(45) DEFAULT NULL,
        `type` tinyint(4) unsigned NOT NULL DEFAULT '0',
        `ext` varchar(4) NOT NULL,
        `path` varchar(256) NOT NULL COMMENT 'путь к файлу относительно корня',
        PRIMARY KEY (`id`),
        KEY `fk_requests_id_idx` (`item_id`),
        CONSTRAINT `fk_files_1` FOREIGN KEY (`item_id`) REFERENCES `*` (`id`) ON UPDATE NO ACTION
     ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
     *
     */

    protected function checkRecordModel(Doctrine_Record $record)
    {
        foreach (['item_id', 'type', 'title', 'size', 'hash', 'ext', 'path'] as $column) {
            if (!$record->contains($column)) throw new Exception(
                sprintf("No %s in %s class", $column, get_class($record))
            );
        }
    }

    /**
     * @var Helper_File_Loader
     */
    protected $loader;

    /**
     * @var Editors|null
     */
    protected $user = null;

    /**
     * @var Helper_File_Accessor|null
     */
    protected $accessor = null;

    /**
     * Helper_File_Manager constructor.
     *
     * @param Helper_File_Manageable $record
     * @param bool $accessControl проверять доступ для действий?
     * @param Editors|null $user
     * @param Zend_Acl|null $acl
     *
     * @throws Application_Exception
     * @throws Zend_Auth_Exception
     * @throws Zend_Exception
     */
    public function __construct(Helper_File_Manageable $record)
    {
        $modelName = $record->getFilesRelationName();
        $this->checkRecordModel(new $modelName);

        // не используем setRecord, храним лоадер и аксесор в менеджере
        $this->record = $record;
        $this->loader = new Helper_File_Loader($this->getLoaderConfig(), FILES_PATH);
    }

    /**
     * @param Editors|null $user
     * @return $this
     */
    public function setUser(Editors $user = null)
    {
        $this->user = $user;
        return $this;
    }

    /**
     * @param Zend_Acl $acl
     * @throws Application_Exception
     * @throws Zend_Auth_Exception
     * @throws Zend_Exception
     */
    public function initAccessControl(Zend_Acl $acl)
    {
        $user = $this->user ?? Editors::find(Editors::getMyId());
        $this->accessor = $this->initAccessor($acl, $user);
        $this->accessor->setRecord($this->getRecord());
    }

    /**
     * @return Helper_File_Loader
     */
    public function getLoader() : Helper_File_Loader
    {
        return $this->loader;
    }

    /**
     * @return Helper_File_Accessor
     */
    public function getAccessor() : ?Helper_File_Accessor
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
    function getErrors() : array
    {
        return $this->errors;
    }

    /**
     * @param Zend_File_Transfer_Adapter_Http|null $adapter
     * @throws Application_Exception
     * @throws Doctrine_Exception
     * @throws Zend_Exception
     * @throws Zend_File_Transfer_Exception
     */
    function manageAdapter(Zend_File_Transfer_Adapter_Http $adapter = null)
    {
        $files = [];
        foreach ($_FILES as $fieldKey => $data) {
            if (array_key_exists('name', $data) && !empty($data['name'][0])) {
                foreach ($data['name'] as $i => $name) {
                    if (!array_key_exists($fieldKey, $files)) {
                        $files[$fieldKey] = [];
                    }
                    $files[$fieldKey][] = $fieldKey . '_' . $i . '_';
                }
            }
        }

        if (empty($files)) {
            return ;
        }

        if ($adapter == null) {
            $adapter = new Zend_File_Transfer_Adapter_Http();
            $adapter->setDestination('/tmp/');
        }

        $schemas = $this->getFieldsSchemas();
        foreach ($schemas as $schema) {

            /* @var $schema Helper_File_Schema */
            if (!array_key_exists($schema->getFieldName(), $files)) {
                // не загружаем файлы, не соотв. схемам
                continue;
            }

            $filesKeys = $files[$schema->getFieldName()];

            if (!@$adapter->receive($filesKeys)) {
                throw new Application_Exception(join('<br />', $adapter->getMessages()));
            }

            $pathes = [];
            foreach ($adapter->getFileInfo($filesKeys) as $item) {
                $pathes[] = $item['tmp_name'];
            }

            $this->manage($pathes, $schema);
        }
    }

    /**
     * @param array $tmpPaths
     * @param Helper_File_Schema $schema
     * @throws Application_Exception
     * @throws Doctrine_Exception
     * @throws Zend_Exception
     */
    function manage(array $tmpPaths, Helper_File_Schema $schema)
    {
        $loader = $this->getLoader();
        $typeCode = $schema->getFileTypeCode();
        $processor = $schema->getProcessor();

        // find records of existed files with same types
        $toDeleteColl = null;
        if (!$schema->isMultiple()) {
            // для мультизагрузки не надо затирать файлы, надо добавлять
            $toDeleteColl = $this->findExistedFiles($typeCode);
        }

        $modelName = $this->getRecord()->getFilesRelationName();
        $toSaveColl = new Doctrine_Collection($modelName);
        $processings = [];

        $conn = Doctrine_Manager::connection();
        $conn->beginTransaction();

        foreach ($tmpPaths as $tmpPath) {

            /* @var $file Helper_File_Loadable */
            $file = $this->createFile($tmpPath, $typeCode);
            if (empty($file->path)) {
                $file->path = '';
            }
            $file->save(); // сохраним сразу

            $loader->setRecord($file);
            $file = $loader->upload();
            $toSaveColl->add($file); // сохранять будем вместе со всем остальным

            if ($processor) {
                /* @var $processor Helper_File_Processor */
                $processor->plan($file);
                $processings[] = $processor->getProcessing();
            }
        }

        // unit of work

        if ($toDeleteColl && $toDeleteColl->count()) {
            //var_dump($toDeleteColl->toArray(0));die;
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
     * @param Zend_Form $form
     *
     * @throws Application_Exception
     * @throws Doctrine_Exception
     * @throws Zend_Exception
     */
    function manageForm(Zend_Form $form) : void
    {
        $schemas = $this->getFieldsSchemas();
        foreach ($schemas as $schema) {

            /* @var $schema Helper_File_Schema */
            $fieldName = $schema->getFieldName();

            /* @var $fieldElement Zend_Form_Element_File */
            $fieldElement = $form->getElement($fieldName);

            if ($fieldElement) {
                if ($fieldElement->hasErrors()) {
                    $this->errors = $fieldElement->getMessages();
                    throw new Application_Exception('Возникли ошибки при загрузке файлов');
                } else {
                    $this->errors = [];
                }

                if ($fieldElement->isReceived()) {
                    $tmpPaths = $fieldElement->getFileName();
                    if (is_string($tmpPaths)) {
                        $tmpPaths = [$tmpPaths];
                    }
                    $this->manage($tmpPaths, $schema);
                }
            }
        }
    }

    /**
     * @param int $typeCode
     * @return Doctrine_Collection
     * @throws Application_Exception
     * @throws Doctrine_Collection_Exception
     * @throws Doctrine_Query_Exception
     */
    protected function findExistedFiles(int $typeCode = 0) : Doctrine_Collection
    {
        $itemId = $this->getRecord()->id;
        $keyColumn = Helper_File_Loadable::KEY_TO_ITEM;
        $modelName = $this->getRecord()->getFilesRelationName();
        $collToDelete = new Doctrine_Collection($modelName);

        $q = ZFE_Query::create()
            ->select('x.*')
            ->from($modelName . ' x INDEXBY id')
            ->where('x.' . $keyColumn . ' = ?', $itemId)
            ->addWhere('x.type = ?', $typeCode);
        $result = $q->execute();
        if ($result->count()) {
            $collToDelete->merge($result);
        }
        return $collToDelete;
    }

    /**
     * Создать записи файлов
     *
     * @param string $path
     * @param int $typeCode
     * @return Helper_File_Loadable
     *
     * @throws Application_Exception
     * @throws Doctrine_Exception
     * @throws Zend_Exception
     */
    protected function createFile(string $path, int $typeCode = 0) : Helper_File_Loadable
    {
        if (!file_exists($path)) {
            throw new Application_Exception('Файла ' . $path . ' не существует');
        }
        if (!is_readable($path)) {
            throw new Application_Exception('Файл ' . $path . ' не доступен для чтения');
        }

        $itemId = $this->getRecord()->id;
        $keyColumn = Helper_File_Loadable::KEY_TO_ITEM;
        $modelName = $this->getRecord()->getFilesRelationName();

        $file = new $modelName;
        $file->set($keyColumn, $itemId);
        $file->set('type', $typeCode);

        $name = $typeCode . '-' . substr(strrchr($path, '/'), 1);
        $newFileName = ZFE_File::safeFilename($name);
        $file->set('title', $newFileName);

        $size = filesize($path);
        $file->set('size', $size);

        $hash = hash_file('crc32', $path) ?? 'empty';
        $file->set('hash', $hash);

        $file->set('ext', strtolower(pathinfo($path, PATHINFO_EXTENSION)));

        $file->set('created_at', date('Y-m-d H:i:s'));
        if ($this->user) {
            $file->set('creator_id', $this->user->id);
        }

        $mapper = new Helper_File_PathMapper($file);
        $mapper->map($path);

        $file->clearRelated();
        return $file;
    }

    /**
     * Получить список из ZFE_File для поля с указанным названием
     * Используется для обратной совместимости в \Application_Form_Extensions::populateFiles
     * @deprecated используйте \Helper_File_Manager::getAgents
     *
     * @param string $fieldName
     * @return array
     *
     * @throws Application_Exception
     * @throws Doctrine_Record_Exception
     */
    public function getZFEFiles(string $fieldName) : array
    {
        $list = [];
        $accessor = $this->getAccessor();
        $loader = $this->getLoader();
        $uploaded = $this->getFilesRecords();
        $schemas = $this->getFieldsSchemas();
        foreach ($schemas as $schema) {
            /* @var $schema Helper_File_Schema */
            if ($fieldName == $schema->getFieldName()) {
                $typeCode = $schema->getFileTypeCode();
                foreach ($uploaded as $file) {
                    /* @var $file Helper_File_Loadable */
                    $loader->setRecord($file);
                    if ($file->type == $typeCode) {
                        $zfile =  new ZFE_File([
                            'name' => $file->title,
                            'size' => intval($file->size),
                            'hash' => $file->hash,
                            'canDelete' => $accessor ? $accessor->isAllowToDelete() : false,
                            'deleteUrl' => $accessor ? $accessor->getDeleteUrl($file) : null,
                            'canDownload' => $accessor ? $accessor->isAllowToDownload() : false,
                            'downloadUrl' => $accessor ? $accessor->getDownloadUrl($file) : null,
                        ]);

                        $list[] = $zfile;
                    }
                }
            }
        }
        return $list;
    }

    /**
     * Получить список агентов по файлам, для указанных схемами полей файлов, если они были загружены
     * @param Helper_File_Schema_Collection $schemas
     * @return array
     * @throws Application_Exception
     * @throws Doctrine_Connection_Exception
     * @throws Doctrine_Query_Exception
     * @throws Doctrine_Record_Exception
     */
    public function getAgents(Helper_File_Schema_Collection $schemas) : array
    {
        $list = [];
        //$loader = $this->getLoader();
        $uploaded = $this->getFilesRecords();
        foreach ($schemas as $schema) {
            /* @var $schema Helper_File_Schema */
            $list[$schema->getTitle()] = [];
            $typeCode = $schema->getFileTypeCode();
            foreach ($uploaded as $file) {
                /* @var $file Helper_File_Loadable */
                //$loader->setRecord($file);
                if ($file->type == $typeCode) {
                    $agent = $this->createFileAgent($file);
                    $list[$schema->getTitle()][] = $agent;
                }
            }
        }
        return $list;
    }

    /**
     * Проверить наличие файлов по обязательным полям (если такие есть)
     * Возвращает массив с текстами о проблемах (если возникли)
     *
     * @param bool $strict проверить наличие файла в ФС
     * @return array
     * @throws Application_Exception
     * @throws Doctrine_Query_Exception
     * @throws Doctrine_Record_Exception
     * @throws Zend_Exception
     */
    public function checkRequired($strict = false) : array
    {
        $files = $this->getFilesRecords('type');
        $schemas = $this->getFieldsSchemas();

        $problems = [];
        if ($schemas->hasRequired()) {
            if ($files->count() == 0) {
                $message = 'Необходимо загрузить файлы в обязательные поля';
                $problems[] = $message;
            } else {
                $message = 'Необходимо загрузить файл(ы) в обязательное поле "%s"';
                $loader = $this->getLoader();
                foreach ($schemas->getRequired() as $schema) {
                    /* @var $schema Helper_File_Schema */
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
     * Получить коллекцию файлов для данной записи
     *
     * @param string $indexBy
     * @return Doctrine_Collection|null
     * @throws Application_Exception
     * @throws Doctrine_Query_Exception
     */
    public function getFilesRecords($indexBy = 'id'): ?Doctrine_Collection
    {
        $record = $this->getRecord();
        $relName = $record->getFilesRelationName();

//        $q = Doctrine_Query::create()->select('*')
//            ->from( $relName . ' INDEXBY ' . $indexBy)
//            ->groupBy($indexBy)
//            ->where('item_id = ?', $record->id);
//        return $q->execute();

        // for postgres
        $rows = clone $record->$relName;
        $res = [];
        foreach ($rows as $row) {
            $res[$row->$indexBy] = $row;
        }
        $rows->setKeyColumn($indexBy);
        $rows->setData($res);

        return $rows;
    }

    /**
     * Возвращает кол-во файлов по типам (если указаны) или всего
     * @param array $types
     * @return int
     * @throws Application_Exception
     * @throws Doctrine_Query_Exception
     */
    public function getFilesCount($types = []): int
    {
        $record = $this->getRecord();
        $relName = $record->getFilesRelationName();
        $q = Doctrine_Query::create()
            ->select('COUNT(*)')
            ->from($relName)
            ->where('item_id = ?', $record->id);
        if (!empty($types)) {
            $q->andWhereIn('type', $types);
        }
        return $q->execute([], Doctrine_Core::HYDRATE_SINGLE_SCALAR);
    }

    /**
     * @return Zend_Config
     */
    abstract protected function getLoaderConfig() : Zend_Config;

    /**
     * @return Helper_File_Schema_Collection
     */
    abstract public function getFieldsSchemas() : Helper_File_Schema_Collection;

    /**
     * @param Zend_Acl $acl
     * @param Editors $user
     * @return Helper_File_Accessor
     */
    abstract protected function initAccessor(Zend_Acl $acl, Editors $user) : Helper_File_Accessor;

    /**
     * Создание агента файла, можно переопределить в дочернем менеджере для модели
     * Используется для получения списка представителей для файлов модели (getAgents)
     * @param Helper_File_Loadable $file
     * @return Helper_File_Agent
     *
     * @throws Application_Exception
     * @throws Doctrine_Connection_Exception
     * @throws Doctrine_Record_Exception
     */
    protected function createFileAgent(Helper_File_Loadable $file) : Helper_File_Agent
    {
        $agent = new Helper_File_Agent($file);
        if ($this->accessor) $agent->useAccessor($this->accessor);
        return $agent;
    }

    /**
     * Удалить все файлы из ФС и записи о файлах в БД
     * @throws Application_Exception
     * @throws Doctrine_Query_Exception
     * @throws Doctrine_Record_Exception
     * @throws Zend_Exception
     */
    function purge()
    {
        $existedFiles = $this->getFilesRecords();
        if ($existedFiles->count()) {
            foreach ($existedFiles as $file) {
                /* @var Helper_File_Loadable $file */
                $this->getLoader()->setRecord($file)->erase();
                $file->clearRelated();
                $file->delete();
            }
        }
    }

}
