this.BX = this.BX || {};
this.BX.Landing = this.BX.Landing || {};
this.BX.Landing.UI = this.BX.Landing.UI || {};
(function (exports,main_core,landing_ui_form_baseform,landing_ui_field_smallswitch,main_core_events,landing_ui_component_link,landing_ui_component_internal) {
	'use strict';

	var _templateObject;

	function ownKeys(object, enumerableOnly) { var keys = Object.keys(object); if (Object.getOwnPropertySymbols) { var symbols = Object.getOwnPropertySymbols(object); enumerableOnly && (symbols = symbols.filter(function (sym) { return Object.getOwnPropertyDescriptor(object, sym).enumerable; })), keys.push.apply(keys, symbols); } return keys; }

	function _objectSpread(target) { for (var i = 1; i < arguments.length; i++) { var source = null != arguments[i] ? arguments[i] : {}; i % 2 ? ownKeys(Object(source), !0).forEach(function (key) { babelHelpers.defineProperty(target, key, source[key]); }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(target, Object.getOwnPropertyDescriptors(source)) : ownKeys(Object(source)).forEach(function (key) { Object.defineProperty(target, key, Object.getOwnPropertyDescriptor(source, key)); }); } return target; }
	/**
	 * @memberOf BX.Landing.UI.Form
	 */

	var FormSettingsForm = /*#__PURE__*/function (_BaseForm) {
	  babelHelpers.inherits(FormSettingsForm, _BaseForm);

	  function FormSettingsForm(options) {
	    var _this;

	    babelHelpers.classCallCheck(this, FormSettingsForm);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(FormSettingsForm).call(this, _objectSpread({
	      opened: true
	    }, options)));

	    _this.setEventNamespace('BX.Landing.UI.Form.FormSettingsForm');

	    _this.subscribeFromOptions(landing_ui_component_internal.fetchEventsFromOptions(options));

	    main_core.Dom.addClass(_this.layout, 'landing-ui-form-form-settings');
	    _this.onFieldChange = _this.onFieldChange.bind(babelHelpers.assertThisInitialized(_this));
	    _this.onSwitchChange = _this.onSwitchChange.bind(babelHelpers.assertThisInitialized(_this));
	    _this.cache = new main_core.Cache.MemoryCache();

	    if (_this.options.toggleable) {
	      _this.onSwitchChange(_this.options.opened);

	      if (!_this.options.toggleableType || _this.options.toggleableType === FormSettingsForm.ToggleableType.Switch) {
	        _this.getSwitch().setValue(_this.options.opened);

	        main_core.Dom.prepend(_this.getSwitch().getNode(), _this.header);
	      }

	      if (_this.options.toggleableType === FormSettingsForm.ToggleableType.Link) {
	        main_core.Dom.clean(_this.header);
	        main_core.Dom.append(_this.getLink().getLayout(), _this.header);
	      }
	    }

	    if (main_core.Type.isPlainObject(_this.options.help)) {
	      main_core.Dom.append(_this.getHelp(_this.options.help), _this.footer);
	    }

	    return _this;
	  }

	  babelHelpers.createClass(FormSettingsForm, [{
	    key: "getHelp",
	    value: function getHelp(options) {
	      return this.cache.remember('help', function () {
	        return main_core.Tag.render(_templateObject || (_templateObject = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"landing-ui-form-help\">\n\t\t\t\t\t<a href=\"", "\" target=\"_blank\">", "</a>\n\t\t\t\t</div>\n\t\t\t"])), options.href, options.text);
	      });
	    }
	  }, {
	    key: "addField",
	    value: function addField(field) {
	      if (main_core.Type.isFunction(field.subscribe)) {
	        field.subscribe('onChange', this.onFieldChange.bind(this));
	      }

	      babelHelpers.get(babelHelpers.getPrototypeOf(FormSettingsForm.prototype), "addField", this).call(this, field);
	    }
	  }, {
	    key: "replaceField",
	    value: function replaceField(oldField, newField) {
	      if (main_core.Type.isFunction(newField.subscribe)) {
	        newField.subscribe('onChange', this.onFieldChange.bind(this));
	      }

	      babelHelpers.get(babelHelpers.getPrototypeOf(FormSettingsForm.prototype), "replaceField", this).call(this, oldField, newField);
	    }
	  }, {
	    key: "onFieldChange",
	    value: function onFieldChange(event) {
	      this.emit('onChange', event.getData());
	    }
	  }, {
	    key: "getSwitch",
	    value: function getSwitch() {
	      var _this2 = this;

	      return this.cache.remember('switch', function () {
	        var switchField = new landing_ui_field_smallswitch.SmallSwitch({
	          value: _this2.options.opened
	        });
	        switchField.subscribe('onChange', function (event) {
	          _this2.onSwitchChange(event.getTarget().getValue());
	        });
	        return switchField;
	      });
	    }
	  }, {
	    key: "getLink",
	    value: function getLink() {
	      var _this3 = this;

	      return this.cache.remember('link', function () {
	        return new landing_ui_component_link.Link({
	          text: _this3.options.title,
	          color: landing_ui_component_link.Link.Colors.Grey,
	          onClick: function onClick() {
	            _this3.onSwitchChange(main_core.Dom.style(_this3.body, 'display') === 'none');
	          }
	        });
	      });
	    }
	  }, {
	    key: "onSwitchChange",
	    value: function onSwitchChange(state) {
	      if (!state) {
	        this.cache.set('isOpened', false);
	        main_core.Dom.style(this.body, 'display', 'none');
	        main_core.Dom.style(this.layout, 'margin-bottom', '20px');
	      } else {
	        this.cache.set('isOpened', true);
	        main_core.Dom.style(this.body, 'display', null);
	        main_core.Dom.style(this.layout, 'margin-bottom', null);
	      }

	      this.emit('onChange');
	    }
	  }, {
	    key: "isOpened",
	    value: function isOpened() {
	      return main_core.Text.toBoolean(this.cache.get('isOpened'));
	    }
	  }, {
	    key: "setOffsetTop",
	    value: function setOffsetTop(offset) {
	      main_core.Dom.style(this.getLayout(), 'margin-top', "".concat(offset, "px"));
	    }
	  }, {
	    key: "clear",
	    value: function clear() {
	      this.fields.forEach(function (field) {
	        if (main_core.Type.isFunction(field.getLayout)) {
	          main_core.Dom.remove(field.getLayout());
	        } else {
	          main_core.Dom.remove(field.layout);
	        }

	        field.unsubscribeAll('onChange');
	      });
	      this.fields.clear();
	    }
	  }]);
	  return FormSettingsForm;
	}(landing_ui_form_baseform.BaseForm);
	babelHelpers.defineProperty(FormSettingsForm, "ToggleableType", {
	  Link: 'link',
	  Switch: 'switch'
	});

	exports.FormSettingsForm = FormSettingsForm;

}((this.BX.Landing.UI.Form = this.BX.Landing.UI.Form || {}),BX,BX.Landing.UI.Form,BX.Landing.UI.Field,BX.Event,BX.Landing.UI.Component,BX.Landing.UI.Component));
//# sourceMappingURL=formsettingsform.bundle.js.map
