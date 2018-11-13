<?php

/*
 * ZFE – платформа для построения редакторских интерфейсов.
 */

/**
 * Форма редактирования собственного профиля пользователем.
 */
class ZFE_Form_Default_Profile extends ZFE_Form_Horizontal
{
    /**
     * Модель сведений о пользователях.
     *
     * @var string
     */
    protected $_modelName = 'Editors';

    /**
     * Доступные для редактирования поля.
     *
     * @var array [ fieldName => [ formElementName, formElementOptions ]]
     */
    protected $_fields = [
        'second_name' => ['addTextElement', ['required' => true]],
        'first_name' =>  ['addTextElement', ['required' => true]],
        'middle_name' => ['addTextElement', ['required' => true]],
        'email' =>       ['addEmailElement'],
        'phone' =>       ['addTelElement'],
    ];

    public function init()
    {
        $this->_addInfoElements();
        $this->_addPasswordsElements();

        $this->addElement('submit', 'submit', [
            'label' => 'Сохранить',
        ]);
    }

    protected function _addInfoElements()
    {
        $table = Doctrine_Core::getTable($this->_modelName);
        foreach ($this->_fields as $fieldName => $fieldOptions) {
            if ($table->hasColumn($fieldName)) {
                $method = empty($fieldOptions[0])
                        ? 'addElementForColumn'
                        : $fieldOptions[0];
                $options = empty($fieldOptions[1])
                         ? []
                         : $fieldOptions[1];
                $this->{$method}($fieldName, $options);
            }
        }
    }

    protected function _addPasswordsElements()
    {
        $this->addElement('password', 'password_new', [
            'label' => 'Новый пароль',
            'description' => 'Заполните, если хотите сменить пароль',
            'required' => false,
        ]);

        $this->addElement('password', 'password_new2', [
            'label' => 'Повторите пароль',
            'description' => 'Заполните, если хотите сменить пароль',
            'required' => false,
            'validators' => [
                ['Identical', false, ['token' => 'password_new']],
            ],
        ]);

        $this->addElement('password', 'password', [
            'label' => 'Текущий пароль',
            'description' => 'Для сохранения любых изменений укажите свой текущий пароль',
            'required' => true,
        ]);
    }

    public function isValid($data)
    {
        // Да, костыль, но без него не работает, а делать в ручную очень многословно
        if ( ! empty($data['password_new'])) {
            $this->getElement('password_new2')->setRequired(true);
        }

        return parent::isValid($data);
    }
}
