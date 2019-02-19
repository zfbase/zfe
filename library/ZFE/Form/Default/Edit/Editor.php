<?php

/*
 * ZFE – платформа для построения редакторских интерфейсов.
 */

/**
 * Форма редактирования пользователей.
 */
class ZFE_Form_Default_Edit_Editor extends ZFE_Form_Edit_AutoGeneration
{
    /**
     * Название модели, соответствующей форме.
     *
     * @var string
     */
    protected $_modelName = 'Editors';

    /**
     * Массив соответствий полей и шаблонов элементов форм
     *
     * @var array|string[]
     */
    protected $_fieldMethods = [
        'second_name' => 'addTextElement',
        'first_name' => 'addTextElement',
        'middle_name' => 'addTextElement',
        'email' => 'addEmailElement',
        'phone' => 'addTelElement',
        'login' => 'addTextElement',
        'password' => 'addPasswordElement',
        'department' => 'addElementForColumn',
        'role' => 'addSelectElement',
        'status' => 'addSelectElement',
        'comment' => 'addTextareaElement',
    ];

    public function init()
    {
        parent::init();

        $this->getElement('role')->setRequired(true);
    }
}
