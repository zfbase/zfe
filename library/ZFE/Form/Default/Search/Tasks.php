<?php

/*
 * ZFE – платформа для построения редакторских интерфейсов.
 */

/**
 * Форма поиска отложенных задач.
 */
class ZFE_Form_Default_Search_Tasks extends ZFE_Form_Search_AbstractInline
{
    protected $_modelName = Tasks::class;

    /**
     * Инициализировать форму.
     */
    public function init()
    {
        $this
            ->setMethod('POST')
            ->setAttrib('role', 'form')
            ->setAttrib('class', 'indexSearch')
            ->setAction('/tasks/index/')
        ;

        $this->addNumberElement('id');

        $this->addElement('static', 'or', ['value' => 'или']);

        $this->addElement('select', 'search', [
            'multiOptions' => [
                'performed' => 'исполняются',
                'waiting' => 'в очереди',
                'failed' => 'завершились с ошибкой',
                'canceled' => 'отмененные',
                'success' => 'завершились успешно',
                'all' => 'все',
            ],
            'addon_prepend' => 'Фильтр',
            'value' => 'performed',
        ]);

        $performerCodes = array_keys(ZFE_Tasks_Manager::getInstance()->getPerformers(false));
        $performerOptions = array_combine(
            array_map(fn ($code) => str_replace('/', '_', $code), $performerCodes),
            $performerCodes);
        $this->addElement('select', 'performer', [
            'label' => Tasks::getFieldName('performer_code'),
            'multiOptions' => [null => 'все'] + $performerOptions,
        ]);

        $this->addNumberElement('related_id');

        $this->addElement('submit', 'submit', [
            'label' => 'Искать',
            'class' => 'btn-default',
        ]);
    }
}
