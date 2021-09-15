<?php

/*
 * ZFE – платформа для построения редакторских интерфейсов.
 */

/**
 * Помощник для поисковых форм.
 */
trait ZFE_Form_Search_Helpers
{
    public function populate(array $values)
    {
        $values = $this->postPopulateHelper($values);
        $values = $this->deletedHelper($values);

        return parent::populate($values);
    }

    /**
     * Помощник для работы с автокомпликтными элементами в поиске.
     *
     * @param array $values
     *
     * @return array
     */
    public function postPopulateHelper(array $values)
    {
        foreach ($this->getElements() as $name => $element) {
            if ($element instanceof ZFE_Form_Element_Autocomplete) {
                foreach ($values as $key => $value) {
                    $pattern = "/^{$name}_(.+)/";
                    if (preg_match($pattern, $key, $mathes)) {
                        $values[$name][$mathes[1]] = $value;
                        unset($values[$key]);
                    }
                }
            }

            if ($element instanceof ZFE_Form_Element_MultiAutocomplete) {
                foreach ($values as $key => $value) {
                    $pattern = "/^{$name}_([0-9]+)_(.+)/";
                    if (preg_match($pattern, $key, $mathes)) {
                        $values[$name][$mathes[1]][$mathes[2]] = $value;
                        unset($values[$key]);
                    }
                }
            }
        }

        return $values;
    }

    /**
     * Помощник для поддержки поиска по корзине.
     *
     * @param array $values
     *
     * @return array
     */
    public function deletedHelper(array $values)
    {
        if ($this->getElement('deleted') === null) {
            $this->addElement('hidden', 'deleted');
        }
        $this->getElement('deleted')->setValue($values['deleted'] ?? false);
        unset($values['deleted']);
        return $values;
    }
}
