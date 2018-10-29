import $ from 'jquery';

$.fn.tableStickyHeader = function tableStickyHeader() {
  const $tables = this;
  const $win = $(window);

  $tables.each((i, el) => {
    const $table = $(el);
    const $head = $('thead', $table);

    $head.clone().addClass('header-fixed hide').appendTo($table);
    $head.addClass('header-original');
  });

  function setPositionValues() {
    $tables.each((i, el) => {
      // таблица с приклеивающимся заголовком
      const $table = $(el);
      // верхняя навигационная панель (navbar)
      const $navBar = $('.navbar-fixed-top');
      // величина прокрутки по вертикали
      const scrollTop = $win.scrollTop();
      // приклеивающийся заголовок (фиксированный)
      const $headFixed = $('.header-fixed', $table);
      // высота верхней навигационной панели
      const navBarHeight = $navBar.height();
      // высота заголовка
      const headHeight = $headFixed.height();
      // приклеивающийся заголовок (плавающий оригинал)
      const $headOriginal = $('.header-original', $table);
      // разница между нижней границей (navbar) и величиной прокрутки по вертикали
      let topOffset = $navBar.offset().top + navBarHeight - scrollTop;
      // нижняя граница фиксированного заголовка
      const headBottom = (topOffset < 0 ? 0 : navBarHeight)
          + headHeight + scrollTop;
      // учитываем в отступе фиксированного заголовка снятиес фиксирования navbar
      // на малых высотах
      topOffset = topOffset < 0 ? 0 : topOffset;
      // верхняя граница фиксированного заголовка
      const headTop = $headOriginal.offset().top - topOffset;
      // разница между нижней границей фиксированного заголовка
      // и верхней границей последней строчки таблицы
      const b = headBottom - $('tbody tr:last', $table).offset().top;
      // итоговый отступ фиксированного заголовка сверху
      topOffset = b > 0 ? topOffset - b : topOffset;
      // итоговый отступ фиксированного заголовка слева
      const leftOffset = $headOriginal.offset().left - $win.scrollLeft();

      $headFixed.css({
        top: topOffset,
        left: leftOffset,
        width: $headOriginal.width(),
      });

      if (scrollTop >= headTop && $(window).width() > 1024) {
        $headFixed.removeClass('hide');
      } else {
        $headFixed.addClass('hide');
      }
    });
  }

  function setWidthValues() {
    $tables.each((i, el) => {
      const $table = $(el);
      const $headFixed = $('.header-fixed td, .header-fixed th', $table);
      const $headOriginal = $('.header-original td, .header-original th', $table);

      $headOriginal.each((tdi, td) => {
        $headFixed.eq(tdi).width($(td).width());
      });
    });
    setPositionValues();
  }

  setWidthValues();

  $win.on('resize', setWidthValues);
  $win.on('scroll', setPositionValues);
};
