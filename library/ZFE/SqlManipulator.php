<?php

/*
 * ZFE – платформа для построения редакторских интерфейсов.
 */

class ZFE_SqlManipulator
{
    protected $_map;

    protected function __construct()
    {
    }

    public static function parseSql($sql)
    {
        $parser = new PHPSQLParser\PHPSQLParser();
        $query = new self();
        $query->_map = $parser->parse($sql);
        return $query;
    }

    public function getSql()
    {
        $creator = new PHPSQLParser\PHPSQLCreator();
        return $creator->create($this->_map);
    }

    public function andWhere($where)
    {
        $parser = new PHPSQLParser\PHPSQLParser();
        $whereMap = $parser->parse('WHERE ' . $where)['WHERE'];

        if (isset($this->_map['WHERE']) && 0 < count($this->_map['WHERE'])) {
            $this->_map['WHERE'][] = [
                'expr_type' => 'operator',
                'base_expr' => 'AND',
                'sub_tree' => false,
            ];
        } else {
            $this->_map['WHERE'] = [];
        }

        foreach ($whereMap as $op) {
            $this->_map['WHERE'][] = $op;
        }

        return $this;
    }

    public function orderBy($orderBy = null)
    {
        $parser = new PHPSQLParser\PHPSQLParser();
        $orderByMap = $parser->parse('ORDER BY ' . $orderBy)['ORDER'];

        if (!isset($this->_map['ORDER']) || 0 == count($this->_map['ORDER'])) {
            $this->_map['ORDER'] = [];
        }

        foreach ($orderByMap as $op) {
            $this->_map['ORDER'][] = $op;
        }

        return $this;
    }

    public function limit($limit = null, $offset = null)
    {
        if (null === $limit) {
            unset($this->_map['LIMIT']);
        } elseif (is_int($limit) && $limit >= 0) {
            $this->_map['LIMIT']['rowcount'] = $limit;
            if (is_int($offset) && $offset >= 0) {
                $this->_map['LIMIT']['offset'] = $offset;
            } elseif (!isset($this->_map['LIMIT']['offset'])) {
                $this->_map['LIMIT']['offset'] = '';
            }
        }

        return $this;
    }
}
