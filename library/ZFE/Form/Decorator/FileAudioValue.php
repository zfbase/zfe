<?php

/*
 * ZFE – платформа для построения редакторских интерфейсов.
 */

class ZFE_Form_Decorator_FileAudioValue extends ZFE_Form_Decorator_FileValue
{
    protected function _renderFile(ZFE_File $file, $disabled = false)
    {
        $actionLinks = [];

        if ($file->canDownload()) {
            $actionLinks[] = $this->_renderActionLink($file->getDownloadUrl(), $file->getDownloadLabel());
        }

        if ($file->canView()) {
            $actionLinks[] = $this->_renderActionLink($file->getViewUrl(), $file->getViewLabel());
        }

        if ($file->canEdit()) {
            $actionLinks[] = $this->_renderActionLink($file->getEditUrl(), $file->getEditLabel());
        }

        if ($file->canDelete()) {
            $actionLinks[] = $this->_renderActionLink($file->getDeleteUrl(), $file->getDeleteLabel(), 'zfe-audio-delete');
        }

        return '<audio '
            . 'src="' . $file->getPreviewUrl() . '" '
            . 'class="zfe-audio" '
            . 'controls>' . implode('', $actionLinks) . '</audio>';
    }

    protected function _renderActionLink($url, $label, $class = null)
    {
        return '<a href="' . $url . '" class="zfe-audio-link ' . ($class ?: '') . '" target="_blank">' . $label . '</a>';
    }
}
