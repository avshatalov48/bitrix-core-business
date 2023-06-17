this.BX = this.BX || {};
this.BX.Messenger = this.BX.Messenger || {};
this.BX.Messenger.v2 = this.BX.Messenger.v2 || {};
(function (exports,im_v2_lib_logger,im_v2_lib_draft,im_v2_lib_localStorage,im_v2_provider_service,im_v2_lib_soundNotification,rest_client,im_v2_application_core,main_core_events,im_v2_lib_smileManager,im_v2_lib_utils,im_v2_lib_parser,ui_vue3_directives_hint,im_v2_lib_entityCreator,im_v2_const,main_core,im_v2_lib_market,im_v2_component_elements) {
	'use strict';

	class ResizeManager extends main_core_events.EventEmitter {
	  constructor() {
	    super();
	    this.isDragging = false;
	    this.setEventNamespace(ResizeManager.eventNamespace);
	  }
	  onResizeStart(event, currentHeight) {
	    if (this.isDragging) {
	      return;
	    }
	    this.isDragging = true;
	    this.resizeCursorStartPoint = event.clientY;
	    this.resizeHeightStartPoint = currentHeight;
	    this.addResizeEvents();
	  }
	  onResizeContinue(event) {
	    if (!this.isDragging) {
	      return;
	    }
	    this.resizeCursorControlPoint = event.clientY;
	    const maxPoint = Math.min(this.resizeHeightStartPoint + this.resizeCursorStartPoint - this.resizeCursorControlPoint, ResizeManager.maxHeight);
	    const newHeight = Math.max(maxPoint, ResizeManager.minHeight);
	    this.emit(ResizeManager.events.onHeightChange, {
	      newHeight: newHeight
	    });
	  }
	  onResizeStop() {
	    if (!this.isDragging) {
	      return;
	    }
	    this.isDragging = false;
	    this.removeResizeEvents();
	    this.emit(ResizeManager.events.onResizeStop);
	  }
	  addResizeEvents() {
	    this.onContinueDragHandler = this.onResizeContinue.bind(this);
	    this.onStopDragHandler = this.onResizeStop.bind(this);
	    document.addEventListener('mousemove', this.onContinueDragHandler);
	    document.addEventListener('touchmove', this.onContinueDragHandler);
	    document.addEventListener('touchend', this.onStopDragHandler);
	    document.addEventListener('mouseup', this.onStopDragHandler);
	    document.addEventListener('mouseleave', this.onStopDragHandler);
	  }
	  removeResizeEvents() {
	    document.removeEventListener('mousemove', this.onContinueDragHandler);
	    document.removeEventListener('touchmove', this.onContinueDragHandler);
	    document.removeEventListener('touchend', this.onStopDragHandler);
	    document.removeEventListener('mouseup', this.onStopDragHandler);
	    document.removeEventListener('mouseleave', this.onStopDragHandler);
	  }
	  destroy() {
	    this.removeResizeEvents();
	  }
	}
	ResizeManager.eventNamespace = 'BX.Messenger.v2.Textarea.ResizeManager';
	ResizeManager.minHeight = 22;
	ResizeManager.maxHeight = 400;
	ResizeManager.events = {
	  onHeightChange: 'onHeightChange',
	  onResizeStop: 'onResizeStop'
	};

	const TYPING_DURATION = 15000;
	const TYPING_REQUEST_TIMEOUT = 5000;
	var _dialogId = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("dialogId");
	var _restClient = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("restClient");
	var _isTyping = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("isTyping");
	var _typingTimeout = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("typingTimeout");
	var _typingRequestTimeout = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("typingRequestTimeout");
	var _isSelfChat = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("isSelfChat");
	class TypingService {
	  constructor(dialogId) {
	    Object.defineProperty(this, _isSelfChat, {
	      value: _isSelfChat2
	    });
	    Object.defineProperty(this, _dialogId, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _restClient, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _isTyping, {
	      writable: true,
	      value: false
	    });
	    Object.defineProperty(this, _typingTimeout, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _typingRequestTimeout, {
	      writable: true,
	      value: void 0
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _dialogId)[_dialogId] = dialogId;
	    babelHelpers.classPrivateFieldLooseBase(this, _restClient)[_restClient] = im_v2_application_core.Core.getRestClient();
	  }
	  startTyping() {
	    if (babelHelpers.classPrivateFieldLooseBase(this, _isTyping)[_isTyping] || babelHelpers.classPrivateFieldLooseBase(this, _isSelfChat)[_isSelfChat]()) {
	      return;
	    }
	    babelHelpers.classPrivateFieldLooseBase(this, _isTyping)[_isTyping] = true;
	    babelHelpers.classPrivateFieldLooseBase(this, _typingTimeout)[_typingTimeout] = setTimeout(() => {
	      babelHelpers.classPrivateFieldLooseBase(this, _isTyping)[_isTyping] = false;
	    }, TYPING_DURATION);
	    babelHelpers.classPrivateFieldLooseBase(this, _typingRequestTimeout)[_typingRequestTimeout] = setTimeout(() => {
	      babelHelpers.classPrivateFieldLooseBase(this, _restClient)[_restClient].callMethod(im_v2_const.RestMethod.imDialogWriting, {
	        'DIALOG_ID': babelHelpers.classPrivateFieldLooseBase(this, _dialogId)[_dialogId]
	      }).catch(error => {
	        console.error('TypingService: startTyping error', error);
	      });
	    }, TYPING_REQUEST_TIMEOUT);
	  }
	  stopTyping() {
	    clearTimeout(babelHelpers.classPrivateFieldLooseBase(this, _typingTimeout)[_typingTimeout]);
	    clearTimeout(babelHelpers.classPrivateFieldLooseBase(this, _typingRequestTimeout)[_typingRequestTimeout]);
	    babelHelpers.classPrivateFieldLooseBase(this, _isTyping)[_isTyping] = false;
	  }
	}
	function _isSelfChat2() {
	  return Number.parseInt(babelHelpers.classPrivateFieldLooseBase(this, _dialogId)[_dialogId], 10) === im_v2_application_core.Core.getUserId();
	}

	const emoji = [{
	  id: 1,
	  code: 'PEOPLE',
	  showForWindows: true,
	  emoji: [{
	    symbol: '\uD83D\uDE00'
	  }, {
	    symbol: '\uD83D\uDE03'
	  }, {
	    symbol: '\uD83D\uDE04'
	  }, {
	    symbol: '\uD83D\uDE01'
	  }, {
	    symbol: '\uD83D\uDE06'
	  }, {
	    symbol: '\uD83D\uDE05'
	  }, {
	    symbol: '\uD83D\uDE02'
	  }, {
	    symbol: '\uD83E\uDD23'
	  }, {
	    symbol: '\uD83D\uDE0A'
	  }, {
	    symbol: '\uD83D\uDE07'
	  }, {
	    symbol: '\uD83D\uDE42'
	  }, {
	    symbol: '\uD83D\uDE43'
	  }, {
	    symbol: '\uD83D\uDE09'
	  }, {
	    symbol: '\uD83D\uDE0C'
	  }, {
	    symbol: '\uD83D\uDE0D'
	  }, {
	    symbol: '\uD83E\uDD70'
	  }, {
	    symbol: '\uD83D\uDE18'
	  }, {
	    symbol: '\uD83D\uDE17'
	  }, {
	    symbol: '\uD83D\uDE19'
	  }, {
	    symbol: '\uD83D\uDE1A'
	  }, {
	    symbol: '\uD83D\uDE0B'
	  }, {
	    symbol: '\uD83D\uDE1B'
	  }, {
	    symbol: '\uD83D\uDE1D'
	  }, {
	    symbol: '\uD83D\uDE1C'
	  }, {
	    symbol: '\uD83E\uDD2A'
	  }, {
	    symbol: '\uD83E\uDD28'
	  }, {
	    symbol: '\uD83E\uDDD0'
	  }, {
	    symbol: '\uD83E\uDD13'
	  }, {
	    symbol: '\uD83D\uDE0E'
	  }, {
	    symbol: '\uD83E\uDD29'
	  }, {
	    symbol: '\uD83E\uDD73'
	  }, {
	    symbol: '\uD83D\uDE0F'
	  }, {
	    symbol: '\uD83D\uDE12'
	  }, {
	    symbol: '\uD83D\uDE1E'
	  }, {
	    symbol: '\uD83D\uDE14'
	  }, {
	    symbol: '\uD83D\uDE1F'
	  }, {
	    symbol: '\uD83D\uDE15'
	  }, {
	    symbol: '\u2639'
	  }, {
	    symbol: '\uD83D\uDE41'
	  }, {
	    symbol: '\uD83D\uDE23'
	  }, {
	    symbol: '\uD83D\uDE2B'
	  }, {
	    symbol: '\uD83D\uDE29'
	  }, {
	    symbol: '\uD83E\uDD7A'
	  }, {
	    symbol: '\uD83D\uDE22'
	  }, {
	    symbol: '\uD83D\uDE2D'
	  }, {
	    symbol: '\uD83D\uDE24'
	  }, {
	    symbol: '\uD83D\uDE20'
	  }, {
	    symbol: '\uD83D\uDE21'
	  }, {
	    symbol: '\uD83E\uDD2C'
	  }, {
	    symbol: '\uD83E\uDD2F'
	  }, {
	    symbol: '\uD83D\uDE33'
	  }, {
	    symbol: '\uD83E\uDD75'
	  }, {
	    symbol: '\uD83E\uDD76'
	  }, {
	    symbol: '\uD83D\uDE31'
	  }, {
	    symbol: '\uD83D\uDE28'
	  }, {
	    symbol: '\uD83D\uDE30'
	  }, {
	    symbol: '\uD83D\uDE25'
	  }, {
	    symbol: '\uD83D\uDE13'
	  }, {
	    symbol: '\uD83E\uDD17'
	  }, {
	    symbol: '\uD83E\uDD14'
	  }, {
	    symbol: '\uD83E\uDD2D'
	  }, {
	    symbol: '\uD83E\uDD2B'
	  }, {
	    symbol: '\uD83E\uDD25'
	  }, {
	    symbol: '\uD83D\uDE36'
	  }, {
	    symbol: '\uD83D\uDE10'
	  }, {
	    symbol: '\uD83D\uDE11'
	  }, {
	    symbol: '\uD83D\uDE2C'
	  }, {
	    symbol: '\uD83D\uDE44'
	  }, {
	    symbol: '\uD83D\uDE2F'
	  }, {
	    symbol: '\uD83D\uDE26'
	  }, {
	    symbol: '\uD83D\uDE27'
	  }, {
	    symbol: '\uD83D\uDE2E'
	  }, {
	    symbol: '\uD83D\uDE32'
	  }, {
	    symbol: '\uD83D\uDE34'
	  }, {
	    symbol: '\uD83E\uDD24'
	  }, {
	    symbol: '\uD83D\uDE2A'
	  }, {
	    symbol: '\uD83D\uDE35'
	  }, {
	    symbol: '\uD83E\uDD10'
	  }, {
	    symbol: '\uD83E\uDD74'
	  }, {
	    symbol: '\uD83E\uDD22'
	  }, {
	    symbol: '\uD83E\uDD2E'
	  }, {
	    symbol: '\uD83E\uDD27'
	  }, {
	    symbol: '\uD83D\uDE37'
	  }, {
	    symbol: '\uD83E\uDD12'
	  }, {
	    symbol: '\uD83E\uDD15'
	  }, {
	    symbol: '\uD83E\uDD11'
	  }, {
	    symbol: '\uD83E\uDD20'
	  }, {
	    symbol: '\uD83D\uDE08'
	  }, {
	    symbol: '\uD83D\uDC7F'
	  }, {
	    symbol: '\uD83D\uDC79'
	  }, {
	    symbol: '\uD83D\uDC7A'
	  }, {
	    symbol: '\uD83E\uDD21'
	  }, {
	    symbol: '\uD83D\uDCA9'
	  }, {
	    symbol: '\uD83D\uDC7B'
	  }, {
	    symbol: '\u2620'
	  }, {
	    symbol: '\uD83D\uDC80'
	  }, {
	    symbol: '\uD83D\uDC7D'
	  }, {
	    symbol: '\uD83D\uDC7E'
	  }, {
	    symbol: '\uD83E\uDD16'
	  }, {
	    symbol: '\uD83C\uDF83'
	  }, {
	    symbol: '\uD83D\uDE3A'
	  }, {
	    symbol: '\uD83D\uDE38'
	  }, {
	    symbol: '\uD83D\uDE39'
	  }, {
	    symbol: '\uD83D\uDE3B'
	  }, {
	    symbol: '\uD83D\uDE3C'
	  }, {
	    symbol: '\uD83D\uDE3D'
	  }, {
	    symbol: '\uD83D\uDE40'
	  }, {
	    symbol: '\uD83D\uDE3F'
	  }, {
	    symbol: '\uD83D\uDE3E'
	  }, {
	    symbol: '\uD83E\uDD32'
	  }, {
	    symbol: '\uD83D\uDC50'
	  }, {
	    symbol: '\uD83D\uDE4C'
	  }, {
	    symbol: '\uD83D\uDC4F'
	  }, {
	    symbol: '\uD83E\uDD1D'
	  }, {
	    symbol: '\uD83D\uDC4D'
	  }, {
	    symbol: '\uD83D\uDC4E'
	  }, {
	    symbol: '\uD83D\uDC4A'
	  }, {
	    symbol: '\u270A'
	  }, {
	    symbol: '\uD83E\uDD1B'
	  }, {
	    symbol: '\uD83E\uDD1C'
	  }, {
	    symbol: '\uD83E\uDD1E'
	  }, {
	    symbol: '\u270C'
	  }, {
	    symbol: '\uD83E\uDD1F'
	  }, {
	    symbol: '\uD83E\uDD18'
	  }, {
	    symbol: '\uD83D\uDC4C'
	  }, {
	    symbol: '\uD83D\uDC48'
	  }, {
	    symbol: '\uD83D\uDC49'
	  }, {
	    symbol: '\uD83D\uDC46'
	  }, {
	    symbol: '\uD83D\uDC47'
	  }, {
	    symbol: '\uD83E\uDD1A'
	  }, {
	    symbol: '\uD83D\uDD90'
	  }, {
	    symbol: '\uD83D\uDD96'
	  }, {
	    symbol: '\uD83D\uDC4B'
	  }, {
	    symbol: '\uD83E\uDD19'
	  }, {
	    symbol: '\uD83D\uDCAA'
	  }, {
	    symbol: '\uD83D\uDD95'
	  }, {
	    symbol: '\u270D'
	  }, {
	    symbol: '\uD83D\uDE4F'
	  }, {
	    symbol: '\uD83E\uDDB6'
	  }, {
	    symbol: '\uD83E\uDDB5'
	  }, {
	    symbol: '\uD83D\uDC84'
	  }, {
	    symbol: '\uD83D\uDC8B'
	  }, {
	    symbol: '\uD83D\uDC44'
	  }, {
	    symbol: '\uD83E\uDDB7'
	  }, {
	    symbol: '\uD83D\uDC45'
	  }, {
	    symbol: '\uD83D\uDC43'
	  }, {
	    symbol: '\uD83D\uDC63'
	  }, {
	    symbol: '\uD83D\uDC41'
	  }, {
	    symbol: '\uD83D\uDC40'
	  }, {
	    symbol: '\uD83E\uDDE0'
	  }, {
	    symbol: '\uD83D\uDDE3'
	  }, {
	    symbol: '\uD83D\uDC64'
	  }, {
	    symbol: '\uD83D\uDC65'
	  }, {
	    symbol: '\uD83D\uDC76'
	  }, {
	    symbol: '\uD83D\uDC67'
	  }, {
	    symbol: '\uD83E\uDDD2'
	  }, {
	    symbol: '\uD83D\uDC66'
	  }, {
	    symbol: '\uD83D\uDC69'
	  }, {
	    symbol: '\uD83E\uDDD1'
	  }, {
	    symbol: '\uD83D\uDC68'
	  }, {
	    symbol: '\uD83D\uDC71'
	  }, {
	    symbol: '\uD83E\uDDD4'
	  }, {
	    symbol: '\uD83D\uDC75'
	  }, {
	    symbol: '\uD83E\uDDD3'
	  }, {
	    symbol: '\uD83D\uDC74'
	  }, {
	    symbol: '\uD83D\uDC72'
	  }, {
	    symbol: '\uD83D\uDC73'
	  }, {
	    symbol: '\uD83E\uDDD5'
	  }, {
	    symbol: '\uD83D\uDC6E'
	  }, {
	    symbol: '\uD83D\uDC77'
	  }, {
	    symbol: '\uD83D\uDC82'
	  }, {
	    symbol: '\uD83D\uDD75'
	  }, {
	    symbol: '\u2695'
	  }, {
	    symbol: '\uD83C\uDF3E'
	  }, {
	    symbol: '\uD83C\uDF73'
	  }, {
	    symbol: '\uD83C\uDF93'
	  }, {
	    symbol: '\uD83C\uDFA4'
	  }, {
	    symbol: '\uD83C\uDFEB'
	  }, {
	    symbol: '\uD83C\uDFED'
	  }, {
	    symbol: '\uD83D\uDCBB'
	  }, {
	    symbol: '\uD83D\uDCBC'
	  }, {
	    symbol: '\uD83D\uDC69'
	  }, {
	    symbol: '\u2764'
	  }, {
	    symbol: '\uD83D\uDD27'
	  }, {
	    symbol: '\uD83D\uDD2C'
	  }, {
	    symbol: '\uD83C\uDFA8'
	  }, {
	    symbol: '\uD83D\uDE92'
	  }, {
	    symbol: '\uD83D\uDE80'
	  }, {
	    symbol: '\uD83D\uDC70'
	  }, {
	    symbol: '\uD83E\uDD35'
	  }, {
	    symbol: '\uD83D\uDC78'
	  }, {
	    symbol: '\uD83E\uDD34'
	  }, {
	    symbol: '\uD83E\uDDB8'
	  }, {
	    symbol: '\uD83E\uDDB9'
	  }, {
	    symbol: '\uD83E\uDD36'
	  }, {
	    symbol: '\uD83C\uDF85'
	  }, {
	    symbol: '\uD83E\uDDD9'
	  }, {
	    symbol: '\uD83E\uDDDD'
	  }, {
	    symbol: '\uD83E\uDDDB'
	  }, {
	    symbol: '\uD83E\uDDDF'
	  }, {
	    symbol: '\uD83E\uDDDE'
	  }, {
	    symbol: '\uD83E\uDDDC'
	  }, {
	    symbol: '\uD83E\uDDDA'
	  }, {
	    symbol: '\uD83D\uDC7C'
	  }, {
	    symbol: '\uD83E\uDD30'
	  }, {
	    symbol: '\uD83E\uDD31'
	  }, {
	    symbol: '\uD83D\uDE47'
	  }, {
	    symbol: '\uD83D\uDC81'
	  }, {
	    symbol: '\uD83D\uDE45'
	  }, {
	    symbol: '\uD83D\uDE46'
	  }, {
	    symbol: '\uD83D\uDE4B'
	  }, {
	    symbol: '\uD83E\uDD26'
	  }, {
	    symbol: '\uD83E\uDD37'
	  }, {
	    symbol: '\uD83D\uDE4E'
	  }, {
	    symbol: '\uD83D\uDE4D'
	  }, {
	    symbol: '\uD83D\uDC87'
	  }, {
	    symbol: '\uD83D\uDC86'
	  }, {
	    symbol: '\uD83E\uDDD6'
	  }, {
	    symbol: '\uD83D\uDC85'
	  }, {
	    symbol: '\uD83E\uDD33'
	  }, {
	    symbol: '\uD83D\uDC83'
	  }, {
	    symbol: '\uD83D\uDD7A'
	  }, {
	    symbol: '\uD83D\uDC6F'
	  }, {
	    symbol: '\uD83D\uDD74'
	  }, {
	    symbol: '\uD83D\uDEB6'
	  }, {
	    symbol: '\uD83C\uDFC3'
	  }, {
	    symbol: '\uD83D\uDC6B'
	  }, {
	    symbol: '\uD83D\uDC6D'
	  }, {
	    symbol: '\uD83D\uDC6C'
	  }, {
	    symbol: '\uD83D\uDC91'
	  }, {
	    symbol: '\uD83D\uDC8F'
	  }, {
	    symbol: '\uD83D\uDC6A'
	  }, {
	    symbol: '\uD83E\uDDF6'
	  }, {
	    symbol: '\uD83E\uDDF5'
	  }, {
	    symbol: '\uD83E\uDDE5'
	  }, {
	    symbol: '\uD83E\uDD7C'
	  }, {
	    symbol: '\uD83D\uDC5A'
	  }, {
	    symbol: '\uD83D\uDC55'
	  }, {
	    symbol: '\uD83D\uDC56'
	  }, {
	    symbol: '\uD83D\uDC54'
	  }, {
	    symbol: '\uD83D\uDC57'
	  }, {
	    symbol: '\uD83D\uDC59'
	  }, {
	    symbol: '\uD83D\uDC58'
	  }, {
	    symbol: '\uD83E\uDD7F'
	  }, {
	    symbol: '\uD83D\uDC60'
	  }, {
	    symbol: '\uD83D\uDC61'
	  }, {
	    symbol: '\uD83D\uDC62'
	  }, {
	    symbol: '\uD83D\uDC5E'
	  }, {
	    symbol: '\uD83D\uDC5F'
	  }, {
	    symbol: '\uD83E\uDD7E'
	  }, {
	    symbol: '\uD83E\uDDE6'
	  }, {
	    symbol: '\uD83E\uDDE4'
	  }, {
	    symbol: '\uD83E\uDDE3'
	  }, {
	    symbol: '\uD83C\uDFA9'
	  }, {
	    symbol: '\uD83E\uDDE2'
	  }, {
	    symbol: '\uD83D\uDC52'
	  }, {
	    symbol: '\uD83C\uDF93'
	  }, {
	    symbol: '\u26D1'
	  }, {
	    symbol: '\uD83D\uDC51'
	  }, {
	    symbol: '\uD83D\uDC8D'
	  }, {
	    symbol: '\uD83D\uDC5D'
	  }, {
	    symbol: '\uD83D\uDC5B'
	  }, {
	    symbol: '\uD83D\uDC5C'
	  }, {
	    symbol: '\uD83D\uDCBC'
	  }, {
	    symbol: '\uD83C\uDF92'
	  }, {
	    symbol: '\uD83E\uDDF3'
	  }, {
	    symbol: '\uD83D\uDC53'
	  }, {
	    symbol: '\uD83D\uDD76'
	  }, {
	    symbol: '\uD83E\uDD7D'
	  }, {
	    symbol: '\uD83C\uDF02'
	  }]
	}, {
	  id: 2,
	  code: 'ANIMALS',
	  showForWindows: true,
	  emoji: [{
	    symbol: '\uD83D\uDC36'
	  }, {
	    symbol: '\uD83D\uDC31'
	  }, {
	    symbol: '\uD83D\uDC2D'
	  }, {
	    symbol: '\uD83D\uDC39'
	  }, {
	    symbol: '\uD83D\uDC30'
	  }, {
	    symbol: '\uD83E\uDD8A'
	  }, {
	    symbol: '\uD83D\uDC3B'
	  }, {
	    symbol: '\uD83D\uDC3C'
	  }, {
	    symbol: '\uD83D\uDC28'
	  }, {
	    symbol: '\uD83D\uDC2F'
	  }, {
	    symbol: '\uD83E\uDD81'
	  }, {
	    symbol: '\uD83D\uDC2E'
	  }, {
	    symbol: '\uD83D\uDC37'
	  }, {
	    symbol: '\uD83D\uDC3D'
	  }, {
	    symbol: '\uD83D\uDC38'
	  }, {
	    symbol: '\uD83D\uDC35'
	  }, {
	    symbol: '\uD83D\uDE48'
	  }, {
	    symbol: '\uD83D\uDE49'
	  }, {
	    symbol: '\uD83D\uDE4A'
	  }, {
	    symbol: '\uD83D\uDC12'
	  }, {
	    symbol: '\uD83D\uDC14'
	  }, {
	    symbol: '\uD83D\uDC27'
	  }, {
	    symbol: '\uD83D\uDC26'
	  }, {
	    symbol: '\uD83D\uDC24'
	  }, {
	    symbol: '\uD83D\uDC23'
	  }, {
	    symbol: '\uD83D\uDC25'
	  }, {
	    symbol: '\uD83E\uDD86'
	  }, {
	    symbol: '\uD83E\uDD85'
	  }, {
	    symbol: '\uD83E\uDD89'
	  }, {
	    symbol: '\uD83E\uDD87'
	  }, {
	    symbol: '\uD83D\uDC3A'
	  }, {
	    symbol: '\uD83D\uDC17'
	  }, {
	    symbol: '\uD83D\uDC34'
	  }, {
	    symbol: '\uD83E\uDD84'
	  }, {
	    symbol: '\uD83D\uDC1D'
	  }, {
	    symbol: '\uD83D\uDC1B'
	  }, {
	    symbol: '\uD83E\uDD8B'
	  }, {
	    symbol: '\uD83D\uDC0C'
	  }, {
	    symbol: '\uD83D\uDC1E'
	  }, {
	    symbol: '\uD83D\uDC1C'
	  }, {
	    symbol: '\uD83E\uDD9F'
	  }, {
	    symbol: '\uD83E\uDD97'
	  }, {
	    symbol: '\uD83D\uDD77'
	  }, {
	    symbol: '\uD83D\uDD78'
	  }, {
	    symbol: '\uD83E\uDD82'
	  }, {
	    symbol: '\uD83D\uDC22'
	  }, {
	    symbol: '\uD83D\uDC0D'
	  }, {
	    symbol: '\uD83E\uDD8E'
	  }, {
	    symbol: '\uD83E\uDD96'
	  }, {
	    symbol: '\uD83E\uDD95'
	  }, {
	    symbol: '\uD83D\uDC19'
	  }, {
	    symbol: '\uD83E\uDD91'
	  }, {
	    symbol: '\uD83E\uDD90'
	  }, {
	    symbol: '\uD83E\uDD9E'
	  }, {
	    symbol: '\uD83E\uDD80'
	  }, {
	    symbol: '\uD83D\uDC21'
	  }, {
	    symbol: '\uD83D\uDC20'
	  }, {
	    symbol: '\uD83D\uDC1F'
	  }, {
	    symbol: '\uD83D\uDC2C'
	  }, {
	    symbol: '\uD83D\uDC33'
	  }, {
	    symbol: '\uD83D\uDC0B'
	  }, {
	    symbol: '\uD83E\uDD88'
	  }, {
	    symbol: '\uD83D\uDC0A'
	  }, {
	    symbol: '\uD83D\uDC05'
	  }, {
	    symbol: '\uD83D\uDC06'
	  }, {
	    symbol: '\uD83E\uDD93'
	  }, {
	    symbol: '\uD83E\uDD8D'
	  }, {
	    symbol: '\uD83D\uDC18'
	  }, {
	    symbol: '\uD83E\uDD9B'
	  }, {
	    symbol: '\uD83E\uDD8F'
	  }, {
	    symbol: '\uD83D\uDC2A'
	  }, {
	    symbol: '\uD83D\uDC2B'
	  }, {
	    symbol: '\uD83E\uDD92'
	  }, {
	    symbol: '\uD83E\uDD98'
	  }, {
	    symbol: '\uD83D\uDC03'
	  }, {
	    symbol: '\uD83D\uDC02'
	  }, {
	    symbol: '\uD83D\uDC04'
	  }, {
	    symbol: '\uD83D\uDC0E'
	  }, {
	    symbol: '\uD83D\uDC16'
	  }, {
	    symbol: '\uD83D\uDC0F'
	  }, {
	    symbol: '\uD83D\uDC11'
	  }, {
	    symbol: '\uD83E\uDD99'
	  }, {
	    symbol: '\uD83D\uDC10'
	  }, {
	    symbol: '\uD83E\uDD8C'
	  }, {
	    symbol: '\uD83D\uDC15'
	  }, {
	    symbol: '\uD83D\uDC29'
	  }, {
	    symbol: '\uD83D\uDC08'
	  }, {
	    symbol: '\uD83D\uDC13'
	  }, {
	    symbol: '\uD83E\uDD83'
	  }, {
	    symbol: '\uD83E\uDD9A'
	  }, {
	    symbol: '\uD83E\uDD9C'
	  }, {
	    symbol: '\uD83E\uDDA2'
	  }, {
	    symbol: '\uD83D\uDD4A'
	  }, {
	    symbol: '\uD83D\uDC07'
	  }, {
	    symbol: '\uD83E\uDD9D'
	  }, {
	    symbol: '\uD83E\uDDA1'
	  }, {
	    symbol: '\uD83D\uDC01'
	  }, {
	    symbol: '\uD83D\uDC00'
	  }, {
	    symbol: '\uD83D\uDC3F'
	  }, {
	    symbol: '\uD83E\uDD94'
	  }, {
	    symbol: '\uD83D\uDC3E'
	  }, {
	    symbol: '\uD83D\uDC09'
	  }, {
	    symbol: '\uD83D\uDC32'
	  }, {
	    symbol: '\uD83C\uDF35'
	  }, {
	    symbol: '\uD83C\uDF84'
	  }, {
	    symbol: '\uD83C\uDF32'
	  }, {
	    symbol: '\uD83C\uDF33'
	  }, {
	    symbol: '\uD83C\uDF34'
	  }, {
	    symbol: '\uD83C\uDF31'
	  }, {
	    symbol: '\uD83C\uDF3F'
	  }, {
	    symbol: '\u2618'
	  }, {
	    symbol: '\uD83C\uDF40'
	  }, {
	    symbol: '\uD83C\uDF8D'
	  }, {
	    symbol: '\uD83C\uDF8B'
	  }, {
	    symbol: '\uD83C\uDF43'
	  }, {
	    symbol: '\uD83C\uDF42'
	  }, {
	    symbol: '\uD83C\uDF41'
	  }, {
	    symbol: '\uD83C\uDF44'
	  }, {
	    symbol: '\uD83D\uDC1A'
	  }, {
	    symbol: '\uD83C\uDF3E'
	  }, {
	    symbol: '\uD83D\uDC90'
	  }, {
	    symbol: '\uD83C\uDF37'
	  }, {
	    symbol: '\uD83C\uDF39'
	  }, {
	    symbol: '\uD83E\uDD40'
	  }, {
	    symbol: '\uD83C\uDF3A'
	  }, {
	    symbol: '\uD83C\uDF38'
	  }, {
	    symbol: '\uD83C\uDF3C'
	  }, {
	    symbol: '\uD83C\uDF3B'
	  }, {
	    symbol: '\uD83C\uDF1E'
	  }, {
	    symbol: '\uD83C\uDF1D'
	  }, {
	    symbol: '\uD83C\uDF1B'
	  }, {
	    symbol: '\uD83C\uDF1C'
	  }, {
	    symbol: '\uD83C\uDF1A'
	  }, {
	    symbol: '\uD83C\uDF15'
	  }, {
	    symbol: '\uD83C\uDF16'
	  }, {
	    symbol: '\uD83C\uDF17'
	  }, {
	    symbol: '\uD83C\uDF18'
	  }, {
	    symbol: '\uD83C\uDF11'
	  }, {
	    symbol: '\uD83C\uDF12'
	  }, {
	    symbol: '\uD83C\uDF13'
	  }, {
	    symbol: '\uD83C\uDF14'
	  }, {
	    symbol: '\uD83C\uDF19'
	  }, {
	    symbol: '\uD83C\uDF0E'
	  }, {
	    symbol: '\uD83C\uDF0D'
	  }, {
	    symbol: '\uD83C\uDF0F'
	  }, {
	    symbol: '\uD83D\uDCAB'
	  }, {
	    symbol: '\u2B50'
	  }, {
	    symbol: '\uD83C\uDF1F'
	  }, {
	    symbol: '\u2728'
	  }, {
	    symbol: '\u26A1'
	  }, {
	    symbol: '\u2604'
	  }, {
	    symbol: '\uD83D\uDCA5'
	  }, {
	    symbol: '\uD83D\uDD25'
	  }, {
	    symbol: '\uD83C\uDF2A'
	  }, {
	    symbol: '\uD83C\uDF08'
	  }, {
	    symbol: '\u2600'
	  }, {
	    symbol: '\uD83C\uDF24'
	  }, {
	    symbol: '\uD83C\uDF25'
	  }, {
	    symbol: '\uD83C\uDF26'
	  }, {
	    symbol: '\uD83C\uDF27'
	  }, {
	    symbol: '\uD83C\uDF29'
	  }, {
	    symbol: '\uD83C\uDF28'
	  }, {
	    symbol: '\u2744'
	  }, {
	    symbol: '\u2603'
	  }, {
	    symbol: '\u26C4'
	  }, {
	    symbol: '\uD83C\uDF2C'
	  }, {
	    symbol: '\uD83D\uDCA8'
	  }, {
	    symbol: '\uD83D\uDCA7'
	  }, {
	    symbol: '\uD83D\uDCA6'
	  }, {
	    symbol: '\u2614'
	  }, {
	    symbol: '\u2602'
	  }, {
	    symbol: '\uD83C\uDF0A'
	  }, {
	    symbol: '\uD83C\uDF2B'
	  }]
	}, {
	  id: 3,
	  code: 'FOOD',
	  showForWindows: true,
	  emoji: [{
	    symbol: '\uD83C\uDF4F'
	  }, {
	    symbol: '\uD83C\uDF4E'
	  }, {
	    symbol: '\uD83C\uDF50'
	  }, {
	    symbol: '\uD83C\uDF4A'
	  }, {
	    symbol: '\uD83C\uDF4B'
	  }, {
	    symbol: '\uD83C\uDF4C'
	  }, {
	    symbol: '\uD83C\uDF49'
	  }, {
	    symbol: '\uD83C\uDF47'
	  }, {
	    symbol: '\uD83C\uDF53'
	  }, {
	    symbol: '\uD83C\uDF48'
	  }, {
	    symbol: '\uD83C\uDF52'
	  }, {
	    symbol: '\uD83C\uDF51'
	  }, {
	    symbol: '\uD83E\uDD6D'
	  }, {
	    symbol: '\uD83C\uDF4D'
	  }, {
	    symbol: '\uD83E\uDD65'
	  }, {
	    symbol: '\uD83E\uDD5D'
	  }, {
	    symbol: '\uD83C\uDF45'
	  }, {
	    symbol: '\uD83C\uDF46'
	  }, {
	    symbol: '\uD83E\uDD51'
	  }, {
	    symbol: '\uD83E\uDD66'
	  }, {
	    symbol: '\uD83E\uDD6C'
	  }, {
	    symbol: '\uD83E\uDD52'
	  }, {
	    symbol: '\uD83C\uDF36'
	  }, {
	    symbol: '\uD83C\uDF3D'
	  }, {
	    symbol: '\uD83E\uDD55'
	  }, {
	    symbol: '\uD83E\uDD54'
	  }, {
	    symbol: '\uD83C\uDF60'
	  }, {
	    symbol: '\uD83E\uDD50'
	  }, {
	    symbol: '\uD83E\uDD6F'
	  }, {
	    symbol: '\uD83C\uDF5E'
	  }, {
	    symbol: '\uD83E\uDD56'
	  }, {
	    symbol: '\uD83E\uDD68'
	  }, {
	    symbol: '\uD83E\uDDC0'
	  }, {
	    symbol: '\uD83E\uDD5A'
	  }, {
	    symbol: '\uD83C\uDF73'
	  }, {
	    symbol: '\uD83E\uDD5E'
	  }, {
	    symbol: '\uD83E\uDD53'
	  }, {
	    symbol: '\uD83E\uDD69'
	  }, {
	    symbol: '\uD83C\uDF57'
	  }, {
	    symbol: '\uD83C\uDF56'
	  }, {
	    symbol: '\uD83E\uDDB4'
	  }, {
	    symbol: '\uD83C\uDF2D'
	  }, {
	    symbol: '\uD83C\uDF54'
	  }, {
	    symbol: '\uD83C\uDF5F'
	  }, {
	    symbol: '\uD83C\uDF55'
	  }, {
	    symbol: '\uD83E\uDD6A'
	  }, {
	    symbol: '\uD83E\uDD59'
	  }, {
	    symbol: '\uD83C\uDF2E'
	  }, {
	    symbol: '\uD83C\uDF2F'
	  }, {
	    symbol: '\uD83E\uDD57'
	  }, {
	    symbol: '\uD83E\uDD58'
	  }, {
	    symbol: '\uD83E\uDD6B'
	  }, {
	    symbol: '\uD83C\uDF5D'
	  }, {
	    symbol: '\uD83C\uDF5C'
	  }, {
	    symbol: '\uD83C\uDF72'
	  }, {
	    symbol: '\uD83C\uDF5B'
	  }, {
	    symbol: '\uD83C\uDF63'
	  }, {
	    symbol: '\uD83C\uDF71'
	  }, {
	    symbol: '\uD83E\uDD5F'
	  }, {
	    symbol: '\uD83C\uDF64'
	  }, {
	    symbol: '\uD83C\uDF59'
	  }, {
	    symbol: '\uD83C\uDF5A'
	  }, {
	    symbol: '\uD83C\uDF58'
	  }, {
	    symbol: '\uD83C\uDF65'
	  }, {
	    symbol: '\uD83E\uDD60'
	  }, {
	    symbol: '\uD83E\uDD6E'
	  }, {
	    symbol: '\uD83C\uDF62'
	  }, {
	    symbol: '\uD83C\uDF61'
	  }, {
	    symbol: '\uD83C\uDF67'
	  }, {
	    symbol: '\uD83C\uDF68'
	  }, {
	    symbol: '\uD83C\uDF66'
	  }, {
	    symbol: '\uD83E\uDD67'
	  }, {
	    symbol: '\uD83E\uDDC1'
	  }, {
	    symbol: '\uD83C\uDF70'
	  }, {
	    symbol: '\uD83C\uDF82'
	  }, {
	    symbol: '\uD83C\uDF6E'
	  }, {
	    symbol: '\uD83C\uDF6D'
	  }, {
	    symbol: '\uD83C\uDF6C'
	  }, {
	    symbol: '\uD83C\uDF6B'
	  }, {
	    symbol: '\uD83C\uDF7F'
	  }, {
	    symbol: '\uD83C\uDF69'
	  }, {
	    symbol: '\uD83C\uDF6A'
	  }, {
	    symbol: '\uD83C\uDF30'
	  }, {
	    symbol: '\uD83E\uDD5C'
	  }, {
	    symbol: '\uD83C\uDF6F'
	  }, {
	    symbol: '\uD83E\uDD5B'
	  }, {
	    symbol: '\uD83C\uDF7C'
	  }, {
	    symbol: '\u2615'
	  }, {
	    symbol: '\uD83C\uDF75'
	  }, {
	    symbol: '\uD83E\uDD64'
	  }, {
	    symbol: '\uD83C\uDF76'
	  }, {
	    symbol: '\uD83C\uDF7A'
	  }, {
	    symbol: '\uD83C\uDF7B'
	  }, {
	    symbol: '\uD83E\uDD42'
	  }, {
	    symbol: '\uD83C\uDF77'
	  }, {
	    symbol: '\uD83E\uDD43'
	  }, {
	    symbol: '\uD83C\uDF78'
	  }, {
	    symbol: '\uD83C\uDF79'
	  }, {
	    symbol: '\uD83C\uDF7E'
	  }, {
	    symbol: '\uD83E\uDD44'
	  }, {
	    symbol: '\uD83C\uDF74'
	  }, {
	    symbol: '\uD83C\uDF7D'
	  }, {
	    symbol: '\uD83E\uDD63'
	  }, {
	    symbol: '\uD83E\uDD61'
	  }, {
	    symbol: '\uD83E\uDD62'
	  }, {
	    symbol: '\uD83E\uDDC2'
	  }]
	}, {
	  id: 4,
	  code: 'HOBBY',
	  showForWindows: true,
	  emoji: [{
	    symbol: '\u26BD'
	  }, {
	    symbol: '\uD83C\uDFC0'
	  }, {
	    symbol: '\uD83C\uDFC8'
	  }, {
	    symbol: '\u26BE'
	  }, {
	    symbol: '\uD83E\uDD4E'
	  }, {
	    symbol: '\uD83C\uDFBE'
	  }, {
	    symbol: '\uD83C\uDFD0'
	  }, {
	    symbol: '\uD83C\uDFC9'
	  }, {
	    symbol: '\uD83E\uDD4F'
	  }, {
	    symbol: '\uD83C\uDFB1'
	  }, {
	    symbol: '\uD83C\uDFD3'
	  }, {
	    symbol: '\uD83C\uDFF8'
	  }, {
	    symbol: '\uD83C\uDFD2'
	  }, {
	    symbol: '\uD83C\uDFD1'
	  }, {
	    symbol: '\uD83E\uDD4D'
	  }, {
	    symbol: '\uD83C\uDFCF'
	  }, {
	    symbol: '\uD83E\uDD45'
	  }, {
	    symbol: '\u26F3'
	  }, {
	    symbol: '\uD83C\uDFF9'
	  }, {
	    symbol: '\uD83C\uDFA3'
	  }, {
	    symbol: '\uD83E\uDD4A'
	  }, {
	    symbol: '\uD83E\uDD4B'
	  }, {
	    symbol: '\uD83C\uDFBD'
	  }, {
	    symbol: '\uD83D\uDEF9'
	  }, {
	    symbol: '\uD83D\uDEF7'
	  }, {
	    symbol: '\u26F8'
	  }, {
	    symbol: '\uD83E\uDD4C'
	  }, {
	    symbol: '\uD83C\uDFBF'
	  }, {
	    symbol: '\u26F7'
	  }, {
	    symbol: '\uD83C\uDFC2'
	  }, {
	    symbol: '\uD83C\uDFCB'
	  }, {
	    symbol: '\uD83E\uDD3C'
	  }, {
	    symbol: '\uD83E\uDD38'
	  }, {
	    symbol: '\u26F9'
	  }, {
	    symbol: '\uD83E\uDD3A'
	  }, {
	    symbol: '\uD83E\uDD3E'
	  }, {
	    symbol: '\uD83C\uDFCC'
	  }, {
	    symbol: '\uD83C\uDFC7'
	  }, {
	    symbol: '\uD83E\uDDD8'
	  }, {
	    symbol: '\uD83C\uDFC4'
	  }, {
	    symbol: '\uD83C\uDFCA'
	  }, {
	    symbol: '\uD83E\uDD3D'
	  }, {
	    symbol: '\uD83D\uDEA3'
	  }, {
	    symbol: '\uD83E\uDDD7'
	  }, {
	    symbol: '\uD83D\uDEB5'
	  }, {
	    symbol: '\uD83D\uDEB4'
	  }, {
	    symbol: '\uD83C\uDFC6'
	  }, {
	    symbol: '\uD83E\uDD47'
	  }, {
	    symbol: '\uD83E\uDD48'
	  }, {
	    symbol: '\uD83E\uDD49'
	  }, {
	    symbol: '\uD83C\uDFC5'
	  }, {
	    symbol: '\uD83C\uDF96'
	  }, {
	    symbol: '\uD83C\uDFF5'
	  }, {
	    symbol: '\uD83C\uDF97'
	  }, {
	    symbol: '\uD83C\uDFAB'
	  }, {
	    symbol: '\uD83C\uDF9F'
	  }, {
	    symbol: '\uD83C\uDFAA'
	  }, {
	    symbol: '\uD83E\uDD39'
	  }, {
	    symbol: '\uD83C\uDFAD'
	  }, {
	    symbol: '\uD83C\uDFA8'
	  }, {
	    symbol: '\uD83C\uDFAC'
	  }, {
	    symbol: '\uD83C\uDFA4'
	  }, {
	    symbol: '\uD83C\uDFA7'
	  }, {
	    symbol: '\uD83C\uDFBC'
	  }, {
	    symbol: '\uD83C\uDFB9'
	  }, {
	    symbol: '\uD83E\uDD41'
	  }, {
	    symbol: '\uD83C\uDFB7'
	  }, {
	    symbol: '\uD83C\uDFBA'
	  }, {
	    symbol: '\uD83C\uDFB8'
	  }, {
	    symbol: '\uD83C\uDFBB'
	  }, {
	    symbol: '\uD83C\uDFB2'
	  }, {
	    symbol: '\u265F'
	  }, {
	    symbol: '\uD83C\uDFAF'
	  }, {
	    symbol: '\uD83C\uDFB3'
	  }, {
	    symbol: '\uD83C\uDFAE'
	  }, {
	    symbol: '\uD83C\uDFB0'
	  }, {
	    symbol: '\uD83E\uDDE9'
	  }]
	}, {
	  id: 5,
	  code: 'TRAVEL',
	  showForWindows: true,
	  emoji: [{
	    symbol: '\uD83D\uDE97'
	  }, {
	    symbol: '\uD83D\uDE95'
	  }, {
	    symbol: '\uD83D\uDE99'
	  }, {
	    symbol: '\uD83D\uDE8C'
	  }, {
	    symbol: '\uD83D\uDE8E'
	  }, {
	    symbol: '\uD83C\uDFCE'
	  }, {
	    symbol: '\uD83D\uDE93'
	  }, {
	    symbol: '\uD83D\uDE91'
	  }, {
	    symbol: '\uD83D\uDE92'
	  }, {
	    symbol: '\uD83D\uDE90'
	  }, {
	    symbol: '\uD83D\uDE9A'
	  }, {
	    symbol: '\uD83D\uDE9B'
	  }, {
	    symbol: '\uD83D\uDE9C'
	  }, {
	    symbol: '\uD83D\uDEF4'
	  }, {
	    symbol: '\uD83D\uDEB2'
	  }, {
	    symbol: '\uD83D\uDEF5'
	  }, {
	    symbol: '\uD83C\uDFCD'
	  }, {
	    symbol: '\uD83D\uDEA8'
	  }, {
	    symbol: '\uD83D\uDE94'
	  }, {
	    symbol: '\uD83D\uDE8D'
	  }, {
	    symbol: '\uD83D\uDE98'
	  }, {
	    symbol: '\uD83D\uDE96'
	  }, {
	    symbol: '\uD83D\uDEA1'
	  }, {
	    symbol: '\uD83D\uDEA0'
	  }, {
	    symbol: '\uD83D\uDE9F'
	  }, {
	    symbol: '\uD83D\uDE83'
	  }, {
	    symbol: '\uD83D\uDE8B'
	  }, {
	    symbol: '\uD83D\uDE9E'
	  }, {
	    symbol: '\uD83D\uDE9D'
	  }, {
	    symbol: '\uD83D\uDE84'
	  }, {
	    symbol: '\uD83D\uDE85'
	  }, {
	    symbol: '\uD83D\uDE88'
	  }, {
	    symbol: '\uD83D\uDE82'
	  }, {
	    symbol: '\uD83D\uDE86'
	  }, {
	    symbol: '\uD83D\uDE87'
	  }, {
	    symbol: '\uD83D\uDE8A'
	  }, {
	    symbol: '\uD83D\uDE89'
	  }, {
	    symbol: '\u2708'
	  }, {
	    symbol: '\uD83D\uDEEB'
	  }, {
	    symbol: '\uD83D\uDEEC'
	  }, {
	    symbol: '\uD83D\uDEE9'
	  }, {
	    symbol: '\uD83D\uDCBA'
	  }, {
	    symbol: '\uD83D\uDEF0'
	  }, {
	    symbol: '\uD83D\uDE80'
	  }, {
	    symbol: '\uD83D\uDEF8'
	  }, {
	    symbol: '\uD83D\uDE81'
	  }, {
	    symbol: '\uD83D\uDEF6'
	  }, {
	    symbol: '\u26F5'
	  }, {
	    symbol: '\uD83D\uDEA4'
	  }, {
	    symbol: '\uD83D\uDEE5'
	  }, {
	    symbol: '\uD83D\uDEF3'
	  }, {
	    symbol: '\u26F4'
	  }, {
	    symbol: '\uD83D\uDEA2'
	  }, {
	    symbol: '\u2693'
	  }, {
	    symbol: '\u26FD'
	  }, {
	    symbol: '\uD83D\uDEA7'
	  }, {
	    symbol: '\uD83D\uDEA6'
	  }, {
	    symbol: '\uD83D\uDEA5'
	  }, {
	    symbol: '\uD83D\uDE8F'
	  }, {
	    symbol: '\uD83D\uDDFA'
	  }, {
	    symbol: '\uD83D\uDDFF'
	  }, {
	    symbol: '\uD83D\uDDFD'
	  }, {
	    symbol: '\uD83D\uDDFC'
	  }, {
	    symbol: '\uD83C\uDFF0'
	  }, {
	    symbol: '\uD83C\uDFEF'
	  }, {
	    symbol: '\uD83C\uDFDF'
	  }, {
	    symbol: '\uD83C\uDFA1'
	  }, {
	    symbol: '\uD83C\uDFA2'
	  }, {
	    symbol: '\uD83C\uDFA0'
	  }, {
	    symbol: '\u26F2'
	  }, {
	    symbol: '\u26F1'
	  }, {
	    symbol: '\uD83C\uDFD6'
	  }, {
	    symbol: '\uD83C\uDFDD'
	  }, {
	    symbol: '\uD83C\uDFDC'
	  }, {
	    symbol: '\uD83C\uDF0B'
	  }, {
	    symbol: '\u26F0'
	  }, {
	    symbol: '\uD83C\uDFD4'
	  }, {
	    symbol: '\uD83D\uDDFB'
	  }, {
	    symbol: '\uD83C\uDFD5'
	  }, {
	    symbol: '\u26FA'
	  }, {
	    symbol: '\uD83C\uDFE0'
	  }, {
	    symbol: '\uD83C\uDFE1'
	  }, {
	    symbol: '\uD83C\uDFD8'
	  }, {
	    symbol: '\uD83C\uDFDA'
	  }, {
	    symbol: '\uD83C\uDFD7'
	  }, {
	    symbol: '\uD83C\uDFED'
	  }, {
	    symbol: '\uD83C\uDFE2'
	  }, {
	    symbol: '\uD83C\uDFEC'
	  }, {
	    symbol: '\uD83C\uDFE3'
	  }, {
	    symbol: '\uD83C\uDFE4'
	  }, {
	    symbol: '\uD83C\uDFE5'
	  }, {
	    symbol: '\uD83C\uDFE6'
	  }, {
	    symbol: '\uD83C\uDFE8'
	  }, {
	    symbol: '\uD83C\uDFEA'
	  }, {
	    symbol: '\uD83C\uDFEB'
	  }, {
	    symbol: '\uD83C\uDFE9'
	  }, {
	    symbol: '\uD83D\uDC92'
	  }, {
	    symbol: '\uD83C\uDFDB'
	  }, {
	    symbol: '\u26EA'
	  }, {
	    symbol: '\uD83D\uDD4C'
	  }, {
	    symbol: '\uD83D\uDD4D'
	  }, {
	    symbol: '\uD83D\uDD4B'
	  }, {
	    symbol: '\u26E9'
	  }, {
	    symbol: '\uD83D\uDEE4'
	  }, {
	    symbol: '\uD83D\uDEE3'
	  }, {
	    symbol: '\uD83D\uDDFE'
	  }, {
	    symbol: '\uD83C\uDF91'
	  }, {
	    symbol: '\uD83C\uDFDE'
	  }, {
	    symbol: '\uD83C\uDF05'
	  }, {
	    symbol: '\uD83C\uDF04'
	  }, {
	    symbol: '\uD83C\uDF20'
	  }, {
	    symbol: '\uD83C\uDF87'
	  }, {
	    symbol: '\uD83C\uDF86'
	  }, {
	    symbol: '\uD83C\uDF07'
	  }, {
	    symbol: '\uD83C\uDF06'
	  }, {
	    symbol: '\uD83C\uDFD9'
	  }, {
	    symbol: '\uD83C\uDF03'
	  }, {
	    symbol: '\uD83C\uDF0C'
	  }, {
	    symbol: '\uD83C\uDF09'
	  }, {
	    symbol: '\uD83C\uDF01'
	  }]
	}, {
	  id: 6,
	  code: 'OBJECTS',
	  showForWindows: true,
	  emoji: [{
	    symbol: '\u231A'
	  }, {
	    symbol: '\uD83D\uDCF1'
	  }, {
	    symbol: '\uD83D\uDCF2'
	  }, {
	    symbol: '\uD83D\uDCBB'
	  }, {
	    symbol: '\u2328'
	  }, {
	    symbol: '\uD83D\uDDA5'
	  }, {
	    symbol: '\uD83D\uDDA8'
	  }, {
	    symbol: '\uD83D\uDDB1'
	  }, {
	    symbol: '\uD83D\uDDB2'
	  }, {
	    symbol: '\uD83D\uDD79'
	  }, {
	    symbol: '\uD83D\uDDDC'
	  }, {
	    symbol: '\uD83D\uDCBD'
	  }, {
	    symbol: '\uD83D\uDCBE'
	  }, {
	    symbol: '\uD83D\uDCBF'
	  }, {
	    symbol: '\uD83D\uDCC0'
	  }, {
	    symbol: '\uD83D\uDCFC'
	  }, {
	    symbol: '\uD83D\uDCF7'
	  }, {
	    symbol: '\uD83D\uDCF8'
	  }, {
	    symbol: '\uD83D\uDCF9'
	  }, {
	    symbol: '\uD83C\uDFA5'
	  }, {
	    symbol: '\uD83D\uDCFD'
	  }, {
	    symbol: '\uD83C\uDF9E'
	  }, {
	    symbol: '\uD83D\uDCDE'
	  }, {
	    symbol: '\u260E'
	  }, {
	    symbol: '\uD83D\uDCDF'
	  }, {
	    symbol: '\uD83D\uDCE0'
	  }, {
	    symbol: '\uD83D\uDCFA'
	  }, {
	    symbol: '\uD83D\uDCFB'
	  }, {
	    symbol: '\uD83C\uDF99'
	  }, {
	    symbol: '\uD83C\uDF9A'
	  }, {
	    symbol: '\uD83C\uDF9B'
	  }, {
	    symbol: '\uD83E\uDDED'
	  }, {
	    symbol: '\u23F1'
	  }, {
	    symbol: '\u23F2'
	  }, {
	    symbol: '\u23F0'
	  }, {
	    symbol: '\uD83D\uDD70'
	  }, {
	    symbol: '\u231B'
	  }, {
	    symbol: '\u23F3'
	  }, {
	    symbol: '\uD83D\uDCE1'
	  }, {
	    symbol: '\uD83D\uDD0B'
	  }, {
	    symbol: '\uD83D\uDD0C'
	  }, {
	    symbol: '\uD83D\uDCA1'
	  }, {
	    symbol: '\uD83D\uDD26'
	  }, {
	    symbol: '\uD83D\uDD6F'
	  }, {
	    symbol: '\uD83E\uDDEF'
	  }, {
	    symbol: '\uD83D\uDEE2'
	  }, {
	    symbol: '\uD83D\uDCB8'
	  }, {
	    symbol: '\uD83D\uDCB5'
	  }, {
	    symbol: '\uD83D\uDCB4'
	  }, {
	    symbol: '\uD83D\uDCB6'
	  }, {
	    symbol: '\uD83D\uDCB7'
	  }, {
	    symbol: '\uD83D\uDCB0'
	  }, {
	    symbol: '\uD83D\uDCB3'
	  }, {
	    symbol: '\uD83D\uDC8E'
	  }, {
	    symbol: '\u2696'
	  }, {
	    symbol: '\uD83E\uDDF0'
	  }, {
	    symbol: '\uD83D\uDD27'
	  }, {
	    symbol: '\uD83D\uDD28'
	  }, {
	    symbol: '\u2692'
	  }, {
	    symbol: '\uD83D\uDEE0'
	  }, {
	    symbol: '\u26CF'
	  }, {
	    symbol: '\uD83D\uDD29'
	  }, {
	    symbol: '\u2699'
	  }, {
	    symbol: '\uD83E\uDDF1'
	  }, {
	    symbol: '\u26D3'
	  }, {
	    symbol: '\uD83E\uDDF2'
	  }, {
	    symbol: '\uD83D\uDD2B'
	  }, {
	    symbol: '\uD83D\uDCA3'
	  }, {
	    symbol: '\uD83E\uDDE8'
	  }, {
	    symbol: '\uD83D\uDD2A'
	  }, {
	    symbol: '\uD83D\uDDE1'
	  }, {
	    symbol: '\u2694'
	  }, {
	    symbol: '\uD83D\uDEE1'
	  }, {
	    symbol: '\uD83D\uDEAC'
	  }, {
	    symbol: '\u26B0'
	  }, {
	    symbol: '\u26B1'
	  }, {
	    symbol: '\uD83C\uDFFA'
	  }, {
	    symbol: '\uD83D\uDD2E'
	  }, {
	    symbol: '\uD83D\uDCFF'
	  }, {
	    symbol: '\uD83E\uDDFF'
	  }, {
	    symbol: '\uD83D\uDC88'
	  }, {
	    symbol: '\u2697'
	  }, {
	    symbol: '\uD83D\uDD2D'
	  }, {
	    symbol: '\uD83D\uDD2C'
	  }, {
	    symbol: '\uD83D\uDD73'
	  }, {
	    symbol: '\uD83D\uDC8A'
	  }, {
	    symbol: '\uD83D\uDC89'
	  }, {
	    symbol: '\uD83E\uDDEC'
	  }, {
	    symbol: '\uD83E\uDDA0'
	  }, {
	    symbol: '\uD83E\uDDEB'
	  }, {
	    symbol: '\uD83E\uDDEA'
	  }, {
	    symbol: '\uD83C\uDF21'
	  }, {
	    symbol: '\uD83E\uDDF9'
	  }, {
	    symbol: '\uD83E\uDDFA'
	  }, {
	    symbol: '\uD83E\uDDFB'
	  }, {
	    symbol: '\uD83D\uDEBD'
	  }, {
	    symbol: '\uD83D\uDEB0'
	  }, {
	    symbol: '\uD83D\uDEBF'
	  }, {
	    symbol: '\uD83D\uDEC1'
	  }, {
	    symbol: '\uD83D\uDEC0'
	  }, {
	    symbol: '\uD83E\uDDFC'
	  }, {
	    symbol: '\uD83E\uDDFD'
	  }, {
	    symbol: '\uD83E\uDDF4'
	  }, {
	    symbol: '\uD83D\uDECE'
	  }, {
	    symbol: '\uD83D\uDD11'
	  }, {
	    symbol: '\uD83D\uDDDD'
	  }, {
	    symbol: '\uD83D\uDEAA'
	  }, {
	    symbol: '\uD83D\uDECB'
	  }, {
	    symbol: '\uD83D\uDECF'
	  }, {
	    symbol: '\uD83D\uDECC'
	  }, {
	    symbol: '\uD83E\uDDF8'
	  }, {
	    symbol: '\uD83D\uDDBC'
	  }, {
	    symbol: '\uD83D\uDECD'
	  }, {
	    symbol: '\uD83D\uDED2'
	  }, {
	    symbol: '\uD83C\uDF81'
	  }, {
	    symbol: '\uD83C\uDF88'
	  }, {
	    symbol: '\uD83C\uDF8F'
	  }, {
	    symbol: '\uD83C\uDF80'
	  }, {
	    symbol: '\uD83C\uDF8A'
	  }, {
	    symbol: '\uD83C\uDF89'
	  }, {
	    symbol: '\uD83C\uDF8E'
	  }, {
	    symbol: '\uD83C\uDFEE'
	  }, {
	    symbol: '\uD83C\uDF90'
	  }, {
	    symbol: '\uD83E\uDDE7'
	  }, {
	    symbol: '\u2709'
	  }, {
	    symbol: '\uD83D\uDCE9'
	  }, {
	    symbol: '\uD83D\uDCE8'
	  }, {
	    symbol: '\uD83D\uDCE7'
	  }, {
	    symbol: '\uD83D\uDC8C'
	  }, {
	    symbol: '\uD83D\uDCE5'
	  }, {
	    symbol: '\uD83D\uDCE4'
	  }, {
	    symbol: '\uD83D\uDCE6'
	  }, {
	    symbol: '\uD83C\uDFF7'
	  }, {
	    symbol: '\uD83D\uDCEA'
	  }, {
	    symbol: '\uD83D\uDCEB'
	  }, {
	    symbol: '\uD83D\uDCEC'
	  }, {
	    symbol: '\uD83D\uDCED'
	  }, {
	    symbol: '\uD83D\uDCEE'
	  }, {
	    symbol: '\uD83D\uDCEF'
	  }, {
	    symbol: '\uD83D\uDCDC'
	  }, {
	    symbol: '\uD83D\uDCC3'
	  }, {
	    symbol: '\uD83D\uDCC4'
	  }, {
	    symbol: '\uD83D\uDCD1'
	  }, {
	    symbol: '\uD83E\uDDFE'
	  }, {
	    symbol: '\uD83D\uDCCA'
	  }, {
	    symbol: '\uD83D\uDCC8'
	  }, {
	    symbol: '\uD83D\uDCC9'
	  }, {
	    symbol: '\uD83D\uDDD2'
	  }, {
	    symbol: '\uD83D\uDDD3'
	  }, {
	    symbol: '\uD83D\uDCC6'
	  }, {
	    symbol: '\uD83D\uDCC5'
	  }, {
	    symbol: '\uD83D\uDDD1'
	  }, {
	    symbol: '\uD83D\uDCC7'
	  }, {
	    symbol: '\uD83D\uDDC3'
	  }, {
	    symbol: '\uD83D\uDDF3'
	  }, {
	    symbol: '\uD83D\uDDC4'
	  }, {
	    symbol: '\uD83D\uDCCB'
	  }, {
	    symbol: '\uD83D\uDCC1'
	  }, {
	    symbol: '\uD83D\uDCC2'
	  }, {
	    symbol: '\uD83D\uDDC2'
	  }, {
	    symbol: '\uD83D\uDDDE'
	  }, {
	    symbol: '\uD83D\uDCF0'
	  }, {
	    symbol: '\uD83D\uDCD3'
	  }, {
	    symbol: '\uD83D\uDCD4'
	  }, {
	    symbol: '\uD83D\uDCD2'
	  }, {
	    symbol: '\uD83D\uDCD5'
	  }, {
	    symbol: '\uD83D\uDCD7'
	  }, {
	    symbol: '\uD83D\uDCD8'
	  }, {
	    symbol: '\uD83D\uDCD9'
	  }, {
	    symbol: '\uD83D\uDCDA'
	  }, {
	    symbol: '\uD83D\uDCD6'
	  }, {
	    symbol: '\uD83D\uDD16'
	  }, {
	    symbol: '\uD83E\uDDF7'
	  }, {
	    symbol: '\uD83D\uDD17'
	  }, {
	    symbol: '\uD83D\uDCCE'
	  }, {
	    symbol: '\uD83D\uDD87'
	  }, {
	    symbol: '\uD83D\uDCD0'
	  }, {
	    symbol: '\uD83D\uDCCF'
	  }, {
	    symbol: '\uD83D\uDCCC'
	  }, {
	    symbol: '\uD83D\uDCCD'
	  }, {
	    symbol: '\u2702'
	  }, {
	    symbol: '\uD83D\uDD8A'
	  }, {
	    symbol: '\uD83D\uDD8B'
	  }, {
	    symbol: '\u2712'
	  }, {
	    symbol: '\uD83D\uDD8C'
	  }, {
	    symbol: '\uD83D\uDD8D'
	  }, {
	    symbol: '\uD83D\uDCDD'
	  }, {
	    symbol: '\u270F'
	  }, {
	    symbol: '\uD83D\uDD0D'
	  }, {
	    symbol: '\uD83D\uDD0E'
	  }, {
	    symbol: '\uD83D\uDD0F'
	  }, {
	    symbol: '\uD83D\uDD10'
	  }, {
	    symbol: '\uD83D\uDD12'
	  }, {
	    symbol: '\uD83D\uDD13'
	  }]
	}, {
	  id: 7,
	  code: 'SYMBOLS',
	  showForWindows: true,
	  emoji: [{
	    symbol: '\u2764'
	  }, {
	    symbol: '\uD83E\uDDE1'
	  }, {
	    symbol: '\uD83D\uDC9B'
	  }, {
	    symbol: '\uD83D\uDC9A'
	  }, {
	    symbol: '\uD83D\uDC99'
	  }, {
	    symbol: '\uD83D\uDC9C'
	  }, {
	    symbol: '\uD83D\uDDA4'
	  }, {
	    symbol: '\uD83D\uDC94'
	  }, {
	    symbol: '\u2763'
	  }, {
	    symbol: '\uD83D\uDC95'
	  }, {
	    symbol: '\uD83D\uDC9E'
	  }, {
	    symbol: '\uD83D\uDC93'
	  }, {
	    symbol: '\uD83D\uDC97'
	  }, {
	    symbol: '\uD83D\uDC96'
	  }, {
	    symbol: '\uD83D\uDC98'
	  }, {
	    symbol: '\uD83D\uDC9D'
	  }, {
	    symbol: '\uD83D\uDC9F'
	  }, {
	    symbol: '\u262E'
	  }, {
	    symbol: '\u271D'
	  }, {
	    symbol: '\u262A'
	  }, {
	    symbol: '\uD83D\uDD49'
	  }, {
	    symbol: '\u2638'
	  }, {
	    symbol: '\u2721'
	  }, {
	    symbol: '\uD83D\uDD2F'
	  }, {
	    symbol: '\uD83D\uDD4E'
	  }, {
	    symbol: '\u262F'
	  }, {
	    symbol: '\u2626'
	  }, {
	    symbol: '\uD83D\uDED0'
	  }, {
	    symbol: '\u26CE'
	  }, {
	    symbol: '\u2648'
	  }, {
	    symbol: '\u2649'
	  }, {
	    symbol: '\u264A'
	  }, {
	    symbol: '\u264B'
	  }, {
	    symbol: '\u264C'
	  }, {
	    symbol: '\u264D'
	  }, {
	    symbol: '\u264E'
	  }, {
	    symbol: '\u264F'
	  }, {
	    symbol: '\u2650'
	  }, {
	    symbol: '\u2651'
	  }, {
	    symbol: '\u2652'
	  }, {
	    symbol: '\u2653'
	  }, {
	    symbol: '\uD83C\uDD94'
	  }, {
	    symbol: '\u269B'
	  }, {
	    symbol: '\uD83C\uDE51'
	  }, {
	    symbol: '\u2622'
	  }, {
	    symbol: '\u2623'
	  }, {
	    symbol: '\uD83D\uDCF4'
	  }, {
	    symbol: '\uD83D\uDCF3'
	  }, {
	    symbol: '\uD83C\uDE36'
	  }, {
	    symbol: '\uD83C\uDE1A'
	  }, {
	    symbol: '\uD83C\uDE38'
	  }, {
	    symbol: '\uD83C\uDE3A'
	  }, {
	    symbol: '\uD83C\uDE37'
	  }, {
	    symbol: '\u2734'
	  }, {
	    symbol: '\uD83C\uDD9A'
	  }, {
	    symbol: '\uD83D\uDCAE'
	  }, {
	    symbol: '\uD83C\uDE50'
	  }, {
	    symbol: '\u3299'
	  }, {
	    symbol: '\u3297'
	  }, {
	    symbol: '\uD83C\uDE34'
	  }, {
	    symbol: '\uD83C\uDE35'
	  }, {
	    symbol: '\uD83C\uDE39'
	  }, {
	    symbol: '\uD83C\uDE32'
	  }, {
	    symbol: '\uD83C\uDD70'
	  }, {
	    symbol: '\uD83C\uDD71'
	  }, {
	    symbol: '\uD83C\uDD8E'
	  }, {
	    symbol: '\uD83C\uDD91'
	  }, {
	    symbol: '\uD83C\uDD7E'
	  }, {
	    symbol: '\uD83C\uDD98'
	  }, {
	    symbol: '\u274C'
	  }, {
	    symbol: '\u2B55'
	  }, {
	    symbol: '\uD83D\uDED1'
	  }, {
	    symbol: '\u26D4'
	  }, {
	    symbol: '\uD83D\uDCDB'
	  }, {
	    symbol: '\uD83D\uDEAB'
	  }, {
	    symbol: '\uD83D\uDCAF'
	  }, {
	    symbol: '\uD83D\uDCA2'
	  }, {
	    symbol: '\u2668'
	  }, {
	    symbol: '\uD83D\uDEB7'
	  }, {
	    symbol: '\uD83D\uDEAF'
	  }, {
	    symbol: '\uD83D\uDEB3'
	  }, {
	    symbol: '\uD83D\uDEB1'
	  }, {
	    symbol: '\uD83D\uDD1E'
	  }, {
	    symbol: '\uD83D\uDCF5'
	  }, {
	    symbol: '\uD83D\uDEAD'
	  }, {
	    symbol: '\u2757'
	  }, {
	    symbol: '\u2755'
	  }, {
	    symbol: '\u2753'
	  }, {
	    symbol: '\u2754'
	  }, {
	    symbol: '\u203C'
	  }, {
	    symbol: '\u2049'
	  }, {
	    symbol: '\uD83D\uDD05'
	  }, {
	    symbol: '\uD83D\uDD06'
	  }, {
	    symbol: '\u303D'
	  }, {
	    symbol: '\u26A0'
	  }, {
	    symbol: '\uD83D\uDEB8'
	  }, {
	    symbol: '\uD83D\uDD31'
	  }, {
	    symbol: '\u269C'
	  }, {
	    symbol: '\uD83D\uDD30'
	  }, {
	    symbol: '\u267B'
	  }, {
	    symbol: '\u2705'
	  }, {
	    symbol: '\uD83C\uDE2F'
	  }, {
	    symbol: '\uD83D\uDCB9'
	  }, {
	    symbol: '\u2747'
	  }, {
	    symbol: '\u2733'
	  }, {
	    symbol: '\u274E'
	  }, {
	    symbol: '\uD83C\uDF10'
	  }, {
	    symbol: '\uD83D\uDCA0'
	  }, {
	    symbol: '\u24C2'
	  }, {
	    symbol: '\uD83C\uDF00'
	  }, {
	    symbol: '\uD83D\uDCA4'
	  }, {
	    symbol: '\uD83C\uDFE7'
	  }, {
	    symbol: '\uD83D\uDEBE'
	  }, {
	    symbol: '\u267F'
	  }, {
	    symbol: '\uD83C\uDD7F'
	  }, {
	    symbol: '\uD83C\uDE33'
	  }, {
	    symbol: '\uD83C\uDE02'
	  }, {
	    symbol: '\uD83D\uDEC2'
	  }, {
	    symbol: '\uD83D\uDEC3'
	  }, {
	    symbol: '\uD83D\uDEC4'
	  }, {
	    symbol: '\uD83D\uDEC5'
	  }, {
	    symbol: '\uD83D\uDEB9'
	  }, {
	    symbol: '\uD83D\uDEBA'
	  }, {
	    symbol: '\uD83D\uDEBC'
	  }, {
	    symbol: '\uD83D\uDEBB'
	  }, {
	    symbol: '\uD83D\uDEAE'
	  }, {
	    symbol: '\uD83C\uDFA6'
	  }, {
	    symbol: '\uD83D\uDCF6'
	  }, {
	    symbol: '\uD83C\uDE01'
	  }, {
	    symbol: '\uD83D\uDD23'
	  }, {
	    symbol: '\u2139'
	  }, {
	    symbol: '\uD83D\uDD24'
	  }, {
	    symbol: '\uD83D\uDD21'
	  }, {
	    symbol: '\uD83D\uDD20'
	  }, {
	    symbol: '\uD83C\uDD96'
	  }, {
	    symbol: '\uD83C\uDD97'
	  }, {
	    symbol: '\uD83C\uDD99'
	  }, {
	    symbol: '\uD83C\uDD92'
	  }, {
	    symbol: '\uD83C\uDD95'
	  }, {
	    symbol: '\uD83C\uDD93'
	  }, {
	    symbol: '\u0030'
	  }, {
	    symbol: '\u0031'
	  }, {
	    symbol: '\u0032'
	  }, {
	    symbol: '\u0033'
	  }, {
	    symbol: '\u0034'
	  }, {
	    symbol: '\u0035'
	  }, {
	    symbol: '\u0036'
	  }, {
	    symbol: '\u0037'
	  }, {
	    symbol: '\u0038'
	  }, {
	    symbol: '\u0039'
	  }, {
	    symbol: '\uD83D\uDD1F'
	  }, {
	    symbol: '\uD83D\uDD22'
	  }, {
	    symbol: '\u0023'
	  }, {
	    symbol: '\u002A'
	  }, {
	    symbol: '\u23CF'
	  }, {
	    symbol: '\u25B6'
	  }, {
	    symbol: '\u23F8'
	  }, {
	    symbol: '\u23EF'
	  }, {
	    symbol: '\u23F9'
	  }, {
	    symbol: '\u23FA'
	  }, {
	    symbol: '\u23ED'
	  }, {
	    symbol: '\u23EE'
	  }, {
	    symbol: '\u23E9'
	  }, {
	    symbol: '\u23EA'
	  }, {
	    symbol: '\u23EB'
	  }, {
	    symbol: '\u23EC'
	  }, {
	    symbol: '\u25C0'
	  }, {
	    symbol: '\uD83D\uDD3C'
	  }, {
	    symbol: '\uD83D\uDD3D'
	  }, {
	    symbol: '\u27A1'
	  }, {
	    symbol: '\u2B05'
	  }, {
	    symbol: '\u2B06'
	  }, {
	    symbol: '\u2B07'
	  }, {
	    symbol: '\u2197'
	  }, {
	    symbol: '\u2198'
	  }, {
	    symbol: '\u2199'
	  }, {
	    symbol: '\u2196'
	  }, {
	    symbol: '\u2195'
	  }, {
	    symbol: '\u2194'
	  }, {
	    symbol: '\u21AA'
	  }, {
	    symbol: '\u21A9'
	  }, {
	    symbol: '\u2934'
	  }, {
	    symbol: '\u2935'
	  }, {
	    symbol: '\uD83D\uDD00'
	  }, {
	    symbol: '\uD83D\uDD01'
	  }, {
	    symbol: '\uD83D\uDD02'
	  }, {
	    symbol: '\uD83D\uDD04'
	  }, {
	    symbol: '\uD83D\uDD03'
	  }, {
	    symbol: '\uD83C\uDFB5'
	  }, {
	    symbol: '\uD83C\uDFB6'
	  }, {
	    symbol: '\u2795'
	  }, {
	    symbol: '\u2796'
	  }, {
	    symbol: '\u2797'
	  }, {
	    symbol: '\u2716'
	  }, {
	    symbol: '\u267E'
	  }, {
	    symbol: '\uD83D\uDCB2'
	  }, {
	    symbol: '\uD83D\uDCB1'
	  }, {
	    symbol: '\u2122'
	  }, {
	    symbol: '\u00A9'
	  }, {
	    symbol: '\u00AE'
	  }, {
	    symbol: '\uD83D\uDC41'
	  }, {
	    symbol: '\uD83D\uDDE8'
	  }, {
	    symbol: '\uD83D\uDD1A'
	  }, {
	    symbol: '\uD83D\uDD19'
	  }, {
	    symbol: '\uD83D\uDD1B'
	  }, {
	    symbol: '\uD83D\uDD1D'
	  }, {
	    symbol: '\uD83D\uDD1C'
	  }, {
	    symbol: '\u3030'
	  }, {
	    symbol: '\u27B0'
	  }, {
	    symbol: '\u27BF'
	  }, {
	    symbol: '\u2714'
	  }, {
	    symbol: '\u2611'
	  }, {
	    symbol: '\uD83D\uDD18'
	  }, {
	    symbol: '\u26AA'
	  }, {
	    symbol: '\u26AB'
	  }, {
	    symbol: '\uD83D\uDD34'
	  }, {
	    symbol: '\uD83D\uDD35'
	  }, {
	    symbol: '\uD83D\uDD3A'
	  }, {
	    symbol: '\uD83D\uDD3B'
	  }, {
	    symbol: '\uD83D\uDD38'
	  }, {
	    symbol: '\uD83D\uDD39'
	  }, {
	    symbol: '\uD83D\uDD36'
	  }, {
	    symbol: '\uD83D\uDD37'
	  }, {
	    symbol: '\uD83D\uDD33'
	  }, {
	    symbol: '\uD83D\uDD32'
	  }, {
	    symbol: '\u25AA'
	  }, {
	    symbol: '\u25AB'
	  }, {
	    symbol: '\u25FE'
	  }, {
	    symbol: '\u25FD'
	  }, {
	    symbol: '\u25FC'
	  }, {
	    symbol: '\u25FB'
	  }, {
	    symbol: '\u2B1B'
	  }, {
	    symbol: '\u2B1C'
	  }, {
	    symbol: '\uD83D\uDD08'
	  }, {
	    symbol: '\uD83D\uDD07'
	  }, {
	    symbol: '\uD83D\uDD09'
	  }, {
	    symbol: '\uD83D\uDD0A'
	  }, {
	    symbol: '\uD83D\uDD14'
	  }, {
	    symbol: '\uD83D\uDD15'
	  }, {
	    symbol: '\uD83D\uDCE3'
	  }, {
	    symbol: '\uD83D\uDCE2'
	  }, {
	    symbol: '\uD83D\uDCAC'
	  }, {
	    symbol: '\uD83D\uDCAD'
	  }, {
	    symbol: '\uD83D\uDDEF'
	  }, {
	    symbol: '\u2660'
	  }, {
	    symbol: '\u2663'
	  }, {
	    symbol: '\u2665'
	  }, {
	    symbol: '\u2666'
	  }, {
	    symbol: '\uD83C\uDCCF'
	  }, {
	    symbol: '\uD83C\uDFB4'
	  }, {
	    symbol: '\uD83C\uDC04'
	  }, {
	    symbol: '\uD83D\uDD50'
	  }, {
	    symbol: '\uD83D\uDD51'
	  }, {
	    symbol: '\uD83D\uDD52'
	  }, {
	    symbol: '\uD83D\uDD53'
	  }, {
	    symbol: '\uD83D\uDD54'
	  }, {
	    symbol: '\uD83D\uDD55'
	  }, {
	    symbol: '\uD83D\uDD56'
	  }, {
	    symbol: '\uD83D\uDD57'
	  }, {
	    symbol: '\uD83D\uDD58'
	  }, {
	    symbol: '\uD83D\uDD59'
	  }, {
	    symbol: '\uD83D\uDD5A'
	  }, {
	    symbol: '\uD83D\uDD5B'
	  }, {
	    symbol: '\uD83D\uDD5C'
	  }, {
	    symbol: '\uD83D\uDD5D'
	  }, {
	    symbol: '\uD83D\uDD5E'
	  }, {
	    symbol: '\uD83D\uDD5F'
	  }, {
	    symbol: '\uD83D\uDD60'
	  }, {
	    symbol: '\uD83D\uDD61'
	  }, {
	    symbol: '\uD83D\uDD62'
	  }, {
	    symbol: '\uD83D\uDD63'
	  }, {
	    symbol: '\uD83D\uDD64'
	  }, {
	    symbol: '\uD83D\uDD65'
	  }, {
	    symbol: '\uD83D\uDD66'
	  }, {
	    symbol: '\uD83D\uDD67'
	  }]
	}, {
	  id: 8,
	  code: 'FLAGS',
	  showForWindows: false,
	  emoji: [{
	    symbol: '\uD83C\uDFF3'
	  }, {
	    symbol: '\uD83C\uDFF4'
	  }, {
	    symbol: '\uD83C\uDFC1'
	  }, {
	    symbol: '\uD83D\uDEA9'
	  }, {
	    symbol: '\uD83C\uDFF3\uFE0F\u200D\uD83C\uDF08'
	  }, {
	    symbol: '\uD83C\uDDFA\uD83C\uDDF3'
	  }, {
	    symbol: '\uD83C\uDDE6\uD83C\uDDEB'
	  }, {
	    symbol: '\uD83C\uDDE6\uD83C\uDDFD'
	  }, {
	    symbol: '\uD83C\uDDE6\uD83C\uDDF1'
	  }, {
	    symbol: '\uD83C\uDDE9\uD83C\uDDFF'
	  }, {
	    symbol: '\uD83C\uDDE6\uD83C\uDDF8'
	  }, {
	    symbol: '\uD83C\uDDE6\uD83C\uDDE9'
	  }, {
	    symbol: '\uD83C\uDDE6\uD83C\uDDF4'
	  }, {
	    symbol: '\uD83C\uDDE6\uD83C\uDDEE'
	  }, {
	    symbol: '\uD83C\uDDE6\uD83C\uDDF6'
	  }, {
	    symbol: '\uD83C\uDDE6\uD83C\uDDEC'
	  }, {
	    symbol: '\uD83C\uDDE6\uD83C\uDDF7'
	  }, {
	    symbol: '\uD83C\uDDE6\uD83C\uDDF2'
	  }, {
	    symbol: '\uD83C\uDDE6\uD83C\uDDFC'
	  }, {
	    symbol: '\uD83C\uDDE6\uD83C\uDDFA'
	  }, {
	    symbol: '\uD83C\uDDE6\uD83C\uDDF9'
	  }, {
	    symbol: '\uD83C\uDDE6\uD83C\uDDFF'
	  }, {
	    symbol: '\uD83C\uDDE7\uD83C\uDDF8'
	  }, {
	    symbol: '\uD83C\uDDE7\uD83C\uDDED'
	  }, {
	    symbol: '\uD83C\uDDE7\uD83C\uDDE9'
	  }, {
	    symbol: '\uD83C\uDDE7\uD83C\uDDFE'
	  }, {
	    symbol: '\uD83C\uDDE7\uD83C\uDDEA'
	  }, {
	    symbol: '\uD83C\uDDE7\uD83C\uDDFF'
	  }, {
	    symbol: '\uD83C\uDDE7\uD83C\uDDEF'
	  }, {
	    symbol: '\uD83C\uDDE7\uD83C\uDDF2'
	  }, {
	    symbol: '\uD83C\uDDE7\uD83C\uDDF9'
	  }, {
	    symbol: '\uD83C\uDDE7\uD83C\uDDF4'
	  }, {
	    symbol: '\uD83C\uDDE7\uD83C\uDDE6'
	  }, {
	    symbol: '\uD83C\uDDE7\uD83C\uDDFC'
	  }, {
	    symbol: '\uD83C\uDDE7\uD83C\uDDF7'
	  }, {
	    symbol: '\uD83C\uDDEE\uD83C\uDDF4'
	  }, {
	    symbol: '\uD83C\uDDFB\uD83C\uDDEC'
	  }, {
	    symbol: '\uD83C\uDDE7\uD83C\uDDF3'
	  }, {
	    symbol: '\uD83C\uDDE7\uD83C\uDDEC'
	  }, {
	    symbol: '\uD83C\uDDE7\uD83C\uDDEB'
	  }, {
	    symbol: '\uD83C\uDDE7\uD83C\uDDEE'
	  }, {
	    symbol: '\uD83C\uDDF0\uD83C\uDDED'
	  }, {
	    symbol: '\uD83C\uDDE8\uD83C\uDDF2'
	  }, {
	    symbol: '\uD83C\uDDE8\uD83C\uDDE6'
	  }, {
	    symbol: '\uD83C\uDDEE\uD83C\uDDE8'
	  }, {
	    symbol: '\uD83C\uDDE8\uD83C\uDDFB'
	  }, {
	    symbol: '\uD83C\uDDE7\uD83C\uDDF6'
	  }, {
	    symbol: '\uD83C\uDDF0\uD83C\uDDFE'
	  }, {
	    symbol: '\uD83C\uDDE8\uD83C\uDDEB'
	  }, {
	    symbol: '\uD83C\uDDF9\uD83C\uDDE9'
	  }, {
	    symbol: '\uD83C\uDDE8\uD83C\uDDF1'
	  }, {
	    symbol: '\uD83C\uDDE8\uD83C\uDDF3'
	  }, {
	    symbol: '\uD83C\uDDE8\uD83C\uDDFD'
	  }, {
	    symbol: '\uD83C\uDDE8\uD83C\uDDF4'
	  }, {
	    symbol: '\uD83C\uDDF0\uD83C\uDDF2'
	  }, {
	    symbol: '\uD83C\uDDE8\uD83C\uDDEC'
	  }, {
	    symbol: '\uD83C\uDDE8\uD83C\uDDE9'
	  }, {
	    symbol: '\uD83C\uDDE8\uD83C\uDDF0'
	  }, {
	    symbol: '\uD83C\uDDE8\uD83C\uDDF7'
	  }, {
	    symbol: '\uD83C\uDDE8\uD83C\uDDEE'
	  }, {
	    symbol: '\uD83C\uDDED\uD83C\uDDF7'
	  }, {
	    symbol: '\uD83C\uDDE8\uD83C\uDDFA'
	  }, {
	    symbol: '\uD83C\uDDE8\uD83C\uDDFC'
	  }, {
	    symbol: '\uD83C\uDDE8\uD83C\uDDFE'
	  }, {
	    symbol: '\uD83C\uDDE8\uD83C\uDDFF'
	  }, {
	    symbol: '\uD83C\uDDE9\uD83C\uDDF0'
	  }, {
	    symbol: '\uD83C\uDDE9\uD83C\uDDEF'
	  }, {
	    symbol: '\uD83C\uDDE9\uD83C\uDDF2'
	  }, {
	    symbol: '\uD83C\uDDE9\uD83C\uDDF4'
	  }, {
	    symbol: '\uD83C\uDDEA\uD83C\uDDE8'
	  }, {
	    symbol: '\uD83C\uDDEA\uD83C\uDDEC'
	  }, {
	    symbol: '\uD83C\uDDF8\uD83C\uDDFB'
	  }, {
	    symbol: '\uD83C\uDDEC\uD83C\uDDF6'
	  }, {
	    symbol: '\uD83C\uDDEA\uD83C\uDDF7'
	  }, {
	    symbol: '\uD83C\uDDEA\uD83C\uDDF9'
	  }, {
	    symbol: '\uD83C\uDDEA\uD83C\uDDFA'
	  }, {
	    symbol: '\uD83C\uDDEB\uD83C\uDDF0'
	  }, {
	    symbol: '\uD83C\uDDEB\uD83C\uDDF4'
	  }, {
	    symbol: '\uD83C\uDDEB\uD83C\uDDEF'
	  }, {
	    symbol: '\uD83C\uDDEB\uD83C\uDDEE'
	  }, {
	    symbol: '\uD83C\uDDEB\uD83C\uDDF7'
	  }, {
	    symbol: '\uD83C\uDDEC\uD83C\uDDEB'
	  }, {
	    symbol: '\uD83C\uDDF5\uD83C\uDDEB'
	  }, {
	    symbol: '\uD83C\uDDEC\uD83C\uDDE6'
	  }, {
	    symbol: '\uD83C\uDDEC\uD83C\uDDF2'
	  }, {
	    symbol: '\uD83C\uDDEC\uD83C\uDDEA'
	  }, {
	    symbol: '\uD83C\uDDE9\uD83C\uDDEA'
	  }, {
	    symbol: '\uD83C\uDDEC\uD83C\uDDED'
	  }, {
	    symbol: '\uD83C\uDDEC\uD83C\uDDEE'
	  }, {
	    symbol: '\uD83C\uDDEC\uD83C\uDDF7'
	  }, {
	    symbol: '\uD83C\uDDEC\uD83C\uDDF1'
	  }, {
	    symbol: '\uD83C\uDDEC\uD83C\uDDE9'
	  }, {
	    symbol: '\uD83C\uDDEC\uD83C\uDDF5'
	  }, {
	    symbol: '\uD83C\uDDEC\uD83C\uDDFA'
	  }, {
	    symbol: '\uD83C\uDDEC\uD83C\uDDF9'
	  }, {
	    symbol: '\uD83C\uDDEC\uD83C\uDDF3'
	  }, {
	    symbol: '\uD83C\uDDEC\uD83C\uDDFC'
	  }, {
	    symbol: '\uD83C\uDDEC\uD83C\uDDFE'
	  }, {
	    symbol: '\uD83C\uDDED\uD83C\uDDF9'
	  }, {
	    symbol: '\uD83C\uDDED\uD83C\uDDF3'
	  }, {
	    symbol: '\uD83C\uDDED\uD83C\uDDF0'
	  }, {
	    symbol: '\uD83C\uDDED\uD83C\uDDFA'
	  }, {
	    symbol: '\uD83C\uDDEE\uD83C\uDDF8'
	  }, {
	    symbol: '\uD83C\uDDEE\uD83C\uDDF3'
	  }, {
	    symbol: '\uD83C\uDDEE\uD83C\uDDE9'
	  }, {
	    symbol: '\uD83C\uDDEE\uD83C\uDDF7'
	  }, {
	    symbol: '\uD83C\uDDEE\uD83C\uDDF6'
	  }, {
	    symbol: '\uD83C\uDDEE\uD83C\uDDEA'
	  }, {
	    symbol: '\uD83C\uDDEE\uD83C\uDDF2'
	  }, {
	    symbol: '\uD83C\uDDEE\uD83C\uDDF1'
	  }, {
	    symbol: '\uD83C\uDDEE\uD83C\uDDF9'
	  }, {
	    symbol: '\uD83C\uDDEF\uD83C\uDDF2'
	  }, {
	    symbol: '\uD83C\uDDEF\uD83C\uDDF5'
	  }, {
	    symbol: '\uD83C\uDDEF\uD83C\uDDEA'
	  }, {
	    symbol: '\uD83C\uDDEF\uD83C\uDDF4'
	  }, {
	    symbol: '\uD83C\uDDF0\uD83C\uDDFF'
	  }, {
	    symbol: '\uD83C\uDDF0\uD83C\uDDEA'
	  }, {
	    symbol: '\uD83C\uDDF0\uD83C\uDDEE'
	  }, {
	    symbol: '\uD83C\uDDFD\uD83C\uDDF0'
	  }, {
	    symbol: '\uD83C\uDDF0\uD83C\uDDFC'
	  }, {
	    symbol: '\uD83C\uDDF0\uD83C\uDDEC'
	  }, {
	    symbol: '\uD83C\uDDF1\uD83C\uDDE6'
	  }, {
	    symbol: '\uD83C\uDDF1\uD83C\uDDFB'
	  }, {
	    symbol: '\uD83C\uDDF1\uD83C\uDDE7'
	  }, {
	    symbol: '\uD83C\uDDF1\uD83C\uDDF8'
	  }, {
	    symbol: '\uD83C\uDDF1\uD83C\uDDF7'
	  }, {
	    symbol: '\uD83C\uDDF1\uD83C\uDDFE'
	  }, {
	    symbol: '\uD83C\uDDF1\uD83C\uDDEE'
	  }, {
	    symbol: '\uD83C\uDDF1\uD83C\uDDF9'
	  }, {
	    symbol: '\uD83C\uDDF1\uD83C\uDDFA'
	  }, {
	    symbol: '\uD83C\uDDF2\uD83C\uDDF4'
	  }, {
	    symbol: '\uD83C\uDDF2\uD83C\uDDF0'
	  }, {
	    symbol: '\uD83C\uDDF2\uD83C\uDDEC'
	  }, {
	    symbol: '\uD83C\uDDF2\uD83C\uDDFC'
	  }, {
	    symbol: '\uD83C\uDDF2\uD83C\uDDFE'
	  }, {
	    symbol: '\uD83C\uDDF2\uD83C\uDDFB'
	  }, {
	    symbol: '\uD83C\uDDF2\uD83C\uDDF1'
	  }, {
	    symbol: '\uD83C\uDDF2\uD83C\uDDF9'
	  }, {
	    symbol: '\uD83C\uDDF2\uD83C\uDDED'
	  }, {
	    symbol: '\uD83C\uDDF2\uD83C\uDDF7'
	  }, {
	    symbol: '\uD83C\uDDF2\uD83C\uDDFA'
	  }, {
	    symbol: '\uD83C\uDDFE\uD83C\uDDF9'
	  }, {
	    symbol: '\uD83C\uDDF2\uD83C\uDDFD'
	  }, {
	    symbol: '\uD83C\uDDEB\uD83C\uDDF2'
	  }, {
	    symbol: '\uD83C\uDDF2\uD83C\uDDE9'
	  }, {
	    symbol: '\uD83C\uDDF2\uD83C\uDDE8'
	  }, {
	    symbol: '\uD83C\uDDF2\uD83C\uDDF3'
	  }, {
	    symbol: '\uD83C\uDDF2\uD83C\uDDEA'
	  }, {
	    symbol: '\uD83C\uDDF2\uD83C\uDDF8'
	  }, {
	    symbol: '\uD83C\uDDF2\uD83C\uDDE6'
	  }, {
	    symbol: '\uD83C\uDDF2\uD83C\uDDFF'
	  }, {
	    symbol: '\uD83C\uDDF3\uD83C\uDDE6'
	  }, {
	    symbol: '\uD83C\uDDF3\uD83C\uDDF7'
	  }, {
	    symbol: '\uD83C\uDDF3\uD83C\uDDF5'
	  }, {
	    symbol: '\uD83C\uDDF3\uD83C\uDDF1'
	  }, {
	    symbol: '\uD83C\uDDF3\uD83C\uDDE8'
	  }, {
	    symbol: '\uD83C\uDDF3\uD83C\uDDFF'
	  }, {
	    symbol: '\uD83C\uDDF3\uD83C\uDDEE'
	  }, {
	    symbol: '\uD83C\uDDF3\uD83C\uDDEA'
	  }, {
	    symbol: '\uD83C\uDDF3\uD83C\uDDEC'
	  }, {
	    symbol: '\uD83C\uDDF3\uD83C\uDDFA'
	  }, {
	    symbol: '\uD83C\uDDF3\uD83C\uDDEB'
	  }, {
	    symbol: '\uD83C\uDDF0\uD83C\uDDF5'
	  }, {
	    symbol: '\uD83C\uDDF2\uD83C\uDDF5'
	  }, {
	    symbol: '\uD83C\uDDF3\uD83C\uDDF4'
	  }, {
	    symbol: '\uD83C\uDDF4\uD83C\uDDF2'
	  }, {
	    symbol: '\uD83C\uDDF5\uD83C\uDDF0'
	  }, {
	    symbol: '\uD83C\uDDF5\uD83C\uDDFC'
	  }, {
	    symbol: '\uD83C\uDDF5\uD83C\uDDF8'
	  }, {
	    symbol: '\uD83C\uDDF5\uD83C\uDDE6'
	  }, {
	    symbol: '\uD83C\uDDF5\uD83C\uDDEC'
	  }, {
	    symbol: '\uD83C\uDDF5\uD83C\uDDFE'
	  }, {
	    symbol: '\uD83C\uDDF5\uD83C\uDDEA'
	  }, {
	    symbol: '\uD83C\uDDF5\uD83C\uDDED'
	  }, {
	    symbol: '\uD83C\uDDF5\uD83C\uDDF3'
	  }, {
	    symbol: '\uD83C\uDDF5\uD83C\uDDF1'
	  }, {
	    symbol: '\uD83C\uDDF5\uD83C\uDDF9'
	  }, {
	    symbol: '\uD83C\uDDF5\uD83C\uDDF7'
	  }, {
	    symbol: '\uD83C\uDDF6\uD83C\uDDE6'
	  }, {
	    symbol: '\uD83C\uDDF7\uD83C\uDDF4'
	  }, {
	    symbol: '\uD83C\uDDF7\uD83C\uDDFA'
	  }, {
	    symbol: '\uD83C\uDDF7\uD83C\uDDFC'
	  }, {
	    symbol: '\uD83C\uDDFC\uD83C\uDDF8'
	  }, {
	    symbol: '\uD83C\uDDF8\uD83C\uDDF2'
	  }, {
	    symbol: '\uD83C\uDDF8\uD83C\uDDF9'
	  }, {
	    symbol: '\uD83C\uDDF8\uD83C\uDDE6'
	  }, {
	    symbol: '\uD83C\uDDF8\uD83C\uDDF3'
	  }, {
	    symbol: '\uD83C\uDDF7\uD83C\uDDF8'
	  }, {
	    symbol: '\uD83C\uDDF8\uD83C\uDDE8'
	  }, {
	    symbol: '\uD83C\uDDF8\uD83C\uDDF1'
	  }, {
	    symbol: '\uD83C\uDDF8\uD83C\uDDEC'
	  }, {
	    symbol: '\uD83C\uDDF8\uD83C\uDDFD'
	  }, {
	    symbol: '\uD83C\uDDF8\uD83C\uDDF0'
	  }, {
	    symbol: '\uD83C\uDDF8\uD83C\uDDEE'
	  }, {
	    symbol: '\uD83C\uDDEC\uD83C\uDDF8'
	  }, {
	    symbol: '\uD83C\uDDF8\uD83C\uDDE7'
	  }, {
	    symbol: '\uD83C\uDDF8\uD83C\uDDF4'
	  }, {
	    symbol: '\uD83C\uDDFF\uD83C\uDDE6'
	  }, {
	    symbol: '\uD83C\uDDF0\uD83C\uDDF7'
	  }, {
	    symbol: '\uD83C\uDDEA\uD83C\uDDF8'
	  }, {
	    symbol: '\uD83C\uDDF1\uD83C\uDDF0'
	  }, {
	    symbol: '\uD83C\uDDF8\uD83C\uDDED'
	  }, {
	    symbol: '\uD83C\uDDF0\uD83C\uDDF3'
	  }, {
	    symbol: '\uD83C\uDDF1\uD83C\uDDE8'
	  }, {
	    symbol: '\uD83C\uDDF5\uD83C\uDDF2'
	  }, {
	    symbol: '\uD83C\uDDFB\uD83C\uDDE8'
	  }, {
	    symbol: '\uD83C\uDDF8\uD83C\uDDE9'
	  }, {
	    symbol: '\uD83C\uDDF8\uD83C\uDDF7'
	  }, {
	    symbol: '\uD83C\uDDF8\uD83C\uDDFF'
	  }, {
	    symbol: '\uD83C\uDDF8\uD83C\uDDEA'
	  }, {
	    symbol: '\uD83C\uDDE8\uD83C\uDDED'
	  }, {
	    symbol: '\uD83C\uDDF8\uD83C\uDDFE'
	  }, {
	    symbol: '\uD83C\uDDF9\uD83C\uDDFC'
	  }, {
	    symbol: '\uD83C\uDDF9\uD83C\uDDEF'
	  }, {
	    symbol: '\uD83C\uDDF9\uD83C\uDDFF'
	  }, {
	    symbol: '\uD83C\uDDF9\uD83C\uDDED'
	  }, {
	    symbol: '\uD83C\uDDF9\uD83C\uDDF1'
	  }, {
	    symbol: '\uD83C\uDDF9\uD83C\uDDEC'
	  }, {
	    symbol: '\uD83C\uDDF9\uD83C\uDDF0'
	  }, {
	    symbol: '\uD83C\uDDF9\uD83C\uDDF4'
	  }, {
	    symbol: '\uD83C\uDDF9\uD83C\uDDF3'
	  }, {
	    symbol: '\uD83C\uDDF9\uD83C\uDDF7'
	  }, {
	    symbol: '\uD83C\uDDF9\uD83C\uDDF2'
	  }, {
	    symbol: '\uD83C\uDDF9\uD83C\uDDE8'
	  }, {
	    symbol: '\uD83C\uDDF9\uD83C\uDDFB'
	  }, {
	    symbol: '\uD83C\uDDFB\uD83C\uDDEE'
	  }, {
	    symbol: '\uD83C\uDDFA\uD83C\uDDEC'
	  }, {
	    symbol: '\uD83C\uDDFA\uD83C\uDDE6'
	  }, {
	    symbol: '\uD83C\uDDE6\uD83C\uDDEA'
	  }, {
	    symbol: '\uD83C\uDDEC\uD83C\uDDE7'
	  }, {
	    symbol: '\uD83C\uDDFA\uD83C\uDDF8'
	  }, {
	    symbol: '\uD83C\uDDFA\uD83C\uDDFE'
	  }, {
	    symbol: '\uD83C\uDDFA\uD83C\uDDFF'
	  }, {
	    symbol: '\uD83C\uDDFB\uD83C\uDDFA'
	  }, {
	    symbol: '\uD83C\uDDFB\uD83C\uDDE6'
	  }, {
	    symbol: '\uD83C\uDDFB\uD83C\uDDEA'
	  }, {
	    symbol: '\uD83C\uDDFB\uD83C\uDDF3'
	  }, {
	    symbol: '\uD83C\uDDFC\uD83C\uDDEB'
	  }, {
	    symbol: '\uD83C\uDDEA\uD83C\uDDED'
	  }, {
	    symbol: '\uD83C\uDDFE\uD83C\uDDEA'
	  }, {
	    symbol: '\uD83C\uDDFF\uD83C\uDDF2'
	  }, {
	    symbol: '\uD83C\uDDFF\uD83C\uDDFC'
	  }]
	}];
	const defaultEmojiIcon = '\uD83D\uDE0D';

	// @vue/component
	const SmilePopupContent = {
	  name: 'SmilesContent',
	  emits: ['close'],
	  data() {
	    return {
	      smiles: [],
	      sets: [],
	      recentEmoji: new Set(),
	      selectedSetId: ''
	    };
	  },
	  computed: {
	    categoryTitles() {
	      const categoryTitles = emoji.reduce((acc, category) => {
	        const prefix = `IM_TEXTAREA_EMOJI_CATEGORY_`;
	        const title = main_core.Loc.getMessage(`${prefix}${category.code}`);
	        return {
	          ...acc,
	          [category.code]: title
	        };
	      }, {});
	      categoryTitles[this.frequentlyUsedLoc] = main_core.Loc.getMessage(this.frequentlyUsedLoc);
	      return categoryTitles;
	    },
	    visibleSmiles() {
	      const smiles = this.smiles.filter(smile => {
	        return smile.setId === this.selectedSetId && smile.alternative !== false;
	      });
	      return smiles;
	    },
	    visibleRecentEmoji() {
	      const emoji$$1 = [...this.recentEmoji];
	      return emoji$$1.slice(0, this.maxRecentEmoji);
	    },
	    lastSelectedSetId() {
	      const set = this.sets.find(set => {
	        return !!set.selected;
	      });
	      if (!set) {
	        return this.emojiSetTitle;
	      }
	      return set.id;
	    }
	  },
	  created() {
	    const smileManager = im_v2_lib_smileManager.SmileManager.getInstance();
	    if (!smileManager.smileList) {
	      return;
	    }
	    const {
	      sets,
	      smiles
	    } = smileManager.smileList;
	    this.sets = sets;
	    this.smiles = smiles;
	    this.emojiSetTitle = 'emoji';
	    this.selectedSetId = this.lastSelectedSetId;
	    this.emoji = emoji;
	    this.recentEmoji = new Set(smileManager.recentEmoji);
	    this.defaultEmojiIcon = defaultEmojiIcon;
	    this.maxRecentEmoji = 18;
	    this.frequentlyUsedLoc = 'IM_TEXTAREA_EMOJI_CATEGORY_FREQUENTLY';
	  },
	  beforeUnmount() {
	    const smileManager = im_v2_lib_smileManager.SmileManager.getInstance();
	    if (this.lastSelectedSetId !== this.selectedSetId) {
	      smileManager.updateSelectedSet(this.selectedSetId);
	    }
	    if (this.visibleRecentEmoji.length > smileManager.recentEmoji.size) {
	      smileManager.updateRecentEmoji(new Set(this.recentEmoji));
	    }
	  },
	  methods: {
	    calculateRatioSize(smile) {
	      const ratio = 1.75;
	      const width = `${smile.width * ratio}px`;
	      const height = `${smile.height * ratio}px`;
	      return {
	        width,
	        height
	      };
	    },
	    onSmileClick(smileCode, event) {
	      main_core_events.EventEmitter.emit(im_v2_const.EventType.textarea.insertText, {
	        text: smileCode
	      });
	      if (!im_v2_lib_utils.Utils.key.isAltOrOption(event)) {
	        this.$emit('close');
	      }
	    },
	    onEmojiClick(emojiText, event) {
	      this.onSmileClick(emojiText, event);
	      this.addEmojiToRecent(emojiText);
	    },
	    addEmojiToRecent(symbol) {
	      this.recentEmoji.add(symbol);
	    }
	  },
	  template: `
		<div class="bx-im-smiles-content__scope">
			<div class="bx-im-smiles-content__smiles-box">
				<img
					v-for="smile in visibleSmiles"
					:key="smile.id"
					:src="smile.image"
					:title="smile.name ?? smile.typing"
					:style="calculateRatioSize(smile)"
					:alt="smile.typing"
					class="bx-im-smiles-content__smiles-box_smile"
					@click="onSmileClick(smile.typing, $event)"
				/>
				<template v-if="visibleSmiles.length === 0 && selectedSetId === emojiSetTitle">
					<div
						v-if="recentEmoji.size > 0"
						class="bx-im-smiles-content__smiles-box_category"
						key="frequently-used"
					>
						<p class="bx-im-smiles-content__smiles-box_category-title">
							{{categoryTitles[frequentlyUsedLoc]}}
						</p>
						<span
							v-for="symbol in visibleRecentEmoji"
							class="bx-im-smiles-content__smiles-box_category-emoji"
							role="img"
							:key="'recent-'+ symbol"
							@click="onSmileClick(symbol, $event)"
						>
							{{symbol}}
						</span>
					</div>
					<div
						v-for="category in emoji"
						:key="category.id"
						class="bx-im-smiles-content__smiles-box_category"
					>
						<template v-if="category.showForWindows ?? true">
							<p class="bx-im-smiles-content__smiles-box_category-title">
								{{categoryTitles[category.code]}}
							</p>
							<span
								v-for="element in category.emoji"
								:key="element.symbol"
								class="bx-im-smiles-content__smiles-box_category-emoji"
								role="img"
								@click="onEmojiClick(element.symbol, $event)"
							>
								{{element.symbol}}
							</span>
						</template>
					</div>
				</template>
			</div>
			<div class="bx-im-smiles-content__sets">
				<span
					v-for="set in sets" :key="set.id"
					class="bx-im-smiles-content__sets_set --img"
					:class="{
						'--selected': selectedSetId === set.id
					}"
					:title="set.name"
					@click="selectedSetId = set.id"
				>
					<img :src="set.image" />
				</span>
				<span
					class="bx-im-smiles-content__sets_set --emoji"
					:class="{
						'--selected': selectedSetId === emojiSetTitle
					}"
					@click="selectedSetId = emojiSetTitle"
				>
					{{defaultEmojiIcon}}
				</span>
			</div>
		</div>
	`
	};

	// @vue/component
	const SmilePopupMarketContent = {
	  name: 'SmilePopupMarketContent',
	  components: {
	    Spinner: im_v2_component_elements.Spinner
	  },
	  props: {
	    entityId: {
	      type: String,
	      required: true
	    },
	    dialogId: {
	      type: String,
	      required: true
	    }
	  },
	  emits: ['close'],
	  data() {
	    return {
	      isLoading: true,
	      handleResult: true
	    };
	  },
	  computed: {
	    SpinnerSize: () => im_v2_component_elements.SpinnerSize
	  },
	  watch: {
	    entityId(newValue) {
	      this.isLoading = true;
	      this.load(newValue);
	    }
	  },
	  created() {
	    this.marketManager = im_v2_lib_market.MarketManager.getInstance();
	  },
	  mounted() {
	    this.load(this.entityId);
	  },
	  beforeUnmount() {
	    this.handleResult = false;
	  },
	  methods: {
	    load(placementId) {
	      const context = {
	        dialogId: this.dialogId
	      };
	      this.marketManager.loadPlacement(placementId, context).then(response => {
	        if (!this.handleResult || this.entityId !== placementId) {
	          return;
	        }
	        main_core.Runtime.html(this.$refs['im-messenger-smile-selector-placement'], response);
	      }).finally(() => {
	        this.isLoading = false;
	      });
	    },
	    onClose() {
	      this.handleResult = false;
	      this.$emit('close');
	    }
	  },
	  template: `
		<div class="bx-im-smile-popup-market-content__container">
			<div v-if="isLoading" class="bx-im-smile-popup-market-content__loader-container">
				<Spinner :size="SpinnerSize.S"/>
			</div>
			<div v-show="!isLoading" ref="im-messenger-smile-selector-placement"></div>
		</div>
	`
	};

	const TabType = Object.freeze({
	  default: 'default',
	  market: 'market'
	});
	// @vue/component
	const SmilePopup = {
	  name: 'SmilePopup',
	  components: {
	    MessengerPopup: im_v2_component_elements.MessengerPopup,
	    SmilePopupContent,
	    SmilePopupMarketContent,
	    MessengerTabs: im_v2_component_elements.MessengerTabs
	  },
	  props: {
	    bindElement: {
	      type: Object,
	      required: true
	    },
	    dialogId: {
	      type: String,
	      required: true
	    }
	  },
	  emits: ['close'],
	  data() {
	    return {
	      currentTab: TabType.default,
	      currentEntityId: ''
	    };
	  },
	  computed: {
	    TabsColorScheme: () => im_v2_component_elements.TabsColorScheme,
	    TabType: () => TabType,
	    popupConfig() {
	      return {
	        width: 320,
	        bindElement: this.bindElement,
	        bindOptions: {
	          position: 'top'
	        },
	        offsetTop: 25,
	        offsetLeft: -110,
	        padding: 0
	      };
	    },
	    marketMenuItems() {
	      return im_v2_lib_market.MarketManager.getInstance().getAvailablePlacementsByType(im_v2_const.PlacementType.smilesSelector, this.dialogId);
	    },
	    tabs() {
	      return [this.smilesTab, ...this.marketTabs];
	    },
	    smilesTab() {
	      return {
	        id: 1,
	        title: this.$Bitrix.Loc.getMessage('IM_TEXTAREA_SMILE_SELECTOR_SMILES_TAB'),
	        type: TabType.default
	      };
	    },
	    marketTabs() {
	      return this.marketMenuItems.map(marketItem => {
	        return {
	          id: marketItem.id,
	          title: marketItem.title,
	          type: TabType.market
	        };
	      });
	    }
	  },
	  methods: {
	    tabSelect(tab) {
	      this.currentTab = tab.type;
	      this.currentEntityId = tab.id;
	    }
	  },
	  template: `
		<MessengerPopup
			:config="popupConfig"
			@close="$emit('close')"
			id="im-smiles-popup"
		>
			<div class="bx-im-smile-popup__container bx-im-smile-popup__scope">
				<div class="bx-im-smile-popup__tabs-container">
					<MessengerTabs :colorScheme="TabsColorScheme.gray" :tabs="tabs" @tabSelect="tabSelect"  />
				</div>
				<SmilePopupContent v-if="currentTab === TabType.default" />
				<SmilePopupMarketContent v-else :entityId="currentEntityId" :dialogId="dialogId" />
			</div>
		</MessengerPopup>
	`
	};

	// @vue/component
	const SmileSelector = {
	  name: 'SmileSelector',
	  components: {
	    SmilePopup
	  },
	  props: {
	    dialogId: {
	      type: String,
	      required: true
	    }
	  },
	  data() {
	    return {
	      showPopup: false
	    };
	  },
	  methods: {
	    loc(phraseCode) {
	      return this.$Bitrix.Loc.getMessage(phraseCode);
	    }
	  },
	  template: `
		<div
			@click="showPopup = true"
			:title="loc('IM_TEXTAREA_ICON_SMILE')"
			class="bx-im-textarea__icon --smile"
			:class="{'--active': showPopup}"
			ref="addSmile"
		>
		</div>
		<SmilePopup
			v-if="showPopup"
			:bindElement="$refs['addSmile']"
			:dialogId="dialogId"
			@close="showPopup = false"
		/>
	`
	};

	// @vue/component
	const EditPanel = {
	  name: 'EditPanel',
	  props: {
	    messageId: {
	      type: Number,
	      required: true
	    }
	  },
	  emits: ['close'],
	  data() {
	    return {};
	  },
	  computed: {
	    message() {
	      return this.$store.getters['messages/getById'](this.messageId);
	    },
	    preparedText() {
	      return im_v2_lib_parser.Parser.purifyMessage(this.message);
	    }
	  },
	  methods: {
	    loc(phraseCode) {
	      return this.$Bitrix.Loc.getMessage(phraseCode);
	    }
	  },
	  template: `
		<div class="bx-im-edit-message__container">
			<div class="bx-im-edit-message__icon"></div>
			<div class="bx-im-edit-message__content">
				<div class="bx-im-edit-message__title">{{ loc('IM_TEXTAREA_EDIT_MESSAGE_TITLE') }}</div>
				<div class="bx-im-edit-message__text">{{ preparedText }}</div>
			</div>
			<div @click="$emit('close')" class="bx-im-edit-message__close"></div>
		</div>
	`
	};

	// @vue/component
	const DocumentPanel = {
	  directives: {
	    hint: ui_vue3_directives_hint.hint
	  },
	  data() {
	    return {};
	  },
	  computed: {
	    hintContent() {
	      return {
	        text: this.loc('IM_MESSENGER_NOT_AVAILABLE'),
	        popupOptions: {
	          bindOptions: {
	            position: 'top'
	          },
	          angle: true,
	          targetContainer: document.body,
	          offsetLeft: 125,
	          offsetTop: -30
	        }
	      };
	    }
	  },
	  methods: {
	    loc(phraseCode) {
	      return this.$Bitrix.Loc.getMessage(phraseCode);
	    }
	  },
	  template: `
		<div class="bx-im-file-menu__document-panel_container">
			<div class="bx-im-file-menu__document-panel_title">
				{{ loc('IM_TEXTAREA_CREATE_AND_SEND_FILE') }}
			</div>
			<div class="bx-im-file-menu__document-panel_content" v-hint="hintContent">
				<div class="bx-im-file-menu__document-panel_item">
					<div class="ui-icon ui-icon-file-doc"><i></i></div>
					<div class="bx-im-file-menu__document-panel_item_title">
						{{ loc('IM_TEXTAREA_CREATE_DOCUMENT') }}
					</div>
				</div>
				<div class="bx-im-file-menu__document-panel_item">
					<div class="ui-icon ui-icon-file-xls"><i></i></div>
					<div class="bx-im-file-menu__document-panel_item_title">
						{{ loc('IM_TEXTAREA_CREATE_SPREADSHEET') }}
					</div>
				</div>
				<div class="bx-im-file-menu__document-panel_item">
					<div class="ui-icon ui-icon-file-ppt"><i></i></div>
					<div class="bx-im-file-menu__document-panel_item_title">
						{{ loc('IM_TEXTAREA_CREATE_PRESENTATION') }}
					</div>
				</div>
			</div>
		</div>
	`
	};

	const FILE_DIALOG_ID = 'im-file-dialog';

	/* eslint-disable bitrix-rules/no-bx */
	// @vue/component
	const DiskPopup = {
	  name: 'DiskPopup',
	  emits: ['close', 'diskFileSelect'],
	  created() {
	    if (!BX.DiskFileDialog) {
	      console.error('Couldn\'t initialize disk popup');
	      return;
	    }
	    this.subscribeEvents();
	    this.open();
	  },
	  beforeUnmount() {
	    this.unsubscribeEvents();
	  },
	  methods: {
	    subscribeEvents() {
	      BX.addCustomEvent(BX.DiskFileDialog, 'inited', this.onInited);
	      BX.addCustomEvent(BX.DiskFileDialog, 'loadItems', this.onLoadItems);
	    },
	    unsubscribeEvents() {
	      BX.removeCustomEvent(BX.DiskFileDialog, 'inited', this.onInited);
	      BX.removeCustomEvent(BX.DiskFileDialog, 'loadItems', this.onLoadItems);
	    },
	    onInited(name) {
	      if (name !== FILE_DIALOG_ID) {
	        return;
	      }
	      BX.DiskFileDialog.obCallback[name] = {
	        'saveButton': (tab, path, selected) => {
	          this.$emit('diskFileSelect', {
	            files: selected
	          });
	        },
	        'popupDestroy': () => {
	          this.unsubscribeEvents();
	          this.$emit('close');
	        }
	      };
	      BX.DiskFileDialog.openDialog(name);
	    },
	    onLoadItems(link, name) {
	      if (name !== FILE_DIALOG_ID) {
	        return;
	      }
	      BX.DiskFileDialog.target[name] = link.replace('/bitrix/tools/disk/uf.php', '/bitrix/components/bitrix/im.messenger/file.ajax.php');
	    },
	    open() {
	      main_core.ajax({
	        url: `/bitrix/components/bitrix/im.messenger/file.ajax.php?action=selectFile&dialogName=${FILE_DIALOG_ID}`,
	        method: 'GET',
	        skipAuthCheck: true,
	        timeout: 30
	      });
	    }
	  },
	  template: `<template></template>`
	};

	// @vue/component
	const UploadMenu = {
	  components: {
	    DocumentPanel,
	    MessengerMenu: im_v2_component_elements.MessengerMenu,
	    MenuItem: im_v2_component_elements.MenuItem,
	    DiskPopup
	  },
	  emits: ['fileSelect', 'diskFileSelect'],
	  data() {
	    return {
	      showMenu: false,
	      showDiskPopup: false
	    };
	  },
	  computed: {
	    MenuItemIcon: () => im_v2_component_elements.MenuItemIcon,
	    menuConfig() {
	      return {
	        width: 278,
	        bindElement: this.$refs['upload'] || {},
	        bindOptions: {
	          position: 'top'
	        },
	        offsetTop: 30,
	        offsetLeft: -10,
	        padding: 0
	      };
	    }
	  },
	  methods: {
	    onSelectFromComputerClick() {
	      this.$refs['fileInput'].click();
	      this.showMenu = false;
	    },
	    onSelectFromDiskClick() {
	      this.showDiskPopup = true;
	      this.showMenu = false;
	    },
	    onFileSelect(event) {
	      this.$emit('fileSelect', event);
	      this.showMenu = false;
	    },
	    onDiskFileSelect(event) {
	      this.$emit('diskFileSelect', event);
	    },
	    loc(phraseCode) {
	      return this.$Bitrix.Loc.getMessage(phraseCode);
	    }
	  },
	  template: `
		<div
			class="bx-im-textarea__icon --upload"
			:class="{'--active': showMenu}"
			:title="loc('IM_TEXTAREA_ICON_UPLOAD')"
			@click="showMenu = true"
			ref="upload"
		>
		</div>
		<MessengerMenu v-if="showMenu" :config="menuConfig" @close="showMenu = false" className="bx-im-file-menu__scope">
			<template #header>
				<DocumentPanel />
			</template>
			<MenuItem
				:icon="MenuItemIcon.upload"
				:title="loc('IM_TEXTAREA_SELECT_FROM_COMPUTER')"
				@click="onSelectFromComputerClick"
			/>
			<MenuItem
				:icon="MenuItemIcon.disk"
				:title="loc('IM_TEXTAREA_SELECT_FROM_BITRIX24_DISK')"
				@click="onSelectFromDiskClick"
			/>
			<input type="file" @change="onFileSelect" multiple class="bx-im-file-menu__file-input" ref="fileInput">
		</MessengerMenu>
		<DiskPopup v-if="showDiskPopup" @diskFileSelect="onDiskFileSelect" @close="showDiskPopup = false"/>
	`
	};

	// @vue/component
	const CreateEntityMenu = {
	  components: {
	    MessengerMenu: im_v2_component_elements.MessengerMenu,
	    MenuItem: im_v2_component_elements.MenuItem
	  },
	  props: {
	    dialogId: {
	      type: String,
	      required: true
	    }
	  },
	  data() {
	    return {
	      showMenu: false
	    };
	  },
	  computed: {
	    MenuItemIcon: () => im_v2_component_elements.MenuItemIcon,
	    dialog() {
	      return this.$store.getters['dialogues/get'](this.dialogId, true);
	    },
	    chatId() {
	      return this.dialog.chatId;
	    },
	    menuConfig() {
	      return {
	        width: 288,
	        bindElement: this.$refs['createEntity'] || {},
	        bindOptions: {
	          position: 'top'
	        },
	        offsetTop: 30,
	        offsetLeft: -139,
	        padding: 0
	      };
	    }
	  },
	  methods: {
	    onCreateTaskClick() {
	      this.getEntityCreator().createTaskForChat();
	      this.showMenu = false;
	    },
	    onCreateMeetingClick() {
	      this.getEntityCreator().createMeetingForChat();
	      this.showMenu = false;
	    },
	    onCreateSummaryClick() {
	      //
	    },
	    getEntityCreator() {
	      if (!this.entityCreator) {
	        this.entityCreator = new im_v2_lib_entityCreator.EntityCreator(this.chatId);
	      }
	      return this.entityCreator;
	    },
	    loc(phraseCode) {
	      return this.$Bitrix.Loc.getMessage(phraseCode);
	    }
	  },
	  template: `
		<div
			@click="showMenu = true"
			:title="loc('IM_TEXTAREA_ICON_CREATE')"
			class="bx-im-textarea__icon --create"
			:class="{'--active': showMenu}"
			ref="createEntity"
		>
		</div>
		<MessengerMenu v-if="showMenu" :config="menuConfig" @close="showMenu = false">
			<MenuItem
				:icon="MenuItemIcon.task"
				:title="loc('IM_TEXTAREA_CREATE_TASK_TITLE')"
				:subtitle="loc('IM_TEXTAREA_CREATE_TASK_SUBTITLE')"
				@click="onCreateTaskClick"
			/>
			<MenuItem
				:icon="MenuItemIcon.meeting"
				:title="loc('IM_TEXTAREA_CREATE_MEETING_TITLE')"
				:subtitle="loc('IM_TEXTAREA_CREATE_MEETING_SUBTITLE')"
				@click="onCreateMeetingClick"
			/>
			<MenuItem
				:icon="MenuItemIcon.summary"
				:title="loc('IM_TEXTAREA_CREATE_SUMMARY_TITLE')"
				:subtitle="loc('IM_TEXTAREA_CREATE_SUMMARY_SUBTITLE')"
				:disabled="true"
			/>
			<MenuItem
				:icon="MenuItemIcon.vote"
				:title="loc('IM_TEXTAREA_CREATE_VOTE_TITLE')"
				:subtitle="loc('IM_TEXTAREA_CREATE_VOTE_SUBTITLE')"
				:disabled="true"
			/>
		</MessengerMenu>
	`
	};

	const SEND_MESSAGE_COMBINATION = 'Enter';

	// @vue/component
	const SendButton = {
	  props: {
	    editMode: {
	      type: Boolean,
	      default: false
	    },
	    isDisabled: {
	      type: Boolean,
	      default: false
	    }
	  },
	  data() {
	    return {};
	  },
	  computed: {
	    buttonHint() {
	      return this.loc('IM_TEXTAREA_ICON_SEND_TEXT', {
	        '#SEND_MESSAGE_COMBINATION#': SEND_MESSAGE_COMBINATION
	      });
	    }
	  },
	  methods: {
	    loc(phraseCode, replacements = {}) {
	      return this.$Bitrix.Loc.getMessage(phraseCode, replacements);
	    }
	  },
	  template: `
		<div
			:title="buttonHint"
			class="bx-im-send-panel__button_container"
			:class="{'--edit': editMode, '--disabled': isDisabled}"
		></div>
	`
	};

	// @vue/component
	const MarketAppPopup = {
	  name: 'MarketAppPopup',
	  components: {
	    MessengerPopup: im_v2_component_elements.MessengerPopup,
	    Spinner: im_v2_component_elements.Spinner
	  },
	  props: {
	    bindElement: {
	      type: Object,
	      required: true
	    },
	    entityId: {
	      type: String,
	      required: true
	    },
	    width: {
	      type: Number,
	      required: true
	    },
	    height: {
	      type: Number,
	      required: true
	    },
	    dialogId: {
	      type: String,
	      required: true
	    }
	  },
	  emits: ['close'],
	  data() {
	    return {
	      isLoading: true,
	      handleResult: true
	    };
	  },
	  computed: {
	    SpinnerSize: () => im_v2_component_elements.SpinnerSize,
	    popupConfig() {
	      return {
	        width: this.width,
	        height: this.height,
	        bindElement: this.bindElement,
	        bindOptions: {
	          position: 'top'
	        },
	        offsetTop: 0,
	        offsetLeft: 0,
	        padding: 0
	      };
	    }
	  },
	  created() {
	    this.marketManager = im_v2_lib_market.MarketManager.getInstance();
	  },
	  mounted() {
	    const context = {
	      dialogId: this.dialogId
	    };
	    this.marketManager.loadPlacement(this.entityId, context).then(response => {
	      if (!this.handleResult) {
	        return;
	      }
	      this.isLoading = false;
	      main_core.Runtime.html(this.$refs['im-messenger-textarea-placement'], response);
	    });
	  },
	  methods: {
	    onClose() {
	      this.handleResult = false;
	      this.$emit('close');
	    }
	  },
	  template: `
		<MessengerPopup
			:config="popupConfig"
			@close="onClose"
			id="im-market-app-popup"
		>
			<div class="bx-im-market-app-popup__container">
				<div v-if="isLoading" class="bx-im-market-app-popup__loader-container">
					<Spinner :size="SpinnerSize.S"/>
				</div>
				<div ref="im-messenger-textarea-placement" class="bx-im-market-app-popup__placement-container"></div>
			</div>
		</MessengerPopup>
	`
	};

	// @vue/component
	const MarketAppItem = {
	  name: 'MarketAppItem',
	  components: {
	    MarketAppPopup
	  },
	  props: {
	    item: {
	      type: Object,
	      required: true
	    },
	    hideTitle: {
	      type: Boolean,
	      default: false
	    },
	    dialogId: {
	      type: String,
	      required: true
	    }
	  },
	  data() {
	    return {
	      showApp: false
	    };
	  },
	  computed: {
	    marketItem() {
	      return this.item;
	    },
	    iconClass() {
	      return `fa ${this.marketItem.options.iconName}`;
	    },
	    iconColor() {
	      return this.marketItem.options.color;
	    }
	  },
	  methods: {
	    onAppClick() {
	      this.showApp = !this.showApp;
	    }
	  },
	  template: `
		<div 
			class="bx-im-market-app-item__container" 
			:class="{'--short': hideTitle}" 
			:title="marketItem.title"
			@click="onAppClick"
			ref="market-app"
		>
			<div class="bx-im-market-app-item__icon-container" :style="{backgroundColor: iconColor}">
				<i :class="iconClass" aria-hidden="true"></i>
			</div>
			<div v-if="!hideTitle" class="bx-im-market-app-item__title-container" :title="marketItem.title">
				<div class="bx-im-market-app-item__title-text">
					{{ marketItem.title }}
				</div>
			</div>
			<MarketAppPopup 
				v-if="showApp" 
				:bindElement="$refs['market-app']" 
				:entityId="marketItem.id"
				:width="marketItem.options.width"
				:height="marketItem.options.height"
				:dialogId="dialogId"
				@close="onAppClick"
			/>
		</div>
	`
	};

	// @vue/component
	const MarketShowMorePopupContentItem = {
	  name: 'MarketShowMorePopupContentItem',
	  components: {
	    MarketAppPopup
	  },
	  props: {
	    item: {
	      type: Object,
	      required: true
	    },
	    dialogId: {
	      type: String,
	      required: true
	    }
	  },
	  data() {
	    return {
	      showApp: false
	    };
	  },
	  computed: {
	    marketItem() {
	      return this.item;
	    },
	    iconClass() {
	      return `fa ${this.marketItem.options.iconName}`;
	    },
	    iconColor() {
	      return this.marketItem.options.color;
	    }
	  },
	  methods: {
	    onAppClick() {
	      this.showApp = !this.showApp;
	    }
	  },
	  template: `
		<div 
			class="bx-im-market-show-more-popup-content-item__container"
			:title="marketItem.title"
			@click="onAppClick"
			ref="market-app"
		>
			<div class="bx-im-market-show-more-popup-content-item__icon-container" :style="{backgroundColor: iconColor}">
				<i :class="iconClass" aria-hidden="true"></i>
			</div>
			<div class="bx-im-market-show-more-popup-content-item__title-container">
				<div class="bx-im-market-show-more-popup-content-item__title-text">
					{{ marketItem.title }}
				</div>
			</div>
			<MarketAppPopup
				v-if="showApp" 
				:bindElement="$refs['market-app']" 
				:entityId="marketItem.id"
				:width="marketItem.options.width"
				:height="marketItem.options.height" 
				:dialogId="dialogId"
				@close="onAppClick"
			/>
		</div>
	`
	};

	// @vue/component
	const MarketShowMorePopupContent = {
	  name: 'MarketShowMorePopupContent',
	  components: {
	    MarketShowMorePopupContentItem
	  },
	  props: {
	    marketApps: {
	      type: Array,
	      required: true
	    },
	    dialogId: {
	      type: String,
	      required: true
	    }
	  },
	  data() {
	    return {
	      isLoading: true,
	      needTopShadow: false,
	      needBottomShadow: true
	    };
	  },
	  methods: {
	    onListScroll(event) {
	      this.needBottomShadow = event.target.scrollTop + event.target.clientHeight !== event.target.scrollHeight;
	      if (event.target.scrollTop === 0) {
	        this.needTopShadow = false;
	        return;
	      }
	      this.needTopShadow = true;
	    }
	  },
	  template: `
		<div class="bx-im-market-show-more-popup-content__scope bx-im-market-show-more-popup-content__container">
			<div v-if="needTopShadow" class="bx-im-market-show-more-popup-content__shadow --top">
				<div class="bx-im-market-show-more-popup-content__shadow-inner"></div>
			</div>
			<div @scroll="onListScroll" class="bx-im-market-show-more-popup-content__items-container">
				<MarketShowMorePopupContentItem
					v-for="item in marketApps"
					:item="item"
					:dialogId="dialogId"
					@onAppClick="$emit('close')"
				/>
			</div>
			<div v-if="needBottomShadow" class="bx-im-market-show-more-popup-content__shadow --bottom">
				<div class="bx-im-market-show-more-popup-content__shadow-inner"></div>
			</div>
		</div>
	`
	};

	// @vue/component
	const MarketShowMorePopup = {
	  name: 'MarketShowMorePopup',
	  components: {
	    MessengerPopup: im_v2_component_elements.MessengerPopup,
	    MarketShowMorePopupContent
	  },
	  props: {
	    marketApps: {
	      type: Array,
	      required: true
	    },
	    dialogId: {
	      type: String,
	      required: true
	    }
	  },
	  emits: ['close'],
	  data() {
	    return {
	      showPopup: false
	    };
	  },
	  computed: {
	    popupConfig() {
	      return {
	        titleBar: this.$Bitrix.Loc.getMessage('IM_TEXTAREA_MARKET_OTHER_APPS'),
	        closeIcon: true,
	        width: 302,
	        height: 422,
	        bindElement: this.$refs['textarea-show-more-market-apps'],
	        bindOptions: {
	          position: 'top'
	        },
	        offsetTop: 0,
	        offsetLeft: 0,
	        padding: 0,
	        contentPadding: 0,
	        contentBackground: '#fff',
	        className: 'bx-im-market-show-more-popup__scope'
	      };
	    },
	    showMoreButtonText() {
	      return this.$Bitrix.Loc.getMessage('IM_TEXTAREA_MARKET_APPS_SHOW_MORE_BUTTON').replace('#NUMBER#', this.marketApps.length);
	    }
	  },
	  template: `
		<div
			@click="showPopup = true"
			class="bx-im-market-apps-panel__more-items-button"
			:class="{'--active': showPopup}"
			ref="textarea-show-more-market-apps"
		>
			{{ showMoreButtonText }}
		</div>
		<MessengerPopup
			v-if="showPopup"
			:config="popupConfig"
			@close="showPopup = false"
			id="im-market-apps-more-popup"
		>
			<MarketShowMorePopupContent :marketApps='marketApps' :dialogId="dialogId" @close="showPopup = false" />
		</MessengerPopup>
	`
	};

	const MAX_EXPANDED_ITEMS = 5;
	const MAX_COLLAPSED_ITEMS = 15;

	// @vue/component
	const MarketAppsPanel = {
	  name: 'MarketAppsPanel',
	  components: {
	    MarketAppItem,
	    MarketShowMorePopup
	  },
	  props: {
	    dialogId: {
	      type: String,
	      required: true
	    }
	  },
	  computed: {
	    marketMenuItems() {
	      return im_v2_lib_market.MarketManager.getInstance().getAvailablePlacementsByType(im_v2_const.PlacementType.textarea, this.dialogId);
	    },
	    marketItemsToShow() {
	      const maxItems = this.hideTitle ? MAX_COLLAPSED_ITEMS : MAX_EXPANDED_ITEMS;
	      return {
	        displayedItems: this.marketMenuItems.slice(0, maxItems),
	        hiddenItems: this.marketMenuItems.slice(maxItems)
	      };
	    },
	    hideTitle() {
	      return this.marketMenuItems.length > MAX_EXPANDED_ITEMS;
	    },
	    needMoreButton() {
	      return this.marketItemsToShow.hiddenItems.length > 0;
	    },
	    isEmptyState() {
	      return this.marketItemsToShow.displayedItems.length === 0;
	    }
	  },
	  template: `
		<div class="bx-im-market-apps-panel__scope">
			<div v-if="isEmptyState" class="bx-im-market-apps-panel__empty-state-container">
				<div class="bx-im-market-apps-panel__empty-state-icon"></div>
				<div class="bx-im-market-apps-panel__empty-state-text">
					{{ $Bitrix.Loc.getMessage('IM_TEXTAREA_MARKET_APPS_EMPTY_STATE') }}
				</div>
				<div class="bx-im-market-apps-panel__empty-state-button"></div>
			</div>
			<div v-else class="bx-im-market-apps-panel__container">
				<div class="bx-im-market-apps-panel__items-container" :class="{'--short': hideTitle}">
					<MarketAppItem
						v-for="item in marketItemsToShow.displayedItems"
						:item="item"
						:hideTitle="hideTitle"
						:dialogId="dialogId"
					/>
				</div>
				<MarketShowMorePopup 
					v-if="needMoreButton" 
					:marketApps="marketItemsToShow.hiddenItems"
					:dialogId="dialogId"
				/>
			</div>
		</div>
		
	`
	};

	// @vue/component
	const ChatTextarea = {
	  components: {
	    EditPanel,
	    UploadMenu,
	    CreateEntityMenu,
	    SmileSelector,
	    SendButton,
	    MarketAppsPanel
	  },
	  props: {
	    dialogId: {
	      type: String,
	      default: ''
	    }
	  },
	  data() {
	    return {
	      text: '',
	      mentions: {},
	      textareaHeight: ResizeManager.minHeight,
	      editMessageId: 0,
	      showMarketApps: false
	    };
	  },
	  computed: {
	    dialog() {
	      return this.$store.getters['dialogues/get'](this.dialogId, true);
	    },
	    dialogInited() {
	      return this.dialog.inited;
	    },
	    editMode() {
	      return !!this.editMessageId;
	    },
	    textareaStyle() {
	      let height = `${this.textareaHeight}px`;
	      if (this.textareaHeight === 'auto') {
	        height = 'auto';
	      }
	      return {
	        height,
	        maxHeight: height
	      };
	    },
	    textareaMaxLength() {
	      const settings = main_core.Extension.getSettings('im.v2.component.textarea');
	      return settings.get('maxLength');
	    },
	    hasMentions() {
	      return Object.keys(this.mentions).length > 0;
	    }
	  },
	  watch: {
	    dialogInited(newValue, oldValue) {
	      if (!newValue || oldValue) {
	        return false;
	      }
	    },
	    text(newValue) {
	      this.adjustTextareaHeight();
	      if (!this.editMode) {
	        im_v2_lib_draft.DraftManager.getInstance().setDraft(this.dialogId, newValue);
	      }
	      if (main_core.Type.isStringFilled(newValue)) {
	        this.getTypingService().startTyping();
	      }
	    }
	  },
	  created() {
	    this.initResizeManager();
	    this.restoreTextareaHeight();
	    this.restoreDraftText();
	    main_core_events.EventEmitter.subscribe(im_v2_const.EventType.textarea.insertMention, this.onInsertMention);
	    main_core_events.EventEmitter.subscribe(im_v2_const.EventType.textarea.insertText, this.onInsertText);
	    main_core_events.EventEmitter.subscribe(im_v2_const.EventType.textarea.editMessage, this.onEditMessage);
	  },
	  mounted() {
	    this.$refs['textarea'].focus();
	  },
	  beforeUnmount() {
	    this.resizeManager.destroy();
	    main_core_events.EventEmitter.unsubscribe(im_v2_const.EventType.textarea.insertMention, this.onInsertMention);
	    main_core_events.EventEmitter.unsubscribe(im_v2_const.EventType.textarea.insertText, this.onInsertText);
	    main_core_events.EventEmitter.unsubscribe(im_v2_const.EventType.textarea.editMessage, this.onEditMessage);
	  },
	  methods: {
	    sendMessage() {
	      this.text = this.text.trim();
	      if (!this.text || !this.dialogInited) {
	        return;
	      }
	      if (this.editMode) {
	        this.getMessageService().editMessageText(this.editMessageId, this.text);
	        this.closeEditPanel();
	        this.clear();
	        return;
	      }
	      const text = this.hasMentions ? this.replaceMentions(this.text) : this.text;
	      this.getSendingService().sendMessage({
	        text: text,
	        dialogId: this.dialogId
	      });
	      this.getTypingService().stopTyping();
	      this.clear();
	      im_v2_lib_draft.DraftManager.getInstance().clearDraftInRecentList(this.dialogId);
	      im_v2_lib_soundNotification.SoundNotificationManager.getInstance().playOnce(im_v2_const.SoundType.send);
	    },
	    replaceMentions(text) {
	      if (!this.hasMentions) {
	        return;
	      }
	      let textWithMentions = text;
	      Object.entries(this.mentions).forEach(mention => {
	        const [mentionText, mentionReplacement] = mention;
	        textWithMentions = textWithMentions.replace(mentionText, mentionReplacement);
	      });
	      return textWithMentions;
	    },
	    clear() {
	      this.text = '';
	      this.mentions = {};
	    },
	    openEditPanel(messageId) {
	      this.showMarketApps = false;
	      const message = this.$store.getters['messages/getById'](messageId);
	      this.editMessageId = messageId;
	      this.text = im_v2_lib_parser.Parser.prepareEdit(message);
	      this.$refs['textarea'].focus();
	    },
	    closeEditPanel() {
	      this.editMessageId = 0;
	    },
	    async adjustTextareaHeight() {
	      this.textareaHeight = 'auto';
	      await this.$nextTick();
	      const newMaxPoint = Math.min(ResizeManager.maxHeight, this.$refs['textarea'].scrollHeight);
	      if (this.resizedTextareaHeight) {
	        this.textareaHeight = Math.max(newMaxPoint, this.resizedTextareaHeight);
	        return;
	      }
	      this.textareaHeight = Math.max(newMaxPoint, ResizeManager.minHeight);
	    },
	    saveTextareaHeight() {
	      const WRITE_TO_STORAGE_TIMEOUT = 200;
	      clearTimeout(this.saveTextareaTimeout);
	      this.saveTextareaTimeout = setTimeout(() => {
	        im_v2_lib_localStorage.LocalStorageManager.getInstance().set(im_v2_const.LocalStorageKey.textareaHeight, this.resizedTextareaHeight);
	      }, WRITE_TO_STORAGE_TIMEOUT);
	    },
	    restoreTextareaHeight() {
	      const rawSavedHeight = im_v2_lib_localStorage.LocalStorageManager.getInstance().get(im_v2_const.LocalStorageKey.textareaHeight);
	      const savedHeight = Number.parseInt(rawSavedHeight, 10);
	      if (!savedHeight) {
	        return;
	      }
	      this.resizedTextareaHeight = savedHeight;
	      this.textareaHeight = savedHeight;
	    },
	    restoreDraftText() {
	      this.text = im_v2_lib_draft.DraftManager.getInstance().getDraft(this.dialogId);
	    },
	    onKeyDown(event) {
	      const exitEditCombination = im_v2_lib_utils.Utils.key.isCombination(event, 'Escape');
	      const sendMessageCombination = im_v2_lib_utils.Utils.key.isCombination(event, ['Enter', 'NumpadEnter']);
	      const newLineCombination = im_v2_lib_utils.Utils.key.isCombination(event, 'Shift+Enter');
	      const tabCombination = im_v2_lib_utils.Utils.key.isCombination(event, 'Tab');
	      if (this.editMode && exitEditCombination) {
	        this.onEditPanelClose();
	      } else if (sendMessageCombination && !newLineCombination) {
	        event.preventDefault();
	        this.sendMessage();
	      } else if (tabCombination) {
	        event.preventDefault();
	        this.text += '\t';
	      } else if (this.text === '' && im_v2_lib_utils.Utils.key.isCombination(event, 'ArrowUp')) {
	        event.preventDefault();
	        const lastOwnMessageId = this.$store.getters['messages/getLastOwnMessageId'](this.dialog.chatId);
	        if (lastOwnMessageId) {
	          this.openEditPanel(lastOwnMessageId);
	        }
	      }
	    },
	    onResizeStart(event) {
	      this.resizeManager.onResizeStart(event, this.textareaHeight);
	    },
	    onFileSelect(fileEvent) {
	      const files = Object.values(fileEvent.target.files);
	      this.getSendingService().sendFilesFromInput(files, this.dialogId);
	    },
	    onDiskFileSelect({
	      files
	    }) {
	      this.getSendingService().sendFilesFromDisk(files, this.dialogId);
	    },
	    onInsertMention(event) {
	      const {
	        mentionText,
	        mentionReplacement
	      } = event.getData();
	      this.mentions[mentionText] = mentionReplacement;
	      this.text += `${mentionText} `;
	      this.$refs.textarea.focus();
	    },
	    onInsertText(event) {
	      const {
	        text,
	        withNewLine
	      } = event.getData();
	      if (this.text.length === 0) {
	        this.text = text;
	      } else {
	        this.text = withNewLine ? `${this.text}\n${text}` : `${this.text} ${text}`;
	      }
	      this.$refs.textarea.focus();
	    },
	    onEditMessage(event) {
	      const {
	        messageId
	      } = event.getData();
	      this.openEditPanel(messageId);
	    },
	    onEditPanelClose() {
	      this.closeEditPanel();
	      this.clear();
	    },
	    initResizeManager() {
	      this.resizeManager = new ResizeManager();
	      this.resizeManager.subscribe(ResizeManager.events.onHeightChange, ({
	        data: {
	          newHeight
	        }
	      }) => {
	        im_v2_lib_logger.Logger.warn('Textarea: Resize height change', newHeight);
	        this.textareaHeight = newHeight;
	      });
	      this.resizeManager.subscribe(ResizeManager.events.onResizeStop, () => {
	        im_v2_lib_logger.Logger.warn('Textarea: Resize stop');
	        this.resizedTextareaHeight = this.textareaHeight;
	        this.saveTextareaHeight();
	      });
	    },
	    getSendingService() {
	      if (!this.sendingService) {
	        this.sendingService = im_v2_provider_service.SendingService.getInstance();
	      }
	      return this.sendingService;
	    },
	    getTypingService() {
	      if (!this.typingService) {
	        this.typingService = new TypingService(this.dialogId);
	      }
	      return this.typingService;
	    },
	    getMessageService() {
	      if (!this.messageService) {
	        this.messageService = new im_v2_provider_service.MessageService({
	          chatId: this.dialog.chatId
	        });
	      }
	      return this.messageService;
	    },
	    loc(phraseCode) {
	      return this.$Bitrix.Loc.getMessage(phraseCode);
	    },
	    onPaste(event) {
	      const files = Object.values(event.clipboardData.files);
	      const imagesOnly = files.filter(file => im_v2_lib_utils.Utils.file.isImage(file.name));
	      if (imagesOnly.length === 0) {
	        return;
	      }
	      event.preventDefault();
	      this.getSendingService().sendFilesFromInput(imagesOnly, this.dialogId);
	    },
	    onMarketIconClick() {
	      this.showMarketApps = !this.showMarketApps;
	      if (this.showMarketApps && this.editMode) {
	        this.onEditPanelClose();
	      }
	    }
	  },
	  template: `
		<div class="bx-im-send-panel__scope bx-im-send-panel__container">
			<div class="bx-im-textarea__container">
				<div @mousedown="onResizeStart" class="bx-im-textarea__drag-handle"></div>
				<EditPanel v-if="editMode" :messageId="editMessageId" @close="onEditPanelClose" />
				<MarketAppsPanel v-if="showMarketApps" :dialogId="dialogId"/>
				<div class="bx-im-textarea__content">
					<div class="bx-im-textarea__left">
						<div class="bx-im-textarea__upload_container">
							<UploadMenu @fileSelect="onFileSelect" @diskFileSelect="onDiskFileSelect" />
						</div>
						<textarea
							v-model="text"
							:style="textareaStyle"
							:placeholder="loc('IM_TEXTAREA_PLACEHOLDER')"
							:maxlength="textareaMaxLength"
							@keydown="onKeyDown"
							@paste="onPaste"
							class="bx-im-textarea__element"
							ref="textarea"
							rows="1"
						></textarea>
					</div>
					<div class="bx-im-textarea__right">
						<div class="bx-im-textarea__action-panel">
							<div 
								:title="loc('IM_TEXTAREA_ICON_APPLICATION')"
								@click="onMarketIconClick"
								class="bx-im-textarea__icon --market"
								:class="{'--active': showMarketApps}"
							></div>
							<CreateEntityMenu :dialogId="dialogId" />
							<SmileSelector :dialogId="dialogId" />
						</div>
					</div>
				</div>
			</div>
			<SendButton :editMode="editMode" :isDisabled="text === ''" @click="sendMessage" />
		</div>
	`
	};

	exports.ChatTextarea = ChatTextarea;

}((this.BX.Messenger.v2.Component = this.BX.Messenger.v2.Component || {}),BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX.Messenger.v2.Provider.Service,BX.Messenger.v2.Lib,BX,BX.Messenger.v2.Application,BX.Event,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX.Vue3.Directives,BX.Messenger.v2.Lib,BX.Messenger.v2.Const,BX,BX.Messenger.v2.Lib,BX.Messenger.v2.Component.Elements));
//# sourceMappingURL=textarea.bundle.js.map
