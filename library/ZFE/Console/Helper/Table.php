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
     * Название таблицы.
     *
     * @var array
     */
    protected $_title;

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
     */
    public function setColumnWidth(int $columnIndex, int $width): self
    {
        $this->_columnWidths[$columnIndex] = $width;
        return $this;
    }

    /**
     * Указать размеры колонок.
     *
     * @param int[] $widths
     */
    public function setColumnWidths(array $widths): self
    {
        $this->_columnWidths = [];
        foreach ($widths as $index => $width) {
            $this->setColumnWidth($index, $width);
        }
        return $this;
    }

    /**
     * Указать выравнивание колонки.
     */
    public function setColumnAlign(int $columnIndex, int $align): self
    {
        $this->_columnAligns[$columnIndex] = $align;
        return $this;
    }

    /**
     * Указать выравнивание колонок.
     */
    public function setColumnAligns(array $aligns): self
    {
        $this->_columnAligns = [];
        foreach ($aligns as $index => $align) {
            $this->setColumnAlign($index, $align);
        }
        return $this;
    }

    /**
     * Указать заголовок.
     */
    public function setHeaders(array $headers): self
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
     */
    public function setRows(array $rows): self
    {
        $this->rows = [];
        return $this->addRows($rows);
    }

    /**
     * Добавить строку.
     */
    public function addRow(array $row): self
    {
        $this->_rows[] = array_values($row);
        return $this;
    }

    /**
     * Указать заголовок таблицы.
     */
    public function setTitle(?string $title): self
    {
        $this->_title = $title;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function render(bool $echo = true): string
    {
        $this->prepare();

        $markup = $this->renderRowSeparator();

        if (!empty($this->_title)  ) {
            $markup .= $this->renderTitle();
            $markup .= $this->renderRowSeparator();
        }

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
    public function prepare(): void
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
    public function renderRowSeparator(): ?string
    {
        if (0 == $this->_numberOfColumns) {
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
     */
    public function renderRow(array $data): ?string
    {
        if (0 == $this->_numberOfColumns) {
            return null;
        }

        $markup = static::VERTICAL_BORDER_CHAR;
        for ($col = 0; $col < $this->_numberOfColumns; $col++) {
            $len = $this->_effectiveColumnWidths[$col];
            @$value = $data[$col];
            //value = ZFE_Utilities::shortenText($value, $len);

            $markup .= ' ';
            $markup .= ZFE_Utilities::mb_str_pad($value, $len, ' ', $this->_columnAligns[$col] ?? static::ALIGN_LEFT);
            $markup .= mb_strlen($value) <= $len ? ' ' : '';
            $markup .= static::VERTICAL_BORDER_CHAR;
        }
        return $markup . "\n";
    }

    /**
     * Рендерить название таблицы.
     */
    public function renderTitle(): ?string
    {
        if (0 == $this->_numberOfColumns || empty($this->_title)) {
            return null;
        }

        $columns = count($this->_effectiveColumnWidths);
        $len = array_sum($this->_effectiveColumnWidths) + $columns * 3 - 1;
        $body = ZFE_Utilities::mb_str_pad(mb_strtoupper($this->_title), $len, ' ', STR_PAD_BOTH);
        return static::VERTICAL_BORDER_CHAR . $body . static::VERTICAL_BORDER_CHAR . "\n";
    }
}
