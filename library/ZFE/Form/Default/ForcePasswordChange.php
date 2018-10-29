<?php

/*
 * ZFE – платформа для построения редакторских интерфейсов.
 */

/**
 * Форма принудительной смены пароля.
 *
 * @category  ZFE
 */
class ZFE_Form_Default_ForcePasswordChange extends ZFE_Form
{
    public function init()
    {
        $this->setMethod('post');

        $this->addElement('password', 'password', [
            'label' => 'Текущий пароль',
            'required' => true,
            'filters' => ['StringTrim'],
        ]);

        $this->addElement('password', 'password_first', [
            'label' => 'Новый пароль',
            'required' => true,
            'filters' => ['StringTrim'],
        ]);

        $this->addElement('password', 'password_second', [
            'label' => 'Повторите пароль',
            'required' => true,
            'filters' => ['StringTrim'],
            'validators' => [
                ['Identical', true, ['token' => 'password_first']],
            ],
        ]);

        $this->addElement('submit', 'submit', [
            'label' => 'Сменить',
        ]);

        $this->addElement('hash', 'csrf');
    }
}
