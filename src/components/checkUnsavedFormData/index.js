import $ from 'jquery';

const comparer = (otherArray) => {
  return (current) => {
    return otherArray.filter((other) => {
      return other.name === current.name && other.value === current.value;
    }).length === 0;
  };
};

class CheckUnsavedFormData {
  constructor(form) {
    this.$form = $(form);
    this.freeSnapshot = [];

    this.setFree();
    this.initHandlers();
  }

  initHandlers() {
    const component = this;
    component.$form.on('submit', () => component.setFree());

    window.addEventListener('beforeunload', (e) => {
      if (!component.isFree()) {
        e.preventDefault();
        e.returnValue = true;
      }
    });
  }

  isFree() {
    const snapshot = this.$form.serializeArray();
    return (this.freeSnapshot.filter(comparer(snapshot)).length === 0)
      && (snapshot.filter(comparer(this.freeSnapshot)).length === 0);
  }

  setFree() {
    this.freeSnapshot = this.$form.serializeArray();
  }

  setFreeValue(key, value) {
    let index = null;
    this.freeSnapshot.forEach((field, i) => {
      if (field.name === key) {
        index = i;
      }
    });

    if (value === null) {
      if (index !== null) {
        this.freeSnapshot = this.freeSnapshot.filter((_, i) => index !== i);
      }
      return;
    }

    const newValue = (typeof value === 'undefined')
      ? this.$form.serializeArray().reduce((result, field) => (field.name === key ? field.value : result), null)
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

$.fn.checkUnsavedFormData = function checkUnsavedFormData(command = '', ...args) {
  const results = [];
  const $elements = this.each((i, el) => {
    const $this = $(this);

    let $element = $this.data('plugin_checkUnsavedFormData');
    if (!$element) {
      if (el.tagName === 'FORM') {
        $element = new CheckUnsavedFormData($this);
        $this.data('plugin_checkUnsavedFormData', $element);
      } else {
        window.console.warn(el, '- is incorrect tag for $.fn.checkUnsavedFormData');
      }
    }

    if (command) {
      results.push($element[command](...args));
    }
  });

  return results.length ? results : $elements;
};
