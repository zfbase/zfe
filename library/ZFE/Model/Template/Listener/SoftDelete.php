<?php

/*
 * ZFE – платформа для построения редакторских интерфейсов.
 */

/**
 * Слушатель событий CRUD в моделях Doctrine, реализующий мягкое удаление.
 */
class ZFE_Model_Template_Listener_SoftDelete extends Doctrine_Record_Listener
{
    /**
     * Разрешить мягкое удаление?
     *
     * @var bool
     */
    protected $_allowSoftDelete = true;

    /**
     * Разрешить мягкое удаление?
     * Если указан параметр обновит значение.
     *
     * @param bool $mode
     * @return bool
     */
    public function allowSoftDelete($mode = null)
    {
        if ($mode !== null) {
            $this->_allowSoftDelete = $mode;
        }
        return $this->_allowSoftDelete;
    }

    /**
     * Хук preDelete.
     *
     * @param Doctrine_Event $event
     */
    public function preDelete(Doctrine_Event $event)
    {
        if ($this->_allowSoftDelete) {
            /** @var ZFE_Model_AbstractRecord $invoker */
            $invoker = $event->getInvoker();

            if ($invoker->contains('deleted')) {
                if ($invoker->contains('version')) {
                    ++$invoker->version;
                }
                $invoker->deleted = true;
                $invoker->hardSave();

                $event->skipOperation();
            }
        }
    }

    /**
     * Хук preDqlSelect.
     *
     * @param Doctrine_Event $event
     */
    public function preDqlSelect(Doctrine_Event $event)
    {
        if ($this->_allowSoftDelete) {
            $params = $event->getParams();

            /** @var ZFE_Model_Table $table */
            $table = $params['component']['table'];

            /** @var ZFE_Query $query */
            $query = $event->getQuery();

            if ($query->isHard() || !$table->hasField('deleted') || !empty($params['component']['ref'])) {
                return;
            }

            $outFrom = [];
            $inFrom = $query->getDqlPart('from');
            $where = $query->getDqlPart('where');
            $inWhereNumber = count($where);
            foreach ($inFrom as $inFromRow) {
                $outParts = [];
                $inParts = explode(',', $inFromRow);
                foreach ($inParts as $inPart) {
                    $part = trim($inPart);

                    preg_match('/^(?:[a-z]+\.)?([a-z]+)(?: +([a-z]+))?/i', $part, $matches);
                    $alias = (count($matches) > 2 && $matches[2] !== 'WITH')
                        ? $matches[2]
                        : $matches[1];

                    if ($alias == $params['alias']) {
                        $exc = $params['alias'] . '.deleted = 0';

                        $t = explode(' ', $part);
                        if (count(explode('.', $t[0])) == 2) {
                            $outParts[] = stripos($part, 'WITH') !== false
                                ? $part . ' AND ' . $exc
                                : $part . ' WITH ' . $exc;
                        } else {
                            $outParts[] = $part;

                            if (!$query->isMiddleHard()) {
                                if (count($where)) {
                                    $where[] = 'AND';
                                }
                                $where[] = $exc;
                            }
                        }
                    } else {
                        $outParts[] = $part;
                    }
                }
                $outFrom[] = implode(', ', $outParts);
            }

            if ($inFrom == $outFrom && $inWhereNumber == count($where)) {
                $componentName = $table->getComponentName();
                foreach ($inFrom as $inFromRow) {
                    $inParts = explode(',', $inFromRow);
                    foreach ($inParts as $inPart) {
                        $dotPos = strpos($inPart, '.');
                        if ($dotPos) {
                            $alias = substr($inPart, 0, $dotPos);
                            if ($alias == $componentName) {
                                if (count($where)) {
                                    $where[] = 'AND';
                                }
                                $where[] = $componentName . '.deleted = 0';
                            }
                        }
                    }
                }
            }
            $query->setDqlQueryPart('from', $outFrom);
            $query->setDqlQueryPart('where', $where);
        }
    }
}
