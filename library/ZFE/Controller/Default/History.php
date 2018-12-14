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
     * Определяем запрос поиска по истории.
     *
     * @return ZFE_Query
     */
    protected function _getSearchQuery()
    {
        $modelName = static::$_modelName;

        $q = ZFE_Query::create()
            ->select('x.*, e.*')
            ->from($modelName . ' x, x.Editors e')
            ->groupBy('x.user_id, x.table_name, x.content_id, x.content_version, x.action_type')
            ->where('x.content_id > 0')
        ;

        $this->view->editor = $editor = $this->getParam('editor');
        if ( ! empty($editor)) {
            $q = $q->addWhere('x.user_id = ?', $editor);
        }

        $today = date('Y-m-d');

        $this->view->date_from = $dateFrom = $this->getParam('date_from');
        if (empty($dateFrom)) {
            $this->view->date_from = $dateFrom = $today . 'T00:00';
            $this->view->searchForm->getElement('date_from')->setValue($dateFrom);
        }
        $q = $q->addWhere('datetime_action >= ?', $dateFrom . ':00');

        $this->view->date_till = $dateTill = $this->getParam('date_till');
        if (empty($dateTill)) {
            $this->view->date_till = $dateTill = $today . 'T23:59';
            $this->view->searchForm->getElement('date_till')->setValue($dateTill);
        }
        $q = $q->addWhere('datetime_action <= ?', $dateTill . ':59');

        return $q;
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
            if ( ! (new $modelName() instanceof AbstractRecord)) {
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
