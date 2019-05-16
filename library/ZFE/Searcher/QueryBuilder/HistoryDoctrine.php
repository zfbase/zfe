<?php

/*
 * ZFE – платформа для построения редакторских интерфейсов.
 */

/**
 * Конструктор запросов для поиска по истории.
 */
class ZFE_Searcher_QueryBuilder_HistoryDoctrine extends ZFE_Searcher_QueryBuilder_Doctrine
{
    protected function _filters()
    {
        parent::_filters();

        $editorId = trim((string) $this->getParam('editor'));
        if ( ! empty($editorId)) {
            $this->_query->addWhere('x.user_id = ?', $editorId);
        }

        $dateFrom = $this->getParam('date_from');
        $dateTill = $this->getParam('date_till');
        if ($dateFrom && $dateTill) {
            $this->_query->addWhere('x.datetime_action BETWEEN ? AND ?', [$dateFrom . ':00', $dateTill . ':59']);
        } elseif ($dateFrom) {
            $this->_query->addWhere('x.datetime_action > ?', $dateFrom . ':00');
        } elseif ($dateTill) {
            $this->_query->addWhere('x.datetime_action < ?', $dateTill . ':59');
        }
    }
}
