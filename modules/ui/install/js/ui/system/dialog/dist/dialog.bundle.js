/* eslint-disable */
this.BX = this.BX || {};
this.BX.UI = this.BX.UI || {};
(function (exports,main_popup,ui_system_typography,ui_iconSet_api_core,ui_iconSet_outline,main_core) {
	'use strict';

	const DialogAnglePositions = Object.freeze({
	  topLeft: 'topLeft',
	  topCenter: 'topCenter',
	  topRight: 'topRight',
	  rightTop: 'rightTop',
	  rightCenter: 'rightCenter',
	  rightBottom: 'rightBottom',
	  bottomLeft: 'bottomLeft',
	  bottomCenter: 'bottomCenter',
	  bottomRight: 'bottomRight',
	  leftTop: 'leftTop',
	  leftCenter: 'leftCenter',
	  leftBottom: 'leftBottom'
	});
	const DialogBackground = Object.freeze({
	  default: 'default',
	  vibrant: 'vibrant'
	});
	const aliases = {
	  onShow: {
	    namespace: 'BX.UI.System.Dialog',
	    eventName: 'onShow'
	  },
	  onAfterShow: {
	    namespace: 'BX.UI.System.Dialog',
	    eventName: 'onAfterShow'
	  },
	  onHide: {
	    namespace: 'BX.UI.System.Dialog',
	    eventName: 'onHide'
	  },
	  onAfterHide: {
	    namespace: 'BX.UI.System.Dialog',
	    eventName: 'onAfterHide'
	  }
	};

	const getClosestZIndexElement = target => {
	  let currentElement = target;
	  while (currentElement && currentElement !== document.body) {
	    const computedStyle = getComputedStyle(currentElement);
	    const position = computedStyle.position;
	    if (position === 'absolute' || position === 'fixed') {
	      const zIndex = computedStyle.zIndex;
	      if (zIndex !== 'auto') {
	        const zIndexValue = parseInt(zIndex, 10);
	        if (main_core.Type.isNumber(zIndexValue)) {
	          return zIndexValue;
	        }
	      }
	    }
	    currentElement = currentElement.parentElement;
	  }
	  return 0;
	};

	let _ = t => t,
	  _t,
	  _t2,
	  _t3,
	  _t4,
	  _t5,
	  _t6,
	  _t7,
	  _t8;
	var _title = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("title");
	var _subtitle = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("subtitle");
	var _hasCloseButton = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("hasCloseButton");
	var _content = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("content");
	var _leftButtons = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("leftButtons");
	var _rightButtons = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("rightButtons");
	var _centerButtons = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("centerButtons");
	var _hasVerticalPadding = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("hasVerticalPadding");
	var _hasHorizontalPadding = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("hasHorizontalPadding");
	var _hasOverlay = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("hasOverlay");
	var _width = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("width");
	var _disableScrolling = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("disableScrolling");
	var _closeByClickOutside = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("closeByClickOutside");
	var _closeByEsc = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("closeByEsc");
	var _dialogBackground = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("dialogBackground");
	var _stickPosition = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("stickPosition");
	var _bindElement = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("bindElement");
	var _anglePosition = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("anglePosition");
	var _popup = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("popup");
	var _createPopup = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("createPopup");
	var _getPopup = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getPopup");
	var _updatePopupContent = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("updatePopupContent");
	var _renderPopupContent = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("renderPopupContent");
	var _renderDialogHeader = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("renderDialogHeader");
	var _renderCloseButton = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("renderCloseButton");
	var _renderDialogContent = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("renderDialogContent");
	var _renderDialogFooter = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("renderDialogFooter");
	var _renderLeftButtons = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("renderLeftButtons");
	var _renderCenterButtons = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("renderCenterButtons");
	var _renderRightButtons = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("renderRightButtons");
	var _getPopupBorderRadius = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getPopupBorderRadius");
	var _getCalculatedBindElement = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getCalculatedBindElement");
	var _calculateAnglePosition = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("calculateAnglePosition");
	var _getPopupClassName = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getPopupClassName");
	var _adjustDialogPosition = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("adjustDialogPosition");
	class Dialog extends main_core.Event.EventEmitter {
	  constructor(options = {}) {
	    var _options$leftButtons, _options$centerButton, _options$rightButtons, _options$hasHorizonta, _options$hasVerticalP, _options$closeByClick, _options$closeByEsc;
	    super(options);
	    Object.defineProperty(this, _adjustDialogPosition, {
	      value: _adjustDialogPosition2
	    });
	    Object.defineProperty(this, _getPopupClassName, {
	      value: _getPopupClassName2
	    });
	    Object.defineProperty(this, _calculateAnglePosition, {
	      value: _calculateAnglePosition2
	    });
	    Object.defineProperty(this, _getCalculatedBindElement, {
	      value: _getCalculatedBindElement2
	    });
	    Object.defineProperty(this, _getPopupBorderRadius, {
	      value: _getPopupBorderRadius2
	    });
	    Object.defineProperty(this, _renderRightButtons, {
	      value: _renderRightButtons2
	    });
	    Object.defineProperty(this, _renderCenterButtons, {
	      value: _renderCenterButtons2
	    });
	    Object.defineProperty(this, _renderLeftButtons, {
	      value: _renderLeftButtons2
	    });
	    Object.defineProperty(this, _renderDialogFooter, {
	      value: _renderDialogFooter2
	    });
	    Object.defineProperty(this, _renderDialogContent, {
	      value: _renderDialogContent2
	    });
	    Object.defineProperty(this, _renderCloseButton, {
	      value: _renderCloseButton2
	    });
	    Object.defineProperty(this, _renderDialogHeader, {
	      value: _renderDialogHeader2
	    });
	    Object.defineProperty(this, _renderPopupContent, {
	      value: _renderPopupContent2
	    });
	    Object.defineProperty(this, _updatePopupContent, {
	      value: _updatePopupContent2
	    });
	    Object.defineProperty(this, _getPopup, {
	      value: _getPopup2
	    });
	    Object.defineProperty(this, _createPopup, {
	      value: _createPopup2
	    });
	    Object.defineProperty(this, _title, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _subtitle, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _hasCloseButton, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _content, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _leftButtons, {
	      writable: true,
	      value: []
	    });
	    Object.defineProperty(this, _rightButtons, {
	      writable: true,
	      value: []
	    });
	    Object.defineProperty(this, _centerButtons, {
	      writable: true,
	      value: []
	    });
	    Object.defineProperty(this, _hasVerticalPadding, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _hasHorizontalPadding, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _hasOverlay, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _width, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _disableScrolling, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _closeByClickOutside, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _closeByEsc, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _dialogBackground, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _stickPosition, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _bindElement, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _anglePosition, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _popup, {
	      writable: true,
	      value: void 0
	    });
	    this.setEventNamespace('UI.System.Dialog');
	    this.subscribeFromOptions(options.events);
	    babelHelpers.classPrivateFieldLooseBase(this, _title)[_title] = options.title;
	    babelHelpers.classPrivateFieldLooseBase(this, _subtitle)[_subtitle] = options.subtitle;
	    babelHelpers.classPrivateFieldLooseBase(this, _hasCloseButton)[_hasCloseButton] = main_core.Type.isBoolean(options.hasCloseButton) ? options.hasCloseButton : true;
	    babelHelpers.classPrivateFieldLooseBase(this, _content)[_content] = options.content;
	    babelHelpers.classPrivateFieldLooseBase(this, _leftButtons)[_leftButtons] = (_options$leftButtons = options.leftButtons) != null ? _options$leftButtons : [];
	    babelHelpers.classPrivateFieldLooseBase(this, _centerButtons)[_centerButtons] = (_options$centerButton = options.centerButtons) != null ? _options$centerButton : [];
	    babelHelpers.classPrivateFieldLooseBase(this, _rightButtons)[_rightButtons] = (_options$rightButtons = options.rightButtons) != null ? _options$rightButtons : [];
	    babelHelpers.classPrivateFieldLooseBase(this, _hasHorizontalPadding)[_hasHorizontalPadding] = (_options$hasHorizonta = options.hasHorizontalPadding) != null ? _options$hasHorizonta : true;
	    babelHelpers.classPrivateFieldLooseBase(this, _hasVerticalPadding)[_hasVerticalPadding] = (_options$hasVerticalP = options.hasVerticalPadding) != null ? _options$hasVerticalP : true;
	    babelHelpers.classPrivateFieldLooseBase(this, _hasOverlay)[_hasOverlay] = options.hasOverlay === true;
	    babelHelpers.classPrivateFieldLooseBase(this, _disableScrolling)[_disableScrolling] = options.disableScrolling;
	    babelHelpers.classPrivateFieldLooseBase(this, _closeByClickOutside)[_closeByClickOutside] = (_options$closeByClick = options.closeByClickOutside) != null ? _options$closeByClick : true;
	    babelHelpers.classPrivateFieldLooseBase(this, _closeByEsc)[_closeByEsc] = (_options$closeByEsc = options.closeByEsc) != null ? _options$closeByEsc : true;
	    babelHelpers.classPrivateFieldLooseBase(this, _width)[_width] = options.width;
	    babelHelpers.classPrivateFieldLooseBase(this, _dialogBackground)[_dialogBackground] = options.background || DialogBackground.default;

	    // this.#stickPosition = options.stickPosition;
	    // this.#anglePosition = options.anglePosition;
	    // this.#bindElement = options.bindElement;
	  }

	  static show(options) {
	    const dialog = new Dialog(options);
	    dialog.show();
	  }
	  subscribeFromOptions(events) {
	    super.subscribeFromOptions(events, aliases);
	  }
	  show() {
	    babelHelpers.classPrivateFieldLooseBase(this, _getPopup)[_getPopup]().show();
	  }
	  hide() {
	    babelHelpers.classPrivateFieldLooseBase(this, _getPopup)[_getPopup]().close();
	  }
	  setTitle(title) {
	    babelHelpers.classPrivateFieldLooseBase(this, _title)[_title] = title;
	    if (babelHelpers.classPrivateFieldLooseBase(this, _popup)[_popup]) {
	      babelHelpers.classPrivateFieldLooseBase(this, _updatePopupContent)[_updatePopupContent]();
	    }
	  }
	  setSubtitle(subtitle) {
	    babelHelpers.classPrivateFieldLooseBase(this, _subtitle)[_subtitle] = subtitle;
	    if (babelHelpers.classPrivateFieldLooseBase(this, _popup)[_popup]) {
	      babelHelpers.classPrivateFieldLooseBase(this, _updatePopupContent)[_updatePopupContent]();
	    }
	  }
	  setContent(content) {
	    babelHelpers.classPrivateFieldLooseBase(this, _content)[_content] = content;
	    if (babelHelpers.classPrivateFieldLooseBase(this, _popup)[_popup]) {
	      babelHelpers.classPrivateFieldLooseBase(this, _updatePopupContent)[_updatePopupContent]();
	    }
	  }
	  setLeftButtons(buttons) {
	    babelHelpers.classPrivateFieldLooseBase(this, _leftButtons)[_leftButtons] = buttons;
	    if (babelHelpers.classPrivateFieldLooseBase(this, _popup)[_popup]) {
	      babelHelpers.classPrivateFieldLooseBase(this, _updatePopupContent)[_updatePopupContent]();
	    }
	  }
	  setCenterButtons(buttons) {
	    babelHelpers.classPrivateFieldLooseBase(this, _centerButtons)[_centerButtons] = buttons;
	    if (babelHelpers.classPrivateFieldLooseBase(this, _popup)[_popup]) {
	      babelHelpers.classPrivateFieldLooseBase(this, _updatePopupContent)[_updatePopupContent]();
	    }
	  }
	  setRightButtons(buttons) {
	    babelHelpers.classPrivateFieldLooseBase(this, _rightButtons)[_rightButtons] = buttons;
	    if (babelHelpers.classPrivateFieldLooseBase(this, _popup)[_popup]) {
	      babelHelpers.classPrivateFieldLooseBase(this, _updatePopupContent)[_updatePopupContent]();
	    }
	  }
	}
	function _createPopup2() {
	  return new main_popup.Popup({
	    noAllPaddings: true,
	    className: babelHelpers.classPrivateFieldLooseBase(this, _getPopupClassName)[_getPopupClassName](),
	    closeIcon: false,
	    content: babelHelpers.classPrivateFieldLooseBase(this, _renderPopupContent)[_renderPopupContent](),
	    events: {
	      onShow: () => {
	        babelHelpers.classPrivateFieldLooseBase(this, _adjustDialogPosition)[_adjustDialogPosition]();
	        if (babelHelpers.classPrivateFieldLooseBase(this, _disableScrolling)[_disableScrolling]) {
	          main_core.Dom.addClass(document.body, 'ui-system-dialog__disable-scrolling');
	        }
	        this.emit('onShow');
	      },
	      onAfterShow: () => {
	        babelHelpers.classPrivateFieldLooseBase(this, _adjustDialogPosition)[_adjustDialogPosition]();
	        this.emit('onAfterShow');
	      },
	      onClose: () => {
	        babelHelpers.classPrivateFieldLooseBase(this, _adjustDialogPosition)[_adjustDialogPosition]();
	        if (babelHelpers.classPrivateFieldLooseBase(this, _disableScrolling)[_disableScrolling]) {
	          main_core.Dom.removeClass(document.body, 'ui-system-dialog__disable-scrolling');
	        }
	        this.emit('onHide');
	      },
	      onAfterClose: () => {
	        babelHelpers.classPrivateFieldLooseBase(this, _adjustDialogPosition)[_adjustDialogPosition]();
	        this.emit('onAfterHide');
	      }
	    },
	    borderRadius: babelHelpers.classPrivateFieldLooseBase(this, _getPopupBorderRadius)[_getPopupBorderRadius](),
	    minWidth: babelHelpers.classPrivateFieldLooseBase(this, _width)[_width],
	    maxWidth: babelHelpers.classPrivateFieldLooseBase(this, _width)[_width],
	    overlay: babelHelpers.classPrivateFieldLooseBase(this, _hasOverlay)[_hasOverlay] ? {
	      backgroundColor: 'rgba(0, 32, 78, 0.46)',
	      opacity: 100
	    } : undefined,
	    autoHideHandler: event => {
	      if (event.target.closest('.ui-system-dialog')) {
	        event.preventDefault();
	        return false;
	      }
	      const zIndex = getClosestZIndexElement(event.target);
	      if (zIndex > babelHelpers.classPrivateFieldLooseBase(this, _getPopup)[_getPopup]().getZindex()) {
	        event.preventDefault();
	        return false;
	      }
	      return true;
	    },
	    autoHide: babelHelpers.classPrivateFieldLooseBase(this, _closeByClickOutside)[_closeByClickOutside],
	    closeByEsc: babelHelpers.classPrivateFieldLooseBase(this, _closeByEsc)[_closeByEsc],
	    cacheable: false,
	    anglePosition: babelHelpers.classPrivateFieldLooseBase(this, _anglePosition)[_anglePosition]
	    // bindElement: this.#getCalculatedBindElement(),
	  });
	}
	function _getPopup2() {
	  if (!babelHelpers.classPrivateFieldLooseBase(this, _popup)[_popup]) {
	    babelHelpers.classPrivateFieldLooseBase(this, _popup)[_popup] = babelHelpers.classPrivateFieldLooseBase(this, _createPopup)[_createPopup]();
	  }
	  return babelHelpers.classPrivateFieldLooseBase(this, _popup)[_popup];
	}
	function _updatePopupContent2() {
	  var _babelHelpers$classPr;
	  (_babelHelpers$classPr = babelHelpers.classPrivateFieldLooseBase(this, _popup)[_popup]) == null ? void 0 : _babelHelpers$classPr.setContent(babelHelpers.classPrivateFieldLooseBase(this, _renderPopupContent)[_renderPopupContent]());
	}
	function _renderPopupContent2() {
	  return main_core.Tag.render(_t || (_t = _`
			<div class="ui-system-dialog__content-wrapper">
				${0}
				${0}
				${0}
			</div>
		`), babelHelpers.classPrivateFieldLooseBase(this, _renderDialogHeader)[_renderDialogHeader](), babelHelpers.classPrivateFieldLooseBase(this, _renderDialogContent)[_renderDialogContent](), babelHelpers.classPrivateFieldLooseBase(this, _renderDialogFooter)[_renderDialogFooter]());
	}
	function _renderDialogHeader2() {
	  return main_core.Tag.render(_t2 || (_t2 = _`
			<header class="ui-system-dialog__header">
				<div class="ui-system-dialog__header-left">
					${0}
					${0}
				</div>
				${0}
			</header>
		`), babelHelpers.classPrivateFieldLooseBase(this, _title)[_title] ? ui_system_typography.Headline.render(babelHelpers.classPrivateFieldLooseBase(this, _title)[_title], {
	    size: 'md'
	  }) : '', babelHelpers.classPrivateFieldLooseBase(this, _subtitle)[_subtitle] ? ui_system_typography.Text.render(babelHelpers.classPrivateFieldLooseBase(this, _subtitle)[_subtitle], {
	    size: 'xs',
	    className: 'ui-system-dialog__subtitle'
	  }) : '', babelHelpers.classPrivateFieldLooseBase(this, _hasCloseButton)[_hasCloseButton] ? babelHelpers.classPrivateFieldLooseBase(this, _renderCloseButton)[_renderCloseButton]() : '');
	}
	function _renderCloseButton2() {
	  const icon = new ui_iconSet_api_core.Icon({
	    icon: ui_iconSet_api_core.Outline.CROSS_L,
	    size: 24,
	    hoverMode: ui_iconSet_api_core.IconHoverMode.DEFAULT
	  });
	  const btn = main_core.Tag.render(_t3 || (_t3 = _`<button class="ui-system-dialog__header-close-btn">${0}</button>`), icon.render());
	  main_core.bind(btn, 'click', () => {
	    babelHelpers.classPrivateFieldLooseBase(this, _popup)[_popup].close();
	  });
	  return btn;
	}
	function _renderDialogContent2() {
	  const classes = ['ui-system-dialog__content'];
	  if (babelHelpers.classPrivateFieldLooseBase(this, _hasHorizontalPadding)[_hasHorizontalPadding] === false) {
	    classes.push('--rm-horizontal');
	  }
	  if (babelHelpers.classPrivateFieldLooseBase(this, _hasVerticalPadding)[_hasVerticalPadding] === false) {
	    classes.push('--rm-vertical');
	  }
	  return main_core.Tag.render(_t4 || (_t4 = _`<div class="${0}">${0}</div>`), classes.join(' '), babelHelpers.classPrivateFieldLooseBase(this, _content)[_content]);
	}
	function _renderDialogFooter2() {
	  return main_core.Tag.render(_t5 || (_t5 = _`
			<footer class="ui-system-dialog__footer">
				${0}
				${0}
				${0}
			</footer>
		`), babelHelpers.classPrivateFieldLooseBase(this, _renderLeftButtons)[_renderLeftButtons](babelHelpers.classPrivateFieldLooseBase(this, _leftButtons)[_leftButtons]), babelHelpers.classPrivateFieldLooseBase(this, _renderCenterButtons)[_renderCenterButtons](babelHelpers.classPrivateFieldLooseBase(this, _centerButtons)[_centerButtons]), babelHelpers.classPrivateFieldLooseBase(this, _renderRightButtons)[_renderRightButtons](babelHelpers.classPrivateFieldLooseBase(this, _rightButtons)[_rightButtons]));
	}
	function _renderLeftButtons2(buttons) {
	  const container = main_core.Tag.render(_t6 || (_t6 = _`<div class="ui-system-dialog__left-buttons"></div>`));
	  buttons.forEach(button => {
	    main_core.Dom.append(button.render(), container);
	  });
	  return container;
	}
	function _renderCenterButtons2(buttons) {
	  const container = main_core.Tag.render(_t7 || (_t7 = _`<div class="ui-system-dialog__center-buttons"></div>`));
	  buttons.forEach(button => {
	    main_core.Dom.append(button.render(), container);
	  });
	  return container;
	}
	function _renderRightButtons2(buttons) {
	  const container = main_core.Tag.render(_t8 || (_t8 = _`<div class="ui-system-dialog__right-buttons"></div>`));
	  buttons.forEach(button => {
	    main_core.Dom.append(button.render(), container);
	  });
	  return container;
	}
	function _getPopupBorderRadius2() {
	  if (babelHelpers.classPrivateFieldLooseBase(this, _stickPosition)[_stickPosition] === 'top') {
	    return '0 0 18px 18px';
	  }
	  if (babelHelpers.classPrivateFieldLooseBase(this, _stickPosition)[_stickPosition] === 'right') {
	    return '18ox 0 0 18px';
	  }
	  if (babelHelpers.classPrivateFieldLooseBase(this, _stickPosition)[_stickPosition] === 'bottom') {
	    return '18px 18px 0 0';
	  }
	  if (babelHelpers.classPrivateFieldLooseBase(this, _stickPosition)[_stickPosition] === 'left') {
	    return '18px 0 0 18px';
	  }
	  return '18px 18px 18px 18px';
	}
	function _getCalculatedBindElement2() {
	  if (!babelHelpers.classPrivateFieldLooseBase(this, _bindElement)[_bindElement]) {
	    return null;
	  }
	  if (main_core.Type.isPlainObject(babelHelpers.classPrivateFieldLooseBase(this, _bindElement)[_bindElement]) && 'top' in babelHelpers.classPrivateFieldLooseBase(this, _bindElement)[_bindElement] && 'left' in babelHelpers.classPrivateFieldLooseBase(this, _bindElement)[_bindElement]) {
	    return babelHelpers.classPrivateFieldLooseBase(this, _bindElement)[_bindElement];
	  }
	  if (main_core.Type.isDomNode(babelHelpers.classPrivateFieldLooseBase(this, _bindElement)[_bindElement]) && babelHelpers.classPrivateFieldLooseBase(this, _anglePosition)[_anglePosition]) {
	    var _babelHelpers$classPr2;
	    const {
	      width = 0,
	      height = 0
	    } = main_core.Dom.getPosition((_babelHelpers$classPr2 = babelHelpers.classPrivateFieldLooseBase(this, _popup)[_popup]) == null ? void 0 : _babelHelpers$classPr2.getPopupContainer());
	    return babelHelpers.classPrivateFieldLooseBase(this, _calculateAnglePosition)[_calculateAnglePosition](babelHelpers.classPrivateFieldLooseBase(this, _bindElement)[_bindElement], babelHelpers.classPrivateFieldLooseBase(this, _anglePosition)[_anglePosition], width, height);
	  }
	  return babelHelpers.classPrivateFieldLooseBase(this, _bindElement)[_bindElement];
	}
	function _calculateAnglePosition2(element, anglePosition, popupWidth, popupHeight) {
	  const elementPosition = main_core.Dom.getPosition(element);
	  const {
	    left,
	    top,
	    width,
	    height
	  } = elementPosition;
	  const angleWidth = 16;
	  const angleHeight = 8;
	  const angleOffset = 30;
	  switch (anglePosition) {
	    case DialogAnglePositions.topLeft:
	      return {
	        top: top + height + popupHeight + angleHeight,
	        left: left + (width - angleWidth) / 2 - angleOffset
	      };
	    case DialogAnglePositions.topCenter:
	      return {
	        top: top + height + popupHeight + angleHeight * 2,
	        left: left - width / 2 - angleOffset - angleWidth / 2
	      };
	    case DialogAnglePositions.topRight:
	      return {
	        top: top + height + popupHeight + angleHeight * 2,
	        left: left - popupWidth + width / 2 + angleOffset + angleWidth / 2
	      };
	    case DialogAnglePositions.rightTop:
	      return {
	        top: top + height / 2 - angleOffset - angleWidth / 2,
	        left: left - popupWidth - angleHeight
	      };
	    case DialogAnglePositions.rightCenter:
	      return {
	        top: top + height / 2 - popupHeight / 2,
	        left: left - popupWidth - angleHeight
	      };
	    case DialogAnglePositions.rightBottom:
	      return {
	        top: top + height / 2 - popupHeight + angleOffset + angleWidth / 2,
	        left: left - popupWidth - angleHeight
	      };
	    case DialogAnglePositions.bottomLeft:
	      return {
	        top: top - popupHeight - angleHeight,
	        left: left + width / 2 - angleOffset - angleWidth / 2
	      };
	    case DialogAnglePositions.bottomCenter:
	      return {
	        top: top - popupHeight - angleHeight,
	        left: left - width / 2 - angleOffset - angleWidth / 2
	      };
	    case DialogAnglePositions.bottomRight:
	      return {
	        top: top - popupHeight - angleHeight,
	        left: left - popupWidth + width / 2 + angleOffset + angleWidth / 2
	      };
	    case DialogAnglePositions.leftTop:
	      return {
	        top: top + height / 2 - angleOffset - angleWidth / 2,
	        left: left + width + angleHeight
	      };
	    case DialogAnglePositions.leftCenter:
	      return {
	        top: top + height / 2 - popupHeight / 2,
	        left: left + width + angleHeight
	      };
	    case DialogAnglePositions.leftBottom:
	      return {
	        top: top + height / 2 - popupHeight + angleOffset + angleWidth / 2,
	        left: left + width + angleHeight
	      };
	    default:
	      return {
	        top: top + height / 2,
	        left: left + width / 2
	      };
	  }
	}
	function _getPopupClassName2() {
	  const classes = ['ui-system-dialog'];
	  if (babelHelpers.classPrivateFieldLooseBase(this, _anglePosition)[_anglePosition]) {
	    classes.push('popup-window-with-angle');
	    const angleClass = babelHelpers.classPrivateFieldLooseBase(this, _anglePosition)[_anglePosition].replaceAll(/([A-Z])/g, '-$1').toLowerCase();
	    classes.push(`popup-window-angle-${angleClass}`);
	  }
	  classes.push(`--bg-${babelHelpers.classPrivateFieldLooseBase(this, _dialogBackground)[_dialogBackground]}`);
	  return classes.join(' ');
	}
	function _adjustDialogPosition2() {
	  if (babelHelpers.classPrivateFieldLooseBase(this, _popup)[_popup]) {
	    babelHelpers.classPrivateFieldLooseBase(this, _popup)[_popup].setBindElement(babelHelpers.classPrivateFieldLooseBase(this, _getCalculatedBindElement)[_getCalculatedBindElement]());
	    babelHelpers.classPrivateFieldLooseBase(this, _popup)[_popup].adjustPosition();
	  }
	}

	exports.Dialog = Dialog;
	exports.DialogBackground = DialogBackground;

}((this.BX.UI.System = this.BX.UI.System || {}),BX.Main,BX.UI.System.Typography,BX.UI.IconSet,BX,BX));
//# sourceMappingURL=dialog.bundle.js.map
