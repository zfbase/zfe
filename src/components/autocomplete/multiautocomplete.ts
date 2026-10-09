import sortable from 'html5sortable/dist/html5sortable.es';
import $ from 'jquery';

import { keyCode } from '../../js/constants';
import { showEditModal } from '../modals';
import { getAcEngine } from './acEngine';

const pluginName = 'zfeMultiAutocomplete';
const defaults = {
  templates: {},
  limit: 7,
};

class ZFEMultiAutocomplete {
  $input: any;
  $group: any;
  $iconRight: any;
  $wrap: any;
  settings: any;
  $hint: any;
  placeholderWidth: any;
  engine: any;

  constructor(element: HTMLElement, options: any) {
    this.$input = $(element);
    this.$group = this.$input.closest('.multiac-wrap');
    this.$iconRight = this.$group.find('.tt-icon-right');
    this.$wrap = this.$group.find('.multiac-linked-wrap');
    this.settings = $.extend({}, defaults, this.dataAttrOptions(), options);
    this.init();
    this.$hint = this.$group.find('.tt-hint');
  }

  dataAttrOptions() {
    const { $input } = this;
    const data = $input.data();
    const name = $input.attr('name');
    $input.removeAttr('name');
    return {
      canCreate: data.create === 'allow',
      editUrl: data.editUrl, // атрибут data-edit-url
      itemForm: data.itemForm || data.itemform, // атрибут data-item-form
      minLength: data.termMinLength || 3, // атрибут data-term-min-length
      name,
      sourceUrl: data.source,
      limit: data.limit,
    };
  }

  init() {
    if (!this.settings.sourceUrl) {
      throw new Error(
        `No sourceUrl specified for zfeMultiAutocomplete name=${this.settings.name}`,
      );
    }


    if (this.isDisabled()) {
      return;
    }

    this.startSortable();
    this.initSource();
    this.initTypeahead();
    this.initHandlers();
    this.renderItems();
  }

  isDisabled() {
    return this.$wrap.hasClass('disabled');
  }

  startSortable() {
    sortable(this.$wrap);
    this.$wrap
      .on('dragstart.h5s', (e: JQuery.TriggeredEvent) => {
        this.placeholderWidth = $(e.target).width();
      })
      .on('dragenter.h5s', () => {
        $('.sortable-placeholder', this.$wrap).css({
          width: this.placeholderWidth,
        });
      })
      .on('sortupdate', (e: JQuery.TriggeredEvent) => {
        $('.linked-entity input[name$="\\[priority\\]"]', $(e.target)).each(
          (priority, $input) => {
            $($input).val(priority + 1);
          },
        );
      })
      .trigger('sortupdate');
  }

  renderItems() {
    const renderItem = this.settings.templates.item;
    if (!renderItem) {
      return;
    }
    this.$wrap.find('.linked-entity').each((i: number, entityDom: HTMLElement) => {
      const $item = $(entityDom);
      const $title = $item.find('.title');
      const data = {
        title: $title.text(),
        ...$item.data(),
      };
      $title.replaceWith(renderItem(data));
    });
  }

  hasElement(id: any) {
    let result = false;
    this.$wrap.find('.linked-entity').each((i: number, entityDom: HTMLElement) => {
      if (id == $(entityDom).find('[name*="[id]"]').val()) {
        result = true;
      }
    });
    return result;
  }

  getNewElementIndex() {
    let index = 1;
    this.$wrap.children().each((i: number, el: HTMLElement) => {
      const name = $(el).find('input').first().attr('name');
      if (!name) {
        return;
      }
      const m = name.match(/\[(\d+)\]\[/);
      if (m) {
        index = parseInt(m[1]) + 1;
      }
    });
    return index;
  }

  addElement(title: string, id?: any, data = {}, replace: JQuery | null = null, silent = false) {
    if (this.hasElement(id)) {
      return this.$wrap.find(`.linked-entity:has([name*="[id]"][value=${id}])`);
    }

    const priority = this.getNewElementIndex();

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
      $(templates.item({ ...data, title, id })).appendTo($linkedEntity);
    } else {
      $('<div class="title"/>').text(title).appendTo($linkedEntity);
    }

    if (this.settings.editUrl) {
      $('<div class="btn btn-edit">...</div>').appendTo($linkedEntity);
    } else if (this.$wrap.data('item-form')) {
      $('<a class="btn btn-form" target="_blank"/>')
        .attr('href', this.$wrap.data('item-form').replace('%d', id))
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
    if (!silent) {
      this.onChange();
    }

    return $linkedEntity;
  }

  initSource() {
    const { $wrap } = this;

    const exclude = () => {
      const ids: string[] = [];
      $("input[name$='[id]']", $wrap).each((i, el) => {
        const val = $(el).val();
        if (val) {
          ids.push(String(val));
        }
      });
      return ids;
    };

    this.engine = getAcEngine({ ...this.settings, exclude });
  }

  initTypeahead() {
    const datasetSettings = {
      source: this.engine.bind(this),
      templates: this.settings.templates,
      display: 'value',
      limit: this.settings.limit,
    };
    if (this.settings.itemForm) {
      const oldSuggestion = datasetSettings.templates.suggestion;
      datasetSettings.templates = $.extend(datasetSettings.templates, {
        suggestion: (data: any) => {
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
    this.$input.typeahead(
      {
        minLength: 0,
        highlight: true,
      },
      datasetSettings,
    );
    // this.$input.attr('autocomplete', Math.random().toString(36).substr(2, 9));
  }

  updateExcluded() {
    const v = this.$input.typeahead('val');
    this.$input.typeahead('val', '.');
    this.$input.typeahead('val', v);
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
    $input.on('typeahead:close', (e: JQuery.TriggeredEvent) => {
      if (e.keyCode !== keyCode.ESCAPE) {
        const newValue = $.trim($input.typeahead('val'));
        if (newValue !== '' && canCreate) {
          this.addElement(newValue);
        }
      }

      $input.typeahead('val', '');
      e.preventDefault();
    });

    $input.on('keypress', (e: JQuery.TriggeredEvent) => {
      if (e.keyCode === keyCode.ENTER) {
        if ($input.typeahead('val') !== '') {
          $input.trigger('typeahead:close');
          e.preventDefault();
        }
      }
    });

    // Выбор значения из списка
    $input.on('typeahead:selected', (e: JQuery.TriggeredEvent, selected: any) => {
      const { key, value, ...rest } = selected;
      this.addElement(value, key, rest);
      this.updateExcluded();
      e.preventDefault();
    });

    // Навешиваем на все существующие и будущие кнопки удаления соответствующий метод
    $wrap.on('click', '.btn-remove', (e: JQuery.TriggeredEvent) => {
      $(e.currentTarget).closest('.linked-entity').remove();
      this.updateExcluded();
      e.preventDefault();
      this.onChange();
    });

    $wrap.on('click', '.btn-edit', (e: JQuery.TriggeredEvent) => {
      e.preventDefault();
      const $item = $(e.currentTarget).closest('.linked-entity');
      const id = $item.find('input[name*="[id]"]').val();
      showEditModal({
        url: this.settings.editUrl + (id ? `/id/${id}` : ''),
        data: { title: $item.find('.title').text() },
        callback: ({ id: newId, title, ...data }: any) => {
          if (id !== data.id) {
            this.addElement(title, newId, data, $item);
          }
        },
      });
    });
  }

  disable(disable: boolean) {
    if (disable) {
      this.$input.addClass('disabled');
      this.$iconRight.addClass('tt-disabled');
      this.$hint.css(
        'background',
        'none 0% 0% / auto repeat scroll padding-box border-box rgb(238, 238, 238)',
      );
    } else {
      this.$input.removeClass('disabled');
      this.$iconRight.removeClass('tt-disabled');
      this.$hint.css(
        'background',
        'none 0% 0% / auto repeat scroll padding-box border-box rgb(255, 255, 255)',
      );
    }

    this.$input.attr('disabled', disable);
    this.$hint.attr('disabled', disable);
  }

  addValue(id: any, title: string, data = {}) {
    return this.addElement(title, id, data);
  }

  clear() {
    this.$wrap.empty();
  }

  setValues(values: any) {
    this.clear();
    values.forEach(({ id, title, ...data }: any) =>
      this.addElement(title, id, data, null, true),
    );
  }

  currentValue() {
    const values: Record<string, Record<string, string>> = {};
    this.$wrap.find('input').each((i: number, el: HTMLElement) => {
      const input = el as HTMLInputElement;
      const [, n, key] = input.name.split(/[[\]]+/);
      if (!values[n]) {
        values[n] = {};
      }
      values[n][key] = input.value;
    });
    return Object.values(values);
  }

  onChange() {
    this.$input.trigger('zfe.ac.change', [this.currentValue()]);
    this.$input.get(0).dispatchEvent(new Event('zfe.ac.change'));
  }
}

$.fn[pluginName] = function zfeMultiAutocomplete(
  this: JQuery,
  options?: any,
  ...args: any[]
) {
  const results: any[] = [];
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
    case 0:
      return $elements;
    case 1:
      return results.pop();
    default:
      return results;
  }
};
