import $ from 'jquery';

import { makeModal } from '../modals';

class MergeHelperModal {
  constructor($form, $panel, onCancel, onSuccess, onError) {
    this.$form = $form;
    this.$panel = $panel;
    this.onCancel = onCancel;
    this.onSuccess = onSuccess;
    this.onError = onError;

    this.mergeHelperUrl = $('.btn-merge', $panel).data('url');

    this.$modal = makeModal({
      title: 'Объединение записей',
      size: 'fluid',
    });

    const $modalBodyDefault = $('.modal-body', this.$modal);

    $form
      .insertBefore($modalBodyDefault)
      .addClass('modal-body');

    $modalBodyDefault.remove();
    $('.table', $form).css('margin-bottom', 0);

    window.ZFE.initMergeHelper(this.$modal);
    $('.form-group', this.$modal).hide();


    this.submitBtn = $('<a>', { class: 'btn btn-primary' })
      .append('Объединить')
      .on('click', this.onSubmit.bind(this));

    this.showEqualBtn = $('<a>', { class: 'btn btn-default pull-left' })
      .append($('<span>', { class: 'glyphicon glyphicon-chevron-down' }))
      .append(' Показать совпадающие поля')
      .on('click', this.showEqual.bind(this));

    this.hideEqualBtn = $('<a>', { class: 'btn btn-default pull-left hide' })
      .append($('<span>', { class: 'glyphicon glyphicon-chevron-up' }))
      .append(' Скрыть совпадающие поля')
      .on('click', this.hideEqual.bind(this));

    $('.modal-footer', this.$modal)
      .append(this.submitBtn)
      .append(this.showEqualBtn)
      .append(this.hideEqualBtn);

    $('[data-dismiss="modal"]', this.$modal)
      .on('click', () => { this.onCancel($panel); });

    this.$modal.appendTo(document.body);
    this.$modal.modal('show');
  }

  showEqual() {
    this.$form.removeClass('hide-equal-rows');
    this.showEqualBtn.addClass('hide');
    this.hideEqualBtn.removeClass('hide');
  }

  hideEqual() {
    this.$form.addClass('hide-equal-rows');
    this.showEqualBtn.removeClass('hide');
    this.hideEqualBtn.addClass('hide');
  }

  onSubmit() {
    this.$modal.modal('hide');

    $.ajax({
      method: 'post',
      url: this.mergeHelperUrl,
      data: this.$form.serializeArray(),
      success: (json) => {
        this.onSuccess(this.$panel, json.message);
      },
      error: () => {
        this.onError(this.$panel);
      },
    });
  }
}

$.fn.zfeDuplicates = function zfeDuplicates() {
  const makeAlert = (type, title) => $(`<div class="alert alert-${type} alert-dismissible fade in" role="alert">
      <button type="button" class="close" data-dismiss="alert" aria-label="Close">
        <span aria-hidden="true">&times;</span>
      </button>
    </div>`).append(title);

  const onCancel = ($panel) => {
    $panel
      .removeClass('panel-loading')
      .attr('disabled', false);
    $('.btn', $panel).show();
  };

  const onSuccess = ($panel, message) => {
    makeAlert('success', message || 'Объединение завершено успешно.')
      .insertAfter($panel);
    $panel.slideUp(400, () => {
      $panel.remove();
    });
  };

  const onError = ($panel, message) => {
    makeAlert('danger', message || 'Объединение не удалось.')
      .insertAfter($panel);
  };

  this.on('click', '.btn-merge', (event) => {
    const $btn = $(event.currentTarget);
    const $panel = $btn.closest('.panel');

    $panel
      .addClass('panel-loading')
      .attr('disabled', true);
    $('.btn', $panel).hide();

    const $checkboxes = $panel.find('tbody input[type="checkbox"]:checked');
    const ids = [];
    $checkboxes.each((i, checkbox) => {
      const $checkbox = $(checkbox);
      const id = $checkbox.closest('tr').data('item-id');
      ids.push(id);
    });

    $.ajax({
      url: $btn.data('url'),
      data: {
        ids,
      },
      success: (data) => {
        if (typeof data === 'string') {
          new MergeHelperModal(
            $(data).find('.zfe-merge-helper'),
            $panel,
            onCancel,
            onSuccess,
            onError,
          );
        }
      },
      error: () => {
        onError($panel);
      },
    });
  });

  this.on('click', '.btn-hide', (event) => {
    const $btn = $(event.currentTarget);
    const $panel = $btn.closest('.panel');

    $panel.slideUp();
  });

  /*
  $('.btn-more', this).on('click', (event) => {
    event.preventDefault();
    event.stopPropagation();

    return false;
  });
  */
};
