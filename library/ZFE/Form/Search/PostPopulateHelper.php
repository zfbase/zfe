<?php

/*
 * ZFE – платформа для построения редакторских интерфейсов.
 */

/**
 * Помощник для работы с автокомпликтными элементами в поиске.
 */
trait ZFE_Form_Search_PostPopulateHelper
{
    public function populate(array $values)
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

        return parent::populate($values);
    }
}
