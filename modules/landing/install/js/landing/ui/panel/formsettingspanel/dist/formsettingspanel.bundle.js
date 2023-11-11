this.BX = this.BX || {};
this.BX.Landing = this.BX.Landing || {};
this.BX.Landing.UI = this.BX.Landing.UI || {};
(function (exports,landing_ui_panel_basepresetpanel,landing_pageobject,landing_loc,main_core,landing_backend,main_loader,crm_form_client,ui_buttons,landing_env,landing_ui_panel_stylepanel,ui_dialogs_messagebox,ui_alerts,landing_ui_button_sidebarbutton,ui_tour,landing_ui_panel_fieldspanel,bitrix24_phoneverify,ui_switcher,ui_hint,ui_fonts_opensans,landing_history) {
	'use strict';

	var _templateObject, _templateObject2, _templateObject3;
	function ownKeys(object, enumerableOnly) { var keys = Object.keys(object); if (Object.getOwnPropertySymbols) { var symbols = Object.getOwnPropertySymbols(object); enumerableOnly && (symbols = symbols.filter(function (sym) { return Object.getOwnPropertyDescriptor(object, sym).enumerable; })), keys.push.apply(keys, symbols); } return keys; }
	function _objectSpread(target) { for (var i = 1; i < arguments.length; i++) { var source = null != arguments[i] ? arguments[i] : {}; i % 2 ? ownKeys(Object(source), !0).forEach(function (key) { babelHelpers.defineProperty(target, key, source[key]); }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(target, Object.getOwnPropertyDescriptors(source)) : ownKeys(Object(source)).forEach(function (key) { Object.defineProperty(target, key, Object.getOwnPropertyDescriptor(source, key)); }); } return target; }
	function _classPrivateMethodInitSpec(obj, privateSet) { _checkPrivateRedeclaration(obj, privateSet); privateSet.add(obj); }
	function _classPrivateFieldInitSpec(obj, privateMap, value) { _checkPrivateRedeclaration(obj, privateMap); privateMap.set(obj, value); }
	function _checkPrivateRedeclaration(obj, privateCollection) { if (privateCollection.has(obj)) { throw new TypeError("Cannot initialize the same private elements twice on an object"); } }
	function _classPrivateMethodGet(receiver, privateSet, fn) { if (!privateSet.has(receiver)) { throw new TypeError("attempted to get private field on non-instance"); } return fn; }
	var PHONE_VERIFY_FORM_ENTITY = 'crm_webform';

	/**
	 * @memberOf BX.Landing.UI.Panel
	 */
	var _phoneDoesntVerifiedResponseCode = /*#__PURE__*/new WeakMap();
	var _isPhoneValidationError = /*#__PURE__*/new WeakSet();
	var _showPhoneVerifySlider = /*#__PURE__*/new WeakSet();
	var FormSettingsPanel = /*#__PURE__*/function (_BasePresetPanel) {
	  babelHelpers.inherits(FormSettingsPanel, _BasePresetPanel);
	  babelHelpers.createClass(FormSettingsPanel, null, [{
	    key: "getInstance",
	    value: function getInstance() {
	      var rootWindow = landing_pageobject.PageObject.getRootWindow();
	      var rootWindowPanel = rootWindow.BX.Landing.UI.Panel.FormSettingsPanel;
	      if (!rootWindowPanel.instance && !FormSettingsPanel.instance) {
	        rootWindowPanel.instance = new FormSettingsPanel();
	      }
	      return rootWindowPanel.instance || FormSettingsPanel.instance;
	    }
	  }]);
	  function FormSettingsPanel() {
	    var _this;
	    babelHelpers.classCallCheck(this, FormSettingsPanel);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(FormSettingsPanel).call(this));
	    _classPrivateMethodInitSpec(babelHelpers.assertThisInitialized(_this), _showPhoneVerifySlider);
	    _classPrivateMethodInitSpec(babelHelpers.assertThisInitialized(_this), _isPhoneValidationError);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "adjustActionsPanels", false);
	    _classPrivateFieldInitSpec(babelHelpers.assertThisInitialized(_this), _phoneDoesntVerifiedResponseCode, {
	      writable: true,
	      value: 'PHONE_NOT_VERIFIED'
	    });
	    _this.setEventNamespace('BX.Landing.UI.Panel.FormSettingsPanel');
	    _this.setTitle(landing_loc.Loc.getMessage('LANDING_FORM_SETTINGS_PANEL_TITLE'));
	    _this.lsCache = new main_core.Cache.LocalStorageCache();
	    main_core.Dom.addClass(_this.layout, 'landing-ui-panel-form-settings');
	    _this.subscribe('onCancel', function () {
	      BX.onCustomEvent(babelHelpers.assertThisInitialized(_this), 'BX.Landing.Block:onFormSettingsClose', [_this.getCurrentBlock().id]);
	    });
	    _this.disableOverlay();
	    if (_this.isCrmFormPage()) {
	      var dictionary = landing_env.Env.getInstance().getOptions().formEditorData.dictionary;
	      var preparedSidebarButtons = dictionary.sidebarButtons.map(function (buttonOptions) {
	        return new landing_ui_button_sidebarbutton.SidebarButton(_objectSpread(_objectSpread({}, buttonOptions), {}, {
	          child: true
	        }));
	      });
	      _this.setSidebarButtons(preparedSidebarButtons);
	      var preparedPresets = dictionary.scenarios.map(function (presetOptions) {
	        return new landing_ui_panel_basepresetpanel.Preset(presetOptions);
	      });
	      _this.setPresets(preparedPresets);
	      var preparedPresetCategories = dictionary.scenarioCategories.map(function (categoryOptions) {
	        return new landing_ui_panel_basepresetpanel.PresetCategory(categoryOptions);
	      });
	      _this.setCategories(preparedPresetCategories);
	    } else {
	      main_core.Dom.append(_this.getBlockSettingsButton().render(), _this.getRightHeaderControls());
	    }
	    _this.subscribe('onCancel', _this.onCancelClick.bind(babelHelpers.assertThisInitialized(_this)));
	    main_core.Dom.append(_this.getExpertSwitcherLayout(), _this.layout);
	    return _this;
	  }
	  babelHelpers.createClass(FormSettingsPanel, [{
	    key: "getExpertSwitcherLayout",
	    value: function getExpertSwitcherLayout() {
	      var _this2 = this;
	      return this.cache.remember('switcherLayout', function () {
	        var onClick = function onClick() {
	          _this2.getExpertModeSwitcher().node.click();
	        };
	        return main_core.Tag.render(_templateObject || (_templateObject = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"landing-ui-expert-switcher\">\n\t\t\t\t\t", "\n\t\t\t\t\t<span onclick=\"", "\" class=\"landing-ui-expert-switcher-label\">\n\t\t\t\t\t\t", "\n\t\t\t\t\t</span>\n\t\t\t\t</div>\n\t\t\t"])), _this2.getExpertModeSwitcher().node, onClick, landing_loc.Loc.getMessage('LANDING_FORM_EXPERT_MODE_SWITCHER_LABEL'));
	      });
	    }
	  }, {
	    key: "getExpertModeSwitcher",
	    value: function getExpertModeSwitcher() {
	      var _this3 = this;
	      return this.cache.remember('expertModeSwitcher', function () {
	        var rootWindow = landing_pageobject.PageObject.getRootWindow();
	        var switcher = new rootWindow.BX.UI.Switcher({
	          checked: _this3.isExpertModeEnabled()
	        });
	        main_core.Dom.addClass(switcher.node, 'ui-switcher-size-sm ui-switcher-color-green');
	        main_core.Event.bind(switcher.node, 'click', _this3.onExpertSwitcherClick.bind(_this3));
	        return switcher;
	      });
	    }
	  }, {
	    key: "onExpertSwitcherClick",
	    value: function onExpertSwitcherClick() {
	      this.lsCache.set('formEditorExpertMode', this.getExpertModeSwitcher().isChecked());
	      this.onExpertModeChange();
	    }
	  }, {
	    key: "getCurrentPreset",
	    value: function getCurrentPreset() {
	      var _this$getFormOptions = this.getFormOptions(),
	        templateId = _this$getFormOptions.templateId;
	      var preset = this.getPresets().find(function (currentPreset) {
	        return currentPreset.options.id === templateId;
	      });
	      if (preset) {
	        return preset;
	      }
	      return this.getPresets().find(function (currentPreset) {
	        return currentPreset.options.id === 'expert';
	      });
	    }
	  }, {
	    key: "onExpertModeChange",
	    value: function onExpertModeChange() {
	      var _this4 = this;
	      var currentPreset = this.getCurrentPreset();
	      if (this.getExpertModeSwitcher().isChecked() && main_core.Type.isArrayFilled(currentPreset.options.expertModeItems)) {
	        this.clearSidebar();
	        this.getSidebarButtons().filter(function (button) {
	          return currentPreset.options.expertModeItems.includes(button.id);
	        }).forEach(function (button) {
	          if (!currentPreset.options.items.includes(button.id)) {
	            button.deactivate();
	          }
	          _this4.appendSidebarButton(button);
	        });
	      } else {
	        var currentSidebarButton = this.getSidebarButtons().find(function (button) {
	          return button.isActive();
	        });
	        var buttons = this.getSidebarButtons().filter(function (button) {
	          return currentPreset.options.items.includes(button.id);
	        });
	        this.clearSidebar();
	        buttons.forEach(function (button) {
	          _this4.appendSidebarButton(button);
	        });
	        if (currentSidebarButton && !currentPreset.options.items.includes(currentSidebarButton.id)) {
	          var defaultSection = function () {
	            if (main_core.Type.isStringFilled(currentPreset.options.defaultSection)) {
	              return currentPreset.options.defaultSection;
	            }
	            return 'fields';
	          }();
	          var defaultSectionButton = this.getSidebarButtons().find(function (button) {
	            return button.id === defaultSection;
	          });
	          if (defaultSectionButton) {
	            defaultSectionButton.getLayout().click();
	          }
	        }
	      }
	    }
	  }, {
	    key: "isExpertModeEnabled",
	    value: function isExpertModeEnabled() {
	      return this.lsCache.get('formEditorExpertMode', false);
	    } // eslint-disable-next-line class-methods-use-this
	  }, {
	    key: "isCrmFormPage",
	    value: function isCrmFormPage() {
	      return landing_env.Env.getInstance().getOptions().specialType === 'crm_forms';
	    }
	  }, {
	    key: "getFormDesignButton",
	    value: function getFormDesignButton() {
	      var _this5 = this;
	      return this.cache.remember('formDesignButton', function () {
	        return new ui_buttons.Button({
	          text: landing_loc.Loc.getMessage('LANDING_FORM_DESIGN_BUTTON'),
	          color: ui_buttons.Button.Color.LIGHT_BORDER,
	          round: true,
	          className: 'landing-ui-panel-top-button',
	          onclick: _this5.onFormDesignButtonClick.bind(_this5)
	        });
	      });
	    }
	  }, {
	    key: "getBlockSettingsButton",
	    value: function getBlockSettingsButton() {
	      var _this6 = this;
	      return this.cache.remember('blockSettingsButton', function () {
	        return new ui_buttons.Button({
	          text: landing_loc.Loc.getMessage('LANDING_FORM_SETTINGS_BLOCK_SETTINGS_BUTTON_TEXT'),
	          color: ui_buttons.Button.Color.LIGHT_BORDER,
	          round: true,
	          className: 'landing-ui-panel-top-button',
	          onclick: _this6.onBlockSettingsButtonClick.bind(_this6)
	        });
	      });
	    }
	  }, {
	    key: "onBlockSettingsButtonClick",
	    value: function onBlockSettingsButtonClick() {
	      var _this7 = this;
	      if (this.getCurrentBlock()) {
	        this.hide().then(function () {
	          _this7.getCurrentBlock().showContentPanel();
	        });
	      }
	    }
	  }, {
	    key: "onFormDesignButtonClick",
	    value: function onFormDesignButtonClick() {
	      if (this.getCurrentBlock()) {
	        this.getCurrentBlock().onFormDesignClick();
	      }
	    }
	  }, {
	    key: "getLoader",
	    value: function getLoader() {
	      var _this8 = this;
	      return this.cache.remember('loader', function () {
	        return new main_loader.Loader({
	          target: _this8.body
	        });
	      });
	    }
	  }, {
	    key: "showLoader",
	    value: function showLoader() {
	      main_core.Dom.addClass(this.layout, 'landing-ui-panel-state-content-load');
	      void this.getLoader().show();
	      main_core.Dom.hide(this.sidebar);
	      main_core.Dom.hide(this.content);
	      main_core.Dom.hide(this.getExpertSwitcherLayout());
	    }
	  }, {
	    key: "hideLoader",
	    value: function hideLoader() {
	      main_core.Dom.removeClass(this.layout, 'landing-ui-panel-state-content-load');
	      this.getLoader().hide();
	      main_core.Dom.show(this.sidebar);
	      main_core.Dom.show(this.content);
	      if (main_core.Type.isArrayFilled(this.getCurrentPreset().options.expertModeItems)) {
	        main_core.Dom.show(this.getExpertSwitcherLayout());
	      }
	    }
	  }, {
	    key: "showContentLoader",
	    value: function showContentLoader() {
	      main_core.Dom.addClass(this.layout, 'landing-ui-panel-state-body-load');
	      babelHelpers.get(babelHelpers.getPrototypeOf(FormSettingsPanel.prototype), "showContentLoader", this).call(this);
	    }
	  }, {
	    key: "hideContentLoader",
	    value: function hideContentLoader() {
	      main_core.Dom.removeClass(this.layout, 'landing-ui-panel-state-body-load');
	      babelHelpers.get(babelHelpers.getPrototypeOf(FormSettingsPanel.prototype), "hideContentLoader", this).call(this);
	    }
	  }, {
	    key: "load",
	    value: function load() {
	      var _this9 = this;
	      var options = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};
	      if (options.showWithOptions) {
	        var editorData = landing_env.Env.getInstance().getOptions().formEditorData;
	        var dictionary = editorData.dictionary;
	        var preparedSidebarButtons = dictionary.sidebarButtons.map(function (buttonOptions) {
	          return new landing_ui_button_sidebarbutton.SidebarButton(_objectSpread(_objectSpread({}, buttonOptions), {}, {
	            child: true
	          }));
	        });
	        this.setSidebarButtons(preparedSidebarButtons);
	        var preparedPresets = dictionary.scenarios.map(function (presetOptions) {
	          return new landing_ui_panel_basepresetpanel.Preset(presetOptions);
	        });
	        this.setPresets(preparedPresets);
	        var preparedPresetCategories = dictionary.scenarioCategories.map(function (categoryOptions) {
	          return new landing_ui_panel_basepresetpanel.PresetCategory(categoryOptions);
	        });
	        this.setCategories(preparedPresetCategories);
	        this.setCrmFields(editorData.crmFields);
	        this.setCrmCompanies(editorData.crmCompanies);
	        this.setCrmCategories(editorData.crmCategories);
	        this.setAgreements(editorData.agreements);
	        var currentOptions = main_core.Runtime.clone(editorData.formOptions);
	        if (currentOptions.agreements.use !== true) {
	          currentOptions.agreements.use = true;
	          currentOptions.data.agreements = [];
	        }
	        this.setFormOptions(currentOptions);
	        this.setFormDictionary(editorData.dictionary);
	        return Promise.resolve();
	      }
	      var crmData = landing_backend.Backend.getInstance().batch('Form::getCrmFields', {
	        crmFields: {
	          action: 'Form::getCrmFields',
	          data: null
	        },
	        crmCompanies: {
	          action: 'Form::getCrmCompanies',
	          data: null
	        },
	        crmCategories: {
	          action: 'Form::getCrmCategories',
	          data: null
	        },
	        agreements: {
	          action: 'Form::getAgreements',
	          data: null
	        }
	      }).then(function (result) {
	        _this9.setCrmFields(result.crmFields.result);
	        _this9.setCrmCompanies(result.crmCompanies.result);
	        _this9.setCrmCategories(result.crmCategories.result);
	        _this9.setAgreements(result.agreements.result);
	      });
	      var formOptions = crm_form_client.FormClient.getInstance().getOptions(this.getCurrentFormId()).then(function (options) {
	        var currentOptions = main_core.Runtime.clone(options);
	        if (currentOptions.agreements.use !== true) {
	          currentOptions.agreements.use = true;
	          currentOptions.data.agreements = [];
	        }
	        _this9.setFormOptions(currentOptions);
	      });
	      var formDictionary = crm_form_client.FormClient.getInstance().getDictionary().then(function (dictionary) {
	        _this9.setFormDictionary(dictionary);
	        var preparedSidebarButtons = dictionary.sidebarButtons.map(function (buttonOptions) {
	          return new landing_ui_button_sidebarbutton.SidebarButton(_objectSpread(_objectSpread({}, buttonOptions), {}, {
	            child: true
	          }));
	        });
	        _this9.setSidebarButtons(preparedSidebarButtons);
	        var preparedPresets = dictionary.scenarios.map(function (presetOptions) {
	          return new landing_ui_panel_basepresetpanel.Preset(presetOptions);
	        });
	        _this9.setPresets(preparedPresets);
	        var preparedPresetCategories = dictionary.scenarioCategories.map(function (categoryOptions) {
	          return new landing_ui_panel_basepresetpanel.PresetCategory(categoryOptions);
	        });
	        _this9.setCategories(preparedPresetCategories);
	      });
	      return Promise.all([crmData, formOptions, formDictionary]);
	    }
	  }, {
	    key: "setAgreements",
	    value: function setAgreements(agreements) {
	      this.cache.set('agreements', main_core.Runtime.orderBy(agreements, ['id'], ['asc']));
	    }
	  }, {
	    key: "getAgreements",
	    value: function getAgreements() {
	      return this.cache.get('agreements');
	    }
	  }, {
	    key: "isLeadEnabled",
	    value: function isLeadEnabled() {
	      return this.getFormDictionary().document.lead.enabled;
	    }
	  }, {
	    key: "setCurrentBlock",
	    value: function setCurrentBlock(block) {
	      this.cache.set('currentBlock', block);
	    }
	  }, {
	    key: "getCurrentBlock",
	    value: function getCurrentBlock() {
	      return this.cache.get('currentBlock');
	    }
	  }, {
	    key: "getSaveOriginalFileNameAlert",
	    value: function getSaveOriginalFileNameAlert() {
	      return this.cache.remember('saveOriginalFileNameAlert', function () {
	        var alert = new ui_alerts.Alert({
	          text: landing_loc.Loc.getMessage('LANDING_CRM_FORM_MAIN_OPTION_WARNING'),
	          color: ui_alerts.AlertColor.WARNING
	        });
	        return alert.render();
	      });
	    }
	  }, {
	    key: "show",
	    value: function show() {
	      var _this10 = this;
	      var options = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {
	        formOptions: {}
	      };
	      if (!this.layout.parentNode) {
	        this.enableToggleMode();
	      }
	      if (!this.isFormCreated()) {
	        this.disableTransparentMode();
	      }
	      var _Env$getInstance$getO = landing_env.Env.getInstance().getOptions(),
	        mainOptions = _Env$getInstance$getO.mainOptions;
	      if (mainOptions.saveOriginalFileName === false) {
	        this.prependContent(this.getSaveOriginalFileNameAlert());
	        var closeButtonTop = main_core.Text.toNumber(main_core.Dom.style(this.closeButton.getLayout(), 'top'));
	        var alertHeight = this.getSaveOriginalFileNameAlert().getBoundingClientRect().height;
	        main_core.Dom.style(this.closeButton.getLayout(), 'top', "".concat(closeButtonTop + alertHeight, "px"));
	      }
	      this.setCurrentBlock(options.block);
	      this.setCurrentFormId(options.formId);
	      this.setCurrentFormInstanceId(options.instanceId);
	      this.showLoader();
	      this.load(options).then(function () {
	        _this10.hideLoader();
	        var formOptions = _this10.getFormOptions();
	        if (main_core.Type.isPlainObject(options.formOptions)) {
	          var _formOptions = main_core.Runtime.merge(_this10.getFormOptions(), options.formOptions);
	          _this10.setFormOptions(_formOptions);
	        }
	        if (options.state === 'presets') {
	          var presetFromRequest = _this10.getPresetIdFromRequest();
	          var preset = false;
	          if (presetFromRequest) {
	            preset = _this10.getPresets().find(function (item) {
	              return item.options.id === presetFromRequest;
	            });
	          }
	          if (preset) {
	            _this10.applyPreset(preset);
	          } else {
	            _this10.onPresetFieldClick();
	            _this10.activatePreset(formOptions.templateId);
	          }
	        } else {
	          var _preset = _this10.getPresets().find(function (item) {
	            return item.options.id === formOptions.templateId;
	          });
	          if (!_preset) {
	            _preset = _this10.getPresets().find(function (item) {
	              return item.options.id === 'expert';
	            });
	          }
	          if (_this10.isFormCreated()) {
	            _this10.applyPreset(_preset);
	            _this10.onPresetFieldClick();
	          } else {
	            _this10.applyPreset(_preset, true);
	          }
	        }
	        _this10.setInitialFormOptions(main_core.Runtime.clone(_this10.getFormOptions()));
	        if (!_this10.isFormCreated()) {
	          _this10.onExpertModeChange();
	        }
	      })["catch"](function (error) {
	        if (main_core.Type.isArrayFilled(error)) {
	          var accessDeniedCode = 510;
	          var isAccessDenied = error.some(function (errorItem) {
	            return String(errorItem.code) === String(accessDeniedCode);
	          });
	          if (isAccessDenied) {
	            _this10.getLoader().hide();
	            main_core.Dom.show(_this10.sidebar);
	            main_core.Dom.show(_this10.content);
	            main_core.Dom.hide(_this10.footer);
	            main_core.Dom.append(_this10.getAccessError(), _this10.content);
	          }
	        }
	        console.error(error);
	      });
	      var editorWindow = landing_pageobject.PageObject.getEditorWindow();
	      main_core.Dom.addClass(editorWindow.document.body, 'landing-ui-hide-action-panels-form');
	      void landing_ui_panel_stylepanel.StylePanel.getInstance().hide();
	      this.disableHistory();
	      return babelHelpers.get(babelHelpers.getPrototypeOf(FormSettingsPanel.prototype), "show", this).call(this, options).then(function () {
	        setTimeout(function () {
	          var y = _this10.getCurrentBlock().node.offsetTop;
	          landing_pageobject.PageObject.getEditorWindow().scrollTo(0, y);
	        }, 300);
	        BX.onCustomEvent(_this10, 'BX.Landing.Block:onFormSettingsOpen', [_this10.getCurrentBlock().id]);
	        return Promise.resolve(true);
	      });
	    }
	  }, {
	    key: "getHistoryHint",
	    value: function getHistoryHint() {
	      return this.cache.remember('historyHint', function () {
	        var layout = main_core.Tag.render(_templateObject2 || (_templateObject2 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<span \n\t\t\t\t\tclass=\"landing-ui-history-hint\"\n\t\t\t\t\tdata-hint=\"", "\"\n\t\t\t\t\tdata-hint-no-icon\n\t\t\t\t></span>\n\t\t\t"])), main_core.Text.encode(landing_loc.Loc.getMessage('LANDING_FORM_HISTORY_DISABLED_HINT')));
	        var rootWindow = landing_pageobject.PageObject.getRootWindow();
	        rootWindow.BX.UI.Hint.initNode(layout);
	        return layout;
	      });
	    }
	  }, {
	    key: "disableHistory",
	    value: function disableHistory() {
	      var rootWindow = landing_pageobject.PageObject.getRootWindow();
	      var TopPanel = rootWindow.BX.Landing.UI.Panel.Top;
	      if (TopPanel) {
	        var _TopPanel$getInstance = TopPanel.getInstance(),
	          undoButton = _TopPanel$getInstance.undoButton,
	          redoButton = _TopPanel$getInstance.redoButton;
	        main_core.Dom.addClass(undoButton, 'landing-ui-disabled-from-form');
	        main_core.Dom.addClass(redoButton, 'landing-ui-disabled-from-form');
	        main_core.Dom.append(this.getHistoryHint(), undoButton.parentElement);
	      }
	    }
	  }, {
	    key: "enableHistory",
	    value: function enableHistory() {
	      var rootWindow = landing_pageobject.PageObject.getRootWindow();
	      var TopPanel = rootWindow.BX.Landing.UI.Panel.Top;
	      if (TopPanel) {
	        var _TopPanel$getInstance2 = TopPanel.getInstance(),
	          undoButton = _TopPanel$getInstance2.undoButton,
	          redoButton = _TopPanel$getInstance2.redoButton;
	        main_core.Dom.removeClass(undoButton, 'landing-ui-disabled-from-form');
	        main_core.Dom.removeClass(redoButton, 'landing-ui-disabled-from-form');
	        main_core.Dom.remove(this.getHistoryHint());
	      }
	    }
	  }, {
	    key: "getAccessError",
	    value: function getAccessError() {
	      return this.cache.remember('accessErrorMessage', function () {
	        return main_core.Tag.render(_templateObject3 || (_templateObject3 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"landing-ui-access-error-message\">\n\t\t\t\t\t<div class=\"landing-ui-access-error-message-text\">\n\t\t\t\t\t\t", "\n\t\t\t\t\t</div>\n\t\t\t\t</div>\n\t\t\t"])), landing_loc.Loc.getMessage('LANDING_CRM_ACCESS_ERROR_MESSAGE'));
	      });
	    } // eslint-disable-next-line class-methods-use-this
	  }, {
	    key: "getPresetIdFromRequest",
	    value: function getPresetIdFromRequest() {
	      var uri = new main_core.Uri(window.top.location.href);
	      return uri.getQueryParam('preset');
	    } // eslint-disable-next-line class-methods-use-this
	  }, {
	    key: "isFormCreated",
	    value: function isFormCreated() {
	      var rootWindow = landing_pageobject.PageObject.getRootWindow();
	      var uri = new main_core.Uri(rootWindow.location.href);
	      return main_core.Text.toBoolean(uri.getQueryParam('formCreated'));
	    }
	  }, {
	    key: "setCurrentFormId",
	    value: function setCurrentFormId(formId) {
	      this.cache.set('currentFormId', main_core.Text.toNumber(formId));
	    }
	  }, {
	    key: "getCurrentFormId",
	    value: function getCurrentFormId() {
	      return this.cache.get('currentFormId');
	    }
	  }, {
	    key: "setCurrentFormInstanceId",
	    value: function setCurrentFormInstanceId(formId) {
	      this.cache.set('currentFormInstanceId', formId);
	    }
	  }, {
	    key: "getCurrentFormInstanceId",
	    value: function getCurrentFormInstanceId() {
	      return this.cache.get('currentFormInstanceId');
	    }
	  }, {
	    key: "setCrmFields",
	    value: function setCrmFields(fields) {
	      this.cache.set('fields', fields);
	    }
	  }, {
	    key: "getCrmFields",
	    value: function getCrmFields() {
	      return this.cache.get('fields') || {};
	    }
	  }, {
	    key: "setCrmCompanies",
	    value: function setCrmCompanies(companies) {
	      this.cache.set('companies', companies);
	    }
	  }, {
	    key: "getCrmCompanies",
	    value: function getCrmCompanies() {
	      return this.cache.get('companies') || [];
	    }
	  }, {
	    key: "setCrmCategories",
	    value: function setCrmCategories(categories) {
	      this.cache.set('crmCategories', categories);
	    }
	  }, {
	    key: "getCrmCategories",
	    value: function getCrmCategories() {
	      return this.cache.get('crmCategories') || [];
	    }
	  }, {
	    key: "setFormOptions",
	    value: function setFormOptions(options) {
	      this.cache.set('formOptions', options);
	    }
	  }, {
	    key: "getFormOptions",
	    value: function getFormOptions() {
	      return main_core.Runtime.clone(this.cache.get('formOptions') || {});
	    }
	  }, {
	    key: "setFormDictionary",
	    value: function setFormDictionary(dictionary) {
	      this.cache.set('formDictionary', dictionary);
	    }
	  }, {
	    key: "getFormDictionary",
	    value: function getFormDictionary() {
	      return this.cache.get('formDictionary') || {};
	    }
	  }, {
	    key: "setInitialFormOptions",
	    value: function setInitialFormOptions(options) {
	      this.cache.set('initialFormOptions', main_core.Runtime.clone(options));
	    }
	  }, {
	    key: "getInitialFormOptions",
	    value: function getInitialFormOptions() {
	      return this.cache.get('initialFormOptions');
	    } // eslint-disable-next-line
	  }, {
	    key: "getCrmForm",
	    value: function getCrmForm() {
	      var _this11 = this;
	      var formApp = main_core.Reflection.getClass('b24form.App');
	      if (formApp) {
	        if (this.getCurrentFormInstanceId()) {
	          return formApp.get(this.getCurrentFormInstanceId());
	        }
	        var tmpIndex = -1;
	        var currentFormIndex = babelHelpers.toConsumableArray(this.getCurrentBlock().node.parentElement.childNodes).reduce(function (acc, item) {
	          if (main_core.Dom.attr(item, 'data-subtype') === 'form') {
	            tmpIndex += 1;
	            if (item === _this11.getCurrentBlock().node) {
	              return tmpIndex;
	            }
	          }
	          return acc;
	        }, 0);
	        return formApp.list()[currentFormIndex];
	      }
	      return null;
	    }
	  }, {
	    key: "onChange",
	    value: function onChange(event) {
	      var _this12 = this;
	      var eventData = event.getData();
	      var eventTargetValue = event.getTarget().getValue();
	      Promise.resolve(eventTargetValue).then(function (value) {
	        if (eventData.skipPrepare) {
	          var formOptions = _this12.getFormOptions();
	          if (Reflect.has(value, 'presetFields') || Reflect.has(value, 'document') || Reflect.has(value, 'result')) {
	            var additionalValue = {};
	            if (Reflect.has(value, 'document')) {
	              additionalValue.payment = value.document.payment;
	              delete value.document.payment;
	            }
	            return _objectSpread(_objectSpread(_objectSpread({}, formOptions), value), additionalValue);
	          }
	          if (Reflect.has(value, 'embedding') || Reflect.has(value, 'callback') || Reflect.has(value, 'whatsapp') || Reflect.has(value, 'name') && Reflect.has(value, 'data') && Reflect.has(value.data, 'useSign')) {
	            var mergedOptions = main_core.Runtime.merge(formOptions, value);
	            if (Reflect.has(value, 'responsible')) {
	              mergedOptions.responsible.users = value.responsible.users;
	            }
	            return mergedOptions;
	          }
	          if (Reflect.has(value, 'recaptcha')) {
	            var _value$recaptcha = value.recaptcha,
	              _key = _value$recaptcha.key,
	              secret = _value$recaptcha.secret;
	            delete value.recaptcha.key;
	            delete value.recaptcha.secret;
	            var captcha = {};
	            if (!main_core.Type.isNil(_key)) {
	              captcha.key = _key;
	            }
	            if (!main_core.Type.isNil(secret)) {
	              captcha.secret = secret;
	            }
	            return _objectSpread(_objectSpread({}, formOptions), {}, {
	              captcha: _objectSpread(_objectSpread({}, formOptions.captcha), captcha),
	              data: _objectSpread(_objectSpread({}, formOptions.data), value)
	            });
	          }
	          return _objectSpread(_objectSpread({}, formOptions), {}, {
	            data: _objectSpread(_objectSpread({}, formOptions.data), value)
	          });
	        }
	        return crm_form_client.FormClient.getInstance().prepareOptions(_this12.getFormOptions(), value).then(function (result) {
	          if (value.agreements) {
	            result.data = main_core.Runtime.merge(result.data, value);
	          }
	          if (value.integration) {
	            result.integration = value.integration;
	          }
	          if (value.fields) {
	            result.data.fields = result.data.fields.map(function (field, index) {
	              return main_core.Runtime.merge(field, value.fields[index]);
	            });
	          }
	          return result;
	        });
	      }).then(function (result) {
	        BX.Landing.UI.Panel.Top.getInstance().setFormName(result.name);
	        _this12.setFormOptions(result);
	        _this12.getCrmForm().adjust(main_core.Runtime.clone(result.data));
	      });
	    }
	  }, {
	    key: "getPersonalizationVariables",
	    value: function getPersonalizationVariables() {
	      var _this13 = this;
	      return this.cache.remember('personalizationVariables', function () {
	        return _this13.getFormDictionary().personalization.list.map(function (item) {
	          return {
	            name: item.name,
	            value: item.id
	          };
	        });
	      });
	    }
	  }, {
	    key: "getDefaultValuesVariables",
	    value: function getDefaultValuesVariables() {
	      var _this14 = this;
	      return this.cache.remember('personalizationVariables', function () {
	        var _this14$getFormDictio = _this14.getFormDictionary(),
	          properties = _this14$getFormDictio.properties;
	        if (main_core.Type.isPlainObject(properties) && main_core.Type.isArrayFilled(properties.list)) {
	          return properties.list.map(function (item) {
	            return {
	              name: item.name,
	              value: item.id
	            };
	          });
	        }
	        return [];
	      });
	    }
	  }, {
	    key: "getContent",
	    value: function getContent(id) {
	      var _this15 = this;
	      var currentButton = this.getSidebarButtons().find(function (button) {
	        return id === button.options.id;
	      });
	      var extension = currentButton.options.data.extension;
	      var contentExtension = this.cache.remember(extension, function () {
	        var rootWindow = landing_pageobject.PageObject.getRootWindow();
	        return rootWindow.BX.Runtime.loadExtension(extension).then(function (exports) {
	          return exports["default"];
	        });
	      });
	      return contentExtension.then(function (ContentWrapperClass) {
	        if (main_core.Type.isFunction(ContentWrapperClass)) {
	          return new ContentWrapperClass({
	            formOptions: _this15.getFormOptions(),
	            dictionary: _this15.getFormDictionary(),
	            crmFields: _this15.getCrmFields(),
	            companies: _this15.getCrmCompanies(),
	            categories: _this15.getCrmCategories(),
	            agreements: _this15.getAgreements(),
	            isLeadEnabled: _this15.isLeadEnabled(),
	            form: _this15.getCrmForm()
	          });
	        }
	        return null;
	      });
	    }
	  }, {
	    key: "onPresetClick",
	    value: function onPresetClick(event) {
	      if (event.getTarget().options.openable) {
	        this.disableTransparentMode();
	      }
	      var uri = new main_core.Uri(window.top.location.toString());
	      uri.removeQueryParam('formCreated');
	      uri.removeQueryParam('preset');
	      window.top.history.replaceState(null, document.title, uri.toString());
	      this.applyPreset(event.getTarget());
	    }
	  }, {
	    key: "getCheckActionConfirm",
	    value: function getCheckActionConfirm() {
	      return this.cache.remember('checkActionConfirm', function () {
	        var rootWindow = landing_pageobject.PageObject.getRootWindow();
	        return new rootWindow.BX.UI.Dialogs.MessageBox({
	          buttons: ui_dialogs_messagebox.MessageBoxButtons.OK_CANCEL
	        });
	      });
	    }
	  }, {
	    key: "applyPreset",
	    value: function applyPreset(preset) {
	      var _this16 = this;
	      var skipOptions = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : false;
	      var lastPreset = this.getPresets().find(function (currentPreset) {
	        return main_core.Dom.hasClass(currentPreset.getLayout(), 'landing-ui-panel-preset-active');
	      });
	      this.getPresets().forEach(function (currentPreset) {
	        currentPreset.deactivate();
	      });
	      if (!skipOptions) {
	        var runAction = function () {
	          if (main_core.Type.isArrayFilled(preset.options.actions)) {
	            return Promise.all(preset.options.actions.map(function (action) {
	              if (action.id === 'showTour') {
	                var rootWindow = landing_pageobject.PageObject.getRootWindow();
	                var guide = new rootWindow.BX.UI.Tour.Guide({
	                  onEvents: false,
	                  steps: action.data.steps
	                });
	                guide.start();
	              }
	              if (action.id === 'showHelp') {
	                if (window.top.BX.Helper) {
	                  window.top.BX.Helper.show(action.data.href);
	                }
	              }
	              if (action.id === 'check') {
	                return crm_form_client.FormClient.getInstance().check({
	                  templateId: preset.options.id
	                }).then(function (result) {
	                  if (result.success === false) {
	                    var checkActionConfirm = _this16.getCheckActionConfirm();
	                    checkActionConfirm.setTitle(result.message.title);
	                    checkActionConfirm.setMessage(result.message.description);
	                    checkActionConfirm.setOkCaption(result.message.confirmButton);
	                    checkActionConfirm.setCancelCaption(result.message.cancelButton);
	                    return new Promise(function (resolve) {
	                      checkActionConfirm.setOkCallback(function () {
	                        checkActionConfirm.getOkButton().setDisabled(false);
	                        checkActionConfirm.getCancelButton().setDisabled(false);
	                        checkActionConfirm.close();
	                        resolve(true);
	                      });
	                      checkActionConfirm.setCancelCallback(function () {
	                        checkActionConfirm.getOkButton().setDisabled(false);
	                        checkActionConfirm.getCancelButton().setDisabled(false);
	                        checkActionConfirm.close();
	                        resolve(false);
	                      });
	                      checkActionConfirm.show();
	                    });
	                  }
	                  return Promise.resolve(true);
	                });
	              }
	              return Promise.resolve();
	            }));
	          }
	          return Promise.resolve();
	        }();
	        if (preset.options.openable) {
	          this.showLoader();
	          void runAction.then(function (actions) {
	            var actionsResult = function () {
	              if (main_core.Type.isArrayFilled(preset.options.actions)) {
	                return preset.options.actions.reduce(function (acc, item, index) {
	                  return _objectSpread(_objectSpread({}, acc), {}, babelHelpers.defineProperty({}, item.id, actions[index]));
	                }, {});
	              }
	              return {};
	            }();
	            if (Reflect.has(actionsResult, 'check') && actionsResult.check === true || !Reflect.has(actionsResult, 'check')) {
	              _this16.getPresets().forEach(function (currentPreset) {
	                currentPreset.deactivate();
	              });
	              preset.activate();
	              crm_form_client.FormClient.getInstance().prepareOptions(_this16.getFormOptions(), {
	                templateId: preset.options.id
	              }).then(function (result) {
	                return landing_backend.Backend.getInstance().action('Form::getCrmFields').then(function (crmFields) {
	                  _this16.setCrmFields(crmFields);
	                  landing_ui_panel_fieldspanel.FieldsPanel.getInstance().setCrmFields(crmFields);
	                  return result;
	                });
	              }).then(function (result) {
	                BX.Landing.UI.Panel.Top.getInstance().setFormName(result.name);
	                _this16.setFormOptions(_objectSpread(_objectSpread({}, result), {}, {
	                  templateId: preset.options.id
	                }));
	                _this16.getCrmForm().adjust(main_core.Runtime.clone(result.data));
	                if (_this16.isFormCreated()) {
	                  _this16.onPresetFieldClick();
	                  _this16.activatePreset(preset.options.id);
	                } else {
	                  babelHelpers.get(babelHelpers.getPrototypeOf(FormSettingsPanel.prototype), "applyPreset", _this16).call(_this16, preset);
	                  if (main_core.Type.isArrayFilled(preset.options.expertModeItems)) {
	                    main_core.Dom.show(_this16.getExpertSwitcherLayout());
	                    _this16.onExpertModeChange();
	                  } else {
	                    main_core.Dom.hide(_this16.getExpertSwitcherLayout());
	                  }
	                }
	                _this16.hideLoader();
	              });
	            } else {
	              _this16.hideLoader();
	              _this16.enableTransparentMode();
	              if (lastPreset) {
	                lastPreset.activate();
	                preset.deactivate();
	              }
	            }
	          });
	        }
	      } else {
	        if (preset.options.openable) {
	          babelHelpers.get(babelHelpers.getPrototypeOf(FormSettingsPanel.prototype), "applyPreset", this).call(this, preset);
	          if (main_core.Type.isArrayFilled(preset.options.expertModeItems)) {
	            main_core.Dom.show(this.getExpertSwitcherLayout());
	            this.onExpertModeChange();
	          } else {
	            main_core.Dom.hide(this.getExpertSwitcherLayout());
	          }
	          this.hideLoader();
	        }
	        preset.activate();
	      }
	    }
	  }, {
	    key: "getFormNode",
	    value: function getFormNode() {
	      var _this17 = this;
	      return this.cache.remember('formNode', function () {
	        return _this17.getCurrentBlock().node.querySelector('[data-b24form-use-style]');
	      });
	    }
	  }, {
	    key: "useBlockDesign",
	    value: function useBlockDesign() {
	      var _this18 = this;
	      return this.cache.remember('useBlockDesign', function () {
	        return main_core.Text.toBoolean(main_core.Dom.attr(_this18.getFormNode(), 'data-b24form-use-style'));
	      });
	    }
	  }, {
	    key: "getCurrentCrmEntityName",
	    value: function getCurrentCrmEntityName() {
	      var scheme = this.getFormOptions().document.scheme;
	      var schemeItem = this.getFormDictionary().document.schemes.find(function (item) {
	        return String(scheme) === String(item.id);
	      });
	      return schemeItem.name;
	    }
	  }, {
	    key: "getNotSynchronizedFields",
	    value: function getNotSynchronizedFields() {
	      return crm_form_client.FormClient.getInstance().checkFields(this.getFormOptions()).then(function (result) {
	        return result;
	      });
	    }
	  }, {
	    key: "showSynchronizationPopup",
	    value: function showSynchronizationPopup(notSynchronizedFields) {
	      var _this19 = this;
	      return new Promise(function (resolve) {
	        var onOk = function onOk(messageBox) {
	          messageBox.close();
	          resolve(true);
	        };
	        var onCancel = function onCancel(messageBox) {
	          messageBox.close();
	          resolve(false);
	        };
	        var messageDescription = function () {
	          var entityName = landing_loc.Loc.getMessage('LANDING_SYNCHRONIZATION_POPUP_ENTITY_TEMPLATE').replace('{entityName}', main_core.Text.encode(_this19.getCurrentCrmEntityName()));
	          return landing_loc.Loc.getMessage('LANDING_SYNCHRONIZATION_POPUP_DESCRIPTION').replace('{entityName}', main_core.Text.encode(entityName));
	        }();
	        var messageText = function () {
	          var fields = babelHelpers.toConsumableArray(notSynchronizedFields).map(function (field) {
	            return landing_loc.Loc.getMessage('LANDING_SYNCHRONIZATION_POPUP_FIELD_TEMPLATE').replace('{fieldName}', main_core.Text.encode(field));
	          });
	          if (notSynchronizedFields.length > 1) {
	            var lastField = fields.pop();
	            return landing_loc.Loc.getMessage('LANDING_SYNCHRONIZATION_POPUP_TEXT').replace('{fieldsList}', fields.join(', ')).replace('{lastField}', lastField);
	          }
	          return landing_loc.Loc.getMessage('LANDING_SYNCHRONIZATION_POPUP_TEXT_1').replace('{field}', fields.join(', '));
	        }();
	        window.top.BX.UI.Dialogs.MessageBox.confirm("".concat(messageDescription, "<br><br>").concat(messageText), landing_loc.Loc.getMessage('LANDING_SYNCHRONIZATION_POPUP_TITLE'), onOk, landing_loc.Loc.getMessage('LANDING_SYNCHRONIZATION_POPUP_OK_BUTTON_LABEL'), onCancel);
	      });
	    }
	  }, {
	    key: "showSynchronizationErrorPopup",
	    value: function showSynchronizationErrorPopup(errors) {
	      var message = errors.reduce(function (acc, item) {
	        return "".concat(acc, "\n\n").concat(item);
	      }, '');
	      window.top.BX.UI.Dialogs.MessageBox.alert(message);
	    }
	  }, {
	    key: "getErrorAlert",
	    value: function getErrorAlert() {
	      return this.cache.remember('errorAlert', function () {
	        var rootWindow = landing_pageobject.PageObject.getRootWindow();
	        return new rootWindow.BX.UI.Dialogs.MessageBox({
	          title: landing_loc.Loc.getMessage('LANDING_FORM_SAVE_ERROR_ALERT_TITLE'),
	          buttons: ui_dialogs_messagebox.MessageBoxButtons.OK,
	          popupOptions: {
	            maxHeight: 310
	          }
	        });
	      });
	    }
	  }, {
	    key: "onSaveClick",
	    value: function onSaveClick() {
	      var _this20 = this;
	      var dictionary = this.getFormDictionary();
	      BX.onCustomEvent(this, 'BX.Landing.Block:onFormSave', [this.getCurrentBlock().id]);
	      if (main_core.Type.isPlainObject(dictionary.permissions) && main_core.Type.isPlainObject(dictionary.permissions.form) && dictionary.permissions.form.edit === false) {
	        var rootWindow = landing_pageobject.PageObject.getRootWindow();
	        rootWindow.BX.UI.Dialogs.MessageBox.alert(landing_loc.Loc.getMessage('LANDING_FORM_SAVE_PERMISSION_DENIED'));
	        return;
	      }
	      main_core.Dom.addClass(this.getSaveButton().layout, 'ui-btn-wait');
	      this.getNotSynchronizedFields().then(function (result) {
	        if (main_core.Type.isPlainObject(result.sync)) {
	          if (main_core.Type.isArrayFilled(result.sync.errors)) {
	            _this20.showSynchronizationErrorPopup(result.sync.errors);
	            return false;
	          }
	          if (main_core.Type.isArrayFilled(result.sync.fields)) {
	            var fieldLabels = result.sync.fields.map(function (field) {
	              return field.label;
	            });
	            return _this20.showSynchronizationPopup(fieldLabels);
	          }
	        }
	        return true;
	      }).then(function (isConfirmed) {
	        if (isConfirmed) {
	          var uri = new main_core.Uri(window.top.location.toString());
	          uri.removeQueryParam('formCreated');
	          window.top.history.replaceState(null, document.title, uri.toString());
	          var initialOptions = _this20.getInitialFormOptions();
	          var currentOptions = _this20.getFormOptions();
	          var options = function () {
	            if (!_this20.isCrmFormPage()) {
	              var clonedOptions = main_core.Runtime.clone(currentOptions);
	              clonedOptions.data.design = main_core.Runtime.clone(initialOptions.data.design);
	              return clonedOptions;
	            }
	            return currentOptions;
	          }();
	          if (options.data.recaptcha.use && !_this20.getFormDictionary().captcha.hasKeys && !options.captcha.hasDefaults) {
	            options.data.recaptcha.use = false;
	            var _rootWindow = landing_pageobject.PageObject.getRootWindow();
	            var alert = new _rootWindow.BX.UI.Dialogs.MessageBox({
	              title: landing_loc.Loc.getMessage('LANDING_FORM_SAVE_CAPTCHA_ALERT_TITLE'),
	              message: landing_loc.Loc.getMessage('LANDING_FORM_SAVE_CAPTCHA_ALERT_TEXT_2'),
	              buttons: ui_dialogs_messagebox.MessageBoxButtons.OK,
	              onOk: function onOk() {
	                alert.close();
	                main_core.Dom.removeClass(_this20.getSaveButton().layout, 'ui-btn-wait');
	              }
	            });
	            alert.show();
	          }
	          void crm_form_client.FormClient.getInstance().saveOptions(options).then(function (result) {
	            BX.onCustomEvent(_this20, 'BX.Landing.Block:onAfterFormSave', [_this20.getCurrentBlock().id]);
	            _this20.setFormOptions(result);
	            _this20.setInitialFormOptions(result);
	            crm_form_client.FormClient.getInstance().resetCache(result.id);
	            main_core.Dom.removeClass(_this20.getSaveButton().layout, 'ui-btn-wait');
	            var activeButton = _this20.getSidebarButtons().find(function (button) {
	              return button.isActive();
	            });
	            return landing_backend.Backend.getInstance().action('Form::getCrmFields').then(function (crmFields) {
	              _this20.setCrmFields(crmFields);
	              landing_ui_panel_fieldspanel.FieldsPanel.getInstance().setCrmFields(crmFields);
	              if (activeButton && !main_core.Dom.hasClass(_this20.layout, 'landing-ui-panel-mode-transparent')) {
	                activeButton.getLayout().click();
	              }
	              return result;
	            });
	            if (_this20.isCrmFormPage()) {
	              main_core.Dom.addClass(_this20.getSaveButton().layout, 'ui-btn-icon-done');
	              var currentButtonText = _this20.getSaveButton().layout.innerText;
	              _this20.getSaveButton().setText(landing_loc.Loc.getMessage('LANDING_FORM_EDITOR_SAVE_BUTTON_STATE_SAVED'));
	              setTimeout(function () {
	                main_core.Dom.removeClass(_this20.getSaveButton().layout, 'ui-btn-icon-done');
	                _this20.getSaveButton().setText(currentButtonText);
	              }, 1500);
	            } else {
	              void _this20.hide();
	            }
	          })["catch"](function (errors) {
	            if (main_core.Type.isArrayFilled(errors)) {
	              if (_classPrivateMethodGet(_this20, _isPhoneValidationError, _isPhoneValidationError2).call(_this20, errors)) {
	                _classPrivateMethodGet(_this20, _showPhoneVerifySlider, _showPhoneVerifySlider2).call(_this20);
	              } else {
	                var errorMessage = errors.map(function (item) {
	                  return main_core.Text.encode(item.message);
	                }).join('<br><br>');
	                var errorAlert = _this20.getErrorAlert();
	                errorAlert.setMessage(errorMessage);
	                errorAlert.show();
	              }
	            } else {
	              var _rootWindow2 = landing_pageobject.PageObject.getRootWindow();
	              _rootWindow2.BX.UI.Dialogs.MessageBox.alert(landing_loc.Loc.getMessage('LANDING_FORM_SAVE_UNKNOWN_ERROR_ALERT_TEXT'), landing_loc.Loc.getMessage('LANDING_FORM_SAVE_ERROR_ALERT_TITLE'));
	            }
	            main_core.Dom.removeClass(_this20.getSaveButton().layout, 'ui-btn-wait');
	          });
	          if (_this20.useBlockDesign() && _this20.isCrmFormPage()) {
	            _this20.disableUseBlockDesign();
	          }
	        } else {
	          main_core.Dom.removeClass(_this20.getSaveButton().layout, 'ui-btn-wait');
	        }
	      });
	    }
	  }, {
	    key: "isChanged",
	    value: function isChanged() {
	      return JSON.stringify(this.getFormOptions()) !== JSON.stringify(this.getInitialFormOptions());
	    }
	  }, {
	    key: "disableUseBlockDesign",
	    value: function disableUseBlockDesign() {
	      main_core.Dom.attr(this.getFormNode(), 'data-b24form-use-style', 'N');
	      this.cache.set('useBlockDesign', false);
	      landing_backend.Backend.getInstance().action('Landing\\Block::updateNodes', {
	        block: this.getCurrentBlock().id,
	        data: {
	          '.bitrix24forms': {
	            attrs: {
	              'data-b24form-use-style': 'N'
	            }
	          }
	        },
	        lid: this.getCurrentBlock().lid,
	        siteId: this.getCurrentBlock().siteId
	      }, {
	        code: this.getCurrentBlock().manifest.code
	      }).then(function (result) {
	        return landing_history.History.getInstance().push();
	      });
	    }
	  }, {
	    key: "onCancelClick",
	    value: function onCancelClick() {
	      var initialFormOptions = this.getInitialFormOptions();
	      this.getCrmForm().adjust(initialFormOptions.data);
	      BX.Landing.UI.Panel.Top.getInstance().setFormName(initialFormOptions.name);
	      void this.hide();
	    }
	  }, {
	    key: "hide",
	    value: function hide() {
	      var editorWindow = landing_pageobject.PageObject.getEditorWindow();
	      main_core.Dom.removeClass(editorWindow.document.body, 'landing-ui-hide-action-panels-form');
	      this.enableHistory();
	      return babelHelpers.get(babelHelpers.getPrototypeOf(FormSettingsPanel.prototype), "hide", this).call(this);
	    }
	  }, {
	    key: "onSidebarButtonClick",
	    value: function onSidebarButtonClick(event) {
	      var target = event.getTarget();
	      if (target.options.id === 'design') {
	        this.onFormDesignButtonClick();
	      } else {
	        babelHelpers.get(babelHelpers.getPrototypeOf(FormSettingsPanel.prototype), "onSidebarButtonClick", this).call(this, event);
	      }
	    }
	  }], [{
	    key: "sanitize",
	    value: function sanitize(value) {
	      if (main_core.Type.isStringFilled(value)) {
	        return main_core.Text.decode(value).replace(/<style[^>]*>.*<\/style>/gm, '').replace(/<script[^>]*>.*<\/script>/gm, '').replace(/<[^>]+>/gm, '');
	      }
	      return value;
	    }
	  }]);
	  return FormSettingsPanel;
	}(landing_ui_panel_basepresetpanel.BasePresetPanel);
	function _isPhoneValidationError2(errors) {
	  var _this21 = this;
	  return errors.some(function (error) {
	    return error.code === babelHelpers.classPrivateFieldGet(_this21, _phoneDoesntVerifiedResponseCode);
	  });
	}
	function _showPhoneVerifySlider2() {
	  if (typeof bitrix24_phoneverify.PhoneVerify !== 'undefined') {
	    bitrix24_phoneverify.PhoneVerify.getInstance().setEntityType(PHONE_VERIFY_FORM_ENTITY).setEntityId(this.getCurrentFormId()).startVerify({
	      sliderTitle: landing_loc.Loc.getMessage('LANDING_FORM_EDITOR_PHONE_VERIFY_CUSTOM_SLIDER_TITLE'),
	      title: landing_loc.Loc.getMessage('LANDING_FORM_EDITOR_PHONE_VERIFY_CUSTOM_TITLE'),
	      description: landing_loc.Loc.getMessage('LANDING_FORM_EDITOR_PHONE_VERIFY_CUSTOM_DESCRIPTION')
	    });
	  }
	}

	exports.FormSettingsPanel = FormSettingsPanel;

}((this.BX.Landing.UI.Panel = this.BX.Landing.UI.Panel || {}),BX.Landing.UI.Panel,BX.Landing,BX.Landing,BX,BX.Landing,BX,BX.Crm.Form,BX.UI,BX.Landing,BX.Landing.UI.Panel,BX.UI.Dialogs,BX.UI,BX.Landing.UI.Button,BX.UI.Tour,BX.Landing.UI.Panel,BX.Bitrix24,BX.UI,BX,BX,BX.Landing));
//# sourceMappingURL=formsettingspanel.bundle.js.map
