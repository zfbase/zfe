interface ZfeAutocompleteValue {
  id: number | null;
  title: string;
}

interface JQuery {
  zfeAutocomplete: {
    (method: 'clear'): void;
    (method: 'getId'): number | null;
    (method: 'getTitle'): string;
    (method: 'getValue'): ZfeAutocompleteValue | null;
    (method: 'setValue', value: ZfeAutocompleteValue | null): void;
    (method: 'getValueData'): unknown;
    (method: 'setValueData', value: unknown): void;
  };

  zfeMultiAutocomplete: {
    (method: 'currentValue'): ZfeAutocompleteValue[];
    (method: 'setValues', values: ZfeAutocompleteValue[]): void;
  };
}
