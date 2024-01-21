this.BX = this.BX || {};
(function (exports,main_core,main_core_events) {
	'use strict';

	function _classPrivateMethodInitSpec(obj, privateSet) { _checkPrivateRedeclaration(obj, privateSet); privateSet.add(obj); }
	function _classPrivateFieldInitSpec(obj, privateMap, value) { _checkPrivateRedeclaration(obj, privateMap); privateMap.set(obj, value); }
	function _checkPrivateRedeclaration(obj, privateCollection) { if (privateCollection.has(obj)) { throw new TypeError("Cannot initialize the same private elements twice on an object"); } }
	function _classPrivateMethodGet(receiver, privateSet, fn) { if (!privateSet.has(receiver)) { throw new TypeError("attempted to get private field on non-instance"); } return fn; }
	var _id = /*#__PURE__*/new WeakMap();
	var _isOpen = /*#__PURE__*/new WeakMap();
	var _outerContainer = /*#__PURE__*/new WeakMap();
	var _innerContainer = /*#__PURE__*/new WeakMap();
	var _duration = /*#__PURE__*/new WeakMap();
	var _calcProgress = /*#__PURE__*/new WeakMap();
	var _linear = /*#__PURE__*/new WeakSet();
	var Collapser = /*#__PURE__*/function () {
	  function Collapser(params) {
	    babelHelpers.classCallCheck(this, Collapser);
	    _classPrivateMethodInitSpec(this, _linear);
	    _classPrivateFieldInitSpec(this, _id, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec(this, _isOpen, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec(this, _outerContainer, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec(this, _innerContainer, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec(this, _duration, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec(this, _calcProgress, {
	      writable: true,
	      value: void 0
	    });
	    babelHelpers.classPrivateFieldSet(this, _id, main_core.Type.isNil(params.id) ? 'collapser_' + main_core.Text.getRandom(8) : params.id);
	    babelHelpers.classPrivateFieldSet(this, _isOpen, main_core.Type.isBoolean(params.isOpen) ? params.isOpen : true);
	    babelHelpers.classPrivateFieldSet(this, _outerContainer, params.outerContainer);
	    babelHelpers.classPrivateFieldSet(this, _innerContainer, params.innerContainer);
	    babelHelpers.classPrivateFieldSet(this, _duration, main_core.Type.isNumber(params.duration) ? params.duration : 500);
	    babelHelpers.classPrivateFieldSet(this, _calcProgress, main_core.Type.isFunction(params.calcProgress) ? params.calcProgress : _classPrivateMethodGet(this, _linear, _linear2));
	    this.init(params);
	  }
	  babelHelpers.createClass(Collapser, [{
	    key: "init",
	    value: function init(params) {
	      main_core.Dom.style(this.getChildrenContainer(), 'overflow', 'hidden');
	      if (!babelHelpers.classPrivateFieldGet(this, _isOpen)) {
	        main_core.Dom.style(this.getChildrenContainer(), 'height', '0px');
	      }
	      if (main_core.Type.isElementNode(params.buttons)) {
	        params.buttons = [params.buttons];
	      }
	      if (main_core.Type.isArray(params.buttons) || params.buttons instanceof NodeList) {
	        for (var index in params.buttons) {
	          var button = params.buttons[index];
	          if (main_core.Type.isElementNode(button)) {
	            button.addEventListener('click', this.toggle.bind(this));
	          }
	        }
	      } else {
	        this.getOuterContainer().addEventListener('click', this.toggle.bind(this));
	      }
	    }
	  }, {
	    key: "expand",
	    value: function expand() {
	      if (this.isOpen()) {
	        return;
	      }
	      this.showAnimate(true);
	    }
	  }, {
	    key: "collapse",
	    value: function collapse() {
	      if (!this.isOpen()) {
	        return;
	      }
	      this.showAnimate(false);
	    }
	  }, {
	    key: "showAnimate",
	    value: function showAnimate(isOpen) {
	      var _this = this;
	      var start = performance.now();
	      var draw = this.makeDraw(this.isOpen());
	      var animate = function animate(time) {
	        var partTime = (time - start) / babelHelpers.classPrivateFieldGet(_this, _duration);
	        if (partTime > 1) {
	          partTime = 1;
	        }
	        var process = babelHelpers.classPrivateFieldGet(_this, _calcProgress).call(_this, partTime);
	        draw(process);
	        if (partTime < 1) {
	          requestAnimationFrame(animate);
	        } else {
	          _this.setOpen(isOpen);
	          main_core_events.EventEmitter.emit(main_core_events.EventEmitter.GLOBAL_TARGET, 'BX.UI.Collapse:onToggle', {
	            isOpen: _this.isOpen(),
	            source: _this
	          });
	          if (isOpen) {
	            main_core.Dom.style(_this.getChildrenContainer(), 'height', null);
	          }
	        }
	      };
	      requestAnimationFrame(animate);
	    }
	  }, {
	    key: "makeDraw",
	    value: function makeDraw(isOpen) {
	      var _this2 = this;
	      if (isOpen) {
	        return function (partTime) {
	          var process = _this2.getChildrenContainer().offsetHeight - _this2.getChildrenContainer().offsetHeight * partTime;
	          main_core.Dom.style(_this2.getChildrenContainer(), 'height', process + 'px');
	        };
	      } else {
	        return function (partTime) {
	          var process = _this2.getChildrenContainer().scrollHeight * partTime;
	          main_core.Dom.style(_this2.getChildrenContainer(), 'height', process + 'px');
	        };
	      }
	    }
	  }, {
	    key: "getChildrenContainer",
	    value: function getChildrenContainer() {
	      return babelHelpers.classPrivateFieldGet(this, _innerContainer);
	    }
	  }, {
	    key: "getOuterContainer",
	    value: function getOuterContainer() {
	      return babelHelpers.classPrivateFieldGet(this, _outerContainer);
	    }
	  }, {
	    key: "setOpen",
	    value: function setOpen(state) {
	      babelHelpers.classPrivateFieldSet(this, _isOpen, state);
	    }
	  }, {
	    key: "isOpen",
	    value: function isOpen() {
	      return babelHelpers.classPrivateFieldGet(this, _isOpen);
	    }
	  }, {
	    key: "toggle",
	    value: function toggle() {
	      this.isOpen() ? this.collapse() : this.expand();
	    }
	  }, {
	    key: "getId",
	    value: function getId() {
	      return babelHelpers.classPrivateFieldGet(this, _id);
	    }
	  }]);
	  return Collapser;
	}();
	function _linear2(partTime) {
	  return partTime;
	}

	exports.Collapser = Collapser;

}((this.BX.UI = this.BX.UI || {}),BX,BX.Event));
//# sourceMappingURL=bundle.js.map
