/* eslint no-multi-spaces: 0 */
/* eslint no-fallthrough: 0 */

import $ from 'jquery';

$.fn.zfeMerge = function zfeMerge() {
  const MergeEngine = {};
  MergeEngine.$input =         $('.zfe-merge-search', this);     // Поисковое поле
  MergeEngine.$post_clear =    $('.zfe-merge-post-clear', this); // Флаг: очищать после выбора результата поиска?
  MergeEngine.$searchResults = $('.zfe-merge-suggest', this);    // Поле для результатов поиска
  MergeEngine.$btnMerge =      $('.zfe-merge-exec', this);       // Кнопка запуска процесса объединения
  MergeEngine.$mergeItems =    $('.zfe-merge-items', this);      // Контейнер для выбранных для объединения записей
  MergeEngine.$mergeRowEmpty = $('.zfe-merge-empty', this);      // Строка для указания отсутствия выбранных записей для объединения
  MergeEngine.$mergeRowTmpl =  $('.zfe-merge-tmpl', this);       // Шаблон строки выбранной для объединения записи
  MergeEngine.$selectAll =     $('.zfe-merge-select-all', this); // Кнопка для выбора всех строк
  MergeEngine.searchUrl =      MergeEngine.$input.data('path');  // Адрес для поиска записей

  // Событие изменения значения поиковой строки или состояния фильтров
  MergeEngine.search = () => {
    const term = $.trim(MergeEngine.$input.val());

    if (term === '') {
      MergeEngine.$searchResults.empty();
      MergeEngine.$selectAll.addClass('hide');
      return;
    }

    MergeEngine.suggest(term, 1);
  };

  // Поиск
  MergeEngine.suggest = (term, page) => {
    MergeEngine.$searchResults.load(MergeEngine.searchUrl, {
      term,
      page,
      exclude: MergeEngine.getSelectedIds(),
    }, () => {
      window.ZFE.initItemDetailsPopover(MergeEngine.$searchResults);
      if (MergeEngine.$searchResults.find('tr.result').length > 1) {
        MergeEngine.$selectAll.removeClass('hide');
      } else {
        MergeEngine.$selectAll.addClass('hide');
      }
    });
  };

  // Получить список идентификаторов выбранных для объединения строк
  MergeEngine.getSelectedIds = () => {
    const ids = [];
    MergeEngine.$mergeItems.find('input[name="ids[]"]').each((i, el) => {
      const id = $(el).val();
      if (id) {
        ids.push(id);
      }
    });
    return ids;
  };

  // Событие добавления в выбранные для объединенные
  MergeEngine.onSelected = (event) => {
    MergeEngine.$mergeRowEmpty.hide();

    const $row = $(event.currentTarget);
    const item = $row.data();
    const lastEdited = $row.find('.last-edited');

    if (lastEdited.length) {
      item.lastEdited = lastEdited.html();
    }

    const $newRow = MergeEngine.$mergeRowTmpl.tmpl(item)
      .appendTo(MergeEngine.$mergeItems);

    $('td.item-details', $newRow)
      .html($row.find('td.item-details').html());
    window.ZFE.initItemDetailsPopover($newRow);

    $row.addClass('hide');

    if (MergeEngine.$post_clear.is(':checked')) {
      MergeEngine.$input.val('');
    }

    if (MergeEngine.$mergeItems.find('tr:not(.zfe-merge-empty)').length > 1) {
      MergeEngine.$btnMerge.attr('disabled', false);
    }

    const $resultRows = MergeEngine.$searchResults.find('tr.result:not(.hide)');
    switch ($resultRows.length) {
      case 0:
        MergeEngine.$searchResults.find('table').remove();
        MergeEngine.$searchResults.find('> p.empty').removeClass('hide');
      case 1:
        MergeEngine.$selectAll.addClass('hide');
        break;
      default:
    }
  };

  // Событие удаления из выбранных для объединенных
  MergeEngine.offSelected = (event) => {
    $(event.currentTarget).closest('tr').remove();

    const len = MergeEngine.$mergeItems.find('tr:not(.zfe-merge-empty)').length;
    if (len < 2) {
      MergeEngine.$btnMerge.attr('disabled', 'disabled');

      if (len < 1) {
        MergeEngine.$mergeRowEmpty.show();
      }
    }
  };

  // Добавить все найденные записи в объединяемые
  MergeEngine.selectAll = () => {
    MergeEngine.$searchResults.find('tr.result:not(.hide)').trigger('click');
  };

  // Перейти на другую страницу выдачи поиска
  MergeEngine.goToPage = (event) => {
    MergeEngine.suggest(
      MergeEngine.$input.val(),
      $(event.currentTarget).data('page-num'),
    );
  };

  // Выполнить объединение – событие
  MergeEngine.save = () => {
    const $rows = MergeEngine.$mergeItems.find('tr:not(.zfe-merge-empty)');

    if ($rows.length < 2) {
      return false;
    }

    return true;
  };


  MergeEngine.$input.on('input', MergeEngine.search);
  MergeEngine.$searchResults.on('click', 'tr.result', MergeEngine.onSelected);
  MergeEngine.$searchResults.on('click', '.pagination a', MergeEngine.goToPage);
  MergeEngine.$mergeItems.on('click', 'a', MergeEngine.offSelected);
  MergeEngine.$btnMerge.on('click', MergeEngine.save);
  MergeEngine.$selectAll.on('click', MergeEngine.selectAll);
};
