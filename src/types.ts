// Глобальные типы: jQuery-плагины ZFE и объекты, которые ZFE публикует в window.

declare global {
  /** Контекст поиска элементов: контейнер, внутри которого инициализируются компоненты */
  type ZfeContainer = Element | Document | JQuery;

  /** Элемент списка подсказок, который возвращает сервер: ключ, отображаемое значение и дополнительные поля */
  interface ZfeAutocompleteItem {
    key: string | number;
    value: string;
    [field: string]: any;
  }

  /** Контекст шаблонов typeahead, не относящихся к отдельной подсказке */
  interface ZfeTypeaheadContext {
    query: string;
    suggestions?: ZfeAutocompleteItem[];
  }

  type ZfeTypeaheadTemplate = string | ((context: ZfeTypeaheadContext) => string);

  /** Данные выбранного значения множественного автодополнения */
  interface ZfeMultiAutocompleteItemData {
    id?: string | number | null;
    title: string;
    [field: string]: any;
  }

  /** Шаблоны автодополнения (см. templates в typeahead.js) */
  interface ZfeAutocompleteTemplates {
    /** Отображение одной подсказки */
    suggestion?: (item: ZfeAutocompleteItem) => string;
    notFound?: ZfeTypeaheadTemplate;
    pending?: ZfeTypeaheadTemplate;
    header?: ZfeTypeaheadTemplate;
    footer?: ZfeTypeaheadTemplate;
    /** Только для множественного автодополнения: отображение выбранного значения */
    item?: (data: ZfeMultiAutocompleteItemData) => string;
  }

  /** Идентификаторы, которые нужно исключить из подсказок (или функция, возвращающая их) */
  type ZfeAutocompleteExclude =
    | ReadonlyArray<string | number>
    | (() => ReadonlyArray<string | number>)
    | null;

  /** Опции автодополнения одного значения; недостающие берутся из data-атрибутов поля */
  interface ZfeAutocompleteOptions {
    templates?: ZfeAutocompleteTemplates;
    limit?: number;
    exclude?: ZfeAutocompleteExclude;
    minLength?: number;
    sourceUrl?: string;
    canCreate?: boolean;
    /** Шаблон адреса карточки значения, например `/persons/edit/id/%d` */
    itemForm?: string;
  }

  /** Опции автодополнения нескольких значений; недостающие берутся из data-атрибутов поля */
  interface ZfeMultiAutocompleteOptions {
    templates?: ZfeAutocompleteTemplates;
    limit?: number;
    minLength?: number;
    sourceUrl?: string;
    canCreate?: boolean;
    /** Шаблон адреса карточки значения, например `/persons/edit/id/%d` */
    itemForm?: string;
    /** Адрес формы редактирования значения во всплывающем окне */
    editUrl?: string;
  }

  /** Значение автодополнения одного значения. Для нового (не найденного в справочнике) значения `id` равен null. */
  interface ZfeAutocompleteValue {
    id: string | null;
    title: string;
  }

  /** Значение, которое можно установить в автодополнение; все поля необязательны, лишние попадают в связанные поля формы */
  interface ZfeAutocompleteSetValue {
    id?: string | number | null;
    title?: string;
    [field: string]: unknown;
  }

  /** Выбранное значение множественного автодополнения: поля скрытых полей формы `name[n][поле]` */
  interface ZfeMultiAutocompleteValue {
    id?: string;
    title?: string;
    priority?: string;
    [field: string]: string | undefined;
  }

  /** Значение для добавления во множественное автодополнение */
  interface ZfeMultiAutocompleteInput {
    id?: string | number | null;
    title: string;
    /** Остальные поля сохраняются в data выбранного значения и попадают в шаблон `item` */
    [field: string]: unknown;
  }

  interface ZfeTypeaheadOptions {
    highlight?: boolean;
    hint?: boolean;
    minLength?: number;
    classNames?: Record<string, string>;
  }

  interface ZfeTypeaheadDataset {
    name?: string;
    source: (
      query: string,
      syncResults: (items: ZfeAutocompleteItem[]) => void,
      asyncResults: (items: ZfeAutocompleteItem[]) => void,
    ) => void;
    display?: string | ((item: ZfeAutocompleteItem) => string);
    limit?: number;
    templates?: ZfeAutocompleteTemplates;
  }

  interface ZfeModalOptions {
    title?: string;
    body?: string;
    /** Размер окна: lg, sm, max, fluid или произвольный CSS-класс */
    size?: 'lg' | 'sm' | 'max' | 'fluid' | (string & {});
  }

  /** Опции окна редактирования записи; `T` – тип данных, которые сервер возвращает после сохранения */
  interface ZfeEditModalOptions<T = unknown> {
    /** Адрес формы редактирования */
    url: string;
    /** Вызывается с ответом сервера после успешного сохранения */
    callback?: (data: T) => void;
    title?: string;
    /** Параметры запроса формы */
    data?: Record<string, unknown> | string;
    /** CSS-класс формы (по умолчанию form-horizontal) */
    formClass?: string;
    /** Вызывается после загрузки и инициализации формы */
    onload?: (form: JQuery) => void;
  }

  /** Поле формы в том виде, в котором его возвращает serializeArray() */
  interface ZfeFormField {
    name: string;
    value: string;
  }

  /** Условие совпадения контроллера или действия: значение, список значений, предикат или `*`/null (любое) */
  type ZfeRouteMatcher = string | readonly string[] | ((value: string) => boolean) | null;

  /** Подключаемый приложением визуальный редактор (например, обертка над CKEditor) */
  interface ZfeHtmlEditor {
    create(element: HTMLElement, config: object): unknown;
  }

  /** Метод инициализации компонентов внутри контейнера */
  type ZfeInitMethod = (container: ZfeContainer) => void;

  /**
   * Настройки приложения для ZFE.init().
   *
   * Помимо перечисленных, приложение может передать свои методы инициализации
   * и перечислить их имена в `initialMethods`.
   */
  interface ZfeAppConfig {
    /** Имена методов, которые вызываются для каждого контейнера (по умолчанию ZFE.initialMethods) */
    initialMethods?: string[];
    htmlEditor?: ZfeHtmlEditor;
    ckeditorConfig?: Record<string, unknown>;
    /** Наборы шаблонов автодополнения по имени (атрибут data-templateset) */
    autocompleteTemplates?: Record<string, ZfeAutocompleteTemplates>;
    [method: string]: unknown;
  }

  interface Window {
    bootstrap: typeof import('bootstrap');
    makeModal: (options: ZfeModalOptions) => JQuery;
    showEditModal: <T = unknown>(options: ZfeEditModalOptions<T>) => void;
  }

  interface JQuery {
    /** Отрисовать шаблон jquery-tmpl, взяв разметку из выбранного элемента */
    tmpl(data?: unknown, options?: object): JQuery;

    zfeAudio(): this;
    zfeDuplicates(): void;
    zfeMerge(): void;
    zfeMergeHelper(): this;
    zfeUploadAjax(options?: ZfeUploadAjaxOptions): this;
    tableStickyHeader(): void;

    /** Подключить typeahead к полю */
    typeahead(options: ZfeTypeaheadOptions | null, ...datasets: ZfeTypeaheadDataset[]): this;
    typeahead(method: 'val'): string;
    typeahead(method: 'val', value: string): this;
    typeahead(method: 'open' | 'close' | 'destroy'): this;

    /**
     * Следить за несохраненными изменениями формы.
     *
     * С командой плагин возвращает массив результатов по одному на каждую форму.
     */
    checkUnsavedFormData: {
      (): JQuery;
      (command: 'isFree'): boolean[];
      (command: 'setFree'): undefined[];
      /** Зафиксировать значение поля как «сохраненное»; null – забыть поле, undefined – взять текущее */
      (command: 'setFreeValue', name: string, value?: string | null): undefined[];
      (command: 'getFreeSnapshot'): ZfeFormField[][];
    };

    zfeAutocomplete: {
      (options?: ZfeAutocompleteOptions): JQuery;
      (method: 'clear'): void;
      (method: 'disable', disable: boolean): void;
      (method: 'getId'): string | null;
      (method: 'getTitle'): string;
      (method: 'getValue'): ZfeAutocompleteValue;
      (method: 'setValue', value?: ZfeAutocompleteSetValue): void;
      (method: 'getValueData'): unknown;
      (method: 'setValueData', value: unknown): void;
    };

    zfeMultiAutocomplete: {
      (options?: ZfeMultiAutocompleteOptions): JQuery;
      (method: 'clear'): void;
      (method: 'disable', disable: boolean): void;
      (method: 'addValue', id: string | number | null | undefined, title: string, data?: object): JQuery;
      (method: 'currentValue'): ZfeMultiAutocompleteValue[];
      (method: 'setValues', values: ZfeMultiAutocompleteInput[]): void;
    };
  }

  /** Опции AJAX-загрузчика файлов; недостающие берутся из атрибутов поля */
  interface ZfeUploadAjaxOptions {
    /** Адрес загрузки файла */
    url?: string;
    /** Тип превью: image, audio или null – обычный файл */
    type?: 'image' | 'audio' | null;
    multiple?: boolean;
    errorLoad?: string;
    errorExtensions?: string;
    errorMimeType?: string;
    /** Сообщение о превышении размера; `%s` заменяется на максимальный размер */
    errorSize?: string;
  }
}

export {};
