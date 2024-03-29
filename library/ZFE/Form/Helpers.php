<?php

/*
 * ZFE – платформа для построения редакторских интерфейсов.
 */

/**
 * Помощники форм.
 */
trait ZFE_Form_Helpers
{
    use ZFE_Form_Helpers_Templates;  // Коллекция стандартных элементов форм
    use ZFE_Form_Helpers_Generator;  // Генератор элементов форм по модели
    use ZFE_Form_Helpers_Frequent;   // Наиболее часто используемые элементы форм
    use ZfeFiles_Form_Helpers;       // Помощники элементов формы ZFE Files

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


        $this->ZfeFilesInitializePrefixes();


        $appNameSpace = config('appnamespace');

        $this->addPrefixPath(
            $appNameSpace . '_Form_Element',
            APPLICATION_PATH . '/forms/Element',
            'element'
        );

        $this->addElementPrefixPath(
            $appNameSpace . '_Form_Decorator',
            APPLICATION_PATH . '/forms/Decorator',
            'decorator'
        );

        $this->addElementPrefixPath(
            $appNameSpace . '_Validate',
            APPLICATION_PATH . '/Validate/',
            'validate'
        );
    }
}
