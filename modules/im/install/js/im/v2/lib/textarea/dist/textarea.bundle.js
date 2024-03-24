/* eslint-disable */
this.BX = this.BX || {};
this.BX.Messenger = this.BX.Messenger || {};
this.BX.Messenger.v2 = this.BX.Messenger.v2 || {};
(function (exports,main_core,main_core_events) {
	'use strict';

	const EVENT_NAMESPACE = 'BX.Messenger.v2.Textarea.ResizeManager';
	var _observer = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("observer");
	var _textareaHeight = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("textareaHeight");
	var _initObserver = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("initObserver");
	class ResizeManager extends main_core_events.EventEmitter {
	  constructor() {
	    super();
	    Object.defineProperty(this, _initObserver, {
	      value: _initObserver2
	    });
	    Object.defineProperty(this, _observer, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _textareaHeight, {
	      writable: true,
	      value: void 0
	    });
	    this.setEventNamespace(EVENT_NAMESPACE);
	    babelHelpers.classPrivateFieldLooseBase(this, _initObserver)[_initObserver]();
	  }
	  observeTextarea(element) {
	    babelHelpers.classPrivateFieldLooseBase(this, _observer)[_observer].observe(element);
	    babelHelpers.classPrivateFieldLooseBase(this, _textareaHeight)[_textareaHeight] = element.clientHeight;
	  }
	  unobserveTextarea(element) {
	    babelHelpers.classPrivateFieldLooseBase(this, _observer)[_observer].unobserve(element);
	    babelHelpers.classPrivateFieldLooseBase(this, _textareaHeight)[_textareaHeight] = 0;
	  }
	}
	function _initObserver2() {
	  babelHelpers.classPrivateFieldLooseBase(this, _observer)[_observer] = new ResizeObserver(entries => {
	    entries.forEach(entry => {
	      var _entry$borderBoxSize;
	      const height = (_entry$borderBoxSize = entry.borderBoxSize) == null ? void 0 : _entry$borderBoxSize[0].blockSize;
	      if (main_core.Type.isNumber(height) && height !== babelHelpers.classPrivateFieldLooseBase(this, _textareaHeight)[_textareaHeight]) {
	        this.emit(ResizeManager.events.onHeightChange, {
	          newHeight: height
	        });
	        babelHelpers.classPrivateFieldLooseBase(this, _textareaHeight)[_textareaHeight] = height;
	      }
	    });
	  });
	}
	ResizeManager.events = {
	  onHeightChange: 'onHeightChange'
	};

	const TAB = '\t';
	const NEW_LINE = '\n';
	const LETTER_CODE_PREFIX = 'Key';

	/* eslint-disable no-param-reassign */
	const Textarea = {
	  addTab(textarea) {
	    const newSelectionPosition = textarea.selectionStart + 1;
	    const textBefore = textarea.value.slice(0, textarea.selectionStart);
	    const textAfter = textarea.value.slice(textarea.selectionEnd);
	    const textWithTab = `${textBefore}${TAB}${textAfter}`;
	    textarea.value = textWithTab;
	    textarea.selectionStart = newSelectionPosition;
	    textarea.selectionEnd = newSelectionPosition;
	    return textWithTab;
	  },
	  removeTab(textarea) {
	    const previousSymbol = textarea.value.slice(textarea.selectionStart - 1, textarea.selectionStart);
	    if (previousSymbol !== TAB) {
	      return textarea.value;
	    }
	    const newSelectionPosition = textarea.selectionStart - 1;
	    const textBefore = textarea.value.slice(0, textarea.selectionStart - 1);
	    const textAfter = textarea.value.slice(textarea.selectionEnd);
	    const textWithoutTab = `${textBefore}${textAfter}`;
	    textarea.value = textWithoutTab;
	    textarea.selectionStart = newSelectionPosition;
	    textarea.selectionEnd = newSelectionPosition;
	    return textWithoutTab;
	  },
	  handleDecorationTag(textarea, decorationKey) {
	    decorationKey = decorationKey.replace(LETTER_CODE_PREFIX, '').toLowerCase();
	    const LEFT_TAG = `[${decorationKey}]`;
	    const RIGHT_TAG = `[/${decorationKey}]`;
	    const selectedText = textarea.value.slice(textarea.selectionStart, textarea.selectionEnd);
	    if (!selectedText) {
	      return textarea.value;
	    }
	    const hasDecorationTag = selectedText.toLowerCase().startsWith(LEFT_TAG) && selectedText.toLowerCase().endsWith(RIGHT_TAG);
	    if (hasDecorationTag) {
	      return this.removeDecorationTag(textarea, decorationKey);
	    } else {
	      return this.addDecorationTag(textarea, decorationKey);
	    }
	  },
	  addDecorationTag(textarea, decorationKey) {
	    const LEFT_TAG = `[${decorationKey}]`;
	    const RIGHT_TAG = `[/${decorationKey}]`;
	    const decorationTagLength = LEFT_TAG.length + RIGHT_TAG.length;
	    const newSelectionStart = textarea.selectionStart;
	    const newSelectionEnd = textarea.selectionEnd + decorationTagLength;
	    const textBefore = textarea.value.slice(0, textarea.selectionStart);
	    const selectedText = textarea.value.slice(textarea.selectionStart, textarea.selectionEnd);
	    const textAfter = textarea.value.slice(textarea.selectionEnd);
	    const textWithTag = `${textBefore}${LEFT_TAG}${selectedText}${RIGHT_TAG}${textAfter}`;
	    textarea.value = textWithTag;
	    textarea.selectionStart = newSelectionStart;
	    textarea.selectionEnd = newSelectionEnd;
	    return textWithTag;
	  },
	  removeDecorationTag(textarea, decorationKey) {
	    const LEFT_TAG = `[${decorationKey}]`;
	    const RIGHT_TAG = `[/${decorationKey}]`;
	    const decorationTagLength = LEFT_TAG.length + RIGHT_TAG.length;
	    const newSelectionStart = textarea.selectionStart;
	    const newSelectionEnd = textarea.selectionEnd - decorationTagLength;
	    const textBefore = textarea.value.slice(0, textarea.selectionStart);
	    const textInTagStart = textarea.selectionStart + LEFT_TAG.length;
	    const textInTagEnd = textarea.selectionEnd - RIGHT_TAG.length;
	    const textInTag = textarea.value.slice(textInTagStart, textInTagEnd);
	    const textAfter = textarea.value.slice(textarea.selectionEnd);
	    const textWithoutTag = `${textBefore}${textInTag}${textAfter}`;
	    textarea.value = textWithoutTag;
	    textarea.selectionStart = newSelectionStart;
	    textarea.selectionEnd = newSelectionEnd;
	    return textWithoutTag;
	  },
	  addNewLine(textarea) {
	    const newSelectionPosition = textarea.selectionStart + 1;
	    const textBefore = textarea.value.slice(0, textarea.selectionStart);
	    const textAfter = textarea.value.slice(textarea.selectionEnd);
	    const textWithNewLine = `${textBefore}${NEW_LINE}${textAfter}`;
	    textarea.value = textWithNewLine;
	    textarea.selectionStart = newSelectionPosition;
	    textarea.selectionEnd = newSelectionPosition;
	    return textWithNewLine;
	  },
	  insertText(textarea, config = {}) {
	    const {
	      text,
	      withNewLine = false,
	      replace = false
	    } = config;
	    let resultText = '';
	    if (replace) {
	      resultText = '';
	      textarea.value = '';
	      textarea.selectionStart = 0;
	      textarea.selectionEnd = 0;
	    }
	    if (textarea.value.length === 0) {
	      resultText = text;
	    } else {
	      resultText = withNewLine ? `${textarea.value}${NEW_LINE}${text}` : `${textarea.value} ${text}`;
	    }
	    return resultText;
	  }
	};

	exports.Textarea = Textarea;
	exports.ResizeManager = ResizeManager;

}((this.BX.Messenger.v2.Lib = this.BX.Messenger.v2.Lib || {}),BX,BX.Event));
//# sourceMappingURL=textarea.bundle.js.map
