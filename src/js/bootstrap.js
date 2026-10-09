import * as bootstrap from 'bootstrap';
import $ from 'jquery';

/**
 * jQuery-интерфейс плагинов Bootstrap 5: $(el).modal('show'), $(el).popover({...}) и т.д.
 *
 * Bootstrap регистрирует его сам, только если window.jQuery задан к моменту загрузки модуля,
 * что при импорте ES-модулей не гарантировано, поэтому регистрируем явно.
 */
[
  bootstrap.Alert,
  bootstrap.Button,
  bootstrap.Carousel,
  bootstrap.Collapse,
  bootstrap.Dropdown,
  bootstrap.Modal,
  bootstrap.Offcanvas,
  bootstrap.Popover,
  bootstrap.ScrollSpy,
  bootstrap.Tab,
  bootstrap.Toast,
  bootstrap.Tooltip,
].forEach((plugin) => {
  const name = plugin.NAME;
  const previous = $.fn[name];
  $.fn[name] = plugin.jQueryInterface;
  $.fn[name].Constructor = plugin;
  $.fn[name].noConflict = () => {
    $.fn[name] = previous;
    return plugin.jQueryInterface;
  };
});

window.bootstrap = bootstrap;

export default bootstrap;
