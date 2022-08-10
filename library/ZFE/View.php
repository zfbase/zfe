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
 * @method string|ZFE_View_Helper_AbstractPartial abstractPartial(string $name = null, array|string $module = null, array $model = null, string $controller = null)                      Отрендерить наиболее подходящий шаблон по имени.
 * @method string abstractRender(string $file, string $controller = null)                                                                                                                Отрендерить наиболее подходящий шаблон по имени.
 * @method string action($action, $controller, $module = null, array $params = array())                                                                                                  Retrieve rendered contents of a controller action.
 * @method string alerts()                                                                                                                                                               Вывести нотификации.
 * @method string autoFormat($value, string $columnName = null, string $modelName = null)                                                                                                Автоматически отформатировать значение по ключевым префиксам названия.
 * @method string backToSearchHash(string $label = 'К результатам поиска')                                                                                                               Помощник для сокращения стандартного геттера хеша для возврата к результатам поиска.
 * @method string configTreeViewer(array $tree)                                                                                                                                          Отобразить дерево со сворачивающимися уровнями.
 * @method ZFE_View_Helper_ControlTabs controlTabs(?ZFE_Model_AbstractRecord $item = null)                                                                                               Помощник использования абстрактных вьюшек.
 * @method string crazyButtons(array $buttons, string $class = 'btn btn-default')                                                                                                        Вывести кнопки.
 * @method string dateForHuman(DateTime|string $date)                                                                                                                                    Человеческое написание даты.
 * @method string dateTime(string $dateTime, bool $time = true)                                                                                                                          Форматировать дату (и время).
 * @method string dateTimeCompact(string $dateTime)                                                                                                                                      Преобразовать дату в короткий формат.
 * @method string duration(integer $seconds, bool $short = true)                                                                                                                         Форматировать продолжительность.
 * @method string fieldset(string $name, string $content, array $attribs = null)                                                                                                         Render HTML fieldset.
 * @method string formatArray(array $array, string $listType = 'nolist')                                                                                                                 Отформатировать запрос.
 * @method string formatSql(string $query)                                                                                                                                               Отформатировать запрос.
 * @method string formAutocomplete(string $name, array $value = null, array $attribs = null)                                                                                             Сгенерировать элемент автокомплита одного значения.
 * @method string formClearfix($name)                                                                                                                                                    Элемент формы Clearfix.
 * @method string formDatelist(string $name, string|array<string> $value = null, array $attribs = null)                                                                                  Элемент формы Datelist.
 * @method string formDuration(string $name, string $value = null, array $attribs = null)                                                                                                Элемент формы Duration.
 * @method string formMultiAutocomplete(string $name, array $value = null, array $attribs = null)                                                                                        Сгенерировать элемент автокомплита нескольких значений.
 * @method string historyMeta(AbstractRecord $item, bool $showCreator = true, bool $showEditor = true, bool $showVersion = true)                                                         Вернуть отформатированные сведений о создании и последнем редактирование записи.
 * @method ZFE_View_Helper_HopsHistory hopsHistory()                                                                                                                                     Получить экземпляр помощника HopsHistory.
 * @method string humanFileSize(integer  $bytes, integer  $precision = 2)                                                                                                                Преобразовать размер в байтах в более подходящую размерность.
 * @method bool isAllowedMe(string $resource = null, string $privilege = null)                                                                                                           Проверить наличие доступа.
 * @method string lastEditedCell(AbstractRecord $item, string $class = '')                                                                                                               Генерировать ячейку с информацией о последнем изменении записи.
 * @method string menuItems(array|Zend_Config $pages = null, bool $autoActive = true, bool $disabledAcl = false, bool $dropdownEnable = true)                                            Сгенерировать меню.
 * @method string notices()                                                                                                                                                              Вернуть код вывода нотификации.
 * @method string number(float $number, integer  $decimals = 0, string $dec_point = '.', string $thousands_sep = '&nbsp;')                                                               Отформатировать запрос.
 * @method string paginator(array $options = [], ZFE_Paginator $paginator = null)                                                                                                        Собрать пагинатор.
 * @method string period(DateTime|string $start, DateTime|string $end, bool $showTime = false)                                                                                           Форматировать период.
 * @method ZFE_View_Helper_SearchPages searchPages()                                                                                                                                     Кнопки для перехода к предыдущему и следующему результату поиска.
 * @method string shortenText(string $text, integer $max_len = 100)                                                                                                                      Укоротить текст до определенного размера.
 * @method string showTitles(array|Doctrine_Collection|Traversable $items, string $field = null, string $separator = ', ', integer $maxElements = 0, callback|string $linkMethod = null) Вывести список значений поля коллекции записей.
 * @method string sortableHeadCell(string $field, string $title = null, string $cellClass = '')                                                                                          Получить ячейку заголовка сортируемого поля таблицы.
 * @method string tag(string $name, array $attribs = [], string $content = '')                                                                                                           Генератор HTML-тегов.
 * @method string timeDiff(?string $base, ?string $time)                                                                                                                                 Получить форматированный промежуток времени, прошедший с $base до $time.
 * @method string title(string $title)                                                                                                                                                   Задать заголовок страницы.
 * @method string|void viewRow(?AbstractRecord $item, string|array<string|callable> $field, string $class = null)                                                                        Собрать строку отображения поля для страницы просмотра записи.
 * @method string|void viewValue(AbstractRecord $item, string|array<string|callable> $field)                                                                                             Получить значение поля для страницы просмотра записи.
 * @method string webpack(string $filename)                                                                                                                                              Webpack.
 *
 * @property-read string $containerClass  Класс контроллера
 * @property-read string $controllerName  Имя контроллера
 * @property-read string $actionName      Имя действия
 */
class ZFE_View extends Zend_View
{
}
