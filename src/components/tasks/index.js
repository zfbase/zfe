import $ from 'jquery';

export default () => {
  (() => {
    const $form = $('.indexSearch');
    const $idFilter = $('#id', $form);
    const $otherFilters = $('#search, #performer, #related_id', $form);
    $idFilter.on('change', (event) => {
      const value = $(event.currentTarget).val();
      $otherFilters.attr('disabled', !!value);
    });
    $otherFilters.attr('disabled', !!$idFilter.val());
  })();

  $('.btn-restart').on('click', (e) => {
    const $btn = $(e.currentTarget);
    $btn.attr('disabled', true);
  
    $.ajax({
      type: 'POST',
      url: '/tasks/restart',
      data: { id: $btn.data('id') },
      success: ({ status, message, data }) => {
        if (status === '0') {
          $('<a>')
            .attr('href', `/tasks/index/search/all/id/${data.id}`)
            .addClass('label label-info')
            .text(data.id)
            .insertAfter($btn);
          $btn.remove();
        } else {
          alert(message);
        }
      },
      error: (xhr) => {
        alert(xhr.responseJSON && xhr.responseJSON.message);
      },
    });
  
    return true;
  });
  
  $('.btn-clear-schedule').on('click', (event) => {
    event.preventDefault();
  
    const $btn = $(event.currentTarget);
    $btn.attr('disabled', true);
  
    $.ajax({
      type: 'POST',
      url: '/tasks/clear-schedule',
      data: { id: $btn.data('id') },
      success: ({ status, message }) => {
        if (status === '0') {
          $btn.closest('tr').find('.schedule').remove();
          $btn.remove();
        } else {
          alert(message);
        }
      },
      error: (xhr) => {
        alert(xhr.responseJSON && xhr.responseJSON.message);
      },
    });
  });  
}
