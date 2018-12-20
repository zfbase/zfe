<?php

/*
 * ZFE – платформа для построения редакторских интерфейсов.
 */

/**
 * Управление редакторами.
 */
class ZFE_Controller_Default_Editors extends Controller_AbstractResource
{
    /**
     * Класс основной модели объекта.
     *
     * @var string
     */
    protected static $_modelName = 'Editors';

    /**
     * Имя класса формы для изменения объекта.
     *
     * @var string
     */
    protected static $_editFormName = 'ZFE_Form_Default_Edit_Editor';

    /**
     * Вкладки управления записью.
     *
     * @var string
     */
    protected static $_controlTabs = [
        [
            'action' => 'edit',
            'title' => 'Карточка',
            'onlyValid' => true,
        ],
        [
            'action' => 'access',
            'title' => 'Права',
            'onlyRegistered' => true,
        ],
        [
            'action' => 'recent',
            'title' => 'Последние действия',
            'onlyRegistered' => true,
        ],
        [
            'action' => 'history',
            'title' => 'История',
            'class' => 'pull-right',
            'onlyRegistered' => true,
            'onlyValid' => true,
        ],
    ];

    /**
     * Страница настройки прав.
     */
    public function accessAction()
    {
        $modelName = static::$_modelName;
        $itemId = $this->getParam('id', 0);
        $item = $this->view->item = $modelName::find($itemId);

        if (empty($item)) {
            $this->abort(404, 'Редактор не найден');
        }
    }

    /**
     * Страница с информацией о последней активности пользователя.
     */
    public function recentAction()
    {
        $modelName = static::$_modelName;
        $itemId = $this->getParam('id', 0);
        $item = $this->view->item = $modelName::find($itemId);

        if (empty($item)) {
            $this->abort(404, 'Редактор не найден');
        }
    }
}
