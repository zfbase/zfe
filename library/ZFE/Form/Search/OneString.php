<?php

/*
 * ZFE – платформа для построения редакторских интерфейсов.
 */

/**
 * Однострочный поиск по записям модели для списка записей.
 */
class ZFE_Form_Search_OneString extends ZFE_Form_Inline
{
    use ZFE_Form_Search_Helpers;

    public function init()
    {
        $this
            ->setMethod('POST')
            ->setAttrib('role', 'form')
            ->setAttrib('class', 'indexSearch oneStringSearch')
        ;

        $this->addElement('text', 'term', [
            'filters' => [
                'StringTrim',
            ],
            'autofocus' => 'autofocus',
            'order' => 1,
        ]);

        $this->addElement('submit', 'submit', [
            'label' => 'Искать',
            'class' => 'btn-default',
            'order' => 100,
        ]);
    }
}
