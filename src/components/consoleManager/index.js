import $ from 'jquery';

export default () => {
  const $btn = $('#exec-start');
  const $form = $('#console-manager').on('submit', () => {
    $btn.trigger('click');
    return false;
  });

  $('#exec-start').on('click', () => {
    const $log = $('<div>', { class: 'exec-log' }).insertAfter($form);

    $btn.data('loading-text', 'Выполняется...');
    $btn.button('loading');

    $.ajax({
      url: '/console-manager/console',
      method: 'POST',
      data: {
        command: $('#exec-command').val(),
        params: $('#exec-params').val(),
      },
      dataType: 'html',
      success: (log) => {
        $log.append(log);
      },
      complete: () => {
        $btn.button('reset');
      },
    });
  });
};
