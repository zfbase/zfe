import $ from 'jquery';

const comparer = (otherArray: any) => {
  return (current: any) => {
    return (
      otherArray.filter((other: any) => {
        return other.name === current.name && other.value === current.value;
      }).length === 0
    );
  };
};

class CheckUnsavedFormData {
  $form: any;
  freeSnapshot: any;

  constructor(form: any) {
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

  setFreeValue(key: any, value: any) {
    let index: number | null = null;
    this.freeSnapshot.forEach((field: { name: string }, i: number) => {
      if (field.name === key) {
        index = i;
      }
    });

    if (value === null) {
      if (index !== null) {
        this.freeSnapshot = this.freeSnapshot.filter((_: number, i: number) => index !== i);
      }
      return;
    }

    const newValue =
      typeof value === 'undefined'
        ? this.$form
            .serializeArray()
            .reduce(
              (result: any, field: { name: string; value: string }) => (field.name === key ? field.value : result),
              null
            )
        : value;

    if (index !== null) {
      this.freeSnapshot[index].value = newValue;
    } else {
      this.freeSnapshot.push({ name: key, value: newValue });
    }
  }

  getFreeSnapshot() {
    return this.freeSnapshot;
  }
}

$.fn.checkUnsavedFormData = function checkUnsavedFormData(
  this: JQuery,
  command = '',
  ...args
) {
  const results: any[] = [];
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
      results.push($element[command](...args));
    }
  });

  return results.length ? results : $elements;
};
