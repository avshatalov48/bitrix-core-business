this.BX = this.BX || {};
this.BX.Landing = this.BX.Landing || {};
this.BX.Landing.Ui = this.BX.Landing.Ui || {};
this.BX.Landing.Ui.Panel = this.BX.Landing.Ui.Panel || {};
this.BX.Landing.Ui.Panel.Formsettingspanel = this.BX.Landing.Ui.Panel.Formsettingspanel || {};
(function (exports,landing_ui_card_headercard,landing_ui_panel_basepresetpanel,landing_ui_field_radiobuttonfield,main_core_events,landing_ui_field_presetfield,landing_ui_field_textfield,ui_designTokens,landing_ui_field_basefield,landing_ui_component_internal,landing_ui_card_messagecard,main_core,landing_loc) {
	'use strict';

	var _templateObject;
	var ActionPagesField = /*#__PURE__*/function (_BaseField) {
	  babelHelpers.inherits(ActionPagesField, _BaseField);
	  function ActionPagesField(options) {
	    var _this;
	    babelHelpers.classCallCheck(this, ActionPagesField);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(ActionPagesField).call(this, options));
	    _this.setEventNamespace('BX.Landing.UI.Field.ActionPagesField');
	    _this.subscribeFromOptions(landing_ui_component_internal.fetchEventsFromOptions(options));
	    _this.setLayoutClass('landing-ui-field-action-pages');
	    main_core.Dom.append(_this.getSuccess(), _this.input);
	    main_core.Dom.append(_this.getFailure(), _this.input);
	    main_core.Event.bind(document, 'click', _this.onDocumentClick.bind(babelHelpers.assertThisInitialized(_this)));
	    main_core.Event.bind(window.top.document, 'click', _this.onDocumentClick.bind(babelHelpers.assertThisInitialized(_this)));
	    return _this;
	  }
	  babelHelpers.createClass(ActionPagesField, [{
	    key: "onDocumentClick",
	    value: function onDocumentClick() {
	      var successInput = this.getSuccess().querySelector('.landing-ui-field-action-pages-page-text');
	      var failureInput = this.getFailure().querySelector('.landing-ui-field-action-pages-page-text');
	      main_core.Dom.attr(successInput, 'contenteditable', null);
	      main_core.Dom.attr(failureInput, 'contenteditable', null);
	    }
	  }, {
	    key: "getSuccess",
	    value: function getSuccess() {
	      var _this2 = this;
	      return this.cache.remember('success', function () {
	        return ActionPagesField.createPageBlock({
	          type: 'success',
	          title: landing_loc.Loc.getMessage('LANDING_FORM_ACTIONS_EDIT_SUCCESS_PAGE_TITLE'),
	          text: _this2.options.successText,
	          onFocus: function onFocus() {
	            _this2.emit('onShowSuccess', new main_core.Event.BaseEvent({
	              data: {
	                show: true
	              }
	            }));
	          },
	          onInput: function onInput() {
	            _this2.emit('onChange');
	            _this2.emit('onShowSuccess', new main_core.Event.BaseEvent({
	              data: {
	                show: true
	              }
	            }));
	          },
	          onShowClick: function onShowClick() {
	            _this2.emit('onShowSuccess');
	          },
	          onBlur: function onBlur() {
	            _this2.emit('onBlur');
	          }
	        });
	      });
	    }
	  }, {
	    key: "getFailure",
	    value: function getFailure() {
	      var _this3 = this;
	      return this.cache.remember('failure', function () {
	        return ActionPagesField.createPageBlock({
	          type: 'failure',
	          title: landing_loc.Loc.getMessage('LANDING_FORM_ACTIONS_EDIT_FAILURE_PAGE_TITLE'),
	          text: _this3.options.failureText,
	          onFocus: function onFocus() {
	            _this3.emit('onShowFailure', new main_core.Event.BaseEvent({
	              data: {
	                show: true
	              }
	            }));
	          },
	          onInput: function onInput() {
	            _this3.emit('onChange');
	            _this3.emit('onShowFailure', new main_core.Event.BaseEvent({
	              data: {
	                show: true
	              }
	            }));
	          },
	          onShowClick: function onShowClick() {
	            _this3.emit('onShowFailure');
	          },
	          onBlur: function onBlur() {
	            _this3.emit('onBlur');
	          }
	        });
	      });
	    }
	  }, {
	    key: "getSuccessText",
	    value: function getSuccessText() {
	      return this.getSuccess().querySelector('.landing-ui-field-action-pages-page-text').innerText;
	    }
	  }, {
	    key: "getFailureText",
	    value: function getFailureText() {
	      return this.getFailure().querySelector('.landing-ui-field-action-pages-page-text').innerText;
	    }
	  }, {
	    key: "getValue",
	    value: function getValue() {
	      return {
	        success: this.getSuccessText(),
	        failure: this.getFailureText()
	      };
	    }
	  }], [{
	    key: "createPageBlock",
	    value: function createPageBlock(options) {
	      var classPrefix = 'landing-ui-field-action-pages-page';
	      var onEditClick = function onEditClick(event) {
	        event.preventDefault();
	        event.stopPropagation();
	        var inner = event.currentTarget.closest(".".concat(classPrefix, "-inner"));
	        var textContainer = inner.querySelector(".".concat(classPrefix, "-text"));
	        main_core.Dom.attr(textContainer, 'contenteditable', !textContainer.isContentEditable);
	        if (main_core.Type.isFunction(options.onEditClick)) {
	          options.onEditClick(event);
	        }
	      };
	      var onEditorClick = function onEditorClick(event) {
	        event.stopPropagation();
	      };
	      var onViewClick = function onViewClick(event) {
	        event.preventDefault();
	        if (main_core.Type.isFunction(options.onShowClick)) {
	          options.onShowClick(event);
	        }
	      };
	      var onBlur = function onBlur(event) {
	        event.preventDefault();
	        if (main_core.Type.isFunction(options.onBlur)) {
	          options.onBlur(event);
	        }
	      };
	      var onFocus = function onFocus(event) {
	        event.preventDefault();
	        if (main_core.Type.isFunction(options.onFocus)) {
	          options.onFocus(event);
	        }
	      };
	      return main_core.Tag.render(_templateObject || (_templateObject = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<div class=\"", " ", "-", "\">\n\t\t\t\t<div class=\"", "-title\">\n\t\t\t\t\t", "\n\t\t\t\t</div>\n\t\t\t\t<div class=\"", "-inner\">\n\t\t\t\t\t<div class=\"", "-header\">\n\t\t\t\t\t\t<span class=\"", "-header-view\" onclick=\"", "\">\n\t\t\t\t\t\t\t", "\n\t\t\t\t\t\t</span>\n\t\t\t\t\t</div>\n\t\t\t\t\t<div class=\"", "-icon\"></div>\n\t\t\t\t\t<div class=\"", "-text\" onclick=\"", "\" \n\t\t\t\t\t\tonfocus=\"", "\" onblur=\"", "\" oninput=\"", "\">\n\t\t\t\t\t\t", "\n\t\t\t\t\t</div>\n\t\t\t\t\t<div class=\"", "-footer\">\n\t\t\t\t\t\t<span class=\"", "-footer-edit\" onclick=\"", "\">\n\t\t\t\t\t\t\t", "\n\t\t\t\t\t\t</span>\n\t\t\t\t\t</div>\n\t\t\t\t</div>\n\t\t\t</div>\n\t\t"])), classPrefix, classPrefix, options.type, classPrefix, options.title, classPrefix, classPrefix, classPrefix, onViewClick, landing_loc.Loc.getMessage('LANDING_FORM_ACTIONS_EDIT_PAGE_SHOW'), classPrefix, classPrefix, onEditorClick, onFocus, onBlur, options.onInput, main_core.Text.encode(options.text), classPrefix, classPrefix, onEditClick, landing_loc.Loc.getMessage('LANDING_FORM_ACTIONS_EDIT_PAGE_EDIT'));
	    }
	  }]);
	  return ActionPagesField;
	}(landing_ui_field_basefield.BaseField);

	var type1Icon = "/bitrix/js/landing/ui/panel/formsettingspanel/content/actions/dist/images/type1.svg";

	var type2Icon = "/bitrix/js/landing/ui/panel/formsettingspanel/content/actions/dist/images/type2.svg";

	var type3Icon = "/bitrix/js/landing/ui/panel/formsettingspanel/content/actions/dist/images/type3.svg";

	var _templateObject$1, _templateObject2;
	var RefillActionPagesField = /*#__PURE__*/function (_ActionPagesField) {
	  babelHelpers.inherits(RefillActionPagesField, _ActionPagesField);
	  function RefillActionPagesField(options) {
	    babelHelpers.classCallCheck(this, RefillActionPagesField);
	    return babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(RefillActionPagesField).call(this, options));
	  }
	  babelHelpers.createClass(RefillActionPagesField, [{
	    key: "getSuccess",
	    value: function getSuccess() {
	      var _this = this;
	      return this.cache.remember('success', function () {
	        return RefillActionPagesField.createPageBlock({
	          type: 'success',
	          title: landing_loc.Loc.getMessage('LANDING_FORM_ACTIONS_EDIT_SUCCESS_PAGE_TITLE'),
	          text: _this.options.successText,
	          buttonCaption: _this.options.buttonCaption,
	          onFocus: function onFocus() {
	            _this.emit('onShowSuccess', new main_core.Event.BaseEvent({
	              data: {
	                show: true
	              }
	            }));
	          },
	          onInput: function onInput() {
	            _this.emit('onChange');
	            _this.emit('onShowSuccess', new main_core.Event.BaseEvent({
	              data: {
	                show: true
	              }
	            }));
	          },
	          onShowClick: function onShowClick() {
	            _this.emit('onShowSuccess');
	          },
	          onBlur: function onBlur() {
	            _this.emit('onBlur');
	          }
	        });
	      });
	    }
	  }, {
	    key: "onDocumentClick",
	    value: function onDocumentClick() {
	      var successInput = this.getSuccess().querySelector('.landing-ui-field-action-pages-page-text');
	      var buttonInput = this.getSuccess().querySelector('.landing-ui-field-action-pages-page-button');
	      var failureInput = this.getFailure().querySelector('.landing-ui-field-action-pages-page-text');
	      main_core.Dom.attr(successInput, 'contenteditable', null);
	      main_core.Dom.attr(buttonInput, 'contenteditable', null);
	      main_core.Dom.attr(failureInput, 'contenteditable', null);
	    }
	  }, {
	    key: "getButtonCaptionText",
	    value: function getButtonCaptionText() {
	      return this.getSuccess().querySelector('.landing-ui-field-action-pages-page-button').innerText;
	    }
	  }, {
	    key: "getValue",
	    value: function getValue() {
	      return {
	        success: this.getSuccessText(),
	        buttonCaption: this.getButtonCaptionText(),
	        failure: this.getFailureText()
	      };
	    }
	  }], [{
	    key: "createPageBlock",
	    value: function createPageBlock(options) {
	      var classPrefix = 'landing-ui-field-action-pages-page';
	      var onEditClick = function onEditClick(event) {
	        event.preventDefault();
	        event.stopPropagation();
	        var inner = event.currentTarget.closest(".".concat(classPrefix, "-inner"));
	        var textContainer = inner.querySelector(".".concat(classPrefix, "-text"));
	        var buttonContainer = inner.querySelector(".".concat(classPrefix, "-button"));
	        main_core.Dom.attr(textContainer, 'contenteditable', !textContainer.isContentEditable);
	        main_core.Dom.attr(buttonContainer, 'contenteditable', !buttonContainer.isContentEditable);
	        if (main_core.Type.isFunction(options.onEditClick)) {
	          options.onEditClick(event);
	        }
	      };
	      var onEditorClick = function onEditorClick(event) {
	        event.stopPropagation();
	      };
	      var onViewClick = function onViewClick(event) {
	        event.preventDefault();
	        if (main_core.Type.isFunction(options.onShowClick)) {
	          options.onShowClick(event);
	        }
	      };
	      var onBlur = function onBlur(event) {
	        event.preventDefault();
	        if (main_core.Type.isFunction(options.onBlur)) {
	          options.onBlur(event);
	        }
	      };
	      var onFocus = function onFocus(event) {
	        event.preventDefault();
	        if (main_core.Type.isFunction(options.onFocus)) {
	          options.onFocus(event);
	        }
	      };
	      var buttonTag = '';
	      if (options.type === 'success') {
	        var buttonCaption = main_core.Text.encode(options.buttonCaption) || landing_loc.Loc.getMessage('LANDING_FORM_ACTIONS_REFILL_CAPTION');
	        buttonTag = main_core.Tag.render(_templateObject$1 || (_templateObject$1 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"", "-button\" onclick=\"", "\" \n\t\t\t\t\tonfocus=\"", "\" onblur=\"", "\" oninput=\"", "\">\n\t\t\t\t\t", "\n\t\t\t\t</div>\n\t\t\t"])), classPrefix, onEditorClick, onFocus, onBlur, options.onInput, buttonCaption);
	      }
	      return main_core.Tag.render(_templateObject2 || (_templateObject2 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<div class=\"", " ", "-", "\">\n\t\t\t\t<div class=\"", "-title\">\n\t\t\t\t\t", "\n\t\t\t\t</div>\n\t\t\t\t<div class=\"", "-inner\">\n\t\t\t\t\t<div class=\"", "-header\">\n\t\t\t\t\t\t<span class=\"", "-header-view\" onclick=\"", "\">\n\t\t\t\t\t\t\t", "\n\t\t\t\t\t\t</span>\n\t\t\t\t\t</div>\n\t\t\t\t\t<div class=\"", "-icon\"></div>\n\t\t\t\t\t<div class=\"", "-text\" onclick=\"", "\" onfocus=\"", "\" onblur=\"", "\"  \n\t\t\t\t\t\toninput=\"", "\">\n\t\t\t\t\t\t", "\n\t\t\t\t\t</div>\n\t\t\t\t\t", "\n\t\t\t\t\t<div class=\"", "-footer\">\n\t\t\t\t\t\t<span class=\"", "-footer-edit\" onclick=\"", "\">\n\t\t\t\t\t\t\t", "\n\t\t\t\t\t\t</span>\n\t\t\t\t\t</div>\n\t\t\t\t</div>\n\t\t\t</div>\n\t\t"])), classPrefix, classPrefix, options.type, classPrefix, options.title, classPrefix, classPrefix, classPrefix, onViewClick, landing_loc.Loc.getMessage('LANDING_FORM_ACTIONS_EDIT_PAGE_SHOW'), classPrefix, classPrefix, onEditorClick, onFocus, onBlur, options.onInput, main_core.Text.encode(options.text), buttonTag, classPrefix, classPrefix, onEditClick, landing_loc.Loc.getMessage('LANDING_FORM_ACTIONS_EDIT_PAGE_EDIT'));
	    }
	  }]);
	  return RefillActionPagesField;
	}(ActionPagesField);

	var ActionsContent = /*#__PURE__*/function (_ContentWrapper) {
	  babelHelpers.inherits(ActionsContent, _ContentWrapper);
	  function ActionsContent(options) {
	    var _this;
	    babelHelpers.classCallCheck(this, ActionsContent);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(ActionsContent).call(this, options));
	    _this.setEventNamespace('BX.Landing.UI.Panel.FormSettingsPanel.ActionsContent');
	    main_core.Dom.addClass(_this.getLayout(), 'landing-ui-actions-content-wrapper');
	    _this.addItem(_this.getHeader());
	    _this.addItem(_this.getTypeButtons());
	    if (_this.options.form) {
	      _this.options.form.sent = false;
	      _this.options.form.error = false;
	    }
	    var onBlur = function onBlur() {
	      _this.options.form.sent = false;
	      _this.options.form.error = false;
	    };
	    var showFailure = function showFailure(event) {
	      var show = event.data.show || null;
	      _this.options.formOptions.result = _this.getValue().result;
	      _this.options.form.stateText = _this.options.formOptions.result.failure.text;
	      _this.options.form.sent = show === null ? !_this.options.form.sent : show;
	      _this.options.form.error = _this.options.form.sent;
	    };
	    _this.getActionPages().subscribe('onShowSuccess', function (event) {
	      var show = event.data.show || null;
	      _this.options.formOptions.result = _this.getValue().result;
	      _this.options.form.stateText = _this.options.formOptions.result.success.text;
	      _this.options.form.sent = show === null ? !_this.options.form.sent : show;
	      _this.options.form.error = false;
	    }).subscribe('onShowFailure', showFailure).subscribe('onBlur', onBlur);
	    _this.getRefillActionPages().subscribe('onShowSuccess', function (event) {
	      var show = event.data.show || null;
	      _this.options.formOptions.result = _this.getValue().result;
	      _this.options.form.stateText = _this.options.formOptions.result.success.text;
	      _this.options.form.stateButton.text = _this.options.formOptions.result.refill && _this.options.formOptions.result.refill.active ? _this.options.formOptions.result.refill.caption : '';
	      _this.options.form.sent = show === null ? !_this.options.form.sent : show;
	      _this.options.form.error = false;
	      if (!main_core.Type.isFunction(_this.options.form.stateButton.handler)) {
	        _this.options.form.stateButton.handler = function () {};
	      }
	    }).subscribe('onShowFailure', showFailure).subscribe('onBlur', onBlur);
	    return _this;
	  }
	  babelHelpers.createClass(ActionsContent, [{
	    key: "getHeader",
	    value: function getHeader() {
	      return this.cache.remember('header', function () {
	        return new landing_ui_card_headercard.HeaderCard({
	          title: landing_loc.Loc.getMessage('LANDING_FORM_ACTIONS_TITLE')
	        });
	      });
	    }
	  }, {
	    key: "getMessage",
	    value: function getMessage(type) {
	      return new landing_ui_card_messagecard.MessageCard({
	        id: 'actionsMessage' + type,
	        header: landing_loc.Loc.getMessage('LANDING_ACTIONS_MESSAGE_HEADER_' + type),
	        description: landing_loc.Loc.getMessage('LANDING_ACTIONS_MESSAGE_DESCRIPTION_' + type),
	        restoreState: true
	      });
	    }
	  }, {
	    key: "getTypeButtons",
	    value: function getTypeButtons() {
	      var _this2 = this;
	      return this.cache.remember('typeButtons', function () {
	        return new landing_ui_field_radiobuttonfield.RadioButtonField({
	          selectable: true,
	          value: function () {
	            if (_this2.options.formOptions.result.refill.active) {
	              return 'type3';
	            }
	            if (main_core.Type.isStringFilled(_this2.options.formOptions.result.success.url) || main_core.Type.isStringFilled(_this2.options.formOptions.result.failure.url)) {
	              return 'type2';
	            }
	            return 'type1';
	          }(),
	          items: [{
	            id: 'type1',
	            title: landing_loc.Loc.getMessage('LANDING_FORM_ACTIONS_TYPE_1'),
	            icon: 'landing-ui-form-actions-type1'
	          }, {
	            id: 'type2',
	            title: landing_loc.Loc.getMessage('LANDING_FORM_ACTIONS_TYPE_2'),
	            icon: 'landing-ui-form-actions-type2'
	          }, {
	            id: 'type3',
	            title: landing_loc.Loc.getMessage('LANDING_FORM_ACTIONS_TYPE_3'),
	            icon: 'landing-ui-form-actions-type3'
	          }],
	          onChange: _this2.onTypeChange.bind(_this2)
	        });
	      });
	    }
	  }, {
	    key: "getCheckbox",
	    value: function getCheckbox() {
	      return this.cache.remember('checkbox', function () {
	        return new BX.Landing.UI.Field.Checkbox({
	          items: [{
	            name: landing_loc.Loc.getMessage('LANDING_FORM_ACTIONS_CHECKBOX_TITLE'),
	            value: true
	          }]
	        });
	      });
	    }
	  }, {
	    key: "getTypeDropdown",
	    value: function getTypeDropdown() {
	      var _this3 = this;
	      return this.cache.remember('typeDropdown', function () {
	        var field = new landing_ui_field_presetfield.PresetField({
	          events: {
	            onClick: function onClick() {
	              _this3.clear();
	              _this3.addItem(_this3.getHeader());
	              _this3.addItem(_this3.getTypeButtons());
	            }
	          }
	        });
	        field.setTitle(landing_loc.Loc.getMessage('LANDING_FORM_ACTIONS_TYPE_DROPDOWN_TITLE'));
	        field.setIcon(type1Icon);
	        return field;
	      });
	    }
	  }, {
	    key: "getSuccessLinkField",
	    value: function getSuccessLinkField() {
	      var _this4 = this;
	      return this.cache.remember('successLinkField', function () {
	        return new landing_ui_field_textfield.TextField({
	          title: landing_loc.Loc.getMessage('LANDING_FORM_ACTIONS_SUCCESS_FIELD_TITLE'),
	          placeholder: 'http://',
	          textOnly: true,
	          content: _this4.options.formOptions.result.success.url,
	          onInput: _this4.onChange.bind(_this4)
	        });
	      });
	    }
	  }, {
	    key: "getFailureLinkField",
	    value: function getFailureLinkField() {
	      var _this5 = this;
	      return this.cache.remember('failureLinkField', function () {
	        return new landing_ui_field_textfield.TextField({
	          title: landing_loc.Loc.getMessage('LANDING_FORM_ACTIONS_FAILURE_FIELD_TITLE'),
	          placeholder: 'http://',
	          textOnly: true,
	          content: _this5.options.formOptions.result.failure.url,
	          onInput: _this5.onChange.bind(_this5)
	        });
	      });
	    }
	  }, {
	    key: "getRefillCaptionField",
	    value: function getRefillCaptionField() {
	      var _this6 = this;
	      return this.cache.remember('refillCaptionFill', function () {
	        return new landing_ui_field_textfield.TextField({
	          title: landing_loc.Loc.getMessage('LANDING_FORM_ACTIONS_REFILL_CAPTION_FIELD_TITLE'),
	          textOnly: true,
	          content: _this6.options.formOptions.result.refill.caption || landing_loc.Loc.getMessage('LANDING_FORM_ACTIONS_REFILL_CAPTION'),
	          onInput: _this6.onChange.bind(_this6)
	        });
	      });
	    }
	  }, {
	    key: "getDelayField",
	    value: function getDelayField() {
	      var _this7 = this;
	      return this.cache.remember('delayField', function () {
	        return new BX.Landing.UI.Field.Dropdown({
	          selector: 'redirectDelay',
	          title: landing_loc.Loc.getMessage('LANDING_FORM_ACTIONS_DELAY_TITLE'),
	          content: _this7.options.formOptions.result.redirectDelay,
	          items: Array.from({
	            length: 11
	          }, function (item, index) {
	            return {
	              name: landing_loc.Loc.getMessage("LANDING_FORM_ACTIONS_DELAY_ITEM_".concat(index)),
	              value: index
	            };
	          })
	        });
	      });
	    }
	  }, {
	    key: "onChange",
	    value: function onChange() {
	      this.emit('onChange', {
	        skipPrepare: true
	      });
	    }
	  }, {
	    key: "getActionPages",
	    value: function getActionPages() {
	      var _this8 = this;
	      return this.cache.remember('actionPages', function () {
	        return new ActionPagesField({
	          successText: _this8.options.formOptions.result.success.text,
	          failureText: _this8.options.formOptions.result.failure.text,
	          onChange: _this8.onChange.bind(_this8)
	        });
	      });
	    }
	  }, {
	    key: "getRefillActionPages",
	    value: function getRefillActionPages() {
	      var _this9 = this;
	      return this.cache.remember('refillActionPages', function () {
	        return new RefillActionPagesField({
	          successText: _this9.options.formOptions.result.success.text,
	          buttonCaption: _this9.options.formOptions.result.refill.caption,
	          failureText: _this9.options.formOptions.result.failure.text,
	          onChange: _this9.onChange.bind(_this9)
	        });
	      });
	    }
	  }, {
	    key: "onTypeChange",
	    value: function onTypeChange(event) {
	      var data = event.getData();
	      var typeDropdown = this.getTypeDropdown();
	      this.clear();
	      this.addItem(this.getHeader());
	      this.addItem(this.getMessage(data.item.id));
	      this.addItem(typeDropdown);
	      typeDropdown.setLinkText(data.item.title.replace(/&nbsp;/, ' '));
	      if (data.item.id === 'type1') {
	        typeDropdown.setIcon(type1Icon);
	        this.addItem(this.getActionPages());
	      }
	      if (data.item.id === 'type2') {
	        typeDropdown.setIcon(type2Icon);
	        this.addItem(this.getSuccessLinkField());
	        this.addItem(this.getFailureLinkField());
	        this.addItem(this.getDelayField());
	      }
	      if (data.item.id === 'type3') {
	        typeDropdown.setIcon(type3Icon);
	        this.addItem(this.getRefillActionPages());
	      }
	    }
	  }, {
	    key: "getValue",
	    value: function getValue() {
	      var useRefill = this.getTypeButtons().getValue() === 'type3';
	      var actionPagesValue = !useRefill ? this.getActionPages().getValue() : this.getRefillActionPages().getValue();
	      var useRedirect = this.getTypeButtons().getValue() === 'type2';
	      return {
	        result: {
	          success: {
	            text: actionPagesValue.success,
	            url: useRedirect ? main_core.Text.decode(this.getSuccessLinkField().getValue()) : ''
	          },
	          failure: {
	            text: actionPagesValue.failure,
	            url: useRedirect ? main_core.Text.decode(this.getFailureLinkField().getValue()) : ''
	          },
	          redirectDelay: this.getDelayField().getValue(),
	          refill: {
	            active: useRefill,
	            caption: useRefill ? actionPagesValue.buttonCaption : ''
	          }
	        }
	      };
	    }
	  }]);
	  return ActionsContent;
	}(landing_ui_panel_basepresetpanel.ContentWrapper);

	exports.default = ActionsContent;

}((this.BX.Landing.Ui.Panel.Formsettingspanel.Content = this.BX.Landing.Ui.Panel.Formsettingspanel.Content || {}),BX.Landing.UI.Card,BX.Landing.UI.Panel,BX.Landing.UI.Field,BX.Event,BX.Landing.UI.Field,BX.Landing.UI.Field,BX,BX.Landing.UI.Field,BX.Landing.UI.Component,BX.Landing.UI.Card,BX,BX.Landing));
//# sourceMappingURL=actions.bundle.js.map
