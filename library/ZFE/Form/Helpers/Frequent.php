<?php

/*
 * ZFE – платформа для построения редакторских интерфейсов.
 */

/**
 * Наиболее часто используемые элементы форм.
 *
 * Зависимости:
 * # ZFE_Form_Helpers
 * # ZFE_Form_Helpers_Templates
 *
 * @category  ZFE
 */
trait ZFE_Form_Helpers_Frequent
{
    /**
     * Добавить стандартное скрытое поле ID записи.
     *
     * @return Zend_Form
     */
    public function addElementId()
    {
        return $this->addElement('hidden', 'id', [
            'decorators' => ['viewHelper'],
        ]);
    }

    /**
     * Добавить стандартное поле название записи.
     *
     * @return Zend_Form
     */
    public function addElementTitle()
    {
        return $this->addTextElement('title', [
            'required' => true,
        ]);
    }

    /**
     * Добавить стандартное поле статуса записи.
     *
     * @return Zend_Form
     */
    public function addElementStatus()
    {
        return $this->addSelectElement('status', [
            'required' => true,
        ]);
    }

    /**
     * Добавить стандартное поле комментария к записи.
     *
     * @return Zend_Form
     */
    public function addElementComment()
    {
        return $this->addTextareaElement('comment');
    }

    /**
     * Добавить стандартную кнопку отправки формы «Сохранить».
     *
     * @return Zend_Form
     */
    public function addSubmitElement($label = 'Сохранить')
    {
        return $this->addElement('submit', 'submit', [
            'label' => $label,
            'class' => 'btn-primary',
        ]);
    }
}
