import $ from 'jquery';

const initHistoryDiff = (container: ZfeContainer) => {
  $('#diff select', container).on('change', (event) => {
    $(event.currentTarget).closest('#diff').trigger('submit');
  });
};

export default initHistoryDiff;
