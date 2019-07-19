<?php

/*
 * ZFE – платформа для построения редакторских интерфейсов.
 */

/**
 * Просмотр истории.
 */
class ZFE_Controller_Default_History extends Controller_AbstractResource
{
    /**
     * Возможность добавления объектов.
     *
     * @var bool
     */
    protected static $_canCreate = false;

    /**
     * Возможность удаления объектов.
     *
     * @var bool
     */
    protected static $_canDelete = false;

    /**
     * Возможность отката объектов к предыдущим версиям
     *
     * @var bool
     */
    protected static $_canRestore = false;

    /**
     * Класс основной модели объекта.
     *
     * @var string
     */
    protected static $_modelName = 'History';

    /**
     * Включенные стандартные экшены.
     *
     * @var array
     */
    protected static $_enableActions = [
        'index',
    ];

    /**
     * Форма поиска по записям модели.
     *
     * @var string
     */
    protected static $_searchFormName = 'Application_Form_Search_History';

    /**
     * {@inheritdoc}
     */
    public static function getSearcher()
    {
        if (!static::$_searcher) {
            static::$_searcher = new ZFE_Searcher_Doctrine(static::$_modelName);
            static::$_searcher->setQueryBuilder(new ZFE_Searcher_QueryBuilder_HistoryDoctrine(static::$_modelName));
        }
        return static::$_searcher;
    }

    /**
     * Страница сравнения версий записи.
     */
    public function diffAction()
    {
        $this->_helper->postToGet();

        $this->view->resource = $resource = $this->getParam('resource');

        try {  // Если класс не удалось создать, либо он не потомок абстрактной модели, то что-то тут не то
            $this->view->modelName = $modelName = AbstractRecord::getModelNameByTableName($resource);
            if (!(new $modelName() instanceof AbstractRecord)) {
                throw new ZFE_Controller_Exception();
            }
        } catch (Throwable $ex) {
            $this->abort(500, 'Не корректный класс записи');
        }

        $this->view->resource = $modelName::getControllerName();

        $this->view->curItem = $modelName::hardFind($this->getParam('id', 0));
        if (empty($this->view->curItem)) {
            $this->abort(404, 'Запись не найдена');
        }

        parent::diffAction();
    }
}
