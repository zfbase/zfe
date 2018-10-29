<?php

/*
 * ZFE – платформа для построения редакторских интерфейсов.
 */

/**
 * Форма авторизации на входе в систему.
 *
 * @category  ZFE
 */
class ZFE_Form_Default_Login extends Zend_Form
{
    /**
     * Инициализировать форму.
     */
    public function init()
    {
        $this->addElement('text', 'login', [
            'placeholder' => 'Логин',
            'required' => true,
            'filters' => ['StringTrim'],
            'validators' => [
                ['NotEmpty', false, [
                    'messages' => [Zend_Validate_NotEmpty::IS_EMPTY => 'Вы не ввели логин'],
                ]],
            ],
            'decorators' => ['viewHelper'],
            'class' => 'form-control',
            'autofocus' => 'autofocus',
            'autocomplete' => 'off',
        ]);

        $this->addElement('password', 'password', [
            'placeholder' => 'Пароль',
            'required' => true,
            'filters' => ['StringTrim'],
            'validators' => [
                ['NotEmpty', false, [
                    'messages' => [Zend_Validate_NotEmpty::IS_EMPTY => 'Вы не ввели пароль'],
                ]],
            ],
            'decorators' => ['viewHelper'],
            'class' => 'form-control',
        ]);

        $this->addElement('checkbox', 'remember', [
            'checked' => 'checked',
            'decorators' => ['viewHelper'],
        ]);

        $this->addElement('submit', 'submit', [
            'label' => 'Войти',
            'decorators' => ['viewHelper'],
            'class' => 'btn btn-lg btn-primary btn-block',
        ]);
    }
}
