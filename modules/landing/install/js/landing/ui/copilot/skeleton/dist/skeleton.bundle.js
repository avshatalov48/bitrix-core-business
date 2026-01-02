/* eslint-disable */
this.BX = this.BX || {};
this.BX.Landing = this.BX.Landing || {};
this.BX.Landing.UI = this.BX.Landing.UI || {};
(function (exports,main_core) {
	'use strict';

	const isElementVisible = element => {
	  let current = element;
	  while (current) {
	    const style = window.getComputedStyle(current);
	    if (style.display === 'none' || style.visibility === 'hidden' || style.opacity === '0') {
	      return false;
	    }
	    current = current.parentElement;
	  }
	  const rect = main_core.Dom.getPosition(element);
	  return (rect == null ? void 0 : rect.width) > 1 && (rect == null ? void 0 : rect.height) > 1;
	};

	class SkeletonBlock {
	  async show() {
	    throw new Error('You must implement the show method!');
	  }
	  hide() {
	    throw new Error('You must implement the hide method!');
	  }
	}

	async function wait(ms) {
	  return new Promise(resolve => {
	    setTimeout(() => {
	      resolve();
	    }, ms);
	  });
	}

	var _iconElement = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("iconElement");
	var _iconFontawesomeClassNames = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("iconFontawesomeClassNames");
	var _iconStubClassNames = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("iconStubClassNames");
	var _replaceStubWithIcon = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("replaceStubWithIcon");
	var _getFontawesomeClassNamesFromIcon = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getFontawesomeClassNamesFromIcon");
	var _addClassNames = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("addClassNames");
	var _removeClassNames = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("removeClassNames");
	class SkeletonIconBlock extends SkeletonBlock {
	  constructor(options) {
	    super(options);
	    Object.defineProperty(this, _removeClassNames, {
	      value: _removeClassNames2
	    });
	    Object.defineProperty(this, _addClassNames, {
	      value: _addClassNames2
	    });
	    Object.defineProperty(this, _getFontawesomeClassNamesFromIcon, {
	      value: _getFontawesomeClassNamesFromIcon2
	    });
	    Object.defineProperty(this, _replaceStubWithIcon, {
	      value: _replaceStubWithIcon2
	    });
	    Object.defineProperty(this, _iconElement, {
	      writable: true,
	      value: null
	    });
	    Object.defineProperty(this, _iconFontawesomeClassNames, {
	      writable: true,
	      value: ''
	    });
	    Object.defineProperty(this, _iconStubClassNames, {
	      writable: true,
	      value: ['fa', 'fas', 'fa-stars']
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _iconElement)[_iconElement] = options.iconElement;
	    babelHelpers.classPrivateFieldLooseBase(this, _iconFontawesomeClassNames)[_iconFontawesomeClassNames] = babelHelpers.classPrivateFieldLooseBase(this, _getFontawesomeClassNamesFromIcon)[_getFontawesomeClassNamesFromIcon](babelHelpers.classPrivateFieldLooseBase(this, _iconElement)[_iconElement]);
	  }
	  async show() {
	    await babelHelpers.classPrivateFieldLooseBase(this, _replaceStubWithIcon)[_replaceStubWithIcon]();
	  }
	  hide() {
	    babelHelpers.classPrivateFieldLooseBase(this, _removeClassNames)[_removeClassNames](babelHelpers.classPrivateFieldLooseBase(this, _iconElement)[_iconElement], babelHelpers.classPrivateFieldLooseBase(this, _iconFontawesomeClassNames)[_iconFontawesomeClassNames]);
	    babelHelpers.classPrivateFieldLooseBase(this, _addClassNames)[_addClassNames](babelHelpers.classPrivateFieldLooseBase(this, _iconElement)[_iconElement], babelHelpers.classPrivateFieldLooseBase(this, _iconStubClassNames)[_iconStubClassNames]);
	  }
	}
	async function _replaceStubWithIcon2() {
	  main_core.Dom.style(babelHelpers.classPrivateFieldLooseBase(this, _iconElement)[_iconElement], {
	    transition: '0.2s ease-in-out opacity',
	    opacity: 0
	  });
	  await wait(200);
	  babelHelpers.classPrivateFieldLooseBase(this, _removeClassNames)[_removeClassNames](babelHelpers.classPrivateFieldLooseBase(this, _iconElement)[_iconElement], babelHelpers.classPrivateFieldLooseBase(this, _iconStubClassNames)[_iconStubClassNames]);
	  babelHelpers.classPrivateFieldLooseBase(this, _addClassNames)[_addClassNames](babelHelpers.classPrivateFieldLooseBase(this, _iconElement)[_iconElement], babelHelpers.classPrivateFieldLooseBase(this, _iconFontawesomeClassNames)[_iconFontawesomeClassNames]);
	  main_core.Dom.style(babelHelpers.classPrivateFieldLooseBase(this, _iconElement)[_iconElement], {
	    opacity: 1
	  });
	  await wait(200);
	  main_core.Dom.style(babelHelpers.classPrivateFieldLooseBase(this, _iconElement)[_iconElement], {
	    transition: null,
	    opacity: null
	  });
	}
	function _getFontawesomeClassNamesFromIcon2(iconElement) {
	  return [...iconElement.classList].filter(classname => classname.startsWith('fa'));
	}
	function _addClassNames2(element, classNames) {
	  classNames.forEach(className => {
	    main_core.Dom.addClass(element, className);
	  });
	}
	function _removeClassNames2(element, classNames) {
	  classNames.forEach(className => {
	    main_core.Dom.removeClass(element, className);
	  });
	}

	const hexToRgb = hex => {
	  const result = /^#?([\da-f]{2})([\da-f]{2})([\da-f]{2})$/i.exec(hex);
	  return result ? {
	    r: parseInt(result[1], 16),
	    g: parseInt(result[2], 16),
	    b: parseInt(result[3], 16)
	  } : null;
	};

	const isTextNodeInButton = textNode => {
	  return main_core.Dom.hasClass(textNode == null ? void 0 : textNode.parentElement, 'btn');
	};

	let _ = t => t,
	  _t,
	  _t2;
	var _textNode = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("textNode");
	var _textNodeContainer = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("textNodeContainer");
	var _text = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("text");
	var _rectangles = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("rectangles");
	var _windowResizeHandler = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("windowResizeHandler");
	var _fixWidthAndHeightForTextNodeContainer = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("fixWidthAndHeightForTextNodeContainer");
	var _addTextWithAnimation = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("addTextWithAnimation");
	var _addTextWithWordAnimation = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("addTextWithWordAnimation");
	var _addTextWithLetterAnimation = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("addTextWithLetterAnimation");
	var _renderAnimatedTextElement = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("renderAnimatedTextElement");
	var _hideAllRectangles = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("hideAllRectangles");
	var _hideRectangleWithAnimation = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("hideRectangleWithAnimation");
	var _isShowRectanglesOnText = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("isShowRectanglesOnText");
	var _showRectanglesOnText = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("showRectanglesOnText");
	var _getTextNodeRects = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getTextNodeRects");
	var _createRectanglesFromRects = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("createRectanglesFromRects");
	var _updateRectanglesPosition = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("updateRectanglesPosition");
	var _updateRectanglePosition = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("updateRectanglePosition");
	var _getTextNodeColor = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getTextNodeColor");
	class SkeletonTextBlock extends SkeletonBlock {
	  constructor(options) {
	    super(options);
	    Object.defineProperty(this, _getTextNodeColor, {
	      value: _getTextNodeColor2
	    });
	    Object.defineProperty(this, _updateRectanglePosition, {
	      value: _updateRectanglePosition2
	    });
	    Object.defineProperty(this, _updateRectanglesPosition, {
	      value: _updateRectanglesPosition2
	    });
	    Object.defineProperty(this, _createRectanglesFromRects, {
	      value: _createRectanglesFromRects2
	    });
	    Object.defineProperty(this, _getTextNodeRects, {
	      value: _getTextNodeRects2
	    });
	    Object.defineProperty(this, _showRectanglesOnText, {
	      value: _showRectanglesOnText2
	    });
	    Object.defineProperty(this, _isShowRectanglesOnText, {
	      value: _isShowRectanglesOnText2
	    });
	    Object.defineProperty(this, _hideRectangleWithAnimation, {
	      value: _hideRectangleWithAnimation2
	    });
	    Object.defineProperty(this, _hideAllRectangles, {
	      value: _hideAllRectangles2
	    });
	    Object.defineProperty(this, _renderAnimatedTextElement, {
	      value: _renderAnimatedTextElement2
	    });
	    Object.defineProperty(this, _addTextWithLetterAnimation, {
	      value: _addTextWithLetterAnimation2
	    });
	    Object.defineProperty(this, _addTextWithWordAnimation, {
	      value: _addTextWithWordAnimation2
	    });
	    Object.defineProperty(this, _addTextWithAnimation, {
	      value: _addTextWithAnimation2
	    });
	    Object.defineProperty(this, _fixWidthAndHeightForTextNodeContainer, {
	      value: _fixWidthAndHeightForTextNodeContainer2
	    });
	    Object.defineProperty(this, _textNode, {
	      writable: true,
	      value: null
	    });
	    Object.defineProperty(this, _textNodeContainer, {
	      writable: true,
	      value: null
	    });
	    Object.defineProperty(this, _text, {
	      writable: true,
	      value: ''
	    });
	    Object.defineProperty(this, _rectangles, {
	      writable: true,
	      value: []
	    });
	    Object.defineProperty(this, _windowResizeHandler, {
	      writable: true,
	      value: void 0
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _textNode)[_textNode] = options.textNode;
	    babelHelpers.classPrivateFieldLooseBase(this, _textNodeContainer)[_textNodeContainer] = babelHelpers.classPrivateFieldLooseBase(this, _textNode)[_textNode].parentElement;
	    babelHelpers.classPrivateFieldLooseBase(this, _text)[_text] = options.textNode.data;
	    babelHelpers.classPrivateFieldLooseBase(this, _windowResizeHandler)[_windowResizeHandler] = babelHelpers.classPrivateFieldLooseBase(this, _updateRectanglesPosition)[_updateRectanglesPosition].bind(this);
	  }
	  hide() {
	    babelHelpers.classPrivateFieldLooseBase(this, _fixWidthAndHeightForTextNodeContainer)[_fixWidthAndHeightForTextNodeContainer]();
	    if (babelHelpers.classPrivateFieldLooseBase(this, _isShowRectanglesOnText)[_isShowRectanglesOnText]()) {
	      babelHelpers.classPrivateFieldLooseBase(this, _showRectanglesOnText)[_showRectanglesOnText]();
	    }
	    main_core.bind(window, 'resize', babelHelpers.classPrivateFieldLooseBase(this, _windowResizeHandler)[_windowResizeHandler]);

	    // the space is need for inline element's height and element with :before,:after
	    babelHelpers.classPrivateFieldLooseBase(this, _textNodeContainer)[_textNodeContainer].innerText = ' ';
	  }
	  async show() {
	    if (babelHelpers.classPrivateFieldLooseBase(this, _isShowRectanglesOnText)[_isShowRectanglesOnText]()) {
	      await babelHelpers.classPrivateFieldLooseBase(this, _hideAllRectangles)[_hideAllRectangles]();
	    }
	    main_core.Dom.style(babelHelpers.classPrivateFieldLooseBase(this, _textNodeContainer)[_textNodeContainer], {
	      whiteSpace: null
	    });
	    await babelHelpers.classPrivateFieldLooseBase(this, _addTextWithAnimation)[_addTextWithAnimation]();
	    main_core.Dom.style(babelHelpers.classPrivateFieldLooseBase(this, _textNodeContainer)[_textNodeContainer], {
	      display: null,
	      width: null,
	      height: null
	    });
	    main_core.unbind(window, 'resize', babelHelpers.classPrivateFieldLooseBase(this, _windowResizeHandler)[_windowResizeHandler]);
	  }
	}
	function _fixWidthAndHeightForTextNodeContainer2() {
	  const textNodeContainerRect = main_core.Dom.getPosition(babelHelpers.classPrivateFieldLooseBase(this, _textNodeContainer)[_textNodeContainer]);
	  main_core.Dom.style(babelHelpers.classPrivateFieldLooseBase(this, _textNodeContainer)[_textNodeContainer], {
	    whiteSpace: 'break-spaces',
	    width: `${textNodeContainerRect.width}px`,
	    height: `${textNodeContainerRect.height}px`
	  });
	}
	async function _addTextWithAnimation2() {
	  if (babelHelpers.classPrivateFieldLooseBase(this, _text)[_text].length > 30) {
	    await babelHelpers.classPrivateFieldLooseBase(this, _addTextWithWordAnimation)[_addTextWithWordAnimation]();
	  } else {
	    await babelHelpers.classPrivateFieldLooseBase(this, _addTextWithLetterAnimation)[_addTextWithLetterAnimation]();
	  }
	}
	async function _addTextWithWordAnimation2() {
	  const span = babelHelpers.classPrivateFieldLooseBase(this, _renderAnimatedTextElement)[_renderAnimatedTextElement]();
	  babelHelpers.classPrivateFieldLooseBase(this, _textNodeContainer)[_textNodeContainer].innerText = '';
	  main_core.Dom.append(span, babelHelpers.classPrivateFieldLooseBase(this, _textNodeContainer)[_textNodeContainer]);
	  const words = babelHelpers.classPrivateFieldLooseBase(this, _text)[_text].split(' ');
	  span.innerText = words[0];
	  for (const word of words.slice(1)) {
	    // eslint-disable-next-line no-await-in-loop
	    await wait(50);
	    span.innerText = `${span.innerText} ${word}`;
	  }
	  main_core.Dom.removeClass(span, 'landing__copilot-skeleton_animated-text');
	  babelHelpers.classPrivateFieldLooseBase(this, _textNodeContainer)[_textNodeContainer].innerText = span.innerText;
	}
	async function _addTextWithLetterAnimation2() {
	  const span = babelHelpers.classPrivateFieldLooseBase(this, _renderAnimatedTextElement)[_renderAnimatedTextElement]();
	  babelHelpers.classPrivateFieldLooseBase(this, _textNodeContainer)[_textNodeContainer].innerText = '';
	  main_core.Dom.append(span, babelHelpers.classPrivateFieldLooseBase(this, _textNodeContainer)[_textNodeContainer]);
	  let spaceWithLetter = '';
	  for (const letter of babelHelpers.classPrivateFieldLooseBase(this, _text)[_text]) {
	    if (letter === ' ') {
	      spaceWithLetter += letter;
	    } else if (spaceWithLetter.length > 0) {
	      span.innerText += spaceWithLetter + letter;
	      spaceWithLetter = '';
	    } else {
	      span.innerText += letter;
	    }

	    // eslint-disable-next-line no-await-in-loop
	    await wait(50);
	  }
	  main_core.Dom.removeClass(span, 'landing__copilot-skeleton_animated-text');
	  babelHelpers.classPrivateFieldLooseBase(this, _textNodeContainer)[_textNodeContainer].innerText = span.innerText;
	}
	function _renderAnimatedTextElement2() {
	  const textBlockLineHeight = parseFloat(getComputedStyle(babelHelpers.classPrivateFieldLooseBase(this, _textNodeContainer)[_textNodeContainer]).lineHeight, 10) || 0;
	  const textBlockFontSize = parseFloat(getComputedStyle(babelHelpers.classPrivateFieldLooseBase(this, _textNodeContainer)[_textNodeContainer]).fontSize, 10) || 24;
	  const labelSize = textBlockFontSize > 24 ? 24 : textBlockFontSize;
	  const copilotAnimationLabelBottomIndent = (textBlockLineHeight - labelSize) / 2;
	  const span = main_core.Tag.render(_t || (_t = _`<span class="landing__copilot-skeleton_animated-text"></span>`));
	  main_core.Dom.style(span, {
	    '--copilot-label-bottom-indent': `${copilotAnimationLabelBottomIndent}px`,
	    '--copilot-label-size': `${labelSize}px`,
	    display: 'inline-block',
	    minHeight: `${textBlockLineHeight}px`
	  });
	  span.innerText = '';
	  return span;
	}
	function _hideAllRectangles2() {
	  const promises = babelHelpers.classPrivateFieldLooseBase(this, _rectangles)[_rectangles].map(rectangle => babelHelpers.classPrivateFieldLooseBase(this, _hideRectangleWithAnimation)[_hideRectangleWithAnimation](rectangle));
	  return Promise.all(promises);
	}
	async function _hideRectangleWithAnimation2(rectangle) {
	  return new Promise(resolve => {
	    main_core.Dom.addClass(rectangle, '--hiding');
	    main_core.bindOnce(rectangle, 'transitionend', () => {
	      rectangle.remove();
	      resolve();
	    });
	  });
	}
	function _isShowRectanglesOnText2() {
	  return isTextNodeInButton(babelHelpers.classPrivateFieldLooseBase(this, _textNode)[_textNode]) === false;
	}
	function _showRectanglesOnText2() {
	  const rectangles = babelHelpers.classPrivateFieldLooseBase(this, _createRectanglesFromRects)[_createRectanglesFromRects](babelHelpers.classPrivateFieldLooseBase(this, _getTextNodeRects)[_getTextNodeRects]());
	  rectangles.forEach(rectangle => {
	    main_core.Dom.append(rectangle, babelHelpers.classPrivateFieldLooseBase(this, _textNodeContainer)[_textNodeContainer].ownerDocument.defaultView.document.body);
	  });
	}
	function _getTextNodeRects2() {
	  const range = babelHelpers.classPrivateFieldLooseBase(this, _textNodeContainer)[_textNodeContainer].ownerDocument.defaultView.document.createRange();
	  range.selectNodeContents(babelHelpers.classPrivateFieldLooseBase(this, _textNode)[_textNode]);
	  const rects = range.getClientRects();
	  return [...rects].map(rect => {
	    const scrollTop = window.pageYOffset || babelHelpers.classPrivateFieldLooseBase(this, _textNodeContainer)[_textNodeContainer].ownerDocument.defaultView.document.documentElement.scrollTop;
	    const heightReductionPercent = 0.5;
	    const heightReduction = parseInt(rect.height * heightReductionPercent, 10);
	    return new DOMRect(rect.left, rect.top + scrollTop + heightReduction / 2, rect.width, rect.height - heightReduction);
	  });
	}
	function _createRectanglesFromRects2(rects) {
	  babelHelpers.classPrivateFieldLooseBase(this, _rectangles)[_rectangles] = rects.map(rect => {
	    const rectangle = main_core.Tag.render(_t2 || (_t2 = _`<div class="landing__copilot-skeleton-rectangle --showing"></div>`));
	    main_core.Dom.style(rectangle, {
	      backgroundColor: babelHelpers.classPrivateFieldLooseBase(this, _getTextNodeColor)[_getTextNodeColor]()
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _updateRectanglePosition)[_updateRectanglePosition](rectangle, rect);
	    return rectangle;
	  });
	  return babelHelpers.classPrivateFieldLooseBase(this, _rectangles)[_rectangles];
	}
	function _updateRectanglesPosition2() {
	  babelHelpers.classPrivateFieldLooseBase(this, _getTextNodeRects)[_getTextNodeRects]().forEach((rect, index) => {
	    const rectangle = babelHelpers.classPrivateFieldLooseBase(this, _rectangles)[_rectangles][index];
	    babelHelpers.classPrivateFieldLooseBase(this, _updateRectanglePosition)[_updateRectanglePosition](rectangle, rect);
	  });
	}
	function _updateRectanglePosition2(rectangle, rect) {
	  const documentWidth = babelHelpers.classPrivateFieldLooseBase(this, _textNodeContainer)[_textNodeContainer].ownerDocument.defaultView.document.documentElement.clientWidth;
	  const overWidth = 5;
	  const overHeight = 0;
	  const rectWidth = rect.width + overWidth;
	  const rectHeight = rect.height + overHeight;
	  const rectLeft = rect.left - overWidth / 2;
	  const rectTop = rect.top - overHeight / 2;
	  const rectRight = documentWidth - rectLeft - rectWidth;
	  main_core.Dom.style(rectangle, {
	    top: `${rectTop}px`,
	    left: `${rectLeft}px`,
	    right: `${rectRight}px`,
	    width: `${rectWidth}px`,
	    height: `${rectHeight}px`
	  });
	}
	function _getTextNodeColor2() {
	  const opacity = 0.25;
	  const textNodeContainerElement = babelHelpers.classPrivateFieldLooseBase(this, _textNode)[_textNode].parentElement;
	  const colorString = getComputedStyle(textNodeContainerElement).color;
	  if (colorString.includes('rgb')) {
	    const rgba = colorString.match(/[\d.]+/g);
	    const [r, g, b] = rgba;
	    return `rgba(${r}, ${g}, ${b}, ${opacity})`;
	  }
	  const {
	    r,
	    g,
	    b
	  } = hexToRgb(colorString);
	  return `rgba(${r},${g},${b}, ${opacity})`;
	}

	var _skeletons = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("skeletons");
	var _skeletonBlocks = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("skeletonBlocks");
	var _hideSkeletonBlocks = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("hideSkeletonBlocks");
	var _getSkeletonBlocks = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getSkeletonBlocks");
	class Skeleton {
	  static initOnBlock(blockId, target) {
	    babelHelpers.classPrivateFieldLooseBase(this, _skeletons)[_skeletons].set(blockId, target);
	    babelHelpers.classPrivateFieldLooseBase(this, _skeletonBlocks)[_skeletonBlocks].set(blockId, babelHelpers.classPrivateFieldLooseBase(this, _getSkeletonBlocks)[_getSkeletonBlocks](blockId));
	    babelHelpers.classPrivateFieldLooseBase(this, _hideSkeletonBlocks)[_hideSkeletonBlocks](blockId);
	  }
	  static async removeSkeletonFromBlock(blockId) {
	    const skeletonBlocks = babelHelpers.classPrivateFieldLooseBase(this, _skeletonBlocks)[_skeletonBlocks].get(blockId);
	    for (const block of skeletonBlocks) {
	      // eslint-disable-next-line no-await-in-loop
	      await block.show();
	    }
	  }
	}
	function _hideSkeletonBlocks2(blockId) {
	  const skeletonBlocks = babelHelpers.classPrivateFieldLooseBase(this, _skeletonBlocks)[_skeletonBlocks].get(blockId);
	  skeletonBlocks.forEach(skeletonBlock => {
	    skeletonBlock.hide();
	  });
	}
	function _getSkeletonBlocks2(blockId) {
	  const target = babelHelpers.classPrivateFieldLooseBase(this, _skeletons)[_skeletons].get(blockId);
	  const skeletonBlocks = [];
	  const treeWalker = document.createTreeWalker(target, NodeFilter.SHOW_TEXT | NodeFilter.SHOW_ELEMENT, null, false);
	  while (treeWalker.nextNode()) {
	    var _currentNode$classNam;
	    const currentNode = treeWalker.currentNode;
	    if (currentNode.nodeType === Node.TEXT_NODE && currentNode.data.trim() !== '' && isElementVisible(currentNode.parentElement)) {
	      const parentElement = currentNode.parentElement;
	      if (main_core.Dom.getPosition(parentElement).left <= window.innerWidth) {
	        currentNode.data = currentNode.data.trim();
	        skeletonBlocks.push(new SkeletonTextBlock({
	          textNode: currentNode
	        }));
	      }
	    } else if (currentNode.nodeType === Node.ELEMENT_NODE && main_core.Type.isStringFilled(currentNode.className) && (_currentNode$classNam = currentNode.className) != null && _currentNode$classNam.split(' ').some(className => className.startsWith('fa-'))) {
	      skeletonBlocks.push(new SkeletonIconBlock({
	        iconElement: currentNode
	      }));
	    }
	  }
	  return skeletonBlocks;
	}
	Object.defineProperty(Skeleton, _getSkeletonBlocks, {
	  value: _getSkeletonBlocks2
	});
	Object.defineProperty(Skeleton, _hideSkeletonBlocks, {
	  value: _hideSkeletonBlocks2
	});
	Object.defineProperty(Skeleton, _skeletons, {
	  writable: true,
	  value: new Map()
	});
	Object.defineProperty(Skeleton, _skeletonBlocks, {
	  writable: true,
	  value: new Map()
	});

	exports.Skeleton = Skeleton;

}((this.BX.Landing.UI.Copilot = this.BX.Landing.UI.Copilot || {}),BX));
//# sourceMappingURL=skeleton.bundle.js.map
