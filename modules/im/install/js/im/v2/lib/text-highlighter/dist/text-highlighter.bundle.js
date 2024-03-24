/* eslint-disable */
this.BX = this.BX || {};
this.BX.Messenger = this.BX.Messenger || {};
this.BX.Messenger.v2 = this.BX.Messenger.v2 || {};
(function (exports,main_core,im_v2_lib_utils) {
	'use strict';

	const highlightText = (text, textToHighlight = '') => {
	  if (textToHighlight.length === 0) {
	    return text;
	  }
	  const wordsToHighlight = getWordsToHighlight(textToHighlight);
	  const pattern = createRegExPatternFromWords(wordsToHighlight);
	  return wrapWithSpan(text, pattern);
	};
	const wrapWithSpan = (text, pattern) => {
	  const decodedText = main_core.Text.decode(text);
	  const textWithPlaceholders = decodedText.replaceAll(new RegExp(pattern, 'ig'), wrapWithSpanPlaceholder);
	  return replacePlaceholders(textWithPlaceholders);
	};
	const wrapWithSpanPlaceholder = text => `#SPAN_START#${text}#SPAN_END#`;
	const replacePlaceholders = textWithPlaceholders => {
	  const encodedText = main_core.Text.encode(textWithPlaceholders);
	  return encodedText.replaceAll('#SPAN_START#', '<span class="--highlight">').replaceAll('#SPAN_END#', '</span>');
	};
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

}((this.BX.Messenger.v2.Lib = this.BX.Messenger.v2.Lib || {}),BX,BX.Messenger.v2.Lib));
//# sourceMappingURL=text-highlighter.bundle.js.map
