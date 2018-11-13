<?php

/*
 * ZFE – платформа для построения редакторских интерфейсов.
 */

/**
 * Форма поиска записей истории.
 */
class ZFE_Form_Default_Search_History extends ZFE_Form_Inline
{
    /**
     * Инициализировать форму.
     */
    public function init()
    {
        $this
            ->setMethod('POST')
            ->setAttrib('role', 'form')
            ->setAttrib('class', 'form-inline indexSearch')
            ->setAction('/history/index/')
        ;

        $this->addElement('select', 'editor', [
            'autofocus' => 'autofocus',
            'multiOptions' => ['Все редакторы'] + Editors::getKeyValueList(),
        ]);

        // @todo Установить взаимные ограничения на поля выбора интервала времени
        $this->addElement('dateTimeLocal', 'date_from');
        $this->addElement('dateTimeLocal', 'date_till');

        $this->addElement('submit', 'submit', [
            'label' => 'Искать',
            'class' => 'btn-default',
        ]);
    }
}
