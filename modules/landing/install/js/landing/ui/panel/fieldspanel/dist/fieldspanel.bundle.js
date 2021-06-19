this.BX = this.BX || {};
this.BX.Landing = this.BX.Landing || {};
this.BX.Landing.UI = this.BX.Landing.UI || {};
(function (exports,main_core,landing_ui_panel_content,main_loader,landing_backend,landing_pageobject,landing_ui_button_sidebarbutton,landing_loc,landing_ui_form_formsettingsform,landing_ui_button_basebutton) {
	'use strict';

	/**
	 * @memberOf BX.Landing.UI.Panel
	 */
	var FieldsPanel = /*#__PURE__*/function (_Content) {
	  babelHelpers.inherits(FieldsPanel, _Content);
	  babelHelpers.createClass(FieldsPanel, null, [{
	    key: "getInstance",
	    value: function getInstance(options) {
	      var rootWindow = landing_pageobject.PageObject.getRootWindow();
	      var rootWindowPanel = rootWindow.BX.Landing.UI.Panel.FieldsPanel;

	      if (!rootWindowPanel.instance && !FieldsPanel.instance) {
	        rootWindowPanel.instance = new FieldsPanel(options);
	      }

	      var instance = rootWindowPanel.instance || FieldsPanel.instance;
	      instance.options = options;
	      return instance;
	    }
	  }]);

	  function FieldsPanel() {
	    var _this;

	    var options = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};
	    babelHelpers.classCallCheck(this, FieldsPanel);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(FieldsPanel).call(this));
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "adjustActionsPanels", false);

	    _this.setEventNamespace('BX.Landing.UI.Panel.FieldsPanel');

	    _this.setLayoutClass('landing-ui-panel-fields');

	    _this.setOverlayClass('landing-ui-panel-fields-overlay');

	    _this.setTitle(landing_loc.Loc.getMessage('LANDING_FIELDS_PANEL_TITLE'));

	    _this.onSaveClick = _this.onSaveClick.bind(babelHelpers.assertThisInitialized(_this));
	    _this.onCancelClick = _this.onCancelClick.bind(babelHelpers.assertThisInitialized(_this));
	    _this.options = options;
	    _this.cache = new main_core.Cache.MemoryCache();
	    main_core.Dom.append(_this.layout, _this.getViewContainer());
	    main_core.Dom.append(_this.overlay, _this.getViewContainer());

	    _this.showLoader();

	    _this.loadPromise = _this.load().then(function () {
	      _this.hideLoader();

	      Object.entries(_this.getCrmFields()).forEach(function (_ref) {
	        var _ref2 = babelHelpers.slicedToArray(_ref, 2),
	            categoryId = _ref2[0],
	            category = _ref2[1];

	        if (categoryId !== 'CATALOG' && categoryId !== 'ACTIVITY' && categoryId !== 'INVOICE') {
	          if (main_core.Type.isPlainObject(options) && main_core.Type.isBoolean(options.isLeadEnabled) && !options.isLeadEnabled && categoryId === 'LEAD') {
	            return;
	          }

	          var button = new landing_ui_button_sidebarbutton.SidebarButton({
	            id: categoryId,
	            text: category.CAPTION,
	            child: true,
	            onClick: function onClick() {
	              _this.onSidebarButtonClick(button);
	            }
	          });

	          _this.appendSidebarButton(button);
	        }
	      });

	      _this.sidebarButtons[0].getLayout().click();

	      _this.appendFooterButton(new landing_ui_button_basebutton.BaseButton('save_settings', {
	        text: landing_loc.Loc.getMessage('BLOCK_SAVE'),
	        onClick: _this.onSaveClick,
	        className: 'landing-ui-button-content-save',
	        attrs: {
	          title: landing_loc.Loc.getMessage('LANDING_TITLE_OF_SLIDER_SAVE')
	        }
	      }));

	      _this.appendFooterButton(new landing_ui_button_basebutton.BaseButton('cancel_settings', {
	        text: landing_loc.Loc.getMessage('BLOCK_CANCEL'),
	        onClick: _this.onCancelClick,
	        className: 'landing-ui-button-content-cancel',
	        attrs: {
	          title: landing_loc.Loc.getMessage('LANDING_TITLE_OF_SLIDER_CANCEL')
	        }
	      }));
	    });
	    return _this;
	  }

	  babelHelpers.createClass(FieldsPanel, [{
	    key: "show",
	    value: function show() {
	      var _this2 = this;

	      var options = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};
	      this.disabledFields = [];

	      if (main_core.Type.isArrayFilled(options.disabledFields)) {
	        this.disabledFields = options.disabledFields;
	      }

	      this.loadPromise.then(function () {
	        if (main_core.Type.isArray(options.allowedTypes)) {
	          _this2.originalCrmFields = _this2.getCrmFields();
	          var crmFields = main_core.Runtime.clone(_this2.originalCrmFields);
	          var preparedCrmFields = Object.entries(crmFields).reduce(function (acc, _ref3) {
	            var _ref4 = babelHelpers.slicedToArray(_ref3, 2),
	                categoryId = _ref4[0],
	                category = _ref4[1];

	            var filteredFields = category.FIELDS.filter(function (field) {
	              return options.allowedTypes.includes(field.type);
	            });

	            if (main_core.Type.isArrayFilled(filteredFields)) {
	              acc[categoryId] = babelHelpers.objectSpread({}, category, {
	                FIELDS: filteredFields
	              });
	            }

	            return acc;
	          }, {});

	          _this2.setCrmFields(preparedCrmFields);
	        }

	        _this2.sidebarButtons.forEach(function (button) {
	          main_core.Dom.show(button.layout);
	        });

	        if (options.isLeadEnabled === false) {
	          var leadButton = _this2.sidebarButtons.get('LEAD');

	          if (leadButton) {
	            main_core.Dom.hide(leadButton.layout);
	          }
	        }

	        if (main_core.Type.isArrayFilled(options.allowedCategories)) {
	          _this2.sidebarButtons.forEach(function (button) {
	            if (!options.allowedCategories.includes(button.id)) {
	              main_core.Dom.hide(button.layout);
	            }
	          });
	        }

	        if (_this2.sidebarButtons.length > 0) {
	          _this2.resetState();

	          _this2.sidebarButtons[0].getLayout().click();
	        }
	      });
	      void babelHelpers.get(babelHelpers.getPrototypeOf(FieldsPanel.prototype), "show", this).call(this, options);
	      return new Promise(function (resolve) {
	        _this2.promiseResolver = resolve;
	      });
	    }
	  }, {
	    key: "hide",
	    value: function hide() {
	      if (main_core.Type.isArrayFilled(this.originalCrmFields)) {
	        this.setCrmFields(this.originalCrmFields);
	        delete this.originalCrmFields;
	      }

	      return babelHelpers.get(babelHelpers.getPrototypeOf(FieldsPanel.prototype), "hide", this).call(this);
	    }
	  }, {
	    key: "onSaveClick",
	    value: function onSaveClick() {
	      var selectedFields = Object.values(this.getState()).reduce(function (acc, fields) {
	        return [].concat(babelHelpers.toConsumableArray(acc), babelHelpers.toConsumableArray(fields));
	      }, []);
	      this.promiseResolver(selectedFields);
	      void this.hide();
	      this.resetState();
	    }
	  }, {
	    key: "onCancelClick",
	    value: function onCancelClick() {
	      void this.hide();
	      this.resetState();
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
	    key: "getLoader",
	    value: function getLoader() {
	      var _this3 = this;

	      return this.cache.remember('loader', function () {
	        return new main_loader.Loader({
	          target: _this3.body
	        });
	      });
	    }
	  }, {
	    key: "showLoader",
	    value: function showLoader() {
	      void this.getLoader().show();
	    }
	  }, {
	    key: "hideLoader",
	    value: function hideLoader() {
	      void this.getLoader().hide();
	    }
	  }, {
	    key: "load",
	    value: function load() {
	      var _this4 = this;

	      return landing_backend.Backend.getInstance().action('Form::getCrmFields').then(function (result) {
	        _this4.setCrmFields(result);
	      });
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
	    key: "setState",
	    value: function setState(state) {
	      this.cache.set('state', state);
	    }
	  }, {
	    key: "getState",
	    value: function getState() {
	      return this.cache.get('state') || {};
	    }
	  }, {
	    key: "resetState",
	    value: function resetState() {
	      this.cache.delete('state');
	    }
	  }, {
	    key: "onSidebarButtonClick",
	    value: function onSidebarButtonClick(button) {
	      var _this5 = this;

	      var activeButton = this.sidebarButtons.getActive();

	      if (activeButton) {
	        activeButton.deactivate();
	      }

	      button.activate();
	      var crmFields = this.getCrmFields();

	      if (Reflect.has(crmFields, button.id)) {
	        this.clearContent();
	        var fields = crmFields[button.id].FIELDS;
	        var form = new landing_ui_form_formsettingsform.FormSettingsForm({
	          fields: [new BX.Landing.UI.Field.Checkbox({
	            items: fields.map(function (field) {
	              return {
	                name: field.caption,
	                value: field.name,
	                disabled: _this5.disabledFields.includes(field.name)
	              };
	            }),
	            value: this.getState()[button.id] || [],
	            onValueChange: function onValueChange(checkbox) {
	              var state = babelHelpers.objectSpread({}, _this5.getState());
	              state[button.id] = checkbox.getValue();

	              _this5.setState(state);
	            }
	          })]
	        });
	        this.appendForm(form);
	      }
	    }
	  }]);
	  return FieldsPanel;
	}(landing_ui_panel_content.Content);

	exports.FieldsPanel = FieldsPanel;

}((this.BX.Landing.UI.Panel = this.BX.Landing.UI.Panel || {}),BX,BX.Landing.UI.Panel,BX,BX.Landing,BX.Landing,BX.Landing.UI.Button,BX.Landing,BX.Landing.UI.Form,BX.Landing.UI.Button));
//# sourceMappingURL=fieldspanel.bundle.js.map
