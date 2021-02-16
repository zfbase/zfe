<?php

/*
 * ZFE – платформа для построения редакторских интерфейсов.
 */

/**
 * Помощник для отображения прогресс бара в терминале.
 *
 * Примеры:
 * Индексация: ■■■■■□□□□□□□□□□□□□□□ 25% (475 из 1900)
 * ■■■■■■■■■■■■■■■■■■■■ 735 из 735 (100%) за 7 сек
 */
class ZFE_Console_Helper_ProgressBar extends ZFE_Console_Helper_Abstract
{
    /**
     * Максимальное значение.
     *
     * @var int|float
     */
    protected $_maxValue;

    /**
     * Минимальное значение.
     *
     * @var int|float
     */
    protected $_minValue = 0;

    /**
     * Текущее значение.
     *
     * @var int|float
     */
    protected $_value;

    /**
     * Префикс.
     *
     * @var string
     */
    protected $_prefix;

    /**
     * Метка времени начала процесса.
     *
     * @var int
     */
    protected $_startTime;

    /**
     * Метка времени завершения процесса.
     *
     * @var int
     */
    protected $_finishTime;

    /**
     * Ширина заполнителя.
     *
     * @var int
     */
    protected $_placeholderWidth = 50;

    /**
     * Шаблон бара во время исполнения.
     *
     * @var string
     */
    protected $_template = '[placeholder] [percent] ([value] из [max])';

    /**
     * Шаблон бара по завершению.
     *
     * @var string
     */
    protected $_finishTemplate = '[placeholder] [value] из [max] ([percent]) за [time]';

    /**
     * Символ ограничивающий заполнитель слева.
     *
     * @var string
     */
    protected $_leftSymbol = '';

    /**
     * Символ ограничивающий заполнитель справа.
     *
     * @var string
     */
    protected $_rightSymbol = '';

    /**
     * Символ заполненной ячейки заполнителя.
     *
     * @var string
     */
    protected $_fillStep = '■';

    /**
     * Символ свободной ячейки заполнителя.
     *
     * @var string
     */
    protected $_clearStep = '□';

    /**
     * Конструктор.
     *
     * @param int|float $max
     * @param int|float $min
     * @param int|float $start
     */
    public function __construct($max = null, $min = null, $start = null)
    {
        $this->_prepare($max, $min, $start);
    }

    /**
     * Указать ширину заполнителя.
     */
    public function setPlaceholderWidth(int $width): self
    {
        $this->_placeholderWidth = $width;
        return $this;
    }

    /**
     * Указать шаблон бара.
     */
    public function setTemplate(string $template): self
    {
        $this->_template = $template;
        return $this;
    }

    /**
     * Указать шаблон бара после завершения прогресса.
     */
    public function setFinishTemplate(string $template): self
    {
        $this->_finishTemplate = $template;
        return $this;
    }

    /**
     * Указать префикс перед баром.
     */
    public function setPrefix(string $prefix): self
    {
        $this->_prefix = $prefix;
        return $this;
    }

    /**
     * Запустить и отобразить бар.
     *
     * @param int|float $max
     * @param int|float $min
     * @param int|float $start
     */
    public function start($max = null, $min = null, $start = null, ?int $startTime = null, bool $echo = true): string
    {
        $this->_prepare($max, $min, $start, $startTime);

        if (null === $this->_value) {
            $this->_value = $this->_minValue;
        }

        return $this->render($echo);
    }

    /**
     * Обновить текущее значение и перерисовать бар.
     *
     * @param int|float $value
     * @param bool      $echo
     */
    public function setValue($value, bool $echo = true): string
    {
        $this->_value = $value;

        if ($echo) {
            $this->overwrite();
        }

        return $this->render(false);
    }

    /**
     * Завершить прогресс.
     */
    public function finish(bool $echo = true): string
    {
        $this->_finishTime = microtime(true);

        if ($echo) {
            $this->overwrite();
            echo "\n";
        }

        return $this->render(false);
    }

    /**
     * Получить текущий процент.
     *
     * @return int|float
     */
    public function getPercent(bool $integer = true)
    {
        if ($this->_maxValue == $this->_minValue) {
            return 0;
        }

        $percent = ($this->_value - $this->_minValue) / ($this->_maxValue - $this->_minValue);

        if ($integer) {
            return round($percent * 100);
        }

        return $percent;
    }

    /**
     * Вернуть число секунд исполнения процесса.
     */
    public function getTime(): float
    {
        return round(($this->_finishTime ?? microtime(true)) - $this->_startTime, 3);
    }

    /**
     * {@inheritdoc}
     */
    public function render(bool $echo = true): string
    {
        if ($this->_finishTime) {
            $template = $this->_finishTemplate;
            $time = round($this->_finishTime - $this->_startTime, 3);
        } else {
            $template = $this->_template;
            $time = round(microtime(true) - $this->_startTime, 3);
        }

        $bar = str_replace('[placeholder]', $this->renderPlaceholder(false), $template);
        $bar = str_replace('[value]', $this->_value, $bar);
        $bar = str_replace('[max]', $this->_maxValue, $bar);
        $bar = str_replace('[min]', $this->_minValue, $bar);
        $bar = str_replace('[percent]', $this->getPercent(true) . '%', $bar);
        $bar = str_replace('[time]', $time . ' сек', $bar);

        if ($this->_prefix) {
            $bar = $this->_prefix . ' ' . $bar;
        }

        if ($echo) {
            echo $bar;
        }

        return $bar;
    }

    /**
     * Распечатать бар поверх текущей строки.
     */
    public function overwrite(): void
    {
        echo "\r" . $this->render(false);
    }

    /**
     * Рендерить заполнитель.
     */
    protected function renderPlaceholder(bool $echo = true): string
    {
        if (null === $this->_maxValue) {
            throw new ZFE_Console_Exception('Не указано обязательное максимальное значение.');
        }

        $percent = $this->getPercent(false);
        if ($percent < 1) {
            $fillWidth = round($this->_placeholderWidth * $percent);
            $clearWidth = $this->_placeholderWidth - $fillWidth;
        } else {
            $fillWidth = $this->_placeholderWidth;
            $clearWidth = 0;
        }

        $placeholder  = $this->_leftSymbol;
        $placeholder .= str_repeat($this->_fillStep, $fillWidth);
        $placeholder .= str_repeat($this->_clearStep, $clearWidth);
        $placeholder .= $this->_rightSymbol;

        if ($echo) {
            echo $placeholder;
        }

        return $placeholder;
    }

    /**
     * Подготовить к запуску.
     *
     * @param int|float $max
     * @param int|float $min
     * @param int|float $start
     */
    protected function _prepare($max = null, $min = null, $start = null, ?int $startTime = null): void
    {
        $this->_startTime = $startTime ?? microtime(true);

        if (null !== $max) {
            $this->_maxValue = $max;
        }

        if (null !== $min) {
            $this->_minValue = $min;
        }

        if (null !== $start) {
            $this->_value = $start;
        }
    }
}
