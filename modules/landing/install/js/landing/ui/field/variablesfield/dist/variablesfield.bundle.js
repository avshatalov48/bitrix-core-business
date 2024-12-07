/* eslint-disable */
this.BX = this.BX || {};
this.BX.Landing = this.BX.Landing || {};
this.BX.Landing.UI = this.BX.Landing.UI || {};
(function (exports,landing_ui_field_textfield,main_core,landing_ui_button_basebutton,main_popup,landing_pageobject) {
	'use strict';

	var _templateObject, _templateObject2;
	function ownKeys(object, enumerableOnly) { var keys = Object.keys(object); if (Object.getOwnPropertySymbols) { var symbols = Object.getOwnPropertySymbols(object); enumerableOnly && (symbols = symbols.filter(function (sym) { return Object.getOwnPropertyDescriptor(object, sym).enumerable; })), keys.push.apply(keys, symbols); } return keys; }
	function _objectSpread(target) { for (var i = 1; i < arguments.length; i++) { var source = null != arguments[i] ? arguments[i] : {}; i % 2 ? ownKeys(Object(source), !0).forEach(function (key) { babelHelpers.defineProperty(target, key, source[key]); }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(target, Object.getOwnPropertyDescriptors(source)) : ownKeys(Object(source)).forEach(function (key) { Object.defineProperty(target, key, Object.getOwnPropertyDescriptor(source, key)); }); } return target; }
	var instances = Symbol('instances');

	/**
	 * @memberOf BX.Landing.UI.Field
	 */
	var VariablesField = /*#__PURE__*/function (_TextField) {
	  babelHelpers.inherits(VariablesField, _TextField);
	  function VariablesField(options) {
	    var _this;
	    babelHelpers.classCallCheck(this, VariablesField);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(VariablesField).call(this, _objectSpread(_objectSpread({}, options), {}, {
	      textOnly: true
	    })));
	    _this.setEventNamespace('BX.Landing.UI.Field.VariablesField');
	    _this.onTopDocumentClick = _this.onTopDocumentClick.bind(babelHelpers.assertThisInitialized(_this));
	    main_core.Event.bind(window.top.document, 'click', _this.onTopDocumentClick);
	    main_core.Dom.append(_this.getLayout(), _this.layout);
	    VariablesField[instances].push(babelHelpers.assertThisInitialized(_this));
	    return _this;
	  }
	  babelHelpers.createClass(VariablesField, [{
	    key: "onTopDocumentClick",
	    value: function onTopDocumentClick() {
	      // const rootWindowDocument = PageObject.getRootWindow().document;
	      // if (rootWindowDocument !== this.input.ownerDocument)
	      // {
	      // 	this.getMenu().close();
	      // 	super.onDocumentClick();
	      // }
	    }
	  }, {
	    key: "onInputClick",
	    value: function onInputClick(event) {
	      // event.preventDefault();

	      this.lastRange = this.input.ownerDocument.createRange(this.input.innerText.length, this.input.innerText.length);
	      this.lastRange = this.input.ownerDocument.getSelection().getRangeAt(0);
	    }
	  }, {
	    key: "getLayout",
	    value: function getLayout() {
	      var _this2 = this;
	      return this.cache.remember('layout', function () {
	        return main_core.Tag.render(_templateObject || (_templateObject = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"landing-ui-field landing-ui-field-variables\">\n\t\t\t\t\t<div class=\"landing-ui-field-variables-left\">", "</div>\n\t\t\t\t\t<div class=\"landing-ui-field-variables-right\">", "</div>\n\t\t\t\t</div>\n\t\t\t"])), _this2.input, _this2.getButton());
	      });
	    }
	  }, {
	    key: "getButton",
	    value: function getButton() {
	      var _this3 = this;
	      return this.cache.remember('button', function () {
	        return main_core.Tag.render(_templateObject2 || (_templateObject2 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div \n\t\t\t\t\tclass=\"landing-ui-field-variables-button\" \n\t\t\t\t\tonclick=\"", "\"\n\t\t\t\t></div>\n\t\t\t"])), _this3.onButtonClick.bind(_this3));
	      });
	    }
	  }, {
	    key: "getMenu",
	    value: function getMenu() {
	      var _this4 = this;
	      return this.cache.remember('menu', function () {
	        var rootWindow = landing_pageobject.PageObject.getRootWindow();
	        var menu = new rootWindow.BX.Main.Menu({
	          bindElement: _this4.getButton(),
	          targetContainer: _this4.getLayout(),
	          autoHide: true,
	          maxHeight: 250,
	          items: _this4.options.variables.map(function (variable) {
	            if (variable.delimiter) {
	              return {
	                delimiter: true
	              };
	            }
	            return {
	              text: variable.name,
	              onclick: function onclick() {
	                _this4.onVariableClick(variable);
	                menu.close();
	              }
	            };
	          }),
	          events: {
	            onPopupShow: function onPopupShow() {
	              VariablesField[instances].forEach(function (item) {
	                if (item !== _this4) {
	                  item.getMenu().close();
	                }
	              });
	              setTimeout(function () {
	                main_core.Dom.style(menu.getMenuContainer(), {
	                  left: 'auto',
	                  right: '0px',
	                  top: '30px'
	                });
	              });
	            }
	          }
	        });
	        return menu;
	      });
	    }
	  }, {
	    key: "onInputInput",
	    value: function onInputInput() {
	      var currentDocument = this.getLayout().ownerDocument;
	      this.lastRange = currentDocument.getSelection().getRangeAt(0);
	      babelHelpers.get(babelHelpers.getPrototypeOf(VariablesField.prototype), "onInputInput", this).call(this);
	    }
	  }, {
	    key: "onVariableClick",
	    value: function onVariableClick(variable) {
	      this.enableEdit();
	      this.input.focus();
	      var currentDocument = this.getLayout().ownerDocument;
	      if (this.lastRange) {
	        currentDocument.getSelection().removeAllRanges();
	        currentDocument.getSelection().addRange(this.lastRange);
	      }
	      currentDocument.execCommand('insertText', null, " ".concat(variable.value, " "));
	    }
	  }, {
	    key: "onButtonClick",
	    value: function onButtonClick(event) {
	      event.preventDefault();
	      event.stopPropagation();
	      if (!this.lastRange && this.input.innerText.length) {
	        var currentDocument = this.input.ownerDocument;
	        currentDocument.getSelection().collapse(this.input.childNodes[0], this.input.innerText.length);
	        this.lastRange = currentDocument.getSelection().getRangeAt(0);
	      }
	      var menu = this.getMenu();
	      if (menu.getPopupWindow().isShown()) {
	        this.getMenu().close();
	      } else {
	        this.getMenu().show();
	      }
	    }
	  }, {
	    key: "getValue",
	    value: function getValue() {
	      return this.input.innerText;
	    }
	  }]);
	  return VariablesField;
	}(landing_ui_field_textfield.TextField);
	babelHelpers.defineProperty(VariablesField, instances, []);

	exports.VariablesField = VariablesField;

}((this.BX.Landing.UI.Field = this.BX.Landing.UI.Field || {}),BX.Landing.UI.Field,BX,BX.Landing.UI.Button,BX.Main,BX.Landing));
//# sourceMappingURL=variablesfield.bundle.js.map
