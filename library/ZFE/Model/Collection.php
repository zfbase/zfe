<?php

/*
 * ZFE – платформа для построения редакторских интерфейсов.
 */

/**
 * Модификация Doctrine_Collection.
 */
class ZFE_Model_Collection extends Doctrine_Collection
{
    /**
     * Удалить все записи коллекции в обход механизма истории.
     *
     * @param Doctrine_Connection $conn
     * @param bool                $clearColl
     *
     * @return ZFE_Model_Collection
     */
    public function hardDelete(Doctrine_Connection $conn = null, $clearColl = true)
    {
        if (null === $conn) {
            $conn = $this->_table->getConnection();
        }

        try {
            $conn->beginInternalTransaction();
            $conn->transaction->addCollection($this);

            foreach ($this as $key => $record) {
                $record->hardDelete($conn);
            }

            $conn->commit();
        } catch (Throwable $e) {
            $conn->rollback();
            throw $e;
        }

        if ($clearColl) {
            $this->clear();
        }

        return $this;
    }

    /**
     * Получить значения поля для элементов коллекции.
     *
     * @param string $field возвращаемое поле
     * @param string $key   ключ (при необходимости)
     *
     * @return array
     */
    public function getFieldValues($field = 'id', $key = null)
    {
        $values = [];
        foreach ($this as $item) {
            if ($key) {
                $values[$item->$key] = $item->$field;
            } else {
                $values[] = $item->$field;
            }
        }
        return $values;
    }
}
