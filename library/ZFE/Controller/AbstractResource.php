<?php

/*
 * ZFE – платформа для построения редакторских интерфейсов.
 */

/**
 * Основной базовый контроллер приложения,
 * определяющий типовой порядок управления ресурсами.
 *
 * @category  ZFE
 */
abstract class ZFE_Controller_AbstractResource extends Controller_Abstract
{
    /**
     * Класс основной модели объекта.
     *
     * @var string
     */
    protected static $_modelName;

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

    public function init()
    {
        parent::init();

        if (static::$_enableViewAction) {
            array_unshift(self::$_controlTabs, [
                'action' => 'view',
                'title' => 'Просмотр',
                'onlyValid' => true,
            ]);

            /* Вариант на случай, если решим view называть Карточка в таких ресурсах
            foreach (self::$_controlTabs as $tabIndex => $tabOptions) {
                if ('edit' == $tabOptions['action']) {
                    $tabOptions['title'] = 'Редактирование';
                }
            }
            */
        }
    }

    /**
     * Выполняется до того, как диспетчером будет вызвано действие.
     *
     * @throws ZFE_Controller_Exception
     */
    public function preDispatch()
    {
        parent::preDispatch();

        $modelName = static::$_modelName;
        if (empty($modelName)) {
            throw new ZFE_Controller_Exception('В контроллере не указана модель. Необходимо определить свойство ' . static::class . '::$_modelName');
        }

        $acl = Zend_Registry::get('acl');
        $resource = $modelName::getControllerName();

        $this->view->modelName = $modelName;
        $this->view->listName = $modelName::$namePlural;
        $this->view->itemName = $modelName::$nameSingular;
        $this->view->readonly = static::$_readonly;
        $this->view->controlTabs = static::$_controlTabs;

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
     * Выполняется после того, как диспетчером будет вызвано действие.
     */
    public function postDispatch()
    {
        $this->_helper->abstractView();

        parent::postDispatch();
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

    use ZFE_Controller_AbstractResource_Index;
    use ZFE_Controller_AbstractResource_Edit;
    use ZFE_Controller_AbstractResource_Delete;
    use ZFE_Controller_AbstractResource_History;
    use ZFE_Controller_AbstractResource_Merge;
    use ZFE_Controller_AbstractResource_View;

    /**
     * Ajax-autocomplete модели.
     *
     * @throws Zend_Controller_Action_Exception
     */
    public function autocompleteAction()
    {
        if ( ! in_array('autocomplete', static::$_enableActions, true)) {
            throw new Zend_Controller_Action_Exception('Action "autocomplete" does not exist', 404);
        }

        $modelName = static::$_modelName;
        $array = $modelName::autocomplete($this->getAllParams());

        $this->_helper->json($array);
    }
}
