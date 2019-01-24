<?php

/*
 * ZFE – платформа для построения редакторских интерфейсов.
 */

/**
 * Автоматическое форматирование значения по ключевым префиксам названия.
 */
class ZFE_View_Helper_AutoFormat extends Zend_View_Helper_Abstract
{
    /**
     * Режим поддержки HTML
     *
     * @var boolean
     */
    protected $_htmlMode = true;

    /**
     * Автоматически отформатировать значение по ключевым префиксам названия.
     *
     * @param mixed  $value
     * @param string $columnName
     * @param string $modelName
     *
     * @return string
     */
    public function autoFormat($value, $columnName = null, $modelName = null)
    {
        if (is_object($value)) {
            return (string) $value;
        }

        if ($modelName && $columnName) {
            $manager = Doctrine_Manager::getInstance();
            $hasModel = $manager->hasConnectionForComponent($modelName)
                     && $manager->getConnectionForComponent($modelName)->hasTable($modelName);
            if ($hasModel) {
                $table = Doctrine_Core::getTable($modelName);
                if ($table->hasColumn($columnName)) {
                    return $this->formatByTable($table, $columnName, $value);
                }
            }
        }

        if ($columnName) {
            return $this->formatByFieldName($columnName, $value);
        }

        return $this->formatByData($value);
    }

    /**
     * Установить режим поддержки HTML
     *
     * @param boolean $mode
     * @return ZFE_View_Helper_AutoFormat
     */
    public function setHtmlMode($mode = true)
    {
        $this->_htmlMode = $mode;
        return $this;
    }

    /**
     * Отформатировать значение на основе структуры таблицы.
     *
     * @param ZFE_Model_Table $table
     * @param string          $columnName
     * @param mixed           $value
     *
     * @return string
     */
    public function formatByTable(ZFE_Model_Table $table, $columnName, $value)
    {
        $modelName = $table->getClassnameToReturn();

        if (in_array($columnName, $modelName::$booleanFields, true)) {
            return $value ? 'да' : 'нет';
        }

        if ($modelName::isDictionaryField($columnName)) {
            return $modelName::getDictionaryField($columnName, $value);
        }

        if ($table->isRelationColumn($columnName)) {
            foreach ($table->getRelations() as $name => $opt) { /** @var Doctrine_Relation $opt */
                if ($columnName === $opt->getLocal()) {
                    $alias = $opt->getClass();
                    break;
                }
            }

            return (string) $alias::find($value);
        }

        $config = Zend_Registry::get('config');
        $columnParams = $table->getColumnDefinition($columnName);
        switch ($columnParams['type']) {
            case 'integer':
                if (null === $value) {
                    return null;
                }

                if (false !== mb_strpos($columnName, 'year')) {
                    return $value;
                }

                return number_format($value, 0, ',', $this->_htmlMode ? '&nbsp;' : '');
            case 'float':
            case 'decimal':
                if (null === $value) {
                    return null;
                }

                return number_format($value, $columnParams['scale'] ?? null, ',', $this->_htmlMode ? '&nbsp;' : '');
            case 'timestamp':
                if (empty($value) || '0000-00-00 00:00:00' === $value) {
                    return '';
                }

                return date($config->format->datetime, strtotime($value));
            case 'time':
                if (empty($value) || '00:00:00' === $value) {
                    return '';
                }

                return $value;
            case 'date':
                if (empty($value) || '0000-00-00' === $value) {
                    return '';
                }

                return date($config->format->date, strtotime($value));
            case 'string':
            default:
                return $value;
        }
    }

    /**
     * Отформатировать значение основываясь на названии поля.
     *
     * @param string $columnName
     * @param mixed  $value
     *
     * @return string
     */
    public function formatByFieldName($columnName, $value)
    {
        $config = Zend_Registry::get('config');

        if ('date' === $columnName || 'date_' === mb_substr($columnName, 0, 5)) {
            if (empty($value) || '0000-00-00' === $value) {
                return '';
            }

            return date($config->format->date, strtotime($value));
        }

        if ('datetime' === $columnName || 'datetime_' === mb_substr($columnName, 0, 9)) {
            if (empty($value) || '0000-00-00 00:00:00' === $value) {
                return '';
            }

            return date($config->format->datetime, strtotime($value));
        }

        if ('time' === $columnName || 'time_' === mb_substr($columnName, 0, 5)) {
            if (empty($value) || '00:00:00' === $value) {
                return '';
            }
        }

        if ('month' === $columnName || 'month_' === mb_substr($columnName, 0, 6)) {
            if (empty($value) || '0000-00-00' === $value) {
                return '';
            }

            return strftime($config->format->month, strtotime($value));
        }

        return $this->formatByData($value);
    }

    /**
     * Отформатировать значение по содержанию.
     *
     * @param mixed $value
     *
     * @return string
     */
    public function formatByData($value)
    {
        if (is_array($value)) {
            return $this->view->formatArray($value);
        }

        if (is_bool($value)) {
            return $value ? 'Да' : 'Нет';
        }

        if (is_int($value)) {
            return number_format($value, 0, null, $this->_htmlMode ? '&nbsp;' : '');
        }

        if (is_object($value)) {
            return (string) $value;
        }

        return $value;
    }
}
