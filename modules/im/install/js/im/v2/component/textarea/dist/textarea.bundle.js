/* eslint-disable */
this.BX = this.BX || {};
this.BX.Messenger = this.BX.Messenger || {};
this.BX.Messenger.v2 = this.BX.Messenger.v2 || {};
(function (exports,im_v2_lib_localStorage,im_v2_lib_soundNotification,im_v2_lib_channel,rest_client,im_v2_lib_desktopApi,im_v2_lib_smileManager,im_v2_lib_helpdesk,im_v2_lib_rest,calendar_sharing_interface,im_v2_lib_entityCreator,im_v2_lib_analytics,im_v2_lib_feature,im_v2_lib_permission,main_popup,im_v2_lib_draft,im_v2_lib_hotkey,im_v2_lib_textarea,im_v2_provider_service,im_v2_component_message_file,ui_icons,main_core_events,im_v2_lib_textHighlighter,im_v2_lib_logger,im_v2_lib_search,im_v2_lib_utils,im_v2_application_core,im_v2_lib_parser,im_v2_const,main_core,im_v2_lib_market,im_v2_component_elements) {
	'use strict';

	const MentionSymbols = new Set(['@', '+']);
	const WAIT_FOR_NEXT_SYMBOL_TIME = 10;
	const WAIT_FOR_LAST_SYMBOL_TIME = 10;
	const MentionManagerEvents = Object.freeze({
	  showMentionPopup: 'showMentionPopup',
	  hideMentionPopup: 'hideMentionPopup',
	  insertMention: 'insertMention'
	});
	var _mentionPopupOpened = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("mentionPopupOpened");
	var _mentionSymbol = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("mentionSymbol");
	var _textarea = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("textarea");
	var _mentionReplacementMap = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("mentionReplacementMap");
	var _onClosedMentionKeyDown = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("onClosedMentionKeyDown");
	var _onOpenedMentionKeyDown = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("onOpenedMentionKeyDown");
	var _checkMentionSymbol = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("checkMentionSymbol");
	var _isValidQuery = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("isValidQuery");
	var _isCloseMentionCombination = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("isCloseMentionCombination");
	var _isInsertMentionCombination = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("isInsertMentionCombination");
	var _isOpenMentionCombination = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("isOpenMentionCombination");
	var _sendHidePopupEvent = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("sendHidePopupEvent");
	var _sendInsertMentionEvent = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("sendInsertMentionEvent");
	var _getTextBeforeCursor = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getTextBeforeCursor");
	var _getMentionSymbolIndex = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getMentionSymbolIndex");
	var _getQuery = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getQuery");
	var _getQueryWithoutMentionSymbol = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getQueryWithoutMentionSymbol");
	var _isNavigateCombination = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("isNavigateCombination");
	var _isValidFirstSymbol = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("isValidFirstSymbol");
	var _hasWhitespace = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("hasWhitespace");
	var _hasNumber = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("hasNumber");
	var _hasSpecialSymbol = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("hasSpecialSymbol");
	class MentionManager extends main_core_events.EventEmitter {
	  constructor(textarea) {
	    super();
	    Object.defineProperty(this, _hasSpecialSymbol, {
	      value: _hasSpecialSymbol2
	    });
	    Object.defineProperty(this, _hasNumber, {
	      value: _hasNumber2
	    });
	    Object.defineProperty(this, _hasWhitespace, {
	      value: _hasWhitespace2
	    });
	    Object.defineProperty(this, _isValidFirstSymbol, {
	      value: _isValidFirstSymbol2
	    });
	    Object.defineProperty(this, _isNavigateCombination, {
	      value: _isNavigateCombination2
	    });
	    Object.defineProperty(this, _getQueryWithoutMentionSymbol, {
	      value: _getQueryWithoutMentionSymbol2
	    });
	    Object.defineProperty(this, _getQuery, {
	      value: _getQuery2
	    });
	    Object.defineProperty(this, _getMentionSymbolIndex, {
	      value: _getMentionSymbolIndex2
	    });
	    Object.defineProperty(this, _getTextBeforeCursor, {
	      value: _getTextBeforeCursor2
	    });
	    Object.defineProperty(this, _sendInsertMentionEvent, {
	      value: _sendInsertMentionEvent2
	    });
	    Object.defineProperty(this, _sendHidePopupEvent, {
	      value: _sendHidePopupEvent2
	    });
	    Object.defineProperty(this, _isOpenMentionCombination, {
	      value: _isOpenMentionCombination2
	    });
	    Object.defineProperty(this, _isInsertMentionCombination, {
	      value: _isInsertMentionCombination2
	    });
	    Object.defineProperty(this, _isCloseMentionCombination, {
	      value: _isCloseMentionCombination2
	    });
	    Object.defineProperty(this, _isValidQuery, {
	      value: _isValidQuery2
	    });
	    Object.defineProperty(this, _checkMentionSymbol, {
	      value: _checkMentionSymbol2
	    });
	    Object.defineProperty(this, _onOpenedMentionKeyDown, {
	      value: _onOpenedMentionKeyDown2
	    });
	    Object.defineProperty(this, _onClosedMentionKeyDown, {
	      value: _onClosedMentionKeyDown2
	    });
	    Object.defineProperty(this, _mentionPopupOpened, {
	      writable: true,
	      value: false
	    });
	    Object.defineProperty(this, _mentionSymbol, {
	      writable: true,
	      value: ''
	    });
	    Object.defineProperty(this, _textarea, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _mentionReplacementMap, {
	      writable: true,
	      value: {}
	    });
	    this.setEventNamespace(MentionManager.eventNamespace);
	    babelHelpers.classPrivateFieldLooseBase(this, _textarea)[_textarea] = textarea;
	  }

	  // region 'popup'
	  onActiveMentionKeyDown(event) {
	    if (!babelHelpers.classPrivateFieldLooseBase(this, _mentionPopupOpened)[_mentionPopupOpened]) {
	      return;
	    }
	    babelHelpers.classPrivateFieldLooseBase(this, _onOpenedMentionKeyDown)[_onOpenedMentionKeyDown](event);
	  }
	  onKeyDown(event) {
	    babelHelpers.classPrivateFieldLooseBase(this, _onClosedMentionKeyDown)[_onClosedMentionKeyDown](event);
	  }
	  onMentionPopupClose() {
	    babelHelpers.classPrivateFieldLooseBase(this, _mentionPopupOpened)[_mentionPopupOpened] = false;
	  }
	  // endregion 'popup'

	  // region 'replace'
	  setMentionReplacements(mentionsMap) {
	    babelHelpers.classPrivateFieldLooseBase(this, _mentionReplacementMap)[_mentionReplacementMap] = mentionsMap;
	  }
	  addMentionReplacement(textToReplace, textToInsert) {
	    babelHelpers.classPrivateFieldLooseBase(this, _mentionReplacementMap)[_mentionReplacementMap][textToReplace] = textToInsert;
	    return babelHelpers.classPrivateFieldLooseBase(this, _mentionReplacementMap)[_mentionReplacementMap];
	  }
	  getMentionSymbol() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _mentionSymbol)[_mentionSymbol];
	  }
	  replaceMentions(text) {
	    let resultText = text;
	    Object.entries(babelHelpers.classPrivateFieldLooseBase(this, _mentionReplacementMap)[_mentionReplacementMap]).forEach(([textToReplace, textToInsert]) => {
	      resultText = resultText.replaceAll(textToReplace, textToInsert);
	    });
	    return resultText;
	  }
	  extractMentions(text) {
	    const CHAT_MENTION_CODE = 'chat';
	    const mentions = {};
	    const mentionRegExp = /\[(?<type>user|chat)=(?<dialogId>\w+)](?<mentionText>.*?)\[\/(user|chat)]/gi;
	    const matches = text.matchAll(mentionRegExp);
	    for (const match of matches) {
	      const {
	        mentionText
	      } = match.groups;
	      let {
	        type: mentionType,
	        dialogId
	      } = match.groups;
	      mentionType = mentionType.toLowerCase();
	      if (mentionType === CHAT_MENTION_CODE) {
	        dialogId = `${mentionType}${dialogId}`;
	      }
	      mentions[mentionText] = im_v2_lib_utils.Utils.text.getMentionBbCode(dialogId, mentionText);
	    }
	    return mentions;
	  }
	  clearMentionSymbol() {
	    babelHelpers.classPrivateFieldLooseBase(this, _mentionSymbol)[_mentionSymbol] = '';
	  }
	  clearMentionReplacements() {
	    babelHelpers.classPrivateFieldLooseBase(this, _mentionReplacementMap)[_mentionReplacementMap] = {};
	  }
	  // endregion 'replace'
	}
	function _onClosedMentionKeyDown2(event) {
	  if (!babelHelpers.classPrivateFieldLooseBase(this, _isOpenMentionCombination)[_isOpenMentionCombination](event)) {
	    return;
	  }
	  setTimeout(() => {
	    if (!babelHelpers.classPrivateFieldLooseBase(this, _checkMentionSymbol)[_checkMentionSymbol]()) {
	      return;
	    }
	    babelHelpers.classPrivateFieldLooseBase(this, _mentionPopupOpened)[_mentionPopupOpened] = true;
	    this.emit(MentionManagerEvents.showMentionPopup, {
	      mentionQuery: ''
	    });
	  }, WAIT_FOR_NEXT_SYMBOL_TIME);
	}
	function _onOpenedMentionKeyDown2(event) {
	  if (babelHelpers.classPrivateFieldLooseBase(this, _isCloseMentionCombination)[_isCloseMentionCombination](event)) {
	    babelHelpers.classPrivateFieldLooseBase(this, _sendHidePopupEvent)[_sendHidePopupEvent]();
	    return;
	  }
	  if (babelHelpers.classPrivateFieldLooseBase(this, _isNavigateCombination)[_isNavigateCombination](event)) {
	    event.preventDefault();
	    return;
	  }
	  if (babelHelpers.classPrivateFieldLooseBase(this, _isInsertMentionCombination)[_isInsertMentionCombination](event)) {
	    event.preventDefault();
	    babelHelpers.classPrivateFieldLooseBase(this, _sendInsertMentionEvent)[_sendInsertMentionEvent](event);
	    return;
	  }
	  setTimeout(() => {
	    if (!babelHelpers.classPrivateFieldLooseBase(this, _isValidQuery)[_isValidQuery]()) {
	      babelHelpers.classPrivateFieldLooseBase(this, _sendHidePopupEvent)[_sendHidePopupEvent]();
	      return;
	    }
	    this.emit(MentionManagerEvents.showMentionPopup, {
	      mentionQuery: babelHelpers.classPrivateFieldLooseBase(this, _getQueryWithoutMentionSymbol)[_getQueryWithoutMentionSymbol]()
	    });
	  }, WAIT_FOR_LAST_SYMBOL_TIME);
	}
	function _checkMentionSymbol2() {
	  const cursorPosition = babelHelpers.classPrivateFieldLooseBase(this, _textarea)[_textarea].selectionEnd;
	  babelHelpers.classPrivateFieldLooseBase(this, _mentionSymbol)[_mentionSymbol] = babelHelpers.classPrivateFieldLooseBase(this, _textarea)[_textarea].value.slice(cursorPosition - 1, cursorPosition);
	  if (!MentionSymbols.has(babelHelpers.classPrivateFieldLooseBase(this, _mentionSymbol)[_mentionSymbol])) {
	    return false;
	  }
	  const symbolBeforeMentionSymbol = babelHelpers.classPrivateFieldLooseBase(this, _textarea)[_textarea].value.slice(cursorPosition - 2, cursorPosition - 1);
	  return symbolBeforeMentionSymbol.length === 0 || babelHelpers.classPrivateFieldLooseBase(this, _hasWhitespace)[_hasWhitespace](symbolBeforeMentionSymbol);
	}
	function _isValidQuery2() {
	  const query = babelHelpers.classPrivateFieldLooseBase(this, _getQuery)[_getQuery]();
	  if (query.length === 0) {
	    return false;
	  }
	  const firstQuerySymbol = babelHelpers.classPrivateFieldLooseBase(this, _getQueryWithoutMentionSymbol)[_getQueryWithoutMentionSymbol]().slice(0, 1);
	  if (firstQuerySymbol.length === 0) {
	    return true;
	  }
	  if (!babelHelpers.classPrivateFieldLooseBase(this, _isValidFirstSymbol)[_isValidFirstSymbol](firstQuerySymbol)) {
	    return false;
	  }
	  return !babelHelpers.classPrivateFieldLooseBase(this, _hasWhitespace)[_hasWhitespace](firstQuerySymbol);
	}
	function _isCloseMentionCombination2(event) {
	  return event.key === 'Escape';
	}
	function _isInsertMentionCombination2(event) {
	  return event.key === 'Enter';
	}
	function _isOpenMentionCombination2(event) {
	  return event.key === '+' || event.key === '@';
	}
	function _sendHidePopupEvent2() {
	  babelHelpers.classPrivateFieldLooseBase(this, _mentionPopupOpened)[_mentionPopupOpened] = false;
	  babelHelpers.classPrivateFieldLooseBase(this, _mentionSymbol)[_mentionSymbol] = '';
	  this.emit(MentionManagerEvents.hideMentionPopup);
	}
	function _sendInsertMentionEvent2(event) {
	  event.preventDefault();
	  main_core_events.EventEmitter.emit(im_v2_const.EventType.mention.selectItem);
	  babelHelpers.classPrivateFieldLooseBase(this, _sendHidePopupEvent)[_sendHidePopupEvent]();
	}
	function _getTextBeforeCursor2() {
	  return babelHelpers.classPrivateFieldLooseBase(this, _textarea)[_textarea].value.slice(0, Math.max(0, babelHelpers.classPrivateFieldLooseBase(this, _textarea)[_textarea].selectionEnd));
	}
	function _getMentionSymbolIndex2() {
	  const textBeforeCursor = babelHelpers.classPrivateFieldLooseBase(this, _getTextBeforeCursor)[_getTextBeforeCursor]();
	  return textBeforeCursor.lastIndexOf(babelHelpers.classPrivateFieldLooseBase(this, _mentionSymbol)[_mentionSymbol]);
	}
	function _getQuery2() {
	  const textBeforeCursor = babelHelpers.classPrivateFieldLooseBase(this, _getTextBeforeCursor)[_getTextBeforeCursor]();
	  const mentionSymbolIndex = babelHelpers.classPrivateFieldLooseBase(this, _getMentionSymbolIndex)[_getMentionSymbolIndex]();
	  if (mentionSymbolIndex < 0) {
	    return '';
	  }
	  return textBeforeCursor.slice(mentionSymbolIndex, babelHelpers.classPrivateFieldLooseBase(this, _textarea)[_textarea].selectionEnd);
	}
	function _getQueryWithoutMentionSymbol2() {
	  return babelHelpers.classPrivateFieldLooseBase(this, _getQuery)[_getQuery]().slice(1);
	}
	function _isNavigateCombination2(event) {
	  return event.key === 'ArrowUp' || event.key === 'ArrowDown';
	}
	function _isValidFirstSymbol2(firstQuerySymbol) {
	  return !(babelHelpers.classPrivateFieldLooseBase(this, _hasNumber)[_hasNumber](firstQuerySymbol) || babelHelpers.classPrivateFieldLooseBase(this, _hasWhitespace)[_hasWhitespace](firstQuerySymbol) || babelHelpers.classPrivateFieldLooseBase(this, _hasSpecialSymbol)[_hasSpecialSymbol](firstQuerySymbol));
	}
	function _hasWhitespace2(text) {
	  return /^\s/.test(text);
	}
	function _hasNumber2(text) {
	  return /\d$/.test(text);
	}
	function _hasSpecialSymbol2(text) {
	  const regex = /[!"#$%&'()*+,./<>@\\^_|-]/;
	  return regex.test(text);
	}
	MentionManager.eventNamespace = 'BX.Messenger.v2.Textarea.MentionManager';

	const ResizeDirection = {
	  up: 'up',
	  down: 'down'
	};
	var _calculateNewMaxPoint = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("calculateNewMaxPoint");
	class ResizeManager extends main_core_events.EventEmitter {
	  constructor(options = {}) {
	    super();
	    Object.defineProperty(this, _calculateNewMaxPoint, {
	      value: _calculateNewMaxPoint2
	    });
	    this.isDragging = false;
	    const {
	      direction,
	      maxHeight,
	      minHeight
	    } = options;
	    this.direction = direction;
	    this.maxHeight = maxHeight;
	    this.minHeight = minHeight;
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
	    const maxPoint = babelHelpers.classPrivateFieldLooseBase(this, _calculateNewMaxPoint)[_calculateNewMaxPoint]();
	    const newHeight = Math.max(maxPoint, this.minHeight);
	    this.emit(ResizeManager.events.onHeightChange, {
	      newHeight
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
	function _calculateNewMaxPoint2() {
	  const distance = this.direction === ResizeDirection.up ? this.resizeCursorStartPoint - this.resizeCursorControlPoint : this.resizeCursorControlPoint - this.resizeCursorStartPoint;
	  return Math.min(this.resizeHeightStartPoint + distance, this.maxHeight);
	}
	ResizeManager.eventNamespace = 'BX.Messenger.v2.Textarea.ResizeManager';
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

	const RecognizerEvent = {
	  audioend: 'audioend',
	  audiostart: 'audiostart',
	  end: 'end',
	  error: 'error',
	  nomatch: 'nomatch',
	  result: 'result',
	  soundend: 'soundend',
	  soundstart: 'soundstart',
	  speechend: 'speechend',
	  speechstart: 'speechstart',
	  start: 'start'
	};
	const EVENT_NAMESPACE = 'BX.Messenger.v2.CopilotAudioManager';
	var _bindEvents = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("bindEvents");
	var _getRecognizedText = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getRecognizedText");
	var _getNewText = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getNewText");
	var _initSettings = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("initSettings");
	class AudioManager extends main_core_events.EventEmitter {
	  static isAvailable() {
	    if (im_v2_lib_desktopApi.DesktopApi.isDesktop()) {
	      return im_v2_lib_desktopApi.DesktopApi.getApiVersion() > 74;
	    }
	    return Boolean(window.SpeechRecognition || window.webkitSpeechRecognition);
	  }
	  constructor() {
	    super();
	    Object.defineProperty(this, _initSettings, {
	      value: _initSettings2
	    });
	    Object.defineProperty(this, _getNewText, {
	      value: _getNewText2
	    });
	    Object.defineProperty(this, _getRecognizedText, {
	      value: _getRecognizedText2
	    });
	    Object.defineProperty(this, _bindEvents, {
	      value: _bindEvents2
	    });
	    this.recognizer = null;
	    this.setEventNamespace(EVENT_NAMESPACE);
	    this.recognizer = new (window.SpeechRecognition || window.webkitSpeechRecognition)();
	    babelHelpers.classPrivateFieldLooseBase(this, _initSettings)[_initSettings]();
	    babelHelpers.classPrivateFieldLooseBase(this, _bindEvents)[_bindEvents]();
	  }
	  startRecognition() {
	    this.recognizer.start();
	  }
	  stopRecognition() {
	    this.recognizer.stop();
	  }
	}
	function _bindEvents2() {
	  main_core.Event.bind(this.recognizer, RecognizerEvent.start, () => {
	    this.lastRecognizedText = '';
	    this.emit(AudioManager.events.recognitionStart);
	  });
	  main_core.Event.bind(this.recognizer, RecognizerEvent.error, event => {
	    this.emit(AudioManager.events.recognitionError, event.error);
	    // eslint-disable-next-line no-console
	    console.error('Copilot: AudioManager: error', event.error);
	  });
	  main_core.Event.bind(this.recognizer, RecognizerEvent.end, () => {
	    this.lastRecognizedText = '';
	    this.emit(AudioManager.events.recognitionEnd);
	  });
	  main_core.Event.bind(this.recognizer, RecognizerEvent.result, event => {
	    const recognizedText = babelHelpers.classPrivateFieldLooseBase(this, _getRecognizedText)[_getRecognizedText](event);
	    const newText = babelHelpers.classPrivateFieldLooseBase(this, _getNewText)[_getNewText](recognizedText);
	    if (newText !== '') {
	      this.emit(AudioManager.events.recognitionResult, newText);
	    }
	    this.lastRecognizedText = recognizedText;
	  });
	}
	function _getRecognizedText2(event) {
	  let recognizedChunk = '';
	  Object.values(event.results).forEach(result => {
	    if (result.isFinal) {
	      return;
	    }
	    const [alternative] = result;
	    const {
	      transcript
	    } = alternative;
	    recognizedChunk += transcript;
	  });
	  return recognizedChunk;
	}
	function _getNewText2(fullText) {
	  let additionalText = '';
	  const lastChunkLength = this.lastRecognizedText.length;
	  if (fullText.length > lastChunkLength) {
	    additionalText = fullText.slice(lastChunkLength);
	  }
	  return additionalText;
	}
	function _initSettings2() {
	  this.recognizer.continuous = true;
	  this.recognizer.interimResults = true;
	}
	AudioManager.events = {
	  recognitionStart: 'recognitionStart',
	  recognitionError: 'recognitionError',
	  recognitionEnd: 'recognitionEnd',
	  recognitionResult: 'recognitionResult'
	};

	// @vue/component
	const AudioInput = {
	  name: 'AudioInput',
	  emits: ['inputStart', 'inputResult'],
	  data() {
	    return {
	      audioMode: false,
	      audioUsed: false
	    };
	  },
	  watch: {
	    audioMode(newValue, oldValue) {
	      if (oldValue === false && newValue === true) {
	        this.startAudio();
	      }
	      if (oldValue === true && newValue === false) {
	        this.stopAudio();
	      }
	    }
	  },
	  created() {
	    main_core_events.EventEmitter.subscribe(im_v2_const.EventType.textarea.onAfterSendMessage, this.handleOnAfterSendMessage);
	  },
	  beforeUnmount() {
	    main_core_events.EventEmitter.unsubscribe(im_v2_const.EventType.textarea.onAfterSendMessage, this.handleOnAfterSendMessage);
	  },
	  methods: {
	    onClick() {
	      if (this.audioMode) {
	        this.audioMode = false;
	        return;
	      }
	      this.audioMode = true;
	    },
	    startAudio() {
	      this.getAudioManager().startRecognition();
	      this.bindAudioEvents();
	    },
	    stopAudio() {
	      this.getAudioManager().stopRecognition();
	      this.unbindAudioEvents();
	    },
	    bindAudioEvents() {
	      this.getAudioManager().subscribe(AudioManager.events.recognitionResult, event => {
	        const text = event.getData();
	        this.$emit('inputResult', text);
	        this.audioUsed = true;
	      });
	      this.getAudioManager().subscribe(AudioManager.events.recognitionStart, () => {
	        this.$emit('inputStart');
	      });
	      this.getAudioManager().subscribe(AudioManager.events.recognitionEnd, () => {
	        this.audioMode = false;
	      });
	      this.getAudioManager().subscribe(AudioManager.events.recognitionError, () => {
	        this.audioMode = false;
	        BX.UI.Notification.Center.notify({
	          content: this.loc('IM_TEXTAREA_AUDIO_INPUT_ERROR')
	        });
	      });
	    },
	    unbindAudioEvents() {
	      this.getAudioManager().unsubscribeAll(AudioManager.events.recognitionResult);
	      this.getAudioManager().unsubscribeAll(AudioManager.events.recognitionStart);
	      this.getAudioManager().unsubscribeAll(AudioManager.events.recognitionEnd);
	      this.getAudioManager().unsubscribeAll(AudioManager.events.recognitionError);
	    },
	    isAudioModeAvailable() {
	      return AudioManager.isAvailable();
	    },
	    getAudioManager() {
	      if (!this.audioManager) {
	        this.audioManager = new AudioManager();
	      }
	      return this.audioManager;
	    },
	    loc(phraseCode) {
	      return this.$Bitrix.Loc.getMessage(phraseCode);
	    },
	    handleOnAfterSendMessage() {
	      if (this.audioUsed) {
	        im_v2_lib_analytics.Analytics.getInstance().copilot.onUseAudioInput();
	        this.audioUsed = false;
	      }
	      this.audioMode = false;
	    }
	  },
	  template: `
		<div
			v-if="isAudioModeAvailable()"
			@click="onClick"
			class="bx-im-copilot-audio-input__container"
			:class="{'--active': audioMode}"
		></div>
	`
	};

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
	const TabSmiles = {
	  name: 'SmilesContent',
	  props: {
	    dialogId: {
	      type: String,
	      required: true
	    }
	  },
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
	        text: smileCode,
	        dialogId: this.dialogId
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

	const PAGE_SIZE = 15;
	class GifService {
	  constructor() {
	    this.pageNumber = 1;
	    this.hasMoreItemsToLoad = true;
	  }
	  getPopular() {
	    return im_v2_lib_rest.runAction(im_v2_const.RestMethod.imBotGiphyListPopular, {}).catch(error => {
	      im_v2_lib_logger.Logger.error('GiphyLoadService error', error);
	    });
	  }
	  getQuery(searchQuery, nextPage) {
	    if (nextPage) {
	      this.pageNumber++;
	    } else {
	      this.pageNumber = 1;
	      this.hasMoreItemsToLoad = true;
	    }
	    return im_v2_lib_rest.runAction(im_v2_const.RestMethod.imBotGiphyList, {
	      data: {
	        filter: {
	          search: searchQuery
	        },
	        limit: PAGE_SIZE,
	        offset: this.pageNumber * PAGE_SIZE
	      }
	    }).then(gifs => {
	      if (gifs.length < PAGE_SIZE) {
	        this.hasMoreItemsToLoad = false;
	      }
	      return gifs;
	    }).catch(error => {
	      im_v2_lib_logger.Logger.error('GiphyLoadService error', error);
	    });
	  }
	}

	const UrlTag = Object.freeze({
	  open: '[url]',
	  close: '[/url]'
	});

	// @vue/component
	const TabGiphy = {
	  name: 'GiphyContent',
	  components: {
	    Loader: im_v2_component_elements.Loader,
	    Spinner: im_v2_component_elements.Spinner
	  },
	  props: {
	    dialogId: {
	      type: String,
	      required: true
	    }
	  },
	  emits: ['close'],
	  data() {
	    return {
	      searchQuery: '',
	      gifList: [],
	      popularGifList: [],
	      isSearching: false,
	      isLoading: false,
	      isError: false,
	      needBottomShadow: true
	    };
	  },
	  computed: {
	    itemsReceived() {
	      return this.popularGifList.length > 0;
	    },
	    SpinnerSize: () => im_v2_component_elements.SpinnerSize,
	    SpinnerColor: () => im_v2_component_elements.SpinnerColor,
	    errorText() {
	      if (this.gifList.length === 0) {
	        return this.loc('IM_TEXTAREA_GIPHY_EMPTY_STATE');
	      }
	      if (this.isError) {
	        return this.loc('IM_TEXTAREA_GIPHY_UNAVAILABLE_STATE');
	      }
	      return '';
	    },
	    errorClass() {
	      return this.gifList.length === 0 || this.isError ? '--is-error' : '';
	    },
	    showInputClearButton() {
	      return this.searchQuery.length > 0 && !this.isSearching;
	    },
	    trimmedQuery() {
	      return this.searchQuery.trim();
	    }
	  },
	  created() {
	    this.loadPopular();
	    this.loadQueryWithDebounce = main_core.Runtime.debounce(this.loadQueryList, 500, this);
	  },
	  methods: {
	    handleResponse(gifs) {
	      this.isSearching = false;
	      this.scrollToTop();
	      this.gifList = gifs.length > 0 ? gifs : [];
	    },
	    loadPopular() {
	      this.getGifService().getPopular().then(gifs => {
	        this.popularGifList = gifs.length > 0 ? gifs : [];
	        this.handleResponse(this.popularGifList);
	      }).catch(() => {
	        this.isError = true;
	      });
	    },
	    loadQueryList(query, nextPage) {
	      this.getGifService().getQuery(query, nextPage).then(gifs => {
	        this.handleResponse(gifs);
	      }).catch(() => {
	        this.isError = true;
	      });
	    },
	    loc(phraseCode) {
	      return this.$Bitrix.Loc.getMessage(phraseCode);
	    },
	    getGifService() {
	      if (!this.giphyLoader) {
	        this.giphyLoader = new GifService();
	      }
	      return this.giphyLoader;
	    },
	    getSendingService() {
	      if (!this.sendingService) {
	        this.sendingService = im_v2_provider_service.SendingService.getInstance();
	      }
	      return this.sendingService;
	    },
	    onGifClick(item) {
	      const text = `${UrlTag.open}${item.original}${UrlTag.close}`;
	      this.getSendingService().sendMessage({
	        text,
	        dialogId: this.dialogId
	      });
	      this.$emit('close');
	    },
	    onInputUpdate() {
	      if (this.trimmedQuery.length >= 3) {
	        this.isSearching = true;
	        this.loadQueryWithDebounce(this.trimmedQuery, false);
	      }
	      if (this.trimmedQuery.length === 0) {
	        this.gifList = this.popularGifList;
	      }
	    },
	    onInputClearClick() {
	      this.searchQuery = '';
	      this.scrollToTop();
	      this.onInputUpdate();
	    },
	    onEnterKeyPress() {
	      if (this.gifList.length > 0 && !this.isSearching) {
	        const firstGif = this.gifList[0];
	        this.onGifClick(firstGif);
	      }
	    },
	    needToLoadNextPage(event) {
	      return event.target.scrollTop + event.target.clientHeight >= event.target.scrollHeight - event.target.clientHeight;
	    },
	    onScroll(event) {
	      this.needBottomShadow = event.target.scrollTop + event.target.clientHeight !== event.target.scrollHeight;
	      if (this.isLoading) {
	        return;
	      }
	      if (this.trimmedQuery.length === 0) {
	        return;
	      }
	      if (!this.needToLoadNextPage(event) || !this.getGifService().hasMoreItemsToLoad) {
	        return;
	      }
	      this.isLoading = true;
	      this.getGifService().getQuery(this.trimmedQuery, true).then(gifs => {
	        this.isLoading = false;
	        this.gifList.push(...gifs);
	      }).catch(() => {
	        this.isLoading = false;
	        this.isError = true;
	      });
	    },
	    scrollToTop() {
	      const scrollContainer = this.$refs.gifsContainer;
	      if (scrollContainer) {
	        scrollContainer.scrollTop = 0;
	      }
	    },
	    openHelpArticle() {
	      const ARTICLE_CODE = '17942324';
	      im_v2_lib_helpdesk.openHelpdeskArticle(ARTICLE_CODE);
	    }
	  },
	  template: `
		<div class="bx-im-smiles-content__scope bx-im-smile-popup-giphy-content__container">
			<div 
				v-if="!itemsReceived" 
				class="bx-im-smiles-content-popup__loader"
			>
				<Spinner :color="SpinnerColor.blue" :size="SpinnerSize.S" />
			</div>
			<template v-else>
				<div 
					v-if="itemsReceived"
				 	class="bx-im-smile-popup-search-input__container"
				>
					<div class="bx-im-smile-popup-giphy-content__search-icon"></div>
					<input
						@input="onInputUpdate"
						@keydown.enter="onEnterKeyPress"
						v-model="searchQuery"
						class="bx-im-smile-popup-giphy-content__input bx-im-smile-popup-search-input__element"
						:placeholder="loc('IM_TEXTAREA_GIPHY_INPUT_PLACEHOLDER')"
					/>
					<div
						v-if="showInputClearButton"
						class="bx-im-smile-popup-search-input__clear"
						@click="onInputClearClick"
					 ></div>
					<div v-show="isSearching" class="bx-im-smile-popup-search-input__loader">
						<Spinner :color="SpinnerColor.grey" :size="SpinnerSize.XXS" />
					</div>
				</div>
				<div 
					class="bx-im-smiles-content__smiles-box bx-im-smiles-content__gifs-box"
				 	:class="errorClass"
				 	ref="gifsContainer"
					@scroll="onScroll"
				>
					<div 
						v-if="gifList.length === 0" 
						class="bx-im-smiles-content__gifs-empty"
					>
						<div class="bx-im-smiles-content__gifs-empty_icon bx-im-messenger__search-icon --size-large"></div>
						<div class="bx-im-smiles-content__gifs-empty_title">
							{{ errorText }}
						</div>
					</div>
					<div 
						v-else-if="isError" 
						class="bx-im-smiles-content__gifs-empty"
					>
						<div 
							v-if="isError" 
							class="bx-im-smiles-content__gifs-warning_icon"
						></div>
						<div class="bx-im-smiles-content__gifs-empty_title">
							{{ errorText }}
						</div>
						<div @click="openHelpArticle" class="bx-im-smiles-content__gifs-empty_link">
							{{ loc('IM_TEXTAREA_GIPHY_MORE') }}
						</div>
					</div>
					<template v-else>
						<div v-for="item in gifList" class="bx-im-smiles-content__gifs-item" :key="item.preview">
							<img @click="onGifClick(item)" class="bx-im-smiles-content__gifs-item_img"
								 :src="item.preview"
								 :data-original="item.original" alt="gif"
							>
						</div>
					</template>
					<div :class="needBottomShadow ? '' : '--is-hidden'" class="bx-im-smiles-content__gifs-gradient"></div>
					<Loader v-show="isLoading && !isError" class="bx-im-sidebar-detail__loader-container" />
				</div>
			</template>
		</div>
	`
	};

	// @vue/component
	const TabMarket = {
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
			<div 
				v-show="!isLoading"
				class="bx-im-smile-popup-market-content__placement-container"
				ref="im-messenger-smile-selector-placement"
			></div>
		</div>
	`
	};

	const TabType = Object.freeze({
	  default: 'default',
	  market: 'market',
	  giphy: 'giphy'
	});
	// @vue/component
	const SmilePopup = {
	  name: 'SmilePopup',
	  components: {
	    MessengerPopup: im_v2_component_elements.MessengerPopup,
	    TabSmiles,
	    TabGiphy,
	    TabMarket,
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
	    isGiphyAvailable() {
	      return im_v2_lib_feature.FeatureManager.isFeatureAvailable(im_v2_lib_feature.Feature.giphyAvailable);
	    },
	    tabs() {
	      const tabs = [this.smilesTab];
	      if (this.isGiphyAvailable) {
	        tabs.push(this.giphyTab);
	      }
	      return [...tabs, ...this.marketTabs];
	    },
	    smilesTab() {
	      return {
	        id: 1,
	        title: this.$Bitrix.Loc.getMessage('IM_TEXTAREA_SMILE_SELECTOR_SMILES_TAB'),
	        type: TabType.default
	      };
	    },
	    giphyTab() {
	      return {
	        id: 1,
	        title: 'Giphy',
	        type: TabType.giphy
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
				<TabSmiles v-show="currentTab === TabType.default" :dialogId="dialogId" @close="$emit('close')" />
				<TabGiphy v-if="isGiphyAvailable" v-show="currentTab === TabType.giphy" @close="$emit('close')" :dialogId="dialogId" />
				<TabMarket v-if="currentTab === TabType.market" :entityId="currentEntityId" :dialogId="dialogId" />
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

	const DOCUMENT_SIGN_SLIDER_URL = '/sign/doc/0/?chat_id=';

	// @vue/component
	const UploadMenu = {
	  components: {
	    MessengerMenu: im_v2_component_elements.MessengerMenu,
	    MenuItem: im_v2_component_elements.MenuItem,
	    DiskPopup
	  },
	  props: {
	    dialogId: {
	      type: String,
	      required: true
	    }
	  },
	  emits: ['fileSelect', 'diskFileSelect'],
	  data() {
	    return {
	      showMenu: false,
	      showDiskPopup: false
	    };
	  },
	  computed: {
	    menuItems() {
	      return [{
	        icon: im_v2_component_elements.MenuItemIcon.file,
	        title: this.loc('IM_TEXTAREA_SELECT_LOCAL_FILE'),
	        clickHandler: this.onSelectLocalFile
	      }, {
	        icon: im_v2_component_elements.MenuItemIcon.b24,
	        title: this.loc('IM_TEXTAREA_SELECT_FILE_FROM_B24'),
	        clickHandler: this.onSelectFromB24
	      }, {
	        icon: im_v2_component_elements.MenuItemIcon.task,
	        title: this.loc('IM_TEXTAREA_SELECT_TASK'),
	        clickHandler: this.onCreateTaskClick
	      }, {
	        icon: im_v2_component_elements.MenuItemIcon.meeting,
	        title: this.loc('IM_TEXTAREA_SELECT_MEETING'),
	        clickHandler: this.onCreateMeetingClick
	      }, {
	        icon: im_v2_component_elements.MenuItemIcon.calendarSlot,
	        title: this.loc('IM_TEXTAREA_SELECT_CALENDAR_SLOT'),
	        clickHandler: this.onCreateCalendarSlotClick,
	        showCondition: () => this.isCalendarSlotAvailable
	      }, {
	        icon: im_v2_component_elements.MenuItemIcon.documentSign,
	        title: this.loc('IM_TEXTAREA_SELECT_DOCUMENT_SIGN'),
	        clickHandler: this.onCreateDocumentSignClick,
	        showCondition: () => this.isDocumentSignAvailable
	      }, {
	        icon: im_v2_component_elements.MenuItemIcon.vote,
	        title: this.loc('IM_TEXTAREA_SELECT_VOTE'),
	        clickHandler: this.onCreateVoteClick,
	        showCondition: () => this.isVoteCreationAvailable
	      }];
	    },
	    availableMenuItems() {
	      return this.menuItems.filter(item => {
	        if (!main_core.Type.isFunction(item.showCondition)) {
	          return true;
	        }
	        return item.showCondition();
	      });
	    },
	    menuConfig() {
	      return {
	        width: 278,
	        bindElement: this.$refs.upload || {},
	        bindOptions: {
	          position: 'top'
	        },
	        offsetTop: 30,
	        offsetLeft: -10,
	        padding: 0
	      };
	    },
	    dialog() {
	      return this.$store.getters['chats/get'](this.dialogId, true);
	    },
	    chatId() {
	      return this.dialog.chatId;
	    },
	    isDocumentSignAvailable() {
	      const isActiveFeature = im_v2_lib_feature.FeatureManager.isFeatureAvailable(im_v2_lib_feature.Feature.documentSignAvailable);
	      if (!isActiveFeature) {
	        return false;
	      }
	      return im_v2_lib_permission.PermissionManager.getInstance().canPerformActionByRole(im_v2_const.ActionByRole.createDocumentSign, this.dialogId);
	    },
	    isCalendarSlotAvailable() {
	      return im_v2_lib_permission.PermissionManager.getInstance().canPerformActionByRole(im_v2_const.ActionByRole.createCalendarSlots, this.dialogId);
	    },
	    isVoteCreationAvailable() {
	      return im_v2_lib_feature.FeatureManager.isFeatureAvailable(im_v2_lib_feature.Feature.voteCreationAvailable);
	    }
	  },
	  methods: {
	    onSelectLocalFile() {
	      this.$refs.fileInput.click();
	      this.showMenu = false;
	    },
	    onSelectFromB24() {
	      this.showDiskPopup = true;
	      this.showMenu = false;
	    },
	    onFileSelect(event) {
	      this.$emit('fileSelect', {
	        event
	      });
	      this.showMenu = false;
	    },
	    onDiskFileSelect(event) {
	      this.$emit('diskFileSelect', event);
	    },
	    loc(phraseCode) {
	      return this.$Bitrix.Loc.getMessage(phraseCode);
	    },
	    getEntityCreator() {
	      if (!this.entityCreator) {
	        this.entityCreator = new im_v2_lib_entityCreator.EntityCreator(this.chatId);
	      }
	      return this.entityCreator;
	    },
	    onCreateTaskClick() {
	      void this.getEntityCreator().createTaskForChat();
	      im_v2_lib_analytics.Analytics.getInstance().chatEntities.onCreateTaskFromTextareaClick(this.dialogId);
	      this.showMenu = false;
	    },
	    onCreateMeetingClick() {
	      void this.getEntityCreator().createMeetingForChat();
	      im_v2_lib_analytics.Analytics.getInstance().chatEntities.onCreateEventFromTextareaClick(this.dialogId);
	      this.showMenu = false;
	    },
	    onUploadButtonClick() {
	      if (this.showMenu !== true) {
	        im_v2_lib_analytics.Analytics.getInstance().attachMenu.onOpenUploadMenu(this.dialogId);
	      }
	      this.showMenu = true;
	    },
	    async onCreateCalendarSlotClick(event) {
	      if (!calendar_sharing_interface.GroupSharingController) {
	        return;
	      }
	      const collabInfo = im_v2_application_core.Core.getStore().getters['chats/collabs/getByChatId'](this.chatId);
	      if (!collabInfo || !collabInfo.collabId) {
	        return;
	      }
	      try {
	        const groupSharing = await calendar_sharing_interface.GroupSharingController.getGroupSharing(collabInfo.collabId, event.target);
	        groupSharing.openDialog();
	        this.showMenu = false;
	      } catch (errors) {
	        this.showNotification(this.loc('IM_TEXTAREA_UNKNOWN_ERROR'));
	        console.error('ChatTextarea: UploadMenu: select slots error', errors);
	      }
	    },
	    onCreateDocumentSignClick() {
	      const preparedUrl = DOCUMENT_SIGN_SLIDER_URL + this.chatId;
	      BX.SidePanel.Instance.open(preparedUrl, {
	        cacheable: false
	      });
	    },
	    onCreateVoteClick() {
	      const preparedUrl = `/bitrix/components/bitrix/voting.im.edit/slider.php?chatId=${this.chatId}`;
	      BX.SidePanel.Instance.open(preparedUrl, {
	        cacheable: false,
	        width: 800,
	        allowChangeHistory: false
	      });
	      this.showMenu = false;
	    },
	    showNotification(content) {
	      BX.UI.Notification.Center.notify({
	        content
	      });
	    }
	  },
	  template: `
		<div
			class="bx-im-textarea__icon --upload"
			:class="{'--active': showMenu}"
			:title="loc('IM_TEXTAREA_ICON_UPLOAD_TITLE')"
			@click="onUploadButtonClick"
			ref="upload"
		>
		</div>
		<MessengerMenu v-if="showMenu" :config="menuConfig" @close="showMenu = false" className="bx-im-file-menu__scope">
			<MenuItem
				v-for="item in availableMenuItems"
				:icon="item.icon"
				:title="item.title"
				@click="item.clickHandler"
			/>
			<input type="file" @change="onFileSelect" multiple class="bx-im-file-menu__file-input" ref="fileInput">
		</MessengerMenu>
		<DiskPopup v-if="showDiskPopup" @diskFileSelect="onDiskFileSelect" @close="showDiskPopup = false"/>
	`
	};

	// @vue/component
	const SendButton = {
	  props: {
	    dialogId: {
	      type: String,
	      default: ''
	    },
	    editMode: {
	      type: Boolean,
	      default: false
	    },
	    isDisabled: {
	      type: Boolean,
	      default: false
	    }
	  },
	  computed: {
	    dialog() {
	      return this.$store.getters['chats/get'](this.dialogId, true);
	    },
	    dialogTypeClass() {
	      return `--${this.dialog.type}`;
	    },
	    buttonHint() {
	      const sendByEnter = this.$store.getters['application/settings/get'](im_v2_const.Settings.hotkey.sendByEnter);
	      const ctrlKey = im_v2_lib_utils.Utils.platform.isMac() ? 'Cmd' : 'Ctrl';
	      const sendCombination = sendByEnter ? 'Enter' : `${ctrlKey} + Enter`;
	      return this.loc('IM_TEXTAREA_ICON_SEND_TEXT', {
	        '#SEND_MESSAGE_COMBINATION#': sendCombination
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
			:class="[{'--edit': editMode, '--disabled': isDisabled, }, dialogTypeClass]"
		></div>
	`
	};

	// @vue/component
	const FilePreviewItem = {
	  name: 'FilePreviewItem',
	  props: {
	    file: {
	      type: Object,
	      required: true
	    },
	    maxNameLength: {
	      type: Number,
	      default: 25
	    }
	  },
	  computed: {
	    fileIconClass() {
	      return `ui-icon ui-icon-file-${this.file.icon}`;
	    },
	    fileShortName() {
	      return im_v2_lib_utils.Utils.file.getShortFileName(this.file.name, this.maxNameLength);
	    },
	    fileSize() {
	      return im_v2_lib_utils.Utils.file.formatFileSize(this.file.size);
	    },
	    hasPreview() {
	      return main_core.Type.isStringFilled(this.file.urlPreview);
	    },
	    imageStyles() {
	      return {
	        backgroundImage: `url(${this.file.urlPreview})`
	      };
	    }
	  },
	  template: `
		<div class="bx-im-upload-preview-file-item__file-container">
			<div class="bx-im-upload-preview-file-item__icon">
				<div v-if="hasPreview" :style="imageStyles" class="bx-im-upload-preview-file-item__preview"></div>
				<div v-else :class="fileIconClass"><i></i></div>
			</div>
			<div class="bx-im-upload-preview-file-item__info">
				<div class="bx-im-upload-preview-file-item__name">{{ fileShortName }}</div>
				<div class="bx-im-upload-preview-file-item__size">{{ fileSize }}</div>
			</div>
		</div>
	`
	};

	// @vue/component
	const ErrorPreviewItem = {
	  name: 'ErrorPreviewItem',
	  template: `
		<div class="bx-im-upload-preview-file-item__item-error">
			<div class="bx-im-upload-preview-file-item__item-error-icon"></div>
			<div class="bx-im-upload-preview-file-item__item-error-text">
				{{ $Bitrix.Loc.getMessage('IM_TEXTAREA_UPLOAD_PREVIEW_POPUP_FILE_UPLOAD_ERROR') }}
			</div>
		</div>
	`
	};

	// @vue/component
	const FileItem = {
	  name: 'FileItem',
	  components: {
	    FilePreviewItem,
	    ErrorPreviewItem
	  },
	  props: {
	    file: {
	      type: Object,
	      required: true
	    },
	    removable: {
	      type: Boolean,
	      default: false
	    }
	  },
	  emits: ['onRemoveItem'],
	  computed: {
	    hasError() {
	      return this.file.status === im_v2_const.FileStatus.error;
	    },
	    previewComponentName() {
	      if (this.hasError) {
	        return ErrorPreviewItem.name;
	      }
	      return FilePreviewItem.name;
	    }
	  },
	  methods: {
	    onRemoveClick() {
	      this.$emit('onRemoveItem', {
	        file: this.file
	      });
	    }
	  },
	  template: `
		<div class="bx-im-upload-preview-file-item__scope">
			<component
				:is="previewComponentName"
				:file="file"
			/>
			<div v-if="removable" class="bx-im-upload-preview-file-item__remove" @click="onRemoveClick">
				<div class="bx-im-upload-preview-file-item__remove-icon"></div>
			</div>
		</div>
	`
	};

	const MAX_FILES_COUNT = 10;
	const BUTTONS_CONTAINER_HEIGHT = 74;
	const TextareaHeight = {
	  max: 208,
	  min: 46
	};
	// @vue/component
	const UploadPreviewContent = {
	  name: 'UploadPreviewContent',
	  components: {
	    MediaContent: im_v2_component_message_file.MediaContent,
	    FileItem,
	    SendButton
	  },
	  props: {
	    dialogId: {
	      type: String,
	      required: true
	    },
	    uploaderId: {
	      type: String,
	      required: true
	    },
	    textareaValue: {
	      type: String,
	      required: false,
	      default: ''
	    }
	  },
	  emits: ['sendFiles', 'close', 'updateTitle'],
	  data() {
	    return {
	      text: '',
	      sendAsFile: false,
	      uploaderFiles: [],
	      textareaHeight: TextareaHeight.min,
	      textareaResizedHeight: 0
	    };
	  },
	  computed: {
	    files() {
	      return this.uploaderFiles.map(file => {
	        return this.$store.getters['files/get'](file.getId());
	      });
	    },
	    fileIds() {
	      return this.files.map(file => {
	        return file.id;
	      });
	    },
	    fakeMessage() {
	      return {
	        id: 'fake',
	        files: this.fileIds,
	        text: '',
	        attach: [],
	        forward: {}
	      };
	    },
	    filesCount() {
	      return this.files.length;
	    },
	    isSingleFile() {
	      return this.files.length === 1;
	    },
	    sourceFilesCount() {
	      return this.getUploadingService().getSourceFilesCount(this.uploaderId);
	    },
	    isOverMaxFilesLimit() {
	      return this.sourceFilesCount > MAX_FILES_COUNT;
	    },
	    isMediaOnly() {
	      return this.files.every(file => {
	        return file.type === im_v2_const.FileType.image || file.type === im_v2_const.FileType.video;
	      });
	    },
	    inputMaxLength() {
	      const settings = main_core.Extension.getSettings('im.v2.component.textarea');
	      return settings.get('maxLength');
	    },
	    textareaHeightStyle() {
	      return this.textareaHeight === 'auto' ? 'auto' : `${this.textareaHeight}px`;
	    },
	    title() {
	      return this.$Bitrix.Loc.getMessage('IM_TEXTAREA_UPLOAD_PREVIEW_POPUP_COMPUTED_TITLE', {
	        '#COUNT#': this.filesCount
	      });
	    }
	  },
	  watch: {
	    text() {
	      void this.adjustTextareaHeight();
	    },
	    title() {
	      this.$emit('updateTitle', this.title);
	    },
	    sendAsFile(newValue) {
	      this.uploaderFiles.forEach(file => {
	        file.setCustomData('sendAsFile', newValue);
	      });
	    }
	  },
	  created() {
	    this.initResizeManager();
	    this.getUploadingService().getFiles(this.uploaderId).forEach(file => {
	      this.uploaderFiles.push(file);
	    });
	  },
	  mounted() {
	    this.text = this.textareaValue;
	    this.insertText('');
	    this.$refs.messageText.focus();
	  },
	  beforeUnmount() {
	    this.insertText(this.text);
	    im_v2_lib_draft.DraftManager.getInstance().setDraftText(this.dialogId, this.text);
	    this.resizeManager.destroy();
	  },
	  methods: {
	    async adjustTextareaHeight() {
	      this.textareaHeight = 'auto';
	      await this.$nextTick();
	      if (!this.$refs.messageText) {
	        return;
	      }
	      const TEXTAREA_BORDERS_WIDTH = 2;
	      const newMaxPoint = Math.min(TextareaHeight.max, this.$refs.messageText.scrollHeight + TEXTAREA_BORDERS_WIDTH);
	      if (this.doesContentOverflowScreen(newMaxPoint)) {
	        const textareaTopPoint = this.$refs.messageText.getBoundingClientRect().top;
	        const availableHeight = window.innerHeight - textareaTopPoint - BUTTONS_CONTAINER_HEIGHT;
	        this.textareaHeight = Math.max(TextareaHeight.min, availableHeight);
	        return;
	      }
	      if (this.resizedTextareaHeight) {
	        this.textareaHeight = Math.max(newMaxPoint, this.resizedTextareaHeight);
	        return;
	      }
	      this.textareaHeight = Math.max(newMaxPoint, TextareaHeight.min);
	    },
	    getUploadingService() {
	      if (!this.uploadingService) {
	        this.uploadingService = im_v2_provider_service.UploadingService.getInstance();
	      }
	      return this.uploadingService;
	    },
	    onCancel() {
	      this.$emit('close', {
	        text: this.text
	      });
	    },
	    onSend() {
	      if (this.sendAsFile || !this.isMediaOnly) {
	        this.uploaderFiles.forEach(file => {
	          this.removePreviewParams(file);
	        });
	      }
	      this.$emit('sendFiles', {
	        groupFiles: false,
	        text: this.text,
	        uploaderId: this.uploaderId,
	        sendAsFile: this.sendAsFile
	      });
	      this.text = '';
	    },
	    onKeyDownHandler(event) {
	      const sendMessageCombination = im_v2_lib_hotkey.isSendMessageCombination(event);
	      const newLineCombination = im_v2_lib_hotkey.isNewLineCombination(event);
	      if (sendMessageCombination && !newLineCombination) {
	        event.preventDefault();
	        this.onSend();
	        return;
	      }
	      if (newLineCombination) {
	        event.preventDefault();
	        this.text = im_v2_lib_textarea.Textarea.addNewLine(this.$refs.messageText);
	      }
	    },
	    removePreviewParams(file) {
	      this.$store.dispatch('files/update', {
	        id: file.getId(),
	        fields: {
	          image: false
	        }
	      });
	    },
	    insertText(text) {
	      main_core_events.EventEmitter.emit(im_v2_const.EventType.textarea.insertText, {
	        text,
	        dialogId: this.dialogId,
	        replace: true
	      });
	    },
	    loc(phraseCode, replacements = {}) {
	      return this.$Bitrix.Loc.getMessage(phraseCode, replacements);
	    },
	    initResizeManager() {
	      this.resizeManager = new ResizeManager({
	        direction: ResizeDirection.down,
	        minHeight: TextareaHeight.min,
	        maxHeight: TextareaHeight.max
	      });
	      this.resizeManager.subscribe(ResizeManager.events.onHeightChange, ({
	        data: {
	          newHeight
	        }
	      }) => {
	        this.textareaHeight = newHeight;
	      });
	      this.resizeManager.subscribe(ResizeManager.events.onResizeStop, () => {
	        this.resizedTextareaHeight = this.textareaHeight;
	      });
	    },
	    onResizeStart(event) {
	      this.resizeManager.onResizeStart(event, this.textareaHeight);
	    },
	    doesContentOverflowScreen(newMaxPoint) {
	      const textareaTop = this.$refs.messageText.getBoundingClientRect().top;
	      return textareaTop + newMaxPoint + BUTTONS_CONTAINER_HEIGHT > window.innerHeight;
	    },
	    onRemoveItem(event) {
	      this.getUploadingService().removeFileFromUploader({
	        uploaderId: this.uploaderId,
	        filesIds: [event.file.id]
	      });
	      this.uploaderFiles = this.getUploadingService().getFiles(this.uploaderId);
	      if (this.filesCount === 0) {
	        this.$emit('close');
	      }
	    }
	  },
	  template: `
		<div class="bx-im-upload-preview__container">
			<div class="bx-im-upload-preview__items-container">
				<MediaContent 
					v-if="isMediaOnly && !sendAsFile" 
					:item="fakeMessage" 
					:previewMode="true" 
					:removable="true"
					@onRemoveItem="onRemoveItem"
				/>
				<FileItem 
					v-else 
					v-for="fileItem in files" 
					:file="fileItem" 
					:class="{'--single': isSingleFile}" 
					:removable="true"
					@onRemoveItem="onRemoveItem"
				/>
			</div>
			<div class="bx-im-upload-preview__controls-container">
				<div v-if="isOverMaxFilesLimit" class="ui-alert ui-alert-xs ui-alert-icon-warning bx-im-upload-preview__controls-files-limit-message">
					<span class="ui-alert-message">{{ loc('IM_TEXTAREA_UPLOAD_PREVIEW_POPUP_FILES_LIMIT_MESSAGE_10') }}</span>
				</div>
				<label v-if="isMediaOnly" class="bx-im-upload-preview__control-compress-image">
					<input type="checkbox" class="bx-im-upload-preview__control-compress-image-checkbox" v-model="sendAsFile">
					{{ loc('IM_TEXTAREA_UPLOAD_PREVIEW_POPUP_SEND_WITHOUT_COMPRESSION') }}
				</label>
				<div class="bx-im-upload-preview__control-form">
					<textarea
						ref="messageText"
						v-model="text"
						:placeholder="loc('IM_TEXTAREA_UPLOAD_PREVIEW_POPUP_INPUT_PLACEHOLDER_2')"
						:maxlength="inputMaxLength"
						:style="{'height': textareaHeightStyle}"
						class="bx-im-upload-preview__message-text"
						rows="1"
						@keydown="onKeyDownHandler"
					></textarea>
					<SendButton :dialogId="dialogId" @click="onSend" />
				</div>
				<div @mousedown="onResizeStart" class="bx-im-upload-preview__drag-handle"></div>
			</div>
		</div>
	`
	};

	const POPUP_ID = 'im-chat-upload-preview-popup';

	// @vue/component
	const UploadPreviewPopup = {
	  name: 'UploadPreviewPopup',
	  components: {
	    MessengerPopup: im_v2_component_elements.MessengerPopup,
	    UploadPreviewContent
	  },
	  props: {
	    dialogId: {
	      type: String,
	      required: true
	    },
	    uploaderId: {
	      type: String,
	      required: true
	    },
	    textareaValue: {
	      type: String,
	      required: false,
	      default: ''
	    }
	  },
	  emits: ['close', 'sendFiles'],
	  computed: {
	    POPUP_ID: () => POPUP_ID,
	    config() {
	      return {
	        width: 400,
	        targetContainer: document.body,
	        fixed: true,
	        draggable: {
	          restrict: true
	        },
	        titleBar: ' ',
	        offsetTop: 0,
	        padding: 0,
	        closeIcon: true,
	        contentColor: 'transparent',
	        contentPadding: 0,
	        className: 'bx-im-upload-preview__scope',
	        autoHide: true,
	        overlay: true
	      };
	    }
	  },
	  methods: {
	    onSendFiles(event) {
	      this.$emit('sendFiles', event);
	      this.$emit('close');
	    },
	    onUpdateTitle(title) {
	      var _PopupManager$getPopu;
	      (_PopupManager$getPopu = main_popup.PopupManager.getPopupById(POPUP_ID)) == null ? void 0 : _PopupManager$getPopu.setTitleBar(title);
	    }
	  },
	  template: `
		<MessengerPopup
			:config="config"
			@close="$emit('close')"
			:id="POPUP_ID"
		>
			<UploadPreviewContent 
				:dialogId="dialogId" 
				:uploaderId="uploaderId"
				:textareaValue="textareaValue"
				@close="$emit('close')"
				@sendFiles="onSendFiles"
				@updateTitle="onUpdateTitle"
			/>
		</MessengerPopup>
	`
	};

	const ItemTextByChatType = {
	  [im_v2_const.ChatType.openChannel]: main_core.Loc.getMessage('IM_TEXTAREA_MENTION_OPEN_CHANNEL_TYPE'),
	  [im_v2_const.ChatType.generalChannel]: main_core.Loc.getMessage('IM_TEXTAREA_MENTION_OPEN_CHANNEL_TYPE'),
	  [im_v2_const.ChatType.channel]: main_core.Loc.getMessage('IM_TEXTAREA_MENTION_PRIVATE_CHANNEL_TYPE'),
	  [im_v2_const.ChatType.collab]: main_core.Loc.getMessage('IM_TEXTAREA_MENTION_COLLAB_TYPE'),
	  default: main_core.Loc.getMessage('IM_TEXTAREA_MENTION_CHAT_TYPE')
	};

	// @vue/component
	const MentionItem = {
	  name: 'MentionItem',
	  components: {
	    ChatAvatar: im_v2_component_elements.ChatAvatar,
	    ChatTitleWithHighlighting: im_v2_component_elements.ChatTitleWithHighlighting
	  },
	  props: {
	    dialogId: {
	      type: String,
	      required: true
	    },
	    query: {
	      type: String,
	      default: ''
	    },
	    selected: {
	      type: Boolean,
	      default: false
	    },
	    contextDialogId: {
	      type: String,
	      required: true
	    }
	  },
	  emits: ['itemClick', 'itemHover'],
	  computed: {
	    AvatarSize: () => im_v2_component_elements.AvatarSize,
	    dialog() {
	      return this.$store.getters['chats/get'](this.dialogId, true);
	    },
	    user() {
	      return this.$store.getters['users/get'](this.dialogId, true);
	    },
	    recentItem() {
	      return this.$store.getters['recent/get'](this.dialogId);
	    },
	    isUser() {
	      return this.dialog.type === im_v2_const.ChatType.user;
	    },
	    position() {
	      if (!this.isUser) {
	        return '';
	      }
	      return this.$store.getters['users/getPosition'](this.dialogId);
	    },
	    userItemText() {
	      if (!this.position) {
	        return this.$Bitrix.Loc.getMessage('IM_TEXTAREA_MENTION_USER_TYPE');
	      }
	      return im_v2_lib_textHighlighter.highlightText(main_core.Text.encode(this.position), this.query);
	    },
	    chatItemText() {
	      var _ItemTextByChatType$t;
	      return (_ItemTextByChatType$t = ItemTextByChatType[this.dialog.type]) != null ? _ItemTextByChatType$t : ItemTextByChatType.default;
	    }
	  },
	  methods: {
	    onClick() {
	      this.$emit('itemClick', {
	        dialogId: this.dialogId
	      });
	    }
	  },
	  template: `
		<div 
			@click="onClick" 
			class="bx-im-mention-item__container bx-im-mention-item__scope" 
			:class="{'--selected': selected}"
			@mouseover="$emit('itemHover')"
		>
			<ChatAvatar 
				:avatarDialogId="dialogId"
				:contextDialogId="dialogId"
				:size="AvatarSize.M" 
				class="bx-im-mention-item__avatar-container" 
			/>
			<div class="bx-im-mention-item__content-container">
				<ChatTitleWithHighlighting 
					:dialogId="dialogId" 
					:textToHighlight="query" 
					class="bx-im-mention-item__title"
				/>
				<div v-if="isUser" class="bx-im-mention-item__position" :title="position" v-html="userItemText"></div>
				<div v-else class="bx-im-mention-item__position" :title="chatItemText">{{ chatItemText }}</div>
			</div>
		</div>
	`
	};

	// @vue/component
	const MentionEmptyState = {
	  name: 'MentionEmptyState',
	  template: `
		<div class="bx-im-mention-empty-state__scope bx-im-mention-empty-state__container">
			<span class="bx-im-mention-empty-state__icon"></span>
			<span class="bx-im-mention-empty-state__title">
				{{ $Bitrix.Loc.getMessage('IM_TEXTAREA_MENTION_EMPTY_STATE') }}
			</span>
		</div>
	`
	};

	// @vue/component
	const MentionLoadingState = {
	  name: 'MentionLoadingState',
	  components: {
	    Spinner: im_v2_component_elements.Spinner
	  },
	  computed: {
	    SpinnerSize: () => im_v2_component_elements.SpinnerSize,
	    SpinnerColor: () => im_v2_component_elements.SpinnerColor
	  },
	  template: `
		<div class="bx-im-mention-loading-state__scope bx-im-mention-loading-state__container">
			<div class="bx-im-mention-loading-state__loader">
				<Spinner :size="SpinnerSize.XXS" :color="SpinnerColor.grey"/>
			</div>
			<span class="bx-im-mention-loading-state__title">
				{{ $Bitrix.Loc.getMessage('IM_TEXTAREA_MENTION_LOADING_STATE') }}
			</span>
		</div>
	`
	};

	// @vue/component
	const MentionContentFooter = {
	  name: 'MentionContentFooter',
	  components: {
	    Loader: im_v2_component_elements.Loader
	  },
	  props: {
	    isLoading: {
	      type: Boolean,
	      default: false
	    }
	  },
	  computed: {
	    arrowsControlTitle() {
	      return this.$Bitrix.Loc.getMessage('IM_TEXTAREA_MENTION_ARROWS_CONTROL').replace('##ARROWS_ICON##', '');
	    },
	    enterControlTitle() {
	      return this.$Bitrix.Loc.getMessage('IM_TEXTAREA_MENTION_ENTER_CONTROL');
	    },
	    escControlTitle() {
	      return this.$Bitrix.Loc.getMessage('IM_TEXTAREA_MENTION_ESC_CONTROL');
	    }
	  },
	  template: `
		<div class="bx-im-mention-content-footer__container bx-im-mention-content-footer__scope">
			<div class="bx-im-mention-content-footer__controls">
				<div class="bx-im-mention-content-footer__control">
					<span class="bx-im-mention-content-footer__arrows-control-key"></span>
					<span class="bx-im-mention-content-footer__control-description">
						{{ arrowsControlTitle }}
					</span>
				</div>
				<div class="bx-im-mention-content-footer__control">
					<span class="bx-im-mention-content-footer__control-key">Enter</span>
					<span class="bx-im-mention-content-footer__control-description">{{ enterControlTitle }}</span>
				</div>
				<div class="bx-im-mention-content-footer__control">
					<span class="bx-im-mention-content-footer__control-key">Esc</span>
					<span class="bx-im-mention-content-footer__control-description">{{ escControlTitle }}</span>
				</div>
			</div>
			<Loader v-if="isLoading" class="bx-im-mention-content-footer__loader" />
		</div>
	`
	};

	const SEARCH_REQUEST_ENDPOINT = 'ui.entityselector.doSearch';
	var _storeUpdater = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("storeUpdater");
	var _restClient$1 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("restClient");
	var _searchConfig = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("searchConfig");
	var _searchRequest = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("searchRequest");
	var _getDialogIdAndDate = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getDialogIdAndDate");
	class BaseServerSearch {
	  constructor(searchConfig) {
	    Object.defineProperty(this, _getDialogIdAndDate, {
	      value: _getDialogIdAndDate2
	    });
	    Object.defineProperty(this, _searchRequest, {
	      value: _searchRequest2
	    });
	    Object.defineProperty(this, _storeUpdater, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _restClient$1, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _searchConfig, {
	      writable: true,
	      value: void 0
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _searchConfig)[_searchConfig] = searchConfig;
	    babelHelpers.classPrivateFieldLooseBase(this, _storeUpdater)[_storeUpdater] = new im_v2_lib_search.StoreUpdater();
	    babelHelpers.classPrivateFieldLooseBase(this, _restClient$1)[_restClient$1] = im_v2_application_core.Core.getRestClient();
	  }
	  async search(query) {
	    const items = await babelHelpers.classPrivateFieldLooseBase(this, _searchRequest)[_searchRequest](query);
	    await babelHelpers.classPrivateFieldLooseBase(this, _storeUpdater)[_storeUpdater].update(items);
	    return babelHelpers.classPrivateFieldLooseBase(this, _getDialogIdAndDate)[_getDialogIdAndDate](items);
	  }
	  async loadChatParticipants(dialogId) {
	    const queryParams = {
	      order: {
	        lastSendMessageId: 'desc'
	      },
	      dialogId,
	      limit: 50
	    };
	    let users = [];
	    try {
	      const response = await babelHelpers.classPrivateFieldLooseBase(this, _restClient$1)[_restClient$1].callMethod(im_v2_const.RestMethod.imV2ChatUserList, queryParams);
	      users = response.data();
	    } catch (error) {
	      console.error('Mention search service: load chat participants error', error);
	    }
	    void babelHelpers.classPrivateFieldLooseBase(this, _storeUpdater)[_storeUpdater].updateUsers(users);
	    return babelHelpers.classPrivateFieldLooseBase(this, _getDialogIdAndDate)[_getDialogIdAndDate](users);
	  }
	}
	async function _searchRequest2(query) {
	  const config = {
	    json: im_v2_lib_search.getSearchConfig(babelHelpers.classPrivateFieldLooseBase(this, _searchConfig)[_searchConfig])
	  };
	  config.json.searchQuery = {
	    queryWords: im_v2_lib_utils.Utils.text.getWordsFromString(query),
	    query
	  };
	  let items = [];
	  try {
	    const response = await main_core.ajax.runAction(SEARCH_REQUEST_ENDPOINT, config);
	    im_v2_lib_logger.Logger.warn('Mention search service: request result', response);
	    items = response.data.dialog.items;
	  } catch (error) {
	    im_v2_lib_logger.Logger.warn('Mention search service: request error', error);
	  }
	  return items;
	}
	function _getDialogIdAndDate2(items) {
	  return items.map(item => {
	    var _item$customData$date, _item$customData;
	    return {
	      dialogId: item.id.toString(),
	      dateMessage: (_item$customData$date = (_item$customData = item.customData) == null ? void 0 : _item$customData.dateMessage) != null ? _item$customData$date : ''
	    };
	  });
	}

	var _store = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("store");
	var _localSearch = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("localSearch");
	var _baseServerSearch = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("baseServerSearch");
	var _localCollection = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("localCollection");
	var _isSelfDialogId = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("isSelfDialogId");
	var _getDialogIds = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getDialogIds");
	var _isExtranet = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("isExtranet");
	class MentionSearchService {
	  constructor(searchConfig) {
	    Object.defineProperty(this, _isExtranet, {
	      value: _isExtranet2
	    });
	    Object.defineProperty(this, _getDialogIds, {
	      value: _getDialogIds2
	    });
	    Object.defineProperty(this, _isSelfDialogId, {
	      value: _isSelfDialogId2
	    });
	    Object.defineProperty(this, _store, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _localSearch, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _baseServerSearch, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _localCollection, {
	      writable: true,
	      value: new Map()
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _store)[_store] = im_v2_application_core.Core.getStore();
	    babelHelpers.classPrivateFieldLooseBase(this, _localSearch)[_localSearch] = new im_v2_lib_search.LocalSearch(searchConfig);
	    babelHelpers.classPrivateFieldLooseBase(this, _baseServerSearch)[_baseServerSearch] = new BaseServerSearch(searchConfig);
	  }
	  async loadChatParticipants(dialogId) {
	    const items = await babelHelpers.classPrivateFieldLooseBase(this, _baseServerSearch)[_baseServerSearch].loadChatParticipants(dialogId);
	    if (babelHelpers.classPrivateFieldLooseBase(this, _isSelfDialogId)[_isSelfDialogId](dialogId)) {
	      return babelHelpers.classPrivateFieldLooseBase(this, _getDialogIds)[_getDialogIds](items);
	    }
	    const filteredResult = items.filter(item => !babelHelpers.classPrivateFieldLooseBase(this, _isSelfDialogId)[_isSelfDialogId](item.dialogId));
	    filteredResult.forEach(searchItem => {
	      babelHelpers.classPrivateFieldLooseBase(this, _localCollection)[_localCollection].set(searchItem.dialogId, searchItem);
	    });
	    return babelHelpers.classPrivateFieldLooseBase(this, _getDialogIds)[_getDialogIds](filteredResult);
	  }
	  searchLocal(query) {
	    const localCollection = [...babelHelpers.classPrivateFieldLooseBase(this, _localCollection)[_localCollection].values()];
	    const result = babelHelpers.classPrivateFieldLooseBase(this, _localSearch)[_localSearch].search(query, localCollection);
	    return babelHelpers.classPrivateFieldLooseBase(this, _getDialogIds)[_getDialogIds](result);
	  }
	  async search(query) {
	    const searchResult = await babelHelpers.classPrivateFieldLooseBase(this, _baseServerSearch)[_baseServerSearch].search(query);
	    searchResult.forEach(searchItem => {
	      babelHelpers.classPrivateFieldLooseBase(this, _localCollection)[_localCollection].set(searchItem.dialogId, searchItem);
	    });
	    return babelHelpers.classPrivateFieldLooseBase(this, _getDialogIds)[_getDialogIds](searchResult);
	  }
	  sortByDate(items) {
	    items.sort((firstItem, secondItem) => {
	      if (!firstItem.dateMessage || !secondItem.dateMessage) {
	        if (!firstItem.dateMessage && !secondItem.dateMessage) {
	          if (babelHelpers.classPrivateFieldLooseBase(this, _isExtranet)[_isExtranet](firstItem.dialogId)) {
	            return 1;
	          }
	          if (babelHelpers.classPrivateFieldLooseBase(this, _isExtranet)[_isExtranet](secondItem.dialogId)) {
	            return -1;
	          }
	          return 0;
	        }
	        return firstItem.dateMessage ? -1 : 1;
	      }
	      return im_v2_lib_utils.Utils.date.cast(secondItem.dateMessage) - im_v2_lib_utils.Utils.date.cast(firstItem.dateMessage);
	    });
	    return items;
	  }
	}
	function _isSelfDialogId2(dialogId) {
	  return dialogId === im_v2_application_core.Core.getUserId().toString();
	}
	function _getDialogIds2(items) {
	  return items.map(item => item.dialogId);
	}
	function _isExtranet2(dialogId) {
	  const dialog = babelHelpers.classPrivateFieldLooseBase(this, _store)[_store].getters['chats/get'](dialogId);
	  if (!dialog) {
	    return false;
	  }
	  if (dialog.type === im_v2_const.ChatType.user) {
	    const user = babelHelpers.classPrivateFieldLooseBase(this, _store)[_store].getters['users/get'](dialogId);
	    return user && user.type === im_v2_const.UserType.extranet;
	  }
	  return dialog.extranet;
	}

	// @vue/component
	const MentionPopupContent = {
	  name: 'MentionPopupContent',
	  components: {
	    MentionItem,
	    MentionContentFooter,
	    MentionEmptyState,
	    ScrollWithGradient: im_v2_component_elements.ScrollWithGradient,
	    MentionLoadingState
	  },
	  props: {
	    dialogId: {
	      type: String,
	      required: true
	    },
	    query: {
	      type: String,
	      default: ''
	    },
	    searchChats: {
	      type: Boolean,
	      default: true
	    },
	    exclude: {
	      type: Array,
	      default: () => []
	    }
	  },
	  emits: ['close', 'adjustPosition'],
	  data() {
	    return {
	      isLoading: false,
	      searchResult: [],
	      chatParticipants: [],
	      chatParticipantsLoaded: false,
	      currentServerQueries: 0,
	      needTopShadow: false,
	      needBottomShadow: true,
	      selectedIndex: 0,
	      selectedItem: ''
	    };
	  },
	  computed: {
	    itemsToShow() {
	      return this.items.filter(dialogId => !this.exclude.includes(dialogId));
	    },
	    items() {
	      if (this.preparedQuery.length === 0) {
	        if (this.needToShowRecentUsersOnStartScreen) {
	          return this.usersFromRecent;
	        }
	        return this.chatParticipants;
	      }
	      return this.searchResult;
	    },
	    needToShowRecentUsersOnStartScreen() {
	      return this.chatParticipantsLoaded && this.chatParticipants.length <= 1;
	    },
	    usersFromRecent() {
	      const recentUsers = [];
	      this.$store.getters['recent/getSortedCollection'].forEach(recentItem => {
	        if (this.isChat(recentItem.dialogId)) {
	          return;
	        }
	        const user = this.$store.getters['users/get'](recentItem.dialogId, true);
	        const isBot = user.type === im_v2_const.UserType.bot;
	        if (isBot || user.id === im_v2_application_core.Core.getUserId()) {
	          return;
	        }
	        recentUsers.push(user);
	      });
	      return recentUsers.map(user => user.id.toString());
	    },
	    preparedQuery() {
	      return this.query.trim().toLowerCase();
	    },
	    isEmptyState() {
	      if (this.isLoading) {
	        return false;
	      }
	      return this.itemsToShow.length === 0;
	    },
	    searchConfig() {
	      return {
	        chats: this.searchChats,
	        users: true
	      };
	    }
	  },
	  watch: {
	    async isLoading() {
	      await this.adjustPosition();
	    },
	    async searchResult() {
	      await this.adjustPosition();
	    },
	    preparedQuery(newQuery, previousQuery) {
	      if (newQuery === previousQuery) {
	        return;
	      }
	      this.selectedIndex = 0;
	      void this.startSearch(newQuery);
	    }
	  },
	  created() {
	    this.initSettings();
	    this.searchService = new MentionSearchService(this.searchConfig);
	    this.searchOnServerDelayed = main_core.Runtime.debounce(this.searchOnServer, 400, this);
	    void this.loadChatParticipants();
	    main_core.Event.bind(window, 'keydown', this.onKeyDown);
	    main_core_events.EventEmitter.subscribe(im_v2_const.EventType.mention.selectItem, this.onInsertItem);
	  },
	  beforeUnmount() {
	    main_core.Event.unbind(window, 'keydown', this.onKeyDown);
	    main_core_events.EventEmitter.unsubscribe(im_v2_const.EventType.mention.selectItem, this.onInsertItem);
	  },
	  methods: {
	    initSettings() {
	      const settings = main_core.Extension.getSettings('im.v2.component.textarea');
	      const defaultMinTokenSize = 3;
	      this.minTokenSize = settings.get('minSearchTokenSize', defaultMinTokenSize);
	    },
	    async loadChatParticipants() {
	      this.isLoading = true;
	      this.chatParticipants = await this.searchService.loadChatParticipants(this.dialogId);
	      this.searchResult = this.chatParticipants;
	      this.isLoading = false;
	      this.chatParticipantsLoaded = true;
	    },
	    async searchOnServer(query) {
	      this.currentServerQueries++;
	      const dialogIds = await this.searchService.search(query);
	      if (query !== this.preparedQuery) {
	        this.isLoading = false;
	        return;
	      }
	      this.searchResult = [...new Set([...this.searchResult, ...dialogIds])];
	      this.currentServerQueries--;
	      this.stopLoader();
	    },
	    async startSearch(query) {
	      if (query.length > 0) {
	        const dialogIds = this.searchService.searchLocal(query);
	        if (query !== this.preparedQuery) {
	          return;
	        }
	        const sortedLocalResult = this.searchService.sortByDate(dialogIds);
	        this.searchResult = this.appendResult(sortedLocalResult);
	      }
	      if (query.length >= this.minTokenSize) {
	        this.isLoading = true;
	        await this.searchOnServerDelayed(query);
	      }
	      if (query.length === 0) {
	        this.cleanSearchResult();
	      }
	    },
	    stopLoader() {
	      if (this.currentServerQueries > 0) {
	        return;
	      }
	      this.isLoading = false;
	    },
	    cleanSearchResult() {
	      this.searchResult = this.chatParticipants;
	    },
	    async adjustPosition() {
	      await this.$nextTick();
	      this.$emit('adjustPosition');
	    },
	    onInsertItem() {
	      if (!main_core.Type.isArrayFilled(this.itemsToShow)) {
	        return;
	      }
	      this.sendInsertMentionEvent(this.itemsToShow[this.selectedIndex]);
	    },
	    onItemClick({
	      dialogId
	    }) {
	      this.sendInsertMentionEvent(dialogId);
	      this.$emit('close');
	    },
	    sendInsertMentionEvent(dialogId) {
	      const mentionText = this.getMentionText(dialogId);
	      const mentionReplacement = im_v2_lib_utils.Utils.text.getMentionBbCode(dialogId, mentionText);
	      main_core_events.EventEmitter.emit(im_v2_const.EventType.textarea.insertMention, {
	        mentionText,
	        mentionReplacement,
	        textToReplace: this.query,
	        dialogId: this.dialogId
	      });
	    },
	    getMentionText(dialogId) {
	      if (dialogId.startsWith('chat')) {
	        return this.$store.getters['chats/get'](dialogId, true).name;
	      }
	      return this.$store.getters['users/get'](dialogId, true).name;
	    },
	    onKeyDown(event) {
	      if (event.key === 'ArrowDown') {
	        this.selectedIndex = this.selectedIndex === this.itemsToShow.length - 1 ? 0 : this.selectedIndex + 1;
	      }
	      if (event.key === 'ArrowUp') {
	        this.selectedIndex = this.selectedIndex === 0 ? this.itemsToShow.length - 1 : this.selectedIndex - 1;
	      }
	      const element = this.getDomElementById(this.selectedIndex);
	      if (!element) {
	        this.selectedIndex = 0;
	      }
	      this.selectedItem = this.itemsToShow[this.selectedIndex];
	      this.scrollToItem(element);
	    },
	    scrollToItem(element) {
	      const scrollContainer = document.querySelector('.bx-im-mention-popup-content__container .bx-im-scroll-with-gradient__content');
	      const tabRect = main_core.Dom.getPosition(scrollContainer);
	      const nodeRect = main_core.Dom.getPosition(element);
	      const margin = 12; // 'bx-im-mention-popup-content__items' margin

	      if (nodeRect.top < tabRect.top)
	        // scroll up
	        {
	          scrollContainer.scrollTop -= tabRect.top - nodeRect.top + margin;
	        } else if (nodeRect.bottom > tabRect.bottom)
	        // scroll down
	        {
	          scrollContainer.scrollTop += nodeRect.bottom - tabRect.bottom + margin;
	        }
	    },
	    onItemHover(index) {
	      this.selectedIndex = index;
	      this.selectedItem = this.itemsToShow[this.selectedIndex];
	    },
	    getDomElementById(id) {
	      return this.$refs['mention-content'].querySelector(`[data-index="${id}"]`);
	    },
	    appendResult(newItems) {
	      const filtered = this.searchResult.filter(dialogId => newItems.includes(dialogId));
	      return [...new Set([...filtered, ...newItems])];
	    },
	    isChat(dialogId) {
	      return dialogId.startsWith('chat');
	    }
	  },
	  template: `
		<div class="bx-im-mention-popup-content__container" ref="mention-content">
			<ScrollWithGradient 
				v-if="itemsToShow.length > 0" 
				:gradientHeight="13" 
				:containerMaxHeight="200"
				:withShadow="false"
			>
				<div class="bx-im-mention-popup-content__items">
					<MentionItem
						v-for="(itemDialogId, index) in itemsToShow"
						:data-index="index"
						:dialogId="itemDialogId"
						:contextDialogId="dialogId"
						:query="query"
						:selected="selectedIndex === index"
						@itemClick="onItemClick"
						@itemHover="onItemHover(index)"
					/>
				</div>
			</ScrollWithGradient>
			<MentionEmptyState v-if="isEmptyState" />
			<MentionLoadingState v-if="isLoading && itemsToShow.length === 0" />
			<MentionContentFooter :isLoading="isLoading" />
		</div>
	`
	};

	const POPUP_ID$1 = 'im-mention-popup';

	// @vue/component
	const MentionPopup = {
	  name: 'MentionPopup',
	  components: {
	    MessengerPopup: im_v2_component_elements.MessengerPopup,
	    MentionPopupContent
	  },
	  props: {
	    bindElement: {
	      type: Object,
	      required: true
	    },
	    dialogId: {
	      type: String,
	      required: true
	    },
	    query: {
	      type: String,
	      default: ''
	    }
	  },
	  emits: ['close'],
	  computed: {
	    POPUP_ID: () => POPUP_ID$1,
	    dialog() {
	      return this.$store.getters['chats/get'](this.dialogId, true);
	    },
	    isCopilotType() {
	      return this.dialog.type === im_v2_const.ChatType.copilot;
	    },
	    needToShowMentionPopup() {
	      if (this.isCopilotType) {
	        return this.dialog.userCounter > 2;
	      }
	      return true;
	    },
	    excludedChatsFromMentions() {
	      if (!this.isCopilotType) {
	        return [];
	      }
	      const copilotUserId = this.$store.getters['users/bots/getCopilotUserId'];
	      if (copilotUserId && this.dialog.userCounter > 2) {
	        return [copilotUserId.toString()];
	      }
	      return [];
	    },
	    searchChats() {
	      return !this.isCopilotType;
	    },
	    config() {
	      return {
	        height: 200,
	        width: 426,
	        padding: 0,
	        bindElement: this.bindElement,
	        offsetTop: 2,
	        offsetLeft: 0,
	        fixed: true,
	        bindOptions: {
	          position: 'top'
	        },
	        className: 'bx-im-mention-popup__scope'
	      };
	    }
	  },
	  template: `
		<MessengerPopup
			v-if="needToShowMentionPopup"
			:config="config"
			@close="$emit('close');"
			:id="POPUP_ID"
			v-slot="{adjustPosition}"
		>
			<MentionPopupContent 
				:dialogId="dialogId"
				:query="query"
				:exclude="excludedChatsFromMentions"
				:searchChats="searchChats"
				@close="$emit('close');"
				@adjustPosition="adjustPosition()"
			/>
		</MessengerPopup>
	`
	};

	// @vue/component
	const EditPanel = {
	  name: 'EditPanel',
	  props: {
	    messageId: {
	      type: [Number, String],
	      required: true
	    }
	  },
	  emits: ['close'],
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
		<div class="bx-im-message-panel__container">
			<div class="bx-im-message-panel__icon"></div>
			<div class="bx-im-message-panel__content">
				<div class="bx-im-message-panel__title">{{ loc('IM_TEXTAREA_EDIT_MESSAGE_TITLE') }}</div>
				<div class="bx-im-message-panel__text">{{ preparedText }}</div>
			</div>
			<div @click="$emit('close')" class="bx-im-message-panel__close"></div>
		</div>
	`
	};

	const NAME_MAX_LENGTH = 40;

	// @vue/component
	const ReplyPanel = {
	  name: 'ReplyPanel',
	  props: {
	    messageId: {
	      type: Number,
	      required: true
	    }
	  },
	  emits: ['close'],
	  computed: {
	    message() {
	      return this.$store.getters['messages/getById'](this.messageId);
	    },
	    replyAuthor() {
	      return this.$store.getters['users/get'](this.message.authorId);
	    },
	    replyTitle() {
	      return this.replyAuthor ? this.replyAuthor.name : this.loc('IM_DIALOG_CHAT_QUOTE_DEFAULT_TITLE');
	    },
	    messageFile() {
	      return this.$store.getters['messages/getMessageFiles'](this.message.id)[0];
	    },
	    isFile() {
	      return this.messageFile && this.messageFile.type === im_v2_const.FileType.file;
	    },
	    isVideo() {
	      return this.messageFile && this.messageFile.type === im_v2_const.FileType.video;
	    },
	    isImage() {
	      return this.messageFile && this.messageFile.type === im_v2_const.FileType.image;
	    },
	    isAudio() {
	      return this.messageFile && this.messageFile.type === im_v2_const.FileType.audio;
	    },
	    showIcon() {
	      return this.messageFile ? !this.messageFile.urlPreview : false;
	    },
	    truncatedFileName() {
	      return im_v2_lib_utils.Utils.file.getShortFileName(this.messageFile.name, NAME_MAX_LENGTH);
	    },
	    isMessageDeleted() {
	      return this.message.isDeleted;
	    },
	    messageText() {
	      if (this.isFile) {
	        return this.truncatedFileName;
	      }
	      if (this.isAudio) {
	        return this.loc('IM_TEXTAREA_REPLY_AUDIO_TITLE');
	      }
	      if (this.isMessageDeleted) {
	        return this.loc('IM_TEXTAREA_REPLY_DELETED_TITLE');
	      }
	      return im_v2_lib_parser.Parser.purify(this.message);
	    },
	    iconClass() {
	      const iconType = im_v2_lib_utils.Utils.file.getIconTypeByFilename(this.messageFile.name);
	      return `ui-icon-file-${iconType}`;
	    }
	  },
	  methods: {
	    loc(phraseCode) {
	      return this.$Bitrix.Loc.getMessage(phraseCode);
	    }
	  },
	  template: `
		<div class="bx-im-message-panel__container">
			<div class="bx-im-message-panel__icon --quote"></div>
			<div v-if="showIcon" class="bx-im-message-panel-file__icon">
				<div :class="iconClass" class="ui-icon"><i></i></div>
			</div>
			<div v-else-if="isImage || isVideo" class="bx-im-message-panel__image">
				<img 
					v-if="this.messageFile.urlPreview" 
					class="bx-im-message-panel__image_img" 
					:src="this.messageFile.urlPreview"
		                  :alt="this.messageFile.name"
				>
			</div>
			<div class="bx-im-message-panel__content">
				<div class="bx-im-message-panel__title">{{ replyTitle }}</div>
				<div class="bx-im-message-panel__text">{{ messageText }}</div>
			</div>
			<div @click="$emit('close')" class="bx-im-message-panel__close"></div>
		</div>
	`
	};

	const MESSAGE_DISPLAY_LIMIT = 5;

	// @vue/component
	const ForwardPanel = {
	  name: 'ForwardPanel',
	  props: {
	    context: {
	      type: Object,
	      required: true
	    }
	  },
	  emits: ['close'],
	  computed: {
	    forwardContext() {
	      return this.context;
	    },
	    messagesIds() {
	      return this.forwardContext.messagesIds;
	    },
	    sortedMessagesIds() {
	      return [...this.messagesIds].sort();
	    },
	    authorsOfMessages() {
	      return this.sortedMessagesIds.map(id => {
	        const isForward = this.$store.getters['messages/isForward'](id);
	        const message = this.getMessage(id);
	        const userId = isForward ? message.forward.userId : message.authorId;
	        return this.$store.getters['users/get'](userId, true);
	      });
	    },
	    uniqueUsers() {
	      const uniqueUsersObj = {};
	      this.authorsOfMessages.forEach(user => {
	        if (!uniqueUsersObj[user.id]) {
	          uniqueUsersObj[user.id] = user;
	        }
	      });
	      return Object.values(uniqueUsersObj);
	    },
	    forwardMessagesCount() {
	      return this.messagesIds.length;
	    },
	    forwardAuthorName() {
	      const author = this.authorsOfMessages[0];
	      let name = author.name;
	      if (author.id === 0) {
	        name = this.loc('IM_TEXTAREA_FORWARD_SYSTEM');
	      }
	      return `${name}: `;
	    },
	    displayedAuthorNames() {
	      const systemMessagesCount = this.authorsOfMessages.filter(user => user.id === 0).length;
	      const displayedNames = this.uniqueUsers.slice(0, MESSAGE_DISPLAY_LIMIT);
	      const names = [];
	      displayedNames.forEach(user => {
	        if (user.id === 0) {
	          return systemMessagesCount > 1 ? names.push(this.loc('IM_TEXTAREA_FORWARD_MESSAGES_SYSTEM')) : names.push(this.loc('IM_TEXTAREA_FORWARD_SYSTEM'));
	        }
	        if (this.isOwnMessage(user)) {
	          return names.unshift(this.loc('IM_TEXTAREA_FORWARD_OWN_MESSAGE'));
	        }
	        return names.push(user.firstName);
	      });
	      return names.join(', ');
	    },
	    formattedAuthorNames() {
	      if (this.remainingAuthors > 0) {
	        return main_core.Loc.getMessage('IM_TEXTAREA_FORWARD_TEXT_MORE', {
	          '[name]': '<span class="bx-im-message-panel__forward-author_name">',
	          '[/name]': '</span>',
	          '#USER_LIST#': main_core.Text.encode(this.displayedAuthorNames),
	          '[remaining]': '<span class="bx-im-message-panel__forward-author_remaining">',
	          '[/remaining]': '</span>',
	          '#COUNT#': this.remainingAuthors
	        });
	      }
	      return this.loc('IM_TEXTAREA_FORWARD_TEXT', {
	        '#USER_LIST#': main_core.Text.encode(this.displayedAuthorNames)
	      });
	    },
	    remainingAuthors() {
	      return this.uniqueUsers.length - MESSAGE_DISPLAY_LIMIT;
	    },
	    messageText() {
	      return im_v2_lib_parser.Parser.purifyMessage(this.getMessage(this.messagesIds));
	    },
	    titleText() {
	      if (this.forwardMessagesCount > 1) {
	        return this.formattedMessageCounter;
	      }
	      return this.loc('IM_TEXTAREA_FORWARD_TITLE');
	    },
	    formattedMessageCounter() {
	      return main_core.Loc.getMessagePlural('IM_TEXTAREA_FORWARD_TITLE_MULTIPLE_COUNT', this.forwardMessagesCount, {
	        '#COUNT_MESSAGES#': this.forwardMessagesCount
	      });
	    }
	  },
	  methods: {
	    isOwnMessage(user) {
	      return user.id === im_v2_application_core.Core.getUserId() && this.uniqueUsers.length > 1;
	    },
	    getMessage(messageId) {
	      return this.$store.getters['messages/getById'](messageId);
	    },
	    loc(phraseCode, replacements = {}) {
	      return this.$Bitrix.Loc.getMessage(phraseCode, replacements);
	    }
	  },
	  template: `
		<div class="bx-im-message-panel__container">
			<div class="bx-im-message-panel__icon --forward"></div>
			<div class="bx-im-message-panel__content">
				<div class="bx-im-message-panel__title">{{ titleText }}</div>
				<div v-if="forwardMessagesCount > 1" class="bx-im-message-panel__text" :class="{'--compact': remainingAuthors > 0}">
					<div class="bx-im-message-panel__bulk-forward-author" v-html="formattedAuthorNames"></div>
				</div>
				<div v-else class="bx-im-message-panel__text">
					<span class="bx-im-message-panel__forward-author">{{ forwardAuthorName }}</span>
					<span class="bx-im-message-panel__forward-message-text">{{ messageText }}</span>
				</div>
			</div>
			<div @click="$emit('close')" class="bx-im-message-panel__close"></div>
		</div>
	`
	};

	// @vue/component
	const ForwardEntityPanel = {
	  name: 'ForwardEntityPanel',
	  props: {
	    context: {
	      type: Object,
	      required: true
	    }
	  },
	  emits: ['close'],
	  computed: {
	    forwardedEntityContext() {
	      return this.context;
	    },
	    config() {
	      return this.forwardedEntityContext.entityConfig;
	    }
	  },
	  methods: {
	    loc(phraseCode) {
	      return this.$Bitrix.Loc.getMessage(phraseCode);
	    }
	  },
	  template: `
		<div class="bx-im-message-panel__container">
			<div class="bx-im-message-panel__icon --forward"></div>
			<div class="bx-im-message-panel__content">
				<div class="bx-im-message-panel__title">{{ config.title }}</div>
				<div class="bx-im-message-panel__text">
					<span class="bx-im-message-panel__forward-author">author</span>
					<span class="bx-im-message-panel__forward-message-text">message</span>
				</div>
			</div>
			<div @click="$emit('close')" class="bx-im-message-panel__close"></div>
		</div>
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
	const TextareaPanel = {
	  name: 'TextareaPanel',
	  components: {
	    EditPanel,
	    ReplyPanel,
	    ForwardPanel,
	    ForwardEntityPanel,
	    MarketAppsPanel
	  },
	  props: {
	    dialogId: {
	      type: String,
	      required: true
	    },
	    type: {
	      type: String,
	      required: true
	    },
	    context: {
	      type: Object,
	      required: true
	    }
	  },
	  emits: ['close'],
	  computed: {
	    PanelType: () => im_v2_const.TextareaPanelType,
	    configContext() {
	      return this.context;
	    }
	  },
	  template: `
		<EditPanel v-if="type === PanelType.edit" :messageId="configContext.messageId" @close="$emit('close')" />
		<ReplyPanel v-if="type === PanelType.reply" :messageId="configContext.messageId" @close="$emit('close')" />
		<ForwardPanel v-if="type === PanelType.forward" :context="configContext" @close="$emit('close')" />
		<ForwardEntityPanel v-if="type === PanelType.forwardEntity" :context="configContext" @close="$emit('close')" />
		<MarketAppsPanel v-if="type === PanelType.market" :dialogId="dialogId" />
	`
	};

	const MESSAGE_ACTION_PANELS = new Set([im_v2_const.TextareaPanelType.edit, im_v2_const.TextareaPanelType.reply, im_v2_const.TextareaPanelType.forward, im_v2_const.TextareaPanelType.forwardEntity]);
	const TextareaHeight$1 = {
	  max: 400,
	  min: 22
	};

	// @vue/component
	const ChatTextarea = {
	  components: {
	    UploadMenu,
	    SmileSelector,
	    SendButton,
	    UploadPreviewPopup,
	    MentionPopup,
	    TextareaPanel,
	    AudioInput
	  },
	  props: {
	    dialogId: {
	      type: String,
	      default: ''
	    },
	    placeholder: {
	      type: String,
	      default: ''
	    },
	    withCreateMenu: {
	      type: Boolean,
	      default: true
	    },
	    withMarket: {
	      type: Boolean,
	      default: true
	    },
	    withEdit: {
	      type: Boolean,
	      default: true
	    },
	    withUploadMenu: {
	      type: Boolean,
	      default: true
	    },
	    withSmileSelector: {
	      type: Boolean,
	      default: true
	    },
	    withAudioInput: {
	      type: Boolean,
	      default: true
	    },
	    draftManagerClass: {
	      type: Function,
	      default: im_v2_lib_draft.DraftManager
	    }
	  },
	  emits: ['mounted'],
	  data() {
	    return {
	      text: '',
	      textareaHeight: TextareaHeight$1.min,
	      showMention: false,
	      mentionQuery: '',
	      showUploadPreviewPopup: false,
	      previewPopupUploaderId: '',
	      panelType: im_v2_const.TextareaPanelType.none,
	      panelContext: {
	        messageId: 0
	      }
	    };
	  },
	  computed: {
	    dialog() {
	      return this.$store.getters['chats/get'](this.dialogId, true);
	    },
	    dialogInited() {
	      return this.dialog.inited;
	    },
	    replyMode() {
	      return this.panelType === im_v2_const.TextareaPanelType.reply;
	    },
	    forwardMode() {
	      return this.panelType === im_v2_const.TextareaPanelType.forward;
	    },
	    forwardEntityMode() {
	      return this.panelType === im_v2_const.TextareaPanelType.forwardEntity;
	    },
	    editMode() {
	      return this.panelType === im_v2_const.TextareaPanelType.edit;
	    },
	    marketMode() {
	      return this.panelType === im_v2_const.TextareaPanelType.market;
	    },
	    isDisabled() {
	      return this.text.trim() === '' && !this.editMode && !this.forwardMode && !this.forwardEntityMode;
	    },
	    textareaPlaceholder() {
	      if (!this.placeholder) {
	        return this.loc('IM_TEXTAREA_PLACEHOLDER_V3');
	      }
	      return this.placeholder;
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
	    isChannelType() {
	      return im_v2_lib_channel.ChannelManager.isChannel(this.dialogId);
	    },
	    isEmptyText() {
	      return this.text === '';
	    }
	  },
	  watch: {
	    text(newValue) {
	      this.adjustTextareaHeight();
	      this.getDraftManager().setDraftText(this.dialogId, newValue);
	      if (main_core.Type.isStringFilled(newValue)) {
	        this.getTypingService().startTyping();
	      }
	    }
	  },
	  created() {
	    this.initResizeManager();
	    this.restoreTextareaHeight();
	    this.restoreDraft();
	    this.initSendingService();
	    main_core_events.EventEmitter.subscribe(im_v2_const.EventType.textarea.insertMention, this.onInsertMention);
	    main_core_events.EventEmitter.subscribe(im_v2_const.EventType.textarea.insertText, this.onInsertText);
	    main_core_events.EventEmitter.subscribe(im_v2_const.EventType.textarea.editMessage, this.onEditMessage);
	    main_core_events.EventEmitter.subscribe(im_v2_const.EventType.textarea.replyMessage, this.onReplyMessage);
	    main_core_events.EventEmitter.subscribe(im_v2_const.EventType.textarea.forwardEntity, this.onForwardEntity);
	    main_core_events.EventEmitter.subscribe(im_v2_const.EventType.textarea.sendMessage, this.onSendMessage);
	    main_core_events.EventEmitter.subscribe(im_v2_const.EventType.textarea.insertForward, this.onInsertForward);
	    main_core_events.EventEmitter.subscribe(im_v2_const.EventType.textarea.openUploadPreview, this.onOpenUploadPreview);
	    main_core_events.EventEmitter.subscribe(im_v2_const.EventType.dialog.onMessageDeleted, this.onMessageDeleted);
	  },
	  mounted() {
	    this.initMentionManager();
	    this.focus();
	    this.$emit('mounted');
	  },
	  beforeUnmount() {
	    this.resizeManager.destroy();
	    main_core_events.EventEmitter.unsubscribe(im_v2_const.EventType.textarea.insertMention, this.onInsertMention);
	    main_core_events.EventEmitter.unsubscribe(im_v2_const.EventType.textarea.insertText, this.onInsertText);
	    main_core_events.EventEmitter.unsubscribe(im_v2_const.EventType.textarea.editMessage, this.onEditMessage);
	    main_core_events.EventEmitter.unsubscribe(im_v2_const.EventType.textarea.replyMessage, this.onReplyMessage);
	    main_core_events.EventEmitter.unsubscribe(im_v2_const.EventType.textarea.forwardEntity, this.onForwardEntity);
	    main_core_events.EventEmitter.unsubscribe(im_v2_const.EventType.textarea.sendMessage, this.onSendMessage);
	    main_core_events.EventEmitter.unsubscribe(im_v2_const.EventType.textarea.insertForward, this.onInsertForward);
	    main_core_events.EventEmitter.unsubscribe(im_v2_const.EventType.textarea.openUploadPreview, this.onOpenUploadPreview);
	    main_core_events.EventEmitter.unsubscribe(im_v2_const.EventType.dialog.onMessageDeleted, this.onMessageDeleted);
	  },
	  methods: {
	    sendMessage() {
	      this.text = this.text.trim();
	      if (this.isDisabled || !this.dialogInited) {
	        return;
	      }
	      const text = this.mentionManager.replaceMentions(this.text);
	      if (this.hasActiveMessageAction()) {
	        this.handlePanelAction(text);
	        this.closePanel();
	      } else {
	        this.getSendingService().sendMessage({
	          text,
	          dialogId: this.dialogId
	        });
	      }
	      this.getTypingService().stopTyping();
	      this.clear();
	      this.getDraftManager().clearDraft(this.dialogId);
	      im_v2_lib_soundNotification.SoundNotificationManager.getInstance().playOnce(im_v2_const.SoundType.send);
	      this.focus();
	      main_core_events.EventEmitter.emit(im_v2_const.EventType.textarea.onAfterSendMessage);
	    },
	    handlePanelAction(text) {
	      if (this.editMode && text === '') {
	        void this.getMessageService().deleteMessage(this.panelContext.messageId);
	      } else if (this.editMode && text !== '') {
	        this.getMessageService().editMessageText(this.panelContext.messageId, text);
	      } else if (this.forwardMode) {
	        this.getSendingService().forwardMessages({
	          text,
	          dialogId: this.dialogId,
	          forwardIds: this.panelContext.messagesIds
	        });
	      } else if (this.forwardEntityMode) {
	        console.error('sending forwarded entity message');
	      } else if (this.replyMode) {
	        this.getSendingService().sendMessage({
	          text,
	          dialogId: this.dialogId,
	          replyId: this.panelContext.messageId
	        });
	      }
	    },
	    clear() {
	      var _this$mentionManager;
	      this.text = '';
	      (_this$mentionManager = this.mentionManager) == null ? void 0 : _this$mentionManager.clearMentionReplacements();
	    },
	    hasActiveMessageAction() {
	      return MESSAGE_ACTION_PANELS.has(this.panelType);
	    },
	    closePanel() {
	      if (this.editMode) {
	        this.clear();
	      }
	      this.panelType = im_v2_const.TextareaPanelType.none;
	      this.panelContext = {
	        messageId: 0
	      };
	      this.draftManager.setDraftPanel(this.dialogId, this.panelType, this.panelContext);
	    },
	    openEditPanel(messageId) {
	      if (!this.withEdit) {
	        return;
	      }
	      const message = this.$store.getters['messages/getById'](messageId);
	      if (message.isDeleted) {
	        return;
	      }
	      this.panelType = im_v2_const.TextareaPanelType.edit;
	      this.panelContext.messageId = messageId;
	      const mentions = this.mentionManager.extractMentions(message.text);
	      this.mentionManager.setMentionReplacements(mentions);
	      this.text = im_v2_lib_parser.Parser.prepareEdit(message);
	      this.focus();
	      this.draftManager.setDraftText(this.dialogId, this.text);
	      this.draftManager.setDraftPanel(this.dialogId, this.panelType, this.panelContext);
	      this.draftManager.setDraftMentions(this.dialogId, mentions);
	    },
	    openReplyPanel(messageId) {
	      if (this.editMode) {
	        this.clear();
	      }
	      this.panelType = im_v2_const.TextareaPanelType.reply;
	      this.panelContext.messageId = messageId;
	      this.focus();
	      this.draftManager.setDraftPanel(this.dialogId, this.panelType, this.panelContext);
	    },
	    openForwardPanel(messagesIds) {
	      this.panelType = im_v2_const.TextareaPanelType.forward;
	      this.panelContext.messageId = 0;
	      this.panelContext.messagesIds = messagesIds;
	      this.clear();
	      this.focus();
	      this.draftManager.setDraftPanel(this.dialogId, this.panelType, this.panelContext);
	    },
	    async openForwardEntityPanel(entityConfig) {
	      this.panelType = im_v2_const.TextareaPanelType.forwardEntity;
	      this.panelContext.messageId = 0;
	      this.panelContext.entityConfig = entityConfig;
	      this.clear();
	      this.focus();
	    },
	    toggleMarketPanel() {
	      if (this.marketMode) {
	        this.panelType = im_v2_const.TextareaPanelType.none;
	        return;
	      }
	      this.panelType = im_v2_const.TextareaPanelType.market;
	      this.panelContext.messageId = 0;
	    },
	    async adjustTextareaHeight() {
	      this.textareaHeight = 'auto';
	      await this.$nextTick();
	      const newMaxPoint = Math.min(TextareaHeight$1.max, this.$refs.textarea.scrollHeight);
	      if (this.resizedTextareaHeight) {
	        this.textareaHeight = Math.max(newMaxPoint, this.resizedTextareaHeight);
	        return;
	      }
	      this.textareaHeight = Math.max(newMaxPoint, TextareaHeight$1.min);
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
	    async restoreDraft() {
	      const {
	        text = '',
	        panelType = im_v2_const.TextareaPanelType.none,
	        panelContext = {
	          messageId: 0
	        }
	      } = await this.getDraftManager().getDraft(this.dialogId);
	      this.text = text;
	      if (this.panelType === im_v2_const.TextareaPanelType.none) {
	        this.panelType = panelType;
	      }
	      this.panelContext = panelContext;
	    },
	    async onKeyDown(event) {
	      if (this.showMention) {
	        this.mentionManager.onActiveMentionKeyDown(event);
	        return;
	      }
	      const exitActionCombination = im_v2_lib_utils.Utils.key.isCombination(event, 'Escape');
	      if (this.hasActiveMessageAction() && exitActionCombination) {
	        this.closePanel();
	        return;
	      }
	      const sendMessageCombination = im_v2_lib_hotkey.isSendMessageCombination(event);
	      const newLineCombination = im_v2_lib_hotkey.isNewLineCombination(event);
	      if (sendMessageCombination && !newLineCombination) {
	        event.preventDefault();
	        this.sendMessage();
	        return;
	      }
	      if (newLineCombination) {
	        this.handleNewLine();
	        return;
	      }
	      const tabCombination = im_v2_lib_utils.Utils.key.isCombination(event, 'Tab');
	      if (tabCombination) {
	        this.handleTab(event);
	        return;
	      }
	      const decorationCombination = im_v2_lib_utils.Utils.key.isExactCombination(event, ['Ctrl+b', 'Ctrl+i', 'Ctrl+u', 'Ctrl+s']);
	      if (decorationCombination) {
	        event.preventDefault();
	        this.text = im_v2_lib_textarea.Textarea.handleDecorationTag(this.$refs.textarea, event.code);
	        return;
	      }
	      if (this.text === '' && im_v2_lib_utils.Utils.key.isCombination(event, 'ArrowUp')) {
	        this.handleLastOwnMessageEdit(event);
	        return;
	      }
	      this.mentionManager.onKeyDown(event);
	    },
	    handleNewLine() {
	      this.text = im_v2_lib_textarea.Textarea.addNewLine(this.$refs.textarea);
	    },
	    handleTab(event) {
	      event.preventDefault();
	      if (event.shiftKey) {
	        this.text = im_v2_lib_textarea.Textarea.removeTab(this.$refs.textarea);
	        return;
	      }
	      this.text = im_v2_lib_textarea.Textarea.addTab(this.$refs.textarea);
	    },
	    handleLastOwnMessageEdit(event) {
	      event.preventDefault();
	      const lastOwnMessageId = this.$store.getters['messages/getLastOwnMessageId'](this.dialog.chatId);
	      const isForward = this.$store.getters['messages/isForward'](lastOwnMessageId);
	      if (lastOwnMessageId && !isForward) {
	        this.openEditPanel(lastOwnMessageId);
	      }
	    },
	    onSendMessage(event) {
	      const {
	        text,
	        dialogId
	      } = event.getData();
	      if (this.dialogId !== dialogId) {
	        return;
	      }
	      this.getSendingService().sendMessage({
	        text,
	        dialogId: this.dialogId
	      });
	    },
	    onResizeStart(event) {
	      this.resizeManager.onResizeStart(event, this.textareaHeight);
	    },
	    async onFileSelect({
	      event,
	      sendAsFile
	    }) {
	      const uploaderId = await this.getUploadingService().uploadFromInput({
	        event,
	        sendAsFile,
	        dialogId: this.dialogId
	      });
	      this.showUploadPreviewPopup = true;
	      this.previewPopupUploaderId = uploaderId;
	    },
	    onDiskFileSelect({
	      files
	    }) {
	      this.getUploadingService().uploadFileFromDisk(files, this.dialogId);
	    },
	    onInsertMention(event) {
	      const {
	        mentionText,
	        mentionReplacement,
	        dialogId,
	        isMentionSymbol = true
	      } = event.getData();
	      let {
	        textToReplace = ''
	      } = event.getData();
	      if (this.dialogId !== dialogId) {
	        return;
	      }
	      const mentions = this.mentionManager.addMentionReplacement(mentionText, mentionReplacement);
	      this.draftManager.setDraftMentions(this.dialogId, mentions);
	      const mentionSymbol = isMentionSymbol ? this.mentionManager.getMentionSymbol() : '';
	      textToReplace = `${mentionSymbol}${textToReplace}`;
	      this.text = im_v2_lib_textarea.Textarea.insertMention(this.$refs.textarea, {
	        textToInsert: mentionText,
	        textToReplace
	      });
	      this.mentionManager.clearMentionSymbol();
	    },
	    onInsertText(event) {
	      const {
	        dialogId
	      } = event.getData();
	      if (this.dialogId !== dialogId) {
	        return;
	      }
	      this.text = im_v2_lib_textarea.Textarea.insertText(this.$refs.textarea, event.getData());
	    },
	    onEditMessage(event) {
	      const {
	        messageId,
	        dialogId
	      } = event.getData();
	      if (this.dialogId !== dialogId) {
	        return;
	      }
	      this.openEditPanel(messageId);
	    },
	    onReplyMessage(event) {
	      const {
	        messageId,
	        dialogId
	      } = event.getData();
	      if (this.dialogId !== dialogId) {
	        return;
	      }
	      this.openReplyPanel(messageId);
	    },
	    onForwardEntity(event) {
	      const {
	        dialogId,
	        entityConfig
	      } = event.getData();
	      if (this.dialogId !== dialogId) {
	        return;
	      }
	      this.openForwardEntityPanel(entityConfig);
	    },
	    onInsertForward(event) {
	      const {
	        messagesIds,
	        dialogId
	      } = event.getData();
	      if (this.dialogId !== dialogId) {
	        return;
	      }
	      this.openForwardPanel(messagesIds);
	    },
	    async onPaste(clipboardEvent) {
	      if (!this.withUploadMenu) {
	        return;
	      }
	      const uploaderId = await this.getUploadingService().uploadFromClipboard({
	        clipboardEvent,
	        dialogId: this.dialogId,
	        imagesOnly: !this.isChannelType
	      });
	      if (!uploaderId) {
	        return;
	      }
	      this.showUploadPreviewPopup = true;
	      this.previewPopupUploaderId = uploaderId;
	    },
	    onOpenUploadPreview(event) {
	      const {
	        uploaderId
	      } = event.getData();
	      this.showUploadPreviewPopup = true;
	      this.previewPopupUploaderId = uploaderId;
	    },
	    onMarketIconClick() {
	      this.toggleMarketPanel();
	    },
	    onMessageDeleted(event) {
	      const {
	        messageId
	      } = event.getData();
	      if (this.panelContext.messageId === messageId) {
	        this.closePanel();
	      }
	    },
	    initResizeManager() {
	      this.resizeManager = new ResizeManager({
	        direction: ResizeDirection.up,
	        maxHeight: TextareaHeight$1.max,
	        minHeight: TextareaHeight$1.min
	      });
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
	    initSendingService() {
	      if (this.sendingService) {
	        return;
	      }
	      this.sendingService = im_v2_provider_service.SendingService.getInstance();
	    },
	    async initMentionManager() {
	      const {
	        mentions = {}
	      } = await this.getDraftManager().getDraft(this.dialogId);
	      this.mentionManager = new MentionManager(this.$refs.textarea);
	      this.mentionManager.setMentionReplacements(mentions);
	      this.mentionManager.subscribe(MentionManagerEvents.showMentionPopup, event => {
	        const {
	          mentionQuery
	        } = event.getData();
	        this.showMentionPopup(mentionQuery);
	      });
	      this.mentionManager.subscribe(MentionManagerEvents.hideMentionPopup, () => {
	        this.closeMentionPopup();
	      });
	    },
	    getSendingService() {
	      return this.sendingService;
	    },
	    getTypingService() {
	      if (!this.typingService) {
	        this.typingService = new TypingService(this.dialogId);
	      }
	      return this.typingService;
	    },
	    getDraftManager() {
	      if (!this.draftManager) {
	        this.draftManager = this.draftManagerClass.getInstance();
	      }
	      return this.draftManager;
	    },
	    getMessageService() {
	      if (!this.messageService) {
	        this.messageService = new im_v2_provider_service.MessageService({
	          chatId: this.dialog.chatId
	        });
	      }
	      return this.messageService;
	    },
	    getUploadingService() {
	      if (!this.uploadingService) {
	        this.uploadingService = im_v2_provider_service.UploadingService.getInstance();
	      }
	      return this.uploadingService;
	    },
	    onSendFilesFromPreviewPopup(event) {
	      this.text = '';
	      const {
	        groupFiles,
	        text,
	        uploaderId
	      } = event;
	      if (groupFiles) {
	        return;
	      }
	      const textWithMentions = this.mentionManager.replaceMentions(text);
	      this.getUploadingService().sendMessageWithFiles({
	        uploaderId,
	        text: textWithMentions
	      });
	      this.focus();
	    },
	    closeMentionPopup() {
	      this.showMention = false;
	      this.mentionQuery = '';
	      this.mentionManager.onMentionPopupClose();
	    },
	    showMentionPopup(mentionQuery) {
	      this.mentionQuery = mentionQuery;
	      this.showMention = true;
	    },
	    focus() {
	      var _this$$refs$textarea;
	      (_this$$refs$textarea = this.$refs.textarea) == null ? void 0 : _this$$refs$textarea.focus({
	        preventScroll: true
	      });
	    },
	    loc(phraseCode) {
	      return this.$Bitrix.Loc.getMessage(phraseCode);
	    },
	    onAudioInputStart() {
	      if (this.isEmptyText) {
	        return;
	      }
	      this.text += ' ';
	    },
	    onAudioInputResult(inputText) {
	      this.text += inputText;
	    }
	  },
	  template: `
		<div class="bx-im-send-panel__scope bx-im-send-panel__container">
			<div class="bx-im-textarea__container">
				<div @mousedown="onResizeStart" class="bx-im-textarea__drag-handle"></div>
				<TextareaPanel
					:type="panelType"
					:context="panelContext"
					:dialogId="dialogId"
					@close="closePanel"
				/>
				<div class="bx-im-textarea__content" ref="textarea-content">
					<div class="bx-im-textarea__left">
						<div v-if="withUploadMenu" class="bx-im-textarea__upload_container">
							<UploadMenu 
								:dialogId="dialogId" 
								@fileSelect="onFileSelect" 
								@diskFileSelect="onDiskFileSelect" 
							/>
						</div>
						<textarea
							v-model="text"
							:style="textareaStyle"
							:placeholder="textareaPlaceholder"
							:maxlength="textareaMaxLength"
							@keydown="onKeyDown"
							@paste="onPaste"
							class="bx-im-textarea__element"
							ref="textarea"
							rows="1"
						></textarea>
						<AudioInput
							v-if="withAudioInput"
							@inputStart="onAudioInputStart"
							@inputResult="onAudioInputResult"
						/>
					</div>
					<div class="bx-im-textarea__right">
						<div class="bx-im-textarea__action-panel">
							<div
								v-if="withMarket"
								:title="loc('IM_TEXTAREA_ICON_APPLICATION')"
								@click="onMarketIconClick"
								class="bx-im-textarea__icon --market"
								:class="{'--active': marketMode}"
							></div>
							<SmileSelector 
								v-if="withSmileSelector" 
								:dialogId="dialogId" 
							/>
						</div>
					</div>
				</div>
			</div>
			<SendButton :dialogId="dialogId" :editMode="editMode" :isDisabled="isDisabled" @click="sendMessage" />
			<UploadPreviewPopup
				v-if="showUploadPreviewPopup"
				:dialogId="dialogId"
				:uploaderId="previewPopupUploaderId"
				:textareaValue="text"
				@close="showUploadPreviewPopup = false"
				@sendFiles="onSendFilesFromPreviewPopup"
			/>
			<MentionPopup 
				v-if="showMention" 
				:bindElement="$refs['textarea-content']"
				:dialogId="dialogId"
				:query="mentionQuery"
				@close="closeMentionPopup"
			/>
		</div>
	`
	};

	exports.ChatTextarea = ChatTextarea;

}((this.BX.Messenger.v2.Component = this.BX.Messenger.v2.Component || {}),BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX.Calendar.Sharing,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX.Main,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX.Messenger.v2.Service,BX.Messenger.v2.Component.Message,BX,BX.Event,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX.Messenger.v2.Application,BX.Messenger.v2.Lib,BX.Messenger.v2.Const,BX,BX.Messenger.v2.Lib,BX.Messenger.v2.Component.Elements));
//# sourceMappingURL=textarea.bundle.js.map
