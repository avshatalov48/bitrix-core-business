this.BX = this.BX || {};
(function (exports,ui_forms,main_core_events,main_core,ui_entitySelector,catalog_storeSelector,ui_notification,catalog_productModel) {
	'use strict';

	var SelectorErrorCode = function SelectorErrorCode() {
	  babelHelpers.classCallCheck(this, SelectorErrorCode);
	};
	babelHelpers.defineProperty(SelectorErrorCode, "NOT_SELECTED_STORE", 'NOT_SELECTED_STORE');

	var _templateObject, _templateObject2, _templateObject3, _templateObject4, _templateObject5, _templateObject6, _templateObject7, _templateObject8, _templateObject9, _templateObject10;
	var StoreSearchInput = /*#__PURE__*/function () {
	  function StoreSearchInput(id) {
	    var _options$disableByRig, _options$disabledHint;

	    var options = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : {};
	    babelHelpers.classCallCheck(this, StoreSearchInput);
	    babelHelpers.defineProperty(this, "cache", new main_core.Cache.MemoryCache());
	    this.id = id || main_core.Text.getRandom();
	    this.selector = options.selector;

	    if (!(this.selector instanceof catalog_storeSelector.StoreSelector)) {
	      throw new Error('Store selector instance not found.');
	    }

	    this.isEnabledDetailLink = options.isEnabledDetailLink;
	    this.inputName = options.inputName || '';
	    this.allowCreateItem = options.allowCreateItem !== undefined ? options.allowCreateItem : true;
	    this.disableByRights = (_options$disableByRig = options.disableByRights) !== null && _options$disableByRig !== void 0 ? _options$disableByRig : false;
	    this.disabledHint = (_options$disabledHint = options.disabledHint) !== null && _options$disabledHint !== void 0 ? _options$disabledHint : main_core.Loc.getMessage('CATALOG_STORE_SELECTOR_HAS_PERMISSION_VIEW_STORES_HINT');
	  }

	  babelHelpers.createClass(StoreSearchInput, [{
	    key: "getId",
	    value: function getId() {
	      return this.id;
	    }
	  }, {
	    key: "toggleIcon",
	    value: function toggleIcon(icon, value) {
	      if (main_core.Type.isDomNode(icon)) {
	        main_core.Dom.style(icon, 'display', value);
	      }
	    }
	  }, {
	    key: "getNameBlock",
	    value: function getNameBlock() {
	      var _this = this;

	      if (this.disableByRights) {
	        return main_core.Tag.render(_templateObject || (_templateObject = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div\n\t\t\t\t\tclass=\"ui-ctl ui-ctl-w100 ui-ctl-before-icon ui-ctl-after-icon ui-ctl-disabled\"\n\t\t\t\t\tdata-hint=\"", "\"\n\t\t\t\t\tdata-hint-no-icon\n\t\t\t\t>\n\t\t\t\t\t<div class=\"ui-ctl-before catalog-store-field-input-access-denied-lock\"></div>\n\t\t\t\t\t<div class=\"ui-ctl-after catalog-store-field-input-access-denied-hint\"></div>\n\t\t\t\t\t<div class=\"ui-ctl-element\">", "</div>\n\t\t\t\t</div>\n\t\t\t"])), this.disabledHint, main_core.Loc.getMessage('CATALOG_STORE_SELECTOR_HAS_PERMISSION_VIEW_STORES_TITLE'));
	      }

	      return this.cache.remember('nameBlock', function () {
	        return main_core.Tag.render(_templateObject2 || (_templateObject2 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"ui-ctl ui-ctl-textbox ui-ctl-w100\">\n\t\t\t\t\t", "\n\t\t\t\t\t", "\n\t\t\t\t</div>\n\t\t\t"])), _this.getNameInput(), _this.getHiddenNameInput());
	      });
	    }
	  }, {
	    key: "getNameInput",
	    value: function getNameInput() {
	      var _this2 = this;

	      return this.cache.remember('nameInput', function () {
	        return main_core.Tag.render(_templateObject3 || (_templateObject3 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<input type=\"text\"\n\t\t\t\t\tclass=\"ui-ctl-element ui-ctl-textbox\"\n\t\t\t\t\tautocomplete=\"off\"\n\t\t\t\t\tvalue=\"", "\"\n\t\t\t\t\tplaceholder=\"", "\"\n\t\t\t\t\ttitle=\"", "\"\n\t\t\t\t\tonchange=\"", "\"\n\t\t\t\t>\n\t\t\t"])), main_core.Text.encode(_this2.selector.getStoreTitle()), main_core.Text.encode(_this2.getPlaceholder()), main_core.Text.encode(_this2.selector.getStoreTitle()), _this2.handleNameInputHiddenChange.bind(_this2));
	      });
	    }
	  }, {
	    key: "getHiddenNameInput",
	    value: function getHiddenNameInput() {
	      var _this3 = this;

	      return this.cache.remember('hiddenNameInput', function () {
	        return main_core.Tag.render(_templateObject4 || (_templateObject4 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<input\n\t\t\t\t \ttype=\"hidden\"\n\t\t\t\t\tname=\"", "\"\n\t\t\t\t\tvalue=\"", "\"\n\t\t\t\t>\n\t\t\t"])), main_core.Text.encode(_this3.inputName), main_core.Text.encode(_this3.selector.getStoreTitle()));
	      });
	    }
	  }, {
	    key: "handleNameInputHiddenChange",
	    value: function handleNameInputHiddenChange(event) {
	      this.getHiddenNameInput().value = event.target.value;
	    }
	  }, {
	    key: "getClearIcon",
	    value: function getClearIcon() {
	      var _this4 = this;

	      return this.cache.remember('closeIcon', function () {
	        return main_core.Tag.render(_templateObject5 || (_templateObject5 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<button\n\t\t\t\t\tclass=\"ui-ctl-after ui-ctl-icon-clear\"\n\t\t\t\t\tonclick=\"", "\"\n\t\t\t\t></button>\n\t\t\t"])), _this4.handleClearIconClick.bind(_this4));
	      });
	    }
	  }, {
	    key: "getArrowIcon",
	    value: function getArrowIcon() {
	      var _this5 = this;

	      return this.cache.remember('arrowIcon', function () {
	        return main_core.Tag.render(_templateObject6 || (_templateObject6 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<a\n\t\t\t\t\thref=\"", "\"\n\t\t\t\t\ttarget=\"_blank\"\n\t\t\t\t\tclass=\"ui-ctl-after ui-ctl-icon-forward\"\n\t\t\t\t></button>\n\t\t\t"])), _this5.selector.getDetailPath());
	      });
	    }
	  }, {
	    key: "getSearchIcon",
	    value: function getSearchIcon() {
	      var _this6 = this;

	      return this.cache.remember('searchIcon', function () {
	        return main_core.Tag.render(_templateObject7 || (_templateObject7 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<button\n\t\t\t\t\tclass=\"ui-ctl-after ui-ctl-icon-search\"\n\t\t\t\t\tonclick=\"", "\"\n\t\t\t\t></button>\n\t\t\t"])), _this6.handleSearchIconClick.bind(_this6));
	      });
	    }
	  }, {
	    key: "clearInputCache",
	    value: function clearInputCache() {
	      this.cache["delete"]('dialog');
	      this.cache["delete"]('nameBlock');
	      this.cache["delete"]('nameInput');
	      this.cache["delete"]('hiddenNameInput');
	    }
	  }, {
	    key: "clearDialogCache",
	    value: function clearDialogCache() {
	      this.cache["delete"]('dialog');
	    }
	  }, {
	    key: "layout",
	    value: function layout() {
	      this.clearInputCache();
	      var block = main_core.Tag.render(_templateObject8 || (_templateObject8 = babelHelpers.taggedTemplateLiteral(["<div class=\"ui-ctl ui-ctl-w100 ui-ctl-after-icon\"></div>"])));
	      main_core.Dom.append(this.getSearchIcon(), block);
	      this.toggleIcon(this.getSearchIcon(), 'none');
	      main_core.Dom.append(this.getClearIcon(), block);
	      this.toggleIcon(this.getClearIcon(), 'none');

	      if (this.showDetailLink() && main_core.Type.isStringFilled(this.selector.getStoreTitle())) {
	        this.toggleIcon(this.getArrowIcon(), 'block');
	        main_core.Dom.append(this.getArrowIcon(), block);
	      } else {
	        this.toggleIcon(this.getSearchIcon(), 'block');
	      }

	      main_core.Event.bind(this.getNameInput(), 'click', this.handleNameInputClick.bind(this));
	      main_core.Event.bind(this.getNameInput(), 'input', this.handleNameInput.bind(this));
	      main_core.Event.bind(this.getNameInput(), 'blur', this.handleNameInputBlur.bind(this));
	      main_core.Event.bind(this.getNameInput(), 'keydown', this.handleNameInputKeyDown.bind(this));
	      main_core.Dom.append(this.getNameBlock(), block);
	      BX.UI.Hint.init(block);
	      return block;
	    }
	  }, {
	    key: "handleNameInputClick",
	    value: function handleNameInputClick(event) {
	      this.searchInDialog(event.target.value);
	      this.handleIconsSwitchingOnNameInput(event);
	    }
	  }, {
	    key: "handleNameInput",
	    value: function handleNameInput(event) {
	      if (!main_core.Type.isStringFilled(event.target.value)) {
	        this.selector.onClear();
	        return;
	      }

	      this.searchInDialog(event.target.value);
	      this.handleIconsSwitchingOnNameInput(event);
	    }
	  }, {
	    key: "showDetailLink",
	    value: function showDetailLink() {
	      return this.isEnabledDetailLink;
	    }
	  }, {
	    key: "getDialog",
	    value: function getDialog() {
	      var _this7 = this;

	      return this.cache.remember('dialog', function () {
	        var stubOptions = {
	          title: main_core.Tag.message(_templateObject9 || (_templateObject9 = babelHelpers.taggedTemplateLiteral(["", ""])), 'CATALOG_STORE_SELECTOR_IS_EMPTY_TITLE')
	        };

	        if (_this7.allowCreateItem) {
	          stubOptions.subtitle = main_core.Tag.message(_templateObject10 || (_templateObject10 = babelHelpers.taggedTemplateLiteral(["", ""])), 'CATALOG_STORE_SELECTOR_IS_EMPTY_SUBTITLE');
	          stubOptions.arrow = true;
	        }

	        var params = {
	          id: _this7.id + '_store',
	          height: 300,
	          context: 'catalog-store',
	          targetNode: _this7.getNameInput(),
	          enableSearch: false,
	          multiple: false,
	          dropdownMode: true,
	          searchTabOptions: {
	            stubOptions: stubOptions,
	            stub: true
	          },
	          events: {
	            'Item:onSelect': _this7.onStoreSelect.bind(_this7),
	            'onSearch': _this7.onSearch.bind(_this7),
	            'Search:onItemCreateAsync': _this7.createStore.bind(_this7)
	          },
	          entities: [{
	            id: 'store',
	            options: {
	              productId: _this7.selector.getProductId()
	            },
	            searchFields: [{
	              name: 'subtitle',
	              type: 'string',
	              system: true,
	              searchable: false
	            }],
	            dynamicLoad: true,
	            dynamicSearch: true
	          }],
	          searchOptions: {
	            allowCreateItem: _this7.allowCreateItem
	          }
	        };
	        return new ui_entitySelector.Dialog(params);
	      });
	    }
	  }, {
	    key: "handleNameInputKeyDown",
	    value: function handleNameInputKeyDown(event) {
	      var dialog = this.getDialog();

	      if (event.key === 'Enter' && dialog.getActiveTab() === dialog.getSearchTab()) {
	        // prevent a form submit
	        event.preventDefault();

	        if (main_core.Browser.isMac() && event.metaKey || event.ctrlKey) {
	          dialog.getSearchTab().getFooter().createItem();
	        }
	      }
	    }
	  }, {
	    key: "handleIconsSwitchingOnNameInput",
	    value: function handleIconsSwitchingOnNameInput(event) {
	      this.toggleIcon(this.getArrowIcon(), 'none');

	      if (main_core.Type.isStringFilled(event.target.value)) {
	        this.toggleIcon(this.getClearIcon(), 'block');
	        this.toggleIcon(this.getSearchIcon(), 'none');
	      } else {
	        this.toggleIcon(this.getClearIcon(), 'none');
	        this.toggleIcon(this.getSearchIcon(), 'block');
	      }
	    }
	  }, {
	    key: "handleClearIconClick",
	    value: function handleClearIconClick(event) {
	      this.selector.onClear();
	      event.stopPropagation();
	      event.preventDefault();
	    }
	  }, {
	    key: "focusName",
	    value: function focusName() {
	      var _this8 = this;

	      requestAnimationFrame(function () {
	        return _this8.getNameInput().focus();
	      });
	    }
	  }, {
	    key: "searchInDialog",
	    value: function searchInDialog() {
	      var searchQuery = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : '';
	      var dialog = this.getDialog();

	      if (dialog) {
	        dialog.show();
	        dialog.search(searchQuery);
	      }
	    }
	  }, {
	    key: "handleShowSearchDialog",
	    value: function handleShowSearchDialog(event) {
	      this.searchInDialog(event.target.value);
	    }
	  }, {
	    key: "handleNameInputBlur",
	    value: function handleNameInputBlur(event) {
	      var _this9 = this;

	      // timeout to toggle clear icon handler while cursor is inside of name input
	      setTimeout(function () {
	        _this9.toggleIcon(_this9.getClearIcon(), 'none');

	        if (_this9.showDetailLink() && main_core.Type.isStringFilled(_this9.selector.getStoreTitle())) {
	          _this9.toggleIcon(_this9.getSearchIcon(), 'none');

	          _this9.toggleIcon(_this9.getArrowIcon(), 'block');
	        } else {
	          _this9.toggleIcon(_this9.getArrowIcon(), 'none');

	          _this9.toggleIcon(_this9.getSearchIcon(), 'block');
	        }
	      }, 200);

	      if (this.selector.isDisabledEmpty()) {
	        setTimeout(function () {
	          if (_this9.selector.getStoreId() === '') {
	            _this9.selector.setEmptyError();
	          } else {
	            _this9.selector.clearErrorLayout();

	            _this9.selector.clearEmptyError();
	          }
	        }, 200);
	      }
	    }
	  }, {
	    key: "handleSearchIconClick",
	    value: function handleSearchIconClick(event) {
	      this.searchInDialog();
	      this.focusName();
	      event.stopPropagation();
	      event.preventDefault();
	    }
	  }, {
	    key: "onSearch",
	    value: function onSearch(event) {
	      var _event$getData = event.getData(),
	          query = _event$getData.query;

	      if (query === '' || query === this.selector.getStoreTitle()) {
	        var _event$target, _event$target$searchT, _event$target$searchT2;

	        (_event$target = event.target) === null || _event$target === void 0 ? void 0 : (_event$target$searchT = _event$target.searchTab) === null || _event$target$searchT === void 0 ? void 0 : (_event$target$searchT2 = _event$target$searchT.getFooter()) === null || _event$target$searchT2 === void 0 ? void 0 : _event$target$searchT2.hide();
	      } else {
	        var _event$target2, _event$target2$search, _event$target2$search2;

	        (_event$target2 = event.target) === null || _event$target2 === void 0 ? void 0 : (_event$target2$search = _event$target2.searchTab) === null || _event$target2$search === void 0 ? void 0 : (_event$target2$search2 = _event$target2$search.getFooter()) === null || _event$target2$search2 === void 0 ? void 0 : _event$target2$search2.show();
	      }
	    }
	  }, {
	    key: "onStoreSelect",
	    value: function onStoreSelect(event) {
	      var item = event.getData().item;
	      item.getDialog().getTargetNode().value = item.getTitle();

	      if (this.selector) {
	        this.selector.onStoreSelect(item.getId(), item.getTitle());
	      }

	      this.toggleIcon(this.getSearchIcon(), 'none');
	      this.selector.clearLayout();
	      this.selector.layout();
	      this.cache["delete"]('dialog');
	    }
	  }, {
	    key: "createStore",
	    value: function createStore(event) {
	      var _event$getData2 = event.getData(),
	          searchQuery = _event$getData2.searchQuery;

	      var name = searchQuery.getQuery();
	      return new Promise(function (resolve, reject) {
	        if (!main_core.Type.isStringFilled(name)) {
	          reject();
	          return;
	        }

	        var dialog = event.getTarget();
	        dialog.showLoader();
	        main_core.ajax.runAction('catalog.storeSelector.createStore', {
	          json: {
	            name: name
	          }
	        }).then(function (response) {
	          dialog.hideLoader();
	          var id = main_core.Text.toInteger(response.data.id);
	          var item = dialog.addItem({
	            id: id,
	            entityId: 'store',
	            title: name,
	            tabs: dialog.getRecentTab().getId()
	          });

	          if (item) {
	            item.select();
	          }

	          dialog.hide();
	          resolve();
	        })["catch"](function (response) {
	          console.error(response);
	          reject();
	        });
	      });
	    }
	  }, {
	    key: "getPlaceholder",
	    value: function getPlaceholder() {
	      return main_core.Loc.getMessage('CATALOG_STORE_SELECTOR_BEFORE_SEARCH_TITLE');
	    }
	  }, {
	    key: "disable",
	    value: function disable(hint) {
	      this.disableByRights = true;

	      if (hint) {
	        this.disabledHint = hint;
	      }
	    }
	  }]);
	  return StoreSearchInput;
	}();

	var _templateObject$1, _templateObject2$1, _templateObject3$1, _templateObject4$1, _templateObject5$1, _templateObject6$1;

	function _classPrivateFieldInitSpec(obj, privateMap, value) { _checkPrivateRedeclaration(obj, privateMap); privateMap.set(obj, value); }

	function _checkPrivateRedeclaration(obj, privateCollection) { if (privateCollection.has(obj)) { throw new TypeError("Cannot initialize the same private elements twice on an object"); } }
	var instances = new Map();

	var _storeInfo = /*#__PURE__*/new WeakMap();

	var _model = /*#__PURE__*/new WeakMap();

	var StoreSelector = /*#__PURE__*/function (_EventEmitter) {
	  babelHelpers.inherits(StoreSelector, _EventEmitter);
	  babelHelpers.createClass(StoreSelector, null, [{
	    key: "getById",
	    value: function getById(id) {
	      return instances.get(id) || null;
	    }
	  }]);

	  function StoreSelector(id) {
	    var _this;

	    var options = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : {};
	    babelHelpers.classCallCheck(this, StoreSelector);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(StoreSelector).call(this));
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "mode", StoreSelector.MODE_EDIT);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "productId", null);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "cache", new main_core.Cache.MemoryCache());

	    _classPrivateFieldInitSpec(babelHelpers.assertThisInitialized(_this), _storeInfo, {
	      writable: true,
	      value: new Map()
	    });

	    _classPrivateFieldInitSpec(babelHelpers.assertThisInitialized(_this), _model, {
	      writable: true,
	      value: void 0
	    });

	    _this.setEventNamespace('BX.Catalog.StoreSelector');

	    _this.id = id || main_core.Text.getRandom();
	    options.inputFieldTitle = options.inputFieldTitle || StoreSelector.INPUT_FIELD_TITLE;
	    options.inputFieldId = options.inputFieldId || StoreSelector.INPUT_FIELD_ID;
	    options.isDisabledEmpty = !!options.isDisabledEmpty;
	    _this.options = options || {};

	    _this.setMode(options.mode);

	    var settingsCollection = main_core.Extension.getSettings('catalog.store-selector');

	    if (options.model instanceof catalog_productModel.ProductModel) {
	      if (options.model.getField(options.inputFieldId) > 0) {
	        babelHelpers.classPrivateFieldGet(babelHelpers.assertThisInitialized(_this), _storeInfo).set('id', options.model.getField(options.inputFieldId));
	        var name = main_core.Type.isStringFilled(options.model.getField(options.inputFieldTitle)) ? options.model.getField(options.inputFieldTitle) : '';

	        _this.setProductId(options.model.getSkuId());

	        babelHelpers.classPrivateFieldGet(babelHelpers.assertThisInitialized(_this), _storeInfo).set('title', name);
	      } else if (!options.model.isCatalogExisted()) {
	        babelHelpers.classPrivateFieldGet(babelHelpers.assertThisInitialized(_this), _storeInfo).set('id', settingsCollection.get('defaultStoreId'));
	        babelHelpers.classPrivateFieldGet(babelHelpers.assertThisInitialized(_this), _storeInfo).set('title', settingsCollection.get('defaultStoreName'));
	      }

	      babelHelpers.classPrivateFieldSet(babelHelpers.assertThisInitialized(_this), _model, options.model);
	    } else {
	      babelHelpers.classPrivateFieldGet(babelHelpers.assertThisInitialized(_this), _storeInfo).set('id', settingsCollection.get('defaultStoreId'));
	      babelHelpers.classPrivateFieldGet(babelHelpers.assertThisInitialized(_this), _storeInfo).set('title', settingsCollection.get('defaultStoreName'));
	    }

	    _this.searchInput = new StoreSearchInput(_this.id, {
	      selector: babelHelpers.assertThisInitialized(_this),
	      inputName: _this.options.inputFieldTitle,
	      allowCreateItem: _this.options.allowCreateItem || settingsCollection.get('allowCreateItem'),
	      disableByRights: settingsCollection.get('disableByRights')
	    }); // this.setDetailPath(this.getConfig('DETAIL_PATH'));

	    _this.layout();

	    instances.set(_this.id, babelHelpers.assertThisInitialized(_this));
	    return _this;
	  }

	  babelHelpers.createClass(StoreSelector, [{
	    key: "setMode",
	    value: function setMode(mode) {
	      if (!main_core.Type.isNil(mode)) {
	        this.mode = mode === StoreSelector.MODE_VIEW ? StoreSelector.MODE_VIEW : StoreSelector.MODE_EDIT;
	      }
	    }
	  }, {
	    key: "setProductId",
	    value: function setProductId(productId) {
	      var _this$searchInput;

	      productId = main_core.Text.toNumber(productId);

	      if (productId > 0) {
	        this.productId = productId;
	      } else {
	        this.productId = null;
	      }

	      (_this$searchInput = this.searchInput) === null || _this$searchInput === void 0 ? void 0 : _this$searchInput.clearDialogCache();
	    }
	  }, {
	    key: "isViewMode",
	    value: function isViewMode() {
	      return this.mode === StoreSelector.MODE_VIEW;
	    }
	  }, {
	    key: "isDisabledEmpty",
	    value: function isDisabledEmpty() {
	      return this.options.isDisabledEmpty;
	    }
	  }, {
	    key: "getId",
	    value: function getId() {
	      return this.id;
	    }
	  }, {
	    key: "getProductId",
	    value: function getProductId() {
	      return this.productId;
	    }
	  }, {
	    key: "getConfig",
	    value: function getConfig(name, defaultValue) {
	      return BX.prop.get(this.options.config, name, defaultValue);
	    }
	  }, {
	    key: "getDetailPath",
	    value: function getDetailPath() {
	      return this.getConfig('detailPath', '');
	    }
	  }, {
	    key: "getWrapper",
	    value: function getWrapper() {
	      if (!this.wrapper) {
	        this.wrapper = document.getElementById(this.id);
	      }

	      return this.wrapper;
	    }
	  }, {
	    key: "renderTo",
	    value: function renderTo(node) {
	      this.clearLayout();
	      this.wrapper = node;
	      this.layout();
	    }
	  }, {
	    key: "layout",
	    value: function layout() {
	      var wrapper = this.getWrapper();

	      if (!wrapper) {
	        return;
	      }

	      this.defineWrapperClass(wrapper);
	      var block = main_core.Tag.render(_templateObject$1 || (_templateObject$1 = babelHelpers.taggedTemplateLiteral(["<div class=\"catalog-store-field-inner\"></div>"])));
	      main_core.Dom.append(block, wrapper);
	      main_core.Dom.append(this.getNameBlock(), block);
	      main_core.Dom.append(this.getErrorBlock(), block);

	      if (this.getStoreId() === '') {
	        this.layoutEmptyError();
	      }
	    }
	  }, {
	    key: "setEmptyError",
	    value: function setEmptyError() {
	      babelHelpers.classPrivateFieldGet(this, _model).getErrorCollection().setError(SelectorErrorCode.NOT_SELECTED_STORE, main_core.Loc.getMessage('CATALOG_STORE_SELECTOR_UNSELECTED'));
	      this.layoutEmptyError();
	    }
	  }, {
	    key: "clearEmptyError",
	    value: function clearEmptyError() {
	      babelHelpers.classPrivateFieldGet(this, _model).getErrorCollection().removeError(SelectorErrorCode.NOT_SELECTED_STORE);
	      return this;
	    }
	  }, {
	    key: "layoutEmptyError",
	    value: function layoutEmptyError() {
	      this.getErrorBlock().innerHTML = '';
	      main_core.Dom.append(main_core.Tag.render(_templateObject2$1 || (_templateObject2$1 = babelHelpers.taggedTemplateLiteral(["<div class=\"catalog-store-field-error\">", "</div>"])), main_core.Loc.getMessage('CATALOG_STORE_SELECTOR_UNSELECTED')), this.getErrorBlock());

	      if (this.searchInput) {
	        main_core.Dom.addClass(this.getNameBlock(), 'ui-ctl-danger');
	      }

	      return this;
	    }
	  }, {
	    key: "clearErrorLayout",
	    value: function clearErrorLayout() {
	      this.getErrorBlock().innerHTML = '';
	      main_core.Dom.removeClass(this.getNameBlock(), 'ui-ctl-danger');
	      return this;
	    }
	  }, {
	    key: "focusName",
	    value: function focusName() {
	      if (this.searchInput) {
	        this.searchInput.focusName();
	      }

	      return this;
	    }
	  }, {
	    key: "onClear",
	    value: function onClear() {
	      this.clearState();
	      this.clearLayout();
	      this.layout();
	      this.searchInput.searchInDialog();
	      this.focusName();
	      this.emit('onClear', {
	        selectorId: this.getId(),
	        rowId: this.getRowId(),
	        fields: [{
	          NAME: this.options.inputFieldId,
	          VALUE: null
	        }, {
	          NAME: this.options.inputFieldTitle,
	          VALUE: ''
	        }]
	      });
	    }
	  }, {
	    key: "clearState",
	    value: function clearState() {
	      babelHelpers.classPrivateFieldGet(this, _storeInfo).clear();
	    }
	  }, {
	    key: "clearLayout",
	    value: function clearLayout() {
	      var wrapper = this.getWrapper();

	      if (wrapper) {
	        wrapper.innerHTML = '';
	      }

	      this.nameBlock = null;
	      this.clearErrorLayout();
	    }
	  }, {
	    key: "unsubscribeEvents",
	    value: function unsubscribeEvents() {}
	  }, {
	    key: "defineWrapperClass",
	    value: function defineWrapperClass(wrapper) {
	      if (this.isViewMode()) {
	        main_core.Dom.addClass(wrapper, 'catalog-store-view');
	        main_core.Dom.removeClass(wrapper, 'catalog-store-edit');
	      } else {
	        main_core.Dom.addClass(wrapper, 'catalog-store-edit');
	        main_core.Dom.removeClass(wrapper, 'catalog-store-view');
	      }
	    }
	  }, {
	    key: "getViewHtml",
	    value: function getViewHtml() {
	      var storeTitle = main_core.Text.encode(this.getStoreTitle());
	      var titlePlaceholder = main_core.Loc.getMessage('CATALOG_STORE_SELECTOR_VIEW_NAME_TITLE');

	      if (this.getDetailPath()) {
	        return main_core.Tag.render(_templateObject3$1 || (_templateObject3$1 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<a href=\"", "\" title=\"", "\">", "</a>\n\t\t\t"])), this.getDetailPath(), titlePlaceholder, storeTitle);
	      }

	      return main_core.Tag.render(_templateObject4$1 || (_templateObject4$1 = babelHelpers.taggedTemplateLiteral(["<span title=\"", "\">", "</span>"])), titlePlaceholder, storeTitle);
	    }
	  }, {
	    key: "getNameBlock",
	    value: function getNameBlock() {
	      if (this.nameBlock) {
	        return this.nameBlock;
	      }

	      var block = main_core.Tag.render(_templateObject5$1 || (_templateObject5$1 = babelHelpers.taggedTemplateLiteral(["<div class=\"catalog-store-field-input\"></div>"])));

	      if (this.isViewMode()) {
	        main_core.Dom.append(this.getViewHtml(), block);
	      } else {
	        main_core.Dom.append(this.searchInput.layout(), block);
	      }

	      this.nameBlock = block;
	      return this.nameBlock;
	    }
	  }, {
	    key: "getErrorBlock",
	    value: function getErrorBlock() {
	      if (!this.errorBlock) {
	        this.errorBlock = main_core.Tag.render(_templateObject6$1 || (_templateObject6$1 = babelHelpers.taggedTemplateLiteral(["<div class=\"catalog-store-field-error\"></div>"])));
	      }

	      return this.errorBlock;
	    }
	  }, {
	    key: "getStoreTitle",
	    value: function getStoreTitle() {
	      return babelHelpers.classPrivateFieldGet(this, _storeInfo).get('title') || '';
	    }
	  }, {
	    key: "getStoreId",
	    value: function getStoreId() {
	      return babelHelpers.classPrivateFieldGet(this, _storeInfo).get('id') || '';
	    }
	  }, {
	    key: "onStoreSelect",
	    value: function onStoreSelect(storeId, storeTitle) {
	      if (storeTitle === '') {
	        storeTitle = main_core.Loc.getMessage('CATALOG_STORE_SELECTOR_EMPTY_STORE_TITLE');
	      }

	      babelHelpers.classPrivateFieldGet(this, _storeInfo).set('id', storeId);
	      babelHelpers.classPrivateFieldGet(this, _storeInfo).set('title', storeTitle);
	      this.clearLayout();
	      this.clearEmptyError();
	      this.layout();
	      this.emit('onChange', {
	        selectorId: this.id,
	        rowId: this.getRowId(),
	        fields: [{
	          NAME: this.options.inputFieldId,
	          VALUE: storeId
	        }, {
	          NAME: this.options.inputFieldTitle,
	          VALUE: storeTitle
	        }]
	      });
	    }
	  }, {
	    key: "getRowId",
	    value: function getRowId() {
	      return this.getConfig('ROW_ID');
	    }
	  }]);
	  return StoreSelector;
	}(main_core_events.EventEmitter);
	babelHelpers.defineProperty(StoreSelector, "MODE_VIEW", 'view');
	babelHelpers.defineProperty(StoreSelector, "MODE_EDIT", 'edit');
	babelHelpers.defineProperty(StoreSelector, "INPUT_FIELD_TITLE", 'STORE_TITLE');
	babelHelpers.defineProperty(StoreSelector, "INPUT_FIELD_ID", 'STORE_ID');
	babelHelpers.defineProperty(StoreSelector, "ErrorCodes", SelectorErrorCode);

	exports.StoreSelector = StoreSelector;

}((this.BX.Catalog = this.BX.Catalog || {}),BX,BX.Event,BX,BX.UI.EntitySelector,BX.Catalog,BX,BX.Catalog));
//# sourceMappingURL=store-selector.bundle.js.map
