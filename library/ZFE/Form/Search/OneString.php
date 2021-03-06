<?php

/*
 * ZFE – платформа для построения редакторских интерфейсов.
 */

/**
 * Однострочный поиск по записям модели для списка записей.
 */
class ZFE_Form_Search_OneString extends ZFE_Form_Inline
{
    public function init()
    {
        $this
            ->setMethod('POST')
            ->setAttrib('role', 'form')
            ->setAttrib('class', 'indexSearch')
        ;


        $this->addElement('text', 'term', [
            'filters' => [
                'StringTrim',
            ],
            'autofocus' => 'autofocus',
            'order' => 1,
            'style' => 'width: 350px;',
        ]);

        $this->addElement('submit', 'submit', [
            'label' => 'Искать',
            'class' => 'btn-default',
            'order' => 100,
        ]);
    }
}
