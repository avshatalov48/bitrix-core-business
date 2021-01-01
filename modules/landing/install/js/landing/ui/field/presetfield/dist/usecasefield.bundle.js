this.BX = this.BX || {};
this.BX.Landing = this.BX.Landing || {};
this.BX.Landing.UI = this.BX.Landing.UI || {};
(function (exports, landing_ui_field_basefield, main_core, landing_loc) {
	'use strict';

	function _templateObject3() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"landing-ui-field-use-case-link\" onclick=\"", "\">\n\t\t\t\t\t", "\n\t\t\t\t</div>\n\t\t\t"]);

	  _templateObject3 = function _templateObject3() {
	    return data;
	  };

	  return data;
	}

	function _templateObject2() {
	  var data = babelHelpers.taggedTemplateLiteral(["<span class=\"landing-ui-field-use-case-icon landing-ui-field-use-case-icon-default\"></span>"]);

	  _templateObject2 = function _templateObject2() {
	    return data;
	  };

	  return data;
	}

	function _templateObject() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"landing-ui-field-use-case-layout\">\n\t\t\t\t\t<div class=\"landing-ui-field-use-case-left\">\n\t\t\t\t\t\t", "\n\t\t\t\t\t</div>\n\t\t\t\t\t<div class=\"landing-ui-field-use-case-right\">\n\t\t\t\t\t\t", "\n\t\t\t\t\t\t", "\n\t\t\t\t\t</div>\n\t\t\t\t</div>\n\t\t\t"]);

	  _templateObject = function _templateObject() {
	    return data;
	  };

	  return data;
	}
	/**
	 * @memberOf BX.Landing.UI.Field
	 */

	var UseCaseField =
	/*#__PURE__*/
	function (_BaseField) {
	  babelHelpers.inherits(UseCaseField, _BaseField);

	  function UseCaseField() {
	    var _this;

	    var options = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};
	    babelHelpers.classCallCheck(this, UseCaseField);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(UseCaseField).call(this, options));

	    _this.setEventNamespace('BX.Landing.UI.Field.UseCaseField');

	    main_core.Dom.addClass(_this.layout, 'landing-ui-field-use-case');

	    _this.setTitle(landing_loc.Loc.getMessage('LANDING_USE_CASE_FIELD_TITLE'));

	    _this.cache = new main_core.Cache.MemoryCache();
	    _this.onLinkClick = _this.onLinkClick.bind(babelHelpers.assertThisInitialized(_this));
	    main_core.Dom.replace(_this.layout, _this.getLayout());
	    _this.layout = _this.getLayout();
	    return _this;
	  }

	  babelHelpers.createClass(UseCaseField, [{
	    key: "getLayout",
	    value: function getLayout() {
	      var _this2 = this;

	      return this.cache.remember('layout', function () {
	        return main_core.Tag.render(_templateObject(), _this2.getIcon(), _this2.header, _this2.getLink());
	      });
	    }
	  }, {
	    key: "getIcon",
	    value: function getIcon() {
	      return this.cache.remember('icon', function () {
	        return main_core.Tag.render(_templateObject2());
	      });
	    }
	  }, {
	    key: "setIcon",
	    value: function setIcon(icon) {
	      main_core.Dom.style(this.getIcon(), 'background-image', "url(".concat(icon, ")"));
	    }
	  }, {
	    key: "getLink",
	    value: function getLink() {
	      var _this3 = this;

	      return this.cache.remember('link', function () {
	        return main_core.Tag.render(_templateObject3(), _this3.onLinkClick, landing_loc.Loc.getMessage('LANDING_USE_CASE_DEFAULT_CASE_TITLE'));
	      });
	    }
	  }, {
	    key: "setLinkText",
	    value: function setLinkText(text) {
	      this.getLink().textContent = text;
	    }
	  }, {
	    key: "onLinkClick",
	    value: function onLinkClick(event) {
	      event.preventDefault();
	      this.emit('onClick');
	    }
	  }]);
	  return UseCaseField;
	}(landing_ui_field_basefield.BaseField);

	exports.UseCaseField = UseCaseField;

}(this.BX.Landing.UI.Field = this.BX.Landing.UI.Field || {}, BX.Landing.UI.Field, BX, BX.Landing));
//# sourceMappingURL=usecasefield.bundle.js.map
