/* eslint-disable */
this.BX = this.BX || {};
(function (exports,main_core) {
	'use strict';

	var _bindEvents = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("bindEvents");
	var _dragStart = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("dragStart");
	var _dragEnd = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("dragEnd");
	var _dragMove = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("dragMove");
	class TouchDragListener {
	  constructor({
	    element,
	    touchStartCallback,
	    touchEndCallback,
	    touchMoveCallback
	  }) {
	    Object.defineProperty(this, _dragMove, {
	      value: _dragMove2
	    });
	    Object.defineProperty(this, _dragEnd, {
	      value: _dragEnd2
	    });
	    Object.defineProperty(this, _dragStart, {
	      value: _dragStart2
	    });
	    Object.defineProperty(this, _bindEvents, {
	      value: _bindEvents2
	    });
	    this.element = main_core.Type.isDomNode(element) ? element : null;
	    this.touchStartCallback = touchStartCallback;
	    this.touchEndCallback = touchEndCallback;
	    this.touchMoveCallback = touchMoveCallback;
	    this.active = false;
	    this.currentY = null;
	    this.initialY = null;
	    this.yOffset = 0;
	    babelHelpers.classPrivateFieldLooseBase(this, _bindEvents)[_bindEvents]();
	  }
	}
	function _bindEvents2() {
	  if (this.element) {
	    this.element.addEventListener('touchstart', babelHelpers.classPrivateFieldLooseBase(this, _dragStart)[_dragStart].bind(this));
	    this.element.addEventListener('touchend', babelHelpers.classPrivateFieldLooseBase(this, _dragEnd)[_dragEnd].bind(this));
	    this.element.addEventListener('touchmove', babelHelpers.classPrivateFieldLooseBase(this, _dragMove)[_dragMove].bind(this));
	  }
	}
	function _dragStart2(ev) {
	  this.active = true;
	  this.element.classList.add('--ondrag');
	  if (ev.type === 'touchstart') {
	    this.initialY = ev.touches[0].clientY - this.yOffset;
	  } else {
	    this.initialY = ev.clientY - this.yOffset;
	  }
	  if (!this.touchStartCallback) {
	    return;
	  }
	  this.touchStartCallback({
	    element: this.element,
	    active: this.active,
	    currentY: this.currentY,
	    initialY: this.initialY,
	    yOffset: this.offSetY
	  });
	}
	function _dragEnd2(ev) {
	  this.active = true;
	  this.element.classList.remove('--ondrag');
	  this.yOffset = 0;
	  this.initialY = this.currentY;
	  if (!this.touchEndCallback) return;
	  this.touchEndCallback({
	    element: this.element,
	    active: this.active,
	    currentY: this.currentY,
	    initialY: this.initialY,
	    yOffset: this.offSetY
	  });
	}
	function _dragMove2(ev) {
	  if (!this.active) {
	    return;
	  }
	  ev.preventDefault();
	  if (ev.type === 'touchmove') {
	    this.currentY = ev.touches[0].clientY - this.initialY;
	  } else {
	    this.currentY = ev.clientY - this.initialY;
	  }
	  this.yOffset = this.currentX;
	  if (!this.touchMoveCallback) {
	    return;
	  }
	  this.touchMoveCallback({
	    element: this.element,
	    active: this.active,
	    currentY: this.currentY,
	    initialY: this.initialY,
	    yOffset: this.offSetY
	  });
	}

	let _ = t => t,
	  _t,
	  _t2,
	  _t3,
	  _t4,
	  _t5,
	  _t6,
	  _t7,
	  _t8;
	var _getOverlay = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getOverlay");
	var _getHelp = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getHelp");
	var _getClose = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getClose");
	var _getPanel = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getPanel");
	var _getContent = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getContent");
	var _getWrapper = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getWrapper");
	class BottomSheet {
	  constructor({
	    content,
	    help,
	    className,
	    padding: _padding
	  }) {
	    Object.defineProperty(this, _getWrapper, {
	      value: _getWrapper2
	    });
	    Object.defineProperty(this, _getContent, {
	      value: _getContent2
	    });
	    Object.defineProperty(this, _getPanel, {
	      value: _getPanel2
	    });
	    Object.defineProperty(this, _getClose, {
	      value: _getClose2
	    });
	    Object.defineProperty(this, _getHelp, {
	      value: _getHelp2
	    });
	    Object.defineProperty(this, _getOverlay, {
	      value: _getOverlay2
	    });
	    this.content = main_core.Type.isDomNode(content) ? content : null;
	    this.className = main_core.Type.isString(className) ? className : '';
	    this.padding = main_core.Type.isString(_padding) || main_core.Type.isNumber(_padding) ? _padding : null;
	    this.help = null;
	    switch (true) {
	      case main_core.Type.isString(help):
	        this.help = help;
	        break;
	      case main_core.Type.isFunction(help):
	        this.help = help;
	        break;
	    }
	    this.layout = {
	      wrapper: null,
	      container: null,
	      content: null,
	      overlay: null,
	      close: null,
	      help: null
	    };
	    this.halfOfHeight = 0;
	    this.currentHeight = null;
	    this.sheetListener = new TouchDragListener({
	      element: babelHelpers.classPrivateFieldLooseBase(this, _getPanel)[_getPanel](),
	      touchStartCallback: ({
	        element,
	        active,
	        initialY,
	        currentY,
	        yOffset
	      }) => {
	        element.style.setProperty('--translateY', 'translateY(0)');
	        element.style.setProperty('transition', 'unset');
	      },
	      touchEndCallback: ({
	        element,
	        active,
	        initialY,
	        currentY,
	        yOffset
	      }) => {
	        element.style.setProperty('transition', 'transform .3s');
	        element.style.setProperty('--translateY', 'translateY(' + currentY + 'px)');
	        if (parseInt(currentY) > this.halfOfHeight) {
	          this.close();
	        }
	      },
	      touchMoveCallback: ({
	        element,
	        active,
	        initialY,
	        currentY,
	        yOffset
	      }) => {
	        if (currentY <= 0) {
	          return;
	        }
	        if (currentY <= -40) {
	          currentY = -41 + currentY / 10;
	        }
	        element.style.setProperty('--translateY', 'translateY(' + currentY + 'px)');
	      }
	    });
	    if (this.content) {
	      this.setContent(this.content);
	    }
	  }
	  setContent(content) {
	    if (main_core.Type.isDomNode(content)) {
	      main_core.Dom.clean(babelHelpers.classPrivateFieldLooseBase(this, _getContent)[_getContent]());
	      babelHelpers.classPrivateFieldLooseBase(this, _getContent)[_getContent]().appendChild(content);
	    }
	    if (main_core.Type.isString(content)) {
	      main_core.Dom.clean(babelHelpers.classPrivateFieldLooseBase(this, _getContent)[_getContent]());
	      babelHelpers.classPrivateFieldLooseBase(this, _getContent)[_getContent]().innerText = content;
	    }
	  }
	  adjustPosition() {}
	  adjustSize() {
	    if (this.currentHeight !== babelHelpers.classPrivateFieldLooseBase(this, _getPanel)[_getPanel]().offsetHeight) {
	      let currentHeight = this.currentHeight;
	      let newHeight = babelHelpers.classPrivateFieldLooseBase(this, _getPanel)[_getPanel]().offsetHeight;
	      babelHelpers.classPrivateFieldLooseBase(this, _getPanel)[_getPanel]().style.setProperty('height', currentHeight + 'px');
	      setTimeout(() => {
	        currentHeight = babelHelpers.classPrivateFieldLooseBase(this, _getPanel)[_getPanel]().offsetHeight;
	        babelHelpers.classPrivateFieldLooseBase(this, _getPanel)[_getPanel]().style.setProperty('height', newHeight + 'px');
	        const adjustHeight = () => {
	          babelHelpers.classPrivateFieldLooseBase(this, _getPanel)[_getPanel]().style.removeProperty('height', newHeight + 'px');
	          babelHelpers.classPrivateFieldLooseBase(this, _getPanel)[_getPanel]().removeEventListener('transitionend', adjustHeight);
	        };
	        babelHelpers.classPrivateFieldLooseBase(this, _getPanel)[_getPanel]().addEventListener('transitionend', adjustHeight);
	        this.currentHeight = newHeight;
	        this.halfOfHeight = this.currentHeight / 2;
	      });
	    }
	  }
	  close() {
	    if (babelHelpers.classPrivateFieldLooseBase(this, _getWrapper)[_getWrapper]().parentNode) {
	      babelHelpers.classPrivateFieldLooseBase(this, _getPanel)[_getPanel]().classList.remove('--show');
	      babelHelpers.classPrivateFieldLooseBase(this, _getOverlay)[_getOverlay]().classList.remove('--show');
	      const animationProgress = () => {
	        babelHelpers.classPrivateFieldLooseBase(this, _getWrapper)[_getWrapper]().classList.remove('--show');
	        babelHelpers.classPrivateFieldLooseBase(this, _getPanel)[_getPanel]().removeEventListener('transitionend', animationProgress);
	      };
	      babelHelpers.classPrivateFieldLooseBase(this, _getPanel)[_getPanel]().addEventListener('transitionend', animationProgress);
	    }
	  }
	  show() {
	    if (!babelHelpers.classPrivateFieldLooseBase(this, _getWrapper)[_getWrapper]().parentNode) {
	      babelHelpers.classPrivateFieldLooseBase(this, _getWrapper)[_getWrapper]().appendChild(babelHelpers.classPrivateFieldLooseBase(this, _getOverlay)[_getOverlay]());
	      babelHelpers.classPrivateFieldLooseBase(this, _getWrapper)[_getWrapper]().appendChild(babelHelpers.classPrivateFieldLooseBase(this, _getPanel)[_getPanel]());
	      document.body.appendChild(babelHelpers.classPrivateFieldLooseBase(this, _getWrapper)[_getWrapper]());
	    }
	    babelHelpers.classPrivateFieldLooseBase(this, _getWrapper)[_getWrapper]().classList.add('--show');
	    setTimeout(() => {
	      this.currentHeight = babelHelpers.classPrivateFieldLooseBase(this, _getPanel)[_getPanel]().offsetHeight;
	      this.halfOfHeight = this.currentHeight / 2;
	      babelHelpers.classPrivateFieldLooseBase(this, _getPanel)[_getPanel]().classList.add('--show');
	      babelHelpers.classPrivateFieldLooseBase(this, _getOverlay)[_getOverlay]().classList.add('--show');
	    });
	  }
	}
	function _getOverlay2() {
	  if (!this.layout.overlay) {
	    this.layout.overlay = main_core.Tag.render(_t || (_t = _`
				<div class="ui-bottomsheet__overlay"></div>
			`));
	    this.layout.overlay.addEventListener('click', this.close.bind(this));
	  }
	  return this.layout.overlay;
	}
	function _getHelp2() {
	  if (!this.layout.help) {
	    if (main_core.Type.isString(this.help)) {
	      this.layout.help = main_core.Tag.render(_t2 || (_t2 = _`
					<a href="${0}" class="ui-bottomsheet__panel-control--item --cursor-pointer">
						<span class="ui-bottomsheet__panel-control--item-text">${0}</span>
					</a>
				`), this.help, main_core.Loc.getMessage('UI_BOTTOMSHEET_HELP'));
	    }
	    if (main_core.Type.isFunction(this.help)) {
	      this.layout.help = main_core.Tag.render(_t3 || (_t3 = _`
					<div class="ui-bottomsheet__panel-control--item --cursor-pointer">
						<div class="ui-bottomsheet__panel-control--item-text">${0}</div>
					</div>
				`), main_core.Loc.getMessage('UI_BOTTOMSHEET_HELP'));
	      this.layout.help.addEventListener('click', () => {
	        this.help();
	      });
	    }
	  }
	  return this.layout.help;
	}
	function _getClose2() {
	  if (!this.layout.close) {
	    this.layout.close = main_core.Tag.render(_t4 || (_t4 = _`
				<div class="ui-bottomsheet__panel-control--item --cursor-pointer --close">
					<div class="ui-bottomsheet__panel-control--item-text">${0}</div>
				</div>
			`), main_core.Loc.getMessage('UI_BOTTOMSHEET_CLOSE'));
	    this.layout.close.addEventListener('click', this.close.bind(this));
	  }
	  return this.layout.close;
	}
	function _getPanel2() {
	  if (!this.layout.container) {
	    const panelWrapper = main_core.Tag.render(_t5 || (_t5 = _`
				<div class="ui-bottomsheet__panel-wrapper">
					${0}
				</div>
			`), babelHelpers.classPrivateFieldLooseBase(this, _getContent)[_getContent]());
	    if (this.padding || this.padding === 0) {
	      let padding;
	      switch (true) {
	        case main_core.Type.isString(this.padding):
	          padding = this.padding;
	          break;
	        case main_core.Type.isNumber(this.padding):
	          padding = this.padding + 'px';
	          break;
	      }
	      panelWrapper.style.setProperty('padding', padding);
	    }
	    this.layout.container = main_core.Tag.render(_t6 || (_t6 = _`
				<div class="ui-bottomsheet__panel">
					<div class="ui-bottomsheet__panel-control">
						${0}
						<div class="ui-bottomsheet__panel-handler"></div>
						${0}
					</div>
					${0}
				</div>
			`), this.help ? babelHelpers.classPrivateFieldLooseBase(this, _getHelp)[_getHelp]() : '', babelHelpers.classPrivateFieldLooseBase(this, _getClose)[_getClose](), panelWrapper);
	  }
	  return this.layout.container;
	}
	function _getContent2() {
	  if (!this.layout.content) {
	    this.layout.content = main_core.Tag.render(_t7 || (_t7 = _`
				<div class="ui-bottomsheet__panel-content"></div>
			`));
	  }
	  return this.layout.content;
	}
	function _getWrapper2() {
	  if (!this.layout.wrapper) {
	    this.layout.wrapper = main_core.Tag.render(_t8 || (_t8 = _`
				<div class="ui-bottomsheet ${0}"></div>
			`), this.className);
	  }
	  return this.layout.wrapper;
	}

	exports.BottomSheet = BottomSheet;

}((this.BX.UI = this.BX.UI || {}),BX));
//# sourceMappingURL=bottomsheet.bundle.js.map
