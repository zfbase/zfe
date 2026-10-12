import $ from 'jquery';

$(document).on('keyup', (e) => {
  if (!e.altKey && !e.ctrlKey) {
    return;
  }

  if (['INPUT', 'TEXTAREA'].includes((e.target as unknown as Element).tagName)) {
    return;
  }

  // Костяль для динамического отключения.
  if ($('body').hasClass('zfe-keyboard-disable')) {
    return;
  }

  if (e.key === 'ArrowUp') {
    const url = $('.btn-history-up').attr('href');

    if (url) {
      window.location.href = url;
      return;
    }
  }

  const paginator = $('.pagination');
  if (paginator.length === 1 || paginator.length === 2) {
    const $active = $('li.active', paginator);
    let $cursor;

    if (e.key === 'ArrowLeft') {
      $cursor = $active.prev();
    } else if (e.key === 'ArrowRight') {
      $cursor = $active.next();
    }

    if ($cursor && $cursor.length) {
      const url = $cursor.find('a').attr('href');
      if (url) {
        window.location.href = url;
      }
    }
    return;
  }
  // Если больше одного пагинатора – не путаем пользователей.
  if (paginator.length > 1) {
    return;
  }

  const btnSteps = $('.btn-steps');
  if (btnSteps.length === 1) {
    let url;

    if (e.key === 'ArrowLeft') {
      url = $('.btn-steps-prev', btnSteps).attr('href');
    } else if (e.key === 'ArrowRight') {
      url = $('.btn-steps-next', btnSteps).attr('href');
    }

    if (url) {
      window.location.href = url;
    }
  }
});
