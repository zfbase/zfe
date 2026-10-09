import * as bootstrap from 'bootstrap';
import $ from 'jquery';

/**
 * jQuery-интерфейс плагинов Bootstrap 5: $(el).modal('show'), $(el).popover({...}) и т.д.
 *
 * Bootstrap регистрирует его сам, только если window.jQuery задан к моменту загрузки модуля,
 * что при импорте ES-модулей не гарантировано, поэтому регистрируем явно.
 */
const plugins: any[] = [
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
];

plugins.forEach((plugin) => {
  const name = plugin.NAME;
  const fn = $.fn as any;
  const previous = fn[name];
  fn[name] = plugin.jQueryInterface;
  fn[name].Constructor = plugin;
  fn[name].noConflict = () => {
    fn[name] = previous;
    return plugin.jQueryInterface;
  };
});

(window as any).bootstrap = bootstrap;

export default bootstrap;
