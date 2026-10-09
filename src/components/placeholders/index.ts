import $ from 'jquery';

export default (container) => {
  const $container = $(container);
  $.each(['date', 'datetime', 'datetime-local'], (i, type) => {
    $container.find(`input[type="${type}"][placeholder]`).each((j, el) => {
      const $el = $(el);
      $el.addClass(type);
      if (!$el.val()) {
        $el.attr('type', 'text');
      }
    });
    $container.on('focus', `input.${type}`, e => $(e.currentTarget).attr('type', type));
    $container.on('blur', `input.${type}`, (e) => {
      const $el = $(e.currentTarget);
      if (!$el.val()) {
        $el.attr('type', 'text');
      }
    });
  });
};
