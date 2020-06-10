import $ from 'jquery';

export default (container) => {
  $(container).find('.form-edit').each((i, el) => {
    const form = $(el);
    let snapshot = form.serialize();
    form.on('submit', () => {
      snapshot = form.serialize();
    });
    form.find('a').on('click', (e) => {
      const href = e.currentTarget.getAttribute('href');
      if (href.indexOf('/delete/' !== -1)) {
        snapshot = form.serialize();
      }
    });
    window.addEventListener('beforeunload', (e) => {
      if (form.serialize() !== snapshot) {
        e.preventDefault();
        e.returnValue = true;
      }
    });
  });
};
