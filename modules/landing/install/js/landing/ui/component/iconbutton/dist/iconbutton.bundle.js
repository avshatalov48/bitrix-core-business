this.BX = this.BX || {};
this.BX.Landing = this.BX.Landing || {};
this.BX.Landing.UI = this.BX.Landing.UI || {};
(function (exports,main_core,main_core_events,landing_ui_component_internal) {
	'use strict';

	function _templateObject() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div \n\t\t\t\t\tclass=\"landing-ui-button-icon-", "\"\n\t\t\t\t\tonclick=\"", "\"\n\t\t\t\t\ttitle=\"", "\"\n\t\t\t\t></div>\n\t\t\t"]);

	  _templateObject = function _templateObject() {
	    return data;
	  };

	  return data;
	}
	var IconButton = /*#__PURE__*/function (_EventEmitter) {
	  babelHelpers.inherits(IconButton, _EventEmitter);

	  function IconButton(options) {
	    var _this;

	    babelHelpers.classCallCheck(this, IconButton);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(IconButton).call(this));

	    _this.setEventNamespace('BX.Landing.UI.Component.IconButton');

	    _this.subscribeFromOptions(landing_ui_component_internal.fetchEventsFromOptions(options));

	    _this.options = babelHelpers.objectSpread({}, options);
	    _this.cache = new main_core.Cache.MemoryCache();
	    _this.onClick = _this.onClick.bind(babelHelpers.assertThisInitialized(_this));
	    return _this;
	  }

	  babelHelpers.createClass(IconButton, [{
	    key: "getData",
	    value: function getData() {
	      return this.options.data;
	    }
	  }, {
	    key: "onClick",
	    value: function onClick(event) {
	      event.preventDefault();
	      this.emit('onClick');
	    }
	  }, {
	    key: "setType",
	    value: function setType(type) {
	      this.getLayout().className = "landing-ui-button-icon-".concat(type);
	    }
	  }, {
	    key: "getLayout",
	    value: function getLayout() {
	      var _this2 = this;

	      return this.cache.remember('layout', function () {
	        var layout = main_core.Tag.render(_templateObject(), _this2.options.type, _this2.onClick, main_core.Type.isStringFilled(_this2.options.title) ? _this2.options.title : '');

	        if (main_core.Type.isPlainObject(_this2.options.style)) {
	          main_core.Dom.style(layout, _this2.options.style);
	        }

	        if (main_core.Type.isStringFilled(_this2.options.iconSize)) {
	          main_core.Dom.style(layout, 'background-size', _this2.options.iconSize);
	        }

	        return layout;
	      });
	    }
	  }]);
	  return IconButton;
	}(main_core_events.EventEmitter);
	babelHelpers.defineProperty(IconButton, "Types", {
	  remove: 'remove',
	  drag: 'drag',
	  edit: 'edit',
	  font: 'font',
	  link: 'link',
	  user1: 'user1',
	  user1Active: 'user1active'
	});

	exports.IconButton = IconButton;

}((this.BX.Landing.UI.Component = this.BX.Landing.UI.Component || {}),BX,BX.Event,BX.Landing.UI.Component));
//# sourceMappingURL=iconbutton.bundle.js.map
