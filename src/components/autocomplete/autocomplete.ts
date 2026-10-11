import $ from 'jquery';

import { keyCode } from '../../js/constants';
import { getAcEngine } from './acEngine';

const pluginName = 'zfeAutocomplete';
const defaults = {
  templates: {},
  limit: 7,
  exclude: null,
};

/** Связанное поле формы `name[поле]`, значение которого берется из выбранной подсказки */
interface OtherInput {
  element: HTMLInputElement;
  name: string;
}

/** Итоговые настройки: опции плагина поверх данных, прочитанных из атрибутов поля */
interface AutocompleteSettings extends ZfeAutocompleteOptions {
  name: string | undefined;
  $idInput: JQuery;
  $titleInput: JQuery;
  otherInputs: OtherInput[];
  templates: ZfeAutocompleteTemplates;
}

class ZFEAutocomplete {
  $input: JQuery<HTMLInputElement>;
  $group: JQuery;
  $iconRight: JQuery;
  settings: AutocompleteSettings;
  $hint: JQuery;
  $inlineLink: JQuery;
  valueData: unknown;
  engine!: ReturnType<typeof getAcEngine>;

  constructor(element: HTMLElement, options?: ZfeAutocompleteOptions) {
    this.$input = $(element) as JQuery<HTMLInputElement>;
    this.$group = this.$input.closest('.autocomplete-wrap');
    this.$iconRight = this.$group.find('.tt-icon-right');
    this.settings = $.extend({}, defaults, this.dataAttrOptions(), options) as AutocompleteSettings;
    this.init();
    this.$hint = this.$group.find('.tt-hint');
    this.$inlineLink = this.$group.find('.tt-link');
    this.valueData = null;
  }

  dataAttrOptions() {
    const { $input, $group } = this;
    const data = $input.data();
    const name = $input.attr('name');
    $input.removeAttr('name');

    const $inputs = $group.find(`[name^=${name}]`);
    const otherInputs: OtherInput[] = $inputs
      .get()
      .map((i) => {
        const name = (i as HTMLInputElement).name.match(/\[(.+)\]/);
        return { element: i as HTMLInputElement, name: name ? name[1] : '' };
      })
      .filter((i) => i.name && i.name !== 'id' && i.name !== 'title');

    return {
      name,
      $idInput: $group.find(`[name="${name}[id]"]`),
      $titleInput: $group.find(`[name="${name}[title]"]`),
      otherInputs,
      sourceUrl: data.source,
      canCreate: data.create === 'allow',
      itemForm: data.itemForm || data.itemform, // атрибут data-item-form
      minLength: data.termMinLength || 3, // атрибут data-term-min-length
      limit: data.limit,
    };
  }

  init() {
    if (!this.settings.sourceUrl) {
      throw new Error(
        `No sourceUrl specified for zfeAutocomplete name=${this.settings.name}`,
      );
    }
    this.initPreHandlers();
    this.initSource();
    this.initTypeahead();
    this.initHandlers();
  }

  initSource() {
    this.engine = getAcEngine(this.settings);
  }

  initTypeahead() {
    const datasetSettings: ZfeTypeaheadDataset & { templates: ZfeAutocompleteTemplates } = {
      source: this.engine.bind(this),
      templates: this.settings.templates,
      display: 'value',
      limit: this.settings.limit,
    };

    if (this.settings.itemForm) {
      const oldSuggestion = datasetSettings.templates.suggestion;
      datasetSettings.templates = $.extend(datasetSettings.templates, {
        suggestion: (data: ZfeAutocompleteItem) => {
          let content = data.value;
          if (typeof oldSuggestion === 'function') {
            content = oldSuggestion(data);
          }
          const href = this.settings.itemForm!.replace('%d', String(data.key));
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
    // this.input.attr('autocomplete', Math.random().toString(36).substr(2, 9));

    if (this.$input.typeahead('val')) {
      this.$iconRight.addClass('tt-fill');
    }
  }

  initPreHandlers() {
    const { $input } = this;

    $input.on('keydown', (e: JQuery.TriggeredEvent) => {
      if (e.keyCode === keyCode.ESCAPE) {
        e.stopImmediatePropagation();
        $input.typeahead('val', this.getTitle()).typeahead('close');
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

    $input.on('keypress', (e: JQuery.TriggeredEvent) => {
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
    $input.on('typeahead:select', (e: JQuery.TriggeredEvent, selected: ZfeAutocompleteItem) => {
      this.setValueData(selected);
      const { key, value, ...rest } = selected;
      this.setValue({ id: key, title: value, ...rest });
    });

    // Очистка элемента
    $iconRight.find('.clear').on('click', () => this.clear());
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

    // jQuery: attr('disabled', true) выставляет атрибут, attr('disabled', false) удаляет его
    this.$input.attr('disabled', disable ? 'disabled' : null);
    this.$hint.attr('disabled', disable ? 'disabled' : null);
  }

  clear() {
    this.setValue();
  }

  getId(): string | null {
    const { $idInput } = this.settings;
    return ($idInput.val() as string) || null;
  }

  getTitle(): string {
    const { $titleInput } = this.settings;
    return $titleInput.val() as string;
  }

  getValue(): ZfeAutocompleteValue {
    return {
      id: this.getId(),
      title: this.getTitle(),
    };
  }

  setValue({ id = '', title = '', ...rest }: ZfeAutocompleteSetValue = {}) {
    const { $input, $group, $iconRight, $inlineLink } = this;
    const { $idInput, $titleInput, canCreate, itemForm, otherInputs } =
      this.settings;
    const hasId = !!id;
    const hasTitle = !!title;
    const isNew = !hasId && hasTitle;
    const isEmpty = !hasId && !hasTitle;

    if (isNew && !canCreate) {
      throw new Error(
        'Cannot set a value without id for autocomplete with canCreate === false',
      );
    }

    if ($idInput.val() === id && $titleInput.val() === title) {
      return;
    }

    $input.typeahead('val', title);
    $idInput.val(id ?? '');
    $titleInput.val(title);
    otherInputs.forEach((i) => {
      i.element.value = String(rest[i.name] ?? '');
    });
    $iconRight.toggleClass('tt-fill', !isEmpty);
    $group.toggleClass('has-warning', isNew);

    if (itemForm) {
      $inlineLink.attr('href', id ? itemForm.replace('%d', String(id)) : null);
    }

    this.$input.trigger('zfe.ac.change');
    this.$input[0].dispatchEvent(new Event('zfe.ac.change'));
  }

  getValueData() {
    return this.valueData;
  }

  setValueData(data: unknown) {
    this.valueData = data;
  }
}

type PluginMethod = (...args: unknown[]) => unknown;

$.fn[pluginName] = function zfeAutocomplete(
  this: JQuery,
  options?: ZfeAutocompleteOptions | string,
  ...args: unknown[]
): unknown {
  const results: unknown[] = [];
  const $elements = this.each((i, el) => {
    if (!$.data(el, `plugin_${pluginName}`)) {
      $.data(
        el,
        `plugin_${pluginName}`,
        new ZFEAutocomplete(el, typeof options === 'string' ? undefined : options),
      );
    }
    const item = $.data(el, `plugin_${pluginName}`) as Record<string, PluginMethod>;
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
} as JQuery['zfeAutocomplete'];
