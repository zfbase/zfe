import $ from 'jquery';
import Bloodhound from 'bloodhound-js';
import sortable from 'html5sortable/dist/html5sortable.es';

import { keyCode } from '../../js/constants';
import { showEditModal } from '../modals';

const pluginName = 'zfeMultiAutocomplete';
const defaults = {
  templates: {},
};

class ZFEMultiAutocomplete {
  constructor(element, options) {
    this.$input = $(element);
    this.$group = this.$input.closest('.multiac-wrap');
    this.$wrap = this.$group.find('.multiac-linked-wrap');
    this.settings = $.extend({}, defaults, this.dataAttrOptions(), options);
    this.init();
  }

  dataAttrOptions() {
    const { $input } = this;
    const data = $input.data();
    const name = $input.attr('name');
    $input.removeAttr('name');
    return {
      canCreate: data.create === 'allow',
      editUrl: data.editUrl,
      itemForm: data.itemform,
      minLength: data.termMinLength || 3,
      name,
      sourceUrl: data.source,
    };
  }

  init() {
    if (!this.settings.sourceUrl) {
      throw new Error(`No sourceUrl specified for zfeMultiAutocomplete name=${this.settings.name}`);
    }

    this.replaceFeedback();

    if (this.isDisabled()) {
      return;
    }
    
    this.startSortable();
    this.initBloodhound();
    this.initTypeahead();
    this.initHandlers();
    this.renderItems();
  }

  replaceFeedback() { // @todo Хорошо бы делать на сервере, а не при клиенте
    this.$group.closest('.has-feedback').find('.form-control-feedback')
      .appendTo(this.$group.find('.tt-icon-right'));
  }

  isDisabled() {
    return this.$wrap.hasClass('disabled');
  }

  startSortable() {
    sortable(this.$wrap);
    this.$wrap.on('dragstart.h5s', (e) => {
      this.placeholderWidth = $(e.target).width();
    }).on('dragenter.h5s', () => {
      $('.sortable-placeholder', this.$wrap)
        .css({ width: this.placeholderWidth });
    }).on('sortupdate', (e) => {
      $('.linked-entity input[name$="\\[priority\\]"]', $(e.target))
        .each((priority, $input) => $($input).val(priority + 1));
    })
      .trigger('sortupdate');
  }

  renderItems() {
    const renderItem = this.settings.templates.item;
    if (!renderItem) {
      return;
    }
    this.$wrap.find('.linked-entity').each((i, entityDom) => {
      const $item = $(entityDom);
      const $title = $item.find('.title');
      const data = {
        title: $title.text(),
        ...$item.data(),
      };
      $title.replaceWith(renderItem(data));
    });
  }

  hasElement(id) {
    let result = false;
    this.$wrap.find('.linked-entity').each((i, entityDom) => {
      if (id == $(entityDom).find('[name*="\[id\]"]').val()) {
        result = true;
      }
    });
    return result;
  }

  addElement(title, id, data = {}, replace = null) {
    if (this.hasElement(id)) {
      return this.$wrap.find(`.linked-entity:has([name*="\[id\]"][value=${id}])`);
    }

    const priority = this.$wrap.children().length + 1;
    const $linkedEntity = $('<div class="linked-entity" />').data(data);
    const $inputs = $('<div class="inputs" />').appendTo($linkedEntity);
    const { name, templates } = this.settings;

    if (!id) {
      $linkedEntity.addClass('linked-entity-new');
    }

    $(`<input type="hidden" name="${name}[${priority}][id]"/>`)
      .attr('value', id || '')
      .appendTo($inputs);
    $(`<input type="hidden" name="${name}[${priority}][title]"/>`)
      .attr('value', title)
      .appendTo($inputs);
    $(`<input type="hidden" name="${name}[${priority}][priority]"/>`)
      .attr('value', priority)
      .appendTo($inputs);
    if (templates.item) {
      $(templates.item({ ...data, title, id }))
        .appendTo($linkedEntity);
    } else {
      $('<div class="title"/>')
        .text(title)
        .appendTo($linkedEntity);
    }

    if (this.settings.editUrl) {
      $('<div class="btn btn-edit">...</div>')
        .appendTo($linkedEntity);
    } else if (this.$wrap.data('itemform')) {
      $('<a class="btn btn-form" target="_blank"/>')
        .attr('href', this.$wrap.data('itemform').replace('%d', id))
        .append('<span class="glyphicon glyphicon-share-alt"/>')
        .appendTo($linkedEntity);
    }

    $('<div class="btn btn-remove"/>')
      .append('<span class="glyphicon glyphicon-remove"/>')
      .appendTo($linkedEntity);

    if (replace) {
      replace.replaceWith($linkedEntity);
    } else {
      $linkedEntity.appendTo(this.$wrap);
    }

    // Переподключаем сортировку
    sortable(this.$wrap, 'destroy');
    this.startSortable();
    this.onChange();

    return $linkedEntity;
  }

  initBloodhound() {
    const { minLength, sourceUrl } = this.settings;
    const { $wrap } = this;
    this.engine = new Bloodhound({
      datumTokenizer: Bloodhound.tokenizers.obj.whitespace('value'),
      queryTokenizer: Bloodhound.tokenizers.whitespace,
      limit: 1000, // более точнее ограничение производится на сервере
      remote: {
        url: sourceUrl,
        replace: (initialUrl, query) => {
          let url = initialUrl;
          if (query.length >= minLength) {
            url += `/?term=${encodeURIComponent(query)}`;
          } else if (query.length > 0) {
            return false;
          }

          const ids = [];
          $("input[name$='[id]']", $wrap).each((i, el) => {
            const val = $(el).val();
            if (val) {
              ids.push(val);
            }
          });
          if (ids.length) {
            url += (query.length >= minLength) ? '&' : '?';
            url += `exclude=${ids.join(',')}`;
          }

          return url;
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
      limit: 7,
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
    //this.$input.attr('autocomplete', Math.random().toString(36).substr(2, 9));
  }

  initHandlers() {
    const { $input, $group, $wrap } = this;
    const { canCreate } = this.settings;

    // Обработка клика по иконке
    $('i', $group).on('click', () => {
      $input.trigger($.Event('keydown', { keyCode: keyCode.DOWN }));
      $input.focus();
    });

    // Событие завершения работы автокомплита (значение выбрано/указано)
    $input.on('typeahead:closed', (e) => {
      if (e.keyCode !== keyCode.ESCAPE) {
        const newValue = $.trim($input.typeahead('val'));
        if (newValue !== '' && canCreate) {
          this.addElement(newValue);
        }
      }

      $input.typeahead('val', '');
      e.preventDefault();
    });

    $input.on('keypress', (e) => {
      if (e.keyCode === keyCode.ENTER) {
        $input.trigger('typeahead:closed');
        e.preventDefault();
      }
    });

    // Выбор значения из списка
    $input.on('typeahead:selected', (e, selected) => {
      const { key, value, ...rest } = selected;
      this.addElement(value, key, rest);
      $input.typeahead('val', '');
      e.preventDefault();
    });

    // Навешиваем на все существующие и будущие кнопки удаления соответствующий метод
    $wrap.on('click', '.btn-remove', (e) => {
      $(e.currentTarget).closest('.linked-entity').remove();
      e.preventDefault();
      this.onChange();
    });

    $wrap.on('click', '.btn-edit', (e) => {
      e.preventDefault();
      const $item = $(e.currentTarget).closest('.linked-entity');
      const id = $item.find('input[name*="[id]"]').val();
      showEditModal({
        url: this.settings.editUrl + (id ? `/id/${id}` : ''),
        data: { title: $item.find('.title').text() },
        callback: ({ id: newId, title, ...data }) => {
          if (id !== data.id) {
            this.addElement(title, newId, data, $item);
          }
        },
      });
    });
  }

  addValue(id, title, data = {}) {
    return this.addElement(title, id, data);
  }

  currentValue() {
    const values = {};
    this.$wrap.find('input').each((i, el) => {
      const [, n, key] = el.name.split(/[\[\]]+/);
      if (!values[n]) {
        values[n] = {};
      }
      values[n][key] = el.value
    });
    return Object.values(values);
  }

  onChange() {
    this.$input.trigger('zfe.ac.change', [this.currentValue()]);
  }
}

$.fn[pluginName] = function zfeMultiAutocomplete(options, ...args) {
  let results = [];
  const $elements = this.each((i, el) => {
    if (!$.data(el, `plugin_${pluginName}`)) {
      $.data(el, `plugin_${pluginName}`, new ZFEMultiAutocomplete(el, options));
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
