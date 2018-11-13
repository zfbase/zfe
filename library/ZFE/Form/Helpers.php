<?php

/*
 * ZFE – платформа для построения редакторских интерфейсов.
 */

/**
 * Помощники форм.
 *
 * @category  ZFE
 */
trait ZFE_Form_Helpers
{
    use ZFE_Form_Helpers_Templates,  // Коллекция стандартных элементов форм
        ZFE_Form_Helpers_Generator,  // Генератор элементов форм по модели
        ZFE_Form_Helpers_Frequent;   // Наиболее часто используемые элементы форм

    /**
     * Название модели, соответствующей форме
     * (используется для авто генераторов и определителей параметров элементов форм).
     *
     * @var string
     */
    protected $_modelName;

    /**
     * Подключаем дополнительные элементы.
     */
    protected function _initializePrefixes()
    {
        parent::_initializePrefixes();

        $this->addPrefixPath(
            'ZFE_Form_Element',
            'ZFE/Form/Element',
            'element'
        );

        $this->addElementPrefixPath(
            'ZFE_Form_Decorator',
            'ZFE/Form/Decorator',
            'decorator'
        );
        $this->addDisplayGroupPrefixPath(
            'ZFE_Form_Decorator',
            'ZFE/Form/Decorator'
        );

        $this->addElementPrefixPath(
            'ZFE_Validate',
            'ZFE/Validate/',
            'validate'
        );

        $this->addElementPrefixPath(
            'ZFE_Filter',
            'ZFE/Filter/',
            'filter'
        );

        $config = Zend_Registry::get('config');

        $this->addPrefixPath(
            $config->appnamespace . '_Form_Element',
            APPLICATION_PATH . '/forms/elements',
            'element'
        );

        $this->addElementPrefixPath(
            $config->appnamespace . '_Form_Decorator',
            $config->appnamespace . '/Form/Decorator',
            'decorator'
        );

        $this->addElementPrefixPath(
            $config->appnamespace . '_Validate',
            $config->appnamespace . '/Validate/',
            'validate'
        );
    }
}
