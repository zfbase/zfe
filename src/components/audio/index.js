import $ from 'jquery';

const { alert } = window;

const twodigit = num => `0${num}`.slice(-2);

const secToTime = (sec) => {
  if (Number.isNaN(sec)) {
    return '?';
  }

  const hours = Math.floor(sec / 3600);
  const minutes = Math.floor(sec / 60) - (hours * 60);
  const seconds = Math.floor(sec) - (hours * 3600) - (minutes * 60);

  let timeStr = `${twodigit(minutes)}:${twodigit(seconds)}`;
  if (hours > 0) {
    timeStr = `${hours}:${timeStr}`;
  }

  return timeStr;
};


class ZFEAudio {
  constructor(_audio) {
    this._audio = _audio;
    this.audio = $(_audio);
    this.container = null;
    this.playPauseBtn = null;
    this.track = null;
    this.time = null;
    this.duration = null;
    this.muteBtn = null;
    this.allowUpdateTime = true;

    this._buildPlayer();
    this._loadAudio();
  }

  play() {
    this._audio.play();
    this.playPauseBtn.addClass('active');
  }

  pause() {
    this._audio.pause();
    this.playPauseBtn.removeClass('active');
  }

  togglePlayPause() {
    if (this.playPauseBtn.hasClass('active')) {
      this.pause();
    } else {
      this.play();
    }
  }

  toggleMute() {
    if (this.muteBtn.hasClass('active')) {
      this._audio.muted = false;
      this.muteBtn.removeClass('active');
    } else {
      this._audio.muted = true;
      this.muteBtn.addClass('active');
    }
  }

  _buildPlayer() {
    this.container = $('<div>', { class: 'zfe-audio' })
      .insertBefore(this.audio)
      .append(this.audio);

    const btnGroup = $('<div>', { class: 'btn-group btn-group-sm' })
      .appendTo(this.container);

    this.playPauseBtn = $('<button>', {
      class: 'btn btn-default zfe-audio-play-pause-btn',
      type: 'button',
    })
      .append($('<span>', { class: 'glyphicon glyphicon-play' }))
      .append($('<span>', { class: 'glyphicon glyphicon-pause' }))
      .appendTo(btnGroup)
      .on('click', this.togglePlayPause.bind(this));

    this.track = $('<input>', {
      type: 'range',
      min: 0,
      max: 0,
      value: 0,
      class: 'zfe-audio-track',
    })
      .on('mousedown', () => { this.allowUpdateTime = false; })
      .on('change', this._onChangeTrack.bind(this));

    $('<div>', { class: 'btn btn-default btn-static hidden-xs zfe-audio-track-container' })
      .append(this.track)
      .appendTo(btnGroup);

    this.time = $('<span>00:00</span>', { class: 'zfe-audio-time' });

    this.duration = $('<span>', { class: 'zfe-audio-duration' })
      .append($('<span>', { class: 'glyphicon glyphicon-refresh spin' }));

    $('<div>', { class: 'btn btn-default btn-static' })
      .append(this.time)
      .append($('<span> / <span>'))
      .append(this.duration)
      .appendTo(btnGroup);

    this.muteBtn = $('<button>', {
      class: 'btn btn-default zfe-audio-mute-btn',
      type: 'button',
    })
      .append($('<span>', { class: 'glyphicon glyphicon-volume-up' }))
      .append($('<span>', { class: 'glyphicon glyphicon-volume-off' }))
      .appendTo(btnGroup)
      .on('click', this.toggleMute.bind(this));

    const moreContainer = $('<ul>', { class: 'dropdown-menu dropdown-menu-right' });

    this.audio.find('a.zfe-audio-link').each((i, link) => {
      $('<li>').append($(link)).appendTo(moreContainer);
    });

    if (moreContainer.children().length) {
      const dropdownBtn = $('<div>', {
        class: 'btn btn-default dropdown-toggle',
        role: 'button',
      })
        .attr('data-toggle', 'dropdown')
        .attr('aria-haspopup', 'true')
        .attr('aria-expanded', 'false')
        .append($('<span>', { class: 'glyphicon glyphicon-option-vertical' }))
        .dropdown();

      moreContainer.on('click', 'a.zfe-audio-delete', (event) => {
        const url = $(event.target).attr('href');
        if (url) {
          $.getJSON(url, (data) => {
            alert(data.message);
            this.container.fadeOut('fast', () => {
              this.container.remove();
            });
          });
        } else {
          this.container.fadeOut('fast', () => {
            this.container.remove();
          });
        }

        event.preventDefault();
      });

      $('<div>', { class: 'btn-group btn-group-sm dropup' })
        .append(dropdownBtn)
        .append(moreContainer)
        .appendTo(btnGroup);
    }
  }

  _loadAudio() {
    let timeout = 0;
    const check = setInterval(() => {
      if (!this._audio.paused) {
        this.pause();
        clearInterval(check);
        return true;
      }

      if (Number.isNaN(this._audio.duration) === false) {
        this._setDuration(this._audio.duration);

        this.audio.on('loadedmetadata', this._onUpdateTime.bind(this));
        this.audio.on('loadeddata', this._onUpdateTime.bind(this));
        this.audio.on('progress', this._onUpdateTime.bind(this));
        this.audio.on('canplay', this._onUpdateTime.bind(this));
        this.audio.on('canplaythrough', this._onUpdateTime.bind(this));
        this.audio.on('timeupdate', this._onUpdateTime.bind(this));
        this.audio.on('ended', this._onResetTime.bind(this));

        clearInterval(check);
        return true;
      }

      if (this._audio.networkState === 3 || timeout === 100) {
        // 3 = NETWORK_NO_SOURCE - no audio/video source found
        this._error('Не удалось загрузить аудиофайл');
        clearInterval(check);
        return false;
      }

      timeout += 1;
      return null;
    }, 100);

    this.audio.on('error', () => {
      this._error('Ошибка при воспроизведении');
    });
  }

  _onUpdateTime() {
    this._updateTime();
  }

  _onResetTime() {
    this._resetTime();
  }

  _onChangeTrack() {
    this._audio.currentTime = this.track.val();
    this._updateTime(true);

    setTimeout(() => {
      this.allowUpdateTime = true;
    }, 10);
  }

  _setDuration(sec) {
    this.duration.text(secToTime(sec));
    this.track
      .attr('max', sec)
      .attr('step', sec / 1000);
  }

  _setTime(sec) {
    this.time.text(secToTime(sec));
  }

  _updateTime(force = false) {
    if (force || this.allowUpdateTime) {
      this.track.val(this._audio.currentTime);
      this._setTime(this._audio.currentTime);
    }
  }

  _resetTime(force = false) {
    if (force || this.allowUpdateTime) {
      this._audio.currentTime = 0;
      this._updateTime();
      this.playPauseBtn.removeClass('active');
    }
  }

  _error(message) {
    if (message) {
      console.error(message);
    }

    this.playPauseBtn.addClass('disabled');
    this.track.attr('disabled', true);
    this.muteBtn.addClass('disabled');
  }
}


$.fn.zfeAudio = function zfeAudio() {
  return this.each((i, el) => {
    if (!$.data(el, 'plugin_zfeAudio')) {
      $.data(el, 'plugin_zfeAudio', new ZFEAudio(el));
    }
  });
};
