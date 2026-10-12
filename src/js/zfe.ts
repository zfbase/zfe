import autosize from 'autosize';
import 'inputmask/dist/inputmask/jquery.inputmask';
import $ from 'jquery';
import 'zfe-typeahead/dist/typeahead.jquery';

import '../types';
import bootstrap from './bootstrap';

import '../components/keyboard';

import '../components/audio';
import '../components/autocomplete/autocomplete';
import '../components/autocomplete/multiautocomplete';
import '../components/checkUnsavedFormData';
import '../components/consoleManager';
import debug from '../components/debug';
import '../components/duplicates';
import historyDiff from '../components/historyDiff';
import '../components/merge';
import '../components/mergeHelper';
import { makeModal, showEditModal } from '../components/modals';
import '../components/onePress';
import initPlaceholders from '../components/placeholders';
import '../components/tableStickyHeader';
import initTasksIndex from '../components/tasks';
import '../components/uploadAjax';
import '../lib/jquery.tmpl';
import { initZfeFileElement } from './initZfeFileElement';

const { confirm } = window;

const matchControllerAction = (arg: ZfeRouteMatcher, val: string): boolean => {
  if (typeof arg === 'function') {
    return arg(val);
  }
  if (Array.isArray(arg)) {
    return arg.indexOf(val) !== -1;
  }
  if (arg === null || arg === undefined || arg === '*') {
    return true;
  }
  return arg === val;
};

const ZFE = {
  initialMethods: <string[]>[
    'initAudio',
    'initAutocompletes',
    'initMultiAC',
    'initCheckAll',
    'initCheckUnsavedFormData',
    'initConfirm',
    'initDuplicates',
    'initFormFileHelper',
    'initHtmlEditors',
    'initInputMask',
    'initItemDetailsPopover',
    'initMerge',
    'initMergeHelper',
    'initRangeInputs',
    'initTableRowLinkHelper',
    'initTableStickyHeader',
    'initTextareaAutosize',
    'initUploadAjax',
    'initPlaceholders',
    'initRest',
    'initFileAjax',
  ],

  /** Подключаемый приложением визуальный редактор (например, обертка над CKEditor) */
  htmlEditor: undefined as ZfeHtmlEditor | undefined,

  autocompleteTemplates: {} as Record<string, ZfeAutocompleteTemplates>,
  ckeditorConfig: {} as Record<string, unknown>,

  initRest: (container: ZfeContainer) => {
    debug();
    historyDiff(container);
  },

  /** Включить адаптированные аудио плееры */
  initAudio: (container: ZfeContainer) => {
    $('audio.zfe-audio', container).zfeAudio();
  },

  /** Набор шаблонов автодополнения по имени (атрибут data-templateset) */
  getAutocompleteTemplates: (templateSet?: string): ZfeAutocompleteTemplates =>
    (templateSet !== undefined && ZFE.autocompleteTemplates?.[templateSet]) || {},

  /** Настроить автодополнение одного значения */
  initAutocompletes: (container: ZfeContainer) => {
    $('input.autocomplete:not(.custom-engine)', container).each((i, el) => {
      const $input = $(el);
      $input.zfeAutocomplete({
        templates: ZFE.getAutocompleteTemplates($input.data('templateset')),
      });
    });
  },

  /** Настроить автодополнение нескольких значений */
  initMultiAC: (container: ZfeContainer) => {
    $('input.multiac:not(.custom-engine)', container).each((i, el) => {
      const $input = $(el);
      $input.zfeMultiAutocomplete({
        templates: ZFE.getAutocompleteTemplates($input.data('templateset')),
      });
    });
  },

  /** Флаг для выставления статуса всех дочерних флажков */
  initCheckAll: (container: ZfeContainer) => {
    $(container).on('click', '[data-action="check-all"]', (event) => {
      const $this = $(event.currentTarget);
      const $checkboxes = $($this.data('target'));
      $checkboxes.prop('checked', $this.prop('checked'));
      $checkboxes.trigger('change');
    });

    $('[data-action="check-all"]', container).each((i, checkAll) => {
      const $checkAll = $(checkAll);
      const target = $checkAll.data('target');

      $(document).on('click', target, () => {
        if ($(`${target}:checked`).length === 0) {
          $checkAll.prop('indeterminate', false).prop('checked', false);
        } else if ($(`${target}:not(:checked)`).length === 0) {
          $checkAll.prop('indeterminate', false).prop('checked', true);
        } else {
          $checkAll.prop('indeterminate', true);
        }
      });
    });
  },

  /** Запретить переход со страницы при не сохраненных изменениях */
  initCheckUnsavedFormData: (container: ZfeContainer) => {
    $('.form-edit', container).checkUnsavedFormData();
  },

  /** Запрашивать подтверждение действия по атрибуту data-confirm */
  initConfirm: (container: ZfeContainer) => {
    if (confirm) {
      $(container).on('click', '[data-confirm]', (event) =>
        confirm($(event.currentTarget).data('confirm'))
      );
    }
  },

  /** data-action="merge-duplications" */
  initDuplicates: (container: ZfeContainer) => {
    $('.zfe-duplications', container).zfeDuplicates();
  },

  /** Замена файла для элемента загрузки одного файла */
  initFormFileHelper: (container: ZfeContainer) => {
    $(container).on('click', '[data-btn="replace"]', (event) => {
      const $btn = $(event.currentTarget);
      $($btn.data('new-upload')).removeClass('d-none');
      $($btn.data('current')).remove();
      $btn.hide();
    });
  },

  /** Настроить визуальные HTML-редакторы */
  initHtmlEditors: (container: ZfeContainer) => {
    $('.html-editor', container).each((i, el) => {
      if (ZFE.htmlEditor) {
        ZFE.htmlEditor.create(el, ZFE.ckeditorConfig);
      }
    });
  },

  /** Подключить маски для полей ввода */
  initInputMask: (container: ZfeContainer) => {
    $(':input', container).inputmask();
  },

  /** Всплывающая справка по всем заполненным полям записи */
  initItemDetailsPopover: (container: ZfeContainer) => {
    $('.item-details-icon', container).popover({
      content(this: HTMLElement) {
        return $(this)
          .closest('.item-details')
          .find('.item-details-body')
          .html() ?? '';
      },
      allowList: {
        ...bootstrap.Popover.Default.allowList,
        table: [],
        thead: [],
        tbody: [],
        tr: [],
        th: [],
        td: [],
      },
    });
  },

  /** data-action="merge" */
  initMerge: (container: ZfeContainer) => {
    $('.zfe-merge', container).zfeMerge();
  },

  /** data-action="merge-helper" */
  initMergeHelper: (container: ZfeContainer) => {
    $('.zfe-merge-helper', container).zfeMergeHelper();
  },

  /** Элемент формы интервал */
  initRangeInputs: () => {
    $('input[type=range]')
      .on('input', (event) => {
        const $input = $(event.currentTarget);
        $input.attr('data-value', String($input.val()));
      })
      .trigger('input');
  },

  /** Помощник для наделения строк функционалом ссылок */
  initTableRowLinkHelper: (container: ZfeContainer) => {
    $(container).on('click', 'tr[role="button"]', (event) => {
      window.location = $(event.currentTarget).data('href');
    });
  },

  /** Включить прилипание заголовков  */
  initTableStickyHeader: (container: ZfeContainer) => {
    $('.table-sticky-header', container).tableStickyHeader();
  },

  /** Настроить автоматическую высоту многострочных текстовых полей */
  initTextareaAutosize: (container: ZfeContainer) => {
    setTimeout(() => {
      autosize($('textarea.autosize', container));
    });
  },

  /** AJAX загрузчик файлов */
  initUploadAjax: (container: ZfeContainer) => {
    $('input[data-ajax-url]', container).zfeUploadAjax();
  },

  initFileAjax: (container: ZfeContainer) => {
    $('.zfe-files-ajax:not(.custom-engine)', container).each((_, el) =>
      initZfeFileElement(el)
    );
  },

  initPlaceholders: (container: ZfeContainer) => initPlaceholders(container),

  /** Инициализировать все компоненты из initialMethods внутри контейнера */
  initContainer: (container: ZfeContainer): void => {
    $.each(ZFE.initialMethods, (i, method) => (ZFE as unknown as Record<string, ZfeInitMethod>)[method](container));
  },

  /** Инициализация приложения */
  init: (app?: ZfeAppConfig): void => {
    if (typeof app === 'object' && (app as unknown) !== window) {
      $.extend(ZFE, app);
    }
    ZFE.initContainer(document.body);
  },

  /** Помощник для инициализации скриптов только для текущего контроллера и экшена */
  controllerActionScriptHelper: (
    controller: ZfeRouteMatcher,
    action: ZfeRouteMatcher,
    callback: () => void,
  ): void => {
    const classes = Array.from(document.body.classList);
    const controllerName = (
      classes.find((c) => c.indexOf('controller-') === 0) || ''
    ).slice(11);
    const actionName = (
      classes.find((c) => c.indexOf('action-') === 0) || ''
    ).slice(7);
    if (
      matchControllerAction(controller, controllerName) &&
      matchControllerAction(action, actionName)
    ) {
      callback();
    }
  },
};

ZFE.controllerActionScriptHelper('tasks', 'index', initTasksIndex);

declare global {
  interface Window {
    /** Глобальный объект ZFE */
    ZFE: typeof ZFE;
  }
}

window.ZFE = ZFE;

export { makeModal, showEditModal };
export default ZFE;
