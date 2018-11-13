<?php

/*
 * ZFE – платформа для построения редакторских интерфейсов.
 */

/**
 * Стандартный поиск по записям модели для списка записей.
 */
class ZFE_Form_Search_Default extends ZFE_Form_Search_AbstractInline
{
    /**
     * Инициализировать форму.
     */
    public function init()
    {
        $request = Zend_Controller_Front::getInstance()->getRequest();
        $controllerName = $request->getControllerName();
        $actionName = $request->getActionName();

        $this
            ->setMethod('POST')
            ->setAttrib('role', 'form')
            ->setAttrib('class', 'form-inline indexSearch')
            ->setAction('/' . $controllerName . '/' . $actionName . '/')
        ;

        $controllerClass = implode('', array_map('ucfirst', explode('-', $controllerName))) . 'Controller';
        $modelName = $controllerClass::getModelName();
        $this->addElement('text', 'title', [
            'label' => $modelName::getFieldName('title'),
            'filters' => [
                'StringTrim',
            ],
            'autofocus' => 'autofocus',
        ]);

        $this->addElement('submit', 'submit', [
            'label' => 'Искать',
            'class' => 'btn-default',
        ]);
    }
}
