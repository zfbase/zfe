import $ from 'jquery';

const initHistoryDiff = (container: ZfeContainer) => {
  $('#diff select', container).change((event) => {
    $(event.currentTarget).closest('#diff').submit();
  });
};

export default initHistoryDiff;
