this.BX = this.BX || {};
this.BX.Landing = this.BX.Landing || {};
this.BX.Landing.Ui = this.BX.Landing.Ui || {};
(function (exports,main_core,landing_ui_field_basefield,landing_ui_form_menuform) {
	'use strict';

	/**
	 * @memberOf BX.Landing.UI.Field
	 */

	var MenuItemField = /*#__PURE__*/function (_BaseField) {
	  babelHelpers.inherits(MenuItemField, _BaseField);

	  function MenuItemField() {
	    var _this;

	    var options = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};
	    babelHelpers.classCallCheck(this, MenuItemField);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(MenuItemField).call(this, options));
	    _this.depth = options.depth;
	    _this.fields = options.fields;

	    if (_this.depth > 0) {
	      var levelPadding = 20;
	      main_core.Dom.style(_this.layout, 'margin-left', "".concat(levelPadding * _this.depth, "px"));
	    }

	    _this.form = new landing_ui_form_menuform.MenuForm({
	      fields: _this.fields
	    });
	    main_core.Dom.append(_this.form.layout, _this.layout);
	    return _this;
	  }

	  return MenuItemField;
	}(landing_ui_field_basefield.BaseField);

	exports.MenuItemField = MenuItemField;

}((this.BX.Landing.Ui.Field = this.BX.Landing.Ui.Field || {}),BX,BX.Landing.UI.Field,BX.Landing.UI.Form));
//# sourceMappingURL=menuitemfield.bundle.js.map
