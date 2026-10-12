import $ from 'jquery';

// `date-one-press` – историческая опечатка, которая используется в приложениях; верное написание тоже поддерживается
$(document).on('click', '[data-one-press], [date-one-press]', (event) => {
  const $this = $(event.currentTarget)
    .attr('disable', 'disable')
    .attr('disabled', 'disabled')
    .addClass('disabled');

  if ($this.is('[type=submit]')) {
    $this.closest('form').trigger('submit');
  }

  return true;
});
