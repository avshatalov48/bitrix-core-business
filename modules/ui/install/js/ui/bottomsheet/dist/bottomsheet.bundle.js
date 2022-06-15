this.BX = this.BX || {};
(function (exports,main_core) {
	'use strict';

	function _classPrivateMethodInitSpec(obj, privateSet) { _checkPrivateRedeclaration(obj, privateSet); privateSet.add(obj); }

	function _checkPrivateRedeclaration(obj, privateCollection) { if (privateCollection.has(obj)) { throw new TypeError("Cannot initialize the same private elements twice on an object"); } }

	function _classPrivateMethodGet(receiver, privateSet, fn) { if (!privateSet.has(receiver)) { throw new TypeError("attempted to get private field on non-instance"); } return fn; }

	var _bindEvents = /*#__PURE__*/new WeakSet();

	var _dragStart = /*#__PURE__*/new WeakSet();

	var _dragEnd = /*#__PURE__*/new WeakSet();

	var _dragMove = /*#__PURE__*/new WeakSet();

	var TouchDragListener = function TouchDragListener(_ref) {
	  var element = _ref.element,
	      touchStartCallback = _ref.touchStartCallback,
	      touchEndCallback = _ref.touchEndCallback,
	      touchMoveCallback = _ref.touchMoveCallback;
	  babelHelpers.classCallCheck(this, TouchDragListener);

	  _classPrivateMethodInitSpec(this, _dragMove);

	  _classPrivateMethodInitSpec(this, _dragEnd);

	  _classPrivateMethodInitSpec(this, _dragStart);

	  _classPrivateMethodInitSpec(this, _bindEvents);

	  this.element = main_core.Type.isDomNode(element) ? element : null;
	  this.touchStartCallback = touchStartCallback;
	  this.touchEndCallback = touchEndCallback;
	  this.touchMoveCallback = touchMoveCallback;
	  this.active = false;
	  this.currentY = null;
	  this.initialY = null;
	  this.yOffset = 0;

	  _classPrivateMethodGet(this, _bindEvents, _bindEvents2).call(this);
	};

	function _bindEvents2() {
	  if (this.element) {
	    this.element.addEventListener('touchstart', _classPrivateMethodGet(this, _dragStart, _dragStart2).bind(this));
	    this.element.addEventListener('touchend', _classPrivateMethodGet(this, _dragEnd, _dragEnd2).bind(this));
	    this.element.addEventListener('touchmove', _classPrivateMethodGet(this, _dragMove, _dragMove2).bind(this));
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

	var _templateObject, _templateObject2, _templateObject3, _templateObject4, _templateObject5, _templateObject6, _templateObject7, _templateObject8;

	function _classPrivateMethodInitSpec$1(obj, privateSet) { _checkPrivateRedeclaration$1(obj, privateSet); privateSet.add(obj); }

	function _checkPrivateRedeclaration$1(obj, privateCollection) { if (privateCollection.has(obj)) { throw new TypeError("Cannot initialize the same private elements twice on an object"); } }

	function _classPrivateMethodGet$1(receiver, privateSet, fn) { if (!privateSet.has(receiver)) { throw new TypeError("attempted to get private field on non-instance"); } return fn; }

	var _getOverlay = /*#__PURE__*/new WeakSet();

	var _getHelp = /*#__PURE__*/new WeakSet();

	var _getClose = /*#__PURE__*/new WeakSet();

	var _getPanel = /*#__PURE__*/new WeakSet();

	var _getContent = /*#__PURE__*/new WeakSet();

	var _getWrapper = /*#__PURE__*/new WeakSet();

	var BottomSheet = /*#__PURE__*/function () {
	  function BottomSheet(_ref) {
	    var _this = this;

	    var content = _ref.content,
	        help = _ref.help,
	        className = _ref.className,
	        _padding = _ref.padding;
	    babelHelpers.classCallCheck(this, BottomSheet);

	    _classPrivateMethodInitSpec$1(this, _getWrapper);

	    _classPrivateMethodInitSpec$1(this, _getContent);

	    _classPrivateMethodInitSpec$1(this, _getPanel);

	    _classPrivateMethodInitSpec$1(this, _getClose);

	    _classPrivateMethodInitSpec$1(this, _getHelp);

	    _classPrivateMethodInitSpec$1(this, _getOverlay);

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
	      element: _classPrivateMethodGet$1(this, _getPanel, _getPanel2).call(this),
	      touchStartCallback: function touchStartCallback(_ref2) {
	        var element = _ref2.element,
	            active = _ref2.active,
	            initialY = _ref2.initialY,
	            currentY = _ref2.currentY,
	            yOffset = _ref2.yOffset;
	        element.style.setProperty('--translateY', 'translateY(0)');
	        element.style.setProperty('transition', 'unset');
	      },
	      touchEndCallback: function touchEndCallback(_ref3) {
	        var element = _ref3.element,
	            active = _ref3.active,
	            initialY = _ref3.initialY,
	            currentY = _ref3.currentY,
	            yOffset = _ref3.yOffset;
	        element.style.setProperty('transition', 'transform .3s');
	        element.style.setProperty('--translateY', 'translateY(' + currentY + 'px)');

	        if (parseInt(currentY) > _this.halfOfHeight) {
	          _this.close();
	        }
	      },
	      touchMoveCallback: function touchMoveCallback(_ref4) {
	        var element = _ref4.element,
	            active = _ref4.active,
	            initialY = _ref4.initialY,
	            currentY = _ref4.currentY,
	            yOffset = _ref4.yOffset;

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

	  babelHelpers.createClass(BottomSheet, [{
	    key: "setContent",
	    value: function setContent(content) {
	      if (main_core.Type.isDomNode(content)) {
	        main_core.Dom.clean(_classPrivateMethodGet$1(this, _getContent, _getContent2).call(this));

	        _classPrivateMethodGet$1(this, _getContent, _getContent2).call(this).appendChild(content);
	      }

	      if (main_core.Type.isString(content)) {
	        main_core.Dom.clean(_classPrivateMethodGet$1(this, _getContent, _getContent2).call(this));
	        _classPrivateMethodGet$1(this, _getContent, _getContent2).call(this).innerText = content;
	      }
	    }
	  }, {
	    key: "adjustPosition",
	    value: function adjustPosition() {}
	  }, {
	    key: "adjustSize",
	    value: function adjustSize() {
	      var _this2 = this;

	      if (this.currentHeight !== _classPrivateMethodGet$1(this, _getPanel, _getPanel2).call(this).offsetHeight) {
	        var currentHeight = this.currentHeight;

	        var newHeight = _classPrivateMethodGet$1(this, _getPanel, _getPanel2).call(this).offsetHeight;

	        _classPrivateMethodGet$1(this, _getPanel, _getPanel2).call(this).style.setProperty('height', currentHeight + 'px');

	        setTimeout(function () {
	          currentHeight = _classPrivateMethodGet$1(_this2, _getPanel, _getPanel2).call(_this2).offsetHeight;

	          _classPrivateMethodGet$1(_this2, _getPanel, _getPanel2).call(_this2).style.setProperty('height', newHeight + 'px');

	          var adjustHeight = function adjustHeight() {
	            _classPrivateMethodGet$1(_this2, _getPanel, _getPanel2).call(_this2).style.removeProperty('height', newHeight + 'px');

	            _classPrivateMethodGet$1(_this2, _getPanel, _getPanel2).call(_this2).removeEventListener('transitionend', adjustHeight);
	          };

	          _classPrivateMethodGet$1(_this2, _getPanel, _getPanel2).call(_this2).addEventListener('transitionend', adjustHeight);

	          _this2.currentHeight = newHeight;
	          _this2.halfOfHeight = _this2.currentHeight / 2;
	        });
	      }
	    }
	  }, {
	    key: "close",
	    value: function close() {
	      var _this3 = this;

	      if (_classPrivateMethodGet$1(this, _getWrapper, _getWrapper2).call(this).parentNode) {
	        _classPrivateMethodGet$1(this, _getPanel, _getPanel2).call(this).classList.remove('--show');

	        _classPrivateMethodGet$1(this, _getOverlay, _getOverlay2).call(this).classList.remove('--show');

	        var animationProgress = function animationProgress() {
	          _classPrivateMethodGet$1(_this3, _getWrapper, _getWrapper2).call(_this3).classList.remove('--show');

	          _classPrivateMethodGet$1(_this3, _getPanel, _getPanel2).call(_this3).removeEventListener('transitionend', animationProgress);
	        };

	        _classPrivateMethodGet$1(this, _getPanel, _getPanel2).call(this).addEventListener('transitionend', animationProgress);
	      }
	    }
	  }, {
	    key: "show",
	    value: function show() {
	      var _this4 = this;

	      if (!_classPrivateMethodGet$1(this, _getWrapper, _getWrapper2).call(this).parentNode) {
	        _classPrivateMethodGet$1(this, _getWrapper, _getWrapper2).call(this).appendChild(_classPrivateMethodGet$1(this, _getOverlay, _getOverlay2).call(this));

	        _classPrivateMethodGet$1(this, _getWrapper, _getWrapper2).call(this).appendChild(_classPrivateMethodGet$1(this, _getPanel, _getPanel2).call(this));

	        document.body.appendChild(_classPrivateMethodGet$1(this, _getWrapper, _getWrapper2).call(this));
	      }

	      _classPrivateMethodGet$1(this, _getWrapper, _getWrapper2).call(this).classList.add('--show');

	      setTimeout(function () {
	        _this4.currentHeight = _classPrivateMethodGet$1(_this4, _getPanel, _getPanel2).call(_this4).offsetHeight;
	        _this4.halfOfHeight = _this4.currentHeight / 2;

	        _classPrivateMethodGet$1(_this4, _getPanel, _getPanel2).call(_this4).classList.add('--show');

	        _classPrivateMethodGet$1(_this4, _getOverlay, _getOverlay2).call(_this4).classList.add('--show');
	      });
	    }
	  }]);
	  return BottomSheet;
	}();

	function _getOverlay2() {
	  if (!this.layout.overlay) {
	    this.layout.overlay = main_core.Tag.render(_templateObject || (_templateObject = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"ui-bottomsheet__overlay\"></div>\n\t\t\t"])));
	    this.layout.overlay.addEventListener('click', this.close.bind(this));
	  }

	  return this.layout.overlay;
	}

	function _getHelp2() {
	  var _this5 = this;

	  if (!this.layout.help) {
	    if (main_core.Type.isString(this.help)) {
	      this.layout.help = main_core.Tag.render(_templateObject2 || (_templateObject2 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t\t<a href=\"", "\" class=\"ui-bottomsheet__panel-control--item --cursor-pointer\">\n\t\t\t\t\t\t<span class=\"ui-bottomsheet__panel-control--item-text\">", "</span>\n\t\t\t\t\t</a>\n\t\t\t\t"])), this.help, main_core.Loc.getMessage('UI_BOTTOMSHEET_HELP'));
	    }

	    if (main_core.Type.isFunction(this.help)) {
	      this.layout.help = main_core.Tag.render(_templateObject3 || (_templateObject3 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t\t<div class=\"ui-bottomsheet__panel-control--item --cursor-pointer\">\n\t\t\t\t\t\t<div class=\"ui-bottomsheet__panel-control--item-text\">", "</div>\n\t\t\t\t\t</div>\n\t\t\t\t"])), main_core.Loc.getMessage('UI_BOTTOMSHEET_HELP'));
	      this.layout.help.addEventListener('click', function () {
	        _this5.help();
	      });
	    }
	  }

	  return this.layout.help;
	}

	function _getClose2() {
	  if (!this.layout.close) {
	    this.layout.close = main_core.Tag.render(_templateObject4 || (_templateObject4 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"ui-bottomsheet__panel-control--item --cursor-pointer --close\">\n\t\t\t\t\t<div class=\"ui-bottomsheet__panel-control--item-text\">", "</div>\n\t\t\t\t</div>\n\t\t\t"])), main_core.Loc.getMessage('UI_BOTTOMSHEET_CLOSE'));
	    this.layout.close.addEventListener('click', this.close.bind(this));
	  }

	  return this.layout.close;
	}

	function _getPanel2() {
	  if (!this.layout.container) {
	    var panelWrapper = main_core.Tag.render(_templateObject5 || (_templateObject5 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"ui-bottomsheet__panel-wrapper\">\n\t\t\t\t\t", "\n\t\t\t\t</div>\n\t\t\t"])), _classPrivateMethodGet$1(this, _getContent, _getContent2).call(this));

	    if (this.padding || this.padding === 0) {
	      var padding;

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

	    this.layout.container = main_core.Tag.render(_templateObject6 || (_templateObject6 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"ui-bottomsheet__panel\">\n\t\t\t\t\t<div class=\"ui-bottomsheet__panel-control\">\n\t\t\t\t\t\t", "\n\t\t\t\t\t\t<div class=\"ui-bottomsheet__panel-handler\"></div>\n\t\t\t\t\t\t", "\n\t\t\t\t\t</div>\n\t\t\t\t\t", "\n\t\t\t\t</div>\n\t\t\t"])), this.help ? _classPrivateMethodGet$1(this, _getHelp, _getHelp2).call(this) : '', _classPrivateMethodGet$1(this, _getClose, _getClose2).call(this), panelWrapper);
	  }

	  return this.layout.container;
	}

	function _getContent2() {
	  if (!this.layout.content) {
	    this.layout.content = main_core.Tag.render(_templateObject7 || (_templateObject7 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"ui-bottomsheet__panel-content\"></div>\n\t\t\t"])));
	  }

	  return this.layout.content;
	}

	function _getWrapper2() {
	  if (!this.layout.wrapper) {
	    this.layout.wrapper = main_core.Tag.render(_templateObject8 || (_templateObject8 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"ui-bottomsheet ui-bottomsheet__scope ", "\"></div>\n\t\t\t"])), this.className);
	  }

	  return this.layout.wrapper;
	}

	exports.BottomSheet = BottomSheet;

}((this.BX.UI = this.BX.UI || {}),BX));
//# sourceMappingURL=bottomsheet.bundle.js.map
