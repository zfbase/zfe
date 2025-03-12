interface ZfeAutocompleteValue {
  id: number | null;
  value: string;
}

interface JQuery {
  zfeMultiAutocomplete: {
    (method: 'currentValue'): ZfeAutocompleteValue[];
    (method: 'setValues', values: ZfeAutocompleteValue[]): void;
  };
}
