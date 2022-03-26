this.BX = this.BX || {};
this.BX.Landing = this.BX.Landing || {};
this.BX.Landing.UI = this.BX.Landing.UI || {};
(function (exports,main_core,main_core_events) {
	'use strict';

	function _templateObject5() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<div \n\t\t\t\tclass=\"landing-ui-component-action-panel-button\"\n\t\t\t\tonclick=\"", "\"\n\t\t\t\tdata-id=\"", "\"\n\t\t\t>", "</div>\n\t\t"]);

	  _templateObject5 = function _templateObject5() {
	    return data;
	  };

	  return data;
	}

	function _templateObject4() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"landing-ui-component-action-panel-right\"></div>\n\t\t\t"]);

	  _templateObject4 = function _templateObject4() {
	    return data;
	  };

	  return data;
	}

	function _templateObject3() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"landing-ui-component-action-panel-center\"></div>\n\t\t\t"]);

	  _templateObject3 = function _templateObject3() {
	    return data;
	  };

	  return data;
	}

	function _templateObject2() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"landing-ui-component-action-panel-left\"></div>\n\t\t\t"]);

	  _templateObject2 = function _templateObject2() {
	    return data;
	  };

	  return data;
	}

	function _templateObject() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"landing-ui-component-action-panel\">\n\t\t\t\t\t", "\n\t\t\t\t\t", "\n\t\t\t\t\t", "\n\t\t\t\t</div>\n\t\t\t"]);

	  _templateObject = function _templateObject() {
	    return data;
	  };

	  return data;
	}
	var ActionPanel = /*#__PURE__*/function (_EventEmitter) {
	  babelHelpers.inherits(ActionPanel, _EventEmitter);

	  function ActionPanel(options) {
	    var _this;

	    babelHelpers.classCallCheck(this, ActionPanel);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(ActionPanel).call(this));

	    _this.setEventNamespace('BX.Landing.UI.Component.ActionPanel');

	    _this.options = babelHelpers.objectSpread({}, options);
	    _this.cache = new main_core.Cache.MemoryCache();
	    var _this$options = _this.options,
	        left = _this$options.left,
	        center = _this$options.center,
	        right = _this$options.right;

	    if (main_core.Type.isArray(left)) {
	      left.forEach(function (item) {
	        return _this.addItem(babelHelpers.objectSpread({}, item, {
	          align: 'left'
	        }));
	      });
	    }

	    if (main_core.Type.isArray(center)) {
	      center.forEach(function (item) {
	        return _this.addItem(babelHelpers.objectSpread({}, item, {
	          align: 'center'
	        }));
	      });
	    }

	    if (main_core.Type.isArray(right)) {
	      right.forEach(function (item) {
	        return _this.addItem(babelHelpers.objectSpread({}, item, {
	          align: 'right'
	        }));
	      });
	    }

	    if (main_core.Type.isDomNode(_this.options.renderTo)) {
	      main_core.Dom.append(_this.getLayout(), _this.options.renderTo);
	    }

	    if (main_core.Type.isPlainObject(_this.options.style)) {
	      main_core.Dom.style(_this.getLayout(), _this.options.style);
	    }

	    return _this;
	  }

	  babelHelpers.createClass(ActionPanel, [{
	    key: "getLayout",
	    value: function getLayout() {
	      var _this2 = this;

	      return this.cache.remember('layout', function () {
	        return main_core.Tag.render(_templateObject(), _this2.getLeftContainer(), _this2.getCenterContainer(), _this2.getRightContainer());
	      });
	    }
	  }, {
	    key: "getNode",
	    value: function getNode() {
	      return this.getLayout();
	    }
	  }, {
	    key: "getLeftContainer",
	    value: function getLeftContainer() {
	      return this.cache.remember('leftContainer', function () {
	        return main_core.Tag.render(_templateObject2());
	      });
	    }
	  }, {
	    key: "getCenterContainer",
	    value: function getCenterContainer() {
	      return this.cache.remember('centerContainer', function () {
	        return main_core.Tag.render(_templateObject3());
	      });
	    }
	  }, {
	    key: "getRightContainer",
	    value: function getRightContainer() {
	      return this.cache.remember('rightContainer', function () {
	        return main_core.Tag.render(_templateObject4());
	      });
	    }
	  }, {
	    key: "addItem",
	    value: function addItem(itemOptions) {
	      var item = main_core.Tag.render(_templateObject5(), itemOptions.onClick, itemOptions.id, main_core.Text.encode(itemOptions.text));

	      if (itemOptions.align === 'left') {
	        main_core.Dom.append(item, this.getLeftContainer());
	      }

	      if (itemOptions.align === 'center') {
	        main_core.Dom.append(item, this.getCenterContainer());
	      }

	      if (itemOptions.align === 'right') {
	        main_core.Dom.append(item, this.getRightContainer());
	      }
	    }
	  }]);
	  return ActionPanel;
	}(main_core_events.EventEmitter);

	exports.ActionPanel = ActionPanel;

}((this.BX.Landing.UI.Component = this.BX.Landing.UI.Component || {}),BX,BX.Event));
//# sourceMappingURL=actionpanel.bundle.js.map
