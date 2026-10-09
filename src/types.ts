// Глобальные типы: jQuery-плагины ZFE и объекты, которые ZFE публикует в window.

declare global {
  interface ZfeAutocompleteValue {
    id: number | null;
    title: string;
  }

  interface ZfeAutocompleteOptions {
    templates?: any;
    limit?: number;
    exclude?: any;
    [key: string]: any;
  }

  interface ZfeModalOptions {
    title?: string;
    body?: string;
    size?: string;
  }

  interface ZfeEditModalOptions {
    url: string;
    callback: (data: any) => void;
    title?: string;
    data?: any;
    formClass?: string;
    onload?: (form: JQuery) => void;
  }

  interface Window {
    bootstrap: typeof import('bootstrap');
    makeModal: (options: ZfeModalOptions) => JQuery;
    showEditModal: (options: ZfeEditModalOptions) => void;
  }

  interface JQuery {
    zfeAudio(): void;
    zfeDuplicates(): void;
    zfeMerge(): void;
    zfeMergeHelper(): void;
    zfeUploadAjax(options?: object): this;
    tableStickyHeader(): void;
    typeahead(...args: any[]): any;
    checkUnsavedFormData(...args: any[]): any;

    zfeAutocomplete: {
      (options?: ZfeAutocompleteOptions): JQuery;
      (method: 'clear'): void;
      (method: 'getId'): number | null;
      (method: 'getTitle'): string;
      (method: 'getValue'): ZfeAutocompleteValue | null;
      (method: 'setValue', value: ZfeAutocompleteValue | null): void;
      (method: 'getValueData'): unknown;
      (method: 'setValueData', value: unknown): void;
    };

    zfeMultiAutocomplete: {
      (options?: ZfeAutocompleteOptions): JQuery;
      (method: 'currentValue'): ZfeAutocompleteValue[];
      (method: 'setValues', values: ZfeAutocompleteValue[]): void;
    };
  }
}

export {};
