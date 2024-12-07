/* eslint-disable */
this.BX = this.BX || {};
this.BX.Landing = this.BX.Landing || {};
this.BX.Landing.UI = this.BX.Landing.UI || {};
(function (exports,landing_ui_button_basebutton,main_core,ui_label) {
	'use strict';

	var _templateObject;

	/**
	 * @memberOf BX.Landing.UI.Button
	 */
	var ActionButton = /*#__PURE__*/function (_BaseButton) {
	  babelHelpers.inherits(ActionButton, _BaseButton);
	  function ActionButton(id, options) {
	    var _this;
	    babelHelpers.classCallCheck(this, ActionButton);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(ActionButton).call(this, id, options));
	    _this.layout.classList.add('landing-ui-button-action');
	    _this.separate = Reflect.has(options, 'separate') ? options.separate : false;
	    _this.label = Reflect.has(options, 'label') ? options.label : null;
	    _this.disabled = Reflect.has(options, 'disabled') ? options.disabled : false;
	    _this.disabledHint = Reflect.has(options, 'disabledHint') ? options.disabledHint : null;
	    if (_this.separate) {
	      _this.layout.classList.add('--separate');
	    }
	    if (_this.label) {
	      var label = new ui_label.Label({
	        text: _this.label,
	        color: ui_label.LabelColor.PRIMARY,
	        size: ui_label.LabelSize.SM,
	        fill: true
	      });
	      main_core.Dom.append(label.render(), _this.layout);
	    }
	    if (_this.disabled && _this.disabledHint) {
	      _this.layout = main_core.Tag.render(_templateObject || (_templateObject = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"landing-ui-button-action-container\">", "</div>\n\t\t\t"])), _this.layout);
	      _this.layout.setAttribute('data-hint', _this.disabledHint);
	      _this.layout.setAttribute('data-hint-no-icon', '');
	      BX.UI.Hint.initNode(_this.layout);
	    }
	    return _this;
	  }
	  return ActionButton;
	}(landing_ui_button_basebutton.BaseButton);

	exports.ActionButton = ActionButton;

}((this.BX.Landing.UI.Button = this.BX.Landing.UI.Button || {}),BX.Landing.UI.Button,BX,BX.UI));
//# sourceMappingURL=actionbutton.bundle.js.map
