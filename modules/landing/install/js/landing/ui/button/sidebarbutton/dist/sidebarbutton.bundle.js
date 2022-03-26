this.BX = this.BX || {};
this.BX.Landing = this.BX.Landing || {};
this.BX.Landing.UI = this.BX.Landing.UI || {};
(function (exports,main_core,landing_ui_button_basebutton,landing_loc) {
	'use strict';

	var _templateObject;
	/**
	 * @memberOf BX.Landing.UI.Button
	 */

	var SidebarButton = /*#__PURE__*/function (_BaseButton) {
	  babelHelpers.inherits(SidebarButton, _BaseButton);

	  function SidebarButton(id, options) {
	    var _this;

	    babelHelpers.classCallCheck(this, SidebarButton);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(SidebarButton).call(this, id, options));
	    main_core.Dom.addClass(_this.layout, 'landing-ui-button-sidebar');

	    if (_this.options.child === true) {
	      main_core.Dom.addClass(_this.layout, 'landing-ui-button-sidebar-child');
	    }

	    if (_this.options.empty === true) {
	      main_core.Dom.addClass(_this.layout, 'landing-ui-button-sidebar-empty');
	    }

	    if (_this.options.important === true && _this.options.child === true) {
	      var label = _this.createLabel('landing-ui-button-sidebar-icon-important', landing_loc.Loc.getMessage('LANDING_SIDEBAR_BUTTON_IMPORTANT_LABEL_TITLE'));

	      main_core.Dom.append(label, _this.layout);
	    }

	    return _this;
	  }

	  babelHelpers.createClass(SidebarButton, [{
	    key: "createLabel",
	    value: function createLabel(icon, text) {
	      return main_core.Tag.render(_templateObject || (_templateObject = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<span class=\"landing-ui-button-sidebar-icon ", "\">", "</span>\n\t\t"])), icon, text);
	    }
	  }]);
	  return SidebarButton;
	}(landing_ui_button_basebutton.BaseButton);

	exports.SidebarButton = SidebarButton;

}((this.BX.Landing.UI.Button = this.BX.Landing.UI.Button || {}),BX,BX.Landing.UI.Button,BX.Landing));
//# sourceMappingURL=sidebarbutton.bundle.js.map
