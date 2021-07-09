<?php

/*
 * ZFE – платформа для построения редакторских интерфейсов.
 */

/**
 * Стандартные обработчики создания и редактирования записи.
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
     * Редактор записи.
     *
     * @param bool|string $redirectUrl адрес для перенаправления в случае успеха;
     *                                 если адрес равен FALSE, то перенаправление не произойдет;
     *                                 в адресе можно единоразово использовать %d для вставки id записи
     * @param array       $formOptions конфигурация формы редактирования (по умолчанию содержит класс редактируемой записи
     *
     * @return bool|void В случае отсутствия перенаправления и успешного сохранения, возвращает TRUE, в остальных случаях NULL
     */
    public function editAction($redirectUrl = null, array $formOptions = [])
    {
        if (!in_array('edit', static::$_enableActions)) {
            $this->abort(404);
        }

        $modelName = static::$_modelName;
        $form = $this->_initEditForm($formOptions);
        $item = $this->_initEditItem();

        if (empty($item)) {
            $this->abort(404, $modelName::decline('%s не найден.', '%s не найдена.', '%s не найдено.'));
        }

        if (static::$_readonly || $item->isDeleted()) {
            $this->view->form->setDisabled(true);
        }

        if ($this->_request->isPost() && !static::$_readonly) {
            if (!$item->isDeleted()) {
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
                            $this->success(
                                $modelName::decline('%s успешно сохранен.', '%s успешно сохранена.', '%s успешно сохранено.'),
                                false !== $redirectUrl
                            );

                            if (false !== $redirectUrl) {
                                $this->redirect((null === $redirectUrl)
                                    ? $item->getEditUrl() . $this->view->hopsHistory()->getSideHash('?')
                                    : sprintf($redirectUrl, $item->id));
                            } else {
                                return true;
                            }
                        } else {
                            $this->abort(500, 'После сохранения в записи отсутствует ID.');
                        }
                    } catch (Throwable $ex) {
                        ZFE_Utilities::popupException($ex);

                        $this->error('Сохранить не удалось', $ex);
                    }
                }
            } else {
                $this->error('Редактирование удаленных записей запрещено.');
                $post = [];
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
     * Подготовить форму.
     *
     * @return ZFE_Form
     */
    protected function _initEditForm(array $options)
    {
        if (!key_exists('modelName', $options)) {
            $options['modelName'] = static::$_modelName;
        }

        if (!($this->view->form instanceof Zend_Form)) {
            $formName = static::$_editFormName;
            if (!empty($formName) && is_string($formName)) {
                $this->view->form = new $formName($options);
            } else {
                $this->abort(500, 'Некорректная форма');
            }
        }

        return $this->view->form;
    }

    /**
     * Подготовить запись.
     *
     * @return AbstractRecord
     */
    protected function _initEditItem()
    {
        $modelName = static::$_modelName;

        if (!($this->view->item instanceof Doctrine_Record)) {
            if (!static::$_canCreate && !$this->hasParam('id')) {
                $this->abort(403, 'Невозможно создать ' . mb_strtolower($modelName::$nameSingular) . ': доступ запрещен');
            }
            $itemId = (int) $this->getParam('id');
            $this->view->item = $itemId > 0
                ? $modelName::hardFind($itemId)
                : new $modelName();
        }

        return $this->view->item;
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
            $item = $this->view->item;
            $data = (static::$_modelName)::autocompleteItemToArray($item) + [
                'id' => $item->id,
                'title' => $item->getTitle(),
            ];
            // скрыть оповещение об успешном сохранении
            $this->view->getHelper('alerts')->alerts();
            $this->_helper->json($data);
        }
    }
}
