<?php

/*
 * ZFE – платформа для построения редакторских интерфейсов.
 */

/**
 * Стартовый экран для авторизованного пользователя.
 */
class ZFE_Controller_Default_Dashboard extends Controller_Abstract
{
    /**
     * Страница со сведениями о последних измененных записях в БД.
     */
    public function indexAction()
    {
        $userId = Zend_Registry::get('user')->data->id;
        $timeBorder = "NOW() - INTERVAL '1' MONTH";

        $this->view->myHistory = ZFE_Query::create()
            ->select('x.*')
            ->from('History x')
            ->andWhere('x.content_id > 0')
            ->andWhere('x.user_id = ?', $userId)
            ->andWhere('x.datetime_action > ' . $timeBorder)
            ->groupBy('x.table_name, x.content_id, x.content_version, x.action_type')
            ->orderBy('x.datetime_action DESC')
            ->limit(10)
            ->execute()
        ;

        $this->view->otherHistory = ZFE_Query::create()
            ->select('x.*, e.*')
            ->from('History x, x.Editors e, History h')
            ->andWhere('x.content_id > 0')
            ->andWhere('x.user_id <> ?', $userId)
            ->andWhere('h.user_id = ?', $userId)
            ->andWhere('h.datetime_action > ' . $timeBorder)
            ->andWhere('x.table_name = h.table_name')
            ->andWhere('x.content_id = h.content_id')
            ->andWhere('x.content_version - 1 = h.content_version')
            ->andWhere('h.id IS NOT NULL')
            ->groupBy('x.table_name, x.content_id, x.content_version, x.action_type')
            ->orderBy('x.datetime_action DESC')
            ->limit(10)
            ->execute()
        ;
    }
}
