<?php

/*
 * ZFE – платформа для построения редакторских интерфейсов.
 */

/**
 * Отображает загруженный файл и элементы управления им для элемента загрузки файла.
 */
class ZFE_Form_Decorator_FileValue extends Zend_Form_Decorator_Abstract
{
    /**
     * Положение относительно контекста.
     *
     * @var string
     */
    protected $_placement = 'PREPEND';

    /**
     * Преобразовать контекст для отображения загруженных файлов.
     *
     * @param string $content
     *
     * @return string
     */
    public function render($content)
    {
        /** @var Zend_Form_Element $element */
        $element = $this->getElement();
        $id = $element->getName();

        if ('multiple' === $element->getAttrib('multiple')) {
            if (!count($files = $element->getFiles())) {
                return $content;
            }
        } else {
            if (null === ($file = $element->getFile())) {
                return $content;
            }

            $files = [$file];
        }

        $html = '';

        /** @var ZFE_File $file */
        foreach ($files as $file) {
            $html .= $this->_renderFile($file, $element->getAttrib('disabled'));
        }

        // если файл один и он уже есть, элемент выбора файла убираем под кнопку «заменить»
        if ('multiple' !== $element->getAttrib('multiple') && 1 === count($files)) {
            $btnReplace = '<a class="btn btn-link"'
                               . ' data-btn="replace"'
                               . ' data-current="#' . $id . '-current-file"'
                               . ' data-new-upload="#' . $id . '-new-upload"'
                               . '><span class="glyphicon glyphicon-repeat small"></span> Заменить</a>';
            $showHtml = '<span id="' . $id . '-current-file" class="formfile-current-file">' . $html . '</span>';
            $hideHtml = '<span id="' . $id . '-new-upload" class="hide">' . $content . '</span>';

            return $showHtml . $btnReplace . $hideHtml;
        }
        switch ($this->getPlacement()) {
                case self::APPEND:
                    return $content . $html;
                case self::PREPEND:
                    return $html . $content;
            }
    }

    protected function _renderFile(ZFE_File $file, $disabled = false)
    {
        $value = $file->getName();
        $class = 'help-block';

        if ($file->hasPreview()) {
            $value = '<a class="image" style="background-image:url(' . $file->getPreviewUrl() . ');"';
            if ($file->canDownload()) {
                $value .= ' href="' . $file->getDownloadUrl() . '" target="_blank"';
            }
            $value .= '></a>';

            $class .= ' image-uploaded';
        } else {
            if ($file->canDownload()) {
                $value = '<a href="' . $file->getDownloadUrl() . '" target="_blank">' . $value . '</a>';
            }

            if ($file->hasIcon()) {
                $value = '<i class="' . $file->getIconClass() . '"></i> ' . $value;
            }
        }

        if ($file->canDelete() && !$disabled) {
            if (!$file->hasPreview()) {
                $value .= ' &nbsp;';
            }
            $value .= '<a href="' . $file->getDeleteUrl() . '" class="text-danger">'
                    . '<i class="glyphicon glyphicon-remove"></i></a>';
        }

        return '<p class="' . $class . '">' . $value . '</p>';
    }
}
