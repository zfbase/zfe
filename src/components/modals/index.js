import $ from 'jquery';

export const makeModal = ({ title, body, size = 'lg' }) => {
  const sizeClass = ['lg', 'sm', 'max'].includes(size) ? `modal-${size}` : size;
  const modal = $(
    '<div class="modal fade" tabindex="-1" role="dialog">'
      + `<div class="modal-dialog ${sizeClass}" role="document">`
      + '<div class="modal-content">'
        + '<div class="modal-header">'
        + '<button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span></button>'
        + '<h4 class="modal-title"></h4>'
        + '</div>'
        + '<div class="modal-body clearfix"></div>'
        + '<div class="modal-footer">'
        + '<button type="button" class="btn btn-default" data-dismiss="modal">Отмена</button>'
        + '</div>'
      + '</div>'
      + '</div>'
    + '</div>',
  );
  modal.find('.modal-body').html(body);
  modal.find('.modal-title').text(title);
  return modal;
};

window.makeModal = makeModal;

let modalCallback = null;
let modalUrl = null;

const editModal = makeModal({ body: '<form></form>' });
editModal.appendTo(document.body);

const setModalHtml = (html) => {
  const form = editModal.find('form');
  form.html(html);
  window.ZFE.initContainer(form);
  return form;
};

editModal.on('submit', 'form', (e) => {
  e.preventDefault();
  const body = $(e.currentTarget).serialize();
  $.post(modalUrl, body)
    .done((data) => {
      if (typeof data === 'string') {
        setModalHtml(data);
      } else {
        if (typeof modalCallback === 'function') {
          modalCallback(data);
        }
        editModal.modal('hide');
      }
    });
});

const submitButton = $('<button type="button" class="btn btn-primary">Сохранить</button>');
submitButton.appendTo(editModal.find('.modal-footer'));

submitButton.on('click', () => editModal.find('form').trigger('submit'));

export const showEditModal = ({
  url,
  callback,
  title,
  data,
  formClass,
  onload,
}) => {
  modalUrl = url;
  modalCallback = callback;

  editModal.find('.modal-title')
    .text(title || 'Редактирование');
  editModal.find('form')
    .attr('class', formClass || 'form-horizontal')
    .empty()
    .append($('<span>', {
      class: 'h1 glyphicon glyphicon-refresh spin',
      style: 'margin: auto;',
    }));

  onload = onload || (form => form);
  $.get(url, data)
    .done(res => onload(setModalHtml(res)));
  editModal.modal('show');
};

window.showEditModal = showEditModal;
