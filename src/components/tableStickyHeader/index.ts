import $ from 'jquery';

$.fn.tableStickyHeader = function tableStickyHeader(this: JQuery) {
  const $win = $(window);
  const offsetOf = ($el: JQuery) => $el.offset() ?? { top: 0, left: 0 };

  this.each((_, el) => {
    const $table = $(el);
    const $head = $('thead', $table);

    $head.clone().addClass('header-fixed d-none').appendTo($table);
    $head.addClass('header-original');
  });

  const setPositionValues = () => {
    this.each((i, el) => {
      // таблица с приклеивающимся заголовком
      const $table = $(el);
      // верхняя навигационная панель (navbar)
      const $navBar = $('.fixed-top');
      // величина прокрутки по вертикали
      const scrollTop = $win.scrollTop() ?? 0;
      // приклеивающийся заголовок (фиксированный)
      const $headFixed = $('.header-fixed', $table);
      // высота верхней навигационной панели
      const navBarHeight = $navBar.height() ?? 0;
      // высота заголовка
      const headHeight = $headFixed.height() ?? 0;
      // приклеивающийся заголовок (плавающий оригинал)
      const $headOriginal = $('.header-original', $table);
      // разница между нижней границей (navbar) и величиной прокрутки по вертикали
      let topOffset = offsetOf($navBar).top + navBarHeight - scrollTop;
      // нижняя граница фиксированного заголовка
      const headBottom =
        (topOffset < 0 ? 0 : navBarHeight) + headHeight + scrollTop;
      // учитываем в отступе фиксированного заголовка снятиес фиксирования navbar
      // на малых высотах
      topOffset = topOffset < 0 ? 0 : topOffset;
      // верхняя граница фиксированного заголовка
      const headTop = offsetOf($headOriginal).top - topOffset;
      // разница между нижней границей фиксированного заголовка
      // и верхней границей последней строчки таблицы
      const b = headBottom - offsetOf($('tbody tr:last', $table)).top;
      // итоговый отступ фиксированного заголовка сверху
      topOffset = b > 0 ? topOffset - b : topOffset;
      // итоговый отступ фиксированного заголовка слева
      const leftOffset = offsetOf($headOriginal).left - ($win.scrollLeft() ?? 0);

      $headFixed.css({
        top: topOffset,
        left: leftOffset,
        width: $headOriginal.width() ?? 0,
      });

      if (scrollTop >= headTop && ($(window).width() ?? 0) > 1024) {
        $headFixed.removeClass('d-none');
      } else {
        $headFixed.addClass('d-none');
      }
    });
  };

  const setWidthValues = () => {
    this.each((i, el) => {
      const $table = $(el);
      const $headFixed = $('.header-fixed td, .header-fixed th', $table);
      const $headOriginal = $(
        '.header-original td, .header-original th',
        $table
      );

      $headOriginal.each((tdi, td) => {
        $headFixed.eq(tdi).width($(td).width() ?? 0);
      });
    });
    setPositionValues();
  };

  setWidthValues();

  $win.on('resize', setWidthValues);
  $win.on('scroll', setPositionValues);
};
