<?php

/*
 * ZFE – платформа для построения редакторских интерфейсов.
 */

/**
 * Кнопки для перехода к предыдущему и следующему результату поиска.
 */
class ZFE_View_Helper_SearchPages extends Zend_View_Helper_Abstract
{
    /**
     * Получить экземпляр помощника.
     *
     * @return ZFE_View_Helper_SearchPages
     */
    public function searchPages()
    {
        return $this;
    }

    /**
     * Получить параметр для сохранения позиции при переходе к результату поиска из поисковой выдачи.
     *
     * @param int    $rowNum
     * @param string $prefix
     *
     * @return string
     */
    public function getHash($rowNum, $prefix = null)
    {
        $paginator = ZFE_Paginator::getInstance();
        $pageNumber = $paginator->getPageNumber();
        $itemsPerPage = $paginator->getItemsPerPage();
        $resultNumber = ($pageNumber - 1) * $itemsPerPage + $rowNum;

        switch ($prefix) {
            case '?':
                return '?rn=' . $resultNumber;
            case '&':
                return '&rn=' . $resultNumber;
            default:
                return $resultNumber;
        }
    }

    /**
     * Получить группу кнопок перехода к предыдущему и следующему результату поиска.
     *
     * @param string $containerClass
     * @param string $btnSize
     *
     * @return string
     */
    public function getStepBtns($containerClass = 'pull-right', $btnSize = 'xs')
    {
        $request = Zend_Controller_Front::getInstance()->getRequest();

        $hash = $request->getParam('h');
        if (empty($hash)) {
            return '';
        }

        $hops = new Zend_Session_Namespace('HopsHistory');
        if (empty($hops->{$hash}) || empty($hops->{$hash}['params']['totalResults'])) {
            return '';
        }

        $currentRowNumber = $request->getParam('rn');
        if (empty($currentRowNumber)) {
            return '';
        }

        if (1 !== $currentRowNumber) {
            $prevUrl = $this->getPrevUrl();
            $prevBtn = '<a role="button" class="btn btn-' . $btnSize . ' btn-default" href="' . $prevUrl . '">'
                     . '<span class="glyphicon glyphicon-arrow-left"></span>'
                     . '</a>';
        } else {
            $prevBtn = '<a role="button" class="btn btn-' . $btnSize . ' btn-default disabled" href="#">'
                     . '<span class="glyphicon glyphicon-arrow-left"></span>'
                     . '</a>';
        }

        if ($currentRowNumber < $hops->{$hash}['params']['totalResults']) {
            $nextUrl = $this->getNextUrl();
            $nextBtn = '<a role="button" class="btn btn-' . $btnSize . ' btn-default" href="' . $nextUrl . '">'
                     . '<span class="glyphicon glyphicon-arrow-right"></span>'
                     . '</a>';
        } else {
            $nextBtn = '<a role="button" class="btn btn-' . $btnSize . ' btn-default disabled" href="#">'
                     . '<span class="glyphicon glyphicon-arrow-right"></span>'
                     . '</a>';
        }

        if ($prevBtn || $nextBtn) {
            return '<div class="btn-group ' . $containerClass . '" role="group">' . $prevBtn . $nextBtn . '</div>';
        }

        return '';
    }

    /**
     * Получить ссылку для перехода к предыдущему результату поиска.
     *
     * @return string
     */
    public function getPrevUrl()
    {
        return $this->getUrl('-1');
    }

    /**
     * Получить ссылку для перехода к следующему результату поиска.
     *
     * @return string
     */
    public function getNextUrl()
    {
        return $this->getUrl('+1');
    }

    /**
     * Получить ссылку для перехода к предыдущему или следующему результату поиска.
     *
     * @param int $offset -1 – предыдущий, +1 – следующий
     *
     * @throws UnderflowException
     * @throws DomainException
     *
     * @return string
     */
    protected function getUrl($offset)
    {
        $request = Zend_Controller_Front::getInstance()->getRequest();
        $hash = $request->getParam('h');
        if (empty($hash)) {
            throw new UnderflowException();
        }

        $curentRowNumber = $request->getParam('rn');
        if (empty($hash)) {
            throw new UnderflowException();
        }

        if ('-1' === $offset) {
            $rowNumber = $curentRowNumber - 1;
        } elseif ('+1' === $offset) {
            $rowNumber = $curentRowNumber + 1;
        } else {
            throw new DomainException();
        }

        $hops = new Zend_Session_Namespace('HopsHistory');
        if (empty($hops->{$hash})) {
            throw new UnderflowException();
        }

        $url = $hops->{$hash}['url'];
        $url .= (false === strpos($url, '?')) ? '?' : '&';
        $url .= 'rh=' . $hash;  // revert hash
        $url .= '&rn=' . $rowNumber;

        return $url;
    }
}
