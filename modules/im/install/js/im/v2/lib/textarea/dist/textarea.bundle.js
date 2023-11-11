/* eslint-disable */
this.BX = this.BX || {};
this.BX.Messenger = this.BX.Messenger || {};
this.BX.Messenger.v2 = this.BX.Messenger.v2 || {};
(function (exports) {
	'use strict';

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
	  }
	};

	exports.Textarea = Textarea;

}((this.BX.Messenger.v2.Lib = this.BX.Messenger.v2.Lib || {})));
//# sourceMappingURL=textarea.bundle.js.map
