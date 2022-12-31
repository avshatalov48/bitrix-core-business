this.BX = this.BX || {};
this.BX.Landing = this.BX.Landing || {};
this.BX.Landing.UI = this.BX.Landing.UI || {};
(function (exports,main_core,landing_ui_panel_content,main_loader,landing_backend,landing_pageobject,landing_ui_button_sidebarbutton,landing_loc,landing_ui_form_formsettingsform,landing_ui_button_basebutton,landing_ui_field_textfield,landing_ui_panel_formsettingspanel,crm_form_client,main_core_zIndexManager) {
	'use strict';

	var _templateObject, _templateObject2, _templateObject3;
	function ownKeys(object, enumerableOnly) { var keys = Object.keys(object); if (Object.getOwnPropertySymbols) { var symbols = Object.getOwnPropertySymbols(object); enumerableOnly && (symbols = symbols.filter(function (sym) { return Object.getOwnPropertyDescriptor(object, sym).enumerable; })), keys.push.apply(keys, symbols); } return keys; }
	function _objectSpread(target) { for (var i = 1; i < arguments.length; i++) { var source = null != arguments[i] ? arguments[i] : {}; i % 2 ? ownKeys(Object(source), !0).forEach(function (key) { babelHelpers.defineProperty(target, key, source[key]); }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(target, Object.getOwnPropertyDescriptors(source)) : ownKeys(Object(source)).forEach(function (key) { Object.defineProperty(target, key, Object.getOwnPropertyDescriptor(source, key)); }); } return target; }
	function _classPrivateMethodInitSpec(obj, privateSet) { _checkPrivateRedeclaration(obj, privateSet); privateSet.add(obj); }
	function _checkPrivateRedeclaration(obj, privateCollection) { if (privateCollection.has(obj)) { throw new TypeError("Cannot initialize the same private elements twice on an object"); } }
	function _classPrivateMethodGet(receiver, privateSet, fn) { if (!privateSet.has(receiver)) { throw new TypeError("attempted to get private field on non-instance"); } return fn; }
	var _setShowLock = /*#__PURE__*/new WeakSet();
	var _getShowLock = /*#__PURE__*/new WeakSet();
	var _setHideLock = /*#__PURE__*/new WeakSet();
	var _getHideLock = /*#__PURE__*/new WeakSet();
	/**
	 * @memberOf BX.Landing.UI.Panel
	 */
	var FieldsPanel = /*#__PURE__*/function (_Content) {
	  babelHelpers.inherits(FieldsPanel, _Content);
	  babelHelpers.createClass(FieldsPanel, null, [{
	    key: "isEditorContext",
	    value: function isEditorContext() {
	      return FieldsPanel.staticCache.remember('isEditorContext', function () {
	        var rootWindow = landing_pageobject.PageObject.getRootWindow();
	        var viewContainer = rootWindow.document.body.querySelector('.landing-ui-view');
	        return main_core.Type.isDomNode(viewContainer);
	      });
	    }
	  }, {
	    key: "getRootWindow",
	    value: function getRootWindow() {
	      return FieldsPanel.staticCache.remember('rootWindow', function () {
	        if (FieldsPanel.isEditorContext()) {
	          return landing_pageobject.PageObject.getRootWindow();
	        }
	        return window;
	      });
	    }
	  }, {
	    key: "getInstance",
	    value: function getInstance(options) {
	      var rootWindow = FieldsPanel.getRootWindow();
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
	    _classPrivateMethodInitSpec(babelHelpers.assertThisInitialized(_this), _getHideLock);
	    _classPrivateMethodInitSpec(babelHelpers.assertThisInitialized(_this), _setHideLock);
	    _classPrivateMethodInitSpec(babelHelpers.assertThisInitialized(_this), _getShowLock);
	    _classPrivateMethodInitSpec(babelHelpers.assertThisInitialized(_this), _setShowLock);
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
	    main_core.Dom.insertAfter(_this.getSearchContainer(), _this.header);
	    main_core.Dom.append(_this.getCreateFieldLayout(), _this.body);
	    _this.appendFooterButton(new landing_ui_button_basebutton.BaseButton('save_settings', {
	      text: landing_loc.Loc.getMessage('LANDING_FIELDS_PANEL_ADD_SELECTED_BUTTON'),
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
	    return _this;
	  }
	  babelHelpers.createClass(FieldsPanel, [{
	    key: "isMultiple",
	    value: function isMultiple() {
	      return this.cache.get('multiple', true);
	    }
	  }, {
	    key: "setMultiple",
	    value: function setMultiple(mode) {
	      this.cache.set('multiple', mode);
	    }
	  }, {
	    key: "setAllowedTypes",
	    value: function setAllowedTypes(types) {
	      this.cache.set('allowedTypes', types);
	    }
	  }, {
	    key: "getAllowedTypes",
	    value: function getAllowedTypes() {
	      return this.cache.get('allowedTypes', []);
	    }
	  }, {
	    key: "setDisabledFields",
	    value: function setDisabledFields(fields) {
	      this.cache.set('disabledFields', fields);
	    }
	  }, {
	    key: "getDisabledFields",
	    value: function getDisabledFields() {
	      return this.cache.get('disabledFields', []);
	    }
	  }, {
	    key: "setAllowedCategories",
	    value: function setAllowedCategories(categories) {
	      this.cache.set('allowedCategories', categories);
	    }
	  }, {
	    key: "getAllowedCategories",
	    value: function getAllowedCategories() {
	      return this.cache.get('allowedCategories', []);
	    }
	  }, {
	    key: "setDisabledCategories",
	    value: function setDisabledCategories(categories) {
	      this.cache.set('disabledCategories', categories);
	    }
	  }, {
	    key: "getDisabledCategories",
	    value: function getDisabledCategories() {
	      return this.cache.get('disabledCategories', []);
	    }
	  }, {
	    key: "resetFactoriesCache",
	    value: function resetFactoriesCache() {
	      var _this2 = this;
	      this.cache.keys().forEach(function (key) {
	        if (key.startsWith('userFieldFactory_')) {
	          _this2.cache["delete"](key);
	        }
	      });
	    }
	  }, {
	    key: "setLoadOptions",
	    value: function setLoadOptions(options) {
	      this.cache.set('loadOptions', _objectSpread({}, options));
	    }
	  }, {
	    key: "getLoadOptions",
	    value: function getLoadOptions() {
	      return this.cache.get('loadOptions', {});
	    }
	  }, {
	    key: "show",
	    value: function show() {
	      var _options$position,
	        _this3 = this;
	      var options = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};
	      if (_classPrivateMethodGet(this, _getShowLock, _getShowLock2).call(this)) {
	        return Promise.resolve();
	      }
	      _classPrivateMethodGet(this, _setShowLock, _setShowLock2).call(this, true);
	      this.getSearchField().input.textContent = '';
	      this.setMultiple(true);
	      this.setAllowedTypes([]);
	      this.setDisabledFields([]);
	      this.setAllowedCategories([]);
	      this.setDisabledCategories([]);
	      this.resetFactoriesCache();
	      if (main_core.Type.isArrayFilled(options.disabledFields)) {
	        this.setDisabledFields(options.disabledFields);
	      }
	      if (main_core.Type.isArrayFilled(options.allowedCategories)) {
	        this.setAllowedCategories(options.allowedCategories);
	      }
	      if (main_core.Type.isArrayFilled(options.disabledCategories)) {
	        this.setDisabledCategories(options.disabledCategories);
	      }
	      if (main_core.Type.isArrayFilled(options.allowedTypes)) {
	        this.setAllowedTypes(options.allowedTypes);
	      }
	      if (main_core.Type.isBoolean(options.multiple)) {
	        this.setMultiple(options.multiple);
	      }
	      main_core.Dom.style(this.layout, 'position', (_options$position = options.position) !== null && _options$position !== void 0 ? _options$position : null);
	      var allowedLoadOptions = ['hideVirtual', 'hideRequisites', 'hideSmartDocument', 'presetId'];
	      var loadOptions = Object.entries(options).reduce(function (acc, _ref) {
	        var _ref2 = babelHelpers.slicedToArray(_ref, 2),
	          key = _ref2[0],
	          value = _ref2[1];
	        if (allowedLoadOptions.includes(key)) {
	          acc[key] = value;
	        }
	        return acc;
	      }, {});
	      this.setLoadOptions(loadOptions);
	      this.showLoader();
	      this.load(loadOptions).then(function () {
	        _this3.hideLoader();
	        _this3.clearSidebar();
	        Object.entries(_this3.getCrmFields()).forEach(function (_ref3) {
	          var _ref4 = babelHelpers.slicedToArray(_ref3, 2),
	            categoryId = _ref4[0],
	            category = _ref4[1];
	          if (categoryId !== 'CATALOG' && categoryId !== 'ACTIVITY' && categoryId !== 'INVOICE') {
	            if (main_core.Type.isPlainObject(_this3.options) && main_core.Type.isBoolean(_this3.options.isLeadEnabled) && !_this3.options.isLeadEnabled && categoryId === 'LEAD') {
	              return;
	            }
	            var button = new landing_ui_button_sidebarbutton.SidebarButton({
	              id: categoryId,
	              text: category.CAPTION,
	              child: true,
	              onClick: function onClick() {
	                _this3.onSidebarButtonClick(button);
	              }
	            });
	            _this3.appendSidebarButton(button);
	          }
	        });
	      }).then(function () {
	        var filteredFieldsTree = _this3.getFilteredFieldsTree();
	        var categories = Object.keys(filteredFieldsTree);
	        _this3.sidebarButtons.forEach(function (button) {
	          button.deactivate();
	          if (categories.includes(button.id)) {
	            main_core.Dom.show(button.getLayout());
	          } else {
	            main_core.Dom.hide(button.getLayout());
	          }
	        });
	        if (_this3.sidebarButtons.length > 0) {
	          _this3.resetState();
	          var firstShowedButton = _this3.sidebarButtons.find(function (button) {
	            return button.getLayout().hidden !== true;
	          });
	          if (firstShowedButton) {
	            firstShowedButton.getLayout().click();
	          }
	        }
	      });
	      main_core.Dom.append(this.overlay, this.layout.parentElement);
	      babelHelpers.get(babelHelpers.getPrototypeOf(FieldsPanel.prototype), "show", this).call(this, options).then(function () {
	        _classPrivateMethodGet(_this3, _setShowLock, _setShowLock2).call(_this3, false);
	        _this3.getSearchField().enableEdit();
	        _this3.getSearchField().input.focus();
	      });
	      return new Promise(function (resolve) {
	        _this3.promiseResolver = resolve;
	      });
	    }
	  }, {
	    key: "hide",
	    value: function hide() {
	      this.setCrmFields(this.getOriginalCrmFields());
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
	        if (FieldsPanel.isEditorContext()) {
	          var rootWindow = FieldsPanel.getRootWindow();
	          return rootWindow.document.querySelector('.landing-ui-view-container');
	        }
	        return document.body;
	      });
	    }
	  }, {
	    key: "getLoader",
	    value: function getLoader() {
	      var _this4 = this;
	      return this.cache.remember('loader', function () {
	        return new main_loader.Loader({
	          target: _this4.body
	        });
	      });
	    }
	  }, {
	    key: "showLoader",
	    value: function showLoader() {
	      this.hideCreateFieldButton();
	      void this.getLoader().show();
	    }
	  }, {
	    key: "hideLoader",
	    value: function hideLoader() {
	      this.showCreateFieldButton();
	      void this.getLoader().hide();
	    }
	  }, {
	    key: "setHideVirtual",
	    value: function setHideVirtual(value) {
	      this.cache.set('hideVirtual', value);
	    }
	  }, {
	    key: "getHideVirtual",
	    value: function getHideVirtual() {
	      return this.cache.get('hideVirtual', null);
	    }
	  }, {
	    key: "setHideRequisites",
	    value: function setHideRequisites(value) {
	      this.cache.set('hideRequisites', value);
	    }
	  }, {
	    key: "getHideRequisites",
	    value: function getHideRequisites() {
	      return this.cache.get('hideRequisites', null);
	    }
	  }, {
	    key: "setHideSmartDocuments",
	    value: function setHideSmartDocuments(value) {
	      this.cache.set('hideSmartDocument', value);
	    }
	  }, {
	    key: "getHideSmartDocuments",
	    value: function getHideSmartDocuments() {
	      return this.cache.get('hideSmartDocument', true);
	    }
	  }, {
	    key: "load",
	    value: function load() {
	      var _this5 = this;
	      var options = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};
	      return landing_backend.Backend.getInstance().action('Form::getCrmFields', {
	        options: options
	      }).then(function (result) {
	        _this5.setOriginalCrmFields(result);
	        _this5.setCrmFields(result);
	        if (FieldsPanel.isEditorContext()) {
	          Object.assign(landing_ui_panel_formsettingspanel.FormSettingsPanel.getInstance().getCrmFields(), result);
	        }
	        return crm_form_client.FormClient.getInstance().getDictionary().then(function (dictionary) {
	          _this5.setFormDictionary(dictionary);
	        });
	      });
	    }
	  }, {
	    key: "setFormDictionary",
	    value: function setFormDictionary(dictionary) {
	      this.cache.set('formDictionary', dictionary);
	    }
	  }, {
	    key: "getFormDictionary",
	    value: function getFormDictionary() {
	      return this.cache.get('formDictionary', {});
	    }
	  }, {
	    key: "setOriginalCrmFields",
	    value: function setOriginalCrmFields(fields) {
	      this.cache.set('originalFields', fields);
	    }
	  }, {
	    key: "getOriginalCrmFields",
	    value: function getOriginalCrmFields() {
	      return this.cache.get('originalFields') || {};
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
	      this.cache["delete"]('state');
	    }
	  }, {
	    key: "onSidebarButtonClick",
	    value: function onSidebarButtonClick(button) {
	      var activeButton = this.sidebarButtons.getActive();
	      if (activeButton) {
	        activeButton.deactivate();
	      }
	      button.activate();
	      var hideCreateButton = this.getAllowedTypes().every(function (type) {
	        return main_core.Type.isPlainObject(type);
	      });
	      if (main_core.Type.isArrayFilled(this.getAllowedTypes()) && hideCreateButton) {
	        this.hideCreateFieldButton();
	      } else {
	        this.showCreateFieldButton();
	      }
	      var crmFields = this.getCrmFields();
	      if (Reflect.has(crmFields, button.id)) {
	        this.clearContent();
	        var form = this.createFieldsListForm(button.id);
	        this.appendForm(form);
	      }
	    }
	  }, {
	    key: "getFilteredFieldsTree",
	    value: function getFilteredFieldsTree() {
	      var searchString = String(this.getSearchField().getValue()).toLowerCase().trim();
	      var allowedCategories = this.getAllowedCategories();
	      var disabledCategories = this.getDisabledCategories();
	      var allowedTypes = this.getAllowedTypes();
	      return Object.entries(this.getCrmFields()).reduce(function (acc, _ref5) {
	        var _ref6 = babelHelpers.slicedToArray(_ref5, 2),
	          categoryId = _ref6[0],
	          category = _ref6[1];
	        if (categoryId !== 'CATALOG' && categoryId !== 'ACTIVITY' && categoryId !== 'INVOICE' && (!main_core.Type.isArrayFilled(allowedCategories) || allowedCategories.includes(categoryId)) && !disabledCategories.includes(categoryId)) {
	          var filteredFields = category.FIELDS.filter(function (field) {
	            if (field.name === 'CONTACT_ORIGIN_VERSION' || field.name === 'CONTACT_LINK') {
	              return false;
	            }
	            var fieldCaption = String(field.caption).toLowerCase().trim();
	            if (main_core.Type.isArrayFilled(allowedTypes)) {
	              var isTypeAllowed = allowedTypes.some(function (allowedType) {
	                if (!main_core.Type.isPlainObject(allowedType)) {
	                  allowedType = {
	                    type: allowedType
	                  };
	                }
	                if (allowedType.entityFieldName && allowedType.entityFieldName !== field.entity_field_name) {
	                  return false;
	                }
	                if (main_core.Type.isBoolean(allowedType.multiple) && allowedType.multiple !== field.multiple) {
	                  return false;
	                }
	                return field.type === allowedType.type;
	              });
	              if (!isTypeAllowed) {
	                return false;
	              }
	            }
	            return !main_core.Type.isStringFilled(searchString) || fieldCaption.includes(searchString);
	          });
	          if (main_core.Type.isArrayFilled(filteredFields)) {
	            acc[categoryId] = _objectSpread(_objectSpread({}, category), {}, {
	              FIELDS: filteredFields
	            });
	          }
	        }
	        return acc;
	      }, {});
	    }
	  }, {
	    key: "createFieldsListForm",
	    value: function createFieldsListForm(category) {
	      var _this6 = this;
	      var fieldsListTree = this.getFilteredFieldsTree();
	      var disabledFields = this.getDisabledFields();
	      var fieldOptions = {
	        items: fieldsListTree[category].FIELDS.map(function (field) {
	          return {
	            name: field.caption,
	            value: field.name,
	            disabled: main_core.Type.isArrayFilled(disabledFields) && disabledFields.includes(field.name)
	          };
	        }),
	        value: this.getState()[category] || [],
	        onValueChange: function onValueChange(checkbox) {
	          var state = _objectSpread({}, _this6.getState());
	          state[category] = checkbox.getValue();
	          _this6.setState(state);
	        }
	      };
	      return new landing_ui_form_formsettingsform.FormSettingsForm({
	        fields: [this.isMultiple() ? new BX.Landing.UI.Field.Checkbox(fieldOptions) : new BX.Landing.UI.Field.Radio(fieldOptions)]
	      });
	    }
	  }, {
	    key: "onSearchChange",
	    value: function onSearchChange() {
	      var filteredFieldsTree = this.getFilteredFieldsTree();
	      var categories = Object.keys(filteredFieldsTree);
	      this.sidebarButtons.forEach(function (button) {
	        button.deactivate();
	        if (categories.includes(button.id)) {
	          main_core.Dom.show(button.getLayout());
	        } else {
	          main_core.Dom.hide(button.getLayout());
	        }
	      });
	      this.clearContent();
	      var firstCategory = categories[0];
	      if (firstCategory) {
	        var firstCategoryButton = this.sidebarButtons.get(firstCategory);
	        if (firstCategoryButton) {
	          firstCategoryButton.activate();
	        }
	        var form = this.createFieldsListForm(firstCategory);
	        this.showCreateFieldButton();
	        this.appendForm(form);
	      } else {
	        this.hideCreateFieldButton();
	      }
	    }
	  }, {
	    key: "getSearchField",
	    value: function getSearchField() {
	      var _this7 = this;
	      return this.cache.remember('searchField', function () {
	        var rootWindow = FieldsPanel.getRootWindow();
	        return new rootWindow.BX.Landing.UI.Field.Text({
	          selector: 'search',
	          textOnly: true,
	          placeholder: landing_loc.Loc.getMessage('LANDING_FIELDS_PANEL_SEARCH'),
	          onChange: _this7.onSearchChange.bind(_this7)
	        });
	      });
	    }
	  }, {
	    key: "getSearchContainer",
	    value: function getSearchContainer() {
	      var _this8 = this;
	      return this.cache.remember('searchLayout', function () {
	        return main_core.Tag.render(_templateObject || (_templateObject = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"landing-ui-panel-content-element landing-ui-panel-content-search\">\n\t\t\t\t\t", "\n\t\t\t\t\t<div class=\"landing-ui-panel-content-search-icon\"></div>\n\t\t\t\t</div>\n\t\t\t"])), _this8.getSearchField().getLayout());
	      });
	    }
	  }, {
	    key: "getUserFieldFactory",
	    value: function getUserFieldFactory(entityId) {
	      var _this9 = this;
	      var factory = this.cache.remember("userFieldFactory_".concat(entityId), function () {
	        var rootWindow = window.top;
	        var preparedEntityId = function () {
	          if (entityId.startsWith('DYNAMIC_')) {
	            return _this9.getCrmFields()[entityId].DYNAMIC_ID;
	          }
	          return "CRM_".concat(entityId);
	        }();
	        var Factory = function () {
	          if (rootWindow.BX.UI.UserFieldFactory) {
	            return rootWindow.BX.UI.UserFieldFactory.Factory;
	          }
	          return BX.UI.UserFieldFactory.Factory;
	        }();
	        return new Factory(preparedEntityId, {
	          moduleId: 'crm',
	          bindElement: _this9.getCreateFieldButton()
	        });
	      });
	      if (main_core.Type.isArrayFilled(this.getAllowedTypes())) {
	        factory.types = factory.types.filter(function (type) {
	          return _this9.getAllowedTypes().includes(type.name);
	        });
	      } else {
	        factory.types = factory.types.filter(function (type) {
	          return type.name !== 'employee';
	        });
	      }
	      return factory;
	    }
	  }, {
	    key: "onCreateFieldClick",
	    value: function onCreateFieldClick(event) {
	      var _this10 = this;
	      event.preventDefault();
	      var dictionary = this.getFormDictionary();
	      if (main_core.Type.isPlainObject(dictionary.permissions) && main_core.Type.isPlainObject(dictionary.permissions.userField) && dictionary.permissions.userField.add === false) {
	        var rootWindow = FieldsPanel.getRootWindow();
	        rootWindow.BX.UI.Dialogs.MessageBox.alert(landing_loc.Loc.getMessage('LANDING_FORM_ADD_USER_FIELD_PERMISSION_DENIED'));
	        return;
	      }
	      var activeButton = this.sidebarButtons.getActive();
	      var currentCategoryId = activeButton.id;
	      var factory = this.getUserFieldFactory(currentCategoryId);
	      var menu = factory.getMenu();
	      menu.open(function (type) {
	        var configurator = factory.getConfigurator({
	          userField: factory.createUserField(type),
	          onSave: function onSave(userField) {
	            userField.save().then(function () {
	              return _this10.load(_this10.getLoadOptions());
	            }).then(function () {
	              _this10.getSearchField().setValue(userField.getData().editFormLabel[landing_loc.Loc.getMessage('LANGUAGE_ID')]);
	              _this10.showCreateFieldButton();
	            });
	          },
	          onCancel: function onCancel() {
	            _this10.showCreateFieldButton();
	            _this10.sidebarButtons.getActive().getLayout().click();
	          }
	        });
	        _this10.clearContent();
	        main_core.Dom.append(configurator.render(), _this10.content);
	        _this10.hideCreateFieldButton();
	      });
	      main_core.Dom.style(menu.getPopup().getPopupContainer(), 'z-index', 9999);
	    }
	  }, {
	    key: "getCreateFieldButton",
	    value: function getCreateFieldButton() {
	      var _this11 = this;
	      return this.cache.remember('getCreateFieldButton', function () {
	        return main_core.Tag.render(_templateObject2 || (_templateObject2 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div\n\t\t\t\t\tclass=\"landing-ui-panel-content-create-field-button\"\n\t\t\t\t\tonclick=\"", "\"\n\t\t\t\t>\n\t\t\t\t\t", "\n\t\t\t\t</div>\n\t\t\t"])), _this11.onCreateFieldClick.bind(_this11), landing_loc.Loc.getMessage('LANDING_FIELDS_PANEL_CREATE_FIELD'));
	      });
	    }
	  }, {
	    key: "getCreateFieldLayout",
	    value: function getCreateFieldLayout() {
	      var _this12 = this;
	      return this.cache.remember('createFieldLayout', function () {
	        return main_core.Tag.render(_templateObject3 || (_templateObject3 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"landing-ui-panel-content-create-field\">\n\t\t\t\t\t", "\n\t\t\t\t</div>\n\t\t\t"])), _this12.getCreateFieldButton());
	      });
	    }
	  }, {
	    key: "isUserFieldEditorShowed",
	    value: function isUserFieldEditorShowed() {
	      return main_core.Type.isDomNode(this.content.querySelector('.ui-userfieldfactory-configurator'));
	    }
	  }, {
	    key: "showCreateFieldButton",
	    value: function showCreateFieldButton() {
	      main_core.Dom.append(this.getCreateFieldLayout(), this.body);
	    }
	  }, {
	    key: "hideCreateFieldButton",
	    value: function hideCreateFieldButton() {
	      main_core.Dom.remove(this.getCreateFieldLayout(), this.body);
	    }
	  }]);
	  return FieldsPanel;
	}(landing_ui_panel_content.Content);
	function _setShowLock2(value) {
	  this.cache.set('showLock', value);
	}
	function _getShowLock2() {
	  return this.cache.get('showLock', false);
	}
	babelHelpers.defineProperty(FieldsPanel, "staticCache", new main_core.Cache.MemoryCache());

	exports.FieldsPanel = FieldsPanel;

}((this.BX.Landing.UI.Panel = this.BX.Landing.UI.Panel || {}),BX,BX.Landing.UI.Panel,BX,BX.Landing,BX.Landing,BX.Landing.UI.Button,BX.Landing,BX.Landing.UI.Form,BX.Landing.UI.Button,BX.Landing.UI.Field,BX.Landing.UI.Panel,BX.Crm.Form,BX));
//# sourceMappingURL=fieldspanel.bundle.js.map
