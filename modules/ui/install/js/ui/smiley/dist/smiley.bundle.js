/* eslint-disable */
this.BX = this.BX || {};
this.BX.UI = this.BX.UI || {};
(function (exports,ui_textParser,main_core) {
	'use strict';

	var _name = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("name");
	var _image = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("image");
	var _typing = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("typing");
	var _width = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("width");
	var _height = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("height");
	class Smiley {
	  constructor(smileyOptions) {
	    Object.defineProperty(this, _name, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _image, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _typing, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _width, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _height, {
	      writable: true,
	      value: void 0
	    });
	    const options = main_core.Type.isPlainObject(smileyOptions) ? smileyOptions : {};
	    this.setName(options.name);
	    this.setImage(options.image);
	    this.setTyping(options.typing);
	    this.setWidth(options.width);
	    this.setHeight(options.height);
	  }
	  getName() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _name)[_name];
	  }
	  setName(value) {
	    babelHelpers.classPrivateFieldLooseBase(this, _name)[_name] = value;
	  }
	  getImage() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _image)[_image];
	  }
	  setImage(value) {
	    babelHelpers.classPrivateFieldLooseBase(this, _image)[_image] = value;
	  }
	  getTyping() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _typing)[_typing];
	  }
	  setTyping(value) {
	    babelHelpers.classPrivateFieldLooseBase(this, _typing)[_typing] = value;
	  }
	  getWidth() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _width)[_width];
	  }
	  setWidth(value) {
	    babelHelpers.classPrivateFieldLooseBase(this, _width)[_width] = value;
	  }
	  getHeight() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _height)[_height];
	  }
	  setHeight(value) {
	    babelHelpers.classPrivateFieldLooseBase(this, _height)[_height] = value;
	  }
	}

	var _splitOffsets = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("splitOffsets");
	var _tokenTree = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("tokenTree");
	var _textParser = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("textParser");
	var _parseSmileys = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("parseSmileys");
	var _consumeSmiley = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("consumeSmiley");
	var _isWordBoundary = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("isWordBoundary");
	var _isNextWordBoundary = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("isNextWordBoundary");
	var _parseEmoji = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("parseEmoji");
	class SmileyParser {
	  constructor(smileys) {
	    Object.defineProperty(this, _parseEmoji, {
	      value: _parseEmoji2
	    });
	    Object.defineProperty(this, _isNextWordBoundary, {
	      value: _isNextWordBoundary2
	    });
	    Object.defineProperty(this, _isWordBoundary, {
	      value: _isWordBoundary2
	    });
	    Object.defineProperty(this, _consumeSmiley, {
	      value: _consumeSmiley2
	    });
	    Object.defineProperty(this, _parseSmileys, {
	      value: _parseSmileys2
	    });
	    Object.defineProperty(this, _splitOffsets, {
	      writable: true,
	      value: []
	    });
	    Object.defineProperty(this, _tokenTree, {
	      writable: true,
	      value: null
	    });
	    Object.defineProperty(this, _textParser, {
	      writable: true,
	      value: null
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _tokenTree)[_tokenTree] = new ui_textParser.TokenTree();
	    smileys.forEach(smiley => {
	      babelHelpers.classPrivateFieldLooseBase(this, _tokenTree)[_tokenTree].addToken(smiley.getTyping());
	    });
	  }
	  parse(text) {
	    babelHelpers.classPrivateFieldLooseBase(this, _splitOffsets)[_splitOffsets] = [];
	    babelHelpers.classPrivateFieldLooseBase(this, _textParser)[_textParser] = new ui_textParser.TextParser(text);
	    while (babelHelpers.classPrivateFieldLooseBase(this, _textParser)[_textParser].hasNext()) {
	      let success = false;
	      success = success || babelHelpers.classPrivateFieldLooseBase(this, _parseEmoji)[_parseEmoji]();
	      success = success || babelHelpers.classPrivateFieldLooseBase(this, _parseSmileys)[_parseSmileys]();
	      success = success || babelHelpers.classPrivateFieldLooseBase(this, _textParser)[_textParser].consumeText();
	    }
	    return babelHelpers.classPrivateFieldLooseBase(this, _splitOffsets)[_splitOffsets];
	  }
	}
	function _parseSmileys2() {
	  if (babelHelpers.classPrivateFieldLooseBase(this, _isWordBoundary)[_isWordBoundary]()) {
	    return babelHelpers.classPrivateFieldLooseBase(this, _textParser)[_textParser].tryChangePosition(() => {
	      const currentPosition = babelHelpers.classPrivateFieldLooseBase(this, _textParser)[_textParser].getCurrentPosition();
	      if (babelHelpers.classPrivateFieldLooseBase(this, _consumeSmiley)[_consumeSmiley]() && babelHelpers.classPrivateFieldLooseBase(this, _isNextWordBoundary)[_isNextWordBoundary]()) {
	        babelHelpers.classPrivateFieldLooseBase(this, _splitOffsets)[_splitOffsets].push({
	          start: currentPosition,
	          end: babelHelpers.classPrivateFieldLooseBase(this, _textParser)[_textParser].getCurrentPosition()
	        });
	        babelHelpers.classPrivateFieldLooseBase(this, _textParser)[_textParser].flushText();
	        return true;
	      }
	      return false;
	    });
	  }
	  return false;
	}
	function _consumeSmiley2() {
	  return babelHelpers.classPrivateFieldLooseBase(this, _textParser)[_textParser].consumeTree(babelHelpers.classPrivateFieldLooseBase(this, _tokenTree)[_tokenTree].getTreeIndex());
	}
	function _isWordBoundary2() {
	  if (!babelHelpers.classPrivateFieldLooseBase(this, _textParser)[_textParser].hasPendingText()) {
	    const last = babelHelpers.classPrivateFieldLooseBase(this, _splitOffsets)[_splitOffsets].at(-1);
	    if (last && last.end === babelHelpers.classPrivateFieldLooseBase(this, _textParser)[_textParser].getCurrentPosition()) {
	      return true;
	    }
	  }
	  return babelHelpers.classPrivateFieldLooseBase(this, _textParser)[_textParser].isWordBoundary();
	}
	function _isNextWordBoundary2() {
	  let isSmileyNext = false;
	  babelHelpers.classPrivateFieldLooseBase(this, _textParser)[_textParser].tryChangePosition(() => {
	    if (babelHelpers.classPrivateFieldLooseBase(this, _consumeSmiley)[_consumeSmiley]()) {
	      isSmileyNext = true;
	    }
	    return false;
	  });
	  if (isSmileyNext) {
	    return true;
	  }
	  return ui_textParser.isDelimiter(babelHelpers.classPrivateFieldLooseBase(this, _textParser)[_textParser].peek());
	}
	function _parseEmoji2() {
	  return false;
	}

	var _smileys = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("smileys");
	class SmileyManager {
	  static getSize() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _smileys)[_smileys].size;
	  }
	  static get(typing) {
	    return babelHelpers.classPrivateFieldLooseBase(this, _smileys)[_smileys].get(typing) || null;
	  }
	  static getAll() {
	    return [...babelHelpers.classPrivateFieldLooseBase(this, _smileys)[_smileys].values()];
	  }
	}
	Object.defineProperty(SmileyManager, _smileys, {
	  writable: true,
	  value: new Map()
	});
	(() => {
	  const settings = main_core.Extension.getSettings('ui.smiley');
	  const smileys = settings.get('smileys', []);
	  for (const smiley of smileys) {
	    babelHelpers.classPrivateFieldLooseBase(SmileyManager, _smileys)[_smileys].set(smiley.typing, new Smiley(smiley));
	  }
	})();

	exports.Smiley = Smiley;
	exports.SmileyParser = SmileyParser;
	exports.SmileyManager = SmileyManager;

}((this.BX.UI.Smiley = this.BX.UI.Smiley || {}),BX.UI.TextParser,BX));
//# sourceMappingURL=smiley.bundle.js.map
