import * as bootstrap from 'bootstrap';
import $ from 'jquery';

/**
 * jQuery-интерфейс плагинов Bootstrap 5: $(el).modal('show'), $(el).popover({...}) и т.д.
 *
 * Bootstrap регистрирует его сам, только если window.jQuery задан к моменту загрузки модуля,
 * что при импорте ES-модулей не гарантировано, поэтому регистрируем явно.
 */
/** Статические члены компонентов Bootstrap, которые использует его jQuery-интерфейс (в @types/bootstrap описаны не у всех) */
interface BootstrapPlugin {
  NAME: string;
  jQueryInterface: unknown;
}

const plugins = [
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
] as unknown as BootstrapPlugin[];

/** Ячейка jQuery-плагина в $.fn: jQuery-интерфейс компонента Bootstrap с noConflict */
interface PluginSlot {
  (this: JQuery, ...args: unknown[]): unknown;
  Constructor: unknown;
  noConflict: () => PluginSlot;
}

const fn = $.fn as unknown as Record<string, PluginSlot | undefined>;

plugins.forEach((plugin) => {
  const name = plugin.NAME;
  const previous = fn[name];
  const slot = plugin.jQueryInterface as PluginSlot;
  fn[name] = slot;
  slot.Constructor = plugin;
  slot.noConflict = () => {
    fn[name] = previous;
    return slot;
  };
});

window.bootstrap = bootstrap;

export default bootstrap;
