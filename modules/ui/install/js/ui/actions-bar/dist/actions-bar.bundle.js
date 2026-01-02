/* eslint-disable */
this.BX = this.BX || {};
(function (exports,ui_buttons,main_core) {
	'use strict';

	var _buttonsContainer = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("buttonsContainer");
	var _buttons = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("buttons");
	var _resizeObserver = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("resizeObserver");
	var _mutationObserver = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("mutationObserver");
	var _deltas = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("deltas");
	var _collapsable = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("collapsable");
	var _shift = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("shift");
	var _initButtons = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("initButtons");
	var _initResizeObserver = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("initResizeObserver");
	var _initMutationObserver = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("initMutationObserver");
	var _observe = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("observe");
	var _handleContainerWidthUpdate = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("handleContainerWidthUpdate");
	var _isButtonsOverflowContainer = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("isButtonsOverflowContainer");
	var _isEnoughSpaceForExpandedButton = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("isEnoughSpaceForExpandedButton");
	var _getButtonRelativePositionLeft = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getButtonRelativePositionLeft");
	var _expandOneMoreButton = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("expandOneMoreButton");
	var _collapseOneMoreButton = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("collapseOneMoreButton");
	var _getDelta = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getDelta");
	var _useAirDesign = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("useAirDesign");
	var _styleButton = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("styleButton");
	var _buttonColorStyleMap = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("buttonColorStyleMap");
	class RightButtons {
	  constructor(options) {
	    Object.defineProperty(this, _buttonColorStyleMap, {
	      value: _buttonColorStyleMap2
	    });
	    Object.defineProperty(this, _styleButton, {
	      value: _styleButton2
	    });
	    Object.defineProperty(this, _useAirDesign, {
	      value: _useAirDesign2
	    });
	    Object.defineProperty(this, _getDelta, {
	      value: _getDelta2
	    });
	    Object.defineProperty(this, _collapseOneMoreButton, {
	      value: _collapseOneMoreButton2
	    });
	    Object.defineProperty(this, _expandOneMoreButton, {
	      value: _expandOneMoreButton2
	    });
	    Object.defineProperty(this, _getButtonRelativePositionLeft, {
	      value: _getButtonRelativePositionLeft2
	    });
	    Object.defineProperty(this, _isEnoughSpaceForExpandedButton, {
	      value: _isEnoughSpaceForExpandedButton2
	    });
	    Object.defineProperty(this, _isButtonsOverflowContainer, {
	      value: _isButtonsOverflowContainer2
	    });
	    Object.defineProperty(this, _handleContainerWidthUpdate, {
	      value: _handleContainerWidthUpdate2
	    });
	    Object.defineProperty(this, _observe, {
	      value: _observe2
	    });
	    Object.defineProperty(this, _initMutationObserver, {
	      value: _initMutationObserver2
	    });
	    Object.defineProperty(this, _initResizeObserver, {
	      value: _initResizeObserver2
	    });
	    Object.defineProperty(this, _initButtons, {
	      value: _initButtons2
	    });
	    Object.defineProperty(this, _buttonsContainer, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _buttons, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _resizeObserver, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _mutationObserver, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _deltas, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _collapsable, {
	      writable: true,
	      value: void 0
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _buttonsContainer)[_buttonsContainer] = options.buttonsContainer;
	    babelHelpers.classPrivateFieldLooseBase(this, _collapsable)[_collapsable] = options.collapsable === true;
	  }
	  init() {
	    if (babelHelpers.classPrivateFieldLooseBase(this, _useAirDesign)[_useAirDesign]() === false) {
	      return;
	    }
	    babelHelpers.classPrivateFieldLooseBase(this, _initButtons)[_initButtons]();
	    if (babelHelpers.classPrivateFieldLooseBase(this, _collapsable)[_collapsable]) {
	      babelHelpers.classPrivateFieldLooseBase(this, _handleContainerWidthUpdate)[_handleContainerWidthUpdate]();
	      babelHelpers.classPrivateFieldLooseBase(this, _initResizeObserver)[_initResizeObserver]();
	    }
	    babelHelpers.classPrivateFieldLooseBase(this, _initMutationObserver)[_initMutationObserver]();
	    babelHelpers.classPrivateFieldLooseBase(this, _observe)[_observe]();
	  }
	}
	function _initButtons2() {
	  const buttonElements = babelHelpers.classPrivateFieldLooseBase(this, _buttonsContainer)[_buttonsContainer].querySelectorAll('.ui-btn, .ui-btn-split');
	  babelHelpers.classPrivateFieldLooseBase(this, _buttons)[_buttons] = [...buttonElements].map(button => {
	    const btn = ui_buttons.ButtonManager.createFromNode(button);
	    babelHelpers.classPrivateFieldLooseBase(this, _styleButton)[_styleButton](btn);
	    return btn;
	  });
	  babelHelpers.classPrivateFieldLooseBase(this, _deltas)[_deltas] = babelHelpers.classPrivateFieldLooseBase(this, _buttons)[_buttons].map(() => 0);
	}
	function _initResizeObserver2() {
	  babelHelpers.classPrivateFieldLooseBase(this, _resizeObserver)[_resizeObserver] = new ResizeObserver(entries => {
	    // eslint-disable-next-line no-unused-vars
	    for (const entry of entries) {
	      babelHelpers.classPrivateFieldLooseBase(this, _handleContainerWidthUpdate)[_handleContainerWidthUpdate]();
	    }
	  });
	}
	function _initMutationObserver2() {
	  babelHelpers.classPrivateFieldLooseBase(this, _mutationObserver)[_mutationObserver] = new MutationObserver(mutationsList => {
	    mutationsList.forEach(mutation => {
	      if (mutation.type !== 'childList') {
	        return;
	      }
	      mutation.addedNodes.forEach(node => {
	        if (node.nodeType !== Node.ELEMENT_NODE) {
	          return;
	        }
	        if (main_core.Dom.hasClass(node, 'ui-btn') || main_core.Dom.hasClass(node, 'ui-btn-split')) {
	          babelHelpers.classPrivateFieldLooseBase(this, _initButtons)[_initButtons]();
	        }
	        const foundButtons = node.querySelectorAll('.ui-btn, .ui-btn-split');
	        if (foundButtons.length > 0) {
	          babelHelpers.classPrivateFieldLooseBase(this, _initButtons)[_initButtons]();
	        }
	      });
	    });
	  });
	}
	function _observe2() {
	  var _babelHelpers$classPr, _babelHelpers$classPr2;
	  (_babelHelpers$classPr = babelHelpers.classPrivateFieldLooseBase(this, _resizeObserver)[_resizeObserver]) == null ? void 0 : _babelHelpers$classPr.observe(babelHelpers.classPrivateFieldLooseBase(this, _buttonsContainer)[_buttonsContainer]);
	  (_babelHelpers$classPr2 = babelHelpers.classPrivateFieldLooseBase(this, _mutationObserver)[_mutationObserver]) == null ? void 0 : _babelHelpers$classPr2.observe(babelHelpers.classPrivateFieldLooseBase(this, _buttonsContainer)[_buttonsContainer], {
	    childList: true,
	    subtree: true
	  });
	}
	function _handleContainerWidthUpdate2() {
	  if (babelHelpers.classPrivateFieldLooseBase(this, _isButtonsOverflowContainer)[_isButtonsOverflowContainer]()) {
	    babelHelpers.classPrivateFieldLooseBase(this, _collapseOneMoreButton)[_collapseOneMoreButton]();
	  } else if (babelHelpers.classPrivateFieldLooseBase(this, _isEnoughSpaceForExpandedButton)[_isEnoughSpaceForExpandedButton]()) {
	    babelHelpers.classPrivateFieldLooseBase(this, _expandOneMoreButton)[_expandOneMoreButton]();
	  }
	}
	function _isButtonsOverflowContainer2() {
	  return babelHelpers.classPrivateFieldLooseBase(this, _getButtonRelativePositionLeft)[_getButtonRelativePositionLeft]() + babelHelpers.classPrivateFieldLooseBase(RightButtons, _shift)[_shift] >= 0;
	}
	function _isEnoughSpaceForExpandedButton2() {
	  return babelHelpers.classPrivateFieldLooseBase(this, _getButtonRelativePositionLeft)[_getButtonRelativePositionLeft]() + babelHelpers.classPrivateFieldLooseBase(this, _getDelta)[_getDelta]() + babelHelpers.classPrivateFieldLooseBase(RightButtons, _shift)[_shift] < 0;
	}
	function _getButtonRelativePositionLeft2() {
	  return main_core.Dom.getRelativePosition(babelHelpers.classPrivateFieldLooseBase(this, _buttonsContainer)[_buttonsContainer], babelHelpers.classPrivateFieldLooseBase(this, _buttons)[_buttons][0].getContainer()).left;
	}
	function _expandOneMoreButton2() {
	  const collapsedButtonIndex = babelHelpers.classPrivateFieldLooseBase(this, _buttons)[_buttons].findIndex(button => button.isCollapsed());
	  if (collapsedButtonIndex < 0) {
	    return;
	  }
	  const collapsedButton = babelHelpers.classPrivateFieldLooseBase(this, _buttons)[_buttons][collapsedButtonIndex];
	  collapsedButton.setCollapsed(false);
	  babelHelpers.classPrivateFieldLooseBase(this, _deltas)[_deltas][collapsedButtonIndex] = 0;
	  if (babelHelpers.classPrivateFieldLooseBase(this, _isEnoughSpaceForExpandedButton)[_isEnoughSpaceForExpandedButton]()) {
	    babelHelpers.classPrivateFieldLooseBase(this, _expandOneMoreButton)[_expandOneMoreButton]();
	  }
	}
	function _collapseOneMoreButton2() {
	  const notCollapsedButtonIndex = babelHelpers.classPrivateFieldLooseBase(this, _buttons)[_buttons].findLastIndex(button => button.isCollapsed() === false);
	  if (notCollapsedButtonIndex < 0) {
	    return;
	  }
	  const notCollapsedButton = babelHelpers.classPrivateFieldLooseBase(this, _buttons)[_buttons][notCollapsedButtonIndex];
	  babelHelpers.classPrivateFieldLooseBase(this, _deltas)[_deltas][notCollapsedButtonIndex] += notCollapsedButton.getContainer().offsetWidth;
	  notCollapsedButton.setCollapsed(true);
	  babelHelpers.classPrivateFieldLooseBase(this, _deltas)[_deltas][notCollapsedButtonIndex] -= notCollapsedButton.getContainer().offsetWidth;
	  if (babelHelpers.classPrivateFieldLooseBase(this, _isButtonsOverflowContainer)[_isButtonsOverflowContainer]()) {
	    babelHelpers.classPrivateFieldLooseBase(this, _collapseOneMoreButton)[_collapseOneMoreButton]();
	  }
	}
	function _getDelta2() {
	  var _babelHelpers$classPr3;
	  return (_babelHelpers$classPr3 = babelHelpers.classPrivateFieldLooseBase(this, _deltas)[_deltas].find(delta => delta > 0)) != null ? _babelHelpers$classPr3 : 0;
	}
	function _useAirDesign2() {
	  return main_core.Extension.getSettings('ui.actions-bar').get('useAirDesign');
	}
	function _styleButton2(button) {
	  const isButtonHasAirDesign = button.hasAirDesign();
	  button.setAirDesign(true);
	  button.setSize(ui_buttons.ButtonSize.SMALL);
	  if (isButtonHasAirDesign === false) {
	    button.setStyle(babelHelpers.classPrivateFieldLooseBase(this, _buttonColorStyleMap)[_buttonColorStyleMap](button.getColor()));
	  }
	  button.setNoCaps(true);
	}
	function _buttonColorStyleMap2(color) {
	  if (color === ui_buttons.ButtonColor.PRIMARY) {
	    return ui_buttons.AirButtonStyle.FILLED;
	  }
	  return ui_buttons.AirButtonStyle.OUTLINE;
	}
	Object.defineProperty(RightButtons, _shift, {
	  writable: true,
	  value: 32
	});

	const ActionsBar = {
	  RightButtons
	};

	exports.ActionsBar = ActionsBar;

}((this.BX.UI = this.BX.UI || {}),BX.UI,BX));
//# sourceMappingURL=actions-bar.bundle.js.map
