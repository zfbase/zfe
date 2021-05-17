import $ from 'jquery';

const initDebug = () => {
  $('.zfe-dump').each((i, node) => {
    const $node = $(node);
    let html = $node.html();

    html = html.replace(/[[{]\n/g, (s) => {
      let str = s;
      str = str.replace(/^\[\n/, '[ <span class="glyphicon glyphicon-plus-sign"></span> <span class="hide">\n');
      str = str.replace(/^\{\n/, '{ <span class="glyphicon glyphicon-plus-sign"></span> <span class="hide">\n');
      return str;
    });

    html = html.replace(/\n\s*[}\]]/g, (s) => {
      let str = s;
      str = str.replace(/\]$/, '</span>]');
      str = str.replace(/\}$/, '</span>}');
      return str;
    });

    $node.html(html);
  });

  $('html').on('click', 'pre.zfe-dump .glyphicon', (e) => {
    const $this = $(e.currentTarget);
    const $block = $this.next();

    if ($block.hasClass('hide')) {
      $block.removeClass('hide');
      $this.removeClass('glyphicon-plus-sign')
        .addClass('glyphicon-minus-sign');
    } else {
      $block.addClass('hide');
      $this.removeClass('glyphicon-minus-sign')
        .addClass('glyphicon-plus-sign');
    }
  });

  $(() => {
    $('#DevelConfigViewer_Tree ul').each((i, list) => {
      const $list = $(list).addClass('hide');
      const $btn = $('<i>', {
        'data-toggle': 'collapse',
        class: 'glyphicon glyphicon-chevron-up',
        role: 'button',
      }).insertBefore($list);

      $btn.on('click', () => {
        if ($btn.hasClass('glyphicon-chevron-up')) {
          $btn.removeClass('glyphicon-chevron-up');
          $btn.addClass('glyphicon-chevron-down');
          $list.removeClass('hide');
        } else {
          $btn.removeClass('glyphicon-chevron-down');
          $btn.addClass('glyphicon-chevron-up');
          $list.addClass('hide');
        }
      });
    });
  });
};

export default initDebug;
