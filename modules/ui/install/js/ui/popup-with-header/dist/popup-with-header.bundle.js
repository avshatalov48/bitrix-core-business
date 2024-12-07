/* eslint-disable */
this.BX = this.BX || {};
(function (exports,main_core_events,main_loader,ui_progressround,ui_popupcomponentsmaker,ui_iconSet_api_core,main_popup,ui_popupWithHeader,main_core,ui_buttons,ui_infoHelper) {
	'use strict';

	let _ = t => t,
	  _t,
	  _t2,
	  _t3,
	  _t4,
	  _t5;
	var _pausePlayerWidth = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("pausePlayerWidth");
	var _scale = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("scale");
	var _videos = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("videos");
	var _loop = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("loop");
	var _autoplay = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("autoplay");
	var _muted = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("muted");
	var _content = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("content");
	var _videoNode = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("videoNode");
	var _playerNode = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("playerNode");
	var _progressBar = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("progressBar");
	var _barPadding = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("barPadding");
	var _posterUrl = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("posterUrl");
	var _loader = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("loader");
	var _currentPlayState = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("currentPlayState");
	var _playButton = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("playButton");
	var _stopButton = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("stopButton");
	var _wrapper = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("wrapper");
	var _hasAutoPlayed = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("hasAutoPlayed");
	var _analyticsCallback = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("analyticsCallback");
	var _onInitVideoMetadata = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("onInitVideoMetadata");
	var _onTick = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("onTick");
	var _onClickPlayer = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("onClickPlayer");
	var _onClickStopButton = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("onClickStopButton");
	var _onVideoEnded = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("onVideoEnded");
	var _scaleTo = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("scaleTo");
	var _onPause = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("onPause");
	var _onPlay = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("onPlay");
	class RoundPlayer {
	  constructor(options) {
	    var _options$width;
	    Object.defineProperty(this, _onPlay, {
	      value: _onPlay2
	    });
	    Object.defineProperty(this, _onPause, {
	      value: _onPause2
	    });
	    Object.defineProperty(this, _scaleTo, {
	      value: _scaleTo2
	    });
	    Object.defineProperty(this, _onVideoEnded, {
	      value: _onVideoEnded2
	    });
	    Object.defineProperty(this, _onClickStopButton, {
	      value: _onClickStopButton2
	    });
	    Object.defineProperty(this, _onClickPlayer, {
	      value: _onClickPlayer2
	    });
	    Object.defineProperty(this, _onTick, {
	      value: _onTick2
	    });
	    Object.defineProperty(this, _onInitVideoMetadata, {
	      value: _onInitVideoMetadata2
	    });
	    Object.defineProperty(this, _pausePlayerWidth, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _scale, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _videos, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _loop, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _autoplay, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _muted, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _content, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _videoNode, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _playerNode, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _progressBar, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _barPadding, {
	      writable: true,
	      value: 3
	    });
	    Object.defineProperty(this, _posterUrl, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _loader, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _currentPlayState, {
	      writable: true,
	      value: RoundPlayer.PLAY_STATE_BACKGROUND
	    });
	    Object.defineProperty(this, _playButton, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _stopButton, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _wrapper, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _hasAutoPlayed, {
	      writable: true,
	      value: false
	    });
	    Object.defineProperty(this, _analyticsCallback, {
	      writable: true,
	      value: void 0
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _wrapper)[_wrapper] = options.wrapper;
	    babelHelpers.classPrivateFieldLooseBase(this, _pausePlayerWidth)[_pausePlayerWidth] = (_options$width = options.width) != null ? _options$width : 86;
	    babelHelpers.classPrivateFieldLooseBase(this, _scale)[_scale] = main_core.Type.isNumber(options.scale) ? options.scale : 1;
	    babelHelpers.classPrivateFieldLooseBase(this, _videos)[_videos] = main_core.Type.isArrayFilled(options.videos) ? options.videos : [];
	    babelHelpers.classPrivateFieldLooseBase(this, _loop)[_loop] = main_core.Type.isBoolean(options.loop) ? options.loop : true;
	    babelHelpers.classPrivateFieldLooseBase(this, _autoplay)[_autoplay] = main_core.Type.isBoolean(options.autoplay) ? options.autoplay : true;
	    babelHelpers.classPrivateFieldLooseBase(this, _muted)[_muted] = main_core.Type.isBoolean(options.muted) ? options.muted : true;
	    babelHelpers.classPrivateFieldLooseBase(this, _posterUrl)[_posterUrl] = options.posterUrl;
	    babelHelpers.classPrivateFieldLooseBase(this, _analyticsCallback)[_analyticsCallback] = main_core.Type.isFunction(options.analyticsCallback) ? options.analyticsCallback : null;
	    babelHelpers.classPrivateFieldLooseBase(this, _playerNode)[_playerNode] = main_core.Tag.render(_t || (_t = _`<div class="ui-popupcomponentmaker__round-player"></div>`));
	    babelHelpers.classPrivateFieldLooseBase(this, _playButton)[_playButton] = main_core.Tag.render(_t2 || (_t2 = _`<div class="ui-popupcomponentmaker__round-player-btn"></div>`));
	    babelHelpers.classPrivateFieldLooseBase(this, _stopButton)[_stopButton] = main_core.Tag.render(_t3 || (_t3 = _`<div class="ui-popupcomponentmaker__round-player-btn --stop-btn"></div>`));
	    let poster = '';
	    if (main_core.Type.isStringFilled(babelHelpers.classPrivateFieldLooseBase(this, _posterUrl)[_posterUrl])) {
	      babelHelpers.classPrivateFieldLooseBase(this, _playerNode)[_playerNode].style.backgroundImage = 'url("' + main_core.Text.encode(babelHelpers.classPrivateFieldLooseBase(this, _posterUrl)[_posterUrl]) + '")';
	      poster = 'poster="' + main_core.Text.encode(babelHelpers.classPrivateFieldLooseBase(this, _posterUrl)[_posterUrl]) + '"';
	    }
	    const autoplay = babelHelpers.classPrivateFieldLooseBase(this, _autoplay)[_autoplay] ? 'autoplay' : '';
	    const muted = babelHelpers.classPrivateFieldLooseBase(this, _muted)[_muted] ? 'muted' : '';
	    babelHelpers.classPrivateFieldLooseBase(this, _videoNode)[_videoNode] = main_core.Tag.render(_t4 || (_t4 = _`<video ${0} ${0} ${0}></video>`), poster, autoplay, muted);
	    babelHelpers.classPrivateFieldLooseBase(this, _videoNode)[_videoNode].muted = babelHelpers.classPrivateFieldLooseBase(this, _muted)[_muted];
	    babelHelpers.classPrivateFieldLooseBase(this, _videoNode)[_videoNode].autoplay = babelHelpers.classPrivateFieldLooseBase(this, _autoplay)[_autoplay];
	    // this.#videoNode.loop = this.#loop;

	    babelHelpers.classPrivateFieldLooseBase(this, _loader)[_loader] = new main_loader.Loader({
	      size: 40
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _playerNode)[_playerNode].style.width = babelHelpers.classPrivateFieldLooseBase(this, _pausePlayerWidth)[_pausePlayerWidth] + 'px';
	    babelHelpers.classPrivateFieldLooseBase(this, _videoNode)[_videoNode].addEventListener('timeupdate', babelHelpers.classPrivateFieldLooseBase(this, _onTick)[_onTick].bind(this));
	    babelHelpers.classPrivateFieldLooseBase(this, _videoNode)[_videoNode].addEventListener('loadedmetadata', babelHelpers.classPrivateFieldLooseBase(this, _onInitVideoMetadata)[_onInitVideoMetadata].bind(this));
	    babelHelpers.classPrivateFieldLooseBase(this, _playerNode)[_playerNode].addEventListener('click', babelHelpers.classPrivateFieldLooseBase(this, _onClickPlayer)[_onClickPlayer].bind(this));
	    babelHelpers.classPrivateFieldLooseBase(this, _videoNode)[_videoNode].addEventListener('ended', babelHelpers.classPrivateFieldLooseBase(this, _onVideoEnded)[_onVideoEnded].bind(this));
	    babelHelpers.classPrivateFieldLooseBase(this, _videoNode)[_videoNode].addEventListener('play', babelHelpers.classPrivateFieldLooseBase(this, _onPlay)[_onPlay].bind(this));
	    babelHelpers.classPrivateFieldLooseBase(this, _videoNode)[_videoNode].addEventListener('pause', babelHelpers.classPrivateFieldLooseBase(this, _onPause)[_onPause].bind(this));
	    babelHelpers.classPrivateFieldLooseBase(this, _playButton)[_playButton].addEventListener('click', babelHelpers.classPrivateFieldLooseBase(this, _onClickPlayer)[_onClickPlayer].bind(this));
	    babelHelpers.classPrivateFieldLooseBase(this, _stopButton)[_stopButton].addEventListener('click', babelHelpers.classPrivateFieldLooseBase(this, _onClickStopButton)[_onClickStopButton].bind(this));
	    babelHelpers.classPrivateFieldLooseBase(this, _videoNode)[_videoNode].addEventListener('canplay', () => {
	      babelHelpers.classPrivateFieldLooseBase(this, _loader)[_loader].hide();
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _videoNode)[_videoNode].addEventListener('waiting', () => {
	      babelHelpers.classPrivateFieldLooseBase(this, _loader)[_loader].show(babelHelpers.classPrivateFieldLooseBase(this, _playerNode)[_playerNode]);
	    });
	  }
	  render() {
	    if (babelHelpers.classPrivateFieldLooseBase(this, _content)[_content]) {
	      return babelHelpers.classPrivateFieldLooseBase(this, _content)[_content];
	    }
	    babelHelpers.classPrivateFieldLooseBase(this, _videos)[_videos].forEach(video => {
	      main_core.Dom.append(main_core.Tag.render(_t5 || (_t5 = _`<source src="${0}" type="${0}">`), video.url, video.type), babelHelpers.classPrivateFieldLooseBase(this, _videoNode)[_videoNode]);
	    });
	    main_core.Dom.append(babelHelpers.classPrivateFieldLooseBase(this, _videoNode)[_videoNode], babelHelpers.classPrivateFieldLooseBase(this, _playerNode)[_playerNode]);
	    main_core.Dom.append(babelHelpers.classPrivateFieldLooseBase(this, _playButton)[_playButton], babelHelpers.classPrivateFieldLooseBase(this, _wrapper)[_wrapper]);
	    main_core.Dom.append(babelHelpers.classPrivateFieldLooseBase(this, _stopButton)[_stopButton], babelHelpers.classPrivateFieldLooseBase(this, _wrapper)[_wrapper]);
	    main_core.Dom.append(babelHelpers.classPrivateFieldLooseBase(this, _playerNode)[_playerNode], babelHelpers.classPrivateFieldLooseBase(this, _wrapper)[_wrapper]);
	    babelHelpers.classPrivateFieldLooseBase(this, _content)[_content] = babelHelpers.classPrivateFieldLooseBase(this, _wrapper)[_wrapper];
	    return babelHelpers.classPrivateFieldLooseBase(this, _content)[_content];
	  }
	  renderTo(wrapper) {
	    main_core.Dom.append(wrapper, this.render());
	    return wrapper;
	  }
	  play() {
	    babelHelpers.classPrivateFieldLooseBase(this, _videoNode)[_videoNode].play();
	    main_core.Dom.removeClass(this.render(), '--stop');
	  }
	  setMute(mute) {
	    babelHelpers.classPrivateFieldLooseBase(this, _videoNode)[_videoNode].muted = mute;
	  }
	  getPlayState() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _currentPlayState)[_currentPlayState];
	  }
	  pause() {
	    babelHelpers.classPrivateFieldLooseBase(this, _videoNode)[_videoNode].pause();
	  }
	  stop() {
	    this.pause();
	    babelHelpers.classPrivateFieldLooseBase(this, _currentPlayState)[_currentPlayState] = RoundPlayer.PLAY_STATE_BACKGROUND;
	    babelHelpers.classPrivateFieldLooseBase(this, _videoNode)[_videoNode].currentTime = 0;
	    main_core.Dom.addClass(this.render(), '--stop');
	  }
	  userPlay() {
	    this.stop();
	    babelHelpers.classPrivateFieldLooseBase(this, _currentPlayState)[_currentPlayState] = RoundPlayer.PLAY_STATE_USER;
	    babelHelpers.classPrivateFieldLooseBase(this, _progressBar)[_progressBar].setValue(0);
	    main_core.Dom.remove(babelHelpers.classPrivateFieldLooseBase(this, _progressBar)[_progressBar].getContainer());
	    babelHelpers.classPrivateFieldLooseBase(this, _progressBar)[_progressBar].renderTo(babelHelpers.classPrivateFieldLooseBase(this, _playerNode)[_playerNode]);
	    this.setMute(false);
	    this.play();
	  }
	}
	function _onInitVideoMetadata2(event) {
	  babelHelpers.classPrivateFieldLooseBase(this, _progressBar)[_progressBar] = new ui_progressround.ProgressRound({
	    width: babelHelpers.classPrivateFieldLooseBase(this, _pausePlayerWidth)[_pausePlayerWidth] - 2 * babelHelpers.classPrivateFieldLooseBase(this, _barPadding)[_barPadding],
	    lineSize: 2,
	    maxValue: babelHelpers.classPrivateFieldLooseBase(this, _videoNode)[_videoNode].duration,
	    value: babelHelpers.classPrivateFieldLooseBase(this, _videoNode)[_videoNode].currentTime,
	    colorBar: '#fff',
	    colorTrack: 'rgba(0, 0, 0, 0)'
	  });
	  if (babelHelpers.classPrivateFieldLooseBase(this, _autoplay)[_autoplay]) {
	    this.play();
	  }
	}
	function _onTick2() {
	  babelHelpers.classPrivateFieldLooseBase(this, _progressBar)[_progressBar].update(babelHelpers.classPrivateFieldLooseBase(this, _videoNode)[_videoNode].currentTime);
	}
	function _onClickPlayer2() {
	  if (babelHelpers.classPrivateFieldLooseBase(this, _currentPlayState)[_currentPlayState] === RoundPlayer.PLAY_STATE_BACKGROUND) {
	    this.userPlay();
	    main_core.Dom.removeClass(this.render(), '--stop');
	  } else {
	    babelHelpers.classPrivateFieldLooseBase(this, _videoNode)[_videoNode].paused ? this.play() : this.pause();
	  }
	  if (babelHelpers.classPrivateFieldLooseBase(this, _analyticsCallback)[_analyticsCallback]) {
	    babelHelpers.classPrivateFieldLooseBase(this, _analyticsCallback)[_analyticsCallback]('click-player');
	  }
	}
	function _onClickStopButton2() {
	  if (babelHelpers.classPrivateFieldLooseBase(this, _analyticsCallback)[_analyticsCallback]) {
	    babelHelpers.classPrivateFieldLooseBase(this, _analyticsCallback)[_analyticsCallback]('click-player');
	  }
	  this.stop();
	}
	function _onVideoEnded2() {
	  if (babelHelpers.classPrivateFieldLooseBase(this, _analyticsCallback)[_analyticsCallback] && (!babelHelpers.classPrivateFieldLooseBase(this, _videoNode)[_videoNode].muted || !babelHelpers.classPrivateFieldLooseBase(this, _hasAutoPlayed)[_hasAutoPlayed])) {
	    babelHelpers.classPrivateFieldLooseBase(this, _analyticsCallback)[_analyticsCallback]('video_finished', `isMuted_${babelHelpers.classPrivateFieldLooseBase(this, _videoNode)[_videoNode].muted ? 'Y' : 'N'}`);
	  }
	  if (!babelHelpers.classPrivateFieldLooseBase(this, _hasAutoPlayed)[_hasAutoPlayed]) {
	    babelHelpers.classPrivateFieldLooseBase(this, _hasAutoPlayed)[_hasAutoPlayed] = babelHelpers.classPrivateFieldLooseBase(this, _videoNode)[_videoNode].muted;
	  }
	  this.stop();
	  main_core.Dom.remove(babelHelpers.classPrivateFieldLooseBase(this, _progressBar)[_progressBar].getContainer());
	  this.setMute(true);
	  if (babelHelpers.classPrivateFieldLooseBase(this, _loop)[_loop]) {
	    this.play();
	  }
	}
	function _scaleTo2(x) {
	  babelHelpers.classPrivateFieldLooseBase(this, _playerNode)[_playerNode].style.transform = `scale(${x})`;
	}
	function _onPause2() {
	  babelHelpers.classPrivateFieldLooseBase(this, _scaleTo)[_scaleTo](1);
	  if (babelHelpers.classPrivateFieldLooseBase(this, _analyticsCallback)[_analyticsCallback] && (!babelHelpers.classPrivateFieldLooseBase(this, _videoNode)[_videoNode].muted || !babelHelpers.classPrivateFieldLooseBase(this, _hasAutoPlayed)[_hasAutoPlayed])) {
	    babelHelpers.classPrivateFieldLooseBase(this, _analyticsCallback)[_analyticsCallback]('on-pause');
	  }
	}
	function _onPlay2() {
	  babelHelpers.classPrivateFieldLooseBase(this, _scaleTo)[_scaleTo](babelHelpers.classPrivateFieldLooseBase(this, _scale)[_scale]);
	  if (babelHelpers.classPrivateFieldLooseBase(this, _analyticsCallback)[_analyticsCallback] && (!babelHelpers.classPrivateFieldLooseBase(this, _videoNode)[_videoNode].muted || !babelHelpers.classPrivateFieldLooseBase(this, _hasAutoPlayed)[_hasAutoPlayed])) {
	    babelHelpers.classPrivateFieldLooseBase(this, _analyticsCallback)[_analyticsCallback]('on-play');
	  }
	}
	RoundPlayer.PLAY_STATE_BACKGROUND = 'background';
	RoundPlayer.PLAY_STATE_USER = 'user';

	let _$1 = t => t,
	  _t$1,
	  _t2$1,
	  _t3$1,
	  _t4$1,
	  _t5$1,
	  _t6,
	  _t7,
	  _t8,
	  _t9,
	  _t10,
	  _t11,
	  _t12,
	  _t13,
	  _t14,
	  _t15;
	var _options = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("options");
	var _content$1 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("content");
	var _player = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("player");
	class HeaderBuilder {
	  constructor(options) {
	    Object.defineProperty(this, _options, {
	      writable: true,
	      value: null
	    });
	    Object.defineProperty(this, _content$1, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _player, {
	      writable: true,
	      value: void 0
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _options)[_options] = options;
	  }
	  buildPlayer(playerOptions) {
	    return new RoundPlayer({
	      wrapper: playerOptions.wrapper,
	      pausePlayerWidth: playerOptions.width,
	      scale: playerOptions.scale,
	      posterUrl: playerOptions.posterUrl,
	      videos: playerOptions.videos,
	      loop: playerOptions.loop,
	      autoplay: playerOptions.autoplay,
	      muted: playerOptions.muted,
	      analyticsCallback: babelHelpers.classPrivateFieldLooseBase(this, _options)[_options].analyticsCallback
	    });
	  }
	  renderPlayer(playerOptions) {
	    if (babelHelpers.classPrivateFieldLooseBase(this, _player)[_player]) {
	      return babelHelpers.classPrivateFieldLooseBase(this, _player)[_player];
	    }
	    const wrapper = main_core.Tag.render(_t$1 || (_t$1 = _$1`<div class="ui-popupcomponentsmaker__round-player-box"/>`));
	    babelHelpers.classPrivateFieldLooseBase(this, _player)[_player] = this.buildPlayer({
	      ...playerOptions,
	      wrapper: wrapper
	    });
	    if (babelHelpers.classPrivateFieldLooseBase(this, _player)[_player]) {
	      return babelHelpers.classPrivateFieldLooseBase(this, _player)[_player].render();
	    }
	    return main_core.Tag.render(_t2$1 || (_t2$1 = _$1``));
	  }
	  getPlayer() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _player)[_player];
	  }
	  renderTitle(titleOptions) {
	    const title = main_core.Tag.render(_t3$1 || (_t3$1 = _$1`
			<div class="ui-popupcomponentsmaker-header-tariff__header-content">
				<div class="ui-popupcomponentsmaker-header-tariff__title">${0}</div>
			</div>
		`), titleOptions.title);
	    if (!main_core.Type.isNil(titleOptions.subtitle)) {
	      main_core.Dom.append(main_core.Tag.render(_t4$1 || (_t4$1 = _$1`<div class="ui-popupcomponentsmaker-header-tariff__subtitle">${0}</div>`), titleOptions.subtitle), title);
	    }
	    return title;
	  }
	  renderDescription(descriptionOptions) {
	    const descriptionText = main_core.Tag.render(_t5$1 || (_t5$1 = _$1`
		<div class="ui-popupcomponentsmaker-header-tariff__box">
			<div class="ui-popupcomponentsmaker-header-tariff__title">${0}</div>
		</div>`), descriptionOptions.title);
	    if (!main_core.Type.isNil(descriptionOptions.subtitle)) {
	      main_core.Dom.append(main_core.Tag.render(_t6 || (_t6 = _$1`<div class="ui-popupcomponentsmaker-header-tariff__subtitle">${0}</div>`), descriptionOptions.subtitle), descriptionText);
	    }
	    if (!main_core.Type.isNil(descriptionOptions.subtitleDescription)) {
	      main_core.Dom.append(main_core.Tag.render(_t7 || (_t7 = _$1`<div class="ui-popupcomponentsmaker-header-tariff__text">${0}</div>`), descriptionOptions.subtitleDescription), descriptionText);
	    }
	    if (!main_core.Type.isNil(descriptionOptions.code)) {
	      const onclick = e => {
	        e.stopPropagation();
	        ui_infoHelper.FeaturePromotersRegistry.getPromoter({
	          code: descriptionOptions.code
	        }).show();
	      };
	      main_core.Dom.append(main_core.Tag.render(_t8 || (_t8 = _$1`<a onclick="${0}" target="_blank" class="ui-popupcomponentsmaker-header-tariff__more">${0}<div class="ui-icon-set --chevron-right ui-popupcomponentsmaker-header-tariff__more-icon"></div></a>`), onclick, descriptionOptions.moreLabel), descriptionText);
	    }
	    let roundContent = '';
	    if (main_core.Type.isPlainObject(descriptionOptions.roundContent)) {
	      roundContent = this.renderPlayer(descriptionOptions.roundContent);
	    } else if (main_core.Type.isStringFilled(descriptionOptions.roundContent)) {
	      roundContent = this.renderIcon(descriptionOptions.roundContent);
	    } else if (main_core.Type.isDomNode(descriptionOptions.roundContent)) {
	      roundContent = this.embedIcon(descriptionOptions.roundContent);
	    }
	    const descriptionBlock = main_core.Tag.render(_t9 || (_t9 = _$1`
			<div class="ui-popupcomponentsmaker-header-tariff__message-wrapper">
				${0}
				${0}
			</div>
		`), roundContent, descriptionText);
	    const description = new ui_popupcomponentsmaker.PopupComponentsMakerItem({
	      html: descriptionBlock,
	      withoutBackground: false
	    });
	    main_core.Dom.addClass(description.getContainer(), 'ui-popupcomponentsmaker-header-tariff__section-message-wrapper');
	    description.getContainer().style.marginTop = '14px';
	    description.getContainer().classList.add('--transparent');
	    return description.getContainer();
	  }
	  renderBtn(btnOptions) {
	    const btn = btnOptions instanceof ui_buttons.Button ? btnOptions : new ui_buttons.Button({
	      text: btnOptions.label,
	      color: ui_buttons.ButtonColor.LIGHT_BORDER,
	      size: ui_buttons.ButtonSize.SMALL,
	      link: btnOptions.url,
	      onclick: () => {
	        if (babelHelpers.classPrivateFieldLooseBase(this, _options)[_options].analyticsCallback) {
	          babelHelpers.classPrivateFieldLooseBase(this, _options)[_options].analyticsCallback('click-button-header', btnOptions.url);
	        }
	      },
	      round: true,
	      noCaps: true
	    });
	    btn.addClass('ui-popupcomponentsmaker-header-tariff__button ui-btn-themes');
	    return btn.render();
	  }
	  renderIcon(iconClass) {
	    if (main_core.Type.isStringFilled(iconClass)) {
	      return main_core.Tag.render(_t10 || (_t10 = _$1`
				<div class="ui-popupcomponentsmaker-header-tariff__icon">
					<div class="ui-icon-set ${0}"></div>
				</div>
			`), iconClass);
	    }
	    return main_core.Tag.render(_t11 || (_t11 = _$1``));
	  }
	  embedIcon(icon) {
	    if (main_core.Type.isDomNode(icon)) {
	      return main_core.Tag.render(_t12 || (_t12 = _$1`
				<div class="ui-popupcomponentsmaker-header-tariff__icon">
					${0}
				</div>
			`), icon);
	    }
	    return main_core.Tag.render(_t13 || (_t13 = _$1``));
	  }
	  render() {
	    if (babelHelpers.classPrivateFieldLooseBase(this, _content$1)[_content$1]) {
	      return babelHelpers.classPrivateFieldLooseBase(this, _content$1)[_content$1];
	    }
	    let btnContent = '';
	    if (babelHelpers.classPrivateFieldLooseBase(this, _options)[_options].button) {
	      btnContent = main_core.Tag.render(_t14 || (_t14 = _$1`
				<div class="ui-popupcomponentsmaker-header-tariff__button-bar">
					${0}
				</div>`), this.renderBtn(babelHelpers.classPrivateFieldLooseBase(this, _options)[_options].button));
	    }
	    babelHelpers.classPrivateFieldLooseBase(this, _content$1)[_content$1] = main_core.Tag.render(_t15 || (_t15 = _$1`
			<div class="ui-popupcomponentsmaker-header-tariff__wrapper">
				<div class="ui-popupcomponentsmaker-header-tariff__title-section">
					${0}
					${0}
				</div>
				
				${0}
				${0}
				
			</div>
		`), babelHelpers.classPrivateFieldLooseBase(this, _options)[_options].icon instanceof HTMLElement ? this.embedIcon(babelHelpers.classPrivateFieldLooseBase(this, _options)[_options].icon) : this.renderIcon(babelHelpers.classPrivateFieldLooseBase(this, _options)[_options].iconClass), this.renderTitle(babelHelpers.classPrivateFieldLooseBase(this, _options)[_options].top), this.renderDescription(babelHelpers.classPrivateFieldLooseBase(this, _options)[_options].info), btnContent);
	    return babelHelpers.classPrivateFieldLooseBase(this, _content$1)[_content$1];
	  }
	}

	let _$2 = t => t,
	  _t$2,
	  _t2$2;
	var _getThemePicker = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getThemePicker");
	var _applyTheme = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("applyTheme");
	class PopupHeader extends ui_popupcomponentsmaker.PopupComponentsMakerItem {
	  constructor(options = {}) {
	    options.withoutBackground = true;
	    options.backgroundColor = null;
	    options.backgroundImage = null;
	    super(options);
	    Object.defineProperty(this, _applyTheme, {
	      value: _applyTheme2
	    });
	    Object.defineProperty(this, _getThemePicker, {
	      value: _getThemePicker2
	    });
	  }
	  getContainer() {
	    if (!this.layout.container) {
	      var _babelHelpers$classPr;
	      const theme = (_babelHelpers$classPr = babelHelpers.classPrivateFieldLooseBase(this, _getThemePicker)[_getThemePicker]()) == null ? void 0 : _babelHelpers$classPr.getAppliedTheme();
	      this.layout.container = main_core.Tag.render(_t$2 || (_t$2 = _$2`<div class="ui-popupcomponentsmaker__header">${0}</div>`), this.getContent());
	      this.bacgroundNode = main_core.Tag.render(_t2$2 || (_t2$2 = _$2`<div class="ui-popupcomponentsmaker__header-background"></div>`));
	      main_core.Dom.append(this.bacgroundNode, this.layout.container);
	      if (theme) {
	        babelHelpers.classPrivateFieldLooseBase(this, _applyTheme)[_applyTheme](this.bacgroundNode, theme);
	      }
	      main_core_events.EventEmitter.subscribe('BX.Intranet.Bitrix24:ThemePicker:onThemeApply', event => {
	        babelHelpers.classPrivateFieldLooseBase(this, _applyTheme)[_applyTheme](this.bacgroundNode, event.data.theme);
	      });
	    }
	    return super.getContainer();
	  }
	  static createByJson(popupId, options) {
	    const builder = new HeaderBuilder(options);
	    const header = new PopupHeader({
	      html: builder.render()
	    });
	    main_core_events.EventEmitter.subscribe('BX.Main.Popup:onClose', event => {
	      if (popupId === event.target.uniquePopupId) {
	        var _builder$getPlayer;
	        (_builder$getPlayer = builder.getPlayer()) == null ? void 0 : _builder$getPlayer.stop();
	      }
	    });
	    return header;
	  }
	}
	function _getThemePicker2() {
	  var _BX$Intranet$Bitrix, _BX$Intranet, _BX$Intranet$Bitrix2, _top$BX$Intranet, _top$BX$Intranet$Bitr;
	  return (_BX$Intranet$Bitrix = (_BX$Intranet = BX.Intranet) == null ? void 0 : (_BX$Intranet$Bitrix2 = _BX$Intranet.Bitrix24) == null ? void 0 : _BX$Intranet$Bitrix2.ThemePicker.Singleton) != null ? _BX$Intranet$Bitrix : (_top$BX$Intranet = top.BX.Intranet) == null ? void 0 : (_top$BX$Intranet$Bitr = _top$BX$Intranet.Bitrix24) == null ? void 0 : _top$BX$Intranet$Bitr.ThemePicker.Singleton;
	}
	function _applyTheme2(container, theme) {
	  const previewImage = `url('${main_core.Text.encode(theme.previewImage)}')`;
	  main_core.Dom.style(container, 'backgroundImage', previewImage);
	  main_core.Dom.removeClass(this.layout.container, 'bitrix24-theme-default bitrix24-theme-dark bitrix24-theme-light');
	  let themeClass = 'bitrix24-theme-default';
	  if (theme.id !== 'default') {
	    themeClass = String(theme.id).indexOf('dark:') === 0 ? 'bitrix24-theme-dark' : 'bitrix24-theme-light';
	  }
	  main_core.Dom.addClass(this.layout.container, themeClass);
	}

	let _$3 = t => t,
	  _t$3,
	  _t2$3;
	var _size = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("size");
	var _getInnerBlock = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getInnerBlock");
	class Skeleton {
	  constructor(size = 473) {
	    Object.defineProperty(this, _getInnerBlock, {
	      value: _getInnerBlock2
	    });
	    Object.defineProperty(this, _size, {
	      writable: true,
	      value: void 0
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _size)[_size] = size;
	  }
	  get() {
	    return main_core.Tag.render(_t$3 || (_t$3 = _$3`
			<div style="height: ${0}px;" class="popup-with-header-skeleton__wrap">
				<div class="popup-with-header-skeleton__header">
					<div class="popup-with-header-skeleton__header-top">
						<div class="popup-with-header-skeleton__header-circle">
							<div class="popup-with-header-skeleton__header-circle-inner"></div>
						</div>
						<div style="width: 100%;">
							<div style="margin-bottom: 12px; max-width: 219px; height: 6px; background: rgba(255,255,255,.8);" class="popup-with-header-skeleton__line"></div>
							<div style="max-width: 119px; height: 4px;" class="popup-with-header-skeleton__line"></div>
						</div>
					</div>
					<div class="popup-with-header-skeleton__header-bottom">
						<div class="popup-with-header-skeleton__header-bottom-circle-box">
							<div class="popup-with-header-skeleton__header-bottom-circle"></div>
							<div class="popup-with-header-skeleton__header-bottom-circle-blue"></div>
						</div>
						<div style="width: 100%;">
							<div style="margin-bottom: 9px; max-width: 193px; height: 5px;" class="popup-with-header-skeleton__line"></div>
							<div style="margin-bottom: 15px; max-width: 163px; height: 5px;" class="popup-with-header-skeleton__line"></div>
							<div style="margin-bottom: 9px; max-width: 156px; height: 2px;" class="popup-with-header-skeleton__line"></div>
							<div style="margin-bottom: 9px; max-width: 93px; height: 2px;" class="popup-with-header-skeleton__line"></div>
						</div>
					</div>
				</div>
				<div class="popup-with-header-skeleton__bottom">
					${0}
					${0}
					${0}
				</div>
			</div>
		`), babelHelpers.classPrivateFieldLooseBase(this, _size)[_size], babelHelpers.classPrivateFieldLooseBase(this, _getInnerBlock)[_getInnerBlock](), babelHelpers.classPrivateFieldLooseBase(this, _getInnerBlock)[_getInnerBlock](), babelHelpers.classPrivateFieldLooseBase(this, _getInnerBlock)[_getInnerBlock]());
	  }
	}
	function _getInnerBlock2() {
	  return main_core.Tag.render(_t2$3 || (_t2$3 = _$3`
			<div class="popup-with-header-skeleton__bottom-inner">
				<div class="popup-with-header-skeleton__bottom-left">
					<div style="margin-bottom: 11px; max-width: 193px; height: 5px;" class="popup-with-header-skeleton__line"></div>
					<div style="margin-bottom: 17px; max-width: 163px; height: 5px;" class="popup-with-header-skeleton__line"></div>
					<div style="margin-bottom: 9px; max-width: 168px; height: 3px; background: rgba(149,156,164,.23);" class="popup-with-header-skeleton__line --dark-animation"></div>
					<div style="margin-bottom: 9px; max-width: 131px; height: 3px; background: rgba(149,156,164,.23);" class="popup-with-header-skeleton__line --dark-animation"></div>
					<div style="margin-bottom: 9px; max-width: 150px; height: 3px; background: rgba(149,156,164,.23);" class="popup-with-header-skeleton__line --dark-animation"></div>
					<div style="margin-bottom: 9px; max-width: 56px; height: 5px; background: rgba(32,102,176,.23);" class="popup-with-header-skeleton__line"></div>
				</div>
				<div class="popup-with-header-skeleton__bottom-right">
					<div class="popup-with-header-skeleton-btn"></div>
					<div style="margin: 0 auto; max-width: 36px; height: 3px; background: #d9d9d9;" class="popup-with-header-skeleton__line"></div>
				</div>
			</div>
		`));
	}

	let _$4 = t => t,
	  _t$4,
	  _t2$4,
	  _t3$2,
	  _t4$2,
	  _t5$2,
	  _t6$1;
	var _popupOptions = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("popupOptions");
	var _prepareItemsContent = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("prepareItemsContent");
	var _getThemePicker$1 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getThemePicker");
	var _applyTheme$1 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("applyTheme");
	class PopupWithHeader extends ui_popupcomponentsmaker.PopupComponentsMaker {
	  constructor(options) {
	    var _options$animationTem, _options$skeletonSize;
	    super(options);
	    Object.defineProperty(this, _applyTheme$1, {
	      value: _applyTheme2$1
	    });
	    Object.defineProperty(this, _getThemePicker$1, {
	      value: _getThemePicker2$1
	    });
	    Object.defineProperty(this, _prepareItemsContent, {
	      value: _prepareItemsContent2
	    });
	    Object.defineProperty(this, _popupOptions, {
	      writable: true,
	      value: void 0
	    });
	    this.header = options.header instanceof PopupHeader ? options.header : null;
	    this.template = options.template instanceof ui_popupWithHeader.BaseTemplate ? options.template : null;
	    this.asyncData = options.asyncData instanceof BX.Promise || options.asyncData instanceof Promise ? options.asyncData : null;
	    this.animationTemplate = (_options$animationTem = options.animationTemplate) != null ? _options$animationTem : true;
	    this.skeletonSize = (_options$skeletonSize = options.skeletonSize) != null ? _options$skeletonSize : 473;
	    this.analyticsCallback = main_core.Type.isFunction(options.analyticsCallback) ? options.analyticsCallback : null;
	    babelHelpers.classPrivateFieldLooseBase(this, _popupOptions)[_popupOptions] = main_core.Type.isPlainObject(options.popupOptions) ? options.popupOptions : {};
	  }
	  getPopup() {
	    if (!this.popup) {
	      const popupWidth = this.width ? this.width : 344;
	      const popupId = this.id ? `${this.id}-popup` : null;
	      let content = [];
	      if (!this.asyncData) {
	        content = main_core.Tag.render(_t$4 || (_t$4 = _$4`
					<div>
						${0}
					<div>
				`), this.getHeaderWrapper());
	        if (this.content.length > 0) {
	          content.append(main_core.Tag.render(_t2$4 || (_t2$4 = _$4`<div style="padding: 0 ${0}px ${0}px ${0}px">${0}</div>`), this.padding, this.padding, this.padding, this.getContentWrapper()));
	        }
	      }
	      this.popup = new main_popup.Popup(popupId, this.target, {
	        className: 'ui-popupcomponentmaker',
	        contentBackground: 'transparent',
	        contentPadding: this.contentPadding,
	        angle: this.useAngle ? {
	          offset: popupWidth / 2 - 16
	        } : false,
	        offsetTop: this.offsetTop,
	        width: popupWidth,
	        offsetLeft: -(popupWidth / 2) + (this.target ? this.target.offsetWidth / 2 : 0) + 40,
	        autoHide: true,
	        closeByEsc: true,
	        padding: 0,
	        animation: 'fading-slide',
	        content,
	        cacheable: this.cacheable,
	        ...babelHelpers.classPrivateFieldLooseBase(this, _popupOptions)[_popupOptions]
	      });
	      if (this.asyncData) {
	        const container = this.popup.getContentContainer();
	        main_core.Dom.clean(container);
	        main_core.Dom.append(this.getSkeleton(), container);
	        this.preparePopupAngly(container);
	        if (main_core.Type.isDomNode(container.parentNode)) {
	          main_core.Dom.addClass(container.parentNode, '--with-header');
	        }
	        this.asyncData.then(response => {
	          main_core.Dom.clean(container);
	          response.data.header.analyticsCallback = this.analyticsCallback;
	          this.header = PopupHeader.createByJson(popupId, response.data.header);
	          content = main_core.Tag.render(_t3$2 || (_t3$2 = _$4`
						<div>
							${0}
						<div>
					`), this.getHeaderWrapper());
	          let hasContent = response.data.items && this.template;
	          if (hasContent) {
	            this.template.setOptions({
	              items: response.data.items,
	              analyticsCallback: this.analyticsCallback
	            });
	            this.content = this.template.getContent();
	            this.contentWrapper = null;
	            if (main_core.Dom.hasClass(this.getHeaderWrapper(), '--empty-content')) {
	              main_core.Dom.removeClass(this.getHeaderWrapper(), '--empty-content');
	            }
	            if (!this.getHeaderWrapper().querySelector('.ui-popupcomponentsmaker__round-player-box') && !main_core.Dom.hasClass(this.getHeaderWrapper(), '--without-video')) {
	              main_core.Dom.addClass(this.getHeaderWrapper(), '--without-video');
	            }
	            if (this.content.length > 0) {
	              content.append(main_core.Tag.render(_t4$2 || (_t4$2 = _$4`<div class="ui-popupcomponentmaker__content-wrap">${0}</div>`), this.getContentWrapper()));
	            } else {
	              hasContent = false;
	            }
	          }
	          main_core.Dom.append(content, container);
	          if (hasContent) {
	            if (this.popup.isShown()) {
	              babelHelpers.classPrivateFieldLooseBase(this, _prepareItemsContent)[_prepareItemsContent](content);
	            } else {
	              this.popup.subscribeOnce('onShow', () => {
	                babelHelpers.classPrivateFieldLooseBase(this, _prepareItemsContent)[_prepareItemsContent](content);
	              });
	            }
	          }
	          this.popup.adjustPosition({
	            forceBindPosition: true,
	            position: this.popup.isBottomAngle() ? 'top' : 'bottom'
	          });
	        });
	      }
	      this.popup.getContentContainer().style.overflowX = null;
	    }
	    return this.popup;
	  }
	  getSkeleton() {
	    if (!this.skeleton) {
	      var _babelHelpers$classPr;
	      this.skeleton = new Skeleton(this.skeletonSize).get();
	      const theme = (_babelHelpers$classPr = babelHelpers.classPrivateFieldLooseBase(this, _getThemePicker$1)[_getThemePicker$1]()) == null ? void 0 : _babelHelpers$classPr.getAppliedTheme();
	      if (!theme) {
	        return this.skeleton;
	      }
	      const headerContainer = this.skeleton.querySelector('.popup-with-header-skeleton__header');
	      babelHelpers.classPrivateFieldLooseBase(this, _applyTheme$1)[_applyTheme$1](headerContainer, theme);
	      main_core_events.EventEmitter.subscribe('BX.Intranet.Bitrix24:ThemePicker:onThemeApply', event => {
	        babelHelpers.classPrivateFieldLooseBase(this, _applyTheme$1)[_applyTheme$1](headerContainer, event.data.theme);
	      });
	    }
	    return this.skeleton;
	  }
	  preparePopupAngly(popupContainer) {
	    var _popupContainer$paren;
	    const angly = popupContainer == null ? void 0 : (_popupContainer$paren = popupContainer.parentNode) == null ? void 0 : _popupContainer$paren.querySelector('.popup-window-angly--arrow');
	    if (main_core.Type.isDomNode(angly)) {
	      var _babelHelpers$classPr2;
	      const theme = (_babelHelpers$classPr2 = babelHelpers.classPrivateFieldLooseBase(this, _getThemePicker$1)[_getThemePicker$1]()) == null ? void 0 : _babelHelpers$classPr2.getAppliedTheme();
	      if (theme) {
	        babelHelpers.classPrivateFieldLooseBase(this, _applyTheme$1)[_applyTheme$1](angly, theme);
	        main_core_events.EventEmitter.subscribe('BX.Intranet.Bitrix24:ThemePicker:onThemeApply', event => {
	          babelHelpers.classPrivateFieldLooseBase(this, _applyTheme$1)[_applyTheme$1](angly, event.data.theme);
	        });
	      }
	      main_core.Dom.style(angly, 'background-position', 'center top');
	      main_core.Dom.addClass(popupContainer == null ? void 0 : popupContainer.parentNode, '--with-header');
	    }
	  }
	  getHeaderWrapper() {
	    if (!this.header) {
	      return null;
	    }
	    if (!this.headerWrapper) {
	      var _this$header, _this$header2, _this$header3, _this$header4, _this$header4$html;
	      this.headerWrapper = main_core.Tag.render(_t5$2 || (_t5$2 = _$4`
				<div class="ui-popupcomponentmaker__header-content"></div>
			`));
	      if (this.content.length <= 0) {
	        this.headerWrapper.classList.add('--empty-content');
	      }
	      const sectionNode = this.getSection();
	      if ((_this$header = this.header) != null && _this$header.marginBottom) {
	        main_core.Type.isNumber(this.header.marginBottom) ? sectionNode.style.marginBottom = `${this.header.marginBottom}px` : null;
	      }
	      if ((_this$header2 = this.header) != null && _this$header2.className) {
	        main_core.Dom.addClass(sectionNode, this.header.className);
	      }
	      if (main_core.Type.isDomNode((_this$header3 = this.header) == null ? void 0 : _this$header3.html)) {
	        sectionNode.appendChild(this.getItem(this.header).getContainer());
	        this.headerWrapper.appendChild(sectionNode);
	      }
	      if (main_core.Type.isFunction((_this$header4 = this.header) == null ? void 0 : (_this$header4$html = _this$header4.html) == null ? void 0 : _this$header4$html.then)) {
	        this.adjustPromise(this.header, sectionNode);
	        this.headerWrapper.appendChild(sectionNode);
	      }
	    }
	    return this.headerWrapper;
	  }
	}
	function _prepareItemsContent2(content) {
	  main_core.Dom.addClass(this.getContentWrapper(), 'ui-popup-with-header__content');
	  content.append(main_core.Tag.render(_t6$1 || (_t6$1 = _$4`<div class="ui-popupcomponentmaker__content-wrap">${0}</div>`), this.getContentWrapper()));
	  if (this.popup.isBottomAngle() || !this.animationTemplate) {
	    main_core.Dom.style(this.getContentWrapper(), 'transition', 'none');
	  }
	  if (this.getContentWrapper().scrollHeight > 287 && !main_core.Dom.hasClass(this.getContentWrapper(), '--active-scroll')) {
	    main_core.Dom.style(this.getContentWrapper(), 'height', '287px');
	    main_core.Dom.style(this.getContentWrapper(), 'overflow-y', 'scroll');
	    main_core.Dom.addClass(content, 'active-scroll');
	  } else {
	    main_core.Dom.style(this.getContentWrapper(), 'height', `${this.getContentWrapper().scrollHeight}px`);
	  }
	}
	function _getThemePicker2$1() {
	  var _BX$Intranet$Bitrix, _BX$Intranet, _BX$Intranet$Bitrix2, _top$BX$Intranet, _top$BX$Intranet$Bitr;
	  return (_BX$Intranet$Bitrix = (_BX$Intranet = BX.Intranet) == null ? void 0 : (_BX$Intranet$Bitrix2 = _BX$Intranet.Bitrix24) == null ? void 0 : _BX$Intranet$Bitrix2.ThemePicker.Singleton) != null ? _BX$Intranet$Bitrix : (_top$BX$Intranet = top.BX.Intranet) == null ? void 0 : (_top$BX$Intranet$Bitr = _top$BX$Intranet.Bitrix24) == null ? void 0 : _top$BX$Intranet$Bitr.ThemePicker.Singleton;
	}
	function _applyTheme2$1(container, theme) {
	  const previewImage = `url('${main_core.Text.encode(theme.previewImage)}')`;
	  main_core.Dom.style(container, 'backgroundImage', previewImage);
	  main_core.Dom.removeClass(container, 'bitrix24-theme-default bitrix24-theme-dark bitrix24-theme-light');
	  let themeClass = 'bitrix24-theme-default';
	  if (theme.id !== 'default') {
	    themeClass = String(theme.id).indexOf('dark:') === 0 ? 'bitrix24-theme-dark' : 'bitrix24-theme-light';
	  }
	  main_core.Dom.addClass(container, themeClass);
	}

	class BaseTemplate {
	  getContent() {
	    throw new Error('Must be implemented in a child class');
	  }
	  setOptions(options) {
	    this.options = options;
	  }
	}

	let _$5 = t => t,
	  _t$5,
	  _t2$5,
	  _t3$3,
	  _t4$3,
	  _t5$3,
	  _t6$2;
	var _cache = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("cache");
	var _getItemContent = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getItemContent");
	var _getTitle = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getTitle");
	var _getIcon = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getIcon");
	var _getDescription = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getDescription");
	var _getMoreLink = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getMoreLink");
	var _getButton = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getButton");
	var _getButtonDescription = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getButtonDescription");
	var _setTextStyles = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("setTextStyles");
	class SaleTemplate extends BaseTemplate {
	  constructor(options = {}) {
	    super();
	    Object.defineProperty(this, _setTextStyles, {
	      value: _setTextStyles2
	    });
	    Object.defineProperty(this, _getButtonDescription, {
	      value: _getButtonDescription2
	    });
	    Object.defineProperty(this, _getButton, {
	      value: _getButton2
	    });
	    Object.defineProperty(this, _getMoreLink, {
	      value: _getMoreLink2
	    });
	    Object.defineProperty(this, _getDescription, {
	      value: _getDescription2
	    });
	    Object.defineProperty(this, _getIcon, {
	      value: _getIcon2
	    });
	    Object.defineProperty(this, _getTitle, {
	      value: _getTitle2
	    });
	    Object.defineProperty(this, _getItemContent, {
	      value: _getItemContent2
	    });
	    Object.defineProperty(this, _cache, {
	      writable: true,
	      value: new main_core.Cache.MemoryCache()
	    });
	    this.options = options;
	  }
	  getContent() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _cache)[_cache].remember('popup-content', () => {
	      const content = [];
	      this.options.items.forEach((item, index) => {
	        var _item$styles, _item$styles2;
	        const itemContent = babelHelpers.classPrivateFieldLooseBase(this, _getItemContent)[_getItemContent](item);
	        if ((_item$styles = item.styles) != null && _item$styles.color) {
	          main_core.Dom.style(itemContent, 'color', item.styles.color);
	        }
	        content.push({
	          html: itemContent,
	          background: (_item$styles2 = item.styles) == null ? void 0 : _item$styles2.background,
	          margin: index === 0 ? '12px 0 0 0' : null
	        });
	      });
	      return content;
	    });
	  }
	}
	function _getItemContent2(config) {
	  return main_core.Tag.render(_t$5 || (_t$5 = _$5`
			<div class="ui-popupconstructor-content-item-wrapper">
				<div class="ui-popupconstructor-content-item-wrapper_information">
					<div class="ui-popupconstructor-content-item-wrapper-title">
						${0}
						${0}
					</div>
					<div>
						${0}
						${0}
					</div>
				</div>
				<div class="ui-popupconstructor-content-item-wrapper_button">
					${0}
					${0}
				</div>
			</div>
		`), config.icon ? babelHelpers.classPrivateFieldLooseBase(this, _getIcon)[_getIcon](config.icon) : null, config.title ? babelHelpers.classPrivateFieldLooseBase(this, _getTitle)[_getTitle](config.title) : null, config.description ? babelHelpers.classPrivateFieldLooseBase(this, _getDescription)[_getDescription](config.description) : null, config.more ? babelHelpers.classPrivateFieldLooseBase(this, _getMoreLink)[_getMoreLink](config.more, config.button) : null, config.button ? babelHelpers.classPrivateFieldLooseBase(this, _getButton)[_getButton](config.button) : null, config.button.description ? babelHelpers.classPrivateFieldLooseBase(this, _getButtonDescription)[_getButtonDescription](config.button.description) : null);
	}
	function _getTitle2(config) {
	  const title = main_core.Tag.render(_t2$5 || (_t2$5 = _$5`
			<div class="ui-popupconstructor-content-item__title">${0}</div>
		`), config.text);
	  babelHelpers.classPrivateFieldLooseBase(this, _setTextStyles)[_setTextStyles](title, config);
	  return title;
	}
	function _getIcon2(config) {
	  const icon = main_core.Tag.render(_t3$3 || (_t3$3 = _$5`
			<div class="ui-popupconstructor-content-item__icon ui-icon-set --${0}"></div>
		`), config.name);
	  if (config.color) {
	    main_core.Dom.style(icon, 'background-color', config.color);
	  }
	  return icon;
	}
	function _getDescription2(config) {
	  const description = main_core.Tag.render(_t4$3 || (_t4$3 = _$5`
			<div class="ui-popupconstructor-content-item__description">
				${0}
			</div>
		`), config.text);
	  babelHelpers.classPrivateFieldLooseBase(this, _setTextStyles)[_setTextStyles](description, config);
	  return description;
	}
	function _getMoreLink2(config, configMainButton) {
	  const onclick = () => {
	    var _this$options;
	    if (config.code) {
	      ui_infoHelper.FeaturePromotersRegistry.getPromoter({
	        code: config.code
	      }).show();
	    } else if (config.articleId) {
	      top.BX.Helper.show(`redirect=detail&code=${config.articleId}`);
	    }
	    if ((_this$options = this.options) != null && _this$options.analyticsCallback) {
	      this.options.analyticsCallback('click-more', configMainButton.url);
	    }
	  };
	  const moreLink = main_core.Tag.render(_t5$3 || (_t5$3 = _$5`
			<div class="ui-popupconstructor-content-item__more-link" onclick="${0}">${0}</div>
		`), onclick, config.text.text);
	  babelHelpers.classPrivateFieldLooseBase(this, _setTextStyles)[_setTextStyles](moreLink, config.text);
	  return moreLink;
	}
	function _getButton2(config) {
	  const buttonTag = config.target ? ui_buttons.ButtonTag.BUTTON : ui_buttons.ButtonTag.LINK;
	  const button = new ui_buttons.Button({
	    round: true,
	    text: config.text,
	    size: ui_buttons.Button.Size.EXTRA_SMALL,
	    color: ui_buttons.Button.Color.SUCCESS,
	    noCaps: true,
	    tag: buttonTag,
	    link: config.target ? null : config.url,
	    onclick: () => {
	      var _this$options2;
	      if (config.target) {
	        window.open(config.url, config.target);
	      }
	      if ((_this$options2 = this.options) != null && _this$options2.analyticsCallback) {
	        this.options.analyticsCallback('click-button', config.url);
	      }
	    }
	  });
	  if (config.backgroundColor) {
	    main_core.Dom.style(button.render(), 'background-color', config.backgroundColor);
	    button.setColor(ui_buttons.Button.Color.LIGHT);
	  }
	  return button.render();
	}
	function _getButtonDescription2(config) {
	  const buttonDescription = main_core.Tag.render(_t6$2 || (_t6$2 = _$5`
			<div class="ui-popupconstructor-content-item__button-description">
				${0}
			</div>
		`), config.text);
	  babelHelpers.classPrivateFieldLooseBase(this, _setTextStyles)[_setTextStyles](buttonDescription, config);
	  return buttonDescription;
	}
	function _setTextStyles2(element, config) {
	  if (config.color) {
	    main_core.Dom.style(element, 'color', config.color);
	  }
	  if (config.fontSize) {
	    main_core.Dom.style(element, 'font-size', config.fontSize);
	  }
	  if (config.weight) {
	    main_core.Dom.style(element, 'font-weight', config.weight);
	  }
	}

	exports.PopupWithHeader = PopupWithHeader;
	exports.PopupHeader = PopupHeader;
	exports.SaleTemplate = SaleTemplate;
	exports.BaseTemplate = BaseTemplate;

}((this.BX.UI = this.BX.UI || {}),BX.Event,BX,BX.UI,BX.UI,BX.UI.IconSet,BX.Main,BX.UI,BX,BX.UI,BX.UI));
//# sourceMappingURL=popup-with-header.bundle.js.map
