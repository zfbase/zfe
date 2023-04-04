import $ from 'jquery';
import { keyCode } from '../../js/constants';

$(document).on('keyup', (e) => {
  if (!e.altKey && !e.ctrlKey) {
    return;
  }

  if (['INPUT', 'TEXTAREA'].includes(e.target.tagName)) {
    return;
  }

  // Костяль для динамического отключения.
  if ($('body').hasClass('zfe-keyboard-disable')) {
    return;
  }

  if (e.keyCode === keyCode.UP) {
    const url = $('.btn-history-up').attr('href');

    if (url) {
      window.location = url;
      return;
    }
  }

  const paginator = $('.pagination');
  if (paginator.length === 1 || paginator.length === 2) {
    const $active = $('li.active', paginator);
    let $cursor;

    if (e.keyCode === keyCode.LEFT) {
      $cursor = $active.prev();
    } else if (e.keyCode === keyCode.RIGHT) {
      $cursor = $active.next();
    }

    if ($cursor && $cursor.length) {
      const url = $cursor.find('a').attr('href');
      if (url) {
        window.location = url;
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

    if (e.keyCode === keyCode.LEFT) {
      url = $('.btn-steps-prev', btnSteps).attr('href');
    } else if (e.keyCode === keyCode.RIGHT) {
      url = $('.btn-steps-next', btnSteps).attr('href');
    }

    if (url) {
      window.location = url;
    }
  }
});
