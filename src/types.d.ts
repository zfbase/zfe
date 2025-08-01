interface ZfeAutocompleteValue {
  id: number | null;
  value: string;
}

interface JQuery {
  zfeAutocomplete: {
    (method: 'clear'): void;
    (method: 'getId'): number | null;
    (method: 'getTitle'): string;
    (method: 'getValue'): ZfeAutocompleteValue;
    (method: 'setValue', value: ZfeAutocompleteValue | null): void;
    (method: 'getValueData'): unknown;
    (method: 'setValueData', value: unknown): void;
  };

  zfeMultiAutocomplete: {
    (method: 'currentValue'): ZfeAutocompleteValue[];
    (method: 'setValues', values: ZfeAutocompleteValue[]): void;
  };
}
