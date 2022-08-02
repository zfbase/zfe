import $ from 'jquery';

$(document).on('click', '[date-one-press]', (event) => {
  const $this = $(event.currentTarget)
    .attr('disable', 'disable')
    .attr('disabled', 'disabled')
    .addClass('disabled');

  if ($this.is('[type=submit]')) {
    $this.closest('form').submit();
  }

  return true;
});
