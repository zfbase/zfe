<?php

/*
 * ZFE – платформа для построения редакторских интерфейсов.
 */

/**
 * Помощник для работы с автокомпликтными элементами в поиске.
 * 
 * @deprecated 1.35.23
 */
trait ZFE_Form_Search_PostPopulateHelper
{
    public function populate(array $values)
    {
        trigger_error(
            'Трейт ZFE_Form_Search_PostPopulateHelper устарел. Используйте современный трейт ZFE_Form_Search_Helpers.',
            E_USER_DEPRECATED);

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
