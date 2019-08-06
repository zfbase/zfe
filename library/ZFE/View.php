<?php

/**
 * Class ZFE_View
 *
 * Convenience methods for build in helpers (@see __call):
 *
 * @method string action($action, $controller, $module = null, array $params = array()) Retrieve rendered contents of a controller action
 * @todo дописать для остальных помощников ...
 *
 * @method string abstractRender($file, $controller = null) Отрендерить наиболее подходящий шаблон по имени
 * @method ZFE_View_Helper_ControlTabs controlTabs(?ZFE_Model_AbstractRecord $item = null) Помощник использования абстрактных вьюшек
 * @method string historyMeta(AbstractRecord $item, $showCreator = true, $showEditor = true, $showVersion = true) Вернуть отформатированные сведений о создании и последнем редактирование записи
 * @method ZFE_View_Helper_HopsHistory hopsHistory() Получить экземпляр помощника
 * @method ZFE_View_Helper_SearchPages searchPages() Кнопки для перехода к предыдущему и следующему результату поиска.
 * @method string sortableHeadCell($field, $title = null, $cellClass = '') Получить ячейку заголовка сортируемого поля таблицы.
 * @method string paginator(array $options = []) Собрать пагинатор
 * @method string period($start, $end, $showTime = false) Форматировать период
 * @method string tag($name, $attribs = [], $content = '') Генератор HTML-тегов
 * @method string title($title) Задать заголовок страницы
 * @method string webpack($filename) ...
 */
class ZFE_View extends Zend_View
{

}