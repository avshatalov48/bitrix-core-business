this.BX = this.BX || {};
this.BX.Catalog = this.BX.Catalog || {};
(function (exports,ui_entityEditor,ui_notification,main_core_events,translit,main_core) {
	'use strict';

	var LazyLoader =
	/*#__PURE__*/
	function () {
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
	        this.startRequest(babelHelpers.objectSpread({}, this.params, {
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

	var Tab =
	/*#__PURE__*/
	function () {
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
	      this.loader = new LazyLoader(this.id, babelHelpers.objectSpread({}, this.data.loader, {
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

	var Manager =
	/*#__PURE__*/
	function () {
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
	      this.items.forEach(function (current) {
	        return current.setActive(current === item);
	      });
	    }
	  }]);
	  return Manager;
	}();

	function _templateObject5() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"ui-entity-editor-content-block\">\n\t\t\t\t\t<span class=\"ui-tile-selector-selector-wrap readonly\">\n\t\t\t\t\t\t", "\n\t\t\t\t\t</span>\n\t\t\t\t</div>"]);

	  _templateObject5 = function _templateObject5() {
	    return data;
	  };

	  return data;
	}

	function _templateObject4() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t\t<span class=\"ui-tile-selector-item ui-tile-selector-item-readonly-yes\">\n\t\t\t\t\t\t<span data-role=\"tile-item-name\">", "</span>\n\t\t\t\t\t</span>\n\t\t\t\t"]);

	  _templateObject4 = function _templateObject4() {
	    return data;
	  };

	  return data;
	}

	function _templateObject3() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"ui-entity-editor-content-block\">\n\t\t\t\t\t", "\n\t\t\t\t</div>\n\t\t\t"]);

	  _templateObject3 = function _templateObject3() {
	    return data;
	  };

	  return data;
	}

	function _templateObject2() {
	  var data = babelHelpers.taggedTemplateLiteral(["<div class=\"ui-entity-editor-content-block\"></div>"]);

	  _templateObject2 = function _templateObject2() {
	    return data;
	  };

	  return data;
	}

	function _templateObject() {
	  var data = babelHelpers.taggedTemplateLiteral(["<input type=\"hidden\" name=\"", "[]\" value=\"0\">"]);

	  _templateObject = function _templateObject() {
	    return data;
	  };

	  return data;
	}

	var IblockSectionField =
	/*#__PURE__*/
	function (_BX$UI$EntityEditorFi) {
	  babelHelpers.inherits(IblockSectionField, _BX$UI$EntityEditorFi);

	  function IblockSectionField(id, settings) {
	    var _this;

	    babelHelpers.classCallCheck(this, IblockSectionField);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(IblockSectionField).call(this));

	    _this.initialize(id, settings);

	    _this.innerWrapper = null;
	    _this.tileSelector = null;
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
	      this.defaultInput = main_core.Tag.render(_templateObject(), this.getName());

	      this._wrapper.appendChild(this.defaultInput);

	      this.innerWrapper = main_core.Tag.render(_templateObject2());

	      this._wrapper.appendChild(this.innerWrapper);

	      main_core.ajax.runComponentAction('bitrix:catalog.productcard.iblocksectionfield', 'lazyLoad', {
	        mode: 'ajax',
	        data: {
	          iblockId: this.getIblockId(),
	          productId: this.getProductId(),
	          selectedSectionIds: this.getValue()
	        }
	      }).then(this.renderFromResponse.bind(this)).catch(function (response) {
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
	        callback: this.initTileSelector.bind(this)
	      });
	    }
	  }, {
	    key: "initTileSelector",
	    value: function initTileSelector() {
	      if (BX.UI && BX.UI.TileSelector) {
	        this.tileSelector = BX.UI.TileSelector.getById(this.getTileSelectorId());

	        if (!this.tileSelector) {
	          throw new Error('Tile selector `' + this.getTileSelectorId() + '` not found.');
	        }

	        if (this.tileSelector) {
	          this.changeDisplay(this.tileSelector.buttonAdd, false);
	        }

	        main_core_events.EventEmitter.subscribe(this.tileSelector, this.tileSelector.events.buttonSelectFirst, this.tileSelectorSelectFirstHandler.bind(this));
	        main_core_events.EventEmitter.subscribe(this.tileSelector, this.tileSelector.events.input, this.onInputSearch.bind(this));
	        main_core_events.EventEmitter.subscribe(this.tileSelector, this.tileSelector.events.buttonAdd, this.onButtonAdd.bind(this));
	        main_core_events.EventEmitter.subscribe(this.tileSelector, this.tileSelector.events.buttonSelect, this.onButtonSelect.bind(this));
	        main_core_events.EventEmitter.subscribe(this.tileSelector, this.tileSelector.events.tileAdd, this.markAsChanged.bind(this));
	        main_core_events.EventEmitter.subscribe(this.tileSelector, this.tileSelector.events.tileRemove, this.markAsChanged.bind(this));
	        main_core_events.EventEmitter.subscribe(this.tileSelector, this.tileSelector.events.search, this.onInputEnd.bind(this));
	        main_core_events.EventEmitter.subscribe(this.tileSelector, this.tileSelector.events.searcherInit, this.onSearcherInit.bind(this));
	        main_core.Event.bind(this.tileSelector.input, 'blur', this.onBlur.bind(this));

	        if (this.tileSelector.buttonAdd) {
	          this.tileSelector.buttonAdd.style.top = 'auto';
	          this.tileSelector.buttonAdd.style.bottom = 0;
	        }
	      }
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
	    key: "onBlur",
	    value: function onBlur() {
	      var _this2 = this;

	      window.setTimeout(function () {
	        _this2.tileSelector.onInputEnd();
	      }, 500);
	    }
	  }, {
	    key: "onSearcherInit",
	    value: function onSearcherInit(event) {
	      var _event$getData = event.getData(),
	          _event$getData2 = babelHelpers.slicedToArray(_event$getData, 1),
	          seacher = _event$getData2[0];

	      var popup = BX.PopupWindowManager.getPopupById(seacher.id);

	      if (popup) {
	        popup.destroy();
	      }

	      if (seacher.categoryContainer) {
	        seacher.categoryContainer.parentNode.removeChild(seacher.categoryContainer);
	      }
	    }
	  }, {
	    key: "onInputEnd",
	    value: function onInputEnd() {
	      this.changeDisplay(this.tileSelector.buttonAdd, false);
	    }
	  }, {
	    key: "getTileSelectorId",
	    value: function getTileSelectorId() {
	      var iblockId = this.getIblockId() || 0;
	      var productId = this.getProductId() || 0;
	      return "catalog-iblocksectionfield-".concat(iblockId, "-").concat(productId);
	    }
	  }, {
	    key: "onButtonSelect",
	    value: function onButtonSelect() {
	      if (this.tileSelector) {
	        this.tileSelector.showSearcher();
	      }
	    }
	  }, {
	    key: "onButtonAdd",
	    value: function onButtonAdd() {
	      if (!BX.type.isNotEmptyString(this.tileSelector.input.value)) {
	        return;
	      }

	      main_core.ajax.runComponentAction('bitrix:catalog.productcard.iblocksectionfield', 'addSection', {
	        mode: 'ajax',
	        data: {
	          iblockId: this.getIblockId(),
	          name: this.tileSelector.input.value
	        }
	      }).then(this.onAddSection.bind(this)).catch(function (response) {
	        throw new Error(response.errors.join("\n"));
	      });
	    }
	  }, {
	    key: "onAddSection",
	    value: function onAddSection(response) {
	      var item = this.tileSelector.searcher.addItem('all', response.data.id, response.data.name);
	      this.tileSelector.searcher.onItemClick(item);

	      if (this.tileSelector.searcher.popup) {
	        this.tileSelector.searcher.popup.destroy();
	        this.tileSelector.searcher.popup = null;
	      }

	      this.changeDisplay(item.node, false);
	      this.tileSelector.onInputEnd();
	      this.onInputEnd();
	    }
	  }, {
	    key: "onInputSearch",
	    value: function onInputSearch() {
	      var name = this.tileSelector.input.value;
	      var regexp = new RegExp(BX.util.escapeRegExp(name), 'i');

	      if (!this.tileSelector.searcher) {
	        return;
	      }

	      var filtered = this.tileSelector.searcher.items.filter(function (item) {
	        return regexp.test(item.name);
	      });

	      if (filtered.length === 0) {
	        this.changeDisplay(this.tileSelector.buttonAdd, true);
	        this.tileSelector.searcher.hide();
	      } else {
	        this.changeDisplay(this.tileSelector.buttonAdd, false);
	        this.tileSelector.searcher.show();
	      }
	    }
	  }, {
	    key: "tileSelectorSelectFirstHandler",
	    value: function tileSelectorSelectFirstHandler() {
	      var _this3 = this;

	      main_core.ajax.runComponentAction('bitrix:catalog.productcard.iblocksectionfield', 'getSections', {
	        mode: 'ajax',
	        data: {
	          iblockId: this.getIblockId()
	        }
	      }).then(function (response) {
	        _this3.tileSelector.setSearcherData(response.data || []);
	      }).catch(this.tileSelector.hideSearcher.bind(this.tileSelector));
	    }
	  }, {
	    key: "drawViewMode",
	    value: function drawViewMode() {
	      if (this.hasNoSections()) {
	        this.innerWrapper = main_core.Tag.render(_templateObject3(), main_core.Loc.getMessage("CATALOG_ENTITY_CARD_EMPTY_SECTION"));
	        main_core.Dom.addClass(this._wrapper, 'ui-entity-editor-content-block-click-empty');
	      } else {
	        var content = [];
	        Object.entries(this.getSections()).forEach(function (_ref) {
	          var _ref2 = babelHelpers.slicedToArray(_ref, 2),
	              id = _ref2[0],
	              name = _ref2[1];

	          // ui-tile-selector-item-%type%
	          content.push(main_core.Tag.render(_templateObject4(), main_core.Text.encode(name)));
	        });
	        this.innerWrapper = main_core.Tag.render(_templateObject5(), content);
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
	      if (this.tileSelector) {
	        main_core_events.EventEmitter.unsubscribeAll(this.tileSelector, this.tileSelector.events.buttonSelect);
	        main_core_events.EventEmitter.unsubscribeAll(this.tileSelector, this.tileSelector.events.buttonSelectFirst);
	        main_core_events.EventEmitter.unsubscribeAll(this.tileSelector, this.tileSelector.events.tileAdd);
	        main_core_events.EventEmitter.unsubscribeAll(this.tileSelector, this.tileSelector.events.tileRemove);
	        main_core_events.EventEmitter.unsubscribeAll(this.tileSelector, this.tileSelector.events.searcherInit);
	        main_core_events.EventEmitter.unsubscribeAll(this.tileSelector, this.tileSelector.events.search);
	        main_core_events.EventEmitter.unsubscribeAll(this.tileSelector, this.tileSelector.events.buttonAdd);
	      }

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

	var FieldsFactory =
	/*#__PURE__*/
	function () {
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
	      }

	      return null;
	    }
	  }]);
	  return FieldsFactory;
	}();

	var PROPERTY_PREFIX = 'PROPERTY_';
	var PROPERTY_BLOCK_NAME = 'properties';

	var IblockSectionController =
	/*#__PURE__*/
	function (_BX$UI$EntityEditorCo) {
	  babelHelpers.inherits(IblockSectionController, _BX$UI$EntityEditorCo);

	  function IblockSectionController(id, settings) {
	    var _this;

	    babelHelpers.classCallCheck(this, IblockSectionController);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(IblockSectionController).call(this));

	    _this.initialize(id, settings);

	    _this.isRequesting = false;

	    _this.clearServiceFields();

	    main_core_events.EventEmitter.subscribe(_this._editor, 'IblockSectionField:onChange', _this.onChangeHandler.bind(babelHelpers.assertThisInitialized(_this)));
	    main_core_events.EventEmitter.subscribe(_this._editor, 'BX.UI.EntityEditor:onFieldCreate', _this.onFieldAdd.bind(babelHelpers.assertThisInitialized(_this)));
	    main_core_events.EventEmitter.subscribe(_this._editor, 'BX.UI.EntityEditor:onFieldModify', _this.onFieldUpdate.bind(babelHelpers.assertThisInitialized(_this)));
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
	    key: "onChangeHandler",
	    value: function onChangeHandler(event) {
	      var _this2 = this;

	      var _event$getData = event.getData(),
	          _event$getData2 = babelHelpers.slicedToArray(_event$getData, 1),
	          field = _event$getData2[0];

	      var newData = field.tileSelector.list.map(function (tile) {
	        return tile.id;
	      });
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
	    key: "onFieldAdd",
	    value: function onFieldAdd(event) {
	      var _this3 = this;

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
	        var propertySection = _this3._editor.getSchemeElementByName(PROPERTY_BLOCK_NAME);

	        var property = response.data.PROPERTY_FIELDS;

	        if (!propertySection || !property) {
	          return;
	        }

	        var additionalValues = response.data.ADDITIONAL_VALUES;

	        if (additionalValues) {
	          var model = _this3._editor._model;

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

	        var control = _this3.createProperty(property, {
	          layout: {
	            notifyIfNotDisplayed: true,
	            forceDisplay: eventArgs.showAlways
	          },
	          mode: mode
	        });

	        control.toggleOptionFlag(eventArgs.showAlways);

	        _this3._editor.saveSchemeChanges();

	        _this3.isRequesting = false;
	      }).catch(function (response) {
	        _this3.isRequesting = false;
	      });
	    }
	  }, {
	    key: "onFieldUpdate",
	    value: function onFieldUpdate(event) {
	      var _this4 = this;

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
	        if (currentField instanceof BX.UI.EntityEditorDatetime || currentField instanceof BX.UI.EntityEditorMultiDatetime) {
	          var data = currentField.getSchemeElement().getData();
	          data.enableTime = eventArgs.enableTime;
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

	          schemeElement = currentField.getSchemeElement();
	        }

	        var property = response.data.PROPERTY_FIELDS;

	        if ((currentField instanceof BX.UI.EntityEditorList || currentField instanceof BX.UI.EntityEditorMultiList) && property) {
	          schemeElement = BX.UI.EntitySchemeElement.create(property);
	          newType = property.type;
	        }

	        if (newType) {
	          var index = section.getChildIndex(currentField);

	          var newControl = _this4._editor.createControl(newType, eventArgs.CODE, {
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
	          section.removeChild(currentField, {
	            enableSaving: false
	          });
	        }

	        _this4.isRequesting = false;
	      }).catch(function (response) {
	        _this4.isRequesting = false;
	      });
	    }
	  }, {
	    key: "getFieldsForm",
	    value: function getFieldsForm(fields) {
	      var _this5 = this;

	      var form = new FormData();
	      var formatted = {
	        NAME: fields.label,
	        MULTIPLE: fields.multiple ? 'Y' : 'N',
	        IS_REQUIRED: fields.mandatory ? 'Y' : 'N',
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
	          fields.enumeration.forEach(function (enumItem, key) {
	            form.append(_this5.getFormFieldName('VALUES][' + key + '][SORT'), enumItem.SORT);
	            form.append(_this5.getFormFieldName('VALUES][' + key + '][VALUE'), enumItem.VALUE);
	            form.append(_this5.getFormFieldName('VALUES][' + key + '][ID'), enumItem.ID);
	          });
	          break;

	        case 'directory':
	          formatted.USER_TYPE = 'directory';
	          fields.enumeration.forEach(function (enumItem, key) {
	            form.append(_this5.getFormFieldName('VALUES][' + key + '][SORT'), enumItem.SORT);
	            form.append(_this5.getFormFieldName('VALUES][' + key + '][VALUE'), enumItem.VALUE.value);
	            form.append(_this5.getFormFieldName('VALUES][' + key + '][XML_ID'), enumItem.XML_ID);
	            form.append(_this5.getFormFieldName('VALUES][' + key + '][FILE_ID'), enumItem.FILE_ID);
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
	    key: "refreshLinkedProperties",
	    value: function refreshLinkedProperties(sectionIds) {
	      var _this6 = this;

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
	        var allCurrentProperties = _this6.getAllCurrentProperties();

	        if (_this6.initialElements === null) {
	          _this6.initialElements = babelHelpers.toConsumableArray(allCurrentProperties);
	        }

	        response.data.ENTITY_FIELDS.forEach(function (property) {
	          if (!allCurrentProperties.includes(property.name)) {
	            _this6.addProperty(property, {
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
	            _this6.removeProperty(name);
	          }
	        });

	        _this6._editor.commitSchemeChanges();

	        _this6.isRequesting = false;
	      }).catch(function (response) {
	        _this6.isRequesting = false;
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
	      control.getParent().addChild(control, babelHelpers.objectSpread({}, options, {
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

	      propertyBlockControl.addChild(control, babelHelpers.objectSpread({}, options, {
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
	      var _this7 = this;

	      babelHelpers.get(babelHelpers.getPrototypeOf(IblockSectionController.prototype), "rollback", this).call(this);

	      if (this.initialElements === null) {
	        return;
	      }

	      var allCurrentProperties = this.getAllCurrentProperties();
	      allCurrentProperties.forEach(function (element) {
	        if (!_this7.initialElements.includes(element)) {
	          _this7.removeProperty(element);
	        }
	      });
	      this.initialElements.forEach(function (element) {
	        if (!allCurrentProperties.includes(element)) {
	          _this7.addProperty({
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

	var VariationGridController =
	/*#__PURE__*/
	function (_BX$UI$EntityEditorCo) {
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
	    }
	  }, {
	    key: "onAfterSave",
	    value: function onAfterSave() {
	      if (this.isChanged()) {
	        this.setGridControlCache(null);
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
	      var gridComponent = this.getVariationGridComponent();

	      if (gridComponent) {
	        gridComponent.unsubscribeCustomEvents();
	      }
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
	          eventArgs = _event$getCompatData4[1];

	      eventArgs.sessid = BX.bitrix_sessid();
	      eventArgs.method = 'POST';
	      eventArgs.url = this.getReloadUrl();
	      eventArgs.data = babelHelpers.objectSpread({}, eventArgs.data, {
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
	      var skuGridData = grid.getRows().getEditSelectedValues(); // replace sku custom properties edit data names with original names

	      for (var id in skuGridData) {
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
	      }

	      if (!main_core.Type.isPlainObject(eventArgs.options)) {
	        eventArgs.options = {};
	      }

	      if (!main_core.Type.isPlainObject(eventArgs.options.data)) {
	        eventArgs.options.data = {};
	      }

	      eventArgs.options.data[skuGridName] = skuGridData;
	      this.areaHeight = this.getGridControl().getWrapper().offsetHeight;
	    }
	  }]);
	  return VariationGridController;
	}(BX.UI.EntityEditorController);

	var ControllersFactory =
	/*#__PURE__*/
	function () {
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
	      if (type === 'iblock_section') {
	        return new IblockSectionController(controlId, settings);
	      }

	      if (type === 'variation_grid') {
	        return new VariationGridController(controlId, settings);
	      }

	      return null;
	    }
	  }]);
	  return ControllersFactory;
	}();

	function _templateObject5$1() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"ui-entity-editor-content-remove-block\"></div>\n\t\t\t"]);

	  _templateObject5$1 = function _templateObject5() {
	    return data;
	  };

	  return data;
	}

	function _templateObject4$1() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<input \n\t\t\t\t\tclass=\"ui-ctl-element\" \n\t\t\t\t\tvalue=\"", "\"\n\t\t\t\t\tplaceholder=\"", "\"\n\t\t\t\t>\n\t\t\t"]);

	  _templateObject4$1 = function _templateObject4() {
	    return data;
	  };

	  return data;
	}

	function _templateObject3$1() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<label class=\"catalog-dictionary-item ", "\">\n\t\t\t\t<img src=\"", "\" alt=\"\">\n\t\t\t\t", "\n\t\t\t</label>\n\t\t\t"]);

	  _templateObject3$1 = function _templateObject3() {
	    return data;
	  };

	  return data;
	}

	function _templateObject2$1() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<input class=\"input-image-hidden\" value=\"", "\" type=\"file\" accept=\"image/*\">\n\t\t\t"]);

	  _templateObject2$1 = function _templateObject2() {
	    return data;
	  };

	  return data;
	}

	function _templateObject$1() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"ui-ctl ui-ctl-textbox ui-ctl-w100 ui-ctl-row\"></div>\n\t\t\t"]);

	  _templateObject$1 = function _templateObject() {
	    return data;
	  };

	  return data;
	}

	var IblockDirectoryFieldItem =
	/*#__PURE__*/
	function (_BX$UI$EntityEditorUs) {
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

	      this._wrapper = main_core.Tag.render(_templateObject$1());
	      this._fileInput = main_core.Tag.render(_templateObject2$1(), BX.prop.getString(this._data, 'FILE_ID', ''));
	      main_core.Event.bind(this._fileInput, 'change', this.onFileLoaderChange.bind(this));
	      var link = BX.prop.getString(this._data, 'IMAGE_SRC', '');

	      this._wrapper.appendChild(main_core.Tag.render(_templateObject3$1(), link === '' ? 'catalog-dictionary-item-empty' : '', link, this._fileInput));

	      var labelText = main_core.Text.encode(BX.prop.getString(this._data, 'TEXT', ''));
	      this._labelInput = main_core.Tag.render(_templateObject4$1(), labelText, BX.message('CATALOG_ENTITY_CARD_NEW_FIELD_ITEM_PLACEHOLDER'));

	      this._wrapper.appendChild(this._labelInput);

	      var deleteButton = main_core.Tag.render(_templateObject5$1());
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

	      if (textValue === '' && !this.isFileChanged()) {
	        return;
	      }

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

	function _templateObject8() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"ui-entity-editor-content-block-add-field\">\n\t\t\t\t\t", "\n\t\t\t\t</div>\n\t\t\t"]);

	  _templateObject8 = function _templateObject8() {
	    return data;
	  };

	  return data;
	}

	function _templateObject7() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"ui-entity-card-content-add-field\">\n\t\t\t\t\t", "\n\t\t\t\t</div>\n\t\t\t"]);

	  _templateObject7 = function _templateObject7() {
	    return data;
	  };

	  return data;
	}

	function _templateObject6() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"ui-entity-editor-content-block\"></div>\n\t\t\t"]);

	  _templateObject6 = function _templateObject6() {
	    return data;
	  };

	  return data;
	}

	function _templateObject5$2() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<div class=\"ui-entity-editor-content-block\">\n\t\t\t\t<div class=\"ui-entity-editor-block-title\">\n\t\t\t\t\t<span class=\"ui-entity-editor-block-title-text\">", "</span>\n\t\t\t\t</div>\n\t\t\t</div>\n\t\t"]);

	  _templateObject5$2 = function _templateObject5() {
	    return data;
	  };

	  return data;
	}

	function _templateObject4$2() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<div class=\"ui-entity-editor-content-block\"></div>\n\t\t"]);

	  _templateObject4$2 = function _templateObject4() {
	    return data;
	  };

	  return data;
	}

	function _templateObject3$2() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<div class=\"ui-entity-editor-content-block\"></div>\n\t\t"]);

	  _templateObject3$2 = function _templateObject3() {
	    return data;
	  };

	  return data;
	}

	function _templateObject2$2() {
	  var data = babelHelpers.taggedTemplateLiteral(["<hr class=\"ui-entity-editor-line\">"]);

	  _templateObject2$2 = function _templateObject2() {
	    return data;
	  };

	  return data;
	}

	function _templateObject$2() {
	  var data = babelHelpers.taggedTemplateLiteral(["<hr class=\"ui-entity-editor-line\">"]);

	  _templateObject$2 = function _templateObject() {
	    return data;
	  };

	  return data;
	}

	var IblockFieldConfigurator =
	/*#__PURE__*/
	function (_BX$UI$EntityEditorFi) {
	  babelHelpers.inherits(IblockFieldConfigurator, _BX$UI$EntityEditorFi);

	  function IblockFieldConfigurator() {
	    babelHelpers.classCallCheck(this, IblockFieldConfigurator);
	    return babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(IblockFieldConfigurator).apply(this, arguments));
	  }

	  babelHelpers.createClass(IblockFieldConfigurator, [{
	    key: "layoutInternal",
	    value: function layoutInternal() {
	      this._wrapper.appendChild(this.getInputContainer());

	      if (this._typeId === "list" || this._typeId === "multilist" || this._typeId === "directory") {
	        this._wrapper.appendChild(main_core.Tag.render(_templateObject$2()));

	        this._wrapper.appendChild(this.getEnumerationContainer());
	      }

	      this._wrapper.appendChild(this.getOptionContainer());

	      this._wrapper.appendChild(this.getErrorContainer());

	      main_core.Dom.append(main_core.Tag.render(_templateObject2$2()), this._wrapper);

	      this._wrapper.appendChild(this.getButtonContainer());
	    }
	  }, {
	    key: "getOptionContainer",
	    value: function getOptionContainer() {
	      var isNew = this._field === null;
	      this._optionWrapper = main_core.Tag.render(_templateObject3$2());

	      if (this._typeId === "datetime" || this._typeId === "multidatetime") {
	        this._isTimeEnabledCheckBox = this.getIsTimeEnabledCheckBox();
	      }

	      if (this._typeId !== "boolean" && this._enableMandatoryControl) {
	        this._isRequiredCheckBox = this.getIsRequiredCheckBox();
	      }

	      if (this._typeId !== "directory") {
	        this._isMultipleCheckBox = this.getMultipleCheckBox();
	      } //region Show Always


	      this._showAlwaysCheckBox = this.createOption({
	        caption: BX.message("UI_ENTITY_EDITOR_SHOW_ALWAYS"),
	        helpUrl: "https://helpdesk.bitrix24.ru/open/7046149/",
	        helpCode: "9627471"
	      });
	      this._showAlwaysCheckBox.checked = isNew ? BX.prop.getBoolean(this._settings, "showAlways", true) : this._field.checkOptionFlag(BX.UI.EntityEditorControlOptions.showAlways); //endregion

	      return this._optionWrapper;
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
	      this._errorContainer = main_core.Tag.render(_templateObject4$2());
	      return this._errorContainer;
	    }
	  }, {
	    key: "getEnumerationContainer",
	    value: function getEnumerationContainer() {
	      var _this = this;

	      var enumWrapper = main_core.Tag.render(_templateObject5$2(), BX.message("UI_ENTITY_EDITOR_UF_ENUM_ITEMS"));
	      this._enumItemContainer = main_core.Tag.render(_templateObject6());
	      main_core.Dom.append(this._enumItemContainer, enumWrapper);
	      var addButton = main_core.Tag.render(_templateObject7(), BX.message("UI_ENTITY_EDITOR_ADD"));
	      main_core.Event.bind(addButton, "click", this.onEnumerationItemAddButtonClick.bind(this));
	      main_core.Dom.append(main_core.Tag.render(_templateObject8(), addButton), enumWrapper);

	      if (this._field) {
	        this._field.getItems().forEach(function (enumFields) {
	          if (enumFields.VALUE !== '') {
	            _this.createEnumerationItem({
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
	  }], [{
	    key: "create",
	    value: function create(id, settings) {
	      var self = new this();
	      self.initialize(id, settings);
	      return self;
	    }
	  }]);
	  return IblockFieldConfigurator;
	}(BX.UI.EntityEditorFieldConfigurator);
	main_core.Reflection.namespace('BX.Catalog').IblockFieldConfigurator = IblockFieldConfigurator;

	var IblockFieldConfigurationManager =
	/*#__PURE__*/
	function (_BX$UI$EntityConfigur) {
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

	function _templateObject5$3() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t\t<div class=\"ui-entity-editor-content-block-add-field\">\n\t\t\t\t\t\t", "\n\t\t\t\t\t</div>\n\t\t\t\t"]);

	  _templateObject5$3 = function _templateObject5() {
	    return data;
	  };

	  return data;
	}

	function _templateObject4$3() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t\t<div class=\"ui-entity-card-content-add-field\">\n\t\t\t\t\t\t", "\n\t\t\t\t\t</div>\n\t\t\t\t"]);

	  _templateObject4$3 = function _templateObject4() {
	    return data;
	  };

	  return data;
	}

	function _templateObject3$3() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t\t<div class=\"ui-entity-editor-content-block\"></div>\n\t\t\t\t"]);

	  _templateObject3$3 = function _templateObject3() {
	    return data;
	  };

	  return data;
	}

	function _templateObject2$3() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"ui-entity-editor-content-block\">\n\t\t\t\t\t<div class=\"ui-entity-editor-block-title\">\n\t\t\t\t\t\t<span class=\"ui-entity-editor-block-title-text\">", "</span>\n\t\t\t\t\t</div>\n\t\t\t\t</div>\n\t\t\t"]);

	  _templateObject2$3 = function _templateObject2() {
	    return data;
	  };

	  return data;
	}

	function _templateObject$3() {
	  var data = babelHelpers.taggedTemplateLiteral(["<hr class=\"ui-entity-editor-line\">"]);

	  _templateObject$3 = function _templateObject() {
	    return data;
	  };

	  return data;
	}

	var GridFieldConfigurator =
	/*#__PURE__*/
	function (_BX$UI$EntityEditorFi) {
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
	        main_core.Dom.append(main_core.Tag.render(_templateObject$3()), this._wrapper);
	        var enumWrapper = main_core.Tag.render(_templateObject2$3(), BX.message("UI_ENTITY_EDITOR_UF_ENUM_ITEMS"));
	        main_core.Dom.append(enumWrapper, this._wrapper);
	        this._enumItemContainer = main_core.Tag.render(_templateObject3$3());
	        main_core.Dom.append(this._enumItemContainer, enumWrapper);
	        var addButton = main_core.Tag.render(_templateObject4$3(), BX.message("UI_ENTITY_EDITOR_ADD"));
	        main_core.Event.bind(addButton, "click", this.onEnumerationItemAddButtonClick.bind(this));
	        main_core.Dom.append(main_core.Tag.render(_templateObject5$3(), addButton), enumWrapper);

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

	var GridFieldConfigurationManager =
	/*#__PURE__*/
	function (_BX$UI$EntityConfigur) {
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

	function _templateObject$4() {
	  var data = babelHelpers.taggedTemplateLiteral(["<div class=\"catalog-entity-overlay\"></div>"]);

	  _templateObject$4 = function _templateObject() {
	    return data;
	  };

	  return data;
	}
	var EntityCard =
	/*#__PURE__*/
	function () {
	  function EntityCard(id) {
	    var settings = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : {};
	    babelHelpers.classCallCheck(this, EntityCard);
	    babelHelpers.defineProperty(this, "stackWithOffset", null);
	    this.id = main_core.Type.isStringFilled(id) ? id : main_core.Text.getRandom();
	    this.settings = settings;
	    this.cardSettings = settings.cardSettings || [];
	    this.feedbackUrl = settings.feedbackUrl || '';
	    this.entityId = main_core.Text.toInteger(settings.entityId) || 0;
	    this.container = document.getElementById(settings.containerId);
	    this.componentName = settings.componentName || null;
	    this.componentSignedParams = settings.componentSignedParams || null;
	    this.isSimpleProduct = settings.isSimpleProduct || false;
	    this.initializeTabManager();
	    this.checkFadeOverlay();
	    this.registerFieldsFactory();
	    this.registerControllersFactory();
	    this.registerEvents();
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
	        this.overlay = main_core.Tag.render(_templateObject$4());
	        main_core.Dom.append(this.overlay, this.container);

	        if (window === window.top) {
	          this.overlay.style.position = 'absolute';
	          this.overlay.style.top = this.overlay.style.left = this.overlay.style.right = '-15px';
	        }
	      }
	    }
	  }, {
	    key: "registerFieldsFactory",
	    value: function registerFieldsFactory() {
	      return new FieldsFactory();
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
	    }
	  }, {
	    key: "onAttachFilesHandler",
	    value: function onAttachFilesHandler(event) {
	      var editor = this.getEditorInstance();

	      if (!editor) {
	        return;
	      }

	      var _event$getCompatData = event.getCompatData(),
	          _event$getCompatData2 = babelHelpers.slicedToArray(_event$getCompatData, 3),
	          uploader = _event$getCompatData2[2];

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
	      var _event$getCompatData3 = event.getCompatData(),
	          _event$getCompatData4 = babelHelpers.slicedToArray(_event$getCompatData3, 1),
	          popup = _event$getCompatData4[0];

	      if (popup && popup.getId() === 'popupFM' && popup.onApplyFlag) {
	        this.showNotification(main_core.Loc.getMessage('CATALOG_ENTITY_CARD_FILE_CLOSE_NOTIFICATION'), {
	          autoHideDelay: 5000
	        });
	      }
	    }
	  }, {
	    key: "onEditorInitHandler",
	    value: function onEditorInitHandler(event) {
	      var _event$getCompatData5 = event.getCompatData(),
	          _event$getCompatData6 = babelHelpers.slicedToArray(_event$getCompatData5, 2),
	          editor = _event$getCompatData6[0],
	          fields = _event$getCompatData6[1];

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
	      var _event$getCompatData7 = event.getCompatData(),
	          _event$getCompatData8 = babelHelpers.slicedToArray(_event$getCompatData7, 2),
	          fields = _event$getCompatData8[0],
	          response = _event$getCompatData8[1];

	      var title = fields.NAME || '';
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
	      var _event$getCompatData9 = event.getCompatData(),
	          _event$getCompatData10 = babelHelpers.slicedToArray(_event$getCompatData9, 1),
	          data = _event$getCompatData10[0];

	      this.postSliderMessage('onCreate', data);
	    }
	  }, {
	    key: "onEntityUpdateHandler",
	    value: function onEntityUpdateHandler(event) {
	      var _event$getCompatData11 = event.getCompatData(),
	          _event$getCompatData12 = babelHelpers.slicedToArray(_event$getCompatData11, 1),
	          data = _event$getCompatData12[0];

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
	      var _event$getCompatData13 = event.getCompatData(),
	          _event$getCompatData14 = babelHelpers.slicedToArray(_event$getCompatData13, 2),
	          eventArgs = _event$getCompatData14[1];

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
	  }]);
	  return EntityCard;
	}();

	exports.EntityCard = EntityCard;

}((this.BX.Catalog.EntityCard = this.BX.Catalog.EntityCard || {}),BX,BX,BX.Event,BX,BX));
//# sourceMappingURL=entity-card.bundle.js.map
