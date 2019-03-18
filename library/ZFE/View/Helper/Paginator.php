<?php

/*
 * ZFE – платформа для построения редакторских интерфейсов.
 */

/**
 * Помощник отображения пагинатора.
 *
 * При необходимости добавления
 */
class ZFE_View_Helper_Paginator extends Zend_View_Helper_Abstract
{
    /**
     * Одиночка Doctrine_Pager.
     *
     * @var Doctrine_Pager
     */
    private $_pager;

    /**
     * Одиночка Doctrine_Pager_Layout.
     *
     * @var Doctrine_Pager_Layout
     */
    private $_layout;

    /**
     * Шаблон страницы (ссылки).
     *
     * @var string
     */
    private $_template = '<li><a href="{%url}">{%page}</a></li>';

    /**
     * Шаблон выбранной страницы (ссылки).
     *
     * @var string
     */
    private $_selectedTemplate = '<li class="active"><a href="{%url}">{%page}</a></li>';

    /**
     * Разделитель страниц (ссылок).
     *
     * @var string
     */
    private $_separatorTemplate = '';

    /**
     * Начало контейнера.
     *
     * @var string
     */
    private $_containerPrefix = '<ul class="pull-right pagination">';

    /**
     * Окончание контейнера.
     *
     * @var string
     */
    private $_containerPostfix = '</ul>';

    /**
     * Собрать пагинатор
     *
     * @param array $options опции пагинатора:
     *                       style = 'sliding' (default) | 'jumping' - способ скрытия лишних ссылок на страницы
     *                       chunk = 10 - сколько одновременно показывать ссылок на страницы
     *                       template - шаблон страницы (ссылки)
     *                       selectedTemplate - шаблон выбранной страницы (ссылки)
     *                       separatorTemplate - разделитель страниц (ссылок)
     *                       containerPrefix - начало контейнера
     *                       containerPostfix - окончание контейнера
     *
     * @return string
     */
    public function paginator(array $options = [])
    {
        $pConfig = Zend_Registry::get('config')->view->paginator;
        $default = [
            'chunk' => isset($pConfig->chunk) ? $pConfig->chunk : 10,
            'style' => isset($pConfig->style) ? $pConfig->style : 'sliding',
        ];

        $chunk = $options['chunk'] ?? $default['chunk'];
        $style = $options['style'] ?? $default['style'];

        $this->_template =          $options['template']          ?? $this->_template;
        $this->_selectedTemplate =  $options['selectedTemplate']  ?? $this->_selectedTemplate;
        $this->_separatorTemplate = $options['separatorTemplate'] ?? $this->_separatorTemplate;
        $this->_containerPrefix =   $options['containerPrefix']   ?? $this->_containerPrefix;
        $this->_containerPostfix =  $options['containerPostfix']  ?? $this->_containerPostfix;

        $rangeClass = 'Doctrine_Pager_Range_' . ucfirst($style);
        $range = new $rangeClass(['chunk' => $chunk]);

        $pager = $this->_pager = ZFE_Paginator::getInstance()->getPager();
        $url = str_replace('%7B%25page_number%7D', '{%page_number}', ZFE_Paginator::getInstance()->getUrl());

        $layout = $this->_layout = new Doctrine_Pager_Layout($pager, $range, $url);
        $layout->setSelectedTemplate($this->_selectedTemplate);
        $layout->setSeparatorTemplate($this->_separatorTemplate);
        $layout->setTemplate($this->_template);

        if (1 === $pager->getLastPage()) {
            return '';
        }

        return
            $this->_containerPrefix .
            $this->prevPage() .
            $layout->display([], true) .
            $this->nextPage() .
            $this->_containerPostfix;
    }

    /**
     * Получить ссылку на предыдущую страницу.
     *
     * @return string
     */
    private function nextPage()
    {
        if ($this->_pager->getPage() !== $this->_pager->getNextPage()) {
            $this->_layout->addMaskReplacement('page', '&raquo;', true);
            $page = ['page_number' => $this->_pager->getNextPage()];
            $str = $this->_layout->processPage($page);
            $this->_layout->removeMaskReplacement('page');

            return $str;
        }

        return '';
    }

    /**
     * Получить ссылку на следующую страницу.
     *
     * @return string
     */
    private function prevPage()
    {
        if ($this->_pager->getPage() !== $this->_pager->getPreviousPage()) {
            $this->_layout->addMaskReplacement('page', '&laquo;', true);
            $page = ['page_number' => $this->_pager->getPreviousPage()];
            $str = $this->_layout->processPage($page);
            $this->_layout->removeMaskReplacement('page');

            return $str;
        }

        return '';
    }
}
