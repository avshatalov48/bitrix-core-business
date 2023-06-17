this.BX = this.BX || {};
this.BX.Landing = this.BX.Landing || {};
this.BX.Landing.Ui = this.BX.Landing.Ui || {};
this.BX.Landing.Ui.Panel = this.BX.Landing.Ui.Panel || {};
this.BX.Landing.Ui.Panel.Formsettingspanel = this.BX.Landing.Ui.Panel.Formsettingspanel || {};
(function (exports,ui_designTokens,landing_ui_card_headercard,landing_loc,landing_ui_form_formsettingsform,landing_ui_field_textfield,landing_ui_panel_basepresetpanel,main_core_events,landing_ui_field_basefield,ui_entitySelector,landing_pageobject,main_core,landing_ui_card_basecard) {
	'use strict';

	var UserSelectorField = /*#__PURE__*/function (_BaseField) {
	  babelHelpers.inherits(UserSelectorField, _BaseField);
	  function UserSelectorField(options) {
	    var _this;
	    babelHelpers.classCallCheck(this, UserSelectorField);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(UserSelectorField).call(this, options));
	    main_core.Dom.removeClass(_this.input, 'landing-ui-field-input');
	    _this.getTagSelector().renderTo(_this.input);
	    return _this;
	  }
	  babelHelpers.createClass(UserSelectorField, [{
	    key: "getTagSelector",
	    value: function getTagSelector() {
	      var _this2 = this;
	      return this.cache.remember('tagSelector', function () {
	        var root = landing_pageobject.PageObject.getRootWindow();
	        return new root.BX.UI.EntitySelector.TagSelector({
	          id: 'user-selector',
	          dialogOptions: {
	            id: 'user-selector',
	            entities: [{
	              id: 'user',
	              options: {
	                activeUsers: true
	              }
	            }],
	            preselectedItems: _this2.options.value,
	            events: {
	              'Item:onSelect': function ItemOnSelect() {
	                _this2.emit('onChange', {
	                  skipPrepare: true
	                });
	              },
	              'Item:onDeselect': function ItemOnDeselect() {
	                _this2.emit('onChange', {
	                  skipPrepare: true
	                });
	              }
	            }
	          }
	        });
	      });
	    }
	  }, {
	    key: "getValue",
	    value: function getValue() {
	      return this.getTagSelector().getDialog().getSelectedItems().map(function (item) {
	        return item.id;
	      });
	    }
	  }]);
	  return UserSelectorField;
	}(landing_ui_field_basefield.BaseField);

	var _templateObject;
	function ownKeys(object, enumerableOnly) { var keys = Object.keys(object); if (Object.getOwnPropertySymbols) { var symbols = Object.getOwnPropertySymbols(object); enumerableOnly && (symbols = symbols.filter(function (sym) { return Object.getOwnPropertyDescriptor(object, sym).enumerable; })), keys.push.apply(keys, symbols); } return keys; }
	function _objectSpread(target) { for (var i = 1; i < arguments.length; i++) { var source = null != arguments[i] ? arguments[i] : {}; i % 2 ? ownKeys(Object(source), !0).forEach(function (key) { babelHelpers.defineProperty(target, key, source[key]); }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(target, Object.getOwnPropertyDescriptors(source)) : ownKeys(Object(source)).forEach(function (key) { Object.defineProperty(target, key, Object.getOwnPropertyDescriptor(source, key)); }); } return target; }
	var Other = /*#__PURE__*/function (_ContentWrapper) {
	  babelHelpers.inherits(Other, _ContentWrapper);
	  function Other(options) {
	    var _this;
	    babelHelpers.classCallCheck(this, Other);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(Other).call(this, options));
	    _this.setEventNamespace('BX.Landing.UI.Panel.FormSettingsPanel.SpamProtection');
	    var header = new landing_ui_card_headercard.HeaderCard({
	      title: landing_loc.Loc.getMessage('LANDING_FORM_OTHER_TITLE')
	    });
	    var otherForm = new landing_ui_form_formsettingsform.FormSettingsForm({
	      id: 'other',
	      description: null,
	      fields: [_this.getNameField(), _this.getUserSelectorField(), _this.getCheckWorkTimeField(), _this.getLanguageField(), _this.getUseSignField()]
	    });
	    _this.addItem(header);
	    _this.addItem(otherForm);
	    var idCard = new landing_ui_card_basecard.BaseCard();
	    main_core.Dom.style(idCard.getLayout(), {
	      padding: 0,
	      margin: 0
	    });
	    var id = _this.options.formOptions.id;
	    main_core.Dom.append(main_core.Tag.render(_templateObject || (_templateObject = babelHelpers.taggedTemplateLiteral(["<div>", ": ", "</div>"])), landing_loc.Loc.getMessage('LANDING_CRM_FORM_ID'), id), idCard.getBody());
	    _this.addItem(idCard);
	    return _this;
	  }
	  babelHelpers.createClass(Other, [{
	    key: "canRemoveCopyrights",
	    value: function canRemoveCopyrights() {
	      return this.options.dictionary.sign.canRemove;
	    }
	  }, {
	    key: "getNameField",
	    value: function getNameField() {
	      var _this2 = this;
	      return this.cache.remember('nameField', function () {
	        return new landing_ui_field_textfield.TextField({
	          title: landing_loc.Loc.getMessage('LANDING_FORM_OTHER_TITLE_NAME_TITLE'),
	          selector: 'name',
	          textOnly: true,
	          content: _this2.options.formOptions.name
	        });
	      });
	    }
	  }, {
	    key: "getUseSignField",
	    value: function getUseSignField() {
	      var _this3 = this;
	      return this.cache.remember('useSignField', function () {
	        return new BX.Landing.UI.Field.Checkbox({
	          selector: 'useSign',
	          value: _this3.options.formOptions.data.useSign ? ['useSign'] : [],
	          items: [{
	            value: 'useSign',
	            html: "".concat(landing_loc.Loc.getMessage('LANDING_HEADER_AND_BUTTONS_SHOW_SIGN')).concat(_this3.createCopyRight()),
	            name: ''
	          }],
	          compact: true
	        });
	      });
	    }
	  }, {
	    key: "getUserSelectorField",
	    value: function getUserSelectorField() {
	      var _this4 = this;
	      return this.cache.remember('userSelectorField', function () {
	        return new UserSelectorField({
	          selector: 'users',
	          title: landing_loc.Loc.getMessage('LANDING_CRM_FORM_USER'),
	          value: _this4.options.formOptions.responsible.users.reduce(function (acc, item) {
	            if (main_core.Type.isStringFilled(item) || main_core.Type.isNumber(item)) {
	              acc.push(['user', item]);
	            }
	            return acc;
	          }, [])
	        });
	      });
	    }
	  }, {
	    key: "getCheckWorkTimeField",
	    value: function getCheckWorkTimeField() {
	      var _this5 = this;
	      return this.cache.remember('checkWorkTimeField', function () {
	        return new BX.Landing.UI.Field.Checkbox({
	          selector: 'checkWorkTime',
	          compact: true,
	          value: [_this5.options.formOptions.responsible.checkWorkTime ? 'Y' : 'N'],
	          items: [{
	            name: landing_loc.Loc.getMessage('LANDING_FORM_OTHER_CHECK_WORK_TIME'),
	            value: 'Y'
	          }]
	        });
	      });
	    }
	  }, {
	    key: "getLanguageField",
	    value: function getLanguageField() {
	      var _this6 = this;
	      return this.cache.remember('language', function () {
	        return new BX.Landing.UI.Field.Dropdown({
	          selector: 'language',
	          title: landing_loc.Loc.getMessage('LANDING_CRM_FORM_LANGUAGE'),
	          items: _this6.options.dictionary.languages.map(function (item) {
	            return {
	              name: item.name,
	              value: item.id
	            };
	          }),
	          content: _this6.options.formOptions.data.language
	        });
	      });
	    } // eslint-disable-next-line class-methods-use-this
	  }, {
	    key: "createCopyRight",
	    value: function createCopyRight() {
	      return "\n\t\t\t<span class=\"landing-ui-signin\">\n\t\t\t\t<span class=\"landing-ui-sign\">".concat(landing_loc.Loc.getMessage('LANDING_HEADER_AND_BUTTONS_BUTTONS_SIGN'), "</span>\n\t\t\t\t<span class=\"landing-ui-sign-in\">").concat(landing_loc.Loc.getMessage('LANDING_HEADER_AND_BUTTONS_BUTTONS_SIGN_BY'), "</span>\n\t\t\t\t<span class=\"landing-ui-sign-24\">24</span>\n\t\t\t</span>\n\t\t");
	    }
	  }, {
	    key: "valueReducer",
	    value: function valueReducer(value) {
	      return {
	        name: value.name,
	        data: {
	          language: this.getLanguageField().getValue(),
	          useSign: value.useSign.includes('useSign')
	        },
	        responsible: {
	          users: value.users,
	          checkWorkTime: value.checkWorkTime[0] === 'Y'
	        }
	      };
	    }
	  }, {
	    key: "onChange",
	    value: function onChange(event) {
	      if (!this.canRemoveCopyrights()) {
	        var checkbox = this.getUseSignField();
	        if (!checkbox.getValue().includes('useSign')) {
	          checkbox.setValue(['useSign']);
	          if (main_core.Type.isStringFilled(this.options.dictionary.restriction.helper)) {
	            var evalGlobal = main_core.Reflection.getClass('BX.evalGlobal');
	            if (main_core.Type.isFunction(evalGlobal)) {
	              evalGlobal(this.options.dictionary.restriction.helper);
	            }
	          }
	        }
	      }
	      this.emit('onChange', _objectSpread(_objectSpread({}, event.getData()), {}, {
	        skipPrepare: true
	      }));
	    }
	  }]);
	  return Other;
	}(landing_ui_panel_basepresetpanel.ContentWrapper);

	exports.default = Other;

}((this.BX.Landing.Ui.Panel.Formsettingspanel.Content = this.BX.Landing.Ui.Panel.Formsettingspanel.Content || {}),BX,BX.Landing.UI.Card,BX.Landing,BX.Landing.UI.Form,BX.Landing.UI.Field,BX.Landing.UI.Panel,BX.Event,BX.Landing.UI.Field,BX.UI.EntitySelector,BX.Landing,BX,BX.Landing.UI.Card));
//# sourceMappingURL=other.bundle.js.map
