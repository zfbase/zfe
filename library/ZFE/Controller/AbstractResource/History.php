<?php

/*
 * ZFE – платформа для построения редакторских интерфейсов.
 */

/**
 * Работа с историей.
 */
trait ZFE_Controller_AbstractResource_History
{
    /**
     * Возможность отката объектов к предыдущим версиям
     *
     * @var bool
     */
    protected static $_canRestore = true;

    /**
     * Просмотр изменений конкретной записи.
     */
    public function historyAction()
    {
        if (!in_array('history', static::$_enableActions)) {
            $this->abort(404);
        }

        $modelName = static::$_modelName;
        if (!($this->view->item instanceof $modelName) && $this->hasParam('id')) {
            $itemId = (int) $this->getParam('id');
            $this->view->item = $modelName::hardFind($itemId);
        }
        $item = $this->view->item;

        if (empty($item)) {
            $this->abort(404, $modelName::decline('%s не найден.', '%s не найдена.', '%s не найдено.'));
        }

        $this->view->title($item->getTitle());

        if (empty($this->view->history)) {
            $q = ZFE_Query::create()
                ->select('x.*, e.*, count(*) cnt')
                ->from('History x, x.Editors e')
                ->andWhere('x.table_name = ?', $item->getTableName())
                ->andWhere('x.content_id = ?', $item->id)
                ->groupBy('x.user_id, x.content_version, x.action_type')
                ->orderBy('x.datetime_action ASC, x.content_version ASC')
            ;

            $this->view->history = ZFE_Paginator::execute($q);
        }
    }

    /**
     * Сравнить две версии конкретной записи.
     */
    public function diffAction()
    {
        $this->_helper->postToGet();

        if (empty($this->view->curItem) && $this->hasParam('id')) {
            $itemId = (int) $this->getParam('id');
            $this->view->curItem = (static::$_modelName)::hardFind($itemId);
        }
        $curItem = $this->view->curItem;

        if (empty($curItem)) {
            $this->abort(404, (static::$_modelName)::decline('%s не найден.', '%s не найдена.', '%s не найдено.'));
        }

        $this->view->title($curItem->getTitle());

        $this->view->milestones = History::getVersionsListFor($curItem);

        $rightVersion = (int) $this->getParam('right');
        if (!$rightVersion) {  // По умолчанию, текущая версия
            $rightVersion = $curItem->version;
        }

        $leftVersion = (int) $this->getParam('left');
        if (!$leftVersion) {  // По умолчанию, предыдущая перед правой
            $leftVersion = $rightVersion > 1
                ? $rightVersion - 1
                : 1;
        }

        $this->view->rightVersion = $rightVersion;
        $this->view->rightEditor = $curItem->getEditorOfVersion($rightVersion);
        $this->view->rightItem = $rightItem = $curItem->getStateForVersion($rightVersion);

        $this->view->leftVersion = $leftVersion;
        $this->view->leftEditor = $curItem->getEditorOfVersion($leftVersion);
        $this->view->leftItem = $leftItem = $curItem->getStateForVersion($leftVersion);
    }

    /**
     * Восстановить запись к предыдущей версии.
     *
     * @param bool|string $redirectUrl Адрес для перенаправления; если адрес равен FALSE, то перенаправление не происходит
     *
     * @return bool|void В случае отсутствия перенаправления, возвращает TRUE или FALSE в зависимости от успеха удаления
     */
    public function restoreAction($redirectUrl = null)
    {
        $version = $this->getParam('version');
        $modelName = static::$_modelName;

        if (!static::$_canRestore) {
            $this->abort(403, 'Невозможно откатить ' . mb_strtolower($modelName::$nameSingular) . ' к версии ' . $version . ': доступ запрещен');
        }

        $curItem = $modelName::find($this->getParam('id'));
        if (empty($curItem)) {
            $this->abort(404, $modelName::decline('%s не найден.', '%s не найдена.', '%s не найдено.'));
        }

        try {
            $restoreItem = $curItem->getStateForVersion($version);
            $restoreItem->save();

            ZFE_Notices::ok($modelName::decline('%s успешно откатан', '%s успешно откатана', '%s успешно откатано') . ' к версии ' . $version);

            $status = true;
        } catch (Throwable $ex) {
            ZFE_Utilities::popupException($ex);

            $this->error('Не удалось откатить ' . mb_strtolower($modelName::$nameSingular) . ' к версии ' . $version, $ex);

            $status = false;
        }

        if (false !== $redirectUrl) {
            if (null === $redirectUrl) {
                $redirectUrl = $curItem->getEditUrl() . $this->view->hopsHistory()->getSideHash('?');
            }
            $this->redirect($redirectUrl);
        } else {
            return $status;
        }
    }

    public function historyMergeAction()
    {
        $curItem = (static::$_modelName)::find($this->getParam('id'));
        if (empty($curItem)) {
            $this->abort(404, (static::$_modelName)::decline('%s не найден.', '%s не найдена.', '%s не найдено.'));
        }
        $this->view->item = $item = $curItem->getStateForVersion(1);

        /** @var History $history */
        $history = History::find($this->getParam('hid'));
        if (empty($history)) {
            $this->abort(404, 'Историческая запись не найдена');
        }

        $slavesStr = $history->content_old;
        $slaveIds = explode(',', $slavesStr);

        $q = ZFE_Query::create()
            ->select('x.*')
            ->from(static::$_modelName . ' x')
            ->whereIn('id', $slaveIds)
            ->setHard(true)
        ;
        $this->view->slaves = $q->execute();
    }
}
