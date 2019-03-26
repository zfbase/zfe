<?php

/*
 * ZFE – платформа для построения редакторских интерфейсов.
 */

/**
 * Основной базовый контроллер приложения,
 * определяющий типовой порядок управления ресурсами.
 */
abstract class ZFE_Controller_AbstractResource extends Controller_Abstract
{
    /**
     * Класс модели основного объекта.
     *
     * @var string
     */
    protected static $_modelName;

    /**
     * Класс главной поисковой формы (indexAction).
     *
     * @var string
     */
    protected static $_searchFormName = 'ZFE_Form_Search_Default';

    /**
     * Класс поискового движка.
     *
     * @var string
     */
    protected static $_searcherName = 'ZFE_Searcher_Default';

    /**
     * Включенные стандартные экшены.
     *
     * @var array
     */
    protected static $_enableActions = [
        'index',
        'edit',
        'delete',
        'undelete',
        'autocomplete',
        'history',
        'merge',
        'search-duplicates',
    ];

    /**
     * Запрет редактирования объектов.
     *
     * @var bool
     */
    protected static $_readonly = false;

    /**
     * Поисковой движок основной модели.
     *
     * @var ZFE_Searcher_Interface
     */
    protected static $_searcher;

    /**
     * Получить настроенный поисковой движок основной модели.
     *
     * @return ZFE_Searcher_Interface
     */
    public static function getSearcher()
    {
        if ( ! static::$_searcher) {
            static::$_searcher = new static::$_searcherName(static::$_modelName);
        }
        return static::$_searcher;
    }

    /**
     * {@inheritdoc}
     */
    public function init()
    {
        parent::init();

        if (static::$_enableViewAction) {
            $this->view->controlTabs()
                ->addTab('view', [
                    'action' => 'view',
                    'title' => 'Просмотр',
                    'onlyRegistered' => true,
                    'order' => 0,
                ])
                ->modifyTab('edit', ['title' => 'Редактирование'])
            ;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function preDispatch()
    {
        parent::preDispatch();

        $modelName = static::$_modelName;
        if (empty($modelName)) {
            $this->abort(500, 'В контроллере не указана модель. Необходимо определить свойство ' . static::class . '::$_modelName');
        }

        $acl = Zend_Registry::get('acl');
        $resource = $modelName::getControllerName();

        $this->view->title($modelName::$namePlural);

        $this->view->modelName = $modelName;
        $this->view->listName = $modelName::$namePlural;
        $this->view->itemName = $modelName::$nameSingular;
        $this->view->readonly = static::$_readonly;

        $this->view->canMerge = static::$_canMerge
                             && $modelName::isMergeable()
                             && $acl->isAllowedMe($resource, 'merge')
                             && ! static::$_readonly;

        $this->view->canCreate = static::$_canCreate
                              && $acl->isAllowedMe($resource, 'edit')
                              && ! static::$_readonly;

        $this->view->canDelete = static::$_canDelete
                              && $modelName::isRemovable()
                              && $acl->isAllowedMe($resource, 'delete')
                              && ! static::$_readonly;

        $this->view->canRestore = static::$_canRestore
                               && $modelName::isRemovable()
                               && $acl->isAllowedMe($resource, 'restore')
                               && ! static::$_readonly;
    }

    /**
     * {@inheritdoc}
     */
    public function postDispatch()
    {
        $this->_helper->abstractView();

        parent::postDispatch();
    }

    /**
     * Главная страница модели с просмотром перечня и поиском по модели.
     *
     * @return ZFE_Searcher_Interface
     */
    public function indexAction()
    {
        if ( ! in_array('index', static::$_enableActions, true)) {
            $this->abort(404);
        }

        $this->_helper->postToGet();

        $rowParams = $this->getAllParams();
        $deleted = $this->getParam('deleted');

        if ( ! empty(static::$_searchFormName)) {
            $searchForm = new static::$_searchFormName();
            if ('1' === $deleted) {
                $searchForm->addElement('hidden', 'deleted', ['value' => 1]);
            }
            $searchForm->setAction((static::$_modelName)::getIndexUrl());
            $searchForm->populate($rowParams);
            $this->view->searchForm = $searchForm;

            $params = array_merge($rowParams, $searchForm->getValues());
        } else {
            $params = $rowParams;
        }

        $this->view->items = static::getSearcher()->search($params);
        $this->view->deleted = $deleted;
    }

    /**
     * Получить имя основной модели контроллера.
     *
     * @return string
     */
    public static function getModelName()
    {
        return static::$_modelName;
    }

    use ZFE_Controller_AbstractResource_Edit;
    use ZFE_Controller_AbstractResource_Delete;
    use ZFE_Controller_AbstractResource_History;
    use ZFE_Controller_AbstractResource_Merge;
    use ZFE_Controller_AbstractResource_View;

    /**
     * Ajax-autocomplete модели.
     */
    public function autocompleteAction()
    {
        if ( ! in_array('autocomplete', static::$_enableActions, true)) {
            $this->abort(404, 'Action "autocomplete" does not exist');
        }

        $modelName = static::$_modelName;
        $array = $modelName::autocomplete($this->getAllParams());

        $this->_helper->json($array);
    }
}
