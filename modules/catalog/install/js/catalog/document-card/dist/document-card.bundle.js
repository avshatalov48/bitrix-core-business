this.BX = this.BX || {};
this.BX.Catalog = this.BX.Catalog || {};
(function (exports,catalog_entityCard,main_core_events,currency_currencyCore,ui_entitySelector,main_popup,catalog_storeUse,ui_feedback_form,main_core) {
	'use strict';

	var ProductListController = /*#__PURE__*/function (_BX$UI$EntityEditorCo) {
	  babelHelpers.inherits(ProductListController, _BX$UI$EntityEditorCo);

	  function ProductListController(id, settings) {
	    var _this;

	    babelHelpers.classCallCheck(this, ProductListController);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(ProductListController).call(this));
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "getCurrencyId", function () {
	      return this._currencyId;
	    });

	    _this.initialize(id, settings);

	    _this._setProductListHandler = _this.handleSetProductList.bind(babelHelpers.assertThisInitialized(_this));
	    _this._tabShowHandler = _this.onTabShow.bind(babelHelpers.assertThisInitialized(_this));
	    _this._editorControlChangeHandler = _this.onEditorControlChange.bind(babelHelpers.assertThisInitialized(_this));
	    _this._currencyId = _this._model.getField('CURRENCY', '');
	    main_core_events.EventEmitter.subscribe(_this._editor, 'onControlChanged', _this.onEditorControlChange.bind(babelHelpers.assertThisInitialized(_this)));
	    main_core_events.EventEmitter.subscribe('DocumentProductListController', _this._setProductListHandler);
	    main_core_events.EventEmitter.subscribe('onEntityDetailsTabShow', _this._tabShowHandler);
	    main_core_events.EventEmitter.subscribe('BX.UI.EntityEditorList:onItemSelect', function (event) {
	      var _event$data = babelHelpers.slicedToArray(event.data, 2),
	          field = _event$data[0],
	          params = _event$data[1];

	      if ((field === null || field === void 0 ? void 0 : field.getId()) === 'TOTAL_WITH_CURRENCY') {
	        _this.changeCurrency(params.item.value);
	      }
	    });
	    return _this;
	  }

	  babelHelpers.createClass(ProductListController, [{
	    key: "handleSetProductList",
	    value: function handleSetProductList(event) {
	      var productList = event.getData()[0];
	      this.setProductList(productList);
	      main_core_events.EventEmitter.unsubscribe('DocumentProductListController', this._setProductListHandler);
	    }
	  }, {
	    key: "reinitializeProductList",
	    value: function reinitializeProductList() {
	      if (this.productList) {
	        this.productList.reloadGrid(false);
	      }
	    }
	  }, {
	    key: "onTabShow",
	    value: function onTabShow(event) {
	      var _event$getData = event.getData(),
	          _event$getData2 = babelHelpers.slicedToArray(_event$getData, 1),
	          tab = _event$getData2[0];

	      if (tab.id === 'tab_products' && this.productList) {
	        this.productList.handleOnTabShow();
	        main_core_events.EventEmitter.unsubscribe('onEntityDetailsTabShow', this._tabShowHandler);
	        main_core_events.EventEmitter.emit('onDocumentProductListTabShow', this);
	      }
	    }
	  }, {
	    key: "innerCancel",
	    value: function innerCancel() {
	      this.rollback();

	      if (this.productList) {
	        this.productList.onInnerCancel();
	      }

	      this._currencyId = this._model.getField('CURRENCY');

	      if (this.productList) {
	        this.productList.changeCurrencyId(this._currencyId);
	        this.productList.updateTotalUiCurrency();
	      }

	      this._isChanged = false;
	    }
	  }, {
	    key: "setProductList",
	    value: function setProductList(productList) {
	      if (this.productList === productList) {
	        return;
	      }

	      if (this.productList) {
	        this.productList.destroy();
	      }

	      this.productList = productList;

	      if (this.productList) {
	        this.productList.setController(this);
	        this.productList.setForm(this._editor.getFormElement());

	        if (this.productList.getCurrencyId() !== this.getCurrencyId()) {
	          this.productList.changeCurrencyId(this.getCurrencyId());
	        }

	        this._prevProductCount = this._curProductCount = this.productList.getProductCount();
	      }
	    }
	  }, {
	    key: "onAfterSave",
	    value: function onAfterSave() {
	      babelHelpers.get(babelHelpers.getPrototypeOf(ProductListController.prototype), "onAfterSave", this).call(this);

	      if (this.productList) {
	        this.productList.removeFormFields();
	      }

	      this._editor._toolPanel.showViewModeButtons();
	    }
	  }, {
	    key: "productChange",
	    value: function productChange() {
	      var _disableSaveButton;

	      var disableSaveButton = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : false;
	      disableSaveButton = (_disableSaveButton = disableSaveButton) !== null && _disableSaveButton !== void 0 ? _disableSaveButton : false;
	      this.markAsChanged();

	      if (disableSaveButton) {
	        this.disableSaveButton();
	      }

	      main_core_events.EventEmitter.emit('onDocumentProductChange', this.productList.getProductsFields());
	    }
	  }, {
	    key: "onBeforeSubmit",
	    value: function onBeforeSubmit() {
	      if (this.productList && (this.isChanged() || this._editor.isNew())) {
	        this.productList.compileProductData();
	      }
	    }
	  }, {
	    key: "enableSaveButton",
	    value: function enableSaveButton() {
	      var _this$_editor;

	      if ((_this$_editor = this._editor) !== null && _this$_editor !== void 0 && _this$_editor._toolPanel) {
	        this._editor._toolPanel.enableSaveButton();
	      }
	    }
	  }, {
	    key: "disableSaveButton",
	    value: function disableSaveButton() {
	      var _this$_editor2;

	      if ((_this$_editor2 = this._editor) !== null && _this$_editor2 !== void 0 && _this$_editor2._toolPanel) {
	        this._editor._toolPanel.disableSaveButton();
	      }
	    }
	  }, {
	    key: "onEditorControlChange",
	    value: function onEditorControlChange(event) {
	      var _event$getData3 = event.getData(),
	          _event$getData4 = babelHelpers.slicedToArray(_event$getData3, 2),
	          field = _event$getData4[0],
	          params = _event$getData4[1];

	      if (field instanceof BX.UI.EntityEditorMoney && (params === null || params === void 0 ? void 0 : params.fieldName) === 'CURRENCY') {
	        this.changeCurrency(params.fieldValue);
	      }
	    }
	  }, {
	    key: "changeCurrency",
	    value: function changeCurrency(currencyValue) {
	      this._currencyId = currencyValue;

	      if (this.productList && this._currencyId) {
	        this.productList.changeCurrencyId(this._currencyId);
	        this.markAsChanged();
	      }
	    }
	  }, {
	    key: "setTotal",
	    value: function setTotal(totalData) {
	      this._model.setField('FORMATTED_TOTAL', BX.Currency.currencyFormat(totalData.totalCost, this.getCurrencyId(), false));

	      this._model.setField('FORMATTED_TOTAL_WITH_CURRENCY', BX.Currency.currencyFormat(totalData.totalCost, this.getCurrencyId(), true));

	      this._model.setField('TOTAL', totalData.totalCost);

	      var totalCurrencyControl = this._editor.getControlById('TOTAL_WITH_CURRENCY');

	      if (totalCurrencyControl instanceof BX.UI.EntityEditorMoney) {
	        totalCurrencyControl.refreshLayout();
	      }
	    }
	  }, {
	    key: "validateProductList",
	    value: function validateProductList() {
	      var errorsArray = this.productList.validate();

	      if (errorsArray.length > 0) {
	        this._editor._toolPanel.addError(errorsArray[0]);

	        main_core_events.EventEmitter.emit('onProductsCheckFailed', errorsArray);
	        return false;
	      }

	      return true;
	    }
	  }]);
	  return ProductListController;
	}(BX.UI.EntityEditorController);

	function _classPrivateMethodInitSpec(obj, privateSet) { _checkPrivateRedeclaration(obj, privateSet); privateSet.add(obj); }

	function _checkPrivateRedeclaration(obj, privateCollection) { if (privateCollection.has(obj)) { throw new TypeError("Cannot initialize the same private elements twice on an object"); } }

	function _classPrivateMethodGet(receiver, privateSet, fn) { if (!privateSet.has(receiver)) { throw new TypeError("attempted to get private field on non-instance"); } return fn; }

	var _subscribeToEvents = /*#__PURE__*/new WeakSet();

	var _subscribeToProductRowSummaryEvents = /*#__PURE__*/new WeakSet();

	var DocumentCardController = /*#__PURE__*/function (_BX$UI$EntityEditorCo) {
	  babelHelpers.inherits(DocumentCardController, _BX$UI$EntityEditorCo);

	  function DocumentCardController(id, settings) {
	    var _this;

	    babelHelpers.classCallCheck(this, DocumentCardController);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(DocumentCardController).call(this));

	    _classPrivateMethodInitSpec(babelHelpers.assertThisInitialized(_this), _subscribeToProductRowSummaryEvents);

	    _classPrivateMethodInitSpec(babelHelpers.assertThisInitialized(_this), _subscribeToEvents);

	    _this.initialize(id, settings);

	    _this._model.lockField('TOTAL');

	    return _this;
	  }

	  babelHelpers.createClass(DocumentCardController, [{
	    key: "doInitialize",
	    value: function doInitialize() {
	      _classPrivateMethodGet(this, _subscribeToEvents, _subscribeToEvents2).call(this);
	    }
	  }, {
	    key: "onAfterSave",
	    value: function onAfterSave() {
	      babelHelpers.get(babelHelpers.getPrototypeOf(DocumentCardController.prototype), "onAfterSave", this).call(this);
	      window.top.BX.onCustomEvent('DocumentCard:onDocumentCardSave');
	      var sliders = BX.SidePanel.Instance.getOpenSliders();
	      sliders.forEach(function (slider) {
	        var _slider$getWindow, _slider$getWindow$BX$;

	        if ((_slider$getWindow = slider.getWindow()) !== null && _slider$getWindow !== void 0 && (_slider$getWindow$BX$ = _slider$getWindow.BX.Catalog) !== null && _slider$getWindow$BX$ !== void 0 && _slider$getWindow$BX$.DocumentGridManager) {
	          slider.getWindow().BX.onCustomEvent('DocumentCard:onDocumentCardSave');
	        }
	      });
	    }
	  }]);
	  return DocumentCardController;
	}(BX.UI.EntityEditorController);

	function _subscribeToEvents2() {
	  _classPrivateMethodGet(this, _subscribeToProductRowSummaryEvents, _subscribeToProductRowSummaryEvents2).call(this);
	}

	function _subscribeToProductRowSummaryEvents2() {
	  main_core_events.EventEmitter.subscribe('BX.UI.EntityEditorProductRowSummary:onDetailProductListLinkClick', function () {
	    main_core_events.EventEmitter.emit('BX.Catalog.EntityCard.TabManager:onOpenTab', {
	      tabId: 'tab_products'
	    });
	  });
	  main_core_events.EventEmitter.subscribe('BX.UI.EntityEditorProductRowSummary:onAddNewRowInProductList', function () {
	    main_core_events.EventEmitter.emit('BX.Catalog.EntityCard.TabManager:onOpenTab', {
	      tabId: 'tab_products'
	    });
	    setTimeout(function () {
	      main_core_events.EventEmitter.emit('onFocusToProductList');
	    }, 500);
	  });
	}

	var ControllersFactory = /*#__PURE__*/function () {
	  function ControllersFactory(eventName) {
	    var _this = this;

	    babelHelpers.classCallCheck(this, ControllersFactory);
	    main_core_events.EventEmitter.subscribe(eventName, function (event) {
	      var _event$getCompatData = event.getCompatData(),
	          _event$getCompatData2 = babelHelpers.slicedToArray(_event$getCompatData, 2),
	          eventArgs = _event$getCompatData2[1];

	      eventArgs.methods['entityCard'] = _this.factory.bind(_this);
	    });
	  }

	  babelHelpers.createClass(ControllersFactory, [{
	    key: "factory",
	    value: function factory(type, controlId, settings) {
	      if (type === 'document_card') {
	        return new DocumentCardController(controlId, settings);
	      }

	      if (type === 'catalog_store_document_product_list') {
	        return new ProductListController(controlId, settings);
	      }

	      return null;
	    }
	  }]);
	  return ControllersFactory;
	}();

	var DocumentModel = /*#__PURE__*/function (_BX$UI$EntityModel) {
	  babelHelpers.inherits(DocumentModel, _BX$UI$EntityModel);

	  function DocumentModel(id, settings) {
	    var _this;

	    babelHelpers.classCallCheck(this, DocumentModel);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(DocumentModel).call(this));

	    _this.initialize(id, settings);

	    return _this;
	  }

	  babelHelpers.createClass(DocumentModel, [{
	    key: "isCaptionEditable",
	    value: function isCaptionEditable() {
	      return true;
	    }
	  }, {
	    key: "getCaption",
	    value: function getCaption() {
	      var title = this.getField("TITLE");
	      return BX.type.isString(title) ? title : "";
	    }
	  }, {
	    key: "setCaption",
	    value: function setCaption(caption) {
	      this.setField("TITLE", caption);
	    }
	  }, {
	    key: "prepareCaptionData",
	    value: function prepareCaptionData(data) {
	      data["TITLE"] = this.getField("TITLE", "");
	    }
	  }]);
	  return DocumentModel;
	}(BX.UI.EntityModel);

	var ModelFactory = /*#__PURE__*/function () {
	  function ModelFactory() {
	    var _this = this;

	    babelHelpers.classCallCheck(this, ModelFactory);
	    main_core_events.EventEmitter.subscribe('BX.UI.EntityEditorModelFactory:onInitialize', function (event) {
	      var _event$getCompatData = event.getCompatData(),
	          _event$getCompatData2 = babelHelpers.slicedToArray(_event$getCompatData, 2),
	          eventArgs = _event$getCompatData2[1];

	      eventArgs.methods['store_document'] = _this.factory.bind(_this);
	    });
	  }

	  babelHelpers.createClass(ModelFactory, [{
	    key: "factory",
	    value: function factory(type, controlId, settings) {
	      if (type === 'store_document') {
	        return new DocumentModel(controlId, settings);
	      }

	      return null;
	    }
	  }]);
	  return ModelFactory;
	}();

	/**
	 * @deprecated Use BX.UI.EntityEditorProductRowSummary instead
	 */

	var ProductRowSummary = /*#__PURE__*/function (_BX$UI$EntityEditorFi) {
	  babelHelpers.inherits(ProductRowSummary, _BX$UI$EntityEditorFi);

	  function ProductRowSummary(id, settings) {
	    var _this;

	    babelHelpers.classCallCheck(this, ProductRowSummary);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(ProductRowSummary).call(this));

	    _this.initialize(id, settings);

	    _this._loader = null;
	    _this._productsContainer = null;
	    _this._previousData = [];
	    _this._itemCount = 0;
	    _this._totalCount = 0;
	    _this._moreButton = null;
	    _this._moreButtonRow = null;
	    _this._totalsRow = null;
	    _this._moreButtonClickHandler = BX.delegate(_this._onMoreButtonClick, babelHelpers.assertThisInitialized(_this));
	    _this._visibleItemsLimit = 5;
	    return _this;
	  }

	  babelHelpers.createClass(ProductRowSummary, [{
	    key: "layout",
	    value: function layout() {
	      var options = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};

	      if (this._hasLayout) {
	        return;
	      }

	      this.ensureWrapperCreated({});
	      this.adjustWrapper();
	      var data = this.getValue();

	      if (!BX.type.isPlainObject(data)) {
	        return;
	      }

	      var title = this.getTitle();
	      var items = BX.prop.getArray(data, 'items', []);
	      this._totalCount = BX.prop.getInteger(data, 'count', 0);
	      this._itemCount = items.length;
	      var length = this._itemCount;
	      var maxLength = this._visibleItemsLimit;
	      var restLength = 0;

	      if (length > maxLength) {
	        restLength = this._totalCount - maxLength;
	        length = maxLength;
	      }

	      if (this.isDragEnabled()) {
	        this._wrapper.appendChild(this.createDragButton());
	      }

	      this._wrapper.appendChild(this.createTitleNode(title));

	      this._productsContainer = BX.create('div', {
	        props: {
	          className: 'catalog-entity-widget-content-block-products-list'
	        }
	      });

	      for (var i = 0; i < length; i++) {
	        this.addProductRow(items[i]);
	      }

	      this._moreButton = null;

	      if (restLength > 0) {
	        this.addMoreButton(restLength);
	      }

	      this.addTotalRow(data['total']);

	      this._wrapper.appendChild(BX.create('div', {
	        props: {
	          className: 'catalog-entity-widget-content-block-products'
	        },
	        children: [this._productsContainer]
	      }));

	      if (this.isContextMenuEnabled()) {
	        this._wrapper.appendChild(this.createContextMenuButton());
	      }

	      if (this.isDragEnabled()) {
	        this.initializeDragDropAbilities();
	      }

	      this.registerLayout(options);
	      this._hasLayout = true;
	    }
	  }, {
	    key: "addMoreButton",
	    value: function addMoreButton(restLength) {
	      var row = BX.create('div', {
	        props: {
	          className: 'catalog-entity-widget-content-block-products-item'
	        }
	      });
	      this._moreButtonRow = row;

	      this._productsContainer.appendChild(row);

	      var nameCell = BX.create("div", {
	        props: {
	          className: 'catalog-entity-widget-content-block-products-item-name'
	        }
	      });
	      row.appendChild(nameCell);
	      this._moreButton = BX.create('span', {
	        attrs: {
	          className: 'catalog-entity-widget-content-block-products-show-more'
	        },
	        events: {
	          click: this._moreButtonClickHandler
	        },
	        text: main_core.Loc.getMessage('DOCUMENT_PRODUCTS_NOT_SHOWN', {
	          '#COUNT#': restLength.toString()
	        })
	      });
	      nameCell.appendChild(this._moreButton);
	      row.appendChild(BX.create('div', {
	        props: {
	          className: 'catalog-entity-widget-content-block-products-price'
	        }
	      }));
	    }
	  }, {
	    key: "addTotalRow",
	    value: function addTotalRow(total) {
	      var row = BX.create('div', {
	        props: {
	          className: 'catalog-entity-widget-content-block-products-item'
	        }
	      });
	      this._totalsRow = row;

	      this._productsContainer.appendChild(row);

	      var nameCell = BX.create('div', {
	        props: {
	          className: 'catalog-entity-widget-content-block-products-item-name'
	        },
	        html: main_core.Loc.getMessage('DOCUMENT_PRODUCTS_TOTAL')
	      });
	      row.appendChild(nameCell);
	      var valueCell = BX.create('div', {
	        props: {
	          className: 'catalog-entity-widget-content-block-products-price'
	        },
	        html: currency_currencyCore.CurrencyCore.currencyFormat(total.amount, total.currency, true)
	      });
	      row.appendChild(valueCell);
	    }
	  }, {
	    key: "addAddProductButton",
	    value: function addAddProductButton() {
	      var addProductsLink = BX.create('a', {
	        props: {
	          href: '#'
	        }
	      });
	      addProductsLink.text = main_core.Loc.getMessage('DOCUMENT_PRODUCTS_ADD_PRODUCT');

	      addProductsLink.onclick = function () {
	        main_core_events.EventEmitter.emit('BX.Catalog.EntityCard.TabManager:onOpenTab', {
	          tabId: 'tab_products'
	        });
	      };

	      var row = BX.create('div', {
	        props: {
	          className: 'catalog-entity-widget-content-block-products-add-products'
	        },
	        children: [addProductsLink]
	      });

	      this._productsContainer.appendChild(row);
	    }
	  }, {
	    key: "_onMoreButtonClick",
	    value: function _onMoreButtonClick(e) {
	      main_core_events.EventEmitter.emit('BX.Catalog.EntityCard.TabManager:onOpenTab', {
	        tabId: 'tab_products'
	      });
	    }
	  }, {
	    key: "doClearLayout",
	    value: function doClearLayout() {
	      this._productsContainer = null;
	      this._moreButton = null;
	      this._moreButtonRow = null;
	      this._totalsRow = null;
	    }
	  }, {
	    key: "addProductRow",
	    value: function addProductRow(data) {
	      var row = BX.create('div', {
	        props: {
	          className: 'catalog-entity-widget-content-block-products-item'
	        }
	      });

	      this._productsContainer.appendChild(row);

	      var nameCell = BX.create('div', {
	        props: {
	          className: 'catalog-entity-widget-content-block-products-item-name'
	        }
	      });
	      nameCell.innerHTML = BX.util.htmlspecialchars(data['PRODUCT_NAME']);
	      row.appendChild(nameCell);
	      var valueCell = BX.create('div', {
	        props: {
	          className: 'catalog-entity-widget-content-block-products-price'
	        }
	      });
	      row.appendChild(valueCell);
	      valueCell.appendChild(BX.create('div', {
	        attrs: {
	          className: 'catalog-entity-widget-content-block-products-price-value'
	        },
	        html: data['SUM']
	      }));
	    }
	  }]);
	  return ProductRowSummary;
	}(BX.UI.EntityEditorField);

	var _templateObject, _templateObject2, _templateObject3, _templateObject4;

	var Contractor = /*#__PURE__*/function (_BX$UI$EntityEditorFi) {
	  babelHelpers.inherits(Contractor, _BX$UI$EntityEditorFi);

	  function Contractor(id, settings) {
	    var _this;

	    babelHelpers.classCallCheck(this, Contractor);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(Contractor).call(this));

	    _this.initialize(id, settings);

	    _this._input = null;
	    _this.innerWrapper = null;
	    _this.currentContractorName = '';
	    _this.viewModeDisplay = null;
	    return _this;
	  }

	  babelHelpers.createClass(Contractor, [{
	    key: "getContentWrapper",
	    value: function getContentWrapper() {
	      return this.innerWrapper;
	    }
	  }, {
	    key: "layout",
	    value: function layout() {
	      var _this2 = this;

	      var options = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};

	      if (this._hasLayout) {
	        return;
	      }

	      this.ensureWrapperCreated({});
	      this.adjustWrapper();
	      var title = this.getTitle();

	      if (this.isDragEnabled()) {
	        this._wrapper.appendChild(this.createDragButton());
	      }

	      this._wrapper.appendChild(this.createTitleNode(title));

	      var name = this.getName();
	      var value = this.getValue();

	      var data = this._schemeElement.getData();

	      if (!this.currentContractorName) {
	        this.currentContractorName = this.getContractorNameFromModel();
	      }

	      this._input = main_core.Tag.render(_templateObject || (_templateObject = babelHelpers.taggedTemplateLiteral(["<input name=\"", "\" type=\"hidden\" value=\"", "\"/>"])), name, value);

	      this._wrapper.appendChild(this._input);

	      this.innerWrapper = main_core.Tag.render(_templateObject2 || (_templateObject2 = babelHelpers.taggedTemplateLiteral(["<div class=\"ui-entity-editor-content-block\"></div>"])));

	      this._wrapper.appendChild(this.innerWrapper);

	      if (this._mode === BX.UI.EntityEditorMode.edit) {
	        var currentSelectedItems = [];

	        if (value) {
	          currentSelectedItems.push({
	            id: value,
	            entityId: 'contractor',
	            title: this.currentContractorName
	          });
	        }

	        var contractorSelector = new ui_entitySelector.TagSelector({
	          items: currentSelectedItems,
	          placeholder: main_core.Loc.getMessage('DOCUMENT_CONTRACTOR_FIELD_PLACEHOLDER'),
	          textBoxWidth: '100%',
	          multiple: false,
	          dialogOptions: {
	            context: 'catalog_document_contractors',
	            entities: [{
	              id: 'contractor',
	              dynamicLoad: true,
	              dynamicSearch: true
	            }],
	            searchOptions: {
	              allowCreateItem: true,
	              footerOptions: {
	                label: main_core.Loc.getMessage('DOCUMENT_ADD_CONTRACTOR')
	              }
	            },
	            events: {
	              'Item:onSelect': function ItemOnSelect(event) {
	                _this2._input.value = event.data.item.getId();

	                if (_this2.viewModeDisplay) {
	                  _this2.currentContractorName = event.data.item.getTitle();
	                  _this2.viewModeDisplay.innerHTML = BX.util.htmlspecialchars(_this2.currentContractorName);
	                }

	                _this2._changeHandler();
	              },
	              'Search:onItemCreateAsync': this.createContractor.bind(this),
	              'Item:onDeselect': function ItemOnDeselect(event) {
	                _this2._input.value = '';

	                _this2._changeHandler();
	              }
	            }
	          }
	        });
	        contractorSelector.renderTo(this.innerWrapper);

	        if (BX.UI.EntityEditorModeOptions.check(this._modeOptions, BX.UI.EntityEditorModeOptions.individual)) {
	          contractorSelector.getDialog().show();
	        }
	      } else // if(this._mode === BX.UI.EntityEditorMode.view)
	        {
	          if (this.hasContentToDisplay()) {
	            this.viewModeDisplay = main_core.Tag.render(_templateObject3 || (_templateObject3 = babelHelpers.taggedTemplateLiteral(["<div class=\"ui-entity-editor-content-block-text\">", "</div>"])), BX.util.htmlspecialchars(this.currentContractorName));
	          } else {
	            this.viewModeDisplay = main_core.Tag.render(_templateObject4 || (_templateObject4 = babelHelpers.taggedTemplateLiteral(["<div class=\"ui-entity-editor-content-block-text\">", "</div>"])), main_core.Loc.getMessage('DOCUMENT_CONTRACTOR_NOT_FILLED'));
	          }

	          this.innerWrapper.appendChild(this.viewModeDisplay);
	        }

	      if (this.isContextMenuEnabled()) {
	        this._wrapper.appendChild(this.createContextMenuButton());
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
	      if (!(this._mode === BX.UI.EntityEditorMode.edit && this._input)) {
	        throw "BX.Catalog.DocumentCard.Contractor. Invalid validation context";
	      }

	      this.clearError();

	      if (this.hasValidators()) {
	        return this.executeValidators(result);
	      }

	      var isValid = !(this.isRequired() || this.isRequiredByAttribute()) || BX.util.trim(this._input.value) !== "";

	      if (!isValid) {
	        result.addError(BX.UI.EntityValidationError.create({
	          field: this
	        }));
	        this.showRequiredFieldError(this._input);
	      }

	      return isValid;
	    }
	  }, {
	    key: "hasValue",
	    value: function hasValue() {
	      if (this.getValue() === '0') {
	        return false;
	      }

	      return babelHelpers.get(babelHelpers.getPrototypeOf(Contractor.prototype), "hasValue", this).call(this);
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
	  }, {
	    key: "createContractor",
	    value: function createContractor(event) {
	      var _event$getData = event.getData(),
	          searchQuery = _event$getData.searchQuery;

	      var companyName = searchQuery.getQuery();
	      return new Promise(function (resolve, reject) {
	        var dialog = event.getTarget();
	        var fields = {
	          companyName: companyName
	        };
	        dialog.showLoader();
	        main_core.ajax.runAction('catalog.contractor.createContractor', {
	          data: {
	            fields: fields
	          }
	        }).then(function (response) {
	          dialog.hideLoader();
	          var item = dialog.addItem({
	            id: response.data.id,
	            entityId: 'contractor',
	            title: searchQuery.getQuery(),
	            tabs: dialog.getRecentTab().getId()
	          });

	          if (item) {
	            item.select();
	          }

	          dialog.hide();
	          resolve();
	        })["catch"](function () {
	          dialog.hideLoader();
	          BX.UI.Notification.Center.notify({
	            content: main_core.Loc.getMessage('DOCUMENT_ADD_CONTRACTOR_ERROR')
	          });
	          dialog.hide();
	          reject();
	        });
	      });
	    }
	  }, {
	    key: "getContractorNameFromModel",
	    value: function getContractorNameFromModel() {
	      return this._model.getSchemeField(this._schemeElement, 'contractorName', '');
	    }
	  }, {
	    key: "rollback",
	    value: function rollback() {
	      this.currentContractorName = this.getContractorNameFromModel();
	    }
	  }]);
	  return Contractor;
	}(BX.UI.EntityEditorField);

	var FieldsFactory = /*#__PURE__*/function () {
	  function FieldsFactory() {
	    var _this = this;

	    babelHelpers.classCallCheck(this, FieldsFactory);
	    main_core_events.EventEmitter.subscribe('BX.UI.EntityEditorControlFactory:onInitialize', function (event) {
	      var _event$getCompatData = event.getCompatData(),
	          _event$getCompatData2 = babelHelpers.slicedToArray(_event$getCompatData, 2),
	          eventArgs = _event$getCompatData2[1];

	      eventArgs.methods['documentCard'] = _this.factory.bind(_this);
	    });
	  }

	  babelHelpers.createClass(FieldsFactory, [{
	    key: "factory",
	    value: function factory(type, controlId, settings) {
	      if (type === 'contractor') {
	        return new Contractor(controlId, settings);
	      }

	      return null;
	    }
	  }]);
	  return FieldsFactory;
	}();

	function _classStaticPrivateFieldSpecGet(receiver, classConstructor, descriptor) { _classCheckPrivateStaticAccess(receiver, classConstructor); _classCheckPrivateStaticFieldDescriptor(descriptor, "get"); return _classApplyDescriptorGet(receiver, descriptor); }

	function _classApplyDescriptorGet(receiver, descriptor) { if (descriptor.get) { return descriptor.get.call(receiver); } return descriptor.value; }

	function _classStaticPrivateFieldSpecSet(receiver, classConstructor, descriptor, value) { _classCheckPrivateStaticAccess(receiver, classConstructor); _classCheckPrivateStaticFieldDescriptor(descriptor, "set"); _classApplyDescriptorSet(receiver, descriptor, value); return value; }

	function _classCheckPrivateStaticFieldDescriptor(descriptor, action) { if (descriptor === undefined) { throw new TypeError("attempted to " + action + " private static field before its declaration"); } }

	function _classCheckPrivateStaticAccess(receiver, classConstructor) { if (receiver !== classConstructor) { throw new TypeError("Private static access of wrong provenance"); } }

	function _classApplyDescriptorSet(receiver, descriptor, value) { if (descriptor.set) { descriptor.set.call(receiver, value); } else { if (!descriptor.writable) { throw new TypeError("attempted to set read only private field"); } descriptor.value = value; } }

	var DocumentCard = /*#__PURE__*/function (_BaseCard) {
	  babelHelpers.inherits(DocumentCard, _BaseCard);

	  function DocumentCard(id, settings) {
	    var _this;

	    babelHelpers.classCallCheck(this, DocumentCard);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(DocumentCard).call(this, id, settings));
	    _this.documentType = settings.documentType;
	    _this.isDocumentConducted = settings.documentStatus === 'Y';
	    _this.componentName = settings.componentName;
	    _this.signedParameters = settings.signedParameters;
	    _this.isConductLocked = settings.isConductLocked;
	    _this.masterSliderUrl = settings.masterSliderUrl;
	    _this.editorName = settings.includeCrmEntityEditor ? 'BX.Crm.EntityEditor' : 'BX.UI.EntityEditor';
	    _this.inventoryManagementSource = settings.inventoryManagementSource;
	    _this.activeTabId = 'main';
	    _this.isTabAnalyticsSent = false;

	    _this.setSliderText();

	    _this.addCopyLinkPopup();

	    _this.subscribeToEvents();

	    if (settings.documentTypeSelector) {
	      _this.initDocumentTypeSelector();
	    }

	    _classStaticPrivateFieldSpecSet(DocumentCard, DocumentCard, _instance, babelHelpers.assertThisInitialized(_this)); // setting this to true so that we can decide
	    // whether to close the slider or not on the fly on backend (closeOnSave=Y)


	    BX.UI.SidePanel.Wrapper.setParam("closeAfterSave", true);
	    _this.showNotificationOnClose = false;
	    return _this;
	  }

	  babelHelpers.createClass(DocumentCard, [{
	    key: "initDocumentTypeSelector",
	    value: function initDocumentTypeSelector() {
	      var _this2 = this;

	      var documentTypeSelector = this.settings.documentTypeSelector;
	      var documentTypeSelectorTypes = this.settings.documentTypeSelectorTypes;

	      if (!documentTypeSelector || !documentTypeSelectorTypes) {
	        return;
	      }

	      var menuItems = [];
	      documentTypeSelectorTypes.forEach(function (type) {
	        menuItems.push({
	          text: main_core.Loc.getMessage('DOC_TYPE_SHORT_' + type),
	          onclick: function onclick(e) {
	            var slider = BX.SidePanel.Instance.getTopSlider();

	            if (slider) {
	              slider.url = BX.Uri.addParam(slider.getUrl(), {
	                DOCUMENT_TYPE: type
	              });
	              slider.url = BX.Uri.removeParam(slider.url, ['firstTime', 'focusedTab']);

	              if (_this2.activeTabId !== 'main') {
	                slider.url = BX.Uri.addParam(slider.getUrl(), {
	                  focusedTab: _this2.activeTabId
	                });
	              }

	              if (type === 'A' || type === 'S') {
	                slider.requestMethod = 'post';
	                slider.requestParams = {
	                  'preloadedFields': {
	                    'DOCUMENT_FIELDS': _this2.getDocumentFieldsForTypeSwitching(),
	                    'PRODUCTS': _this2.getProductsForTypeSwitching()
	                  }
	                };
	              }

	              slider.setFrameSrc();
	            }
	          }
	        });
	      });
	      var popupMenu = main_popup.MenuManager.create({
	        id: 'document-type-selector',
	        bindElement: documentTypeSelector,
	        items: menuItems
	      });
	      documentTypeSelector.addEventListener('click', function (e) {
	        e.preventDefault();
	        popupMenu.show();
	      });
	    }
	  }, {
	    key: "getDocumentFieldsForTypeSwitching",
	    value: function getDocumentFieldsForTypeSwitching() {
	      var documentFields = {};
	      var editor = this.getEditorInstance();

	      if (!editor) {
	        return documentFields;
	      }

	      var form = editor.getFormElement();
	      var formData = new FormData(form);
	      var formProps = Object.fromEntries(formData);
	      var fieldsToTransfer = ['TITLE', 'CURRENCY', 'TOTAL'];
	      fieldsToTransfer.forEach(function (field) {
	        var _formProps$field;

	        documentFields[field] = (_formProps$field = formProps[field]) !== null && _formProps$field !== void 0 ? _formProps$field : '';
	      });
	      return documentFields;
	    }
	  }, {
	    key: "getProductsForTypeSwitching",
	    value: function getProductsForTypeSwitching() {
	      var products = [];

	      if (!main_core.Reflection.getClass('BX.Catalog.Store.ProductList.Instance')) {
	        return products;
	      }

	      var productFields = ['ID', 'STORE_TO', {
	        'ELEMENT_ID': 'SKU_ID'
	      }, 'AMOUNT', 'PURCHASING_PRICE', 'BASE_PRICE', 'BASE_PRICE_EXTRA', 'BASE_PRICE_EXTRA_RATE'];
	      BX.Catalog.Store.ProductList.Instance.getProductsFields().forEach(function (productRow) {
	        var product = {};
	        productFields.forEach(function (field) {
	          if (main_core.Type.isObject(field)) {
	            var _productRow$sourceFie;

	            var destinationField = Object.keys(field)[0];
	            var sourceField = field[destinationField];
	            product[destinationField] = (_productRow$sourceFie = productRow[sourceField]) !== null && _productRow$sourceFie !== void 0 ? _productRow$sourceFie : '';
	          } else {
	            var _productRow$field;

	            product[field] = (_productRow$field = productRow[field]) !== null && _productRow$field !== void 0 ? _productRow$field : '';
	          }
	        });
	        products.push(product);
	      });
	      return products;
	    }
	  }, {
	    key: "openMasterSlider",
	    value: function openMasterSlider() {
	      var card = this;
	      new catalog_storeUse.Slider().open(this.masterSliderUrl, {
	        data: {
	          openGridOnDone: false
	        },
	        events: {
	          onCloseComplete: function onCloseComplete(event) {
	            var slider = event.getSlider();

	            if (!slider) {
	              return;
	            }

	            if (slider.getData().get('isInventoryManagementEnabled')) {
	              card.isConductLocked = false;
	              BX.SidePanel.Instance.getOpenSliders().forEach(function (slider) {
	                var _slider$getWindow, _slider$getWindow$BX$;

	                if ((_slider$getWindow = slider.getWindow()) !== null && _slider$getWindow !== void 0 && (_slider$getWindow$BX$ = _slider$getWindow.BX.Catalog) !== null && _slider$getWindow$BX$ !== void 0 && _slider$getWindow$BX$.DocumentGridManager) {
	                  slider.allowChangeHistory = false;
	                  slider.getWindow().location.reload();
	                }
	              });
	            }
	          }
	        }
	      });
	    }
	  }, {
	    key: "adjustToolPanel",
	    value: function adjustToolPanel() {
	      return;
	    }
	  }, {
	    key: "focusOnTab",
	    value: function focusOnTab(tabId) {
	      main_core_events.EventEmitter.emit('BX.Catalog.EntityCard.TabManager:onOpenTab', {
	        tabId: tabId
	      });
	    } // deprecated

	  }, {
	    key: "setViewModeButtons",
	    value: function setViewModeButtons(editor) {
	      editor._toolPanel.showViewModeButtons();
	    } // deprecated

	  }, {
	    key: "setEditModeButtons",
	    value: function setEditModeButtons(editor) {
	      editor._toolPanel.showEditModeButtons();
	    }
	  }, {
	    key: "getEditorInstance",
	    value: function getEditorInstance() {
	      var editorInstance = main_core.Reflection.getClass(this.editorName);

	      if (editorInstance) {
	        return editorInstance.getDefault();
	      }

	      return null;
	    }
	  }, {
	    key: "subscribeToEvents",
	    value: function subscribeToEvents() {
	      this.subscribeToUserSelectorEvent();
	      this.subscribeToValidationFailedEvent();
	      this.subscribeToOnSaveEvent();
	      this.subscribeToTabOpenEvent();
	      this.subscribeToDirectActionEvent();
	      this.subscribeToEntityCreateEvent();
	      this.subscribeToBeforeEntityRedirectEvent();
	    }
	  }, {
	    key: "subscribeToUserSelectorEvent",
	    value: function subscribeToUserSelectorEvent() {
	      var _this3 = this;

	      if (this.editorName !== 'BX.UI.EntityEditor') {
	        return;
	      }

	      main_core_events.EventEmitter.subscribe('BX.UI.EntityEditorUser:openSelector', function (event) {
	        var eventData = event.data[1];
	        var dialog = new ui_entitySelector.Dialog({
	          targetNode: eventData.anchor,
	          enableSearch: true,
	          multiple: false,
	          context: 'CATALOG_DOCUMENT',
	          entities: [{
	            id: 'user'
	          }, {
	            id: 'department'
	          }],
	          events: {
	            'Item:onSelect': function ItemOnSelect(onSelectEvent) {
	              var fieldId = eventData.id;
	              var selectedItem = onSelectEvent.data.item;
	              var userData = {
	                entityId: selectedItem.id,
	                avatar: selectedItem.avatar,
	                name: main_core.Text.encode(selectedItem.title.text)
	              };

	              if (_this3.entityId > 0) {
	                var fields = {};
	                fields[fieldId] = selectedItem.id;
	                BX.ajax.runComponentAction(_this3.componentName, 'save', {
	                  mode: 'class',
	                  signedParameters: _this3.signedParameters,
	                  data: {
	                    fields: fields
	                  }
	                }).then(function (result) {
	                  eventData.callback(dialog, userData);
	                });
	              } else {
	                eventData.callback(dialog, userData);
	              }
	            }
	          }
	        });
	        dialog.show();
	      });
	    }
	  }, {
	    key: "subscribeToValidationFailedEvent",
	    value: function subscribeToValidationFailedEvent() {
	      main_core_events.EventEmitter.subscribe(this.editorName + ':onFailedValidation', function (event) {
	        main_core_events.EventEmitter.emit('BX.Catalog.EntityCard.TabManager:onOpenTab', {
	          tabId: 'main'
	        });
	      });
	      main_core_events.EventEmitter.subscribe('onProductsCheckFailed', function (event) {
	        main_core_events.EventEmitter.emit('BX.Catalog.EntityCard.TabManager:onOpenTab', {
	          tabId: 'tab_products'
	        });
	      });
	    }
	  }, {
	    key: "subscribeToOnSaveEvent",
	    value: function subscribeToOnSaveEvent() {
	      var _this4 = this;

	      main_core_events.EventEmitter.subscribe(this.editorName + ':onSave', function (event) {
	        var _event$data$;

	        var eventEditor = event.data[0];
	        var action = (_event$data$ = event.data[1]) === null || _event$data$ === void 0 ? void 0 : _event$data$.actionId;

	        if (eventEditor && eventEditor._ajaxForm) {
	          var _eventEditor$_toolPan;

	          (_eventEditor$_toolPan = eventEditor._toolPanel) === null || _eventEditor$_toolPan === void 0 ? void 0 : _eventEditor$_toolPan.clearErrors();

	          if (action === 'SAVE_AND_CONDUCT') {
	            if (_this4.isConductLocked) {
	              var _event$data$0$_toolPa;

	              event.data[1].cancel = true;
	              (_event$data$0$_toolPa = event.data[0]._toolPanel) === null || _event$data$0$_toolPa === void 0 ? void 0 : _event$data$0$_toolPa.setLocked(false);

	              _this4.openMasterSlider();

	              return;
	            }

	            if (!_this4.validateControllers(eventEditor.getControllers())) {
	              var _eventEditor$_toolPan2;

	              event.data[1].cancel = true;
	              (_eventEditor$_toolPan2 = eventEditor._toolPanel) === null || _eventEditor$_toolPan2 === void 0 ? void 0 : _eventEditor$_toolPan2.setLocked(false);
	              return;
	            }

	            if (event.data[1].cancel) {
	              return;
	            }
	          }

	          var form = eventEditor._ajaxForms[action];

	          if (form) {
	            form.addUrlParams({
	              documentType: _this4.documentType,
	              isNewDocument: _this4.entityId <= 0 ? 'Y' : 'N',
	              inventoryManagementSource: _this4.inventoryManagementSource
	            });
	          }
	        }
	      });
	    }
	  }, {
	    key: "subscribeToTabOpenEvent",
	    value: function subscribeToTabOpenEvent() {
	      var _this5 = this;

	      main_core_events.EventEmitter.subscribe('BX.Catalog.EntityCard.TabManager:onSelectItem', function (event) {
	        var tabId = event.data.tabId;

	        if (tabId === 'tab_products' && !_this5.isTabAnalyticsSent) {
	          _this5.sendAnalyticsData({
	            tab: 'products',
	            isNewDocument: _this5.entityId <= 0 ? 'Y' : 'N',
	            documentType: _this5.documentType,
	            inventoryManagementSource: _this5.inventoryManagementSource
	          });

	          _this5.isTabAnalyticsSent = true;
	        }

	        if (tabId) {
	          _this5.activeTabId = tabId;
	        }
	      });
	    }
	  }, {
	    key: "subscribeToDirectActionEvent",
	    value: function subscribeToDirectActionEvent() {
	      var _this6 = this;

	      main_core_events.EventEmitter.subscribe(this.editorName + ':onDirectAction', function (event) {
	        var _event$data$2, _event$data$3;

	        var eventEditor = event.data[0];

	        if (((_event$data$2 = event.data[1]) === null || _event$data$2 === void 0 ? void 0 : _event$data$2.actionId) === 'CONDUCT') {
	          var _eventEditor$_toolPan3;

	          (_eventEditor$_toolPan3 = eventEditor._toolPanel) === null || _eventEditor$_toolPan3 === void 0 ? void 0 : _eventEditor$_toolPan3.clearErrors();

	          if (_this6.isConductLocked) {
	            var _event$data$0$_toolPa2;

	            event.data[1].cancel = true;
	            (_event$data$0$_toolPa2 = event.data[0]._toolPanel) === null || _event$data$0$_toolPa2 === void 0 ? void 0 : _event$data$0$_toolPa2.setLocked(false);

	            _this6.openMasterSlider();

	            return;
	          }

	          if (!_this6.validateControllers(eventEditor.getControllers())) {
	            var _eventEditor$_toolPan4;

	            event.data[1].cancel = true;
	            (_eventEditor$_toolPan4 = eventEditor._toolPanel) === null || _eventEditor$_toolPan4 === void 0 ? void 0 : _eventEditor$_toolPan4.setLocked(false);
	            return;
	          }

	          event.data[0]._ajaxForms['CONDUCT'].addUrlParams({
	            documentType: _this6.documentType,
	            inventoryManagementSource: _this6.inventoryManagementSource
	          });
	        }

	        if (((_event$data$3 = event.data[1]) === null || _event$data$3 === void 0 ? void 0 : _event$data$3.actionId) === 'CANCEL_CONDUCT') {
	          event.data[0]._ajaxForms['CANCEL_CONDUCT'].addUrlParams({
	            documentType: _this6.documentType,
	            inventoryManagementSource: _this6.inventoryManagementSource
	          });
	        }
	      });
	    }
	  }, {
	    key: "subscribeToEntityCreateEvent",
	    value: function subscribeToEntityCreateEvent() {
	      main_core_events.EventEmitter.subscribe('onEntityCreate', function (event) {
	        var _event$data$4;

	        window.top.BX.onCustomEvent('DocumentCard:onEntityCreate');
	        BX.SidePanel.Instance.getOpenSliders().forEach(function (slider) {
	          var _slider$getWindow2, _slider$getWindow2$BX;

	          if ((_slider$getWindow2 = slider.getWindow()) !== null && _slider$getWindow2 !== void 0 && (_slider$getWindow2$BX = _slider$getWindow2.BX.Catalog) !== null && _slider$getWindow2$BX !== void 0 && _slider$getWindow2$BX.DocumentGridManager) {
	            slider.getWindow().BX.onCustomEvent('DocumentCard:onEntityCreate');
	          }
	        });
	        var editor = event === null || event === void 0 ? void 0 : (_event$data$4 = event.data[0]) === null || _event$data$4 === void 0 ? void 0 : _event$data$4.sender;

	        if (editor) {
	          editor._toolPanel.disableSaveButton();

	          editor.hideToolPanel();
	        }
	      });
	    }
	  }, {
	    key: "subscribeToBeforeEntityRedirectEvent",
	    value: function subscribeToBeforeEntityRedirectEvent() {
	      var _this7 = this;

	      main_core_events.EventEmitter.subscribe('beforeEntityRedirect', function (event) {
	        var _event$data$5;

	        window.top.BX.onCustomEvent('DocumentCard:onBeforeEntityRedirect');
	        BX.SidePanel.Instance.getOpenSliders().forEach(function (slider) {
	          slider.getWindow().BX.onCustomEvent('DocumentCard:onBeforeEntityRedirect');
	        });
	        var editor = event === null || event === void 0 ? void 0 : (_event$data$5 = event.data[0]) === null || _event$data$5 === void 0 ? void 0 : _event$data$5.sender;

	        if (editor) {
	          var _event$data$6;

	          editor._toolPanel.disableSaveButton();

	          editor.hideToolPanel();
	          _this7.showNotificationOnClose = (event === null || event === void 0 ? void 0 : (_event$data$6 = event.data[0]) === null || _event$data$6 === void 0 ? void 0 : _event$data$6.showNotificationOnClose) === 'Y';

	          if (_this7.showNotificationOnClose) {
	            var url = event.data[0].redirectUrl;

	            if (!url) {
	              return;
	            }

	            url = BX.Uri.removeParam(url, 'closeOnSave');
	            window.top.BX.UI.Notification.Center.notify({
	              content: main_core.Loc.getMessage('DOCUMENT_CONDUCT_SUCCESSFUL'),
	              actions: [{
	                title: main_core.Loc.getMessage('DOCUMENT_CONDUCT_SUCCESSFUL_VIEW'),
	                href: url,
	                events: {
	                  click: function click(event, balloon, action) {
	                    balloon.close();
	                  }
	                }
	              }]
	            });
	          }
	        }
	      });
	    }
	  }, {
	    key: "validateControllers",
	    value: function validateControllers(controllers) {
	      var validateResult = true;

	      if (controllers instanceof Array) {
	        controllers.forEach(function (controller) {
	          if (controller instanceof ProductListController) {
	            if (!controller.validateProductList()) {
	              validateResult = false;
	            }
	          }
	        });
	      } else {
	        validateResult = false;
	      }

	      return validateResult;
	    }
	  }, {
	    key: "sendAnalyticsData",
	    value: function sendAnalyticsData(data) {
	      BX.ajax.runAction('catalog.analytics.sendAnalyticsLabel', {
	        analyticsLabel: data
	      });
	    }
	  }, {
	    key: "addCopyLinkPopup",
	    value: function addCopyLinkPopup() {
	      var _this8 = this;

	      var copyLinkButton = document.getElementById(this.settings.copyLinkButtonId);

	      if (!copyLinkButton) {
	        return;
	      }

	      copyLinkButton.onclick = function () {
	        _this8.copyDocumentLinkToClipboard();
	      };
	    }
	  }, {
	    key: "copyDocumentLinkToClipboard",
	    value: function copyDocumentLinkToClipboard() {
	      var url = BX.util.remove_url_param(window.location.href, ["IFRAME", "IFRAME_TYPE"]);

	      if (!BX.clipboard.copy(url)) {
	        return;
	      }

	      var popup = new BX.PopupWindow('catalog_copy_document_url_to_clipboard', document.getElementById(this.settings.copyLinkButtonId), {
	        content: main_core.Loc.getMessage('DOCUMENT_LINK_COPIED'),
	        darkMode: true,
	        autoHide: true,
	        zIndex: 1000,
	        angle: true,
	        bindOptions: {
	          position: "top"
	        }
	      });
	      popup.show();
	      setTimeout(function () {
	        popup.close();
	      }, 1500);
	    }
	  }, {
	    key: "setSliderText",
	    value: function setSliderText() {
	      var slider = BX.SidePanel.Instance.getTopSlider();

	      if (slider) {
	        slider.getLabel().setText(main_core.Loc.getMessage('SLIDER_LABEL_' + this.documentType));
	      }
	    }
	  }, {
	    key: "disableSaveAndConductButton",
	    value: function disableSaveAndConductButton() {
	      if (!this.conductAndSaveButton) {
	        return;
	      }

	      this.conductAndSaveButton.disabled = true;
	      BX.addClass(this.conductAndSaveButton, 'ui-btn-disabled');
	    }
	  }, {
	    key: "enableSaveAndConductButton",
	    value: function enableSaveAndConductButton() {
	      if (!this.conductAndSaveButton) {
	        return;
	      }

	      this.conductAndSaveButton.disabled = false;
	      BX.removeClass(this.conductAndSaveButton, 'ui-btn-disabled');
	    }
	  }], [{
	    key: "getInstance",
	    value: function getInstance() {
	      return _classStaticPrivateFieldSpecGet(DocumentCard, DocumentCard, _instance);
	    }
	  }, {
	    key: "registerFieldFactory",
	    value: function registerFieldFactory() {
	      _classStaticPrivateFieldSpecSet(DocumentCard, DocumentCard, _fieldFactory, new FieldsFactory());
	    }
	  }, {
	    key: "registerModelFactory",
	    value: function registerModelFactory() {
	      _classStaticPrivateFieldSpecSet(DocumentCard, DocumentCard, _modelFactory, new ModelFactory());
	    }
	  }, {
	    key: "registerDocumentControllersFactory",
	    value: function registerDocumentControllersFactory(eventName) {
	      _classStaticPrivateFieldSpecSet(DocumentCard, DocumentCard, _controllersFactory, new ControllersFactory(eventName));
	    }
	  }]);
	  return DocumentCard;
	}(catalog_entityCard.BaseCard);

	var _instance = {
	  writable: true,
	  value: void 0
	};
	var _fieldFactory = {
	  writable: true,
	  value: void 0
	};
	var _modelFactory = {
	  writable: true,
	  value: void 0
	};
	var _controllersFactory = {
	  writable: true,
	  value: void 0
	};

	var _templateObject$1;

	var Button = /*#__PURE__*/function () {
	  function Button() {
	    babelHelpers.classCallCheck(this, Button);
	  }

	  babelHelpers.createClass(Button, null, [{
	    key: "render",
	    value: function render(parentNode, highlight) {
	      var buttonTitle = main_core.Loc.getMessage('FEEDBACK_BUTTON_TITLE');
	      var button = main_core.Tag.render(_templateObject$1 || (_templateObject$1 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<button class=\"ui-btn ui-btn-light-border ui-btn-themes\" title=\"", "\">\n\t\t\t\t<span class=\"ui-btn-text\">\n\t\t\t\t\t", "\n\t\t\t\t</span>\n\t\t\t</button>\n\t\t"])), buttonTitle, buttonTitle);

	      if (highlight) {
	        button.style.zIndex = 140;
	        button.style.backgroundColor = '#fff';
	      }

	      button.addEventListener('click', function () {
	        BX.Catalog.DocumentCard.Slider.openFeedbackForm();
	      });
	      parentNode.appendChild(button);
	      return button;
	    }
	  }]);
	  return Button;
	}();

	function ownKeys(object, enumerableOnly) { var keys = Object.keys(object); if (Object.getOwnPropertySymbols) { var symbols = Object.getOwnPropertySymbols(object); enumerableOnly && (symbols = symbols.filter(function (sym) { return Object.getOwnPropertyDescriptor(object, sym).enumerable; })), keys.push.apply(keys, symbols); } return keys; }

	function _objectSpread(target) { for (var i = 1; i < arguments.length; i++) { var source = null != arguments[i] ? arguments[i] : {}; i % 2 ? ownKeys(Object(source), !0).forEach(function (key) { babelHelpers.defineProperty(target, key, source[key]); }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(target, Object.getOwnPropertyDescriptors(source)) : ownKeys(Object(source)).forEach(function (key) { Object.defineProperty(target, key, Object.getOwnPropertyDescriptor(source, key)); }); } return target; }

	var Slider = /*#__PURE__*/function () {
	  function Slider() {
	    babelHelpers.classCallCheck(this, Slider);
	  }

	  babelHelpers.createClass(Slider, null, [{
	    key: "openFeedbackForm",
	    value: function openFeedbackForm() {
	      var url = new main_core.Uri('/bitrix/components/bitrix/catalog.feedback/slider.php');
	      url.setQueryParams({
	        feedback_type: 'feedback'
	      });
	      return Slider.open(url.toString(), {
	        width: 735
	      });
	    }
	  }, {
	    key: "openIntegrationRequestForm",
	    value: function openIntegrationRequestForm(event) {
	      var params = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : {};

	      if (event && main_core.Type.isFunction(event.preventDefault)) {
	        event.preventDefault();
	      }

	      if (!main_core.Type.isPlainObject(params)) {
	        params = {};
	      }

	      var url = new main_core.Uri('/bitrix/components/bitrix/catalog.feedback/slider.php');
	      url.setQueryParams({
	        feedback_type: 'integration_request'
	      });
	      url.setQueryParams(params);
	      return Slider.open(url.toString(), {
	        width: 735
	      });
	    }
	  }, {
	    key: "open",
	    value: function open(url, options) {
	      if (!main_core.Type.isPlainObject(options)) {
	        options = {};
	      }

	      options = _objectSpread(_objectSpread({}, {
	        cacheable: false,
	        allowChangeHistory: false,
	        events: {}
	      }), options);
	      return new Promise(function (resolve) {
	        if (main_core.Type.isString(url) && url.length > 1) {
	          options.events.onClose = function (event) {
	            resolve(event.getSlider());
	          };

	          BX.SidePanel.Instance.open(url, options);
	        } else {
	          resolve();
	        }
	      });
	    }
	  }]);
	  return Slider;
	}();

	exports.DocumentCard = DocumentCard;
	exports.ProductListController = ProductListController;
	exports.FeedbackButton = Button;
	exports.Slider = Slider;

}((this.BX.Catalog.DocumentCard = this.BX.Catalog.DocumentCard || {}),BX.Catalog.EntityCard,BX.Event,BX.Currency,BX.UI.EntitySelector,BX.Main,BX.Catalog.StoreUse,BX,BX));
//# sourceMappingURL=document-card.bundle.js.map
