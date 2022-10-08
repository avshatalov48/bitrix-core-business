this.BX = this.BX || {};
this.BX.Landing = this.BX.Landing || {};
this.BX.Landing.UI = this.BX.Landing.UI || {};
(function (exports,ui_designTokens,main_core,main_core_events) {
	'use strict';

	var _templateObject, _templateObject2, _templateObject3, _templateObject4, _templateObject5;

	function ownKeys(object, enumerableOnly) { var keys = Object.keys(object); if (Object.getOwnPropertySymbols) { var symbols = Object.getOwnPropertySymbols(object); enumerableOnly && (symbols = symbols.filter(function (sym) { return Object.getOwnPropertyDescriptor(object, sym).enumerable; })), keys.push.apply(keys, symbols); } return keys; }

	function _objectSpread(target) { for (var i = 1; i < arguments.length; i++) { var source = null != arguments[i] ? arguments[i] : {}; i % 2 ? ownKeys(Object(source), !0).forEach(function (key) { babelHelpers.defineProperty(target, key, source[key]); }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(target, Object.getOwnPropertyDescriptors(source)) : ownKeys(Object(source)).forEach(function (key) { Object.defineProperty(target, key, Object.getOwnPropertyDescriptor(source, key)); }); } return target; }
	var ActionPanel = /*#__PURE__*/function (_EventEmitter) {
	  babelHelpers.inherits(ActionPanel, _EventEmitter);

	  function ActionPanel(options) {
	    var _this;

	    babelHelpers.classCallCheck(this, ActionPanel);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(ActionPanel).call(this));

	    _this.setEventNamespace('BX.Landing.UI.Component.ActionPanel');

	    _this.options = _objectSpread({}, options);
	    _this.cache = new main_core.Cache.MemoryCache();
	    var _this$options = _this.options,
	        left = _this$options.left,
	        center = _this$options.center,
	        right = _this$options.right;

	    if (main_core.Type.isArray(left)) {
	      left.forEach(function (item) {
	        return _this.addItem(_objectSpread(_objectSpread({}, item), {}, {
	          align: 'left'
	        }));
	      });
	    }

	    if (main_core.Type.isArray(center)) {
	      center.forEach(function (item) {
	        return _this.addItem(_objectSpread(_objectSpread({}, item), {}, {
	          align: 'center'
	        }));
	      });
	    }

	    if (main_core.Type.isArray(right)) {
	      right.forEach(function (item) {
	        return _this.addItem(_objectSpread(_objectSpread({}, item), {}, {
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
	        return main_core.Tag.render(_templateObject || (_templateObject = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"landing-ui-component-action-panel\">\n\t\t\t\t\t", "\n\t\t\t\t\t", "\n\t\t\t\t\t", "\n\t\t\t\t</div>\n\t\t\t"])), _this2.getLeftContainer(), _this2.getCenterContainer(), _this2.getRightContainer());
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
	        return main_core.Tag.render(_templateObject2 || (_templateObject2 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"landing-ui-component-action-panel-left\"></div>\n\t\t\t"])));
	      });
	    }
	  }, {
	    key: "getCenterContainer",
	    value: function getCenterContainer() {
	      return this.cache.remember('centerContainer', function () {
	        return main_core.Tag.render(_templateObject3 || (_templateObject3 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"landing-ui-component-action-panel-center\"></div>\n\t\t\t"])));
	      });
	    }
	  }, {
	    key: "getRightContainer",
	    value: function getRightContainer() {
	      return this.cache.remember('rightContainer', function () {
	        return main_core.Tag.render(_templateObject4 || (_templateObject4 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"landing-ui-component-action-panel-right\"></div>\n\t\t\t"])));
	      });
	    }
	  }, {
	    key: "addItem",
	    value: function addItem(itemOptions) {
	      var item = main_core.Tag.render(_templateObject5 || (_templateObject5 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<div \n\t\t\t\tclass=\"landing-ui-component-action-panel-button\"\n\t\t\t\tonclick=\"", "\"\n\t\t\t\tdata-id=\"", "\"\n\t\t\t>", "</div>\n\t\t"])), itemOptions.onClick, itemOptions.id, main_core.Text.encode(itemOptions.text));

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

}((this.BX.Landing.UI.Component = this.BX.Landing.UI.Component || {}),BX,BX,BX.Event));
//# sourceMappingURL=actionpanel.bundle.js.map
