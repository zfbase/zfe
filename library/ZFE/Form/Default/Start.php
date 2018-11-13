<?php

/*
 * ZFE – платформа для построения редакторских интерфейсов.
 */

/**
 * Форма для создания первого пользователя системы.
 */
class ZFE_Form_Default_Start extends ZFE_Form_Default_Edit_Editor
{
    /**
     * Инициализировать форму.
     */
    public function init()
    {
        parent::init();

        $this->getElement('role')->setAttrib('disabled', true);
        $this->getElement('status')->setAttrib('disabled', true);
        $this->addSubmitElement();
    }
}
