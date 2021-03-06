<?php

/*
 * ZFE – платформа для построения редакторских интерфейсов.
 */

/**
 * Помощник для отображения таблиц в терминале.
 *
 * На основе: https://github.com/symfony/symfony/blob/3.4/src/Symfony/Component/Console/Helper/Table.php
 *
 * Пример:
 * +---------+-------------------------------------+
 * | Команда | Описание                            |
 * +---------+-------------------------------------+
 * | help    | Справка по доступным командам       |
 * | models  | Сгенерировать модели Doctrine по БД |
 * | indexer | Индексация Sphinx                   |
 * +---------+-------------------------------------+
 */
class ZFE_Console_Helper_Table extends ZFE_Console_Helper_Abstract
{
    const CROSSING_CHAR = '+';
    const HORIZONTAL_BORDER_CHAR = '-';
    const VERTICAL_BORDER_CHAR = '|';

    const ALIGN_LEFT = STR_PAD_RIGHT;
    const ALIGN_CENTER = STR_PAD_BOTH;
    const ALIGN_RIGHT = STR_PAD_LEFT;

    /**
     * Строки заголовков.
     *
     * @var array
     */
    protected $_headers = [];

    /**
     * Строки.
     *
     * @var array
     */
    protected $_rows = [];

    /**
     * Число колонок.
     *
     * @var int
     */
    protected $_numberOfColumns = 0;

    /**
     * Ширина колонок.
     *
     * @var array<int>|int[]
     */
    protected $_columnWidths = [];

    /**
     * Фактическая ширина колонок.
     *
     * @var array<int>|int[]
     */
    protected $_effectiveColumnWidths = [];

    /**
     * Выравнивание колонок.
     *
     * @var array<int>|int[]
     */
    protected $_columnAligns = [];

    /**
     * Указать ширину колонки.
     *
     * @param int $columnIndex
     * @param int $width
     *
     * @return ZFE_Console_Helper_Table
     */
    public function setColumnWidth(int $columnIndex, int $width)
    {
        $this->_columnWidths[$columnIndex] = $width;
        return $this;
    }

    /**
     * Указать размеры колонок.
     *
     * @param array<int>|int $widths
     *
     * @return ZFE_Console_Helper_Table
     */
    public function setColumnWidths(array $widths)
    {
        $this->_columnWidths = [];
        foreach ($widths as $index => $width) {
            $this->setColumnWidth($index, $width);
        }
        return $this;
    }

    /**
     * Указать выравнивание колонки.
     *
     * @param int $columnIndex
     * @param int $align
     *
     * @return ZFE_Console_Helper_Table
     */
    public function setColumnAlign(int $columnIndex, $align)
    {
        $this->_columnAligns[$columnIndex] = $align;
        return $this;
    }

    /**
     * Указать выравнивание колонок.
     *
     * @param array $aligns
     *
     * @return ZFE_Console_Helper_Table
     */
    public function setColumnAligns(array $aligns)
    {
        $this->_columnAligns = [];
        foreach ($aligns as $index => $align) {
            $this->setColumnAlign($index, $align);
        }
        return $this;
    }

    /**
     * Указать заголовок.
     *
     * @param array $headers
     *
     * @return ZFE_Console_Helper_Table
     */
    public function setHeaders(array $headers)
    {
        $headers = array_values($headers);
        if (!empty($headers) && !is_array($headers[0])) {
            $headers = [$headers];
        }

        $this->_headers = $headers;
        return $this;
    }

    /**
     * Указать строки.
     *
     * @param array<array>|array[] $rows
     *
     * @return ZFE_Console_Helper_Table
     */
    public function setRows(array $rows)
    {
        $this->rows = [];
        return $this->addRows($rows);
    }

    /**
     * Добавить строку.
     *
     * @param array $row
     *
     * @return ZFE_Console_Helper_Table
     */
    public function addRow(array $row)
    {
        $this->_rows[] = array_values($row);
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function render(bool $echo = true)
    {
        $this->prepare();

        $markup = $this->renderRowSeparator();
        if (!empty($this->_headers)) {
            foreach ($this->_headers as $header) {
                $markup .= $this->renderRow($header);
                $markup .= $this->renderRowSeparator();
            }
        }
        foreach ($this->_rows as $row) {
            $markup .= $this->renderRow($row);
        }
        if (!empty($this->_rows)) {
            $markup .= $this->renderRowSeparator();
        }

        if ($echo) {
            echo $markup;
        }

        return $markup;
    }

    /**
     * Подготовить таблицу к рендеренгу:
     * (1) определить число колонок,
     * (2) определить ширину колонок.
     */
    public function prepare()
    {
        $numberOfColumns = 0;
        $contentWidths = [];
        $rows = array_merge($this->_headers, $this->_rows);
        foreach ($rows as $row) {
            // Определяем число столбцов
            $num = count($row);
            if ($numberOfColumns < $num) {
                $numberOfColumns = $num;
                $contentWidths = array_pad($contentWidths, $num, 0);
            }

            // Определяем максимальную ширину контента
            foreach ($row as $col => $value) {
                $len = mb_strlen($value);
                if ($contentWidths[$col] < $len) {
                    $contentWidths[$col] = $len;
                }
            }
        }

        $columnWidths = $this->_columnWidths;
        for ($col = 0; $col < $numberOfColumns; $col++) {
            if (empty($columnWidths[$col])) {
                $columnWidths[$col] = $contentWidths[$col];
            }
        }

        $this->_numberOfColumns = $numberOfColumns;
        $this->_effectiveColumnWidths = $columnWidths;
    }

    /**
     * Рендерить разделитель строк.
     */
    public function renderRowSeparator()
    {
        if (0 === $this->_numberOfColumns) {
            return null;
        }

        $markup = static::CROSSING_CHAR;
        for ($col = 0; $col < $this->_numberOfColumns; $col++) {
            $colWidth = $this->_effectiveColumnWidths[$col] + 2;
            $markup .= str_repeat(static::HORIZONTAL_BORDER_CHAR, $colWidth) . static::CROSSING_CHAR;
        }
        return $markup . "\n";
    }

    /**
     * Рендерить строку с данными.
     *
     * @param array $data
     */
    public function renderRow(array $data)
    {
        if (0 === $this->_numberOfColumns) {
            return null;
        }

        $markup = static::VERTICAL_BORDER_CHAR;
        for ($col = 0; $col < $this->_numberOfColumns; $col++) {
            $len = $this->_effectiveColumnWidths[$col];
            @$value = $data[$col];
            //value = ZFE::shortenText($value, $len);

            $markup .= ' ';
            $markup .= ZFE::mb_str_pad($value, $len, ' ', $this->_columnAligns[$col] ?? static::ALIGN_LEFT);
            $markup .= mb_strlen($value) <= $len ? ' ' : '';
            $markup .= static::VERTICAL_BORDER_CHAR;
        }
        return $markup . "\n";
    }
}
