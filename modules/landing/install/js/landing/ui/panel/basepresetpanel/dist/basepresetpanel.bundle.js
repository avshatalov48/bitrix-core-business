this.BX = this.BX || {};
this.BX.Landing = this.BX.Landing || {};
this.BX.Landing.UI = this.BX.Landing.UI || {};
(function (exports,landing_ui_panel_content,landing_ui_button_basebutton,landing_ui_field_presetfield,landing_pageobject,landing_ui_button_sidebarbutton,landing_loc,main_core_events,main_core,landing_ui_card_headercard,landing_ui_card_messagecard,landing_ui_form_formsettingsform,landing_collection_basecollection,landing_ui_form_baseform) {
	'use strict';

	function _templateObject2() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"landing-ui-presets-category\">\n\t\t\t\t\t<div class=\"landing-ui-presets-category-title\">", "</div>\n\t\t\t\t\t", "\n\t\t\t\t</div>\n\t\t\t"]);

	  _templateObject2 = function _templateObject2() {
	    return data;
	  };

	  return data;
	}

	function _templateObject() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"landing-ui-presets-category-list\"></div>\n\t\t\t"]);

	  _templateObject = function _templateObject() {
	    return data;
	  };

	  return data;
	}

	var PresetCategory = /*#__PURE__*/function () {
	  function PresetCategory(options) {
	    babelHelpers.classCallCheck(this, PresetCategory);
	    this.options = babelHelpers.objectSpread({}, options);
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
	        return main_core.Tag.render(_templateObject());
	      });
	    }
	  }, {
	    key: "getLayout",
	    value: function getLayout() {
	      var _this = this;

	      return this.cache.remember('layout', function () {
	        return main_core.Tag.render(_templateObject2(), _this.options.title, _this.getListContainer());
	      });
	    }
	  }]);
	  return PresetCategory;
	}();

	function _templateObject5() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"landing-ui-panel-preset", "", "\" onclick=\"", "\">\n\t\t\t\t\t", "\n\t\t\t\t\t<div class=\"landing-ui-panel-preset-text\">\n\t\t\t\t\t\t", "\n\t\t\t\t\t\t", "\n\t\t\t\t\t</div>\n\t\t\t\t\t", "\n\t\t\t\t</div>\n\t\t\t"]);

	  _templateObject5 = function _templateObject5() {
	    return data;
	  };

	  return data;
	}

	function _templateObject4() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"landing-ui-panel-preset-soon-label\">\n\t\t\t\t\t", "\n\t\t\t\t</div>\n\t\t\t"]);

	  _templateObject4 = function _templateObject4() {
	    return data;
	  };

	  return data;
	}

	function _templateObject3() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div \n\t\t\t\t\tclass=\"landing-ui-panel-preset-text-description\"\n\t\t\t\t\ttitle=\"", "\"\n\t\t\t\t>", "</div>\n\t\t\t"]);

	  _templateObject3 = function _templateObject3() {
	    return data;
	  };

	  return data;
	}

	function _templateObject2$1() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div \n\t\t\t\t\tclass=\"landing-ui-panel-preset-text-title\" \n\t\t\t\t\ttitle=\"", "\"\n\t\t\t\t>", "</div>\n\t\t\t"]);

	  _templateObject2$1 = function _templateObject2() {
	    return data;
	  };

	  return data;
	}

	function _templateObject$1() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div \n\t\t\t\t\tclass=\"landing-ui-panel-preset-icon\" \n\t\t\t\t\tstyle=\"background-image: url(", ")\"\n\t\t\t\t></div>\n\t\t\t"]);

	  _templateObject$1 = function _templateObject() {
	    return data;
	  };

	  return data;
	}
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

	    _this.options = babelHelpers.objectSpread({}, defaultOptions, options);

	    if (landing_loc.Loc.getMessage('LANGUAGE_ID') !== 'ru') {
	      _this.options.items = _this.options.items.filter(function (item) {
	        return item !== 'vk';
	      });
	    }

	    _this.cache = new main_core.Cache.MemoryCache();
	    return _this;
	  }

	  babelHelpers.createClass(Preset, [{
	    key: "getIconNode",
	    value: function getIconNode() {
	      var _this2 = this;

	      return this.cache.remember('iconNode', function () {
	        return main_core.Tag.render(_templateObject$1(), _this2.options.icon);
	      });
	    }
	  }, {
	    key: "getTitleNode",
	    value: function getTitleNode() {
	      var _this3 = this;

	      return this.cache.remember('titleNode', function () {
	        return main_core.Tag.render(_templateObject2$1(), main_core.Text.encode(_this3.options.title), _this3.options.title);
	      });
	    }
	  }, {
	    key: "getDescriptionNode",
	    value: function getDescriptionNode() {
	      var _this4 = this;

	      return this.cache.remember('descriptionNode', function () {
	        return main_core.Tag.render(_templateObject3(), main_core.Text.encode(_this4.options.description), _this4.options.description);
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
	    key: "getSoonLabel",
	    value: function getSoonLabel() {
	      return this.cache.remember('soonLabel', function () {
	        return main_core.Tag.render(_templateObject4(), landing_loc.Loc.getMessage('LANDING_UI_BASE_PRESET_PANEL_SOON_LABEL'));
	      });
	    }
	  }, {
	    key: "getLayout",
	    value: function getLayout() {
	      var _this5 = this;

	      return this.cache.remember('layout', function () {
	        var onLayoutClick = function onLayoutClick(event) {
	          event.preventDefault();

	          _this5.activate();

	          _this5.emit('onClick');
	        };

	        var additionalClass = _this5.options.active ? ' landing-ui-panel-preset-active' : '';
	        var disabledClass = _this5.options.disabled ? ' landing-ui-disabled' : '';
	        return main_core.Tag.render(_templateObject5(), additionalClass, disabledClass, onLayoutClick, main_core.Type.isStringFilled(_this5.options.icon) && _this5.getIconNode(), main_core.Type.isStringFilled(_this5.options.title) ? _this5.getTitleNode() : '', main_core.Type.isStringFilled(_this5.options.description) ? _this5.getDescriptionNode() : '', _this5.options.soon ? _this5.getSoonLabel() : '');
	      });
	    }
	  }]);
	  return Preset;
	}(main_core_events.EventEmitter);

	function _templateObject$2() {
	  var data = babelHelpers.taggedTemplateLiteral(["<div class=\"landing-ui-panel-form-settings-content-wrapper\"></div>"]);

	  _templateObject$2 = function _templateObject() {
	    return data;
	  };

	  return data;
	}

	var ContentWrapper = /*#__PURE__*/function (_EventEmitter) {
	  babelHelpers.inherits(ContentWrapper, _EventEmitter);

	  function ContentWrapper(options) {
	    var _this;

	    babelHelpers.classCallCheck(this, ContentWrapper);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(ContentWrapper).call(this));

	    _this.setEventNamespace('BX.Landing.UI.Panel.FormSettingsPanel.ContentWrapper');

	    _this.options = babelHelpers.objectSpread({}, options);
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
	        return main_core.Tag.render(_templateObject$2());
	      });
	    }
	  }, {
	    key: "getValue",
	    value: function getValue() {
	      var value = this.items.reduce(function (acc, item) {
	        if (item instanceof landing_ui_form_baseform.BaseForm && item.getLayout().parentElement) {
	          return babelHelpers.objectSpread({}, acc, item.serialize());
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

	function _templateObject3$1() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"landing-ui-panel-base-preset-header-controls-left\">\n\t\t\t\t\t", "\n\t\t\t\t</div>\n\t\t\t"]);

	  _templateObject3$1 = function _templateObject3() {
	    return data;
	  };

	  return data;
	}

	function _templateObject2$2() {
	  var data = babelHelpers.taggedTemplateLiteral(["<div class=\"landing-ui-panel-base-preset-header-controls-right\"></div>"]);

	  _templateObject2$2 = function _templateObject2() {
	    return data;
	  };

	  return data;
	}

	function _templateObject$3() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"landing-ui-panel-base-preset-header-controls\">\n\t\t\t\t\t", "\n\t\t\t\t\t", "\n\t\t\t\t</div>\n\t\t\t"]);

	  _templateObject$3 = function _templateObject() {
	    return data;
	  };

	  return data;
	}
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
	      this.sidebarButtons.getActive().deactivate();
	      event.getTarget().activate();
	      this.clearContent();
	      var content = this.getContent(event.getTarget().id);

	      if (content) {
	        content.subscribe('onChange', this.onChange);
	        main_core.Dom.append(content.getLayout(), this.content);
	      }
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
	      var _this5 = this;

	      return this.cache.remember('headerControlsContainer', function () {
	        return main_core.Tag.render(_templateObject$3(), _this5.getLeftHeaderControls(), _this5.getRightHeaderControls());
	      });
	    }
	  }, {
	    key: "getRightHeaderControls",
	    value: function getRightHeaderControls() {
	      return this.cache.remember('rightHeaderControls', function () {
	        return main_core.Tag.render(_templateObject2$2());
	      });
	    }
	  }, {
	    key: "getLeftHeaderControls",
	    value: function getLeftHeaderControls() {
	      var _this6 = this;

	      return this.cache.remember('leftHeaderControls', function () {
	        return main_core.Tag.render(_templateObject3$1(), _this6.getPresetField().getNode());
	      });
	    }
	  }, {
	    key: "getPresetField",
	    value: function getPresetField() {
	      var _this7 = this;

	      return this.cache.remember('presetField', function () {
	        return new landing_ui_field_presetfield.PresetField({
	          events: {
	            onClick: _this7.onPresetFieldClick
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
	      var _this8 = this;

	      var viewWrapper = this.getViewWrapper();

	      if (this.isToggleModeEnabled()) {
	        main_core.Dom.style(viewWrapper, 'margin-left', null);
	      }

	      return babelHelpers.get(babelHelpers.getPrototypeOf(BasePresetPanel.prototype), "hide", this).call(this).then(function () {
	        if (_this8.isToggleModeEnabled()) {
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
	      this.cache.delete('renderedPresets');
	    }
	  }, {
	    key: "getCategories",
	    value: function getCategories() {
	      return this.cache.get('categories');
	    }
	  }, {
	    key: "setPresets",
	    value: function setPresets(presets) {
	      var _this9 = this;

	      presets.forEach(function (preset) {
	        preset.unsubscribe('onClick', _this9.onPresetClick);
	        preset.subscribe('onClick', _this9.onPresetClick);
	      });
	      this.cache.set('presets', presets);
	      this.cache.delete('renderedPresets');
	    }
	  }, {
	    key: "getPresets",
	    value: function getPresets() {
	      return this.cache.get('presets');
	    }
	  }, {
	    key: "setSidebarButtons",
	    value: function setSidebarButtons(buttons) {
	      var _this10 = this;

	      buttons.forEach(function (button) {
	        button.subscribe('onClick', _this10.onSidebarButtonClick);
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
	      var _this11 = this;

	      this.clear();
	      this.enableTransparentMode();
	      this.getCategories().forEach(function (category) {
	        var presets = _this11.getPresets().filter(function (preset) {
	          return preset.options.category === category.options.id;
	        });

	        category.setPresets(presets);
	        main_core.Dom.append(category.getLayout(), _this11.content);
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
	      var _this12 = this;
	      this.clear();
	      var presetField = this.getPresetField();
	      presetField.setLinkText(preset.options.title);
	      presetField.setIcon(preset.options.icon);
	      var buttons = this.getSidebarButtons().filter(function (button) {
	        return preset.options.items.includes(button.id);
	      });
	      buttons.forEach(function (button) {
	        button.deactivate();

	        _this12.appendSidebarButton(button);
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
	  }]);
	  return BasePresetPanel;
	}(landing_ui_panel_content.Content);

	exports.BasePresetPanel = BasePresetPanel;
	exports.PresetCategory = PresetCategory;
	exports.Preset = Preset;
	exports.ContentWrapper = ContentWrapper;

}((this.BX.Landing.UI.Panel = this.BX.Landing.UI.Panel || {}),BX.Landing.UI.Panel,BX.Landing.UI.Button,BX.Landing.UI.Field,BX.Landing,BX.Landing.UI.Button,BX.Landing,BX.Event,BX,BX.Landing.UI.Card,BX.Landing.UI.Card,BX.Landing.UI.Form,BX.Landing.Collection,BX.Landing.UI.Form));
//# sourceMappingURL=basepresetpanel.bundle.js.map
