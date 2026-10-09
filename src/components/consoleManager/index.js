import $ from 'jquery';

export default () => {
  const $btn = $('#exec-start');
  const $form = $('#console-manager').on('submit', () => {
    $btn.trigger('click');
    return false;
  });

  $('#exec-start').on('click', () => {
    const $log = $('<div>', { class: 'exec-log' }).insertAfter($form);

    const btnText = $btn.text();
    $btn.prop('disabled', true).text('Выполняется...');

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
        $btn.prop('disabled', false).text(btnText);
      },
    });
  });
};
