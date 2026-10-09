import $ from 'jquery';

import { makeModal } from '../modals';

class MergeHelperModal {
  $form: any;
  $panel: any;
  onCancel: any;
  onSuccess: any;
  onError: any;
  mergeHelperUrl: any;
  $modal: any;
  submitBtn: any;
  showEqualBtn: any;
  hideEqualBtn: any;

  constructor($form: JQuery, $panel: JQuery, onCancel: any, onSuccess: any, onError: any) {
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

    $form.insertBefore($modalBodyDefault).addClass('modal-body');

    $modalBodyDefault.remove();
    $('.table', $form).css('margin-bottom', 0);

    window.ZFE.initMergeHelper(this.$modal);
    $('.form-group', this.$modal).hide();

    this.submitBtn = $('<a>', { class: 'btn btn-primary' })
      .append('Объединить')
      .on('click', this.onSubmit.bind(this));

    this.showEqualBtn = $('<a>', { class: 'btn btn-default float-start' })
      .append($('<span>', { class: 'glyphicon glyphicon-chevron-down' }))
      .append(' Показать совпадающие поля')
      .on('click', this.showEqual.bind(this));

    this.hideEqualBtn = $('<a>', { class: 'btn btn-default float-start d-none' })
      .append($('<span>', { class: 'glyphicon glyphicon-chevron-up' }))
      .append(' Скрыть совпадающие поля')
      .on('click', this.hideEqual.bind(this));

    $('.modal-footer', this.$modal)
      .append(this.submitBtn)
      .append(this.showEqualBtn)
      .append(this.hideEqualBtn);

    $('[data-bs-dismiss="modal"]', this.$modal).on('click', () => {
      this.onCancel($panel);
    });

    this.$modal.appendTo(document.body);
    this.$modal.modal('show');
  }

  showEqual() {
    this.$form.removeClass('hide-equal-rows');
    this.showEqualBtn.addClass('d-none');
    this.hideEqualBtn.removeClass('d-none');
  }

  hideEqual() {
    this.$form.addClass('hide-equal-rows');
    this.showEqualBtn.removeClass('d-none');
    this.hideEqualBtn.addClass('d-none');
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
  const makeAlert = (type: string, title: string) =>
    $(`<div class="alert alert-${type} alert-dismissible fade show" role="alert">
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>`).append(title);

  const onCancel = ($panel: JQuery) => {
    $panel.removeClass('panel-loading').prop('disabled', false);
    $('.btn', $panel).show();
  };

  const onSuccess = ($panel: JQuery, message: string) => {
    makeAlert(
      'success',
      message || 'Объединение завершено успешно.'
    ).insertAfter($panel);
    $panel.slideUp(400, () => {
      $panel.remove();
    });
  };

  const onError = ($panel: JQuery, message?: string) => {
    makeAlert('danger', message || 'Объединение не удалось.').insertAfter(
      $panel
    );
  };

  this.on('click', '.btn-merge', (event) => {
    const $btn = $(event.currentTarget);
    const $panel = $btn.closest('.card');

    $panel.addClass('panel-loading').prop('disabled', true);
    $('.btn', $panel).hide();

    const $checkboxes = $panel.find('tbody input[type="checkbox"]:checked');
    const ids: string[] = [];
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
        // console.log(typeof data);
        // console.log(data);
        if (typeof data === 'string') {
          new MergeHelperModal(
            $(data).find('.zfe-merge-helper'),
            $panel,
            onCancel,
            onSuccess,
            onError
          );
        } else if (
          typeof data === 'object' &&
          typeof data.message === 'string'
        ) {
          if (data.status === '0') {
            onSuccess($panel, data.message);
          } else {
            onError($panel, data.message);
          }
        }
      },
      error: () => {
        onError($panel);
      },
    });
  });

  this.on('click', '.btn-hide', (event) => {
    const $btn = $(event.currentTarget);
    const $panel = $btn.closest('.card');

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
