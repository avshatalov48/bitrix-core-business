(function (exports,main_core) {
	'use strict';

	function _templateObject() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t<div class=\"main-ui-loader main-ui-hide\">\n\t\t\t<svg class=\"main-ui-loader-svg\" viewBox=\"25 25 50 50\">\n\t\t\t\t<circle class=\"main-ui-loader-svg-circle\" cx=\"50\" cy=\"50\" r=\"20\" fill=\"none\" stroke-miterlimit=\"10\">\n\t\t\t</svg>\n\t\t</div>\n\t"]);

	  _templateObject = function _templateObject() {
	    return data;
	  };

	  return data;
	}
	function layout() {
	  var container = main_core.Tag.render(_templateObject());
	  var circle = container.querySelector('.main-ui-loader-svg-circle');
	  return {
	    container: container,
	    circle: circle
	  };
	}

	function _templateObject4() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\tdata-is-shown: false;\n\t\t\t"]);

	  _templateObject4 = function _templateObject4() {
	    return data;
	  };

	  return data;
	}

	function _templateObject3() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t\t\tdisplay: none;\n\t\t\t\t\t"]);

	  _templateObject3 = function _templateObject3() {
	    return data;
	  };

	  return data;
	}

	function _templateObject2() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\tdisplay: null;\n\t\t\t"]);

	  _templateObject2 = function _templateObject2() {
	    return data;
	  };

	  return data;
	}

	function _templateObject$1() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\tdata-is-shown: true;\n\t\t\t"]);

	  _templateObject$1 = function _templateObject() {
	    return data;
	  };

	  return data;
	}
	function show(element) {
	  if (!main_core.Type.isDomNode(element)) {
	    return Promise.reject(new Error('element is not Element'));
	  }

	  return new Promise(function (resolve) {
	    if (element.dataset.isShown === 'false' || !element.dataset.isShown) {
	      var handler = function handler(event) {
	        if (event.animationName === 'showMainLoader') {
	          main_core.Event.unbind(element, 'animationend', handler);
	          resolve(event);
	        }
	      };

	      main_core.Event.bind(element, 'animationend', handler);
	      main_core.Tag.attrs(element)(_templateObject$1());
	      main_core.Tag.style(element)(_templateObject2());
	      main_core.Dom.removeClass(element, 'main-ui-hide');
	      main_core.Dom.addClass(element, 'main-ui-show');
	    }
	  });
	}
	function hide(element) {
	  if (!main_core.Type.isDomNode(element)) {
	    return Promise.reject(new Error('element is not Element'));
	  }

	  return new Promise(function (resolve) {
	    if (element.dataset.isShown === 'true') {
	      var handler = function handler(event) {
	        if (event.animationName === 'hideMainLoader') {
	          main_core.Tag.style(element)(_templateObject3());
	          main_core.Event.unbind(element, 'animationend', handler);
	          resolve(event);
	        }
	      };

	      main_core.Event.bind(element, 'animationend', handler);
	      main_core.Tag.attrs(element)(_templateObject4());
	      main_core.Dom.removeClass(element, 'main-ui-show');
	      main_core.Dom.addClass(element, 'main-ui-hide');
	    }
	  });
	}

	var defaultOptions = {
	  size: 110
	};
	var STATE_READY = 'ready';
	var STATE_SHOWN = 'shown';
	var STATE_HIDDEN = 'hidden';
	var Loader =
	/*#__PURE__*/
	function () {
	  function Loader() {
	    var options = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};
	    babelHelpers.classCallCheck(this, Loader);
	    babelHelpers.defineProperty(this, "data", layout());
	    babelHelpers.defineProperty(this, "state", STATE_READY);
	    babelHelpers.defineProperty(this, "currentTarget", null);
	    var currentOptions = babelHelpers.objectSpread({}, defaultOptions, options);
	    this.currentTarget = currentOptions.target;
	    this.setOptions(currentOptions);
	  }

	  babelHelpers.createClass(Loader, [{
	    key: "createLayout",
	    value: function createLayout() {
	      return this.layout;
	    }
	  }, {
	    key: "show",
	    value: function show$$1() {
	      var _this = this;

	      var target = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : null;
	      return new Promise(function () {
	        var targetElement = target || _this.currentTarget;

	        if (main_core.type.isDomNode(targetElement) && targetElement !== _this.layout.parentNode) {
	          _this.currentTarget = targetElement;
	          main_core.append(_this.layout, targetElement);
	        }

	        if (_this.state !== STATE_SHOWN) {
	          _this.state = STATE_SHOWN;
	          return show(_this.layout);
	        }

	        return false;
	      });
	    }
	  }, {
	    key: "hide",
	    value: function hide$$1() {
	      var _this2 = this;

	      return new Promise(function () {
	        if (_this2.state !== STATE_HIDDEN) {
	          _this2.state = STATE_HIDDEN;
	          return hide(_this2.layout);
	        }

	        return false;
	      });
	    }
	  }, {
	    key: "isShown",
	    value: function isShown() {
	      return this.state === STATE_SHOWN;
	    }
	  }, {
	    key: "destroy",
	    value: function destroy() {
	      main_core.remove(this.layout);
	    }
	  }, {
	    key: "setOptions",
	    value: function setOptions(_ref) {
	      var _this3 = this;

	      var target = _ref.target,
	          size = _ref.size,
	          color = _ref.color,
	          offset = _ref.offset,
	          mode = _ref.mode;
	      var layoutStyles = new Map();
	      var circleStyles = new Map();

	      if (main_core.type.isDomNode(target)) {
	        this.currentTarget = target;
	      }

	      if (main_core.type.isNumber(size)) {
	        layoutStyles.set('width', "".concat(size, "px"));
	        layoutStyles.set('height', "".concat(size, "px"));
	      }

	      if (main_core.type.isString(color)) {
	        circleStyles.set('stroke', color);
	      }

	      if (main_core.type.isObjectLike(offset)) {
	        var prefix = /^inline$|^custom$/.test(mode) ? '' : 'margin-';

	        if (main_core.type.isString(offset.top)) {
	          layoutStyles.set("".concat(prefix, "top"), offset.top);
	        }

	        if (main_core.type.isString(offset.left)) {
	          layoutStyles.set("".concat(prefix, "left"), offset.left);
	        }
	      }

	      if (mode === 'inline') {
	        main_core.addClass(this.layout, 'main-ui-loader-inline');
	      } else {
	        main_core.removeClass(this.layout, 'main-ui-loader-inline');
	      }

	      if (mode === 'custom') {
	        main_core.addClass(this.layout, 'main-ui-loader-custom');
	        main_core.removeClass(this.layout, 'main-ui-loader-inline');
	      }

	      layoutStyles.forEach(function (value, key) {
	        _this3.layout.style[key] = value;
	      });
	      circleStyles.forEach(function (value, key) {
	        _this3.circle.style[key] = value;
	      });
	    }
	  }, {
	    key: "layout",
	    get: function get() {
	      return this.data.container;
	    }
	  }, {
	    key: "circle",
	    get: function get() {
	      return this.data.circle;
	    }
	  }]);
	  return Loader;
	}();

	exports.Loader = Loader;

}((this.BX = this.BX || {}),BX));
//# sourceMappingURL=loader.bundle.js.map
