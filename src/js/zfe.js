import $ from 'jquery';
import autosize from 'autosize';
import 'zfe-typeahead/dist/typeahead.jquery';
import 'inputmask/dist/inputmask/jquery.inputmask';

import '../components/keyboard';

import '../lib/jquery.tmpl';
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
import '../components/modals';
import '../components/tableStickyHeader';
import '../components/uploadAjax';
import '../components/onePress';
import initPlaceholders from '../components/placeholders';
import initTasksIndex from '../components/tasks';

// eslint-disable-next-line import/order
import { createFileAjax } from 'zfe-files';

const { confirm } = window;

const matchControllerAction = (arg, val) => {
  if (typeof arg === 'function') {
    return arg(val);
  }
  if (Array.isArray(arg)) {
    return arg.indexOf(val) !== -1;
  }
  if (arg === null || arg === 'undefined' || arg === '*') {
    return true;
  }
  return arg === val;
};

const ZFE = {
  initialMethods: [
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

  autocompleteTemplates: {},
  ckeditorConfig: {},

  initRest: (container) => {
    debug(container);
    historyDiff(container);
  },

  /** Включить адаптированные аудио плееры */
  initAudio: (container) => {
    $('audio.zfe-audio', container).zfeAudio();
  },

  getAutocompleteTemplates: templateSet => (ZFE.autocompleteTemplates
    && ZFE.autocompleteTemplates[templateSet]) || {},

  /** Настроить автодополнение одного значения */
  initAutocompletes: (container) => {
    $('input.autocomplete:not(.custom-engine)', container).each((i, el) => {
      const $input = $(el);
      $input.zfeAutocomplete({
        templates: ZFE.getAutocompleteTemplates($input.data('templateset')),
      });
    });
  },

  /** Настроить автодополнение нескольких значений */
  initMultiAC: (container) => {
    $('input.multiac:not(.custom-engine)', container).each((i, el) => {
      const $input = $(el);
      $input.zfeMultiAutocomplete({
        templates: ZFE.getAutocompleteTemplates($input.data('templateset')),
      });
    });
  },

  /** Флаг для выставления статуса всех дочерних флажков */
  initCheckAll: (container) => {
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
  initCheckUnsavedFormData: (container) => {
    $('.form-edit', container).checkUnsavedFormData();
  },

  /** Настроить автоматическую высоту многострочных текстовых полей */
  initConfirm: (container) => {
    if (confirm) {
      $(container).on('click', '[data-confirm]', event => confirm($(event.currentTarget).data('confirm')));
    }
  },

  /** data-action="merge-duplications" */
  initDuplicates: (container) => {
    $('.zfe-duplications', container).zfeDuplicates();
  },

  /** Замена файла для элемента загрузки одного файла */
  initFormFileHelper: (container) => {
    $(container).on('click', '[data-btn="replace"]', (event) => {
      const $btn = $(event.currentTarget);
      $($btn.data('new-upload')).removeClass('hide');
      $($btn.data('current')).remove();
      $btn.hide();
    });
  },

  /** Настроить визуальные HTML-редакторы */
  initHtmlEditors: (container) => {
    $('.html-editor', container).each((i, el) => {
      if (ZFE.htmlEditor) {
        ZFE.htmlEditor.create(el, ZFE.ckeditorConfig);
      }
    });
  },

  /** Подключить маски для полей ввода */
  initInputMask: (container) => {
    $(':input', container).inputmask();
  },

  /** Всплывающая справка по всем заполненным полям записи */
  initItemDetailsPopover: (container) => {
    $('.item-details-icon', container).popover({
      content: function getBody() {
        return $(this).closest('.item-details').find('.item-details-body').html();
      },
    });
  },

  /** data-action="merge" */
  initMerge: (container) => {
    $('.zfe-merge', container).zfeMerge();
  },

  /** data-action="merge-helper" */
  initMergeHelper: (container) => {
    $('.zfe-merge-helper', container).zfeMergeHelper();
  },

  /** Элемент формы интервал */
  initRangeInputs: () => {
    $('input[type=range]').on('input', (event) => {
      const $input = $(event.currentTarget);
      $input.attr('data-value', $input.val());
    }).trigger('input');
  },

  /** Помощник для наделения строк функционалом ссылок */
  initTableRowLinkHelper: (container) => {
    $(container).on('click', 'tr[role="button"]', (event) => {
      window.location = $(event.currentTarget).data('href');
    });
  },

  /** Включить прилипание заголовков  */
  initTableStickyHeader: (container) => {
    $('.table-sticky-header', container).tableStickyHeader();
  },

  /** Настроить автоматическую высоту многострочных текстовых полей */
  initTextareaAutosize: (container) => {
    autosize($('textarea.autosize', container));
  },

  /** AJAX загрузчик файлов */
  initUploadAjax: (container) => {
    $('input[data-ajax-url]', container).zfeUploadAjax();
  },

  initFileAjax: (container) => {
    $('.zfe-files-ajax:not(.custom-engine)', container).each((i, el) => createFileAjax(el));
  },

  initPlaceholders: container => initPlaceholders(container),

  initContainer: container => $.each(ZFE.initialMethods, (i, method) => ZFE[method](container)),

  /** Инициализация приложения */
  init: (app) => {
    if (typeof app === 'object' && app !== window) {
      $.extend(ZFE, app);
    }
    ZFE.initContainer(document.body);
  },

  /** Помощник для инициализации скриптов только для текущего контроллера и экшена */
  controllerActionScriptHelper: (controller, action, callback) => {
    const classes = Array.from(document.body.classList);
    const controllerName = (classes.find(c => c.indexOf('controller-') === 0) || '').substr(11);
    const actionName = (classes.find(c => c.indexOf('action-') === 0) || '').substr(7);
    if (matchControllerAction(controller, controllerName)
      && matchControllerAction(action, actionName)) {
      callback();
    }
  },
};

ZFE.controllerActionScriptHelper('tasks', 'index', initTasksIndex);

window.ZFE = ZFE;

export default ZFE;
