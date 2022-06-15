this.BX = this.BX || {};
this.BX.Catalog = this.BX.Catalog || {};
(function (exports,ui_entityEditor,ui_notification,ui_hint,translit,main_core_events,main_popup,main_core,catalog_storeUse) {
	'use strict';

	function ownKeys(object, enumerableOnly) { var keys = Object.keys(object); if (Object.getOwnPropertySymbols) { var symbols = Object.getOwnPropertySymbols(object); enumerableOnly && (symbols = symbols.filter(function (sym) { return Object.getOwnPropertyDescriptor(object, sym).enumerable; })), keys.push.apply(keys, symbols); } return keys; }

	function _objectSpread(target) { for (var i = 1; i < arguments.length; i++) { var source = null != arguments[i] ? arguments[i] : {}; i % 2 ? ownKeys(Object(source), !0).forEach(function (key) { babelHelpers.defineProperty(target, key, source[key]); }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(target, Object.getOwnPropertyDescriptors(source)) : ownKeys(Object(source)).forEach(function (key) { Object.defineProperty(target, key, Object.getOwnPropertyDescriptor(source, key)); }); } return target; }

	var LazyLoader = /*#__PURE__*/function () {
	  function LazyLoader(id, settings) {
	    babelHelpers.classCallCheck(this, LazyLoader);
	    this.id = main_core.Type.isStringFilled(id) ? id : main_core.Text.getRandom();
	    this.settings = main_core.Type.isObjectLike(settings) ? settings : {};
	    this.container = this.settings.container;

	    if (!this.container) {
	      throw 'Error: Could not find container.';
	    }

	    this.serviceUrl = this.settings.serviceUrl || '';

	    if (!main_core.Type.isStringFilled(this.serviceUrl)) {
	      throw 'Error. Could not find service url.';
	    }

	    this.tabId = this.settings.tabId || '';

	    if (!main_core.Type.isStringFilled(this.tabId)) {
	      throw 'Error: Could not find tab id.';
	    }

	    this.params = main_core.Type.isObjectLike(this.settings.componentData) ? this.settings.componentData : {};
	    this.isRequestRunning = false;
	    this.loaded = false;
	  }

	  babelHelpers.createClass(LazyLoader, [{
	    key: "isLoaded",
	    value: function isLoaded() {
	      return this.loaded;
	    }
	  }, {
	    key: "load",
	    value: function load() {
	      if (!this.isLoaded()) {
	        this.startRequest(_objectSpread(_objectSpread({}, this.params), {
	          'TABID': this.tabId
	        }));
	      }
	    }
	  }, {
	    key: "startRequest",
	    value: function startRequest(params) {
	      if (this.isRequestRunning) {
	        return false;
	      }

	      this.isRequestRunning = true;
	      BX.ajax({
	        url: this.serviceUrl,
	        method: 'POST',
	        dataType: 'html',
	        data: {
	          'LOADERID': this.id,
	          'PARAMS': params
	        },
	        onsuccess: this.onRequestSuccess.bind(this),
	        onfailure: this.onRequestFailure.bind(this)
	      });
	      return true;
	    }
	  }, {
	    key: "onRequestSuccess",
	    value: function onRequestSuccess(data) {
	      this.isRequestRunning = false;
	      this.container.innerHTML = data;
	      this.loaded = true;
	    }
	  }, {
	    key: "onRequestFailure",
	    value: function onRequestFailure() {
	      this.isRequestRunning = false;
	      this.loaded = true;
	    }
	  }]);
	  return LazyLoader;
	}();

	function ownKeys$1(object, enumerableOnly) { var keys = Object.keys(object); if (Object.getOwnPropertySymbols) { var symbols = Object.getOwnPropertySymbols(object); enumerableOnly && (symbols = symbols.filter(function (sym) { return Object.getOwnPropertyDescriptor(object, sym).enumerable; })), keys.push.apply(keys, symbols); } return keys; }

	function _objectSpread$1(target) { for (var i = 1; i < arguments.length; i++) { var source = null != arguments[i] ? arguments[i] : {}; i % 2 ? ownKeys$1(Object(source), !0).forEach(function (key) { babelHelpers.defineProperty(target, key, source[key]); }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(target, Object.getOwnPropertyDescriptors(source)) : ownKeys$1(Object(source)).forEach(function (key) { Object.defineProperty(target, key, Object.getOwnPropertyDescriptor(source, key)); }); } return target; }

	var Tab = /*#__PURE__*/function () {
	  function Tab(id, settings) {
	    babelHelpers.classCallCheck(this, Tab);
	    this.id = main_core.Type.isStringFilled(id) ? id : main_core.Text.getRandom();
	    this.settings = main_core.Type.isObjectLike(settings) ? settings : {};
	    this.data = main_core.Type.isObjectLike(this.settings.data) ? this.settings.data : {};
	    this.manager = settings.manager || null;
	    this.container = this.settings.container;
	    this.menuContainer = this.settings.menuContainer;
	    this.active = main_core.Type.isBoolean(this.data.active) ? this.data.active : false;
	    this.enabled = main_core.Type.isBoolean(this.data.enabled) ? this.data.enabled : true;
	    main_core.Event.bind(this.menuContainer.querySelector('a.catalog-entity-section-tab-link'), 'click', this.onMenuClick.bind(this));
	    this.loader = null;

	    if (main_core.Type.isObjectLike(this.data.loader)) {
	      this.loader = new LazyLoader(this.id, _objectSpread$1(_objectSpread$1({}, this.data.loader), {
	        tabId: this.id,
	        container: this.container
	      }));
	    }
	  }

	  babelHelpers.createClass(Tab, [{
	    key: "isEnabled",
	    value: function isEnabled() {
	      return this.enabled;
	    }
	  }, {
	    key: "isActive",
	    value: function isActive() {
	      return this.active;
	    }
	  }, {
	    key: "setActive",
	    value: function setActive(active) {
	      active = !!active;

	      if (this.isActive() === active) {
	        return;
	      }

	      this.active = active;

	      if (this.isActive()) {
	        this.showTab();
	      } else {
	        this.hideTab();
	      }
	    }
	  }, {
	    key: "showTab",
	    value: function showTab() {
	      var _this = this;

	      main_core.Dom.addClass(this.container, 'catalog-entity-section-tab-content-show');
	      main_core.Dom.removeClass(this.container, 'catalog-entity-section-tab-content-hide');
	      main_core.Dom.addClass(this.menuContainer, 'catalog-entity-section-tab-current');
	      this.container.style.display = '';
	      this.container.style.position = 'absolute';
	      this.container.style.top = 0;
	      this.container.style.left = 0;
	      this.container.style.width = '100%';
	      new BX.easing({
	        duration: 350,
	        start: {
	          opacity: 0,
	          translateX: 100
	        },
	        finish: {
	          opacity: 100,
	          translateX: 0
	        },
	        transition: BX.easing.makeEaseOut(BX.easing.transitions.quart),
	        step: function step(state) {
	          _this.container.style.opacity = state.opacity / 100;
	          _this.container.style.transform = 'translateX(' + state.translateX + '%)';
	        },
	        complete: function complete() {
	          main_core.Dom.removeClass(_this.container, 'catalog-entity-section-tab-content-show');
	          _this.container.style.cssText = '';
	          main_core.Event.EventEmitter.emit(window, 'onEntityDetailsTabShow', [_this]);
	        }
	      }).animate();
	    }
	  }, {
	    key: "hideTab",
	    value: function hideTab() {
	      var _this2 = this;

	      main_core.Dom.addClass(this.container, 'catalog-entity-section-tab-content-hide');
	      main_core.Dom.removeClass(this.container, 'catalog-entity-section-tab-content-show');
	      main_core.Dom.removeClass(this.menuContainer, 'catalog-entity-section-tab-current');
	      new BX.easing({
	        duration: 350,
	        start: {
	          opacity: 100
	        },
	        finish: {
	          opacity: 0
	        },
	        transition: BX.easing.makeEaseOut(BX.easing.transitions.quart),
	        step: function step(state) {
	          _this2.container.style.opacity = state.opacity / 100;
	        },
	        complete: function complete() {
	          _this2.container.style.display = 'none';
	          _this2.container.style.transform = 'translateX(100%)';
	          _this2.container.style.opacity = 0;
	        }
	      }).animate();
	    }
	  }, {
	    key: "onMenuClick",
	    value: function onMenuClick(event) {
	      if (this.isEnabled()) {
	        if (this.loader && !this.loader.isLoaded()) {
	          this.loader.load();
	        }

	        this.manager.selectItem(this);
	      }

	      event.preventDefault();
	    }
	  }]);
	  return Tab;
	}();

	var Manager = /*#__PURE__*/function () {
	  function Manager(id, settings) {
	    var _this = this;

	    babelHelpers.classCallCheck(this, Manager);
	    this.id = main_core.Type.isStringFilled(id) ? id : main_core.Text.getRandom();
	    this.settings = main_core.Type.isObjectLike(settings) ? settings : {};
	    this.container = this.settings.container;
	    this.menuContainer = this.settings.menuContainer;
	    this.items = [];

	    if (main_core.Type.isArray(this.settings.data)) {
	      this.settings.data.forEach(function (item) {
	        _this.items.push(new Tab(item.id, {
	          manager: _this,
	          data: item,
	          container: _this.container.querySelector('[data-tab-id="' + item.id + '"]'),
	          menuContainer: _this.menuContainer.querySelector('[data-tab-id="' + item.id + '"]')
	        }));
	      });
	    }

	    main_core_events.EventEmitter.subscribe('BX.Catalog.EntityCard.TabManager:onOpenTab', function (event) {
	      var tabId = event.data.tabId;

	      var item = _this.findItemById(tabId);

	      if (item) {
	        _this.selectItem(item);
	      }
	    });
	  }

	  babelHelpers.createClass(Manager, [{
	    key: "findItemById",
	    value: function findItemById(id) {
	      return this.items.find(function (item) {
	        return item.id === id;
	      }) || null;
	    }
	  }, {
	    key: "selectItem",
	    value: function selectItem(item) {
	      main_core_events.EventEmitter.emit('BX.Catalog.EntityCard.TabManager:onSelectItem', {
	        tabId: item.id
	      });
	      this.items.forEach(function (current) {
	        return current.setActive(current === item);
	      });
	    }
	  }]);
	  return Manager;
	}();

	var _templateObject, _templateObject2, _templateObject3, _templateObject4, _templateObject5, _templateObject6;

	var IblockSectionField = /*#__PURE__*/function (_BX$UI$EntityEditorFi) {
	  babelHelpers.inherits(IblockSectionField, _BX$UI$EntityEditorFi);

	  function IblockSectionField(id, settings) {
	    var _this;

	    babelHelpers.classCallCheck(this, IblockSectionField);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(IblockSectionField).call(this));

	    _this.initialize(id, settings);

	    _this.innerWrapper = null;
	    return _this;
	  }

	  babelHelpers.createClass(IblockSectionField, [{
	    key: "getContentWrapper",
	    value: function getContentWrapper() {
	      return this.innerWrapper;
	    }
	  }, {
	    key: "layout",
	    value: function layout() {
	      var options = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};

	      if (this._hasLayout) {
	        return;
	      }

	      this.ensureWrapperCreated({
	        classNames: ['catalog-entity-editor-content-block-field-iblock-section']
	      });
	      this.adjustWrapper();

	      if (this.isNeedToDisplay()) {
	        this._wrapper.appendChild(this.createTitleNode(this.getTitle()));

	        if (this._mode === BX.UI.EntityEditorMode.edit) {
	          this.drawEditMode();
	        } else {
	          this.drawViewMode();
	        }

	        if (this.isContextMenuEnabled()) {
	          this._wrapper.appendChild(this.createContextMenuButton());
	        }
	      }

	      this.registerLayout(options);
	      this._hasLayout = true;
	    }
	  }, {
	    key: "drawEditMode",
	    value: function drawEditMode() {
	      this.defaultInput = main_core.Tag.render(_templateObject || (_templateObject = babelHelpers.taggedTemplateLiteral(["<input type=\"hidden\" name=\"", "[]\" value=\"0\">"])), this.getName());

	      this._wrapper.appendChild(this.defaultInput);

	      this.innerWrapper = main_core.Tag.render(_templateObject2 || (_templateObject2 = babelHelpers.taggedTemplateLiteral(["<div class=\"ui-entity-editor-content-block\"></div>"])));

	      this._wrapper.appendChild(this.innerWrapper);

	      main_core.ajax.runComponentAction('bitrix:catalog.productcard.iblocksectionfield', 'lazyLoad', {
	        mode: 'ajax',
	        data: {
	          iblockId: this.getIblockId(),
	          productId: this.getProductId(),
	          selectedSectionIds: this.getValue()
	        }
	      }).then(this.renderFromResponse.bind(this))["catch"](function (response) {
	        throw new Error(response.errors.join("\n"));
	      });
	    }
	  }, {
	    key: "renderFromResponse",
	    value: function renderFromResponse(response) {
	      if (!this._wrapper) {
	        return;
	      }

	      main_core.Runtime.html(this.innerWrapper, response.data.html, {
	        callback: this.initEntitySelector.bind(this)
	      });
	    }
	  }, {
	    key: "initEntitySelector",
	    value: function initEntitySelector() {
	      main_core_events.EventEmitter.subscribe(main_core_events.EventEmitter.GLOBAL_TARGET, 'Item:onSelect', this.markAsChanged.bind(this));
	      main_core_events.EventEmitter.subscribe(main_core_events.EventEmitter.GLOBAL_TARGET, 'Item:onDeselect', this.markAsChanged.bind(this));
	    }
	  }, {
	    key: "changeDisplay",
	    value: function changeDisplay(node, isShow) {
	      if (!node) {
	        return;
	      }

	      node.style.display = isShow ? '' : 'none';
	    }
	  }, {
	    key: "markAsChanged",
	    value: function markAsChanged(event) {
	      babelHelpers.get(babelHelpers.getPrototypeOf(IblockSectionField.prototype), "markAsChanged", this).call(this);
	      main_core_events.EventEmitter.emit(this.getEditor(), 'IblockSectionField:onChange', [this].concat(babelHelpers.toConsumableArray(event.getData())));
	    }
	  }, {
	    key: "drawViewMode",
	    value: function drawViewMode() {
	      if (this.hasNoSections()) {
	        this.innerWrapper = main_core.Tag.render(_templateObject3 || (_templateObject3 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"ui-entity-editor-content-block\">\n\t\t\t\t\t", "\n\t\t\t\t</div>\n\t\t\t"])), main_core.Loc.getMessage("CATALOG_ENTITY_CARD_EMPTY_SECTION"));
	        main_core.Dom.addClass(this._wrapper, 'ui-entity-editor-content-block-click-empty');
	      } else {
	        var content = [];
	        this.getSections().forEach(function (section) {
	          // ui-tile-selector-item-%type%
	          var picture = '';

	          if (main_core.Type.isStringFilled(section.PICTURE)) {
	            picture = main_core.Tag.render(_templateObject4 || (_templateObject4 = babelHelpers.taggedTemplateLiteral(["<span class=\"ui-tile-selector-item-picture\" style=\"background-image: url('", "');\"></span>"])), main_core.Text.encode(section.PICTURE));
	          }

	          content.push(main_core.Tag.render(_templateObject5 || (_templateObject5 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t\t<span class=\"ui-tile-selector-item ui-tile-selector-item-readonly-yes\">\n\t\t\t\t\t\t", "\n\t\t\t\t\t\t<span data-role=\"tile-item-name\">", "</span>\n\t\t\t\t\t</span>\n\t\t\t\t"])), picture, main_core.Text.encode(section.NAME)));
	        });
	        this.innerWrapper = main_core.Tag.render(_templateObject6 || (_templateObject6 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"ui-entity-editor-content-block\">\n\t\t\t\t\t<span class=\"ui-tile-selector-selector-wrap readonly\">\n\t\t\t\t\t\t", "\n\t\t\t\t\t</span>\n\t\t\t\t</div>"])), content);
	      }

	      this._wrapper.appendChild(this.innerWrapper);
	    }
	  }, {
	    key: "getSections",
	    value: function getSections() {
	      return this._model.getField('IBLOCK_SECTION_DATA', {});
	    }
	  }, {
	    key: "getIblockId",
	    value: function getIblockId() {
	      return this._model.getField('IBLOCK_ID', 0);
	    }
	  }, {
	    key: "getProductId",
	    value: function getProductId() {
	      return this._model.getField('ID', 0);
	    }
	  }, {
	    key: "hasNoSections",
	    value: function hasNoSections() {
	      var sectionIds = this.getValue();
	      return sectionIds.length === 0 || sectionIds.length === 1 && (sectionIds.includes('0') || sectionIds.includes(0));
	    }
	  }, {
	    key: "doClearLayout",
	    value: function doClearLayout(options) {
	      if (this.defaultInput) {
	        main_core.Dom.clean(this.defaultInput);
	        this.defaultInput = null;
	      }

	      if (this.innerWrapper) {
	        main_core.Dom.clean(this.innerWrapper);
	        this.innerWrapper = null;
	      }

	      this._hasLayout = false;
	    }
	  }, {
	    key: "getModeSwitchType",
	    value: function getModeSwitchType(mode) {
	      var result = BX.UI.EntityEditorModeSwitchType.common;

	      if (mode === BX.UI.EntityEditorMode.edit) {
	        result |= BX.UI.EntityEditorModeSwitchType.button | BX.UI.EntityEditorModeSwitchType.content;
	      }

	      return result;
	    }
	  }]);
	  return IblockSectionField;
	}(BX.UI.EntityEditorField);

	var _templateObject$1, _templateObject2$1, _templateObject3$1, _templateObject4$1, _templateObject5$1, _templateObject6$1, _templateObject7, _templateObject8, _templateObject9, _templateObject10, _templateObject11, _templateObject12, _templateObject13, _templateObject14, _templateObject15, _templateObject16, _templateObject17, _templateObject18;

	function _classPrivateMethodInitSpec(obj, privateSet) { _checkPrivateRedeclaration(obj, privateSet); privateSet.add(obj); }

	function _checkPrivateRedeclaration(obj, privateCollection) { if (privateCollection.has(obj)) { throw new TypeError("Cannot initialize the same private elements twice on an object"); } }

	function _classPrivateMethodGet(receiver, privateSet, fn) { if (!privateSet.has(receiver)) { throw new TypeError("attempted to get private field on non-instance"); } return fn; }

	var _creatLabelForEditMode = /*#__PURE__*/new WeakSet();

	var _onInputHandler = /*#__PURE__*/new WeakSet();

	var _getHintNode = /*#__PURE__*/new WeakSet();

	var _onCodeStateButtonClick = /*#__PURE__*/new WeakSet();

	var NameCodeField = /*#__PURE__*/function (_BX$UI$EntityEditorMu) {
	  babelHelpers.inherits(NameCodeField, _BX$UI$EntityEditorMu);

	  function NameCodeField(id, settings) {
	    var _this;

	    babelHelpers.classCallCheck(this, NameCodeField);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(NameCodeField).call(this));

	    _classPrivateMethodInitSpec(babelHelpers.assertThisInitialized(_this), _onCodeStateButtonClick);

	    _classPrivateMethodInitSpec(babelHelpers.assertThisInitialized(_this), _getHintNode);

	    _classPrivateMethodInitSpec(babelHelpers.assertThisInitialized(_this), _onInputHandler);

	    _classPrivateMethodInitSpec(babelHelpers.assertThisInitialized(_this), _creatLabelForEditMode);

	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "getValue", function () {
	      return BX.UI.EntityEditorBoolean.superclass.getValue.apply(this);
	    });
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "hasContentToDisplay", function () {
	      return true;
	    });
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "doPrepareContextMenuItems", function (menuItems) {
	      if (this.isShownSymbolicCode) {
	        menuItems.push({
	          value: 'hide_symbolic_code',
	          text: main_core.Loc.getMessage('CATALOG_ENTITY_CARD_HIDE_SYMBOLIC_CODE')
	        });
	      } else {
	        menuItems.push({
	          value: 'show_symbolic_code',
	          text: main_core.Loc.getMessage('CATALOG_ENTITY_CARD_SHOW_SYMBOLIC_CODE')
	        });
	      }
	    });

	    _this.initialize(id, settings);

	    _this.isShownSymbolicCode = _this.getSchemeShowCodeState() === 'true';
	    _this.allowToGenerateCode = _this._editor.isNew();
	    return _this;
	  }

	  babelHelpers.createClass(NameCodeField, [{
	    key: "getSchemeShowCodeState",
	    value: function getSchemeShowCodeState() {
	      return BX.prop.get(this.getSchemeElement()._options, 'showCode');
	    }
	  }, {
	    key: "setSchemeShowCodeState",
	    value: function setSchemeShowCodeState(state) {
	      this.getSchemeElement()._options['showCode'] = state;
	    }
	  }, {
	    key: "processContextMenuCommand",
	    value: function processContextMenuCommand(e, command) {
	      babelHelpers.get(babelHelpers.getPrototypeOf(NameCodeField.prototype), "processContextMenuCommand", this).call(this, e, command);
	      var codeContainerElement = document.getElementById('code_container');
	      var nameCodeMarkerElement = document.getElementById('name_code_marker');

	      if (command === 'hide_symbolic_code') {
	        this.isShownSymbolicCode = false;
	        this.allowToGenerateCode = this._editor.isNew();

	        if (this._mode === BX.UI.EntityEditorMode.edit) {
	          var codeTextElement = document.getElementById('code_text');
	          var codeStateButtonElement = document.getElementById('code_state_button');
	          codeTextElement.readOnly = this.allowToGenerateCode;

	          if (this.allowToGenerateCode) {
	            codeTextElement.className = 'ui-ctl-element ui-ctl-element-symbol-code-input-disabled';
	            codeStateButtonElement.className = 'ui-ctl-before ui-ctl-icon-chain';
	          } else {
	            codeTextElement.className = 'ui-ctl-element';
	            codeStateButtonElement.className = 'ui-ctl-before ui-ctl-icon-unchain';
	          }

	          codeContainerElement.className = 'name-code-container name-code-container-hidden';
	          main_core.Dom.removeClass(this._innerWrapper, 'ui-entity-editor-content-block--code');
	          main_core.Dom.addClass(this._innerWrapper, 'ui-entity-editor-content-block--no-code');
	          nameCodeMarkerElement.style.display = 'inline';
	        } else {
	          this.refreshLayout();
	        }

	        this.setSchemeShowCodeState(false);

	        this._parent.processChildControlSchemeChange(this);
	      } else if (command === 'show_symbolic_code') {
	        this.isShownSymbolicCode = true;

	        if (this._mode === BX.UI.EntityEditorMode.edit) {
	          codeContainerElement.className = 'name-code-container';
	          main_core.Dom.removeClass(this._innerWrapper, 'ui-entity-editor-content-block--no-code');
	          main_core.Dom.addClass(this._innerWrapper, 'ui-entity-editor-content-block--code');
	          nameCodeMarkerElement.style.display = 'none';
	        } else {
	          this.refreshLayout();
	        }

	        this.setSchemeShowCodeState(true);

	        this._parent.processChildControlSchemeChange(this);
	      }
	    }
	  }, {
	    key: "createTitleMarker",
	    value: function createTitleMarker() {
	      if (this._mode === BX.UI.EntityEditorMode.view) {
	        return null;
	      }

	      var display = this.isShownSymbolicCode ? 'none' : 'inline';

	      if (this._mode === BX.UI.EntityEditorMode.edit) {
	        return main_core.Tag.render(_templateObject$1 || (_templateObject$1 = babelHelpers.taggedTemplateLiteral(["<span id=\"name_code_marker\" style=\"color: rgb(255, 0, 0); display: ", ";\">*</span>"])), display);
	      }
	    }
	  }, {
	    key: "layout",
	    value: function layout(options) {
	      if (this._hasLayout) {
	        return;
	      }

	      this.ensureWrapperCreated({
	        classNames: ['ui-entity-editor-field-multitext']
	      });
	      this.adjustWrapper();

	      if (!this.isNeedToDisplay()) {
	        this.registerLayout(options);
	        this._hasLayout = true;
	        return;
	      }

	      var title = this.getTitle();
	      var values = this.getValue();
	      this._inputValue = values;
	      this._innerWrapper = null;

	      if (this.isDragEnabled()) {
	        main_core.Dom.append(this.createDragButton(), this._wrapper);
	      }

	      main_core.Dom.append(this.createTitleNode(title), this._wrapper);

	      if (this._mode === BX.UI.EntityEditorMode.edit) {
	        this._inputContainer = main_core.Tag.render(_templateObject2$1 || (_templateObject2$1 = babelHelpers.taggedTemplateLiteral(["<div></div>"])));

	        for (var valueKey in values) {
	          main_core.Dom.append(this.createSingleInput(values[valueKey], valueKey), this._inputContainer);
	        }

	        this._innerWrapper = main_core.Tag.render(_templateObject3$1 || (_templateObject3$1 = babelHelpers.taggedTemplateLiteral(["<div class=\"ui-entity-editor-content-block\">", "</div>"])), this._inputContainer);

	        if (this.isShownSymbolicCode) {
	          main_core.Dom.addClass(this._innerWrapper, 'ui-entity-editor-content-block--code');
	        } else {
	          main_core.Dom.addClass(this._innerWrapper, 'ui-entity-editor-content-block--no-code');
	        }
	      } else {
	        this._innerWrapper = main_core.Tag.render(_templateObject4$1 || (_templateObject4$1 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"ui-entity-editor-content-block\">", "</div>\n\t\t\t"])), this.getViewInnerLayout());
	      }

	      main_core.Dom.append(this._innerWrapper, this._wrapper);

	      if (this.isContextMenuEnabled()) {
	        main_core.Dom.append(this.createContextMenuButton(), this._wrapper);
	      }

	      if (this.isDragEnabled()) {
	        this.initializeDragDropAbilities();
	      }

	      this.registerLayout(options);
	      this._hasLayout = true;
	    }
	  }, {
	    key: "validate",
	    value: function validate(result) {
	      if (this._mode !== BX.UI.EntityEditorMode.edit) {
	        throw 'BX.UI.EntityEditorMultiText. Invalid validation context';
	      }

	      if (!this.isEditable()) {
	        return true;
	      }

	      this.clearError();

	      if (this.hasValidators()) {
	        return this.executeValidators(result);
	      }

	      var isEmptyField = false;

	      if (this._inputContainer) {
	        var nameTextElement = document.getElementById('name_text');

	        if (BX.util.trim(nameTextElement.value) === '') {
	          isEmptyField = true;
	          main_core.Dom.addClass(nameTextElement.parentNode, "ui-ctl-danger");
	        } else {
	          main_core.Dom.removeClass(nameTextElement.parentNode, "ui-ctl-danger");
	        }
	      }

	      var isValid = !this.isRequired() || !isEmptyField;

	      if (!isValid) {
	        result.addError(BX.UI.EntityValidationError.create({
	          field: this
	        }));
	        this.showRequiredFieldError(this._input);
	      }

	      return isValid;
	    }
	  }, {
	    key: "showError",
	    value: function showError(error, anchor) {
	      if (!this._errorContainer) {
	        this._errorContainer = main_core.Tag.render(_templateObject5$1 || (_templateObject5$1 = babelHelpers.taggedTemplateLiteral(["<div class=\"ui-entity-editor-field-error-text\"></div>"])));
	      }

	      this._errorContainer.innerHTML = BX.util.htmlspecialchars(error);

	      if (this._wrapper) {
	        main_core.Dom.append(this._errorContainer, this._wrapper);
	      }

	      this._hasError = true;
	    }
	  }, {
	    key: "createSingleInput",
	    value: function createSingleInput(value, name) {
	      var inputWrapper = main_core.Tag.render(_templateObject6$1 || (_templateObject6$1 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<div id=\"", "_container\"></div>\n\t\t"])), name.toLowerCase());
	      var inputContainer = main_core.Tag.render(_templateObject7 || (_templateObject7 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<div class=\"ui-ctl ui-ctl-w100 ui-ctl-textbox\"></div>\n\t\t"])));
	      var input;

	      if (this.getLineCount() > 1) {
	        input = main_core.Tag.render(_templateObject8 || (_templateObject8 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<textarea\n\t\t\t\t\tclass=\"ui-ctl-element ui-entity-editor-field-textarea\"\n\t\t\t\t\tname=\"", "\"\n\t\t\t\t\tid=\"", "\"\n\t\t\t\t\trows=\"", "\">", "</textarea>\n\t\t\t"])), name, name.toLowerCase() + '_text', this.getLineCount(), BX.util.htmlspecialchars(value) || '');
	      } else {
	        input = main_core.Tag.render(_templateObject9 || (_templateObject9 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<input\n\t\t\t\t\tclass=\"ui-ctl-element\"\n\t\t\t\t\tname=\"", "\"\n\t\t\t\t\tid=\"", "\"\n\t\t\t\t\ttype=\"text\"\n\t\t\t\t\tvalue=\"", "\"/>\n\t\t\t"])), name, name.toLowerCase() + '_text', BX.util.htmlspecialchars(value) || '');
	      }

	      main_core.Event.bind(input, 'input', _classPrivateMethodGet(this, _onInputHandler, _onInputHandler2).bind(this, name));

	      if (name === 'CODE') {
	        if (!this.isShownSymbolicCode) {
	          main_core.Dom.addClass(inputWrapper, 'name-code-container-hidden');
	        }

	        if (this.allowToGenerateCode === true) {
	          main_core.Dom.addClass(input, 'ui-ctl-element-symbol-code-input-disabled');
	          main_core.Dom.attr(input, 'readonly', this.allowToGenerateCode);
	        }

	        main_core.Dom.addClass(inputContainer, 'ui-ctl-ext-before-icon');
	        main_core.Dom.addClass(inputWrapper, 'name-code-container');
	        var chainState = this.allowToGenerateCode ? 'chain' : 'unchain';
	        var button = main_core.Tag.render(_templateObject10 || (_templateObject10 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<button name=\"", "\" class=\"ui-ctl-before ui-ctl-icon-", "\" id=\"code_state_button\"></button>\n\t\t\t"])), name, chainState);
	        main_core.Event.bind(button, 'click', _classPrivateMethodGet(this, _onCodeStateButtonClick, _onCodeStateButtonClick2).bind(this));
	        main_core.Dom.append(button, inputContainer);
	      }

	      var label = _classPrivateMethodGet(this, _creatLabelForEditMode, _creatLabelForEditMode2).call(this, name);

	      main_core.Dom.append(label, inputWrapper);
	      main_core.Dom.append(input, inputContainer);
	      main_core.Dom.append(inputContainer, inputWrapper);
	      return inputWrapper;
	    }
	  }, {
	    key: "getViewInnerLayout",
	    value: function getViewInnerLayout() {
	      var textValue = main_core.Tag.render(_templateObject11 || (_templateObject11 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<div class=\"ui-entity-editor-content-block-text\"></div>\n\t\t"])));
	      var values = this.getValue();

	      if (!this.isShownSymbolicCode) {
	        main_core.Dom.append(main_core.Tag.render(_templateObject12 || (_templateObject12 = babelHelpers.taggedTemplateLiteral(["<p>", "</p>"])), BX.util.htmlspecialchars(values.NAME)), textValue);
	        return textValue;
	      }

	      main_core.Dom.append(main_core.Tag.render(_templateObject13 || (_templateObject13 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<div class=\"ui-entity-editor-symbol-code-label\">\n\t\t\t\t", "\n\t\t\t</div>\n\t\t"])), main_core.Loc.getMessage('CATALOG_ENTITY_CARD_NAME')), textValue);
	      main_core.Dom.append(main_core.Tag.render(_templateObject14 || (_templateObject14 = babelHelpers.taggedTemplateLiteral(["<p>", "</p>"])), BX.util.htmlspecialchars(values.NAME)), textValue);
	      main_core.Dom.addClass(textValue, 'ui-entity-editor-symbol-code');
	      var codeValue = values.CODE === '' ? main_core.Loc.getMessage('UI_ENTITY_EDITOR_FIELD_EMPTY') : values.CODE;
	      var chainClass = this.allowToGenerateCode ? 'ui-entity-editor-symbol-code-value-chain' : 'ui-entity-editor-symbol-code-value-unchain';
	      main_core.Dom.append(main_core.Tag.render(_templateObject15 || (_templateObject15 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<div class=\"ui-entity-editor-symbol-code-box\">\n\t\t\t\t<div class=\"ui-entity-editor-symbol-code-label\">\n\t\t\t\t\t", "\n\t\t\t\t</div>\n\t\t\t\t<div class=\"ui-entity-editor-symbol-code-value ", "\">\n\t\t\t\t\t", "\n\t\t\t\t</div>\n\t\t\t</div>\n\t\t"])), main_core.Loc.getMessage('CATALOG_ENTITY_CARD_SYMBOLIC_CODE'), chainClass, BX.util.htmlspecialchars(codeValue)), textValue);
	      return textValue;
	    }
	  }]);
	  return NameCodeField;
	}(BX.UI.EntityEditorMultiText);

	function _creatLabelForEditMode2(name) {
	  var label = main_core.Tag.render(_templateObject16 || (_templateObject16 = babelHelpers.taggedTemplateLiteral(["<label class=\"ui-entity-editor-block-title\"></label>"])));
	  var labelText;

	  if (name === 'CODE') {
	    labelText = main_core.Tag.render(_templateObject17 || (_templateObject17 = babelHelpers.taggedTemplateLiteral(["<span>", "</span>"])), main_core.Loc.getMessage('CATALOG_ENTITY_CARD_SYMBOLIC_CODE'));
	    main_core.Dom.append(labelText, label);
	    main_core.Dom.append(_classPrivateMethodGet(this, _getHintNode, _getHintNode2).call(this), label);
	  } else {
	    labelText = main_core.Tag.render(_templateObject18 || (_templateObject18 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<span>\n\t\t\t\t\t", "\n\t\t\t\t\t<span style=\"color: rgb(255, 0, 0);\">*</span>\n\t\t\t\t</span>\n\t\t\t"])), main_core.Loc.getMessage('CATALOG_ENTITY_CARD_NAME'));
	    main_core.Dom.append(labelText, label);
	  }

	  return label;
	}

	function _onInputHandler2(name) {
	  this._changeHandler();

	  if (this.allowToGenerateCode && name === 'NAME') {
	    var codeTextElement = document.getElementById('code_text');
	    var nameTextElement = document.getElementById('name_text');
	    codeTextElement.value = BX.translit(nameTextElement.value, null);
	  }
	}

	function _getHintNode2() {
	  return BX.UI.Hint.createNode(main_core.Loc.getMessage('CATALOG_ENTITY_CARD_SYMBOLIC_CODE_HINT'));
	}

	function _onCodeStateButtonClick2() {
	  var codeTextElement = document.getElementById('code_text');
	  var nameTextElement = document.getElementById('name_text');
	  var codeStateButtonElement = document.getElementById('code_state_button');
	  this.allowToGenerateCode = !this.allowToGenerateCode;
	  codeTextElement.readOnly = this.allowToGenerateCode;

	  if (this.allowToGenerateCode) {
	    codeTextElement.className = 'ui-ctl-element ui-ctl-element-symbol-code-input-disabled';
	    codeStateButtonElement.className = 'ui-ctl-before ui-ctl-icon-chain';
	    codeTextElement.value = BX.translit(nameTextElement.value, null);
	  } else {
	    codeTextElement.className = 'ui-ctl-element';
	    codeStateButtonElement.className = 'ui-ctl-before ui-ctl-icon-unchain';

	    var _nameTextElement = document.getElementById('name_text');

	    var newValue = BX.translit(_nameTextElement.value, null);

	    if (codeTextElement.value !== newValue) {
	      this.markAsChanged();
	    }

	    codeTextElement.value = newValue;
	  }
	}

	var FieldsFactory = /*#__PURE__*/function () {
	  function FieldsFactory() {
	    var _this = this;

	    babelHelpers.classCallCheck(this, FieldsFactory);
	    main_core_events.EventEmitter.subscribe('BX.UI.EntityEditorControlFactory:onInitialize', function (event) {
	      var _event$getCompatData = event.getCompatData(),
	          _event$getCompatData2 = babelHelpers.slicedToArray(_event$getCompatData, 2),
	          eventArgs = _event$getCompatData2[1];

	      eventArgs.methods['entityCard'] = _this.factory.bind(_this);
	    });
	  }

	  babelHelpers.createClass(FieldsFactory, [{
	    key: "factory",
	    value: function factory(type, controlId, settings) {
	      if (type === 'iblock_section') {
	        return new IblockSectionField(controlId, settings);
	      } else if (type === 'name-code') {
	        return new NameCodeField(controlId, settings);
	      }

	      return null;
	    }
	  }]);
	  return FieldsFactory;
	}();

	function ownKeys$2(object, enumerableOnly) { var keys = Object.keys(object); if (Object.getOwnPropertySymbols) { var symbols = Object.getOwnPropertySymbols(object); enumerableOnly && (symbols = symbols.filter(function (sym) { return Object.getOwnPropertyDescriptor(object, sym).enumerable; })), keys.push.apply(keys, symbols); } return keys; }

	function _objectSpread$2(target) { for (var i = 1; i < arguments.length; i++) { var source = null != arguments[i] ? arguments[i] : {}; i % 2 ? ownKeys$2(Object(source), !0).forEach(function (key) { babelHelpers.defineProperty(target, key, source[key]); }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(target, Object.getOwnPropertyDescriptors(source)) : ownKeys$2(Object(source)).forEach(function (key) { Object.defineProperty(target, key, Object.getOwnPropertyDescriptor(source, key)); }); } return target; }
	var PROPERTY_PREFIX = 'PROPERTY_';
	var PROPERTY_BLOCK_NAME = 'properties';

	var IblockSectionController = /*#__PURE__*/function (_BX$UI$EntityEditorCo) {
	  babelHelpers.inherits(IblockSectionController, _BX$UI$EntityEditorCo);

	  function IblockSectionController(id) {
	    var _this;

	    var settings = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : {};
	    babelHelpers.classCallCheck(this, IblockSectionController);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(IblockSectionController).call(this));
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "onChangeHandler", _this.handleChange.bind(babelHelpers.assertThisInitialized(_this)));

	    _this.initialize(id, settings);

	    _this.isRequesting = false;

	    _this.clearServiceFields();

	    main_core_events.EventEmitter.subscribe(_this._editor, 'IblockSectionField:onChange', _this.onChangeHandler);
	    return _this;
	  }

	  babelHelpers.createClass(IblockSectionController, [{
	    key: "clearServiceFields",
	    value: function clearServiceFields() {
	      this.lastDataHash = null;
	      this.initialElements = null;
	      this.deletedControls = {};
	      this.deletedAvailableSchemes = {};
	    }
	  }, {
	    key: "handleChange",
	    value: function handleChange(event) {
	      var _this2 = this;

	      var newData = event.getData();
	      newData.shift();
	      var newDataHash = JSON.stringify(newData);

	      if (this.lastDataHash === null || this.lastDataHash !== newDataHash) {
	        this.lastDataHash = newDataHash;
	        clearTimeout(this.timeout);
	        this.timeout = setTimeout(function () {
	          _this2.refreshLinkedProperties(newData);
	        }, 50);
	      }
	    }
	  }, {
	    key: "refreshLinkedProperties",
	    value: function refreshLinkedProperties(sectionIds) {
	      var _this3 = this;

	      if (this.isRequesting) {
	        return;
	      }

	      this.isRequesting = true;
	      main_core.ajax.runComponentAction(this._editor._settings.ajaxData.COMPONENT_NAME, 'refreshLinkedProperties', {
	        mode: 'class',
	        signedParameters: this._editor._settings.ajaxData.SIGNED_PARAMETERS,
	        data: {
	          sectionIds: sectionIds
	        }
	      }).then(function (response) {
	        var allCurrentProperties = _this3.getAllCurrentProperties();

	        if (_this3.initialElements === null) {
	          _this3.initialElements = babelHelpers.toConsumableArray(allCurrentProperties);
	        }

	        response.data.ENTITY_FIELDS.forEach(function (property) {
	          if (!allCurrentProperties.includes(property.name)) {
	            _this3.addProperty(property, {
	              layout: {
	                forceDisplay: true
	              },
	              mode: BX.UI.EntityEditorMode.edit
	            });
	          }
	        });
	        var newProperties = response.data.ENTITY_FIELDS.map(function (el) {
	          return el.name;
	        });
	        allCurrentProperties.forEach(function (name) {
	          if (!newProperties.includes(name)) {
	            _this3.removeProperty(name);
	          }
	        });

	        _this3._editor.commitSchemeChanges();

	        _this3.isRequesting = false;
	      })["catch"](function (response) {
	        _this3.isRequesting = false;
	      });
	    }
	  }, {
	    key: "getAllCurrentProperties",
	    value: function getAllCurrentProperties() {
	      var activeProperties = this._editor.getAllControls().filter(function (el) {
	        return el.getName().indexOf(PROPERTY_PREFIX) === 0;
	      }).map(function (el) {
	        return el.getName();
	      });

	      var hiddenProperties = this._editor.getAvailableSchemeElements().filter(function (el) {
	        return el.getName().indexOf(PROPERTY_PREFIX) === 0;
	      }).map(function (el) {
	        return el.getName();
	      });

	      return [].concat(babelHelpers.toConsumableArray(activeProperties), babelHelpers.toConsumableArray(hiddenProperties));
	    }
	  }, {
	    key: "addProperty",
	    value: function addProperty(property) {
	      var options = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : {};

	      if (property.name in this.deletedControls) {
	        this.restoreDeletedProperty(this.deletedControls[property.name], options);
	      } else if (property.name in this.deletedAvailableSchemes) {
	        this.restoreDeletedAvailableProperty(this.deletedAvailableSchemes[property.name], options);
	      } else {
	        this.createProperty(property, options);
	      }
	    }
	  }, {
	    key: "restoreDeletedProperty",
	    value: function restoreDeletedProperty(control) {
	      var options = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : {};
	      var mode = options.mode || control._mode;
	      control._mode = mode;
	      control.getParent().addChild(control, _objectSpread$2(_objectSpread$2({}, options), {}, {
	        enableSaving: false
	      }));

	      if (mode === BX.UI.EntityEditorMode.edit) {
	        this._editor.registerActiveControl(control);
	      } else if (mode === BX.UI.EntityEditorMode.view) {
	        this._editor.unregisterActiveControl(control);
	      }
	    }
	  }, {
	    key: "restoreDeletedAvailableProperty",
	    value: function restoreDeletedAvailableProperty(schemeElement) {

	      this._editor.addAvailableSchemeElement(schemeElement);
	    }
	  }, {
	    key: "createProperty",
	    value: function createProperty(property) {
	      var options = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : {};

	      var propertyBlockScheme = this._editor.getSchemeElementByName(PROPERTY_BLOCK_NAME);

	      var schemeElement = BX.UI.EntitySchemeElement.create(property);

	      propertyBlockScheme._elements.push(schemeElement);

	      var mode = options.mode || BX.UI.EntityEditorMode.edit;

	      var control = this._editor.createControl(schemeElement.getType(), schemeElement.getName(), {
	        schemeElement: schemeElement,
	        model: this._model,
	        parent: this,
	        mode: mode
	      });

	      if (!control) {
	        return;
	      }

	      var propertyBlockControl = this._editor.getControlById(PROPERTY_BLOCK_NAME);

	      propertyBlockControl.addChild(control, _objectSpread$2(_objectSpread$2({}, options), {}, {
	        enableSaving: false
	      }));
	      return control;
	    }
	  }, {
	    key: "removeProperty",
	    value: function removeProperty(name) {
	      var control = this._editor.getControlByIdRecursive(name);

	      if (control) {
	        this.deletedControls[control.getName()] = control;
	        control.getParent().removeChild(control, {
	          enableSaving: false
	        });

	        this._editor.removeAvailableSchemeElement(control.getSchemeElement());

	        this._editor.unregisterActiveControl(control);
	      } else {
	        var schemeElement = this._editor.getAvailableSchemeElementByName(name);

	        if (schemeElement) {
	          this.deletedAvailableSchemes[schemeElement.getName()] = schemeElement;

	          this._editor.removeAvailableSchemeElement(schemeElement);
	        }
	      }
	    }
	  }, {
	    key: "rollback",
	    value: function rollback() {
	      var _this4 = this;

	      babelHelpers.get(babelHelpers.getPrototypeOf(IblockSectionController.prototype), "rollback", this).call(this);

	      if (this.initialElements === null) {
	        return;
	      }

	      var allCurrentProperties = this.getAllCurrentProperties();
	      allCurrentProperties.forEach(function (element) {
	        if (!_this4.initialElements.includes(element)) {
	          _this4.removeProperty(element);
	        }
	      });
	      this.initialElements.forEach(function (element) {
	        if (!allCurrentProperties.includes(element)) {
	          _this4.addProperty({
	            name: element
	          }, {
	            layout: {
	              forceDisplay: false
	            },
	            mode: BX.UI.EntityEditorMode.view
	          });
	        }
	      });

	      this._editor.commitSchemeChanges();

	      this.clearServiceFields();
	    }
	  }]);
	  return IblockSectionController;
	}(BX.UI.EntityEditorController);

	function ownKeys$3(object, enumerableOnly) { var keys = Object.keys(object); if (Object.getOwnPropertySymbols) { var symbols = Object.getOwnPropertySymbols(object); enumerableOnly && (symbols = symbols.filter(function (sym) { return Object.getOwnPropertyDescriptor(object, sym).enumerable; })), keys.push.apply(keys, symbols); } return keys; }

	function _objectSpread$3(target) { for (var i = 1; i < arguments.length; i++) { var source = null != arguments[i] ? arguments[i] : {}; i % 2 ? ownKeys$3(Object(source), !0).forEach(function (key) { babelHelpers.defineProperty(target, key, source[key]); }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(target, Object.getOwnPropertyDescriptors(source)) : ownKeys$3(Object(source)).forEach(function (key) { Object.defineProperty(target, key, Object.getOwnPropertyDescriptor(source, key)); }); } return target; }

	var VariationGridController = /*#__PURE__*/function (_BX$UI$EntityEditorCo) {
	  babelHelpers.inherits(VariationGridController, _BX$UI$EntityEditorCo);

	  function VariationGridController(id, settings) {
	    var _this;

	    babelHelpers.classCallCheck(this, VariationGridController);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(VariationGridController).call(this));
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "areaHeight", null);

	    _this.initialize(id, settings);

	    return _this;
	  }

	  babelHelpers.createClass(VariationGridController, [{
	    key: "doInitialize",
	    value: function doInitialize() {
	      babelHelpers.get(babelHelpers.getPrototypeOf(VariationGridController.prototype), "doInitialize", this).call(this);
	      main_core_events.EventEmitter.subscribe('Grid::thereEditedRows', this.markAsChangedHandler.bind(this));
	      main_core_events.EventEmitter.subscribe('Grid::noEditedRows', this.checkEditorToolbar.bind(this));
	      main_core_events.EventEmitter.subscribe('Grid::updated', this.checkEditorToolbar.bind(this));
	      main_core_events.EventEmitter.subscribe('Grid::beforeRequest', this.onBeforeGridRequest.bind(this));
	      main_core_events.EventEmitter.subscribe('onAjaxSuccess', this.ajaxSuccessHandler.bind(this));
	      main_core_events.EventEmitter.subscribe('BX.UI.EntityEditorIncludedArea:onBeforeLoad', this.onBeforeIncludedAreaLoaded.bind(this));
	      main_core_events.EventEmitter.subscribe('BX.UI.EntityEditorIncludedArea:onAfterLoad', this.onAfterIncludedAreaLoaded.bind(this));
	      this.subscribeToFormSubmit();
	    }
	  }, {
	    key: "onBeforeIncludedAreaLoaded",
	    value: function onBeforeIncludedAreaLoaded(event) {
	      if (main_core.Type.isNumber(this.areaHeight)) {
	        main_core.Dom.style(this.getVariationGridLoader(), 'height', this.areaHeight + 'px');
	      }
	    }
	  }, {
	    key: "onAfterIncludedAreaLoaded",
	    value: function onAfterIncludedAreaLoaded(event) {
	      main_core.Dom.style(this.getVariationGridLoader(), 'height', '');
	      this.areaHeight = null;
	    }
	  }, {
	    key: "getVariationGridLoader",
	    value: function getVariationGridLoader() {
	      var control = this.getGridControl();

	      if (control) {
	        var wrapper = control.getWrapper();

	        if (wrapper) {
	          return wrapper.querySelector('.ui-entity-editor-included-area-container-loader');
	        }
	      }

	      return null;
	    }
	  }, {
	    key: "rollback",
	    value: function rollback() {
	      babelHelpers.get(babelHelpers.getPrototypeOf(VariationGridController.prototype), "rollback", this).call(this);
	      this.checkEditorToolbar();
	      this.unsubscribeGridEvents();
	      BX.Main.gridManager.destroy(this.getGridId());
	    }
	  }, {
	    key: "onAfterSave",
	    value: function onAfterSave() {
	      if (this.isChanged() || this._editor.isChanged()) {
	        this.setGridControlCache(null);
	        main_core_events.EventEmitter.emit('onAfterVariationGridSave', {
	          gridId: this.getGridId()
	        });
	      }

	      this.subscribeToFormSubmit();
	      babelHelpers.get(babelHelpers.getPrototypeOf(VariationGridController.prototype), "onAfterSave", this).call(this);
	    }
	  }, {
	    key: "setGridControlCache",
	    value: function setGridControlCache(html) {
	      var control = this.getGridControl();

	      if (control) {
	        control._loadedHtml = html;
	      }
	    }
	  }, {
	    key: "onBeforeSubmit",
	    value: function onBeforeSubmit() {
	      this.unsubscribeGridEvents();
	    }
	    /**
	     * @returns {BX.Catalog.VariationGrid|null}
	     */

	  }, {
	    key: "getVariationGridComponent",
	    value: function getVariationGridComponent() {
	      return main_core.Reflection.getClass('BX.Catalog.VariationGrid.Instance');
	    }
	  }, {
	    key: "unsubscribeGridEvents",
	    value: function unsubscribeGridEvents() {
	      var _this$getGrid, _this$getGrid$getSett, _this$getGrid2;

	      var gridComponent = this.getVariationGridComponent();

	      if (gridComponent) {
	        gridComponent.destroy();
	      }

	      var popup = (_this$getGrid = this.getGrid()) === null || _this$getGrid === void 0 ? void 0 : (_this$getGrid$getSett = _this$getGrid.getSettingsWindow()) === null || _this$getGrid$getSett === void 0 ? void 0 : _this$getGrid$getSett.getPopup();

	      if (popup) {
	        main_core_events.EventEmitter.emit(this.getGrid().getSettingsWindow().getPopup(), 'onDestroy');
	      }

	      main_core_events.EventEmitter.unsubscribeAll('BX.Main.grid:paramsUpdated');
	      (_this$getGrid2 = this.getGrid()) === null || _this$getGrid2 === void 0 ? void 0 : _this$getGrid2.destroy();
	    }
	  }, {
	    key: "ajaxSuccessHandler",
	    value: function ajaxSuccessHandler(event) {
	      var _event$getCompatData = event.getCompatData(),
	          _event$getCompatData2 = babelHelpers.slicedToArray(_event$getCompatData, 2),
	          xhrData = _event$getCompatData2[1];

	      if (xhrData.url.indexOf(this.getReloadUrl()) === 0) {
	        this.setGridControlCache(null);
	      }
	    } // ajax form initializes every "save" action

	  }, {
	    key: "subscribeToFormSubmit",
	    value: function subscribeToFormSubmit() {
	      main_core_events.EventEmitter.subscribe(this._editor._ajaxForm, 'onBeforeSubmit', this.onBeforeSubmitForm.bind(this));
	    }
	  }, {
	    key: "markAsChangedHandler",
	    value: function markAsChangedHandler() {
	      if (!this._editor.isNew()) {
	        this.markAsChanged();
	      }
	    }
	  }, {
	    key: "checkEditorToolbar",
	    value: function checkEditorToolbar() {
	      this._isChanged = false;

	      if (this._editor.getActiveControlCount() > 0) {
	        this._editor.showToolPanel();
	      } else {
	        this._editor.hideToolPanel();
	      }

	      if (this._editor._toolPanel) {
	        this._editor._toolPanel.clearErrors();
	      }
	    }
	  }, {
	    key: "getGridControl",
	    value: function getGridControl() {
	      return this._editor.getControlById('variation_grid');
	    }
	  }, {
	    key: "onBeforeGridRequest",
	    value: function onBeforeGridRequest(event) {
	      var _event$getCompatData3 = event.getCompatData(),
	          _event$getCompatData4 = babelHelpers.slicedToArray(_event$getCompatData3, 2),
	          grid = _event$getCompatData4[0],
	          eventArgs = _event$getCompatData4[1];

	      if (!grid || !grid.parent || grid.parent.getId() !== this.getGridId()) {
	        return;
	      }

	      eventArgs.sessid = BX.bitrix_sessid();
	      eventArgs.method = 'POST';
	      eventArgs.url = this.getReloadUrl();
	      eventArgs.data = _objectSpread$3(_objectSpread$3({}, eventArgs.data), {}, {
	        signedParameters: this.getSignedParameters()
	      });
	      this.unsubscribeGridEvents();
	    }
	  }, {
	    key: "getReloadUrl",
	    value: function getReloadUrl() {
	      return this.getConfigStringParam('reloadUrl', '');
	    }
	  }, {
	    key: "getSignedParameters",
	    value: function getSignedParameters() {
	      return this.getConfigStringParam('signedParameters', '');
	    }
	  }, {
	    key: "getGridId",
	    value: function getGridId() {
	      return this.getConfigStringParam('gridId', '');
	    }
	  }, {
	    key: "getGrid",
	    value: function getGrid() {
	      if (!main_core.Reflection.getClass('BX.Main.gridManager.getInstanceById')) {
	        return null;
	      }

	      return BX.Main.gridManager.getInstanceById(this.getGridId());
	    }
	  }, {
	    key: "onBeforeSubmitForm",
	    value: function onBeforeSubmitForm(event) {
	      var _event$getCompatData5 = event.getCompatData(),
	          _event$getCompatData6 = babelHelpers.slicedToArray(_event$getCompatData5, 2),
	          eventArgs = _event$getCompatData6[1];

	      var grid = this.getGrid();

	      if (!grid) {
	        return;
	      }

	      var skuGridName = this.getGridId();
	      var skuGridData = grid.getRows().getEditSelectedValues();
	      var copyItemsMap = grid.getParam('COPY_ITEMS_MAP', {}); // replace sku custom properties edit data names with original names

	      for (var id in skuGridData) {
	        if (!skuGridData.hasOwnProperty(id)) {
	          continue;
	        }

	        for (var name in skuGridData[id]) {
	          if (!skuGridData[id].hasOwnProperty(name)) {
	            continue;
	          }

	          if (name.includes('SKU_GRID_CATALOG_GROUP') || name.includes('SKU_GRID_PURCHASING')) {
	            for (var priceField in skuGridData[id][name]) {
	              if (skuGridData[id][name].hasOwnProperty(priceField)) {
	                skuGridData[id][priceField] = skuGridData[id][name][priceField];
	              }
	            }
	          } else if (name.includes('[EDIT_HTML]')) {
	            var newName = name.replace('[EDIT_HTML]', ''); // lookup for a custom file fields

	            if (newName.endsWith('_custom')) {
	              if ('bxu_files[]' in skuGridData[id][name]) {
	                skuGridData[id][name].isFile = true;
	                delete skuGridData[id][name]['bxu_files[]'];
	              }

	              if (skuGridData[id][name].isFile) {
	                for (var fieldName in skuGridData[id][name]) {
	                  if (skuGridData[id][name].hasOwnProperty(fieldName)) {
	                    // check for new files like "MORE_PHOTO_n1[name]"(multiple) or "DETAIL_PICTURE[name]"(single)
	                    var newFilesRegExp = new RegExp(/([0-9A-Za-z_]+?(_n\d+)*)\[([A-Za-z_]+)\]/);

	                    if (newFilesRegExp.test(fieldName)) {
	                      var fileCounter = void 0,
	                          fileSetting = void 0;

	                      var _fieldName$match = fieldName.match(newFilesRegExp);

	                      var _fieldName$match2 = babelHelpers.slicedToArray(_fieldName$match, 4);

	                      fileCounter = _fieldName$match2[1];
	                      fileSetting = _fieldName$match2[3];

	                      if (fileCounter && fileSetting) {
	                        skuGridData[id][name][fileCounter] = skuGridData[id][name][fileCounter] || {};
	                        skuGridData[id][name][fileCounter][fileSetting] = skuGridData[id][name][fieldName];
	                        delete skuGridData[id][name][fieldName];
	                      }
	                    }
	                  }
	                }
	              }
	            }

	            skuGridData[id][newName] = skuGridData[id][name];
	            delete skuGridData[id][name];
	          }
	        }

	        if (!main_core.Type.isNil(copyItemsMap[id])) {
	          skuGridData[id]['COPY_SKU_ID'] = copyItemsMap[id];
	        }
	      }

	      if (!main_core.Type.isPlainObject(eventArgs.options)) {
	        eventArgs.options = {};
	      }

	      if (!main_core.Type.isPlainObject(eventArgs.options.data)) {
	        eventArgs.options.data = {};
	      }

	      eventArgs.options.data[skuGridName] = skuGridData;
	      this.areaHeight = this.getGridControl().getWrapper().offsetHeight;
	      BX.Main.gridManager.destroy(this.getGridId());
	    }
	  }]);
	  return VariationGridController;
	}(BX.UI.EntityEditorController);

	var GoogleMapController = /*#__PURE__*/function (_BX$UI$EntityEditorCo) {
	  babelHelpers.inherits(GoogleMapController, _BX$UI$EntityEditorCo);

	  function GoogleMapController(id, settings) {
	    var _this;

	    babelHelpers.classCallCheck(this, GoogleMapController);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(GoogleMapController).call(this));

	    _this.initialize(id, settings);

	    main_core_events.EventEmitter.subscribe('onAddGoogleMapPoint', _this.markAsChanged.bind(babelHelpers.assertThisInitialized(_this)));
	    return _this;
	  }

	  babelHelpers.createClass(GoogleMapController, [{
	    key: "rollback",
	    value: function rollback() {
	      babelHelpers.get(babelHelpers.getPrototypeOf(GoogleMapController.prototype), "rollback", this).call(this);

	      if (this._isChanged) {
	        this._isChanged = false;
	      }
	    }
	  }]);
	  return GoogleMapController;
	}(BX.UI.EntityEditorController);

	var EmployeeController = /*#__PURE__*/function (_BX$UI$EntityEditorCo) {
	  babelHelpers.inherits(EmployeeController, _BX$UI$EntityEditorCo);

	  function EmployeeController(id, settings) {
	    var _this;

	    babelHelpers.classCallCheck(this, EmployeeController);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(EmployeeController).call(this));

	    _this.initialize(id, settings);

	    main_core_events.EventEmitter.subscribe('onChangeEmployee', _this.markAsChanged.bind(babelHelpers.assertThisInitialized(_this)));
	    return _this;
	  }

	  babelHelpers.createClass(EmployeeController, [{
	    key: "rollback",
	    value: function rollback() {
	      babelHelpers.get(babelHelpers.getPrototypeOf(EmployeeController.prototype), "rollback", this).call(this);

	      if (this._isChanged) {
	        this._isChanged = false;
	      }
	    }
	  }]);
	  return EmployeeController;
	}(BX.UI.EntityEditorController);

	var BindingToCrmElementController = /*#__PURE__*/function (_BX$UI$EntityEditorCo) {
	  babelHelpers.inherits(BindingToCrmElementController, _BX$UI$EntityEditorCo);

	  function BindingToCrmElementController(id, settings) {
	    var _this;

	    babelHelpers.classCallCheck(this, BindingToCrmElementController);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(BindingToCrmElementController).call(this));

	    _this.initialize(id, settings);

	    return _this;
	  }

	  babelHelpers.createClass(BindingToCrmElementController, [{
	    key: "rollback",
	    value: function rollback() {
	      babelHelpers.get(babelHelpers.getPrototypeOf(BindingToCrmElementController.prototype), "rollback", this).call(this);

	      if (this._isChanged) {
	        this._isChanged = false;
	      }

	      main_core_events.EventEmitter.unsubscribeAll('BX.Main.User.SelectorController::open');
	    }
	  }, {
	    key: "onBeforeSubmit",
	    value: function onBeforeSubmit() {
	      babelHelpers.get(babelHelpers.getPrototypeOf(BindingToCrmElementController.prototype), "onBeforeSubmit", this).call(this);
	      main_core_events.EventEmitter.unsubscribeAll('BX.Main.User.SelectorController::open');
	    }
	  }]);
	  return BindingToCrmElementController;
	}(BX.UI.EntityEditorController);

	function ownKeys$4(object, enumerableOnly) { var keys = Object.keys(object); if (Object.getOwnPropertySymbols) { var symbols = Object.getOwnPropertySymbols(object); enumerableOnly && (symbols = symbols.filter(function (sym) { return Object.getOwnPropertyDescriptor(object, sym).enumerable; })), keys.push.apply(keys, symbols); } return keys; }

	function _objectSpread$4(target) { for (var i = 1; i < arguments.length; i++) { var source = null != arguments[i] ? arguments[i] : {}; i % 2 ? ownKeys$4(Object(source), !0).forEach(function (key) { babelHelpers.defineProperty(target, key, source[key]); }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(target, Object.getOwnPropertyDescriptors(source)) : ownKeys$4(Object(source)).forEach(function (key) { Object.defineProperty(target, key, Object.getOwnPropertyDescriptor(source, key)); }); } return target; }

	var FieldConfiguratorController = /*#__PURE__*/function (_BX$UI$EntityEditorCo) {
	  babelHelpers.inherits(FieldConfiguratorController, _BX$UI$EntityEditorCo);

	  function FieldConfiguratorController(id, settings) {
	    var _this;

	    babelHelpers.classCallCheck(this, FieldConfiguratorController);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(FieldConfiguratorController).call(this));
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "fieldAddHandler", _this.handleFieldAdd.bind(babelHelpers.assertThisInitialized(_this)));
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "fieldUpdateHandler", _this.handleFieldUpdate.bind(babelHelpers.assertThisInitialized(_this)));

	    _this.initialize(id, settings);

	    main_core_events.EventEmitter.subscribe(_this._editor, 'BX.UI.EntityEditor:onFieldCreate', _this.fieldAddHandler);
	    main_core_events.EventEmitter.subscribe(_this._editor, 'BX.UI.EntityEditor:onFieldModify', _this.fieldUpdateHandler);
	    return _this;
	  }

	  babelHelpers.createClass(FieldConfiguratorController, [{
	    key: "handleFieldAdd",
	    value: function handleFieldAdd(event) {
	      var _this2 = this;

	      var _event$getCompatData = event.getCompatData(),
	          _event$getCompatData2 = babelHelpers.slicedToArray(_event$getCompatData, 2),
	          section = _event$getCompatData2[0],
	          eventArgs = _event$getCompatData2[1];

	      var fields = this.getFieldsForm(eventArgs);
	      main_core.ajax.runComponentAction(this._editor._settings.ajaxData.COMPONENT_NAME, 'addProperty', {
	        mode: 'class',
	        signedParameters: this._editor._settings.ajaxData.SIGNED_PARAMETERS,
	        data: fields
	      }).then(function (response) {
	        var property = response.data.PROPERTY_FIELDS;

	        if (!property) {
	          return;
	        }

	        var additionalValues = response.data.ADDITIONAL_VALUES;

	        if (additionalValues) {
	          var model = _this2._editor._model;

	          for (var _i = 0, _Object$entries = Object.entries(additionalValues); _i < _Object$entries.length; _i++) {
	            var _Object$entries$_i = babelHelpers.slicedToArray(_Object$entries[_i], 2),
	                key = _Object$entries$_i[0],
	                value = _Object$entries$_i[1];

	            model.setField(key, value);
	          }
	        }

	        var mode = BX.UI.EntityEditorMode.view;

	        if (section instanceof BX.UI.EntityEditorSection) {
	          mode = section.getMode();
	        }

	        var control = _this2.createProperty(property, section.getName(), {
	          layout: {
	            notifyIfNotDisplayed: true,
	            forceDisplay: eventArgs.showAlways
	          },
	          mode: mode
	        });

	        control.toggleOptionFlag(eventArgs.showAlways);

	        _this2._editor.saveSchemeChanges();

	        _this2.isRequesting = false;
	      })["catch"](function (response) {
	        _this2.isRequesting = false;
	      });
	    }
	  }, {
	    key: "handleFieldUpdate",
	    value: function handleFieldUpdate(event) {
	      var _this3 = this;

	      var _event$getCompatData3 = event.getCompatData(),
	          _event$getCompatData4 = babelHelpers.slicedToArray(_event$getCompatData3, 2),
	          section = _event$getCompatData4[0],
	          eventArgs = _event$getCompatData4[1];

	      if (!(eventArgs.field instanceof BX.UI.EntityEditorControl)) {
	        return;
	      }

	      var currentField = eventArgs.field;
	      eventArgs.CODE = currentField.getId();
	      var fields = this.getFieldsForm(eventArgs);
	      var schemeElement = currentField.getSchemeElement();
	      schemeElement._isRequired = eventArgs.mandatory;
	      main_core.ajax.runComponentAction(this._editor._settings.ajaxData.COMPONENT_NAME, 'updateProperty', {
	        mode: 'class',
	        signedParameters: this._editor._settings.ajaxData.SIGNED_PARAMETERS,
	        data: fields
	      }).then(function (response) {
	        var _response$data;

	        var property = response === null || response === void 0 ? void 0 : (_response$data = response.data) === null || _response$data === void 0 ? void 0 : _response$data.PROPERTY_FIELDS;

	        if (currentField instanceof BX.UI.EntityEditorDatetime || currentField instanceof BX.UI.EntityEditorMultiDatetime) {
	          var schemeElementData = currentField.getSchemeElement().getData();
	          var propertyData = property === null || property === void 0 ? void 0 : property.data;

	          if (propertyData) {
	            schemeElementData.enableTime = propertyData.enableTime;
	            schemeElementData.dateViewFormat = propertyData.dateViewFormat;
	            currentField.refreshLayout();
	          }
	        }

	        var newType = null;
	        var schemeElement = null;

	        if (eventArgs.multiple === true) {
	          if (currentField instanceof BX.UI.EntityEditorText) {
	            newType = 'multitext';
	          } else if (currentField instanceof BX.UI.EntityEditorList) {
	            newType = 'multilist';
	          } else if (currentField instanceof BX.UI.EntityEditorDatetime) {
	            newType = 'multidatetime';
	          } else if (currentField instanceof BX.UI.EntityEditorNumber) {
	            newType = 'multinumber';
	          }
	        } else {
	          if (currentField instanceof BX.UI.EntityEditorMultiList) {
	            newType = 'list';
	          } else if (currentField instanceof BX.UI.EntityEditorMultiDatetime) {
	            newType = 'datetime';
	          } else if (currentField instanceof BX.UI.EntityEditorMultiNumber) {
	            newType = 'number';
	          } else if (currentField instanceof BX.UI.EntityEditorMultiText) {
	            newType = 'text';
	          }
	        }

	        schemeElement = currentField.getSchemeElement();

	        if ((currentField instanceof BX.UI.EntityEditorList || currentField instanceof BX.UI.EntityEditorMultiList) && property) {
	          schemeElement = BX.UI.EntitySchemeElement.create(property);
	          newType = property.type;
	        }

	        if (newType) {
	          var index = section.getChildIndex(currentField);

	          var newControl = _this3._editor.createControl(newType, eventArgs.CODE, {
	            schemeElement: schemeElement,
	            model: section._model,
	            parent: section,
	            mode: section.getMode()
	          });

	          section.addChild(newControl, {
	            index: index,
	            layout: {
	              forceDisplay: true
	            },
	            enableSaving: false
	          });
	          currentField._schemeElement = null;
	          section.removeChild(currentField, {
	            enableSaving: false
	          });
	        }

	        _this3.isRequesting = false;
	      })["catch"](function (response) {
	        _this3.isRequesting = false;
	      });
	    }
	  }, {
	    key: "getFieldsForm",
	    value: function getFieldsForm(fields) {
	      var _this4 = this;

	      var form = new FormData();
	      var formatted = {
	        NAME: fields.label,
	        MULTIPLE: fields.multiple ? 'Y' : 'N',
	        IS_REQUIRED: fields.mandatory ? 'Y' : 'N',
	        IS_PUBLIC: fields.isPublic ? 'Y' : 'N',
	        PROPERTY_TYPE: 'S',
	        CODE: fields.CODE || ''
	      };

	      switch (fields.typeId) {
	        case 'integer':
	        case 'double':
	          formatted.PROPERTY_TYPE = 'N';
	          break;

	        case 'list':
	        case 'multilist':
	          formatted.PROPERTY_TYPE = 'L';
	          (fields.enumeration || []).forEach(function (enumItem, key) {
	            form.append(_this4.getFormFieldName('VALUES][' + key + '][SORT'), enumItem.SORT);
	            form.append(_this4.getFormFieldName('VALUES][' + key + '][VALUE'), enumItem.VALUE);
	            form.append(_this4.getFormFieldName('VALUES][' + key + '][ID'), enumItem.ID);
	          });
	          break;

	        case 'directory':
	          formatted.USER_TYPE = 'directory';
	          (fields.enumeration || []).forEach(function (enumItem, key) {
	            form.append(_this4.getFormFieldName('VALUES][' + key + '][SORT'), enumItem.SORT);
	            form.append(_this4.getFormFieldName('VALUES][' + key + '][VALUE'), enumItem.VALUE.value);
	            form.append(_this4.getFormFieldName('VALUES][' + key + '][XML_ID'), enumItem.XML_ID);
	            form.append(_this4.getFormFieldName('VALUES][' + key + '][FILE_ID'), enumItem.FILE_ID);
	            form.append('FILES[' + enumItem.SORT + ']', enumItem.VALUE.file);
	          });
	          break;

	        case 'boolean':
	          formatted.PROPERTY_TYPE = 'L';
	          form.append(this.getFormFieldName('VALUES][0][VALUE'), 'Y');
	          formatted.LIST_TYPE = 'C';
	          break;

	        case 'money':
	          formatted.USER_TYPE = 'Money';
	          break;

	        case 'address':
	          formatted.USER_TYPE = 'map_google';
	          break;

	        case 'datetime':
	        case 'multidatetime':
	          formatted.USER_TYPE = fields.enableTime === true ? 'DateTime' : 'Date';
	          break;

	        case 'file':
	          formatted.USER_TYPE = 'DiskFile';
	          break;
	      }

	      for (var _i2 = 0, _Object$entries2 = Object.entries(formatted); _i2 < _Object$entries2.length; _i2++) {
	        var _Object$entries2$_i = babelHelpers.slicedToArray(_Object$entries2[_i2], 2),
	            key = _Object$entries2$_i[0],
	            item = _Object$entries2$_i[1];

	        form.append(this.getFormFieldName(key), item);
	      }

	      return form;
	    }
	  }, {
	    key: "getFormFieldName",
	    value: function getFormFieldName(name) {
	      return 'fields[' + name + ']';
	    }
	  }, {
	    key: "createProperty",
	    value: function createProperty(property, sectionName) {
	      var options = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : {};

	      var sectionSchemeElement = this._editor.getSchemeElementByName(sectionName);

	      if (!sectionSchemeElement) {
	        return;
	      }

	      var schemeElement = BX.UI.EntitySchemeElement.create(property);

	      sectionSchemeElement._elements.push(schemeElement);

	      var mode = options.mode || BX.UI.EntityEditorMode.edit;

	      var control = this._editor.createControl(schemeElement.getType(), schemeElement.getName(), {
	        schemeElement: schemeElement,
	        model: this._model,
	        parent: this,
	        mode: mode
	      });

	      if (!control) {
	        return;
	      }

	      var sectionControl = this._editor.getControlById(sectionName);

	      sectionControl.addChild(control, _objectSpread$4(_objectSpread$4({}, options), {}, {
	        enableSaving: false
	      }));
	      return control;
	    }
	  }]);
	  return FieldConfiguratorController;
	}(BX.UI.EntityEditorController);

	var ControllersFactory = /*#__PURE__*/function () {
	  function ControllersFactory() {
	    var _this = this;

	    babelHelpers.classCallCheck(this, ControllersFactory);
	    main_core_events.EventEmitter.subscribe('BX.UI.EntityEditorControllerFactory:onInitialize', function (event) {
	      var _event$getCompatData = event.getCompatData(),
	          _event$getCompatData2 = babelHelpers.slicedToArray(_event$getCompatData, 2),
	          eventArgs = _event$getCompatData2[1];

	      eventArgs.methods['entityCard'] = _this.factory.bind(_this);
	    });
	  }

	  babelHelpers.createClass(ControllersFactory, [{
	    key: "factory",
	    value: function factory(type, controlId, settings) {
	      if (type === 'field_configurator') {
	        return new FieldConfiguratorController(controlId, settings);
	      }

	      if (type === 'iblock_section') {
	        return new IblockSectionController(controlId, settings);
	      }

	      if (type === 'variation_grid') {
	        return new VariationGridController(controlId, settings);
	      }

	      if (type === 'google_map') {
	        return new GoogleMapController(controlId, settings);
	      }

	      if (type === 'employee') {
	        return new EmployeeController(controlId, settings);
	      }

	      if (type === 'binding_to_crm_element') {
	        return new BindingToCrmElementController(controlId, settings);
	      }

	      return null;
	    }
	  }]);
	  return ControllersFactory;
	}();

	var _templateObject$2, _templateObject2$2, _templateObject3$2, _templateObject4$2, _templateObject5$2;

	var IblockDirectoryFieldItem = /*#__PURE__*/function (_BX$UI$EntityEditorUs) {
	  babelHelpers.inherits(IblockDirectoryFieldItem, _BX$UI$EntityEditorUs);

	  function IblockDirectoryFieldItem() {
	    var _babelHelpers$getProt;

	    var _this;

	    babelHelpers.classCallCheck(this, IblockDirectoryFieldItem);

	    for (var _len = arguments.length, args = new Array(_len), _key = 0; _key < _len; _key++) {
	      args[_key] = arguments[_key];
	    }

	    _this = babelHelpers.possibleConstructorReturn(this, (_babelHelpers$getProt = babelHelpers.getPrototypeOf(IblockDirectoryFieldItem)).call.apply(_babelHelpers$getProt, [this].concat(args)));
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "fileChanged", false);
	    return _this;
	  }

	  babelHelpers.createClass(IblockDirectoryFieldItem, [{
	    key: "layout",
	    value: function layout() {
	      if (this._hasLayout) {
	        return;
	      }

	      this._wrapper = main_core.Tag.render(_templateObject$2 || (_templateObject$2 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"ui-ctl ui-ctl-textbox ui-ctl-w100 ui-ctl-row\"></div>\n\t\t\t"])));
	      this._fileInput = main_core.Tag.render(_templateObject2$2 || (_templateObject2$2 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<input class=\"input-image-hidden\" value=\"", "\" type=\"file\" accept=\"image/*\">\n\t\t\t"])), BX.prop.getString(this._data, 'FILE_ID', ''));
	      main_core.Event.bind(this._fileInput, 'change', this.onFileLoaderChange.bind(this));
	      var link = BX.prop.getString(this._data, 'IMAGE_SRC', '');

	      this._wrapper.appendChild(main_core.Tag.render(_templateObject3$2 || (_templateObject3$2 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<label class=\"catalog-dictionary-item ", "\">\n\t\t\t\t<img src=\"", "\" alt=\"\">\n\t\t\t\t", "\n\t\t\t</label>\n\t\t\t"])), link === '' ? 'catalog-dictionary-item-empty' : '', link, this._fileInput));

	      var labelText = main_core.Text.encode(BX.prop.getString(this._data, 'TEXT', ''));
	      this._labelInput = main_core.Tag.render(_templateObject4$2 || (_templateObject4$2 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<input \n\t\t\t\t\tclass=\"ui-ctl-element\" \n\t\t\t\t\tvalue=\"", "\"\n\t\t\t\t\tplaceholder=\"", "\"\n\t\t\t\t>\n\t\t\t"])), labelText, BX.message('CATALOG_ENTITY_CARD_NEW_FIELD_ITEM_PLACEHOLDER'));

	      this._wrapper.appendChild(this._labelInput);

	      var deleteButton = main_core.Tag.render(_templateObject5$2 || (_templateObject5$2 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"ui-entity-editor-content-remove-block\"></div>\n\t\t\t"])));
	      main_core.Event.bind(deleteButton, 'click', this.onDeleteButtonClick.bind(this));

	      this._wrapper.appendChild(deleteButton);

	      var anchor = BX.prop.getElementNode(this._settings, 'anchor');

	      if (anchor) {
	        this._container.insertBefore(this._wrapper, anchor);
	      } else {
	        this._container.appendChild(this._wrapper);
	      }

	      this._hasLayout = true;
	    }
	  }, {
	    key: "onFileLoaderChange",
	    value: function onFileLoaderChange(event) {
	      var input = event.target;

	      if (input.files && input.files[0]) {
	        var reader = new FileReader();

	        reader.onload = function (e) {
	          input.parentNode.querySelector('img').src = e.target.result;
	        };

	        this.fileChanged = true;
	        reader.readAsDataURL(input.files[0]);
	        input.parentNode.classList.remove('catalog-dictionary-item-empty');
	      }
	    }
	  }, {
	    key: "isFileChanged",
	    value: function isFileChanged() {
	      return this.fileChanged;
	    }
	  }, {
	    key: "prepareData",
	    value: function prepareData() {
	      var textValue = this._labelInput ? BX.util.trim(this._labelInput.value) : '';
	      var fileValue = this._fileInput && this._fileInput.files && this._fileInput.files[0] ? this._fileInput.files[0] : {};
	      var data = {
	        'VALUE': {
	          value: textValue,
	          file: fileValue
	        },
	        'XML_ID': '',
	        'FILE_ID': ''
	      };
	      var xmlId = BX.prop.getString(this._data, 'ID', '');

	      if (BX.type.isNotEmptyString(xmlId)) {
	        data['XML_ID'] = xmlId;
	        data['FILE_ID'] = BX.prop.getString(this._data, 'FILE_ID', '');
	      }

	      return data;
	    }
	  }], [{
	    key: "create",
	    value: function create(id, settings) {
	      var self = new this();
	      self.initialize(id, settings);
	      return self;
	    }
	  }]);
	  return IblockDirectoryFieldItem;
	}(BX.UI.EntityEditorUserFieldListItem);

	var _templateObject$3, _templateObject2$3, _templateObject3$3, _templateObject4$3, _templateObject5$3, _templateObject6$2, _templateObject7$1, _templateObject8$1;

	var IblockFieldConfigurator = /*#__PURE__*/function (_BX$UI$EntityEditorFi) {
	  babelHelpers.inherits(IblockFieldConfigurator, _BX$UI$EntityEditorFi);
	  babelHelpers.createClass(IblockFieldConfigurator, null, [{
	    key: "create",
	    value: function create(id, settings) {
	      var self = new this();
	      self.initialize(id, settings);
	      return self;
	    }
	  }]);

	  function IblockFieldConfigurator() {
	    var _this;

	    babelHelpers.classCallCheck(this, IblockFieldConfigurator);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(IblockFieldConfigurator).call(this));
	    _this._enumItems = [];
	    return _this;
	  }

	  babelHelpers.createClass(IblockFieldConfigurator, [{
	    key: "layoutInternal",
	    value: function layoutInternal() {
	      this._wrapper.appendChild(this.getInputContainer());

	      if (this._typeId === "list" || this._typeId === "multilist" || this._typeId === "directory") {
	        this._wrapper.appendChild(main_core.Tag.render(_templateObject$3 || (_templateObject$3 = babelHelpers.taggedTemplateLiteral(["<hr class=\"ui-entity-editor-line\">"]))));

	        this._wrapper.appendChild(this.getEnumerationContainer());
	      }

	      this._wrapper.appendChild(this.getOptionContainer());

	      this._wrapper.appendChild(this.getErrorContainer());

	      main_core.Dom.append(main_core.Tag.render(_templateObject2$3 || (_templateObject2$3 = babelHelpers.taggedTemplateLiteral(["<hr class=\"ui-entity-editor-line\">"]))), this._wrapper);

	      this._wrapper.appendChild(this.getButtonContainer());
	    }
	  }, {
	    key: "getOptionContainer",
	    value: function getOptionContainer() {
	      var isNew = this._field === null;
	      this._optionWrapper = main_core.Tag.render(_templateObject3$3 || (_templateObject3$3 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<div class=\"ui-entity-editor-content-block\"></div>\n\t\t"])));

	      if (this._typeId === "datetime" || this._typeId === "multidatetime") {
	        this._isTimeEnabledCheckBox = this.getIsTimeEnabledCheckBox();
	      }

	      if (this._typeId !== "boolean" && this._enableMandatoryControl) {
	        this._isRequiredCheckBox = this.getIsRequiredCheckBox();
	      }

	      if (this.isAllowedMultipleCheckBox()) {
	        this._isMultipleCheckBox = this.getMultipleCheckBox();
	      }

	      this._isPublic = this.getIsPublicCheckBox(); //region Show Always

	      this._showAlwaysCheckBox = this.createOption({
	        caption: BX.message('UI_ENTITY_EDITOR_SHOW_ALWAYS'),
	        helpUrl: 'https://helpdesk.bitrix24.ru/open/7046149/',
	        helpCode: '9627471'
	      });
	      this._showAlwaysCheckBox.checked = isNew ? BX.prop.getBoolean(this._settings, 'showAlways', true) : this._field.checkOptionFlag(BX.UI.EntityEditorControlOptions.showAlways);

	      if (!this.isAllowedShowAlwaysCheckBox()) {
	        main_core.Dom.style(this._showAlwaysCheckBox.closest('div.ui-ctl-checkbox'), 'display', 'none');
	      } //endregion


	      return this._optionWrapper;
	    }
	  }, {
	    key: "isAllowedMultipleCheckBox",
	    value: function isAllowedMultipleCheckBox() {
	      var _this$_field, _this$_field$getSchem, _this$_field$getSchem2, _this$_field2, _this$_field2$getSche, _this$_field2$getSche2;

	      var isEnabledOfferTree = this === null || this === void 0 ? void 0 : (_this$_field = this._field) === null || _this$_field === void 0 ? void 0 : (_this$_field$getSchem = _this$_field.getSchemeElement()) === null || _this$_field$getSchem === void 0 ? void 0 : (_this$_field$getSchem2 = _this$_field$getSchem._settings) === null || _this$_field$getSchem2 === void 0 ? void 0 : _this$_field$getSchem2.isEnabledOfferTree;
	      var isMultiple = this === null || this === void 0 ? void 0 : (_this$_field2 = this._field) === null || _this$_field2 === void 0 ? void 0 : (_this$_field2$getSche = _this$_field2.getSchemeElement()) === null || _this$_field2$getSche === void 0 ? void 0 : (_this$_field2$getSche2 = _this$_field2$getSche._settings) === null || _this$_field2$getSche2 === void 0 ? void 0 : _this$_field2$getSche2.multiple;
	      return !isEnabledOfferTree || isMultiple;
	    }
	  }, {
	    key: "isAllowedShowAlwaysCheckBox",
	    value: function isAllowedShowAlwaysCheckBox() {
	      return true;
	    }
	  }, {
	    key: "getInputTitle",
	    value: function getInputTitle() {
	      var manager = this._editor.getUserFieldManager();

	      return this._field ? this._field.getTitle() : manager.getDefaultFieldLabel(this._typeId);
	    }
	  }, {
	    key: "getErrorContainer",
	    value: function getErrorContainer() {
	      this._errorContainer = main_core.Tag.render(_templateObject4$3 || (_templateObject4$3 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<div class=\"ui-entity-editor-content-block\"></div>\n\t\t"])));
	      return this._errorContainer;
	    }
	  }, {
	    key: "getEnumerationContainer",
	    value: function getEnumerationContainer() {
	      var _this2 = this;

	      var enumWrapper = main_core.Tag.render(_templateObject5$3 || (_templateObject5$3 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<div class=\"ui-entity-editor-content-block\">\n\t\t\t\t<div class=\"ui-entity-editor-block-title\">\n\t\t\t\t\t<span class=\"ui-entity-editor-block-title-text\">", "</span>\n\t\t\t\t</div>\n\t\t\t</div>\n\t\t"])), BX.message("UI_ENTITY_EDITOR_UF_ENUM_ITEMS"));
	      this._enumItemContainer = main_core.Tag.render(_templateObject6$2 || (_templateObject6$2 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"ui-entity-editor-content-block\"></div>\n\t\t\t"])));
	      main_core.Dom.append(this._enumItemContainer, enumWrapper);
	      var addButton = main_core.Tag.render(_templateObject7$1 || (_templateObject7$1 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"ui-entity-card-content-add-field\">\n\t\t\t\t\t", "\n\t\t\t\t</div>\n\t\t\t"])), BX.message("UI_ENTITY_EDITOR_ADD"));
	      main_core.Event.bind(addButton, "click", this.onEnumerationItemAddButtonClick.bind(this));
	      main_core.Dom.append(main_core.Tag.render(_templateObject8$1 || (_templateObject8$1 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"ui-entity-editor-content-block-add-field\">\n\t\t\t\t\t", "\n\t\t\t\t</div>\n\t\t\t"])), addButton), enumWrapper);

	      if (this._field) {
	        this._field.getItems().forEach(function (enumFields) {
	          if (enumFields.VALUE !== '') {
	            _this2.createEnumerationItem({
	              VALUE: enumFields.NAME,
	              FILE_ID: enumFields.IMAGE || null,
	              IMAGE_SRC: enumFields.IMAGE_SRC || '',
	              TEXT: enumFields.TEXT || '',
	              ID: enumFields.VALUE
	            });
	          }
	        });
	      }

	      var lastItem = this.createEnumerationItem();
	      lastItem.focus();
	      this.initItemClickHandlers();
	      return enumWrapper;
	    }
	  }, {
	    key: "onEnumerationItemAddButtonClick",
	    value: function onEnumerationItemAddButtonClick() {
	      this.unbindItemClickHandlers();
	      this.createEnumerationItem().focus();
	      this.bindLastItemClickHandler();
	    }
	  }, {
	    key: "onEnumerationItemClick",
	    value: function onEnumerationItemClick() {
	      this.unbindItemClickHandlers();
	      this.createEnumerationItem();
	      this.bindLastItemClickHandler();
	    }
	  }, {
	    key: "initItemClickHandlers",
	    value: function initItemClickHandlers() {
	      this.unbindItemClickHandlers();
	      this.bindLastItemClickHandler();
	    }
	  }, {
	    key: "unbindItemClickHandlers",
	    value: function unbindItemClickHandlers() {
	      this._enumItems.forEach(function (item) {
	        return main_core.Event.unbindAll(item._labelInput, 'click');
	      });
	    }
	  }, {
	    key: "bindLastItemClickHandler",
	    value: function bindLastItemClickHandler() {
	      var lastItem = this._enumItems[this._enumItems.length - 1];

	      if (lastItem) {
	        main_core.Event.bindOnce(lastItem._labelInput, 'click', this.onEnumerationItemClick.bind(this));
	      }
	    }
	  }, {
	    key: "createEnumerationItem",
	    value: function createEnumerationItem(data) {
	      var item = null;

	      if (this._typeId === 'directory') {
	        item = IblockDirectoryFieldItem.create("", {
	          configurator: this,
	          container: this._enumItemContainer,
	          data: data
	        });
	      } else {
	        item = BX.UI.EntityEditorUserFieldListItem.create("", {
	          configurator: this,
	          container: this._enumItemContainer,
	          data: data
	        });
	      }

	      this._enumItems.push(item);

	      item.layout();
	      return item;
	    }
	  }, {
	    key: "removeEnumerationItem",
	    value: function removeEnumerationItem(item) {
	      for (var i = 0, length = this._enumItems.length; i < length; i++) {
	        if (this._enumItems[i] === item) {
	          this._enumItems[i].clearLayout();

	          this._enumItems.splice(i, 1);

	          this.initItemClickHandlers();
	          break;
	        }
	      }
	    }
	  }, {
	    key: "prepareSaveParams",
	    value: function prepareSaveParams(e) {
	      var params = babelHelpers.get(babelHelpers.getPrototypeOf(IblockFieldConfigurator.prototype), "prepareSaveParams", this).call(this, this, arguments);

	      if (this._typeId === 'list' || this._typeId === 'multilist') {
	        params['enumeration'] = [];
	        var hashes = [];

	        this._enumItems.forEach(function (enumItem) {
	          if (!(enumItem instanceof BX.UI.EntityEditorUserFieldListItem)) {
	            return;
	          }

	          var enumData = enumItem.prepareData();

	          if (!enumData) {
	            return;
	          }

	          var hash = BX.util.hashCode(enumData['VALUE']);

	          if (BX.util.in_array(hash, hashes)) {
	            return;
	          }

	          hashes.push(hash);

	          if (main_core.Type.isNil(enumData['ID'])) {
	            enumData['ID'] = main_core.Text.getRandom();
	          }

	          enumData['SORT'] = (params['enumeration'].length + 1) * 100;
	          params['enumeration'].push(enumData);
	        });
	      }

	      if (this._typeId === 'directory') {
	        params['enumeration'] = [];

	        this._enumItems.forEach(function (enumItem) {
	          if (!(enumItem instanceof IblockDirectoryFieldItem)) {
	            return;
	          }

	          var enumData = enumItem.prepareData();

	          if (!enumData) {
	            return;
	          }

	          enumData['SORT'] = (params['enumeration'].length + 1) * 100;
	          params['enumeration'].push(enumData);
	        });
	      } else if (this._typeId === 'datetime' || this._typeId === 'multidatetime') {
	        params['enableTime'] = this._isTimeEnabledCheckBox.checked;
	      }

	      if (this._field) {
	        if (this._isMultipleCheckBox) {
	          params["multiple"] = this._isMultipleCheckBox.checked;
	        }
	      } else {
	        if (this._typeId === "boolean") {
	          params["multiple"] = false;
	        } else if (this._isMultipleCheckBox) {
	          params["multiple"] = this._isMultipleCheckBox.checked;
	        }
	      }

	      if (this._isPublic) {
	        params["isPublic"] = this._isPublic.checked;
	      }

	      return params;
	    }
	  }, {
	    key: "getMultipleCheckBox",
	    value: function getMultipleCheckBox() {
	      var checkBox = this.createOption({
	        caption: BX.message("UI_ENTITY_EDITOR_UF_MULTIPLE_FIELD")
	      });

	      if (this._field instanceof BX.UI.EntityEditorMultiText || this._field instanceof BX.UI.EntityEditorMultiNumber || this._field instanceof BX.UI.EntityEditorMultiList || this._field instanceof BX.UI.EntityEditorMultiDatetime || this._field instanceof BX.UI.EntityEditorMultiMoney || this._field instanceof BX.UI.EntityEditorCustom && this._field.getSchemeElement()._settings.multiple) {
	        checkBox.checked = true;
	      }

	      return checkBox;
	    }
	  }, {
	    key: "onSaveButtonClick",
	    value: function onSaveButtonClick() {
	      if (this._isLocked) {
	        return;
	      }

	      if (this._mandatoryConfigurator) {
	        if (this._mandatoryConfigurator.isChanged()) {
	          this._mandatoryConfigurator.acceptChanges();
	        }

	        this._mandatoryConfigurator.close();
	      }

	      var params = this.prepareSaveParams();

	      if (this._field instanceof BX.UI.EntityEditorCustom) {
	        this._field.getSchemeElement().mergeSettings({
	          multiple: params.multiple
	        });

	        var modes = ['edit', 'view'];

	        for (var i = 0; i < modes.length; i++) {
	          var htmlListName = BX.prop.getString(this._field.getSchemeElement().getData(), modes[i] + 'List', null);
	          var htmlList = BX.prop.getObject(this._field.getModel().getData(), htmlListName, null);

	          if (htmlList !== null) {
	            var newHtml = params.multiple ? htmlList.MULTIPLE : htmlList.SINGLE;
	            var htmlName = BX.prop.getString(this._field.getSchemeElement().getData(), modes[i], null);

	            if (BX.prop.getString(this._field.getModel().getData(), htmlName, null) !== null) {
	              this._field.getModel().setField(htmlName, newHtml);

	              this._field.getModel().setInitFieldValue(htmlName, newHtml);

	              if (modes[i] === 'view') {
	                if (newHtml === '') {
	                  main_core.Dom.clean(this._field.getContentWrapper());

	                  this._field.getContentWrapper().appendChild(BX.create("div", {
	                    props: {
	                      className: "ui-entity-editor-content-block-text"
	                    },
	                    text: BX.message("UI_ENTITY_EDITOR_FIELD_EMPTY")
	                  }));
	                } else {
	                  this._field.getContentWrapper().innerHTML = newHtml;
	                }
	              }
	            }
	          }
	        }
	      }

	      BX.onCustomEvent(this, "onSave", [this, params]);
	    }
	  }, {
	    key: "getIsRequiredCheckBox",
	    value: function getIsRequiredCheckBox() {
	      var checkBox;

	      if (this._mandatoryConfigurator) {
	        checkBox = this.createOption({
	          caption: this._mandatoryConfigurator.getTitle() + ":",
	          labelSettings: {
	            props: {
	              className: "ui-entity-new-field-addiction-label"
	            }
	          },
	          containerSettings: {
	            style: {
	              alignItems: "center"
	            }
	          },
	          elements: this._mandatoryConfigurator.getButton().prepareLayout()
	        });
	        checkBox.checked = this._field && this._field.isRequired() || this._mandatoryConfigurator.isCustomized();

	        this._mandatoryConfigurator.setSwitchCheckBox(checkBox);

	        this._mandatoryConfigurator.setLabel(checkBox.nextSibling);

	        this._mandatoryConfigurator.setEnabled(checkBox.checked);

	        this._mandatoryConfigurator.adjust();
	      } else {
	        checkBox = this.createOption({
	          caption: BX.message("UI_ENTITY_EDITOR_UF_REQUIRED_FIELD")
	        });
	        checkBox.checked = this._field && this._field.isRequired();
	      }

	      return checkBox;
	    }
	  }, {
	    key: "getIsTimeEnabledCheckBox",
	    value: function getIsTimeEnabledCheckBox() {
	      var checkBox = this.createOption({
	        caption: BX.message("UI_ENTITY_EDITOR_UF_ENABLE_TIME")
	      });
	      checkBox.checked = this._field && this._field.isTimeEnabled();
	      return checkBox;
	    }
	  }, {
	    key: "getIsPublicCheckBox",
	    value: function getIsPublicCheckBox() {
	      var checkBox = this.createOption({
	        caption: BX.message("CATALOG_ENTITY_EDITOR_IS_PUBLIC_PROPERTY")
	      });

	      if (!this._field) {
	        checkBox.checked = true;
	      } else {
	        checkBox.checked = this._field.getSchemeElement() && BX.prop.get(this._field.getSchemeElement().getData(), "isPublic", true);
	      }

	      return checkBox;
	    }
	  }]);
	  return IblockFieldConfigurator;
	}(BX.UI.EntityEditorFieldConfigurator);
	main_core.Reflection.namespace('BX.Catalog').IblockFieldConfigurator = IblockFieldConfigurator;

	var IblockFieldConfigurationManager = /*#__PURE__*/function (_BX$UI$EntityConfigur) {
	  babelHelpers.inherits(IblockFieldConfigurationManager, _BX$UI$EntityConfigur);

	  function IblockFieldConfigurationManager() {
	    babelHelpers.classCallCheck(this, IblockFieldConfigurationManager);
	    return babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(IblockFieldConfigurationManager).apply(this, arguments));
	  }

	  babelHelpers.createClass(IblockFieldConfigurationManager, [{
	    key: "createFieldConfigurator",
	    value: function createFieldConfigurator(params, parent) {
	      if (!BX.type.isPlainObject(params)) {
	        throw "IblockFieldConfigurationManager: The 'params' argument must be object.";
	      }

	      return this.getSimpleFieldConfigurator(params, parent);
	    }
	  }, {
	    key: "getSimpleFieldConfigurator",
	    value: function getSimpleFieldConfigurator(params, parent) {
	      var typeId = "";
	      var field = BX.prop.get(params, 'field', null);

	      if (field) {
	        typeId = field.getType();
	        field.setVisible(false);

	        if (!BX.prop.get(field.getSchemeElement().getData(), "isProductProperty", false)) {
	          return this._fieldConfigurator = BX.UI.EntityEditorFieldConfigurator.create("", {
	            editor: this._editor,
	            schemeElement: null,
	            model: parent._model,
	            mode: BX.UI.EntityEditorMode.edit,
	            parent: parent,
	            typeId: typeId,
	            field: field,
	            mandatoryConfigurator: null
	          });
	        } else if (BX.prop.get(field.getSchemeElement().getData(), "userType", false)) {
	          typeId = BX.prop.getString(field.getSchemeElement().getData(), "userType");
	        }
	      } else {
	        typeId = BX.prop.get(params, 'typeId', BX.UI.EntityUserFieldType.string);
	      }

	      this._fieldConfigurator = IblockFieldConfigurator.create('', {
	        editor: this._editor,
	        schemeElement: null,
	        model: parent._model,
	        mode: BX.UI.EntityEditorMode.edit,
	        parent: parent,
	        typeId: typeId,
	        field: field,
	        mandatoryConfigurator: null
	      });
	      return this._fieldConfigurator;
	    }
	  }, {
	    key: "isCreationEnabled",
	    value: function isCreationEnabled() {
	      return true;
	    }
	  }, {
	    key: "getCreationPageUrl",
	    value: function getCreationPageUrl(typeId) {
	      return this.creationPageUrl;
	    }
	  }, {
	    key: "openCreationPageUrl",
	    value: function openCreationPageUrl(typeId) {
	      BX.SidePanel.Instance.open(this.getCreationPageUrl(typeId), {
	        allowChangeHistory: false,
	        cacheable: false
	      });
	    }
	  }, {
	    key: "setCreationPageUrl",
	    value: function setCreationPageUrl(url) {
	      return this.creationPageUrl = url;
	    }
	  }, {
	    key: "getTypeInfos",
	    value: function getTypeInfos() {
	      var items = [];
	      items.push({
	        name: "string",
	        title: BX.message("UI_ENTITY_EDITOR_UF_STRING_TITLE"),
	        legend: BX.message("UI_ENTITY_EDITOR_UF_STRING_LEGEND")
	      });
	      items.push({
	        name: "list",
	        title: BX.message("UI_ENTITY_EDITOR_UF_ENUM_TITLE"),
	        legend: BX.message("UI_ENTITY_EDITOR_UF_ENUM_LEGEND")
	      });
	      items.push({
	        name: "datetime",
	        title: BX.message("UI_ENTITY_EDITOR_UF_DATETIME_TITLE"),
	        legend: BX.message("UI_ENTITY_EDITOR_UF_DATETIME_LEGEND")
	      });
	      items.push({
	        name: "address",
	        title: BX.message("UI_ENTITY_EDITOR_UF_ADDRESS_TITLE"),
	        legend: BX.message("UI_ENTITY_EDITOR_UF_ADDRESS_LEGEND")
	      });
	      items.push({
	        name: "money",
	        title: BX.message("UI_ENTITY_EDITOR_UF_MONEY_TITLE"),
	        legend: BX.message("UI_ENTITY_EDITOR_UF_MONEY_LEGEND")
	      });
	      items.push({
	        name: "boolean",
	        title: BX.message("UI_ENTITY_EDITOR_BOOLEAN_TITLE"),
	        legend: BX.message("UI_ENTITY_EDITOR_UF_BOOLEAN_LEGEND")
	      });
	      items.push({
	        name: "double",
	        title: BX.message("UI_ENTITY_EDITOR_UF_DOUBLE_TITLE"),
	        legend: BX.message("UI_ENTITY_EDITOR_UF_DOUBLE_LEGEND")
	      });
	      items.push({
	        name: "directory",
	        title: BX.message("CATALOG_ENTITY_CARD_DICTIONARY_TITLE"),
	        legend: BX.message("CATALOG_ENTITY_CARD_DICTIONARY_LEGEND")
	      });
	      items.push({
	        name: "custom",
	        title: BX.message("UI_ENTITY_EDITOR_UF_CUSTOM_TITLE"),
	        legend: BX.message("UI_ENTITY_EDITOR_UF_CUSTOM_LEGEND")
	      });
	      return items;
	    }
	  }], [{
	    key: "create",
	    value: function create(id, settings) {
	      var self = new this();
	      self.initialize(id, settings);
	      return self;
	    }
	  }]);
	  return IblockFieldConfigurationManager;
	}(BX.UI.EntityConfigurationManager);

	var _templateObject$4, _templateObject2$4, _templateObject3$4, _templateObject4$4, _templateObject5$4;

	var GridFieldConfigurator = /*#__PURE__*/function (_BX$UI$EntityEditorFi) {
	  babelHelpers.inherits(GridFieldConfigurator, _BX$UI$EntityEditorFi);

	  function GridFieldConfigurator() {
	    babelHelpers.classCallCheck(this, GridFieldConfigurator);
	    return babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(GridFieldConfigurator).apply(this, arguments));
	  }

	  babelHelpers.createClass(GridFieldConfigurator, [{
	    key: "appendEnumerationSettings",
	    // ToDo remove unused methods
	    value: function appendEnumerationSettings() {
	      var _this = this;

	      if (this._typeId === "list" || this._typeId === "multilist") {
	        main_core.Dom.append(main_core.Tag.render(_templateObject$4 || (_templateObject$4 = babelHelpers.taggedTemplateLiteral(["<hr class=\"ui-entity-editor-line\">"]))), this._wrapper);
	        var enumWrapper = main_core.Tag.render(_templateObject2$4 || (_templateObject2$4 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"ui-entity-editor-content-block\">\n\t\t\t\t\t<div class=\"ui-entity-editor-block-title\">\n\t\t\t\t\t\t<span class=\"ui-entity-editor-block-title-text\">", "</span>\n\t\t\t\t\t</div>\n\t\t\t\t</div>\n\t\t\t"])), BX.message("UI_ENTITY_EDITOR_UF_ENUM_ITEMS"));
	        main_core.Dom.append(enumWrapper, this._wrapper);
	        this._enumItemContainer = main_core.Tag.render(_templateObject3$4 || (_templateObject3$4 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t\t<div class=\"ui-entity-editor-content-block\"></div>\n\t\t\t\t"])));
	        main_core.Dom.append(this._enumItemContainer, enumWrapper);
	        var addButton = main_core.Tag.render(_templateObject4$4 || (_templateObject4$4 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t\t<div class=\"ui-entity-card-content-add-field\">\n\t\t\t\t\t\t", "\n\t\t\t\t\t</div>\n\t\t\t\t"])), BX.message("UI_ENTITY_EDITOR_ADD"));
	        main_core.Event.bind(addButton, "click", this.onEnumerationItemAddButtonClick.bind(this));
	        main_core.Dom.append(main_core.Tag.render(_templateObject5$4 || (_templateObject5$4 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t\t<div class=\"ui-entity-editor-content-block-add-field\">\n\t\t\t\t\t\t", "\n\t\t\t\t\t</div>\n\t\t\t\t"])), addButton), enumWrapper);

	        if (this._field) {
	          this._field.getItems().forEach(function (enumFields) {
	            if (enumFields.VALUE !== '') {
	              _this.createEnumerationItem({
	                VALUE: enumFields.NAME,
	                ID: enumFields.VALUE
	              });
	            }
	          });
	        }

	        this.createEnumerationItem();
	        this.initItemFocusHandlers();
	      }
	    }
	  }, {
	    key: "onEnumerationItemAddButtonClick",
	    value: function onEnumerationItemAddButtonClick() {
	      this.unbindItemFocusHandlers();
	      this.createEnumerationItem().focus();
	      this.bindLastItemFocusHandler();
	    }
	  }, {
	    key: "onEnumerationItemFocus",
	    value: function onEnumerationItemFocus() {
	      this.unbindItemFocusHandlers();
	      this.createEnumerationItem();
	      this.bindLastItemFocusHandler();
	    }
	  }, {
	    key: "initItemFocusHandlers",
	    value: function initItemFocusHandlers() {
	      this.unbindItemFocusHandlers();
	      this.bindLastItemFocusHandler();
	    }
	  }, {
	    key: "unbindItemFocusHandlers",
	    value: function unbindItemFocusHandlers() {
	      this._enumItems.forEach(function (item) {
	        return main_core.Event.unbindAll(item._labelInput, 'focus');
	      });
	    }
	  }, {
	    key: "bindLastItemFocusHandler",
	    value: function bindLastItemFocusHandler() {
	      var lastItem = this._enumItems[this._enumItems.length - 1];

	      if (lastItem) {
	        main_core.Event.bindOnce(lastItem._labelInput, 'focus', this.onEnumerationItemFocus.bind(this));
	      }
	    }
	  }, {
	    key: "createEnumerationItem",
	    value: function createEnumerationItem(data) {
	      var item = BX.UI.EntityEditorUserFieldListItem.create("", {
	        configurator: this,
	        container: this._enumItemContainer,
	        data: data
	      });

	      this._enumItems.push(item);

	      item.layout();
	      return item;
	    }
	  }, {
	    key: "removeEnumerationItem",
	    value: function removeEnumerationItem(item) {
	      for (var i = 0, length = this._enumItems.length; i < length; i++) {
	        if (this._enumItems[i] === item) {
	          this._enumItems[i].clearLayout();

	          this._enumItems.splice(i, 1);

	          this.initItemFocusHandlers();
	          break;
	        }
	      }
	    }
	  }, {
	    key: "prepareSaveParams",
	    value: function prepareSaveParams(e) {
	      var params = babelHelpers.get(babelHelpers.getPrototypeOf(GridFieldConfigurator.prototype), "prepareSaveParams", this).call(this, this, arguments);

	      if (this._typeId === 'list' || this._typeId === 'multilist') {
	        params['enumeration'] = [];
	        var hashes = [];

	        this._enumItems.forEach(function (enumItem) {
	          if (!(enumItem instanceof BX.UI.EntityEditorUserFieldListItem)) {
	            return;
	          }

	          var enumData = enumItem.prepareData();

	          if (!enumData) {
	            return;
	          }

	          var hash = BX.util.hashCode(enumData['VALUE']);

	          if (BX.util.in_array(hash, hashes)) {
	            return;
	          }

	          hashes.push(hash);
	          enumData['SORT'] = (params['enumeration'].length + 1) * 100;
	          params['enumeration'].push(enumData);
	        });
	      } else if (this._typeId === 'datetime' || this._typeId === 'multidatetime') {
	        params['enableTime'] = this._isTimeEnabledCheckBox.checked;
	      }

	      return params;
	    }
	  }, {
	    key: "getMultipleCheckBox",
	    value: function getMultipleCheckBox() {
	      var checkBox = this.createOption({
	        caption: BX.message("UI_ENTITY_EDITOR_UF_MULTIPLE_FIELD")
	      });

	      if (this._field instanceof BX.UI.EntityEditorMultiText || this._field instanceof BX.UI.EntityEditorMultiNumber || this._field instanceof BX.UI.EntityEditorMultiList || this._field instanceof BX.UI.EntityEditorMultiDatetime) {
	        checkBox.checked = true;
	      }

	      return checkBox;
	    }
	  }, {
	    key: "getIsRequiredCheckBox",
	    value: function getIsRequiredCheckBox() {
	      var checkBox = null;

	      if (this._typeId !== "boolean") {
	        if (this._enableMandatoryControl) {
	          if (this._mandatoryConfigurator) {
	            checkBox = this.createOption({
	              caption: this._mandatoryConfigurator.getTitle() + ":",
	              labelSettings: {
	                props: {
	                  className: "ui-entity-new-field-addiction-label"
	                }
	              },
	              containerSettings: {
	                style: {
	                  alignItems: "center"
	                }
	              },
	              elements: this._mandatoryConfigurator.getButton().prepareLayout()
	            });
	            checkBox.checked = this._field && this._field.isRequired() || this._mandatoryConfigurator.isCustomized();

	            this._mandatoryConfigurator.setSwitchCheckBox(checkBox);

	            this._mandatoryConfigurator.setLabel(checkBox.nextSibling);

	            this._mandatoryConfigurator.setEnabled(checkBox.checked);

	            this._mandatoryConfigurator.adjust();
	          } else {
	            checkBox = this.createOption({
	              caption: BX.message("UI_ENTITY_EDITOR_UF_REQUIRED_FIELD")
	            });
	            checkBox.checked = this._field && this._field.isRequired();
	          }
	        }
	      }

	      return checkBox;
	    }
	  }, {
	    key: "getIsTimeEnabledCheckBox",
	    value: function getIsTimeEnabledCheckBox() {
	      var checkBox = null;

	      if (this._typeId === "datetime" || this._typeId === "multidatetime") {
	        checkBox = this.createOption({
	          caption: BX.message("UI_ENTITY_EDITOR_UF_ENABLE_TIME")
	        });
	        checkBox.checked = this._field && this._field.isTimeEnabled();
	      }

	      return checkBox;
	    }
	  }], [{
	    key: "create",
	    value: function create(id, settings) {
	      var self = new this();
	      self.initialize(id, settings);
	      return self;
	    }
	  }]);
	  return GridFieldConfigurator;
	}(BX.UI.EntityEditorFieldConfigurator);

	var GridFieldConfigurationManager = /*#__PURE__*/function (_BX$UI$EntityConfigur) {
	  babelHelpers.inherits(GridFieldConfigurationManager, _BX$UI$EntityConfigur);

	  function GridFieldConfigurationManager() {
	    babelHelpers.classCallCheck(this, GridFieldConfigurationManager);
	    return babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(GridFieldConfigurationManager).apply(this, arguments));
	  }

	  babelHelpers.createClass(GridFieldConfigurationManager, [{
	    key: "createFieldConfigurator",
	    value: function createFieldConfigurator(params, parent) {
	      if (!main_core.Type.isPlainObject(params)) {
	        throw "GridFieldConfigurationManager: The 'params' argument must be object.";
	      }

	      return this.getSimpleFieldConfigurator(params, parent);
	    }
	  }, {
	    key: "getSimpleFieldConfigurator",
	    value: function getSimpleFieldConfigurator(params, parent) {
	      var typeId = "";
	      var child = BX.prop.get(params, 'field', null);

	      if (child) {
	        typeId = child.getType();
	        child.setVisible(false);

	        if (!BX.prop.get(child.getSchemeElement().getData(), "isProductProperty", false)) {
	          return this._fieldConfigurator = BX.UI.EntityEditorFieldConfigurator.create("", {
	            editor: this._editor,
	            schemeElement: null,
	            model: parent._model,
	            mode: BX.UI.EntityEditorMode.edit,
	            parent: parent,
	            typeId: typeId,
	            field: child,
	            mandatoryConfigurator: null
	          });
	        }
	      } else {
	        typeId = BX.prop.get(params, 'typeId', BX.UI.EntityUserFieldType.string);
	      }

	      this._fieldConfigurator = GridFieldConfigurator.create('', {
	        editor: this._editor,
	        schemeElement: null,
	        model: parent._model,
	        mode: BX.UI.EntityEditorMode.edit,
	        parent: parent,
	        typeId: typeId,
	        field: child,
	        mandatoryConfigurator: null
	      });
	      return this._fieldConfigurator;
	    }
	  }, {
	    key: "isSelectionEnabled",
	    value: function isSelectionEnabled() {
	      return false;
	    }
	  }, {
	    key: "isCreationEnabled",
	    value: function isCreationEnabled() {
	      return false;
	    }
	  }, {
	    key: "hasExternalForm",
	    value: function hasExternalForm(typeId) {
	      return true;
	    }
	  }, {
	    key: "getCreationPageUrl",
	    value: function getCreationPageUrl(typeId) {
	      var filtered = this.getTypeInfos().filter(function (item) {
	        return item.name === typeId;
	      });

	      if (filtered.length > 0) {
	        return this.creationPageUrl.replace('#PROPERTY_TYPE#', typeId);
	      }
	    }
	  }, {
	    key: "openCreationPageUrl",
	    value: function openCreationPageUrl(typeId) {
	      var _this = this;

	      var okCallback = function okCallback() {
	        return _this.openCreationPageSlider(_this.getCreationPageUrl(typeId));
	      };

	      var variationGridInstance = main_core.Reflection.getClass('BX.Catalog.VariationGrid.Instance');

	      if (variationGridInstance) {
	        variationGridInstance.askToLossGridData(okCallback, null, {
	          message: main_core.Loc.getMessage('CATALOG_ENTITY_CARD_UNSAVED_DATA_MESSAGE')
	        });
	      } else {
	        okCallback();
	      }
	    }
	  }, {
	    key: "openCreationPageSlider",
	    value: function openCreationPageSlider(url) {
	      if (main_core.Type.isStringFilled(url)) {
	        BX.SidePanel.Instance.open(url, {
	          width: 550,
	          allowChangeHistory: false,
	          cacheable: false
	        });
	      }
	    }
	  }, {
	    key: "setCreationPageUrl",
	    value: function setCreationPageUrl(url) {
	      return this.creationPageUrl = url;
	    }
	  }, {
	    key: "getTypeInfos",
	    value: function getTypeInfos() {
	      return [{
	        name: "list",
	        title: BX.message("CATALOG_ENTITY_CARD_LIST_TITLE"),
	        legend: BX.message("CATALOG_ENTITY_CARD_LIST_LEGEND")
	      }, {
	        name: "directory",
	        title: BX.message("CATALOG_ENTITY_CARD_DICTIONARY_TITLE"),
	        legend: BX.message("CATALOG_ENTITY_CARD_DICTIONARY_LEGEND")
	      }];
	    }
	  }], [{
	    key: "create",
	    value: function create(id, settings) {
	      var self = new this();
	      self.initialize(id, settings);
	      return self;
	    }
	  }]);
	  return GridFieldConfigurationManager;
	}(BX.UI.EntityConfigurationManager);

	var _templateObject$5;
	var BaseCard = /*#__PURE__*/function () {
	  function BaseCard(id) {
	    var settings = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : {};
	    babelHelpers.classCallCheck(this, BaseCard);
	    this.id = main_core.Type.isStringFilled(id) ? id : main_core.Text.getRandom();
	    this.entityId = main_core.Text.toInteger(settings.entityId) || 0;
	    this.settings = settings;
	    this.container = document.getElementById(settings.containerId);
	    this.initializeTabManager();
	    this.checkFadeOverlay();
	  }

	  babelHelpers.createClass(BaseCard, [{
	    key: "initializeTabManager",
	    value: function initializeTabManager() {
	      return new Manager(this.id, {
	        container: document.getElementById(this.settings.tabContainerId),
	        menuContainer: document.getElementById(this.settings.tabMenuContainerId),
	        data: this.settings.tabs || []
	      });
	    }
	  }, {
	    key: "checkFadeOverlay",
	    value: function checkFadeOverlay() {
	      if (this.entityId <= 0) {
	        this.overlay = main_core.Tag.render(_templateObject$5 || (_templateObject$5 = babelHelpers.taggedTemplateLiteral(["<div class=\"catalog-entity-overlay\"></div>"])));
	        main_core.Dom.append(this.overlay, this.container);

	        if (window === window.top) {
	          this.overlay.style.position = 'absolute';
	          this.overlay.style.top = this.overlay.style.left = this.overlay.style.right = '-15px';
	        }
	      }
	    }
	  }]);
	  return BaseCard;
	}();

	var _templateObject$6, _templateObject2$5, _templateObject3$5, _templateObject4$5, _templateObject5$5, _templateObject6$3, _templateObject7$2;

	var EntityCard = /*#__PURE__*/function (_BaseCard) {
	  babelHelpers.inherits(EntityCard, _BaseCard);

	  function EntityCard(id) {
	    var _this;

	    var settings = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : {};
	    babelHelpers.classCallCheck(this, EntityCard);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(EntityCard).call(this, id, settings));
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "stackWithOffset", null);
	    _this.cardSettings = settings.cardSettings || [];
	    _this.feedbackUrl = settings.feedbackUrl || '';
	    _this.variationGridId = settings.variationGridId;
	    _this.productStoreGridId = settings.productStoreGridId || null;
	    _this.settingsButtonId = settings.settingsButtonId;
	    _this.createDocumentButtonId = settings.createDocumentButtonId;
	    _this.createDocumentButtonMenuPopupItems = settings.createDocumentButtonMenuPopupItems;
	    _this.componentName = settings.componentName || null;
	    _this.componentSignedParams = settings.componentSignedParams || null;
	    _this.isSimpleProduct = settings.isSimpleProduct || false;

	    _this.registerFieldsFactory();

	    _this.registerControllersFactory();

	    _this.registerEvents();

	    _this.bindCardSettingsButton();

	    _this.bindCreateDocumentButtonMenu();

	    main_core_events.EventEmitter.subscribe('SidePanel.Slider:onMessage', _this.onSliderMessage.bind(babelHelpers.assertThisInitialized(_this)));
	    main_core_events.EventEmitter.subscribe('BX.UI.EntityEditorSection:onLayout', _this.onSectionLayout.bind(babelHelpers.assertThisInitialized(_this)));
	    main_core_events.EventEmitter.subscribe('Grid::updated', _this.onGridUpdatedHandler.bind(babelHelpers.assertThisInitialized(_this)));
	    return _this;
	  }

	  babelHelpers.createClass(EntityCard, [{
	    key: "getEntityType",
	    value: function getEntityType() {
	      return 'Entity';
	    }
	  }, {
	    key: "getCardSetting",
	    value: function getCardSetting(id) {
	      return this.cardSettings.filter(function (item) {
	        return item.id === id;
	      })[0];
	    }
	  }, {
	    key: "isCardSettingEnabled",
	    value: function isCardSettingEnabled(id) {
	      var settingItem = this.getCardSetting(id);
	      return settingItem && settingItem.checked;
	    }
	  }, {
	    key: "bindCardSettingsButton",
	    value: function bindCardSettingsButton() {
	      var settingsButton = this.getSettingsButton();

	      if (settingsButton) {
	        main_core.Event.bind(settingsButton.getContainer(), 'click', this.showCardSettingsPopup.bind(this));
	      }
	    }
	  }, {
	    key: "getSettingsButton",
	    value: function getSettingsButton() {
	      return BX.UI.ButtonManager.getByUniqid(this.settingsButtonId);
	    }
	  }, {
	    key: "registerFieldsFactory",
	    value: function registerFieldsFactory() {
	      return new FieldsFactory();
	    }
	  }, {
	    key: "onGridUpdatedHandler",
	    value: function onGridUpdatedHandler(event) {
	      var _event$getCompatData = event.getCompatData(),
	          _event$getCompatData2 = babelHelpers.slicedToArray(_event$getCompatData, 1),
	          grid = _event$getCompatData2[0];

	      if (grid && grid.getId() === this.getVariationGridId()) {
	        this.updateSettingsCheckboxState();
	      }
	    }
	  }, {
	    key: "onSectionLayout",
	    value: function onSectionLayout() {}
	  }, {
	    key: "getProductStoreGridId",
	    value: function getProductStoreGridId() {
	      return this.productStoreGridId;
	    }
	  }, {
	    key: "getProductStoreGridComponent",
	    value: function getProductStoreGridComponent() {
	      return main_core.Reflection.getClass('BX.Catalog.ProductStoreGridManager.Instance');
	    }
	  }, {
	    key: "reloadProductStoreGrid",
	    value: function reloadProductStoreGrid() {
	      var gridComponent = this.getProductStoreGridComponent();

	      if (gridComponent) {
	        if (this.getProductStoreGridId() && this.getProductStoreGridId() === gridComponent.getGridId()) {
	          gridComponent.reloadGrid();
	        }
	      }
	    }
	    /**
	     * @returns {BX.Catalog.VariationGrid|null}
	     */

	  }, {
	    key: "getVariationGridComponent",
	    value: function getVariationGridComponent() {
	      return main_core.Reflection.getClass('BX.Catalog.VariationGrid.Instance');
	    }
	  }, {
	    key: "reloadVariationGrid",
	    value: function reloadVariationGrid() {
	      var gridComponent = this.getVariationGridComponent();

	      if (gridComponent) {
	        gridComponent.reloadGrid();
	      }
	    }
	  }, {
	    key: "getVariationGridId",
	    value: function getVariationGridId() {
	      return this.variationGridId;
	    }
	  }, {
	    key: "getVariationGrid",
	    value: function getVariationGrid() {
	      if (!main_core.Reflection.getClass('BX.Main.gridManager.getInstanceById')) {
	        return null;
	      }

	      return BX.Main.gridManager.getInstanceById(this.getVariationGridId());
	    }
	  }, {
	    key: "registerControllersFactory",
	    value: function registerControllersFactory() {
	      return new ControllersFactory();
	    }
	  }, {
	    key: "registerEvents",
	    value: function registerEvents() {
	      main_core_events.EventEmitter.subscribe('BX.UI.EntityConfigurationManager:onInitialize', this.onConfigurationManagerInit.bind(this));
	      main_core_events.EventEmitter.subscribe('BX.UI.EntityEditor:onCancel', this.removeFileHiddenInputs.bind(this));
	      main_core_events.EventEmitter.subscribe('BX.UI.EntityEditor:onInit', this.onEditorInitHandler.bind(this));
	      main_core_events.EventEmitter.subscribe('BX.UI.EntityEditorAjax:onSubmit', this.onEditorAjaxSubmit.bind(this));
	      main_core_events.EventEmitter.subscribe('onEntityCreate', this.onEntityCreateHandler.bind(this));
	      main_core_events.EventEmitter.subscribe('onEntityUpdate', this.onEntityUpdateHandler.bind(this));
	      main_core_events.EventEmitter.subscribe('onAttachFiles', this.onAttachFilesHandler.bind(this));
	      main_core_events.EventEmitter.subscribe('BX.Main.Popup:onClose', this.onFileEditorCloseHandler.bind(this));
	      main_core_events.EventEmitter.subscribe('onAfterVariationGridSave', this.onAfterVariationGridSave.bind(this));
	    }
	  }, {
	    key: "onAfterVariationGridSave",
	    value: function onAfterVariationGridSave(event) {
	      var data = event.getData();

	      if (data.gridId === this.getVariationGridId()) {
	        this.reloadProductStoreGrid();
	      }
	    }
	  }, {
	    key: "onAttachFilesHandler",
	    value: function onAttachFilesHandler(event) {
	      var editor = this.getEditorInstance();

	      if (!editor) {
	        return;
	      }

	      var _event$getCompatData3 = event.getCompatData(),
	          _event$getCompatData4 = babelHelpers.slicedToArray(_event$getCompatData3, 3),
	          uploader = _event$getCompatData4[2];

	      if (uploader && main_core.Type.isDomNode(uploader.fileInput)) {
	        var parent = uploader.fileInput.closest('[data-cid]');

	        if (main_core.Type.isDomNode(parent)) {
	          var controlName = parent.getAttribute('data-cid');
	          var control = editor.getControlByIdRecursive(controlName);

	          if (control) {
	            control.markAsChanged();
	          }
	        }
	      }
	    }
	  }, {
	    key: "onFileEditorCloseHandler",
	    value: function onFileEditorCloseHandler(event) {
	      var _event$getCompatData5 = event.getCompatData(),
	          _event$getCompatData6 = babelHelpers.slicedToArray(_event$getCompatData5, 1),
	          popup = _event$getCompatData6[0];

	      if (popup && popup.getId() === 'popupFM' && popup.onApplyFlag) {
	        this.showNotification(main_core.Loc.getMessage('CATALOG_ENTITY_CARD_FILE_CLOSE_NOTIFICATION'), {
	          autoHideDelay: 5000
	        });
	      }
	    }
	  }, {
	    key: "onEditorInitHandler",
	    value: function onEditorInitHandler(event) {
	      var _event$getCompatData7 = event.getCompatData(),
	          _event$getCompatData8 = babelHelpers.slicedToArray(_event$getCompatData7, 2),
	          editor = _event$getCompatData8[0],
	          fields = _event$getCompatData8[1];

	      if (editor && !fields.entityId) {
	        var control = editor.getControlByIdRecursive('NAME');

	        if (control) {
	          requestAnimationFrame(function () {
	            control.focus();
	          });
	        }
	      }
	    }
	    /**
	     * @returns {BX.UI.EntityEditor|null}
	     */

	  }, {
	    key: "getEditorInstance",
	    value: function getEditorInstance() {
	      if (main_core.Reflection.getClass('BX.UI.EntityEditor')) {
	        return BX.UI.EntityEditor.getDefault();
	      }

	      return null;
	    }
	  }, {
	    key: "onEditorAjaxSubmit",
	    value: function onEditorAjaxSubmit(event) {
	      var _event$getCompatData9 = event.getCompatData(),
	          _event$getCompatData10 = babelHelpers.slicedToArray(_event$getCompatData9, 2),
	          fields = _event$getCompatData10[0],
	          response = _event$getCompatData10[1];

	      var title = fields['NAME-CODE'].NAME || '';
	      this.changePageTitle(title);

	      if (response.data) {
	        if (main_core.Type.isBoolean(response.data.IS_SIMPLE_PRODUCT)) {
	          this.isSimpleProduct = response.data.IS_SIMPLE_PRODUCT;
	        }
	      }

	      if (response.status === 'success') {
	        this.removeFileHiddenInputs();
	      }
	    }
	  }, {
	    key: "onEntityCreateHandler",
	    value: function onEntityCreateHandler(event) {
	      var _event$getCompatData11 = event.getCompatData(),
	          _event$getCompatData12 = babelHelpers.slicedToArray(_event$getCompatData11, 1),
	          data = _event$getCompatData12[0];

	      this.postSliderMessage('onCreate', data);
	    }
	  }, {
	    key: "onEntityUpdateHandler",
	    value: function onEntityUpdateHandler(event) {
	      var _event$getCompatData13 = event.getCompatData(),
	          _event$getCompatData14 = babelHelpers.slicedToArray(_event$getCompatData13, 1),
	          data = _event$getCompatData14[0];

	      this.postSliderMessage('onUpdate', data);
	    }
	  }, {
	    key: "postSliderMessage",
	    value: function postSliderMessage(action, fields) {
	      BX.SidePanel.Instance.postMessage(window, "Catalog.".concat(this.getEntityType(), "Card::").concat(action), fields);
	    }
	  }, {
	    key: "changePageTitle",
	    value: function changePageTitle(title) {
	      var titleNode = document.getElementById('pagetitle');

	      if (main_core.Type.isDomNode(titleNode)) {
	        titleNode.innerText = title;
	      }

	      document.title = title;

	      if (BX.getClass('BX.SidePanel.Instance.updateBrowserTitle')) {
	        BX.SidePanel.Instance.updateBrowserTitle();
	      }
	    }
	  }, {
	    key: "removeFileHiddenInputs",
	    value: function removeFileHiddenInputs() {
	      document.querySelectorAll('form>input[type="hidden"]').forEach(function (input) {
	        var name = input.getAttribute('name');
	        var deleteInput = document.querySelector("form>input[name=\"".concat(name, "_del\"]"));

	        if (deleteInput) {
	          main_core.Dom.remove(input);
	          main_core.Dom.remove(deleteInput);
	        }
	      });
	    }
	  }, {
	    key: "onConfigurationManagerInit",
	    value: function onConfigurationManagerInit(event) {
	      var _event$getCompatData15 = event.getCompatData(),
	          _event$getCompatData16 = babelHelpers.slicedToArray(_event$getCompatData15, 2),
	          eventArgs = _event$getCompatData16[1];

	      if (!eventArgs.type || eventArgs.type === 'editor') {
	        eventArgs.configurationFieldManager = this.initializeIblockFieldConfigurationManager(eventArgs);
	      }

	      if (eventArgs.id === 'variation_grid') {
	        eventArgs.configurationFieldManager = this.initializeVariationPropertyConfigurationManager(eventArgs);
	      }
	    }
	  }, {
	    key: "initializeIblockFieldConfigurationManager",
	    value: function initializeIblockFieldConfigurationManager(eventArgs) {
	      var configurationManager = IblockFieldConfigurationManager.create(this.id, eventArgs);
	      configurationManager.setCreationPageUrl(this.settings.creationPropertyUrl);
	      return configurationManager;
	    }
	  }, {
	    key: "initializeVariationPropertyConfigurationManager",
	    value: function initializeVariationPropertyConfigurationManager(eventArgs) {
	      var configurationManager = GridFieldConfigurationManager.create(this.id, eventArgs);
	      configurationManager.setCreationPageUrl(this.settings.creationVariationPropertyUrl);
	      return configurationManager;
	    }
	  }, {
	    key: "showNotification",
	    value: function showNotification(content, options) {
	      options = options || {};

	      if (BX.GetWindowScrollPos().scrollTop <= 10) {
	        options.stack = this.getStackWithOffset();
	      }

	      BX.UI.Notification.Center.notify({
	        content: content,
	        stack: options.stack || null,
	        position: 'top-right',
	        width: 'auto',
	        category: options.category || null,
	        autoHideDelay: options.autoHideDelay || 3000
	      });
	    }
	  }, {
	    key: "getStackWithOffset",
	    value: function getStackWithOffset() {
	      if (this.stackWithOffset === null) {
	        this.stackWithOffset = new BX.UI.Notification.Stack(BX.mergeEx({}, BX.UI.Notification.Center.getStackDefaults(), {
	          id: 'top-right-with-offset',
	          position: 'top-right-with-offset',
	          offsetY: 74
	        }));
	      }

	      return this.stackWithOffset;
	    }
	  }, {
	    key: "openFeedbackPanel",
	    value: function openFeedbackPanel() {
	      if (!main_core.Reflection.getClass('BX.SidePanel.Instance') || !main_core.Type.isStringFilled(this.feedbackUrl)) {
	        return;
	      }

	      BX.SidePanel.Instance.open(this.feedbackUrl, {
	        cacheable: false,
	        allowChangeHistory: false,
	        width: 580
	      });
	    }
	  }, {
	    key: "bindCreateDocumentButtonMenu",
	    value: function bindCreateDocumentButtonMenu() {
	      var createDocumentButtonMenu = this.getCreateDocumentButtonMenu();

	      if (createDocumentButtonMenu) {
	        main_core.Event.bind(createDocumentButtonMenu.getContainer(), 'click', this.showCreateDocumentPopup.bind(this));
	      }
	    }
	  }, {
	    key: "getCreateDocumentButtonMenu",
	    value: function getCreateDocumentButtonMenu() {
	      var createDocumentButton = BX.UI.ButtonManager.getByUniqid(this.createDocumentButtonId);

	      if (createDocumentButton) {
	        return BX.UI.ButtonManager.getByUniqid(this.createDocumentButtonId).getMenuButton();
	      }

	      return null;
	    }
	  }, {
	    key: "getCreateDocumentPopup",
	    value: function getCreateDocumentPopup() {
	      if (!this.createDocumentPopup) {
	        this.createDocumentPopup = new main_popup.Popup(this.id + '-create-document', this.getCreateDocumentButtonMenu().getContainer(), {
	          autoHide: true,
	          draggable: false,
	          offsetLeft: 0,
	          offsetTop: 0,
	          angle: {
	            position: 'top',
	            offset: 43
	          },
	          noAllPaddings: true,
	          bindOptions: {
	            forceBindPosition: true
	          },
	          closeByEsc: true,
	          content: this.getCreateDocumentMenuContent()
	        });
	      }

	      return this.createDocumentPopup;
	    }
	  }, {
	    key: "showCreateDocumentPopup",
	    value: function showCreateDocumentPopup() {
	      this.getCreateDocumentPopup().show();
	    }
	  }, {
	    key: "getCreateDocumentMenuContent",
	    value: function getCreateDocumentMenuContent() {
	      var popupWrapper = main_core.Tag.render(_templateObject$6 || (_templateObject$6 = babelHelpers.taggedTemplateLiteral(["<div class=\"menu-popup\"></div>"])));
	      var popupItemsContainer = main_core.Tag.render(_templateObject2$5 || (_templateObject2$5 = babelHelpers.taggedTemplateLiteral(["<div class=\"menu-popup-items\"></div>"])));
	      popupWrapper.appendChild(popupItemsContainer);
	      this.createDocumentButtonMenuPopupItems.forEach(function (item) {
	        popupItemsContainer.appendChild(main_core.Tag.render(_templateObject3$5 || (_templateObject3$5 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<a class=\"menu-popup-item menu-popup-item-no-icon\" href=\"", "\">\n\t\t\t\t\t<span class=\"menu-popup-item-text\">", "</span>\n\t\t\t\t</a>\n\t\t\t"])), item.link, item.text));
	      });
	      return popupWrapper;
	    }
	  }, {
	    key: "getCardSettingsPopup",
	    value: function getCardSettingsPopup() {
	      if (!this.settingsPopup) {
	        this.settingsPopup = new main_popup.Popup(this.id, this.getSettingsButton().getContainer(), {
	          autoHide: true,
	          draggable: false,
	          offsetLeft: 0,
	          offsetTop: 0,
	          angle: {
	            position: 'top',
	            offset: 43
	          },
	          noAllPaddings: true,
	          bindOptions: {
	            forceBindPosition: true
	          },
	          closeByEsc: true,
	          content: this.prepareCardSettingsContent()
	        });
	      }

	      return this.settingsPopup;
	    }
	  }, {
	    key: "showCardSettingsPopup",
	    value: function showCardSettingsPopup() {
	      var _this2 = this;

	      var okCallback = function okCallback() {
	        return _this2.getCardSettingsPopup().show();
	      };

	      var variationGridInstance = main_core.Reflection.getClass('BX.Catalog.VariationGrid.Instance');

	      if (variationGridInstance) {
	        variationGridInstance.askToLossGridData(okCallback);
	      } else {
	        okCallback();
	      }
	    }
	  }, {
	    key: "prepareCardSettingsContent",
	    value: function prepareCardSettingsContent() {
	      var _this3 = this;

	      var content = main_core.Tag.render(_templateObject4$5 || (_templateObject4$5 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<div class='ui-entity-editor-popup-create-field-list'></div>\n\t\t"])));
	      this.cardSettings.map(function (item) {
	        content.append(_this3.getSettingItem(item));
	      });
	      return content;
	    }
	  }, {
	    key: "getSettingItem",
	    value: function getSettingItem(item) {
	      var _item$disabled,
	          _this4 = this;

	      var input = main_core.Tag.render(_templateObject5$5 || (_templateObject5$5 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<input type=\"checkbox\">\n\t\t"])));
	      input.checked = item.checked;
	      input.disabled = (_item$disabled = item.disabled) !== null && _item$disabled !== void 0 ? _item$disabled : false;
	      input.dataset.settingId = item.id;
	      var hintNode = main_core.Type.isStringFilled(item.hint) ? main_core.Tag.render(_templateObject6$3 || (_templateObject6$3 = babelHelpers.taggedTemplateLiteral(["<span class=\"catalog-entity-setting-hint\" data-hint=\"", "\"></span>"])), item.hint) : '';
	      var setting = main_core.Tag.render(_templateObject7$2 || (_templateObject7$2 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<label class=\"ui-ctl-block ui-entity-editor-popup-create-field-item ui-ctl-w100\">\n\t\t\t\t\t<div class=\"ui-ctl-w10\" style=\"text-align: center\">", "</div>\n\t\t\t\t\t<div class=\"ui-ctl-w75\">\n\t\t\t\t\t\t<span class=\"ui-entity-editor-popup-create-field-item-title ", "\">", "", "</span>\n\t\t\t\t\t\t<span class=\"ui-entity-editor-popup-create-field-item-desc\">", "</span>\n\t\t\t\t\t</div>\n\t\t\t\t</label>\n\t\t\t"])), input, item.disabled ? 'catalog-entity-disabled-setting' : '', item.title, hintNode, item.desc);
	      BX.UI.Hint.init(setting);

	      if (item.id === 'SLIDER') {
	        main_core.Event.bind(setting, 'change', function (event) {
	          new catalog_storeUse.Slider().open(item.url, {}).then(function () {
	            _this4.reloadGrid();

	            _this4.getCardSettingsPopup().close();
	          });
	        });
	      } else {
	        main_core.Event.bind(setting, 'change', this.setProductCardSetting.bind(this));
	      }

	      return setting;
	    }
	  }, {
	    key: "setProductCardSetting",
	    value: function setProductCardSetting(event) {
	      var settingItem = this.getCardSetting(event.target.dataset.settingId);

	      if (!settingItem) {
	        return;
	      }

	      var settingEnabled = event.target.checked;

	      if (settingItem.action === 'grid') {
	        this.requestGridSettings(settingItem, settingEnabled);
	      } else {
	        this.requestCardSettings(settingItem, settingEnabled);
	      }
	    }
	  }, {
	    key: "onSliderMessage",
	    value: function onSliderMessage(event) {
	      var _event$getCompatData17 = event.getCompatData(),
	          _event$getCompatData18 = babelHelpers.slicedToArray(_event$getCompatData17, 1),
	          sliderEvent = _event$getCompatData18[0];

	      if (sliderEvent.getEventId() === 'Catalog.VariationCard::onCreate' || sliderEvent.getEventId() === 'Catalog.VariationCard::onUpdate') {
	        this.reloadVariationGrid();
	      }
	    }
	  }, {
	    key: "reloadGrid",
	    value: function reloadGrid() {
	      document.location.reload();
	    }
	  }, {
	    key: "requestGridSettings",
	    value: function requestGridSettings(setting, enabled) {
	      var _this5 = this;

	      if (!this.getVariationGrid()) ;

	      var headers = [];
	      var cells = this.getVariationGrid().getRows().getHeadFirstChild().getCells();
	      Array.from(cells).forEach(function (header) {
	        if ('name' in header.dataset) {
	          headers.push(header.dataset.name);
	        }
	      });
	      BX.ajax.runComponentAction(this.componentName, 'setGridSetting', {
	        mode: 'class',
	        data: {
	          signedParameters: this.componentSignedParams,
	          settingId: setting.id,
	          selected: enabled,
	          currentHeaders: headers
	        }
	      }).then(function () {
	        var message = null;
	        setting.checked = enabled;

	        _this5.reloadVariationGrid();

	        _this5.postSliderMessage('onUpdate', {});

	        _this5.getCardSettingsPopup().close();

	        if (setting.id === 'WAREHOUSE') {
	          _this5.reloadGrid();

	          message = enabled ? main_core.Loc.getMessage('CATALOG_ENTITY_CARD_WAREHOUSE_ENABLED') : main_core.Loc.getMessage('CATALOG_ENTITY_CARD_WAREHOUSE_DISABLED');
	        } else {
	          message = enabled ? main_core.Loc.getMessage('CATALOG_ENTITY_CARD_SETTING_ENABLED') : main_core.Loc.getMessage('CATALOG_ENTITY_CARD_SETTING_DISABLED');
	          message = message.replace('#NAME#', setting.title);
	        }

	        _this5.showNotification(message, {
	          category: 'popup-settings'
	        });
	      });
	    }
	  }, {
	    key: "requestCardSettings",
	    value: function requestCardSettings(setting, enabled) {
	      var _this6 = this;

	      BX.ajax.runComponentAction(this.componentName, 'setCardSetting', {
	        mode: 'class',
	        data: {
	          signedParameters: this.componentSignedParams,
	          settingId: setting.id,
	          selected: enabled
	        }
	      }).then(function () {
	        setting.checked = enabled;

	        if (setting.id === 'CATALOG_PARAMETERS') {
	          var section = _this6.getEditorInstance().getControlByIdRecursive('catalog_parameters');

	          if (section) {
	            section.refreshLayout();
	          }
	        }

	        _this6.getCardSettingsPopup().close();

	        var message = enabled ? main_core.Loc.getMessage('CATALOG_ENTITY_CARD_SETTING_ENABLED') : main_core.Loc.getMessage('CATALOG_ENTITY_CARD_SETTING_DISABLED');

	        _this6.showNotification(message.replace('#NAME#', setting.title), {
	          category: 'popup-settings'
	        });
	      });
	    }
	  }, {
	    key: "updateSettingsCheckboxState",
	    value: function updateSettingsCheckboxState() {
	      var _this7 = this;

	      var popupContainer = this.getCardSettingsPopup().getContentContainer();
	      this.cardSettings.filter(function (item) {
	        return item.action === 'grid' && main_core.Type.isArray(item.columns);
	      }).forEach(function (item) {
	        var allColumnsExist = true;
	        item.columns.forEach(function (columnName) {
	          if (!_this7.getVariationGrid().getColumnHeaderCellByName(columnName)) {
	            allColumnsExist = false;
	          }
	        });
	        var checkbox = popupContainer.querySelector('input[data-setting-id="' + item.id + '"]');

	        if (main_core.Type.isDomNode(checkbox)) {
	          checkbox.checked = allColumnsExist;
	        }
	      });
	    }
	  }]);
	  return EntityCard;
	}(BaseCard);

	exports.EntityCard = EntityCard;
	exports.BaseCard = BaseCard;

}((this.BX.Catalog.EntityCard = this.BX.Catalog.EntityCard || {}),BX,BX,BX,BX,BX.Event,BX.Main,BX,BX.Catalog.StoreUse));
//# sourceMappingURL=entity-card.bundle.js.map
