import $ from 'jquery';
import humanFileSize from '../humanFileSize';

// Типы формы автозагрузки
const TYPE_DEFAULT = null;
const TYPE_IMAGES = 'image';
const TYPE_AUDIOS = 'audio';

const defaults = {
  url: '/files/upload',
  type: TYPE_DEFAULT,
  multiple: false,
  errorLoad: 'Не удалось загрузить файл.',
  errorExtensions: 'Не допустимое разрешение файла.',
  errorMimeType: 'Не допустимый тип файла.',
  errorSize: 'Загрузка невозможна: размер файла превышает максимальные %s.',
};

class Loader {
  constructor(file, settings, $loadingContainer, $previewContainer, $formContainer) {
    this.file = file;
    this.settings = settings;
    this.$loadingContainer = $loadingContainer;
    this.$previewContainer = $previewContainer;
    this.$formContainer = $formContainer;
    this.$loadingWrap = null;
    this.xhr = null;

    this.init();

    if (this.checkFile()) {
      this.initProgressBar();
      this.loadFile();
    }
  }

  init() {
    this.$loadingWrap = $('<div>')
      .appendTo(this.$loadingContainer);

    $('<div>', { class: 'title' })
      .append(this.file.name)
      .appendTo(this.$loadingWrap);

    $('<button>', {
      type: 'button',
      class: 'close',
    })
      .append($('<span>', { 'aria-hidden': 'true' }).append('&times;'))
      .prependTo(this.$loadingWrap)
      .on('click', () => {
        this.xhr && this.xhr.abort();
        this.$loadingWrap.remove();
      });
  }

  warning(message) {
    const $closeBtn = $('<button>', {
      type: 'button',
      class: 'close',
      'data-dismiss': 'alert',
      'aria-label': 'Закрыть',
    });

    $('<span>', { 'aria-hidden': 'true' })
      .append('&times;')
      .appendTo($closeBtn);

    $('<div>', {
      class: 'alert alert-warning',
      role: 'alert',
    })
      .text(message)
      .prepend($closeBtn)
      .appendTo(this.$loadingWrap)
      .alert()
      .on('closed.bs.alert', () => {
        this.$loadingWrap.remove();
      });
  }

  checkFile() {
    if (this.settings.extensions && this.settings.extensions.length > 0) {
      const extension = this.file.name.split('.').pop().toLowerCase();
      if ($.inArray(`.${extension}`, this.settings.extensions) === -1) {
        this.warning(this.settings.errorExtensions);
        this.restoreForm();
        return false;
      }
    }

    if (this.settings.mimeTypes && this.settings.mimeTypes.length > 0) {
      const mimeType = this.file.type;
      if ($.inArray(mimeType, this.settings.mimeTypes) === -1) {
        this.warning(this.settings.errorMimeType);
        this.restoreForm();
        return false;
      }
    }

    const maxFileSize = $('#MAX_FILE_SIZE').val();
    if (maxFileSize && this.file.size > maxFileSize) {
      this.warning(this.settings.errorSize.replace('%s', humanFileSize(maxFileSize)));
      this.restoreForm();
      return false;
    }

    return true;
  }

  loadFile() {
    const data = new FormData();
    data.append('file', this.file);
    $.ajax({
      url: this.settings.url,
      type: 'POST',
      data,
      cache: false,
      dataType: 'json',
      processData: false,
      contentType: false,
      xhr: () => {
        const xhr = $.ajaxSettings.xhr();
        this.xhr = xhr;
        if (xhr.upload) {
          xhr.upload.addEventListener('progress', (event) => {
            let percent = 0;
            const position = event.loaded || event.position;
            if (event.lengthComputable) {
              percent = Math.ceil(position / event.total * 100);
            }
            this.setProgress(percent);
          }, true);
        }
        return xhr;
      },
      success: (response) => {
        if (response.status === '0') {
          if (this.settings.previewCallback) {
            this.settings.previewCallback.apply(response.data);
          } else {
            switch (this.settings.type) {
              case TYPE_IMAGES:
                this.previewImage(response.data);
                break;
              case TYPE_AUDIOS:
                this.previewAudio(response.data);
                break;
              default:
                this.preview(response.data);
                break;
            }
          }

          this.$loadingWrap.fadeOut('fast', () => {
            this.$loadingWrap.remove();
          });
        } else {
          this.warning(response.message || this.settings.errorLoad);
          this.restoreForm();
        }
      },
      error: () => {
        this.warning(this.settings.errorLoad);
        this.restoreForm();
      },
      always: () => {
        this.$progressBarContainer.slideUp('fast', () => {
          this.$progressBarContainer.remove();
        });
      },
    });
  }

  previewImage(file) {
    const $preview = $('<p>', { class: 'help-block preview-image image-uploaded' });

    let $title = null;
    if (file.previewUrl) {
      $title = $('<a>', {
        class: 'image',
        style: `background-image: url(${file.previewUrl});`,
      });
    } else {
      $title = $('<span>').append(file.title);
    }

    if (file.downloadUrl) {
      $('<a>', { href: file.downloadUrl })
        .append($title)
        .appendTo($preview);
    } else {
      $preview.append($title);
    }

    if (file.deleteUrl) {
      $preview
        .append(' &nbsp; ');
      $('<a>', {
        href: file.deleteUrl,
        class: 'text-danger',
      })
        .append($('<span>', { class: 'glyphicon glyphicon-remove' }))
        .appendTo($preview);
    }

    const $input = $('<input>', {
      type: 'hidden',
      name: this.settings.name,
      value: file.id,
    });

    return $preview
      .append($input)
      .appendTo(this.$previewContainer);
  }

  previewAudio(file) {
    const $audio = $('<audio>', {
      class: 'zfe-audio',
      src: file.previewUrl,
      controls: true,
    });

    if (file.downloadUrl) {
      $('<a>', {
        class: 'zfe-audio-link',
        href: file.downloadUrl,
        target: '_blank',
      })
        .append(file.downloadLabel || 'Скачать аудиофайл')
        .appendTo($audio);
    }

    if (file.viewUrl) {
      $('<a>', {
        class: 'zfe-audio-link',
        href: file.viewUrl,
        target: '_blank',
      })
        .append(file.viewLabel || 'Открыть метаданные')
        .appendTo($audio);
    }

    if (file.editUrl) {
      $('<a>', {
        class: 'zfe-audio-link',
        href: file.editUrl,
        target: '_blank',
      })
        .append(file.editLabel || 'Редактировать метаданные')
        .appendTo($audio);
    }

    // Ссылки может не быть, но кнопка необходима
    // ZFEAudio само разберется как удалять
    $('<a>', {
      class: 'zfe-audio-link zfe-audio-delete',
      href: file.deleteUrl,
    })
      .on('click', this.restoreForm.bind(this))
      .append(file.deleteLabel || 'Удалить аудиофайл')
      .appendTo($audio);

    const $input = $('<input>', {
      type: 'hidden',
      name: this.settings.name,
      value: file.id,
    });

    return $audio
      .append($input)
      .appendTo(this.$previewContainer)
      .zfeAudio();
  }

  preview(file) {
    const $preview = $('<p>', { class: 'help-block' });

    const $title = $('<span>').append(file.title);

    if (file.iconClass) {
      $('<span>', { class: file.iconClass })
        .prepentTo($title);
    }

    if (file.downloadUrl) {
      $('<a>', { href: file.downloadUrl })
        .append($title)
        .appendTo($preview);
    } else {
      $preview.append($title);
    }

    if (file.deleteUrl) {
      $preview.append(' &nbsp; ');
      $('<a>', {
        href: file.deleteUrl,
        class: 'text-danger',
      })
        .append($('<span>', { class: 'glyphicon glyphicon-remove' }))
        .appendTo($preview);
    }

    const $input = $('<input>', {
      type: 'hidden',
      name: this.settings.name,
      value: file.id,
    });

    return $preview
      .append($input)
      .appendTo(this.$previewContainer);
  }

  initProgressBar() {
    this.$progressBarContainer = $('<div>', { class: 'progress' })
      .appendTo(this.$loadingWrap);
    this.$progressBar = $('<div>', {
      class: 'progress-bar progress-bar-info progress-bar-striped active',
      role: 'progressbar',
      'aria-valuenow': 0,
      'aria-valuemin': 0,
      'aria-valuemax': 100,
      style: 'width: 0%;',
    })
      .appendTo(this.$progressBarContainer);
  }

  setProgress(percent) {
    this.$progressBar
      .css('width', `${percent}%`)
      .attr('aria-valuenow', percent)
      .text(`${percent}%`);
    if (percent < 100) {
      this.$progressBar.addClass('active');
    } else {
      this.$progressBar.removeClass('active');
    }
  }

  restoreForm() {
    if (this.settings.multiple === false) {
      this.$formContainer.fadeIn();
      $(`[data-new-upload="${this.settings.name}-new-upload"]`)
        .hide();
    }
  }
}

class ZFEUploadAjax {
  constructor(element, options) {
    this.$input = $(element);
    this.settings = $.extend({}, defaults, this.dataAttrOptions(), options);
    this.init();
  }

  dataAttrOptions() {
    const extensions = [];
    const mimeTypes = [];
    const acceptStr = this.$input.attr('accept') || '';
    $.each(acceptStr.split(','), (i, acceptRaw) => {
      const accept = $.trim(acceptRaw);
      if (accept.substr(0, 1) === '.') {
        extensions.push(accept);
      } else if (accept.indexOf('/') !== -1) {
        mimeTypes.push(accept);
      }
    });

    return {
      name: this.$input.attr('name'),
      url: this.$input.data('ajax-url'),
      type: this.$input.data('type'),
      multiple: this.$input.attr('multiple'),
      previewCallback: this.$input.data('preview-callback'),
      extensions,
      mimeTypes,
    };
  }

  init() {
    this.$formContainer = $('<div>', { class: 'zfe-upload-ajax-form' });
    this.$loadingContainer = $('<div>', { class: 'zfe-upload-ajax-loader' });
    this.$previewContainer = $('<div>', { class: 'zfe-upload-ajax-preview' });

    $('<div>', { class: 'zfe-upload-ajax' })
      .insertBefore(this.$input)
      .prepend(this.$formContainer)
      .prepend(this.$loadingContainer)
      .prepend(this.$previewContainer);

    this.$input.appendTo(this.$formContainer);
    this.$input.on('change', this.onSelectFile.bind(this));
  }

  onSelectFile() {
    const fileList = this.$input.get(0).files;

    if (this.settings.multiple === false && fileList.length === 1) {
      this.$formContainer.fadeOut();
    }

    for (let i = 0; i < fileList.length; i += 1) {
      new Loader(
        fileList[i],
        this.settings,
        this.$loadingContainer,
        this.$previewContainer,
        this.$formContainer,
      );
    }

    this.$input.val('');
    this.$input.blur();
  }
}

$.fn.zfeUploadAjax = function zfeAudio(options) {
  return this.each((i, el) => {
    if (!$.data(el, 'plugin_zfeUploadAjax')) {
      $.data(el, 'plugin_zfeUploadAjax', new ZFEUploadAjax(el, options));
    }
  });
};
