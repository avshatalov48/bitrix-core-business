/* eslint-disable */
this.BX = this.BX || {};
this.BX.Messenger = this.BX.Messenger || {};
this.BX.Messenger.v2 = this.BX.Messenger.v2 || {};
(function (exports,im_v2_lib_utils) {
	'use strict';

	const highlightText = (text, textToHighlight = '') => {
	  if (textToHighlight.length === 0) {
	    return text;
	  }
	  const wordsToHighlight = getWordsToHighlight(textToHighlight);
	  const pattern = createRegExPatternFromWords(wordsToHighlight);
	  return text.replaceAll(new RegExp(pattern, 'ig'), wrapWithSpan);
	};
	const wrapWithSpan = text => `<span class="--highlight">${text}</span>`;
	const getWordsToHighlight = textToHighlight => {
	  const wordsToHighlight = im_v2_lib_utils.Utils.text.getWordsFromString(textToHighlight);
	  const result = new Set(wordsToHighlight);
	  return [...result];
	};
	const createRegExPatternFromWords = words => {
	  return words.map(word => {
	    return BX.util.escapeRegExp(word);
	  }).join('|');
	};

	exports.highlightText = highlightText;

}((this.BX.Messenger.v2.Lib = this.BX.Messenger.v2.Lib || {}),BX.Messenger.v2.Lib));
//# sourceMappingURL=text-highlighter.bundle.js.map
