import $ from 'jquery';

class ZFEMergeHelper {
  constructor(container) {
    this.$container = $(container);
    this.$slaveIds = this.$container.find('.slaves-ids');

    $('.btn-show-equal', this.$container).on('click', this.showEqual.bind(this));
    $('.btn-hide-equal', this.$container).on('click', this.hideEqual.bind(this));

    this.$container.on('click', 'tbody td', (event) => {
      const $td = $(event.currentTarget);
      $td.find('input').prop('checked', true);
      $td.closest('tr').find('td').removeClass('bg-success');
      $td.addClass('bg-success');
    });

    this.$container.find('tbody tr:has(td:not(.null-value))').each((index, tr) => {
      $(tr).find('td:not(.null-value)').first().trigger('click');
    });

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
    });
  }

  markEqual() {
    this.$container.find('tbody tr').each((iTr, tr) => {
      const $tr = $(tr);
      const values = [];
      $tr.find('td').each((iTd, td) => {
        const text = $.trim($(td).text());
        if (text !== '') {
          values.push(text);
        }
      });
      const unique = values.filter((value, iValue, self) => self.indexOf(value) === iValue);
      if (unique.length < 2) {
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
  return this.each((i, el) => {
    if (!$.data(el, 'plugin_zfeMergeHelper')) {
      $.data(el, 'plugin_zfeMergeHelper', new ZFEMergeHelper(el));
    }
  });
};
