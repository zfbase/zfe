<?php

/*
 * ZFE – платформа для построения редакторских интерфейсов.
 */

/**
 * Class ZFE_View.
 *
 * (!) Класс используется только для подсказок в IDE. В коде он не используется.
 *
 * @uses Use name of method for find corresponded view helper class!
 *
 * Convenience methods for build in helpers (@see __call):
 *
 * @method string                                 action($action, $controller, $module = null, array $params = array())                           Retrieve rendered contents of a controller action.
 * @method string|ZFE_View_Helper_AbstractPartial abstractPartial($name = null, $module = null, $model = null, $controller = null)                Отрендерить наиболее подходящий шаблон по имени.
 * @method string                                 abstractRender($file, $controller = null)                                                       Отрендерить наиболее подходящий шаблон по имени.
 * @method string                                 alerts()                                                                                        Вывести нотификации.
 * @method string                                 autoFormat($value, $columnName = null, $modelName = null)                                       Автоматически отформатировать значение по ключевым префиксам названия.
 * @method ZFE_View_Helper_ControlTabs            controlTabs(?ZFE_Model_AbstractRecord $item = null)                                             Помощник использования абстрактных вьюшек.
 * @method string                                 crazyButtons($buttons, $class = 'btn btn-default')                                              Вывести кнопки.
 * @method string                                 dateForHuman($date)                                                                             Человеческое написание даты.
 * @method string                                 dateTime($dateTime, $time = true)                                                               Форматировать дату (и время).
 * @method string                                 dateTimeCompact($dateTime)                                                                      Преобразовать дату в короткий формат.
 * @method string                                 fieldset($name, $content, $attribs = null)                                                      Render HTML form.
 * @method string                                 formatArray(array $array, $listType = 'nolist')                                                 Отформатировать запрос.
 * @method string                                 formatSql($query)                                                                               Отформатировать запрос.
 * @method string                                 formAutocomplete($name, $value = null, $attribs = null)                                         Сгенерировать элемент автокомплита одного значения.
 * @method string                                 formClearfix($name, $value = null, $attribs = null)                                             Элемент формы Clearfix.
 * @method string                                 formDatelist($name, $value = null, $attribs = null)                                             Элемент формы Datelist.
 * @method string                                 formDuration($name, $value = null, $attribs = null)                                             Элемент формы Duration.
 * @method string                                 formMultiAutocomplete($name, $value = null, $attribs = null)                                    Сгенерировать элемент автокомплита нескольких значений.
 * @method string                                 historyMeta(AbstractRecord $item, $showCreator = true, $showEditor = true, $showVersion = true) Вернуть отформатированные сведений о создании и последнем редактирование записи.
 * @method ZFE_View_Helper_HopsHistory            hopsHistory()                                                                                   Получить экземпляр помощника.
 * @method bool                                   isAllowedMe($resource = null, $privilege = null)                                                Проверить наличие доступа.
 * @method string                                 lastEditedCell(AbstractRecord $item, $class = '')                                               Генерировать ячейку с информацией о последнем изменении записи.
 * @method string                                 menuItems($pages = null, $autoActive = true, $disabledAcl = false, $dropdownEnable = true)      Сгенерировать меню.
 * @method string                                 notices()                                                                                       Вернуть код вывода нотификации.
 * @method string                                 number($number, $decimals = 0, $dec_point = '.', $thousands_sep = '&nbsp;')                     Отформатировать запрос.
 * @method string                                 paginator(array $options = [])                                                                  Собрать пагинатор.
 * @method string                                 period($start, $end, $showTime = false)                                                         Форматировать период.
 * @method ZFE_View_Helper_SearchPages            searchPages()                                                                                   Кнопки для перехода к предыдущему и следующему результату поиска.
 * @method string                                 shortenText($text, $max_len = 100)                                                              Укоротить текст до определенного размера.
 * @method string                                 showTitles($items, $field = null, $separator = ', ', $maxElements = 0, $linkMethod = null)      Вывести список значений поля коллекции записей.
 * @method string                                 sortableHeadCell($field, $title = null, $cellClass = '')                                        Получить ячейку заголовка сортируемого поля таблицы.
 * @method string                                 tag($name, $attribs = [], $content = '')                                                        Генератор HTML-тегов.
 * @method string                                 title($title)                                                                                   Задать заголовок страницы.
 * @method string                                 webpack($filename)                                                                              Webpack.
 */
class ZFE_View extends Zend_View
{
}
