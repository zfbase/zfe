import $ from 'jquery';
import Bloodhound from 'bloodhound-js';

import { keyCode } from '../../js/constants';

const pluginName = 'zfeAutocomplete';
const defaults = {
  templates: {},
  limit: 7,
  exclude: null,
};

class ZFEAutocomplete {
  constructor(element, options) {
    this.$input = $(element);
    this.$group = this.$input.closest('.autocomplete-wrap');
    this.$iconRight = this.$group.find('.tt-icon-right');
    this.settings = $.extend({}, defaults, this.dataAttrOptions(), options);
    this.init();
    this.$hint = this.$group.find('.tt-hint');
    this.valueData = null;
  }

  dataAttrOptions() {
    const { $input, $group } = this;
    const data = $input.data();
    const name = $input.attr('name');
    $input.removeAttr('name');
    return {
      name,
      $idInput: $group.find(`[name="${name}[id]"]`),
      $titleInput: $group.find(`[name="${name}[title]"]`),
      sourceUrl: data.source,
      canCreate: data.create === 'allow',
      itemForm: data.itemForm || data.itemform, // атрибут data-item-form
      minLength: data.termMinLength || 3, // атрибут data-term-min-length
      limit: data.limit,
    };
  }

  init() {
    if (!this.settings.sourceUrl) {
      throw new Error(`No sourceUrl specified for zfeAutocomplete name=${this.settings.name}`);
    }
    this.initPreHandlers();
    this.initBloodhound();
    this.initTypeahead();
    this.initHandlers();
  }

  initBloodhound() {
    const { minLength, sourceUrl, exclude } = this.settings;
    this.engine = new Bloodhound({
      datumTokenizer: Bloodhound.tokenizers.obj.whitespace('value'),
      queryTokenizer: Bloodhound.tokenizers.whitespace,
      limit: 1000,
      remote: {
        url: sourceUrl,
        replace: (initialUrl, query) => {
          let url = initialUrl;

          if (query.length >= minLength) {
            url += `/?term=${encodeURIComponent(query)}`;
          } else if (query.length > 0) {
            return false;
          }

          if (exclude) {
            url += (query.length >= minLength) ? '&' : '?';
            if (typeof exclude === 'function') {
              url += `exclude=${exclude().join(',')}`;
            } else {
              url += `exclude=${exclude.join(',')}`;
            }
          }

          return url;
        },
      },
    });
    this.engine.initialize();
  }

  initTypeahead() {
    const datasetSettings = {
      source: this.engine.ttAdapter(),
      templates: this.settings.templates,
      display: 'value',
      limit: this.settings.limit,
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
    this.$input.typeahead({
      minLength: 0, // проверка переезжает в Bloodhound
      highlight: true,
    }, datasetSettings);
    // this.input.attr('autocomplete', Math.random().toString(36).substr(2, 9));

    if (this.$input.typeahead('val')) {
      this.$iconRight.addClass('tt-fill');
    }
  }

  initPreHandlers() {
    const { $input } = this;

    $input.on('keydown', (e) => {
      if (e.keyCode === keyCode.ESCAPE) {
        e.stopImmediatePropagation();
        $input
          .typeahead('val', this.getTitle())
          .typeahead('close');
      }
    });
  }

  initHandlers() {
    const { $input, $group, $iconRight } = this;
    const { canCreate } = this.settings;

    // Обработка клика по иконке
    $('i', $group).on('click', () => {
      $input.trigger($.Event('keydown', { keyCode: 40 }));
      $input.focus();
    });

    // Событие завершения работы автокомплита (значение выбрано/указано)
    $input.on('typeahead:close', () => {
      const title = $.trim($input.typeahead('val'));
      if (title === '') {
        this.clear();
      } else if (title !== this.getTitle()) {
        if (canCreate) {
          this.setValue({ title });
        } else {
          this.clear();
          $input.typeahead('val', '');
        }
      }
    });

    $input.on('keypress', (e) => {
      if (e.keyCode === keyCode.ENTER) {
        const lastValue = this.getValue();
        const freshValue = $input.typeahead('val');
        if (lastValue.title !== freshValue) {
          $input.trigger('typeahead:close');
          e.preventDefault();
        }
      }
    });

    // Выбор значения из списка
    $input.on('typeahead:select', (e, selected) => {
      this.setValueData(selected);
      this.setValue({ id: selected.key, title: selected.value });
    });

    // Очистка элемента
    $iconRight.find('.clear').on('click', () => this.clear());
  }

  disable(disable) {
    if (disable) {
      this.$input.addClass('disabled');
      this.$iconRight.addClass('tt-disabled');
      this.$hint.css('background', 'none 0% 0% / auto repeat scroll padding-box border-box rgb(238, 238, 238)');
    } else {
      this.$input.removeClass('disabled');
      this.$iconRight.removeClass('tt-disabled');
      this.$hint.css('background', 'none 0% 0% / auto repeat scroll padding-box border-box rgb(255, 255, 255)');
    }

    this.$input.attr('disabled', disable);
    this.$hint.attr('disabled', disable);
  }

  clear() {
    this.setValue();
  }

  getId() {
    const { $idInput } = this.settings;
    return $idInput.val() || null;
  }

  getTitle() {
    const { $titleInput } = this.settings;
    return $titleInput.val();
  }

  getValue() {
    return {
      id: this.getId(),
      title: this.getTitle(),
    };
  }

  setValue({ id = '', title = '' } = {}) {
    const { $input, $group, $iconRight } = this;
    const { $idInput, $titleInput, canCreate } = this.settings;
    const hasId = !!id;
    const hasTitle = !!title;
    const isNew = !hasId && hasTitle;
    const isEmpty = !hasId && !hasTitle;

    if (isNew && !canCreate) {
      throw new Error('Cannot set a value without id for autocomplete with canCreate === false');
    }

    if ($idInput.val() === id && $titleInput.val() === title) {
      return;
    }

    $input.typeahead('val', title);
    $idInput.val(id);
    $titleInput.val(title);
    $iconRight.toggleClass('tt-fill', !isEmpty);
    $group.toggleClass('has-warning', isNew);

    this.$input.trigger('zfe.ac.change');
  }

  getValueData() {
    return this.valueData;
  }

  setValueData(data) {
    this.valueData = data;
  }
}

$.fn[pluginName] = function zfeAutocomplete(options, ...args) {
  const results = [];
  const $elements = this.each((i, el) => {
    if (!$.data(el, `plugin_${pluginName}`)) {
      $.data(el, `plugin_${pluginName}`, new ZFEAutocomplete(el, options));
    }
    const item = $.data(el, `plugin_${pluginName}`);
    if (typeof options === 'string' && typeof item[options] === 'function') {
      results.push(item[options](...args));
    }
  });

  switch (results.length) {
    case 0: return $elements;
    case 1: return results.pop();
    default: return results;
  }
};
