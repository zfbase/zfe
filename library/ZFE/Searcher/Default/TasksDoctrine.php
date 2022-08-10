<?php

/*
 * ZFE – платформа для построения редакторских интерфейсов.
 */

/**
 * Построитель Doctrine DQL-запросов для отложенных задач.
 */
class ZFE_Searcher_Default_TasksDoctrine extends ZFE_Searcher_QueryBuilder_Doctrine
{
    /** {@inheritdoc} */
    protected function _filters()
    {
        parent::_filters();

        $id = (int) $this->getParam('id', -1);
        if ($id) {
            $this->_query->addFrom('tasks x2');
            $this->_query->where('(
                x2.id = x.id
                OR x2.parent_id = x.id
                OR x2.id = x.parent_id
                OR x2.parent_id = x.parent_id
            )');
            $this->_query->addWhere('x2.id = ?', $id);
            return;
        }

        $search = $this->getParam('search');
        switch ($search) {
            case 'performed':
                $this->_query->andWhere('x.datetime_started IS NOT NULL');
                $this->_query->andWhere('x.datetime_done IS NULL');
                $this->_query->andWhere('x.datetime_canceled IS NULL');
                $this->_query->andWhere('x.errors IS NULL');
                break;
            case 'waiting':
                $this->_query->andWhere('x.datetime_started IS NULL');
                $this->_query->andWhere('x.datetime_canceled IS NULL');
                break;
            case 'failed':
                $this->_query->andWhere('x.errors IS NOT NULL');
                $this->_query->andWhere('NOT EXISTS(
                    SELECT x3.id
                    FROM tasks x3
                    WHERE x3.parent_id IS NOT NULL
                    AND x3.parent_id IN (t.id, t.parent_id)
                    AND x3.datetime_created > t.datetime_created
                )');
                break;
            case 'canceled':
                $this->_query->andWhere('x.datetime_canceled IS NOT NULL');
                $this->_query->andWhere('NOT EXISTS(
                    SELECT x3.id
                    FROM tasks x3
                    WHERE x3.parent_id IS NOT NULL
                    AND x3.parent_id IN (t.id, t.parent_id)
                    AND x3.datetime_created > t.datetime_created
                )');
                break;
        }

        $performerCode = $this->getParam('performer');
        if ($performerCode) {
            /** @todo Заменить select на autocomplete, позволяющий искать задачи по группам исполнителей */
            $this->_query->andWhere(
                'x.performer_code = ?',
                str_replace('_', '/', $performerCode)
            );
        }

        $relatedId = (int) $this->getParam('related_id', -1);
        if ($relatedId) {
            $this->_query->addWhere('x.related_id = ?', $relatedId);
        }
    }

    /** {@inheritdoc} */
    protected function _orderHelper($field, $direction = 'ASC')
    {
        if ('worked' == $field) {
            $this->_query->orderBy('IF(x.datetime_done IS NULL OR x.datetime_done < x.datetime_started, 1, 0)');
            $this->_query->addOrderBy("TIMESTAMPDIFF(SECOND, x.datetime_started, x.datetime_done) {$direction}");
        } else {
            parent::_orderHelper($field, $direction);
        }
    }
}
