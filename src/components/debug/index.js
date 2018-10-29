import $ from 'jquery';

const initDebug = (container) => {
  if ($('#DevelToolbar', container).length > 0) {
    $('.zfe-dump').each((i, node) => {
      const $node = $(node);
      let html = $node.html();

      html = html.replace(/[[{]\n/g, (s) => {
        let str = s;
        str = str.replace(/^\[\n/, '[ <span class="glyphicon glyphicon-plus-sign"></span>&nbsp;<span class="hide">\n');
        str = str.replace(/^\{\n/, '{ <span class="glyphicon glyphicon-plus-sign"></span>&nbsp;<span class="hide">\n');
        return str;
      });

      html = html.replace(/\n\s*[}]]/g, (s) => {
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
  }
};

export default initDebug;
