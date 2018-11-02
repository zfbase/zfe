import $ from 'jquery';
import Bloodhound from 'bloodhound-js';

import { keyCode } from '../../js/constants';

const pluginName = 'zfeAutocomplete';
const defaults = {
  templates: {},
};

class ZFEAutocomplete {
  constructor(element, options) {
    this.input = $(element);
    this.group = this.input.closest('.autocomplete-wrap');
    this.settings = $.extend({}, defaults, this.dataAttrOptions(), options);
    this.init();
  }

  dataAttrOptions() {
    const { input, group } = this;
    const data = input.data();
    const name = input.attr('name');
    input.removeAttr('name');
    return {
      name,
      idInput: group.find(`[name="${name}[id]"]`),
      titleInput: group.find(`[name="${name}[title]"]`),
      sourceUrl: data.source,
      canCreate: data.create === 'allow',
      itemForm: data.itemform,
      minLength: data.termMinLength || 3,
    };
  }

  init() {
    if (!this.settings.sourceUrl) {
      throw new Error(`No sourceUrl specified for zfeAutocomplete name=${this.settings.name}`);
    }
    this.initBloodhound();
    this.initTypeahead();
    this.initHandlers();
  }

  initBloodhound() {
    const { minLength, sourceUrl } = this.settings;
    this.engine = new Bloodhound({
      datumTokenizer: Bloodhound.tokenizers.obj.whitespace('value'),
      queryTokenizer: Bloodhound.tokenizers.whitespace,
      limit: 1000,
      remote: {
        url: sourceUrl,
        replace: (url, query) => {
          if (query.length >= minLength) {
            return `${url}/?term=${encodeURIComponent(query)}`;
          }
          if (query.length === 0) {
            return sourceUrl;
          }
          return false;
        },
      },
    });
    this.engine.initialize();
  }

  initTypeahead() {
    const datasetSettings = {
      name: this.settings.name,
      source: this.engine.ttAdapter(),
      templates: this.settings.templates,
      display: 'value',
    };
    if (this.settings.itemForm) {
      const oldSuggestion = datasetSettings.templates.suggestion;
      datasetSettings.templates = $.extend(datasetSettings.templates, {
        suggestion: (data) => {
          let content = data.value;
          if (typeof oldSuggestion === 'function') {
            content = oldSuggestion(data);
          }
          const href = this.settings.itemForm.replace('%d', data.key);
          return `<div><a style="float: right;" href="${href}"
            onclick="event.stopPropagation();" target="_blank">
            <i class="glyphicon glyphicon-share-alt"></i></a>${content}</div>`;
        },
      });
    }
    this.input.typeahead({
      minLength: 0, // проверка переезжает в Bloodhound
      highlight: true,
    }, datasetSettings);
    this.input.attr('autocomplete', Math.random().toString(36).substr(2, 9));
  }

  initHandlers() {
    const { input, group } = this;
    const { idInput, titleInput, canCreate } = this.settings;

    // Обработка клика по иконке
    $('i', group).on('click', () => {
      input.trigger($.Event('keydown', { keyCode: 40 }));
      input.focus();
    });

    // Событие заверения работы автокомплита (значение выбрано/указано)
    this.input.on('typeahead:close', (e) => {
      if (e.keyCode === keyCode.ESCAPE) {
        input
          .typeahead('val', titleInput.val())
          .typeahead('close');
        e.preventDefault();
        return;
      }

      const newValue = $.trim(input.typeahead('val'));

      if (newValue === '') {
        idInput.val('');
        titleInput.val('');
        group.removeClass('has-warning');
      } else if (newValue !== titleInput.val()) {
        if (canCreate) {
          idInput.val('');
          titleInput.val(newValue);
          group.addClass('has-warning');
        } else {
          input.typeahead('val', '');
        }
      }
    });

    this.input.on('keypress', (e) => {
      if (e.keyCode === keyCode.ENTER) {
        input.trigger('typeahead:close');
        e.preventDefault();
      }
    });

    // Выбор значения из списка
    this.input.on('typeahead:select', (e, selected) => {
      idInput.val(selected.key);
      titleInput.val(selected.value);
      group.removeClass('has-warning');
    });
  }
}

$.fn[pluginName] = function zfeAutocomplete(options) {
  return this.each((i, el) => {
    if (!$.data(el, `plugin_${pluginName}`)) {
      $.data(el, `plugin_${pluginName}`, new ZFEAutocomplete(el, options));
    }
  });
};
