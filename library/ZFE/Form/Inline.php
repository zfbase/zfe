<?php

/*
 * ZFE – платформа для построения редакторских интерфейсов.
 */

/**
 * Базовая строковая форма ZFE.
 *
 * @category  ZFE
 */
class ZFE_Form_Inline extends Twitter_Bootstrap3_Form_Inline
{
    use Application_Form_Helpers;
    use Application_Form_Extensions;

    /**
     * Отрисовать форму.
     *
     * Для элементов, у которых не указан placeholder, в placeholder будет указан label.
     * Что бы placholder не был указан, необходимо указать его равным FALSE.
     *
     * @param Zend_View_Interface $view
     *
     * @return string
     */
    public function render(Zend_View_Interface $view = null)
    {
        $this->prepare();

        return parent::render();
    }

    /**
     * Подготовить форму к отрисовке.
     */
    public function prepare()
    {
        foreach ($this->getElements() as $element) { /** @var $element Zend_Form_Element */
            $placeholder = $element->getAttrib('placeholder');
            if (empty($placeholder) && false !== $placeholder) {
                $element->setAttrib('placeholder', $element->getLabel());
            }
        }
    }
}
