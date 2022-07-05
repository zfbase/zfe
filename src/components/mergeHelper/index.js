import $ from 'jquery';

class ZFEMergeHelper {
  constructor(container) {
    this.$container = $(container);
    this.$slaveIds = this.$container.find('.slaves-ids');

    $('.btn-show-equal', this.$container).on('click', this.showEqual.bind(this));
    $('.btn-hide-equal', this.$container).on('click', this.hideEqual.bind(this));

    this.$container.on('click', 'tbody td', (event, mode) => {
      const $td = $(event.currentTarget);
      $td.find('input').prop('checked', true);
      const $tr = $td.closest('tr');
      $tr.removeClass('equal-rows');
      $tr.find('td').removeClass('bg-success user-select');
      $td.addClass('bg-success');
      if (mode !== 'auto') {
        $td.addClass('user-select');
      }
    });

    this.autoSelect();
    this.markEqual();
    this.hideEqual();

    this.$container.on('click', '.btn-remove', (event) => {
      const $btn = $(event.currentTarget);
      const $cell = $btn.closest('td');
      const index = $cell.closest('tr').children().index($cell) + 1;
      const itemId = Number($btn.data('id'));
      this.$container.find(`tr > *:nth-child(${index})`).remove();

      const slaveIds = this.$slaveIds.val()
        .split(',')
        .map(Number)
        .filter(id => (id !== itemId))
        .join(',');
      this.$slaveIds.val(slaveIds);

      this.autoSelect();
      this.markEqual();
      this.hideEqual();
    });
  }

  autoSelect() {
    this.$container.find('tbody tr:not(:has(td.user-select))').each((_, tr) => {
      $(tr).find('td:not(.null-value)').first().trigger('click', 'auto');
    });
  }

  markEqual() {
    this.$container.find('tbody tr').each((_, tr) => {
      const $tr = $(tr);
      if ($tr.find('td.user-select').length) {
        return;
      }

      const values = [];
      const $cells = $tr.find('td');
      $cells.each((_, td) => {
        const text = $.trim($(td).text());
        if (text !== '') {
          values.push(text);
        }
      });
      const unique = values.filter((value, iValue, self) => self.indexOf(value) === iValue);
      // Если все пустые или все заполнены одним и тем же – .equal-rows
      if ([0, $cells.length].includes(values.length) && unique.length < 2) {
        $tr.addClass('equal-rows');
      }
    });
  }

  showEqual() {
    this.$container.removeClass('hide-equal-rows');
  }

  hideEqual() {
    this.$container.addClass('hide-equal-rows');
  }
}

$.fn.zfeMergeHelper = function zfeMergeHelper() {
  return this.each((_, el) => {
    if (!$.data(el, 'plugin_zfeMergeHelper')) {
      $.data(el, 'plugin_zfeMergeHelper', new ZFEMergeHelper(el));
    }
  });
};
