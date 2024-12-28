/* eslint-disable */
this.BX = this.BX || {};
this.BX.UI = this.BX.UI || {};
(function (exports,ui_videoJs,main_core,main_core_events) {
	'use strict';

	var _isStarted = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("isStarted");
	var _players = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("players");
	var _init = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("init");
	var _bindPlayerEvents = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("bindPlayerEvents");
	var _handleScroll = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("handleScroll");
	class PlayerManager {
	  static addPlayer(player) {
	    babelHelpers.classPrivateFieldLooseBase(this, _players)[_players].push(player);
	    babelHelpers.classPrivateFieldLooseBase(this, _bindPlayerEvents)[_bindPlayerEvents](player);
	    if (player.autostart || player.lazyload) {
	      babelHelpers.classPrivateFieldLooseBase(this, _init)[_init]();
	    }
	  }
	  static getElementCoords(id) {
	    const VISIBLE_OFFSET = 0.25;
	    const box = document.getElementById(id).getBoundingClientRect();
	    const elementHeight = box.bottom - box.top;
	    const top = box.top + VISIBLE_OFFSET * elementHeight;
	    const bottom = box.bottom - VISIBLE_OFFSET * elementHeight;
	    const elementWidth = box.right - box.left;
	    const left = box.left + VISIBLE_OFFSET * elementWidth;
	    const right = box.right - VISIBLE_OFFSET * elementWidth;
	    return {
	      top: top + window.pageYOffset,
	      bottom: bottom + window.pageYOffset,
	      left: left + window.pageXOffset,
	      right: right + window.pageXOffset,
	      originTop: top,
	      originLeft: left,
	      originBottom: bottom,
	      originRight: right
	    };
	  }
	  static isVisibleOnScreen(id, screens) {
	    let visible = false;
	    const coords = this.getElementCoords(id);
	    const clientHeight = document.documentElement.clientHeight;
	    let windowTop = window.pageYOffset || document.documentElement.scrollTop;
	    let windowBottom = windowTop + clientHeight;
	    const numberOfScreens = screens ? parseInt(screens, 10) : 1;
	    if (numberOfScreens > 1) {
	      windowTop -= clientHeight * (numberOfScreens - 1);
	      windowBottom += clientHeight * (numberOfScreens - 1);
	    }
	    const topVisible = coords.top > windowTop && coords.top < windowBottom;
	    const bottomVisible = coords.bottom < windowBottom && coords.bottom > windowTop;
	    const onScreen = topVisible || bottomVisible;
	    if (onScreen && screens > 1) {
	      return true;
	    }
	    if (!onScreen) {
	      return false;
	    }
	    const playerElement = document.getElementById(id);
	    const playerCenterX = coords.originLeft + (coords.originRight - coords.originLeft) / 2;
	    const playerCenterY = coords.originTop + (coords.originBottom - coords.originTop) / 2 + 20;
	    const currentPlayerCenterElement = document.elementFromPoint(playerCenterX, playerCenterY);
	    if (currentPlayerCenterElement !== null && (currentPlayerCenterElement === playerElement || currentPlayerCenterElement.parentNode === playerElement || currentPlayerCenterElement.parentNode.parentNode === playerElement)) {
	      visible = true;
	    }
	    return onScreen && visible;
	  }
	  static getPlayerById(id) {
	    if (!main_core.Type.isStringFilled(id)) {
	      return null;
	    }
	    for (const player of babelHelpers.classPrivateFieldLooseBase(this, _players)[_players]) {
	      if (player.id === id) {
	        return player;
	      }
	    }
	    return null;
	  }
	}
	function _init2() {
	  if (babelHelpers.classPrivateFieldLooseBase(this, _isStarted)[_isStarted]) {
	    return;
	  }
	  babelHelpers.classPrivateFieldLooseBase(this, _isStarted)[_isStarted] = true;
	  main_core.Event.ready(() => {
	    main_core.Event.bind(window, 'scroll', main_core.Runtime.throttle(babelHelpers.classPrivateFieldLooseBase(this, _handleScroll)[_handleScroll], 300, this));
	    setTimeout(() => {
	      babelHelpers.classPrivateFieldLooseBase(this, _handleScroll)[_handleScroll]();
	    }, 50);

	    /** @type {BX.SidePanel.Manager} */
	    const sliderManager = main_core.Reflection.getClass('top.BX.SidePanel.Instance');
	    if (window !== window.top && sliderManager !== null) {
	      // When players are inside an iframe
	      const currentSlider = sliderManager.getSliderByWindow(window);
	      if (currentSlider) {
	        main_core.Event.EventEmitter.subscribe(currentSlider, 'SidePanel.Slider:onCloseComplete', () => {
	          babelHelpers.classPrivateFieldLooseBase(this, _players)[_players].forEach(player => {
	            player.pause();
	          });
	        });
	      }
	    }
	  });
	}
	function _bindPlayerEvents2(player) {
	  const events = player.getEventList();
	  for (const eventName of events) {
	    main_core.Event.EventEmitter.subscribe(player, eventName, () => {
	      main_core.Event.EventEmitter.emit(this, `PlayerManager.${eventName}`, new main_core_events.BaseEvent({
	        compatData: [player]
	      }));
	    });
	  }
	}
	function _handleScroll2() {
	  if (babelHelpers.classPrivateFieldLooseBase(this, _players)[_players].length === 0) {
	    return;
	  }
	  let topVisiblePlayer = false;
	  let isAnyPlaying = false;
	  const players = [...babelHelpers.classPrivateFieldLooseBase(this, _players)[_players]];
	  for (const [index, player] of players.entries()) {
	    if (!document.getElementById(player.id)) {
	      babelHelpers.classPrivateFieldLooseBase(this, _players)[_players].splice(index, 1);
	      continue;
	    }
	    if (player.lazyload && !player.inited && this.isVisibleOnScreen(player.id, 2)) {
	      player.init();
	    }
	    if (!player.autostart) {
	      continue;
	    }
	    if (player.active) {
	      continue;
	    }
	    if (player.isEnded()) {
	      continue;
	    }
	    if (this.isVisibleOnScreen(player.id, 1)) {
	      if (topVisiblePlayer === false) {
	        topVisiblePlayer = player;
	      }
	    } else if (player.isPlaying()) {
	      player.pause();
	    }
	    if (player.isPlaying()) {
	      isAnyPlaying = true;
	    }
	  }
	  if (isAnyPlaying) {
	    return;
	  }
	  if (topVisiblePlayer !== false) {
	    if (!topVisiblePlayer.inited) {
	      topVisiblePlayer.autostart = true;
	    } else if (topVisiblePlayer.isReady() && !topVisiblePlayer.isEnded()) {
	      topVisiblePlayer.mute(true);
	      main_core.Event.EventEmitter.subscribeOnce(topVisiblePlayer, 'Player:onClick', event => {
	        setTimeout(() => {
	          topVisiblePlayer.mute(false);
	        }, 100);
	      });
	      topVisiblePlayer.play();
	    }
	  }
	}
	Object.defineProperty(PlayerManager, _handleScroll, {
	  value: _handleScroll2
	});
	Object.defineProperty(PlayerManager, _bindPlayerEvents, {
	  value: _bindPlayerEvents2
	});
	Object.defineProperty(PlayerManager, _init, {
	  value: _init2
	});
	Object.defineProperty(PlayerManager, _isStarted, {
	  writable: true,
	  value: void 0
	});
	Object.defineProperty(PlayerManager, _players, {
	  writable: true,
	  value: []
	});

	/* eslint-disable @bitrix24/bitrix24-rules/no-native-dom-methods */
	ui_videoJs.videojs.hook('beforesetup', (videoEl, options) => {
	  main_core.Dom.addClass(videoEl, 'ui-video-player ui-icon-set__scope');
	  if (videoEl.tagName.toLowerCase() === 'audio') {
	    main_core.Dom.addClass(videoEl, 'vjs-audio-only-mode');
	  }
	  return options;
	});
	ui_videoJs.videojs.addLanguage('video-player', {
	  Play: main_core.Loc.getMessage('VIDEO_PLAYER_PLAY'),
	  Pause: main_core.Loc.getMessage('VIDEO_PLAYER_PAUSE'),
	  Replay: main_core.Loc.getMessage('VIDEO_PLAYER_REPLAY'),
	  'Current Time': main_core.Loc.getMessage('VIDEO_PLAYER_CURRENT_TIME'),
	  Duration: main_core.Loc.getMessage('VIDEO_PLAYER_DURATION'),
	  'Remaining Time': main_core.Loc.getMessage('VIDEO_PLAYER_REMAINING_TIME'),
	  Loaded: main_core.Loc.getMessage('VIDEO_PLAYER_LOADED'),
	  Progress: main_core.Loc.getMessage('VIDEO_PLAYER_PROGRESS'),
	  'Progress Bar': main_core.Loc.getMessage('VIDEO_PLAYER_PROGRESS_BAR'),
	  Fullscreen: main_core.Loc.getMessage('VIDEO_PLAYER_FULLSCREEN'),
	  'Exit Fullscreen': main_core.Loc.getMessage('VIDEO_PLAYER_EXIT_FULLSCREEN'),
	  Mute: main_core.Loc.getMessage('VIDEO_PLAYER_MUTE'),
	  Unmute: main_core.Loc.getMessage('VIDEO_PLAYER_UNMUTE'),
	  'Playback Rate': main_core.Loc.getMessage('VIDEO_PLAYER_PLAYBACK_RATE'),
	  'Volume Level': main_core.Loc.getMessage('VIDEO_PLAYER_VOLUME_LEVEL'),
	  'You aborted the media playback': main_core.Loc.getMessage('VIDEO_PLAYER_ABORTED_PLAYBACK'),
	  'A network error caused the media download to fail part-way.': main_core.Loc.getMessage('VIDEO_PLAYER_NETWORK_ERROR'),
	  'The media could not be loaded, either because the server or network failed or because the format is not supported.': main_core.Loc.getMessage('VIDEO_PLAYER_FORMAT_NOT_SUPPORTED'),
	  'The media playback was aborted due to a corruption problem or because the media used features your browser did not support.': main_core.Loc.getMessage('VIDEO_PLAYER_PLAYBACK_WAS_ABORTED'),
	  'No compatible source was found for this media.': main_core.Loc.getMessage('VIDEO_PLAYER_NO_COMPATIBLE_SOURCE'),
	  'The media is encrypted and we do not have the keys to decrypt it.': main_core.Loc.getMessage('VIDEO_PLAYER_MEDIA_IS_ENCRYPTED'),
	  'Play Video': main_core.Loc.getMessage('VIDEO_PLAYER_PLAY_VIDEO'),
	  'Exit Picture-in-Picture': main_core.Loc.getMessage('VIDEO_PLAYER_EXIT_PICTURE_IN_PICTURE'),
	  'Picture-in-Picture': main_core.Loc.getMessage('VIDEO_PLAYER_PICTURE_IN_PICTURE')
	});
	var _getStorageHash = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getStorageHash");
	var _fillParameters = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("fillParameters");
	var _getDefaultOptions = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getDefaultOptions");
	var _hideAudioControls = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("hideAudioControls");
	var _handlePlayOnce = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("handlePlayOnce");
	var _handleClick = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("handleClick");
	var _handleKeyDown = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("handleKeyDown");
	var _fireEvent = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("fireEvent");
	var _proxyEvents = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("proxyEvents");
	class Player {
	  constructor(id, _params) {
	    Object.defineProperty(this, _proxyEvents, {
	      value: _proxyEvents2
	    });
	    Object.defineProperty(this, _fireEvent, {
	      value: _fireEvent2
	    });
	    Object.defineProperty(this, _handleKeyDown, {
	      value: _handleKeyDown2
	    });
	    Object.defineProperty(this, _handleClick, {
	      value: _handleClick2
	    });
	    Object.defineProperty(this, _handlePlayOnce, {
	      value: _handlePlayOnce2
	    });
	    Object.defineProperty(this, _hideAudioControls, {
	      value: _hideAudioControls2
	    });
	    Object.defineProperty(this, _getDefaultOptions, {
	      value: _getDefaultOptions2
	    });
	    Object.defineProperty(this, _fillParameters, {
	      value: _fillParameters2
	    });
	    Object.defineProperty(this, _getStorageHash, {
	      value: _getStorageHash2
	    });
	    this.inited = false;
	    this.id = null;
	    this.muted = false;
	    this.hasStarted = false;
	    this.vjsPlayer = null;
	    this.isAudio = false;
	    this.id = id;
	    babelHelpers.classPrivateFieldLooseBase(this, _fillParameters)[_fillParameters](_params);
	    PlayerManager.addPlayer(this);
	    babelHelpers.classPrivateFieldLooseBase(this, _fireEvent)[_fireEvent]('onCreate');
	  }
	  isReady() {
	    // eslint-disable-next-line no-underscore-dangle
	    return this.vjsPlayer && this.vjsPlayer.isReady_;
	  }
	  play() {
	    this.setPlayedState();
	    this.hasStarted = true;
	    try {
	      this.vjsPlayer.play();
	    } catch {
	      // fail silently
	    }
	    babelHelpers.classPrivateFieldLooseBase(this, _fireEvent)[_fireEvent]('onPlay');
	  }
	  pause() {
	    try {
	      this.vjsPlayer.pause();
	    } catch {
	      // fail silently
	    }
	    babelHelpers.classPrivateFieldLooseBase(this, _fireEvent)[_fireEvent]('onPause');
	  }
	  toggle() {
	    if (this.isPlaying()) {
	      this.pause();
	    } else {
	      this.play();
	    }
	  }
	  isPlaying() {
	    if (this.vjsPlayer) {
	      return this.isReady() && !this.vjsPlayer.paused();
	    }
	    return false;
	  }
	  isEnded() {
	    if (this.vjsPlayer) {
	      return this.vjsPlayer.ended();
	    }
	    return false;
	  }
	  setPlayedState() {
	    const storageHash = babelHelpers.classPrivateFieldLooseBase(this, _getStorageHash)[_getStorageHash]();
	    const localStorage = main_core.Reflection.getClass('BX.localStorage');
	    if (localStorage) {
	      localStorage.set(storageHash, 'played', 14 * 24 * 3600);
	    }
	  }
	  isPlayed() {
	    const storageHash = babelHelpers.classPrivateFieldLooseBase(this, _getStorageHash)[_getStorageHash]();
	    /** @type {BX.localStorage} */
	    const localStorage = main_core.Reflection.getClass('BX.localStorage');
	    if (localStorage) {
	      return localStorage.get(storageHash) === 'played';
	    }
	    return true;
	  }
	  getElement() {
	    return document.getElementById(this.id);
	  }
	  createElement() {
	    let node = this.getElement();
	    if (node) {
	      return node;
	    }
	    if (!this.id) {
	      return null;
	    }
	    let tagName = 'video';
	    const classes = ['video-js', 'ui-video-player', 'ui-icon-set__scope'];
	    if (this.isAudio) {
	      tagName = 'audio';
	      classes.push('vjs-audio-only-mode');
	    }
	    let className = classes.join(' ');
	    if (this.skin) {
	      className += ` ${this.skin}`;
	    }
	    const attrs = {
	      id: this.id,
	      className,
	      width: this.width,
	      height: this.height,
	      controls: true
	    };
	    if (this.muted) {
	      attrs.muted = true;
	    }
	    node = main_core.Dom.create(tagName, {
	      attrs
	    });
	    if (main_core.Type.isArrayFilled(this.params.sources)) {
	      for (const source of this.params.sources) {
	        if (!source.src || !source.type) {
	          continue;
	        }
	        const sourceTag = main_core.Dom.create('source', {
	          attrs: {
	            src: source.src,
	            type: source.type
	          }
	        });
	        main_core.Dom.append(sourceTag, node);
	      }
	    }
	    return node;
	  }
	  setSource(source) {
	    if (!source) {
	      return;
	    }
	    this.vjsPlayer.src(source);
	    babelHelpers.classPrivateFieldLooseBase(this, _fireEvent)[_fireEvent]('onSetSource');
	  }
	  getSource() {
	    return this.vjsPlayer.src();
	  }
	  init() {
	    if (this.vjsPlayer !== null) {
	      return;
	    }
	    babelHelpers.classPrivateFieldLooseBase(this, _fireEvent)[_fireEvent]('onBeforeInit');
	    if (ui_videoJs.videojs.players[this.id]) {
	      ui_videoJs.videojs.players[this.id].dispose();
	    }
	    this.vjsPlayer = ui_videoJs.videojs(this.id, this.params);
	    if (this.isAudio) {
	      babelHelpers.classPrivateFieldLooseBase(this, _hideAudioControls)[_hideAudioControls]();
	    }
	    this.vjsPlayer.on('fullscreenchange', () => {
	      this.vjsPlayer.focus();
	    });
	    if (this.duration > 0) {
	      this.vjsPlayer.one('loadedmetadata', () => {
	        this.vjsPlayer.duration(this.duration);
	      });
	    }
	    this.vjsPlayer.ready(() => {
	      const controlBar = this.vjsPlayer.getChild('ControlBar');
	      const playbackButton = controlBar.getChild('PlaybackRateMenuButton');
	      if (playbackButton) {
	        // eslint-disable-next-line no-underscore-dangle
	        ui_videoJs.videojs.off(playbackButton.menuButton_.el(), 'mouseenter');
	        ui_videoJs.videojs.off(playbackButton.el(), 'mouseleave');
	      }
	      this.vjsPlayer.volume(this.volume);
	      this.vjsPlayer.one('play', babelHelpers.classPrivateFieldLooseBase(this, _handlePlayOnce)[_handlePlayOnce].bind(this));
	      this.inited = true;
	      if (main_core.Type.isFunction(this.onInit)) {
	        this.onInit(this);
	      }
	      babelHelpers.classPrivateFieldLooseBase(this, _fireEvent)[_fireEvent]('onAfterInit');
	      babelHelpers.classPrivateFieldLooseBase(this, _proxyEvents)[_proxyEvents]();
	      if (this.autostart && !this.lazyload) {
	        setTimeout(() => {
	          if (!this.hasStarted) {
	            this.play();
	          }
	        }, 200);
	      }
	    });
	  }
	  getEventList() {
	    return ['Player:onBeforeInit', 'Player:onAfterInit', 'Player:onCreate', 'Player:onSetSource', 'Player:onKeyDown', 'Player:onPlay', 'Player:onPause', 'Player:onClick', 'Player:onError', 'Player:onEnded'];
	  }
	  mute(mute) {
	    var _this$vjsPlayer;
	    return (_this$vjsPlayer = this.vjsPlayer) == null ? void 0 : _this$vjsPlayer.muted(mute);
	  }
	  isMuted() {
	    var _this$vjsPlayer2;
	    return (_this$vjsPlayer2 = this.vjsPlayer) == null ? void 0 : _this$vjsPlayer2.muted();
	  }
	  focus() {
	    var _this$vjsPlayer3;
	    (_this$vjsPlayer3 = this.vjsPlayer) == null ? void 0 : _this$vjsPlayer3.focus();
	  }
	  moveBackward(skipTime) {
	    const currentVideoTime = this.vjsPlayer.currentTime();
	    const liveTracker = this.vjsPlayer.liveTracker;
	    const seekableStart = liveTracker && liveTracker.isLive() && liveTracker.seekableStart();
	    let newTime = 0;
	    if (seekableStart && currentVideoTime - skipTime <= seekableStart) {
	      newTime = seekableStart;
	    } else if (currentVideoTime >= skipTime) {
	      newTime = currentVideoTime - skipTime;
	    }
	    this.vjsPlayer.currentTime(newTime);
	  }
	  moveForward(skipTime) {
	    if (!main_core.Type.isNumber(this.vjsPlayer.duration())) {
	      return;
	    }
	    const currentVideoTime = this.vjsPlayer.currentTime();
	    const liveTracker = this.vjsPlayer.liveTracker;
	    const duration = liveTracker && liveTracker.isLive() ? liveTracker.seekableEnd() : this.vjsPlayer.duration();
	    const newTime = currentVideoTime + skipTime <= duration ? currentVideoTime + skipTime : duration;
	    this.vjsPlayer.currentTime(newTime);
	  }
	  increasePlaybackRate() {
	    const playbackRates = this.vjsPlayer.playbackRates();
	    const currentPlayback = this.vjsPlayer.playbackRate();
	    const nextPlayback = playbackRates.find(value => {
	      return value > currentPlayback;
	    });
	    if (nextPlayback) {
	      this.vjsPlayer.playbackRate(nextPlayback);
	    }
	  }
	  decreasePlaybackRate() {
	    const playbackRates = [...this.vjsPlayer.playbackRates()].reverse();
	    const currentPlayback = this.vjsPlayer.playbackRate();
	    const prevPlayback = playbackRates.find(value => {
	      return value < currentPlayback;
	    });
	    if (prevPlayback) {
	      this.vjsPlayer.playbackRate(prevPlayback);
	    }
	  }
	}
	function _getStorageHash2() {
	  let storageHash = this.id;
	  if (main_core.Type.isArrayFilled(this.params.sources) && this.params.sources[0].src) {
	    storageHash = this.params.sources[0].src;
	  }
	  return `player_${storageHash}`;
	}
	function _fillParameters2(options) {
	  const defaults = babelHelpers.classPrivateFieldLooseBase(this, _getDefaultOptions)[_getDefaultOptions]();
	  const params = main_core.Type.isPlainObject(options) ? {
	    ...defaults,
	    ...options
	  } : defaults;
	  if (main_core.Type.isArrayFilled(params.techOrder)) {
	    // Compatibility
	    params.techOrder = params.techOrder.filter(tech => tech !== 'flash');
	  }
	  this.autostart = params.autostart || false;
	  if (params.playbackRate) {
	    params.playbackRate = parseFloat(params.playbackRate);
	    if (params.playbackRate !== 1) {
	      if (params.playbackRate <= 0) {
	        params.playbackRate = 1;
	      }
	      if (params.playbackRate > 3) {
	        params.playbackRate = 3;
	      }
	    }
	    if (params.playbackRate !== 1) {
	      this.playbackRate = params.playbackRate;
	    }
	  }
	  this.volume = params.volume || 0.8;
	  this.startTime = params.startTime || 0;
	  this.onInit = params.onInit;
	  this.lazyload = params.lazyload;
	  this.skin = params.skin || '';
	  this.isAudio = params.isAudio || false;
	  if (this.isAudio) {
	    params.width = params.width || 400;
	    params.height = params.height || 30;
	    params.audioOnlyMode = true;
	  } else {
	    params.width = Math.max(params.width || 560, 400);
	    params.height = Math.max(params.height || 315, 130);
	  }
	  this.width = params.width;
	  this.height = params.height;
	  this.duration = params.duration || null;
	  this.muted = params.muted || false;
	  this.params = params;
	  this.active = this.isPlayed();
	}
	function _getDefaultOptions2() {
	  return {
	    controls: true,
	    playbackRates: [0.5, 1, 1.25, 1.5, 1.75, 2],
	    language: 'video-player',
	    userActions: {
	      click: babelHelpers.classPrivateFieldLooseBase(this, _handleClick)[_handleClick].bind(this),
	      hotkeys: babelHelpers.classPrivateFieldLooseBase(this, _handleKeyDown)[_handleKeyDown].bind(this)
	    }
	  };
	}
	function _hideAudioControls2() {
	  this.vjsPlayer.removeChild('BigPlayButton');
	  this.vjsPlayer.removeChild('TextTrackSettings');
	  this.vjsPlayer.removeChild('PosterImage');
	  this.vjsPlayer.controlBar.removeChild('FullscreenToggle');
	  this.vjsPlayer.controlBar.removeChild('PictureInPictureToggle');
	  this.vjsPlayer.controlBar.removeChild('ChaptersButton');
	  this.vjsPlayer.controlBar.removeChild('DescriptionsButton');
	  if (this.skin === 'vjs-audio-wave-skin' || this.skin === 'vjs-viewer-audio-player-skin') {
	    this.vjsPlayer.removeChild('VolumePanel');
	    this.vjsPlayer.controlBar.removeChild('VolumePanel');
	    this.vjsPlayer.controlBar.removeChild('CurrentTimeDisplay');
	    this.vjsPlayer.controlBar.removeChild('PlaybackRateMenuButton');
	  }
	}
	function _handlePlayOnce2() {
	  if (this.playbackRate !== 1) {
	    this.vjsPlayer.playbackRate(this.playbackRate);
	  }
	  if (this.volume) {
	    this.vjsPlayer.volume(this.volume);
	  }
	  if (this.startTime > 0) {
	    try {
	      this.vjsPlayer.currentTime(this.startTime);
	    } catch {
	      // Fail silently
	    }
	  }
	  this.vjsPlayer.on('volumechange', () => {
	    this.active = true;
	  });
	}
	function _handleClick2(event) {
	  this.toggle();
	  event.preventDefault();
	  event.stopPropagation();
	}
	function _handleKeyDown2(event) {
	  switch (event.code) {
	    case 'KeyK':
	    case 'Space':
	      {
	        this.toggle();
	        break;
	      }
	    case 'KeyF':
	      {
	        if (!this.isAudio) {
	          if (this.vjsPlayer.isFullscreen()) {
	            this.vjsPlayer.exitFullscreen();
	          } else {
	            this.vjsPlayer.requestFullscreen();
	          }
	        }
	        break;
	      }
	    case 'KeyJ':
	      {
	        this.moveBackward(10);
	        break;
	      }
	    case 'KeyL':
	      {
	        this.moveForward(10);
	        break;
	      }
	    case 'ArrowLeft':
	      {
	        this.moveBackward(5);
	        break;
	      }
	    case 'ArrowRight':
	      {
	        this.moveForward(5);
	        break;
	      }
	    case 'KeyM':
	      {
	        if (this.isMuted()) {
	          this.mute(false);
	        } else {
	          this.mute(true);
	        }
	        break;
	      }
	    case 'Comma':
	      {
	        this.decreasePlaybackRate();
	        break;
	      }
	    case 'Period':
	      {
	        this.increasePlaybackRate();
	        break;
	      }
	    default:

	  }
	  event.preventDefault();
	  event.stopPropagation();
	  babelHelpers.classPrivateFieldLooseBase(this, _fireEvent)[_fireEvent]('onKeyDown');
	}
	function _fireEvent2(eventName) {
	  if (main_core.Type.isStringFilled(eventName)) {
	    const fullName = `Player:${eventName}`;
	    main_core.Event.EventEmitter.emit(this, fullName, new main_core_events.BaseEvent({
	      compatData: [this, fullName]
	    }));
	  }
	}
	function _proxyEvents2() {
	  this.vjsPlayer.on('play', () => {
	    babelHelpers.classPrivateFieldLooseBase(this, _fireEvent)[_fireEvent]('onPlay');
	    this.hasStarted = true;
	  });
	  this.vjsPlayer.on('pause', () => {
	    babelHelpers.classPrivateFieldLooseBase(this, _fireEvent)[_fireEvent]('onPause');
	  });
	  this.vjsPlayer.on('click', () => {
	    this.active = true;
	    babelHelpers.classPrivateFieldLooseBase(this, _fireEvent)[_fireEvent]('onClick');
	  });
	  this.vjsPlayer.on('ended', () => {
	    babelHelpers.classPrivateFieldLooseBase(this, _fireEvent)[_fireEvent]('onEnded');
	  });
	  this.vjsPlayer.on('error', () => {
	    babelHelpers.classPrivateFieldLooseBase(this, _fireEvent)[_fireEvent]('onError');
	  });
	}

	// compatibility
	const filemanNS = main_core.Reflection.namespace('BX.Fileman');
	filemanNS.Player = Player;
	filemanNS.PlayerManager = PlayerManager;

	exports.Player = Player;
	exports.PlayerManager = PlayerManager;

}((this.BX.UI.VideoPlayer = this.BX.UI.VideoPlayer || {}),window,BX,BX.Event));
//# sourceMappingURL=video-player.bundle.js.map
