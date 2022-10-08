this.BX = this.BX || {};
this.BX.Landing = this.BX.Landing || {};
this.BX.Landing.UI = this.BX.Landing.UI || {};
(function (exports,landing_ui_panel_content,landing_ui_button_basebutton,landing_ui_field_presetfield,landing_pageobject,landing_ui_button_sidebarbutton,ui_designTokens,ui_fonts_opensans,landing_loc,ui_textcrop,main_loader,main_core_events,main_core,landing_ui_card_headercard,landing_ui_card_messagecard,landing_ui_form_formsettingsform,landing_collection_basecollection,landing_ui_form_baseform) {
	'use strict';

	var _templateObject, _templateObject2;

	function ownKeys(object, enumerableOnly) { var keys = Object.keys(object); if (Object.getOwnPropertySymbols) { var symbols = Object.getOwnPropertySymbols(object); enumerableOnly && (symbols = symbols.filter(function (sym) { return Object.getOwnPropertyDescriptor(object, sym).enumerable; })), keys.push.apply(keys, symbols); } return keys; }

	function _objectSpread(target) { for (var i = 1; i < arguments.length; i++) { var source = null != arguments[i] ? arguments[i] : {}; i % 2 ? ownKeys(Object(source), !0).forEach(function (key) { babelHelpers.defineProperty(target, key, source[key]); }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(target, Object.getOwnPropertyDescriptors(source)) : ownKeys(Object(source)).forEach(function (key) { Object.defineProperty(target, key, Object.getOwnPropertyDescriptor(source, key)); }); } return target; }

	var PresetCategory = /*#__PURE__*/function () {
	  function PresetCategory(options) {
	    babelHelpers.classCallCheck(this, PresetCategory);
	    this.options = _objectSpread({}, options);
	    this.cache = new main_core.Cache.MemoryCache();
	  }

	  babelHelpers.createClass(PresetCategory, [{
	    key: "setPresets",
	    value: function setPresets(presets) {
	      this.presets = presets;
	      var listContainer = this.getListContainer();
	      main_core.Dom.clean(listContainer);
	      this.presets.forEach(function (preset) {
	        main_core.Dom.append(preset.getLayout(), listContainer);
	      });
	    }
	  }, {
	    key: "getListContainer",
	    value: function getListContainer() {
	      return this.cache.remember('listContainer', function () {
	        return main_core.Tag.render(_templateObject || (_templateObject = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"landing-ui-presets-category-list\"></div>\n\t\t\t"])));
	      });
	    }
	  }, {
	    key: "getLayout",
	    value: function getLayout() {
	      var _this = this;

	      return this.cache.remember('layout', function () {
	        return main_core.Tag.render(_templateObject2 || (_templateObject2 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"landing-ui-presets-category\">\n\t\t\t\t\t<div class=\"landing-ui-presets-category-title\">", "</div>\n\t\t\t\t\t", "\n\t\t\t\t</div>\n\t\t\t"])), _this.options.title, _this.getListContainer());
	      });
	    }
	  }]);
	  return PresetCategory;
	}();

	var _templateObject$1, _templateObject2$1, _templateObject3, _templateObject4, _templateObject5;

	function ownKeys$1(object, enumerableOnly) { var keys = Object.keys(object); if (Object.getOwnPropertySymbols) { var symbols = Object.getOwnPropertySymbols(object); enumerableOnly && (symbols = symbols.filter(function (sym) { return Object.getOwnPropertyDescriptor(object, sym).enumerable; })), keys.push.apply(keys, symbols); } return keys; }

	function _objectSpread$1(target) { for (var i = 1; i < arguments.length; i++) { var source = null != arguments[i] ? arguments[i] : {}; i % 2 ? ownKeys$1(Object(source), !0).forEach(function (key) { babelHelpers.defineProperty(target, key, source[key]); }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(target, Object.getOwnPropertyDescriptors(source)) : ownKeys$1(Object(source)).forEach(function (key) { Object.defineProperty(target, key, Object.getOwnPropertyDescriptor(source, key)); }); } return target; }
	var defaultOptions = {
	  disabled: false,
	  soon: false
	};
	/**
	 * @memberOf BX.Landing.UI.Panel.BasePresetPanel
	 */

	var Preset = /*#__PURE__*/function (_EventEmitter) {
	  babelHelpers.inherits(Preset, _EventEmitter);

	  function Preset(options) {
	    var _this;

	    babelHelpers.classCallCheck(this, Preset);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(Preset).call(this, options));

	    _this.setEventNamespace('BX.Landing.UI.Panel.BasePresetPanel.Preset');

	    _this.options = _objectSpread$1(_objectSpread$1({}, defaultOptions), options);
	    _this.cache = new main_core.Cache.MemoryCache();
	    return _this;
	  }

	  babelHelpers.createClass(Preset, [{
	    key: "getTextCrop",
	    value: function getTextCrop() {
	      var _this2 = this;

	      return this.cache.remember('textCrop', function () {
	        return new ui_textcrop.TextCrop({
	          rows: 2,
	          target: _this2.getDescriptionNode()
	        });
	      });
	    }
	  }, {
	    key: "getIconNode",
	    value: function getIconNode() {
	      var _this3 = this;

	      return this.cache.remember('iconNode', function () {
	        return main_core.Tag.render(_templateObject$1 || (_templateObject$1 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div\n\t\t\t\t\tclass=\"landing-ui-panel-preset-icon\"\n\t\t\t\t\tstyle=\"background-image: url(", "?v2)\"\n\t\t\t\t></div>\n\t\t\t"])), _this3.options.icon);
	      });
	    }
	  }, {
	    key: "getTitleNode",
	    value: function getTitleNode() {
	      var _this4 = this;

	      return this.cache.remember('titleNode', function () {
	        return main_core.Tag.render(_templateObject2$1 || (_templateObject2$1 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div\n\t\t\t\t\tclass=\"landing-ui-panel-preset-text-title\"\n\t\t\t\t\ttitle=\"", "\"\n\t\t\t\t>", "</div>\n\t\t\t"])), main_core.Text.encode(_this4.options.title), _this4.options.title);
	      });
	    }
	  }, {
	    key: "getDescriptionNode",
	    value: function getDescriptionNode() {
	      var _this5 = this;

	      return this.cache.remember('descriptionNode', function () {
	        return main_core.Tag.render(_templateObject3 || (_templateObject3 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div\n\t\t\t\t\tclass=\"landing-ui-panel-preset-text-description\"\n\t\t\t\t\ttitle=\"", "\"\n\t\t\t\t>", "</div>\n\t\t\t"])), main_core.Text.encode(_this5.options.description), _this5.options.description);
	      });
	    }
	  }, {
	    key: "activate",
	    value: function activate() {
	      main_core.Dom.addClass(this.getLayout(), 'landing-ui-panel-preset-active');
	    }
	  }, {
	    key: "deactivate",
	    value: function deactivate() {
	      main_core.Dom.removeClass(this.getLayout(), 'landing-ui-panel-preset-active');
	    }
	  }, {
	    key: "isActive",
	    value: function isActive() {
	      return main_core.Dom.hasClass(this.getLayout(), 'landing-ui-panel-preset-active');
	    }
	  }, {
	    key: "getSoonLabel",
	    value: function getSoonLabel() {
	      return this.cache.remember('soonLabel', function () {
	        return main_core.Tag.render(_templateObject4 || (_templateObject4 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"landing-ui-panel-preset-soon-label\">\n\t\t\t\t\t", "\n\t\t\t\t</div>\n\t\t\t"])), landing_loc.Loc.getMessage('LANDING_UI_BASE_PRESET_PANEL_SOON_LABEL'));
	      });
	    }
	  }, {
	    key: "getLayout",
	    value: function getLayout() {
	      var _this6 = this;

	      return this.cache.remember('layout', function () {
	        var onLayoutClick = function onLayoutClick(event) {
	          event.preventDefault();

	          if (_this6.options.openable) {
	            _this6.activate();
	          }

	          _this6.emit('onClick');
	        };

	        var additionalClass = _this6.options.active ? ' landing-ui-panel-preset-active' : '';
	        var disabledClass = _this6.options.disabled ? ' landing-ui-disabled' : '';
	        return main_core.Tag.render(_templateObject5 || (_templateObject5 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"landing-ui-panel-preset", "", "\" onclick=\"", "\">\n\t\t\t\t\t", "\n\t\t\t\t\t<div class=\"landing-ui-panel-preset-text\">\n\t\t\t\t\t\t", "\n\t\t\t\t\t\t", "\n\t\t\t\t\t</div>\n\t\t\t\t\t", "\n\t\t\t\t</div>\n\t\t\t"])), additionalClass, disabledClass, onLayoutClick, main_core.Type.isStringFilled(_this6.options.icon) ? _this6.getIconNode() : '', main_core.Type.isStringFilled(_this6.options.title) ? _this6.getTitleNode() : '', main_core.Type.isStringFilled(_this6.options.description) ? _this6.getDescriptionNode() : '', _this6.options.soon ? _this6.getSoonLabel() : '');
	      });
	    }
	  }]);
	  return Preset;
	}(main_core_events.EventEmitter);

	var _templateObject$2;

	function ownKeys$2(object, enumerableOnly) { var keys = Object.keys(object); if (Object.getOwnPropertySymbols) { var symbols = Object.getOwnPropertySymbols(object); enumerableOnly && (symbols = symbols.filter(function (sym) { return Object.getOwnPropertyDescriptor(object, sym).enumerable; })), keys.push.apply(keys, symbols); } return keys; }

	function _objectSpread$2(target) { for (var i = 1; i < arguments.length; i++) { var source = null != arguments[i] ? arguments[i] : {}; i % 2 ? ownKeys$2(Object(source), !0).forEach(function (key) { babelHelpers.defineProperty(target, key, source[key]); }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(target, Object.getOwnPropertyDescriptors(source)) : ownKeys$2(Object(source)).forEach(function (key) { Object.defineProperty(target, key, Object.getOwnPropertyDescriptor(source, key)); }); } return target; }

	var ContentWrapper = /*#__PURE__*/function (_EventEmitter) {
	  babelHelpers.inherits(ContentWrapper, _EventEmitter);

	  function ContentWrapper(options) {
	    var _this;

	    babelHelpers.classCallCheck(this, ContentWrapper);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(ContentWrapper).call(this));

	    _this.setEventNamespace('BX.Landing.UI.Panel.FormSettingsPanel.ContentWrapper');

	    _this.options = _objectSpread$2({}, options);
	    _this.cache = new main_core.Cache.MemoryCache();
	    _this.items = new landing_collection_basecollection.BaseCollection();
	    _this.onChange = _this.onChange.bind(babelHelpers.assertThisInitialized(_this));
	    return _this;
	  }

	  babelHelpers.createClass(ContentWrapper, [{
	    key: "addItem",
	    value: function addItem(item) {
	      if (!this.items.includes(item)) {
	        this.items.add(item);
	        item.subscribe('onChange', this.onChange);
	      }

	      main_core.Dom.append(item.getLayout(), this.getLayout());
	    }
	  }, {
	    key: "insertBefore",
	    value: function insertBefore(current, target) {
	      if (!this.items.includes(current)) {
	        this.items.add(current);
	        current.subscribe('onChange', this.onChange);
	      }

	      main_core.Dom.insertBefore(current.getLayout(), target.getLayout());
	    }
	  }, {
	    key: "getLayout",
	    value: function getLayout() {
	      return this.cache.remember('wrapper', function () {
	        return main_core.Tag.render(_templateObject$2 || (_templateObject$2 = babelHelpers.taggedTemplateLiteral(["<div class=\"landing-ui-panel-form-settings-content-wrapper\"></div>"])));
	      });
	    }
	  }, {
	    key: "getValue",
	    value: function getValue() {
	      var value = this.items.reduce(function (acc, item) {
	        if (item instanceof landing_ui_form_baseform.BaseForm && item.getLayout().parentElement) {
	          return _objectSpread$2(_objectSpread$2({}, acc), item.serialize());
	        }

	        return acc;
	      }, {});
	      return this.valueReducer(value);
	    } // eslint-disable-next-line class-methods-use-this

	  }, {
	    key: "valueReducer",
	    value: function valueReducer(value) {
	      return value;
	    }
	  }, {
	    key: "onChange",
	    value: function onChange(event) {
	      this.emit('onChange', event.getData());
	    }
	  }, {
	    key: "clear",
	    value: function clear() {
	      main_core.Dom.clean(this.getLayout());
	    }
	  }]);
	  return ContentWrapper;
	}(main_core_events.EventEmitter);

	var _templateObject$3, _templateObject2$2, _templateObject3$1;
	/**
	 * @memberOf BX.Landing.UI.Panel
	 */

	var BasePresetPanel = /*#__PURE__*/function (_Content) {
	  babelHelpers.inherits(BasePresetPanel, _Content);

	  function BasePresetPanel() {
	    var _this;

	    babelHelpers.classCallCheck(this, BasePresetPanel);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(BasePresetPanel).call(this));

	    _this.setEventNamespace('BX.Landing.UI.Panel.BasePresetPanel');

	    main_core.Dom.addClass(_this.layout, 'landing-ui-panel-base-preset');
	    main_core.Dom.addClass(_this.overlay, 'landing-ui-panel-base-preset-overlay');
	    _this.cache = new main_core.Cache.MemoryCache();
	    _this.onSidebarButtonClick = _this.onSidebarButtonClick.bind(babelHelpers.assertThisInitialized(_this));
	    _this.onSaveClick = _this.onSaveClick.bind(babelHelpers.assertThisInitialized(_this));
	    _this.onCancelClick = _this.onCancelClick.bind(babelHelpers.assertThisInitialized(_this));
	    _this.onPresetFieldClick = _this.onPresetFieldClick.bind(babelHelpers.assertThisInitialized(_this));
	    _this.onPresetClick = _this.onPresetClick.bind(babelHelpers.assertThisInitialized(_this));
	    _this.onChange = _this.onChange.bind(babelHelpers.assertThisInitialized(_this));

	    _this.appendFooterButton(_this.getSaveButton());

	    _this.appendFooterButton(_this.getCancelButton());

	    main_core.Dom.append(_this.getHeaderControlsContainer(), _this.header);
	    return _this;
	  }

	  babelHelpers.createClass(BasePresetPanel, [{
	    key: "enableToggleMode",
	    value: function enableToggleMode() {
	      this.cache.set('toggleMode', true);
	      this.renderTo(this.getViewContainer());
	    }
	  }, {
	    key: "isToggleModeEnabled",
	    value: function isToggleModeEnabled() {
	      return this.cache.get('toggleMode') === true;
	    }
	  }, {
	    key: "disableOverlay",
	    value: function disableOverlay() {
	      main_core.Dom.addClass(this.overlay, 'landing-ui-panel-base-preset-disable-overlay');
	    }
	  }, {
	    key: "getViewContainer",
	    value: function getViewContainer() {
	      return this.cache.remember('viewContainer', function () {
	        var rootWindow = landing_pageobject.PageObject.getRootWindow();
	        return rootWindow.document.querySelector('.landing-ui-view-container');
	      });
	    }
	  }, {
	    key: "getViewWrapper",
	    value: function getViewWrapper() {
	      var _this2 = this;

	      return this.cache.remember('viewWrapper', function () {
	        return _this2.getViewContainer().querySelector('.landing-ui-view-wrapper');
	      });
	    }
	  }, {
	    key: "getSaveButton",
	    value: function getSaveButton() {
	      var _this3 = this;

	      return this.cache.remember('saveButton', function () {
	        var button = new landing_ui_button_basebutton.BaseButton('save_settings', {
	          text: landing_loc.Loc.getMessage('BLOCK_SAVE'),
	          onClick: _this3.onSaveClick,
	          className: 'ui-btn ui-btn-success',
	          attrs: {
	            title: landing_loc.Loc.getMessage('LANDING_TITLE_OF_SLIDER_SAVE')
	          }
	        });
	        main_core.Dom.removeClass(button.layout, 'landing-ui-button');
	        return button;
	      });
	    } // eslint-disable-next-line

	  }, {
	    key: "onSaveClick",
	    value: function onSaveClick() {}
	  }, {
	    key: "getCancelButton",
	    value: function getCancelButton() {
	      var _this4 = this;

	      return this.cache.remember('cancelButton', function () {
	        return new landing_ui_button_basebutton.BaseButton('cancel_settings', {
	          text: landing_loc.Loc.getMessage('BLOCK_CANCEL'),
	          onClick: _this4.onCancelClick,
	          className: 'landing-ui-button-content-cancel',
	          attrs: {
	            title: landing_loc.Loc.getMessage('LANDING_TITLE_OF_SLIDER_CANCEL')
	          }
	        });
	      });
	    } // eslint-disable-next-line

	  }, {
	    key: "onCancelClick",
	    value: function onCancelClick() {}
	  }, {
	    key: "appendSidebarButton",
	    value: function appendSidebarButton(button) {
	      babelHelpers.get(babelHelpers.getPrototypeOf(BasePresetPanel.prototype), "appendSidebarButton", this).call(this, button);
	    } // eslint-disable-next-line

	  }, {
	    key: "onSidebarButtonClick",
	    value: function onSidebarButtonClick(event) {
	      var _this5 = this;

	      var activeButton = this.sidebarButtons.getActive();

	      if (activeButton) {
	        activeButton.deactivate();
	      }

	      event.getTarget().activate();
	      main_core.Dom.addClass(this.content, 'landing-ui-panel-base-preset-fade');
	      this.showContentLoader();
	      void this.getContent(event.getTarget().id).then(function (content) {
	        if (content) {
	          setTimeout(function () {
	            main_core.Dom.removeClass(_this5.content, 'landing-ui-panel-base-preset-fade');

	            _this5.clearContent();

	            _this5.hideContentLoader();

	            content.subscribe('onChange', _this5.onChange);
	            main_core.Dom.append(content.getLayout(), _this5.content);
	          }, 300);
	        } else {
	          main_core.Dom.removeClass(_this5.content, 'landing-ui-panel-base-preset-fade');

	          _this5.clearContent();

	          _this5.hideContentLoader();
	        }
	      });
	    }
	  }, {
	    key: "onChange",
	    value: function onChange(event) {} // eslint-disable-next-line

	  }, {
	    key: "getContent",
	    value: function getContent(id) {
	      throw new Error('Must be implemented in child class');
	    }
	  }, {
	    key: "getHeaderControlsContainer",
	    value: function getHeaderControlsContainer() {
	      var _this6 = this;

	      return this.cache.remember('headerControlsContainer', function () {
	        return main_core.Tag.render(_templateObject$3 || (_templateObject$3 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"landing-ui-panel-base-preset-header-controls\">\n\t\t\t\t\t", "\n\t\t\t\t\t", "\n\t\t\t\t</div>\n\t\t\t"])), _this6.getLeftHeaderControls(), _this6.getRightHeaderControls());
	      });
	    }
	  }, {
	    key: "getRightHeaderControls",
	    value: function getRightHeaderControls() {
	      return this.cache.remember('rightHeaderControls', function () {
	        return main_core.Tag.render(_templateObject2$2 || (_templateObject2$2 = babelHelpers.taggedTemplateLiteral(["<div class=\"landing-ui-panel-base-preset-header-controls-right\"></div>"])));
	      });
	    }
	  }, {
	    key: "getLeftHeaderControls",
	    value: function getLeftHeaderControls() {
	      var _this7 = this;

	      return this.cache.remember('leftHeaderControls', function () {
	        return main_core.Tag.render(_templateObject3$1 || (_templateObject3$1 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"landing-ui-panel-base-preset-header-controls-left\">\n\t\t\t\t\t", "\n\t\t\t\t</div>\n\t\t\t"])), _this7.getPresetField().getNode());
	      });
	    }
	  }, {
	    key: "getPresetField",
	    value: function getPresetField() {
	      var _this8 = this;

	      return this.cache.remember('presetField', function () {
	        return new landing_ui_field_presetfield.PresetField({
	          events: {
	            onClick: _this8.onPresetFieldClick
	          }
	        });
	      });
	    }
	  }, {
	    key: "show",
	    value: function show(options) {
	      if (this.isToggleModeEnabled()) {
	        var contentEditPanel = BX.Landing.UI.Panel.ContentEdit;

	        if (contentEditPanel.showedPanel) {
	          contentEditPanel.showedPanel.hide();
	        }

	        var viewWrapper = this.getViewWrapper();
	        main_core.Dom.style(viewWrapper, 'transition', '400ms margin ease');
	        setTimeout(function () {
	          main_core.Dom.style(viewWrapper, 'margin-left', '880px');
	        });
	      }

	      return babelHelpers.get(babelHelpers.getPrototypeOf(BasePresetPanel.prototype), "show", this).call(this, options);
	    }
	  }, {
	    key: "hide",
	    value: function hide() {
	      var _this9 = this;

	      var viewWrapper = this.getViewWrapper();

	      if (this.isToggleModeEnabled()) {
	        main_core.Dom.style(viewWrapper, 'margin-left', null);
	      }

	      return babelHelpers.get(babelHelpers.getPrototypeOf(BasePresetPanel.prototype), "hide", this).call(this).then(function () {
	        if (_this9.isToggleModeEnabled()) {
	          main_core.Dom.style(viewWrapper, 'transition', null);
	        }
	      });
	    }
	  }, {
	    key: "enableTransparentMode",
	    value: function enableTransparentMode() {
	      main_core.Dom.addClass(this.layout, 'landing-ui-panel-mode-transparent');
	    }
	  }, {
	    key: "disableTransparentMode",
	    value: function disableTransparentMode() {
	      main_core.Dom.removeClass(this.layout, 'landing-ui-panel-mode-transparent');
	    }
	  }, {
	    key: "setCategories",
	    value: function setCategories(categories) {
	      this.cache.set('categories', categories);
	      this.cache["delete"]('renderedPresets');
	    }
	  }, {
	    key: "getCategories",
	    value: function getCategories() {
	      return this.cache.get('categories');
	    }
	  }, {
	    key: "setPresets",
	    value: function setPresets(presets) {
	      var _this10 = this;

	      presets.forEach(function (preset) {
	        preset.unsubscribe('onClick', _this10.onPresetClick);
	        preset.subscribe('onClick', _this10.onPresetClick);
	      });
	      this.cache.set('presets', presets);
	      this.cache["delete"]('renderedPresets');
	    }
	  }, {
	    key: "getPresets",
	    value: function getPresets() {
	      return this.cache.get('presets');
	    }
	  }, {
	    key: "setSidebarButtons",
	    value: function setSidebarButtons(buttons) {
	      var _this11 = this;

	      buttons.forEach(function (button) {
	        button.subscribe('onClick', _this11.onSidebarButtonClick);
	      });
	      this.cache.set('sidebarButtons', buttons);
	    }
	  }, {
	    key: "getSidebarButtons",
	    value: function getSidebarButtons() {
	      return this.cache.get('sidebarButtons');
	    }
	  }, {
	    key: "onPresetFieldClick",
	    value: function onPresetFieldClick() {
	      var _this12 = this;

	      this.clear();
	      this.enableTransparentMode();
	      this.getCategories().forEach(function (category) {
	        var presets = _this12.getPresets().filter(function (preset) {
	          return preset.options.category === category.options.id;
	        });

	        category.setPresets(presets);
	        main_core.Dom.append(category.getLayout(), _this12.content);

	        _this12.getPresets().forEach(function (preset) {
	          preset.getTextCrop().init();
	        });
	      });
	    }
	  }, {
	    key: "onPresetClick",
	    value: function onPresetClick(event) {
	      this.disableTransparentMode();
	      this.applyPreset(event.getTarget());
	    }
	  }, {
	    key: "activatePreset",
	    value: function activatePreset(presetId) {
	      var preset = this.getPresets().find(function (currentPreset) {
	        return currentPreset.options.id === presetId;
	      });
	      var presetField = this.getPresetField();
	      presetField.setLinkText(preset.options.title);
	      presetField.setIcon(preset.options.icon);
	      preset.activate();
	    } // eslint-disable-next-line no-unused-vars

	  }, {
	    key: "applyPreset",
	    value: function applyPreset(preset) {
	      var _this13 = this;
	      this.clear();
	      var presetField = this.getPresetField();
	      presetField.setLinkText(preset.options.title);
	      presetField.setIcon(preset.options.icon);
	      var buttons = this.getSidebarButtons().filter(function (button) {
	        return preset.options.items.includes(button.id);
	      });
	      buttons.forEach(function (button) {
	        button.deactivate();

	        _this13.appendSidebarButton(button);
	      });

	      if (main_core.Type.isStringFilled(preset.options.defaultSection)) {
	        var defaultSectionButton = buttons.find(function (button) {
	          return button.id === preset.options.defaultSection;
	        });

	        if (defaultSectionButton) {
	          defaultSectionButton.activate();
	          defaultSectionButton.layout.click();
	        }
	      } else {
	        var _buttons = babelHelpers.slicedToArray(buttons, 1),
	            firstButton = _buttons[0];

	        firstButton.activate();
	        firstButton.layout.click();
	      }
	    }
	  }, {
	    key: "getContentLoader",
	    value: function getContentLoader() {
	      var _this14 = this;

	      return this.cache.remember('contentLoader', function () {
	        return new main_loader.Loader({
	          target: _this14.body,
	          offset: {
	            left: '130px'
	          }
	        });
	      });
	    }
	  }, {
	    key: "showContentLoader",
	    value: function showContentLoader() {
	      void this.getContentLoader().show();
	    }
	  }, {
	    key: "hideContentLoader",
	    value: function hideContentLoader() {
	      void this.getContentLoader().hide();
	    }
	  }]);
	  return BasePresetPanel;
	}(landing_ui_panel_content.Content);

	exports.BasePresetPanel = BasePresetPanel;
	exports.PresetCategory = PresetCategory;
	exports.Preset = Preset;
	exports.ContentWrapper = ContentWrapper;

}((this.BX.Landing.UI.Panel = this.BX.Landing.UI.Panel || {}),BX.Landing.UI.Panel,BX.Landing.UI.Button,BX.Landing.UI.Field,BX.Landing,BX.Landing.UI.Button,BX,BX,BX.Landing,BX.UI,BX,BX.Event,BX,BX.Landing.UI.Card,BX.Landing.UI.Card,BX.Landing.UI.Form,BX.Landing.Collection,BX.Landing.UI.Form));
//# sourceMappingURL=basepresetpanel.bundle.js.map
