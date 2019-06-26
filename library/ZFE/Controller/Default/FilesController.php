<?php
/**
 * Created by PhpStorm.
 * User: dezzpil
 * Date: 16.10.18
 * Time: 13:43
 */

class FilesController extends Controller_Abstract
{
    /**
     * @return Helper_File_Manageable
     * @throws Zend_Controller_Action_Exception
     */
    protected function findModelItem() : Helper_File_Manageable
    {
        $itemModel = $this->getParam('m');
        $itemId = $this->getParam('id');
        if (class_exists($itemModel)) {
            $item = $itemModel::find($itemId);
            if (!$item) {
                throw new Zend_Controller_Action_Exception('Данной записи не существует в системе', 404);
            }
        } else {
            throw new Zend_Controller_Action_Exception('Данной модели не существует в системе', 404);
        }

        if (!($item instanceof Helper_File_Manageable)) {
            throw new Zend_Controller_Action_Exception('Данная запись не поддерживает необходимые методы для работы с файлами', 403);
        }

        return $item;
    }

    /**
     * @param Helper_File_Manager $manager
     * @return Helper_File_Loadable|null
     * @throws Application_Exception
     * @throws Doctrine_Query_Exception
     * @throws Zend_Controller_Action_Exception
     */
    protected function findFile(Helper_File_Manager $manager) : ?Helper_File_Loadable
    {
        $fileId = $this->getParam('fid');
        $files = $manager->getFilesRecords('id');
        if ($files->count() && $files->contains($fileId)) {
            return $files->get($fileId);
        }
        throw new Zend_Controller_Action_Exception('Файл не найден', 404);
    }

    /**
     * Удалить файл
     *
     * @throws Application_Exception
     * @throws Doctrine_Query_Exception
     * @throws Doctrine_Record_Exception
     * @throws Zend_Controller_Action_Exception
     * @throws Zend_Exception
     */
    public function deleteAction()
    {
        $item = $this->findModelItem();
        $fm = $item->getFileManager();
        $accessor = $fm->getAccessor();
        if (!$accessor->isAllowToDelete()) {
            throw new Zend_Controller_Action_Exception('Удаление файла запрещено', 403);
        }

        $file = $this->findFile($fm);
        if ($file) {
            $fm->getLoader()->setRecord($file)->erase();
            $file->delete();
            $this->getNotices()->ok('Файл ' . $file->getTitle() . ' успешно удален');
        } else {
            $this->getNotices()->err('Файл для записи не найден. Удаление невозможно');
        }
        $this->redirect($item->getEditUrl());
    }

    /**
     * Скачать файл
     *
     * @throws Application_Exception
     * @throws Doctrine_Query_Exception
     * @throws Doctrine_Record_Exception
     * @throws Zend_Controller_Action_Exception
     * @throws Zend_Exception
     */
    public function downloadAction()
    {
        $item = $this->findModelItem();
        $fm = $item->getFileManager();
        $accessor = $fm->getAccessor();
        if (!$accessor->isAllowToDownload()) {
            throw new Zend_Controller_Action_Exception('Скачивание файла запрещено', 403);
        }

        $file = $this->findFile($fm);
        $loader = $fm->getLoader()->setRecord($file);

        //var_dump($loader->absFilePath(), '/download/' . $file->path);die;

        // location /download должен быть указан в конфиге nginx:
        //
        // location /download {
        //        internal;
        //        alias /var/www/links/toebz/data;
        //    }
        $this->_helper->download($loader->absFilePath(), '/download/' . $file->path, $file->title);
    }

    /**
     * @todo метод сам по себе костыльный - нужен для поддержки работы старого подхода управления файлами
     * Скачать файл, который не реализует Helper_File_Loadable
     * Метод создан для обратной совместимости со старым описанием файлов
     *
     * @throws Application_Exception
     * @throws Zend_Controller_Action_Exception
     * @deprecated
     */
    public function downloadGracefullyAction()
    {
        $itemModel = $this->getParam('m');
        $itemId = $this->getParam('id');
        if (class_exists($itemModel)) {
            $item = $itemModel::find($itemId);
            if (!$item) {
                throw new Zend_Controller_Action_Exception('Данной записи не существует в системе', 404);
            }
        }

        $path = $item->getResultPath();
        //$url = str_replace(FILES_PATH, '', $path);

        // TODO это костыли, не было времени починить правильно
        $config = Zend_Registry::get('config');
        $realpath = $config->forms->files->realpath;
        $url = str_replace($realpath, 'files/', $path);

        if (strpos($url, '/') === 0) {
            $url = substr($url, 1);
        }

        //var_dump($path, $realpath, '/download/' . $url);die;

        $this->_helper->download($path, '/download/' . $url, $item->title);
    }

    /**
     * Скачать все файлы одним архивом
     */
    public function downloadAll()
    {
        // TODO создавать временный файл zip и отдавать его
        // TODO как/когда его потом удалять?
    }

    /**
     * Просмотр списка файлов записи
     * Если просмотр списка файлов для записи недоступен, то отдаем null
     *
     * @throws Application_Exception
     * @throws Doctrine_Record_Exception
     * @throws Zend_Controller_Action_Exception
     * @deprecated
     */
    public function listAction()
    {
        $item = $this->findModelItem();
        $fm = $item->getFileManager();

        $result = null;
        $accessor = $fm->getAccessor();
        if ($accessor->isAllowToList()) {
            //throw new Zend_Controller_Action_Exception('Доступ запрещен', 403);
            $result = [];
            foreach ($fm->getFieldsSchemas() as $schema) {
                /* @var $schema Helper_File_Schema */
                $result[$schema->getTitle()] = $fm->getZFEFiles($schema->getFieldName());
            }
        }

        $this->view->items = $result;
    }

    public function agentsAction()
    {
        $item = $this->findModelItem();
        $fm = $item->getFileManager();

        $result = null;
        $accessor = $fm->getAccessor();
        if ($accessor->isAllowToList()) {
            $this->view->items = $fm->getAgents($fm->getFieldsSchemas());
            return;
        }

        $this->view->items = $result;
    }

    /**
     * Запланировать обработку файла
     *
     * @throws Application_Exception
     * @throws Doctrine_Query_Exception
     * @throws Zend_Controller_Action_Exception
     */
    public function processAction()
    {
        $item = $this->findModelItem();
        $fm = $item->getFileManager();
        $accessor = $fm->getAccessor();
        if (!$accessor->isAllowToProcess()) {
            throw new Zend_Controller_Action_Exception('Доступ запрещен', 403);
        }

        /* @var $file Helper_File_Processable */
        $file = $this->findFile($fm);
        if (!($file instanceof Helper_File_Processable)) {
            $this->getNotices()->err('Файл не поддерживает обработку');
        } else {
            /* @var $mapping Helper_File_Processor_Mapping */
            $mapping = $file->getProcessings();
            foreach ($mapping as $modelName => $collection) { /* @var $collection Doctrine_Collection */
                if ($collection->count() == 0) {
                    /* @var $processing Helper_File_Processing */
                    $processing = new $modelName;
                    $processor = $processing->getProcessor();
                    $processor->plan($file)->getProcessing()->save();

                    $this->afterProccessingPlanned($item);

                    $this->getNotices()->ok($processor->getDesc() . ' выполняется');
                }
            }
        }

        /** @var Zend_Controller_Request_Http $req */
        $req = $this->getRequest();
        $this->redirect($req->getServer('HTTP_REFERER'));
    }

    /**
     * Хук
     * @param Helper_File_Manageable $item
     * @throws Exception
     */
    protected function afterProccessingPlanned(Helper_File_Manageable $item)
    {
        /* @var $item Doctrine_Record */
        if ($item instanceof Interface_LayerBase && $item->Documents && $item->Documents->exists()) {
            $layer = Helper_Layer::forge($item);
            $transfer = new Helper_Document_TextsTransfer($item->Documents);
            $document = $transfer->transfer($item, $layer->getTextFields());
            if ($document) $document->save();
        }
    }
}
