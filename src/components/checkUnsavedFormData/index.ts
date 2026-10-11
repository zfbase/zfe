import $ from 'jquery';

const comparer = (otherArray: ZfeFormField[]) => {
  return (current: ZfeFormField) => {
    return (
      otherArray.filter((other) => {
        return other.name === current.name && other.value === current.value;
      }).length === 0
    );
  };
};

class CheckUnsavedFormData {
  $form: JQuery;
  freeSnapshot: ZfeFormField[];

  constructor(form: JQuery) {
    this.$form = $(form);
    this.freeSnapshot = [];

    setTimeout(() => {
      this.setFree();
      this.initHandlers();
    }, 100);
  }

  initHandlers() {
    this.$form.on('submit', () => this.setFree());

    window.addEventListener('beforeunload', (e) => {
      if (!this.isFree()) {
        e.preventDefault();
      }
    });
  }

  isFree() {
    const snapshot = this.$form.serializeArray();
    return (
      this.freeSnapshot.filter(comparer(snapshot)).length === 0 &&
      snapshot.filter(comparer(this.freeSnapshot)).length === 0
    );
  }

  setFree() {
    this.freeSnapshot = this.$form.serializeArray();
  }

  setFreeValue(key: string, value?: string | null) {
    let index = -1;
    this.freeSnapshot.forEach((field, i) => {
      if (field.name === key) {
        index = i;
      }
    });

    if (value === null) {
      if (index !== -1) {
        this.freeSnapshot = this.freeSnapshot.filter((_, i) => index !== i);
      }
      return;
    }

    const newValue =
      typeof value === 'undefined'
        ? this.$form
            .serializeArray()
            .reduce<string | null>(
              (result, field) => (field.name === key ? field.value : result),
              null
            )
        : value;

    if (index !== -1) {
      this.freeSnapshot[index].value = newValue as string;
    } else {
      this.freeSnapshot.push({ name: key, value: newValue as string });
    }
  }

  getFreeSnapshot() {
    return this.freeSnapshot;
  }
}

type CheckUnsavedFormDataCommand = (...args: unknown[]) => unknown;

$.fn.checkUnsavedFormData = function checkUnsavedFormData(
  this: JQuery,
  command: string = '',
  ...args: unknown[]
): JQuery | unknown[] {
  const results: unknown[] = [];
  const $elements = this.each((i, el) => {
    const $this = $(this);

    let $element = $this.data('plugin_checkUnsavedFormData');
    if (!$element) {
      if (el.tagName === 'FORM') {
        $element = new CheckUnsavedFormData($this);
        $this.data('plugin_checkUnsavedFormData', $element);
      } else {
        window.console.warn(
          el,
          '- is incorrect tag for $.fn.checkUnsavedFormData'
        );
      }
    }

    if (command) {
      const instance = $element as unknown as Record<string, CheckUnsavedFormDataCommand>;
      results.push(instance[command](...args));
    }
  });

  return results.length ? results : $elements;
} as JQuery['checkUnsavedFormData'];
