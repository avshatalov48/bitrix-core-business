/* eslint-disable */
this.BX = this.BX || {};
this.BX.UI = this.BX.UI || {};
(function (exports,main_core) {
	'use strict';

	const CodePoint = {
	  TAB: 9,
	  SPACE: 32,
	  NBSP: 160,
	  NEW_LINE: 10,
	  // \n
	  RETURN: 13,
	  // \r
	  LINE_FEED: 12,
	  // \f
	  EXCLAMATION: 33,
	  // !
	  DOUBLE_QUOTE: 34,
	  HASH: 35,
	  // #
	  SINGLE_QUOTE: 39,
	  ASTERISK: 42,
	  COMMA: 44,
	  DOT: 46,
	  COLON: 58,
	  SEMI_COLON: 59,
	  QUESTION: 63,
	  ROUND_BRACKET_OPEN: 40,
	  ROUND_BRACKET_CLOSE: 41,
	  SQUARE_BRACKET_OPEN: 91,
	  SQUARE_BRACKET_CLOSE: 93,
	  CURLY_BRACKET_OPEN: 123,
	  PIPE: 124,
	  CURLY_BRACKET_CLOSE: 125,
	  HYPHEN: 45
	};

	var _currentPosition = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("currentPosition");
	var _text = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("text");
	var _textStart = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("textStart");
	var _textEnd = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("textEnd");
	var _moveNext = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("moveNext");
	class TextParser {
	  constructor(text, position = 0) {
	    Object.defineProperty(this, _moveNext, {
	      value: _moveNext2
	    });
	    Object.defineProperty(this, _currentPosition, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _text, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _textStart, {
	      writable: true,
	      value: -1
	    });
	    Object.defineProperty(this, _textEnd, {
	      writable: true,
	      value: -1
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _text)[_text] = text;
	    babelHelpers.classPrivateFieldLooseBase(this, _currentPosition)[_currentPosition] = position;
	  }
	  getCurrentPosition() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _currentPosition)[_currentPosition];
	  }
	  tryChangePosition(fn) {
	    const currentPosition = babelHelpers.classPrivateFieldLooseBase(this, _currentPosition)[_currentPosition];
	    const success = fn();
	    if (!success) {
	      babelHelpers.classPrivateFieldLooseBase(this, _currentPosition)[_currentPosition] = currentPosition;
	    }
	    return success;
	  }
	  peek() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _text)[_text].codePointAt(babelHelpers.classPrivateFieldLooseBase(this, _currentPosition)[_currentPosition]);
	  }
	  moveNext() {
	    return this.hasNext() ? babelHelpers.classPrivateFieldLooseBase(this, _moveNext)[_moveNext](this.peek()) : NaN;
	  }
	  peekPrevious() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _text)[_text].codePointAt(babelHelpers.classPrivateFieldLooseBase(this, _currentPosition)[_currentPosition] - 1);
	  }
	  hasNext() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _currentPosition)[_currentPosition] < babelHelpers.classPrivateFieldLooseBase(this, _text)[_text].length;
	  }
	  hasPendingText() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _textStart)[_textStart] !== babelHelpers.classPrivateFieldLooseBase(this, _textEnd)[_textEnd];
	  }
	  flushText() {
	    if (this.hasPendingText()) {
	      babelHelpers.classPrivateFieldLooseBase(this, _textStart)[_textStart] = -1;
	      babelHelpers.classPrivateFieldLooseBase(this, _textEnd)[_textEnd] = -1;
	    }
	  }
	  consume(match) {
	    const codePoint = this.peek();
	    const success = main_core.Type.isFunction(match) ? match(codePoint) : codePoint === match;
	    if (success) {
	      this.moveNext(codePoint);
	    }
	    return success;
	  }
	  consumeWhile(match) {
	    const start = babelHelpers.classPrivateFieldLooseBase(this, _currentPosition)[_currentPosition];
	    while (this.hasNext() && this.consume(match)) {
	      /* */
	    }
	    return babelHelpers.classPrivateFieldLooseBase(this, _currentPosition)[_currentPosition] !== start;
	  }
	  consumePoints(codePoints) {
	    const currentPosition = babelHelpers.classPrivateFieldLooseBase(this, _currentPosition)[_currentPosition];
	    for (const codePoint of codePoints) {
	      const currentCodePoint = this.moveNext();
	      if (codePoint !== currentCodePoint) {
	        babelHelpers.classPrivateFieldLooseBase(this, _currentPosition)[_currentPosition] = currentPosition;
	        return false;
	      }
	    }
	    return true;
	  }
	  consumeTree(treeIndex) {
	    const currentPosition = babelHelpers.classPrivateFieldLooseBase(this, _currentPosition)[_currentPosition];
	    let node = treeIndex;
	    while (this.hasNext()) {
	      const codePoint = this.moveNext();
	      const index = node.get(codePoint);
	      if (main_core.Type.isUndefined(index)) {
	        break;
	      }
	      const [isLeaf, entry] = index;
	      if (isLeaf === true) {
	        this.consumeTree(entry);
	        return true;
	      }
	      node = entry;
	    }
	    babelHelpers.classPrivateFieldLooseBase(this, _currentPosition)[_currentPosition] = currentPosition;
	    return false;
	  }
	  consumeText() {
	    if (babelHelpers.classPrivateFieldLooseBase(this, _textStart)[_textStart] === -1) {
	      babelHelpers.classPrivateFieldLooseBase(this, _textStart)[_textStart] = babelHelpers.classPrivateFieldLooseBase(this, _currentPosition)[_currentPosition];
	      babelHelpers.classPrivateFieldLooseBase(this, _textEnd)[_textEnd] = babelHelpers.classPrivateFieldLooseBase(this, _currentPosition)[_currentPosition];
	    }
	    this.moveNext();
	    babelHelpers.classPrivateFieldLooseBase(this, _textEnd)[_textEnd] = babelHelpers.classPrivateFieldLooseBase(this, _currentPosition)[_currentPosition];
	    return true;
	  }
	  isWordBoundary() {
	    if (babelHelpers.classPrivateFieldLooseBase(this, _currentPosition)[_currentPosition] === 0) {
	      return true;
	    }
	    if (this.hasPendingText()) {
	      return isDelimiter(this.peekPrevious());
	    }
	    return false;
	  }
	}

	// [.,;:!?#-*|[](){}]
	function _moveNext2(code) {
	  babelHelpers.classPrivateFieldLooseBase(this, _currentPosition)[_currentPosition] += code > 0xFFFF ? 2 : 1;
	  return code;
	}
	const wordBoundaries = new Set([CodePoint.DOT, CodePoint.COMMA, CodePoint.SEMI_COLON, CodePoint.COLON, CodePoint.EXCLAMATION, CodePoint.QUESTION, CodePoint.HASH, CodePoint.HYPHEN, CodePoint.ASTERISK, CodePoint.PIPE, CodePoint.ROUND_BRACKET_OPEN, CodePoint.ROUND_BRACKET_CLOSE, CodePoint.SQUARE_BRACKET_OPEN, CodePoint.SQUARE_BRACKET_CLOSE, CodePoint.CURLY_BRACKET_OPEN, CodePoint.CURLY_BRACKET_CLOSE]);
	function isWordBoundary(ch) {
	  return wordBoundaries.has(ch);
	}
	function isTextBound(codePoint) {
	  return main_core.Type.isUndefined(codePoint) || Number.isNaN(codePoint) || isNewLine(codePoint) || isWhitespace(codePoint);
	}
	function isDelimiter(codePoint) {
	  return isTextBound(codePoint) || isWordBoundary(codePoint);
	}
	function isWhitespace(codePoint) {
	  return codePoint === CodePoint.SPACE || codePoint === CodePoint.TAB || codePoint === CodePoint.NBSP;
	}
	function isNewLine(codePoint) {
	  return codePoint === CodePoint.NEW_LINE || codePoint === CodePoint.RETURN || codePoint === CodePoint.LINE_FEED;
	}

	var _index = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("index");
	class TokenTree {
	  constructor() {
	    Object.defineProperty(this, _index, {
	      writable: true,
	      value: new Map()
	    });
	  }
	  getTreeIndex() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _index)[_index];
	  }
	  addToken(token) {
	    if (!main_core.Type.isStringFilled(token)) {
	      return;
	    }
	    let index = babelHelpers.classPrivateFieldLooseBase(this, _index)[_index];
	    for (let i = 0; i < token.length; i++) {
	      const codePoint = token.codePointAt(i);
	      if (i === token.length - 1) {
	        if (index.has(codePoint)) {
	          index.get(codePoint)[0] = true;
	        } else {
	          index.set(codePoint, [true, new Map()]);
	        }
	      } else {
	        if (!index.has(codePoint)) {
	          index.set(codePoint, [false, new Map()]);
	        }
	        [, index] = index.get(codePoint);
	      }
	    }
	  }
	}

	exports.TextParser = TextParser;
	exports.isWordBoundary = isWordBoundary;
	exports.isTextBound = isTextBound;
	exports.isDelimiter = isDelimiter;
	exports.isWhitespace = isWhitespace;
	exports.isNewLine = isNewLine;
	exports.TokenTree = TokenTree;
	exports.CodePoint = CodePoint;

}((this.BX.UI.TextParser = this.BX.UI.TextParser || {}),BX));
//# sourceMappingURL=text-parser.bundle.js.map
