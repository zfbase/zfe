<?php

/*
 * ZFE – платформа для построения редакторских интерфейсов.
 */

/**
 * Стандартные обработчики создания и редактирования записи.
 *
 * @category  ZFE
 */
trait ZFE_Controller_AbstractResource_Edit
{
    /**
     * Имя класса формы для изменения объекта.
     *
     * @var string
     */
    protected static $_editFormName = 'ZFE_Form_Edit_AutoGeneration';

    /**
     * Возможность добавления объектов.
     *
     * @var bool
     */
    protected static $_canCreate = true;

    /**
     * Вкладки управления записью.
     *
     * Параметры вкладки:
     * * action          – экшен вкладки
     * * params          – параметры запроса
     * * title           – заголовок вкладки
     * * class           – класс элемента li вкладки
     * * onlyRegistered  – только для зарегистрированных (если есть id), по умолчанию false
     * * onlyValid       - только не удаленные (deleted != 0), по умолчанию false
     *
     * @var array
     */
    protected static $_controlTabs = [
        [
            'action' => 'edit',
            'title' => 'Карточка',
        ],
        [
            'action' => 'history',
            'title' => 'История',
            'class' => 'pull-right',
            'onlyRegistered' => true,
        ],
    ];

    /**
     * Редактор записи.
     *
     * @param bool|string $redirectUrl адрес для перенаправления в случае успеха; если адрес равен FALSE, то перенаправление не произойдет
     * @param array       $formOptions конфигурация формы редактирования (по умолчанию содержит класс редактируемой записи
     *
     * @throws Zend_Controller_Action_Exception
     * @throws ZFE_Controller_Exception
     *
     * @return bool|void В случае отсутствия перенаправления и успешного сохранения, возвращает TRUE, в остальных случаях NULL
     */
    public function editAction($redirectUrl = null, array $formOptions = [])
    {
        if ( ! in_array('edit', static::$_enableActions, true)) {
            throw new Zend_Controller_Action_Exception('Action "edit" does not exist', 404);
        }

        if ( ! array_key_exists('modelName', $formOptions)) {
            $formOptions['modelName'] = static::$_modelName;
        }

        $formName = static::$_editFormName;
        if ( ! ($this->view->form instanceof Zend_Form)) {
            if ( ! empty($formName) && is_string($formName)) {
                $this->view->form = new $formName($formOptions);
            } else {
                throw new ZFE_Controller_Exception('Некорректная форма', 501);
            }
        }
        if (static::$_readonly) {
            $this->view->form->setDisabled(true);
        }
        $form = $this->view->form; /** @var $form ZFE_Form_Horizontal */
        $modelName = static::$_modelName;
        if ( ! ($this->view->item instanceof Doctrine_Record)) {
            if ( ! static::$_canCreate && ! $this->hasParam('id')) {
                throw new ZFE_Controller_Exception('Невозможно создать ' . mb_strtolower($modelName::$nameSingular) . ': доступ запрещен', 403);
            }
            $itemId = (int) $this->getParam('id');
            $this->view->item = $itemId > 0
                ? $modelName::hardFind($itemId)
                : new $modelName();
        }
        $item = $this->view->item; /** @var $item AbstractRecord */
        if (empty($item)) {
            throw new Zend_Controller_Action_Exception($modelName::decline('%s не найден.', '%s не найдена.', '%s не найдено.'), 404);
        }

        if ($this->_request->isPost() && ! $item->isDeleted() && ! static::$_readonly) {
            $post = $this->_request->getPost();

            $this->_beforeValid($item, $form, $post);

            $form->setDisabledToIgnore();
            if ($form->isValidPartial($post)) {
                try {
                    $item->fromArray($form->getValues(), false);

                    $this->_beforeSave($item, $form, $post);
                    $item->save();
                    $this->_afterSave($item, $form, $post);

                    if ($item->exists()) {
                        $msg = $modelName::decline('%s успешно сохранен.', '%s успешно сохранена.', '%s успешно сохранено.');
                        $this->_helper->Notices->ok($msg);

                        if (false !== $redirectUrl) {
                            if (null === $redirectUrl) {
                                $redirectUrl = $item->getEditUrl() . $this->view->hopsHistory()->getSideHash('?');
                            }
                            $this->_redirect($redirectUrl);
                        } else {
                            return true;
                        }
                    } else {
                        throw new ZFE_Controller_Exception('После сохранения в записи отсутствует ID.', 500);
                    }
                } catch (Exception $ex) {
                    $msg = 'Сохранить не удалось';
                    if (Zend_Registry::get('user')->noticeDetails) {
                        $msg .= ': ' . $ex->getMessage();
                    }
                    $this->_helper->Notices->err($msg);

                    if ($log = Zend_Registry::get('log')) {
                        $log->log(
                            $ex->getMessage(),
                            Zend_Log::ERR,
                            [
                                'errno' => $ex->getCode(),
                                'file' => $ex->getFile(),
                                'line' => $ex->getLine(),
                                'context' => $ex->getTraceAsString(),
                            ]
                        );
                    }
                }
            }

            // двойное заполнение необходимо для заполнения disabled-полей
            $form->populate($item->toArray() ?: []);
            $form->populate($post);
        } elseif ($item->exists()) {
            $form->populate($item->toArray() ?: []);
        } else {
            $form->populate($this->getAllParams());
        }

        // Если форма поддерживает автоматическую установку значений
        // элементов загрузки файлов, то установим таким элементам значения.
        // От POST не зависит, т.к. всегда отображаем только то, что уже загружено
        // и прикреплено. Загруженные, но не привязанные файлы пока теряем.
        if (method_exists($form, 'populateFiles')) {
            $form->populateFiles($item);
        }

        $form->removeDecorator('Form');
    }

    /**
     * Функция, выполняющаяся перед валидацией данных формы редактирования записи.
     *
     * @param Doctrine_Record $item
     * @param Zend_Form       $form
     * @param array           $post
     */
    protected function _beforeValid(Doctrine_Record $item, Zend_Form $form, array $post)
    {
    }

    /**
     * Функция, выполняющаяся перед сохранением изменений записи.
     *
     * @param Doctrine_Record $item
     * @param Zend_Form       $form
     * @param array           $post
     */
    protected function _beforeSave(Doctrine_Record $item, Zend_Form $form, array $post)
    {
    }

    /**
     * Функция, выполняющаяся после сохранения изменений записи.
     *
     * @param Doctrine_Record $item
     * @param Zend_Form       $form
     * @param array           $post
     */
    protected function _afterSave(Doctrine_Record $item, Zend_Form $form, array $post)
    {
    }

    /**
     * Работает с модальным окном редактирования записей.
     */
    public function editModalAction()
    {
        $saved = $this->editAction(false);
        $this->_helper->layout()->disableLayout();
        if (true === $saved) {
            $model = static::$_modelName;
            $item = $this->view->item;
            $data = $model::autocompleteItemToArray($item) + [
                'id' => $item->id,
                'title' => $item->getTitle(),
            ];
            // скрыть оповещение об успешном сохранении
            $this->view->getHelper('alerts')->alerts();
            $this->_helper->json($data);
        }
    }
}
