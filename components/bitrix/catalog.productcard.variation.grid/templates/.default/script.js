(function (exports,main_core,main_core_events,main_popup,ui_dialogs_messagebox,ui_entitySelector) {
	'use strict';

	var _templateObject, _templateObject2, _templateObject3, _templateObject4, _templateObject5, _templateObject6, _templateObject7, _templateObject8, _templateObject9, _templateObject10, _templateObject11, _templateObject12, _templateObject13, _templateObject14, _templateObject15;

	function ownKeys(object, enumerableOnly) { var keys = Object.keys(object); if (Object.getOwnPropertySymbols) { var symbols = Object.getOwnPropertySymbols(object); enumerableOnly && (symbols = symbols.filter(function (sym) { return Object.getOwnPropertyDescriptor(object, sym).enumerable; })), keys.push.apply(keys, symbols); } return keys; }

	function _objectSpread(target) { for (var i = 1; i < arguments.length; i++) { var source = null != arguments[i] ? arguments[i] : {}; i % 2 ? ownKeys(Object(source), !0).forEach(function (key) { babelHelpers.defineProperty(target, key, source[key]); }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(target, Object.getOwnPropertyDescriptors(source)) : ownKeys(Object(source)).forEach(function (key) { Object.defineProperty(target, key, Object.getOwnPropertyDescriptor(source, key)); }); } return target; }

	function _classStaticPrivateMethodGet(receiver, classConstructor, method) { _classCheckPrivateStaticAccess(receiver, classConstructor); return method; }

	function _classCheckPrivateStaticAccess(receiver, classConstructor) { if (receiver !== classConstructor) { throw new TypeError("Private static access of wrong provenance"); } }
	var GRID_TEMPLATE_ROW = 'template_0';

	var VariationGrid = /*#__PURE__*/function () {
	  function VariationGrid() {
	    var settings = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};
	    babelHelpers.classCallCheck(this, VariationGrid);
	    babelHelpers.defineProperty(this, "grid", null);
	    babelHelpers.defineProperty(this, "isNew", false);
	    babelHelpers.defineProperty(this, "propertiesWithMenu", []);
	    this.createPropertyId = settings.createPropertyId;
	    this.createPropertyHintId = settings.createPropertyHintId;
	    this.gridId = settings.gridId;
	    this.isNew = settings.isNew;
	    this.isReadOnly = settings.isReadOnly;
	    this.isSimple = settings.isSimple;
	    this.hiddenProperties = settings.hiddenProperties;
	    this.modifyPropertyLink = settings.modifyPropertyLink;
	    this.productCopyLink = settings.productCopyLink;
	    this.gridEditData = settings.gridEditData;
	    this.canHaveSku = settings.canHaveSku || false;
	    this.storeAmount = settings.storeAmount;
	    this.isShowedStoreReserve = settings.isShowedStoreReserve;
	    this.reservedDealsSliderLink = settings.reservedDealsSliderLink;

	    if (settings.copyItemsMap) {
	      this.getGrid().arParams.COPY_ITEMS_MAP = settings.copyItemsMap;
	    }

	    if (settings.supportedAjaxFields) {
	      this.getGrid().arParams.SUPPORTED_AJAX_FIELDS = settings.supportedAjaxFields;
	    }

	    if (!this.isNew) {
	      this.bindPopupInitToQuantityNodes();
	      this.bindSliderToReservedQuantityNodes();
	    }

	    if (this.isReadOnly) {
	      return;
	    }

	    var isGridReload = settings.isGridReload || false;

	    if (!isGridReload) {
	      this.addCustomClassToGrid();
	      this.bindCreateNewVariation();
	      this.bindCreateSkuProperty();
	      this.clearGridSettingsPopupStuff();
	    }

	    var gridEditData = settings.gridEditData || null;

	    if (gridEditData) {
	      this.setGridEditData(gridEditData);
	    }

	    if (this.isNew) {
	      this.enableEdit();
	      this.prepareNewNodes();
	      this.getGrid().updateCounterSelected();
	      this.getGrid().disableCheckAllCheckboxes();
	    } else {
	      this.bindInlineEdit();
	    }

	    main_core.Event.bind(this.getGrid().getScrollContainer(), 'scroll', main_core.Runtime.throttle(this.onScrollHandler.bind(this), 50));
	    this.modifyHeaders();
	    this.subscribeCustomEvents();
	  }

	  babelHelpers.createClass(VariationGrid, [{
	    key: "subscribeCustomEvents",
	    value: function subscribeCustomEvents() {
	      this.onGridUpdatedHandler = this.onGridUpdated.bind(this);
	      main_core_events.EventEmitter.subscribe('Grid::updated', this.onGridUpdatedHandler);
	      this.onPropertySaveHandler = this.onPropertySave.bind(this);
	      main_core_events.EventEmitter.subscribe('SidePanel.Slider:onMessage', this.onPropertySaveHandler);
	      this.onAllRowsSelectHandler = this.enableEdit.bind(this);
	      main_core_events.EventEmitter.subscribe('Grid::allRowsSelected', this.onAllRowsSelectHandler);
	      this.onAllRowsUnselectHandler = this.disableEdit.bind(this);
	      main_core_events.EventEmitter.subscribe('Grid::allRowsUnselected', this.onAllRowsUnselectHandler);
	      this.showPropertySettingsSliderHandler = this.showPropertySettingsSlider.bind(this);
	      main_core_events.EventEmitter.subscribe('VariationGrid::propertyModify', this.showPropertySettingsSliderHandler);
	      this.onPrepareDropDownItemsHandler = this.onPrepareDropDownItems.bind(this);
	      main_core_events.EventEmitter.subscribe('Dropdown::onPrepareItems', this.onPrepareDropDownItemsHandler);
	      this.onCreatePopupHandler = this.onCreatePopup.bind(this);
	      main_core_events.EventEmitter.subscribe('UiSelect::onCreatePopup', this.onCreatePopupHandler);
	    }
	  }, {
	    key: "destroy",
	    value: function destroy() {
	      this.unsubscribeCustomEvents();
	      this.destroyStoreAmountPopups();
	    }
	  }, {
	    key: "unsubscribeCustomEvents",
	    value: function unsubscribeCustomEvents() {
	      if (this.onGridUpdatedHandler) {
	        main_core_events.EventEmitter.unsubscribe('Grid::updated', this.onGridUpdatedHandler);
	        this.onGridUpdatedHandler = null;
	      }

	      if (this.onPropertySaveHandler) {
	        main_core_events.EventEmitter.unsubscribe('SidePanel.Slider:onMessage', this.onPropertySaveHandler);
	        this.onPropertySaveHandler = null;
	      }

	      if (this.showPropertySettingsSliderHandler) {
	        main_core_events.EventEmitter.unsubscribe('VariationGrid::propertyModify', this.showPropertySettingsSliderHandler);
	        this.showPropertySettingsSliderHandler = null;
	      }

	      if (this.onPrepareDropDownItemsHandler) {
	        main_core_events.EventEmitter.unsubscribe('Dropdown::onPrepareItems', this.onPrepareDropDownItemsHandler);
	        this.onPrepareDropDownItemsHandler = null;
	      }

	      if (this.onAllRowsSelectHandler) {
	        main_core_events.EventEmitter.unsubscribe('Grid::allRowsSelected', this.onAllRowsSelectHandler);
	        this.onAllRowsSelectHandler = null;
	      }

	      if (this.onAllRowsUnselectHandler) {
	        main_core_events.EventEmitter.unsubscribe('Grid::allRowsUnselected', this.onAllRowsUnselectHandler);
	        this.onAllRowsUnselectHandler = null;
	      }

	      if (this.onCreatePopupHandler) {
	        main_core_events.EventEmitter.unsubscribe('UiSelect::onCreatePopup', this.onCreatePopupHandler);
	        this.onCreatePopupHandler = null;
	      }
	    }
	  }, {
	    key: "modifyHeaders",
	    value: function modifyHeaders() {
	      var headers = this.getGrid().getParam('COLUMNS_ALL');

	      for (var headerName in headers) {
	        var header = headers[headerName];

	        if (!header.locked && !main_core.Type.isStringFilled(header.headerHint)) {
	          continue;
	        }

	        var headerCell = this.getGrid().getColumnHeaderCellByName(header.id);
	        var headerTitle = headerCell === null || headerCell === void 0 ? void 0 : headerCell.querySelector('.main-grid-head-title');

	        if (!headerTitle) {
	          continue;
	        }

	        if (header.locked) {
	          var lock = main_core.Tag.render(_templateObject || (_templateObject = babelHelpers.taggedTemplateLiteral(["<span class='ui-btn ui-btn-link ui-btn-icon-lock'></span>"])));
	          main_core.Dom.addClass(headerTitle.parentNode, 'main-grid-cell-head-container--locked');
	          main_core.Dom.prepend(lock, headerTitle);
	        }

	        if (main_core.Type.isStringFilled(header.headerHint)) {
	          main_core.Dom.attr(headerTitle, 'data-hint-no-icon', 'true');
	          main_core.Dom.attr(headerTitle, 'data-hint', header.headerHint);
	          BX.UI.Hint.init(headerCell);
	        }
	      }
	    }
	  }, {
	    key: "onScrollHandler",
	    value: function onScrollHandler(event) {
	      var popup = main_popup.PopupManager.getCurrentPopup();

	      if (popup) {
	        popup.close();
	      }

	      this.propertiesWithMenu.forEach(function (propertyId) {
	        var menu = main_popup.MenuManager.getMenuById(propertyId + '_menu');

	        if (menu) {
	          menu.close();
	        }
	      });
	    }
	  }, {
	    key: "onPrepareDropDownItems",
	    value: function onPrepareDropDownItems(event) {
	      var _event$getData = event.getData(),
	          _event$getData2 = babelHelpers.slicedToArray(_event$getData, 3),
	          controlId = _event$getData2[0],
	          menuId = _event$getData2[1],
	          items = _event$getData2[2];

	      if (!main_core.Type.isStringFilled(controlId)) {
	        return;
	      }

	      this.propertiesWithMenu.push(controlId);

	      if (controlId.indexOf('SKU_GRID_PROPERTY_') === -1) {
	        return;
	      }

	      if (!main_core.Type.isArray(items)) {
	        return;
	      }

	      var actionList = items.filter(function (item) {
	        return item.action === 'create-new';
	      });

	      if (actionList.length > 0) {
	        return;
	      }

	      var propertyId = controlId.replace('SKU_GRID_PROPERTY_', '').replace('_control', '');
	      items.push({
	        'action': 'create-new',
	        'html': "\n\t\t\t\t<li data-role=\"createItem\" class=\"catalog-productcard-popup-select-item catalog-productcard-popup-select-item-new\">\n\t\t\t\t\t<label class=\"catalog-productcard-popup-select-label main-dropdown-item\" data-pseudo=\"true\">\n\t\t\t\t\t\t<span class=\"catalog-productcard-popup-select-add\"></span>\n\t\t\t\t\t\t<span class=\"catalog-productcard-popup-select-text\">\n\t\t\t\t\t\t\t".concat(main_core.Loc.getMessage('C_PVG_ADD_NEW_PROPERTY_VALUE_BUTTON'), "\n\t\t\t\t\t\t</span>\n\t\t\t\t\t</label>\n\t\t\t\t</li>"),
	        'onclick': function onclick() {
	          return BX.Catalog.VariationGrid.firePropertyModification(propertyId, menuId);
	        }
	      });
	      requestAnimationFrame(function () {
	        var popup = document.getElementById('menu-popup-' + menuId);
	        main_core.Dom.addClass(popup, 'catalog-productcard-popup-list');
	      });
	    }
	  }, {
	    key: "onCreatePopup",
	    value: function onCreatePopup(event) {
	      var _popup$bindElement;

	      var _event$getData3 = event.getData(),
	          _event$getData4 = babelHelpers.slicedToArray(_event$getData3, 1),
	          popup = _event$getData4[0];

	      var bindElementId = popup === null || popup === void 0 ? void 0 : (_popup$bindElement = popup.bindElement) === null || _popup$bindElement === void 0 ? void 0 : _popup$bindElement.id;

	      if (!main_core.Type.isStringFilled(bindElementId)) {
	        return;
	      }

	      if (bindElementId.indexOf('SKU_GRID_PROPERTY_') === -1) {
	        return;
	      }

	      var propertyId = bindElementId.replace('SKU_GRID_PROPERTY_', '').replace('_control', '');
	      var addButton = main_core.Tag.render(_templateObject2 || (_templateObject2 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<div class=\"catalog-productcard-popup-select-item catalog-productcard-popup-multi-select-item-new\">\n\t\t\t\t<label\n\t\t\t\t\tclass=\"catalog-productcard-popup-select-label main-dropdown-item\">\n\t\t\t\t\t<span class=\"catalog-productcard-popup-select-add\"></span>\n\t\t\t\t\t<span class=\"catalog-productcard-popup-select-text\">\n\t\t\t\t\t\t", "\n\t\t\t\t\t</span>\n\t\t\t\t</label>\n\t\t\t</div>\n\t\t"])), main_core.Loc.getMessage('C_PVG_ADD_NEW_PROPERTY_VALUE_BUTTON'));
	      main_core.Event.bind(addButton, 'mousedown', BX.Catalog.VariationGrid.firePropertyModification.bind(this, propertyId));
	      popup.contentContainer.appendChild(addButton);
	    }
	  }, {
	    key: "clearGridSettingsPopupStuff",
	    value: function clearGridSettingsPopupStuff() {
	      main_core.Dom.remove(document.getElementById(this.gridId + '-grid-settings-window'));
	    }
	  }, {
	    key: "bindCreateNewVariation",
	    value: function bindCreateNewVariation() {
	      if (!this.canHaveSku) {
	        return;
	      }

	      var addRowButton = document.querySelector('[data-role="catalog-productcard-variation-add-row"]');

	      if (!main_core.Type.isDomNode(addRowButton)) {
	        return;
	      }

	      if (this.isSimple) {
	        main_core.Event.bind(addRowButton, 'click', this.openSimpleProductRestrictionPopup.bind(this));
	        return;
	      }

	      main_core.Event.bind(addRowButton, 'click', this.addRowToGrid.bind(this));
	    }
	  }, {
	    key: "addCustomClassToGrid",
	    value: function addCustomClassToGrid() {
	      main_core.Dom.addClass(this.getGrid().getContainer(), 'catalog-product-variation-grid');
	    }
	    /**
	     * @returns {BX.Main.grid|null}
	     */

	  }, {
	    key: "getGrid",
	    value: function getGrid() {
	      if (this.grid === null) {
	        if (!main_core.Reflection.getClass('BX.Main.gridManager.getInstanceById')) {
	          throw Error("Cannot find grid with '".concat(this.gridId, "' id."));
	        }

	        this.grid = BX.Main.gridManager.getInstanceById(this.gridId);
	      }

	      return this.grid;
	    }
	  }, {
	    key: "bindPopupInitToQuantityNodes",
	    value: function bindPopupInitToQuantityNodes() {
	      var _this = this;

	      var rows = this.getGrid().getRows().getRows();
	      rows.forEach(function (row) {
	        if (row.isBodyChild() && !row.isTemplate()) {
	          var quantityNode = row.getNode().querySelector('.main-grid-cell-content-catalog-quantity-inventory-management');

	          if (main_core.Type.isDomNode(quantityNode)) {
	            main_core.Event.bind(quantityNode, 'click', _this.openStoreAmountPopup.bind(_this, row.getId(), quantityNode));
	          }
	        }
	      });
	    }
	  }, {
	    key: "bindSliderToReservedQuantityNodes",
	    value: function bindSliderToReservedQuantityNodes() {
	      var _this2 = this;

	      var rows = this.getGrid().getRows().getRows();
	      rows.forEach(function (row) {
	        if (row.isBodyChild() && !row.isTemplate()) {
	          var reservedQuantityNode = row.getNode().querySelector('.main-grid-cell-content-catalog-reserved-quantity');

	          if (main_core.Type.isDomNode(reservedQuantityNode)) {
	            main_core.Event.bind(reservedQuantityNode, 'click', _this2.openDealsWithReservedProductSlider.bind(_this2, row.getId()));
	          }
	        }
	      });
	    }
	  }, {
	    key: "openSimpleProductRestrictionPopup",
	    value: function openSimpleProductRestrictionPopup(event) {
	      var _this3 = this;

	      event.preventDefault();
	      event.stopPropagation();
	      var id = 'simple-product-restriction';
	      var popup = main_popup.PopupManager.getPopupById(id);

	      if (!popup) {
	        popup = new main_popup.Popup({
	          id: id,
	          width: 400,
	          zIndexOptions: 4000,
	          autoHide: false,
	          draggable: true,
	          overlay: true,
	          className: "bxc-popup-window",
	          content: _classStaticPrivateMethodGet(VariationGrid, VariationGrid, _getSimpleProductRestrictionContent).call(VariationGrid),
	          buttons: [new BX.UI.Button({
	            text: main_core.Loc.getMessage('C_PVG_SIMPLE_PRODUCT_POPUP_BUTTON_COPY'),
	            color: BX.UI.Button.Color.PRIMARY,
	            onclick: function onclick() {
	              BX.SidePanel.Instance.open(_this3.productCopyLink);
	            }
	          }), new BX.UI.Button({
	            text: main_core.Loc.getMessage('C_PVG_SIMPLE_PRODUCT_POPUP_BUTTON_CLOSE'),
	            color: BX.UI.Button.Color.LINK,
	            onclick: function onclick() {
	              popup.close();
	            }
	          })]
	        });
	      }

	      popup.show();
	    }
	  }, {
	    key: "openStoreAmountPopup",
	    value: function openStoreAmountPopup(rowId, quantityNode) {
	      var popupId = rowId + '-store-amount';
	      var popup = main_popup.PopupManager.getPopupById(popupId);

	      if (!popup) {
	        popup = new main_popup.Popup(popupId, quantityNode, {
	          autoHide: true,
	          draggable: false,
	          offsetLeft: -218,
	          offsetTop: 0,
	          angle: {
	            position: 'top',
	            offset: 250
	          },
	          noAllPaddings: true,
	          bindOptions: {
	            forceBindPosition: true
	          },
	          closeByEsc: true,
	          content: this.getStoreAmountPopupContent(rowId)
	        });
	      }

	      popup.show();
	    }
	  }, {
	    key: "openDealsWithReservedProductSlider",
	    value: function openDealsWithReservedProductSlider(rowId) {
	      var storeId = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : 0;

	      if (!this.reservedDealsSliderLink) {
	        return;
	      }

	      var sliderLink = new main_core.Uri(this.reservedDealsSliderLink);
	      sliderLink.setQueryParam('productId', rowId);

	      if (storeId > 0) {
	        sliderLink.setQueryParam('storeId', storeId);
	      }

	      BX.SidePanel.Instance.open(sliderLink.toString(), {
	        allowChangeHistory: false,
	        cacheable: false
	      });
	    }
	  }, {
	    key: "getStoreAmountPopupContent",
	    value: function getStoreAmountPopupContent(rowId) {
	      var skuStoreAmountData = this.storeAmount[rowId];
	      var currentSkusCount = (skuStoreAmountData === null || skuStoreAmountData === void 0 ? void 0 : skuStoreAmountData.storesCount) || 0;

	      if (!main_core.Type.isObject(skuStoreAmountData) || currentSkusCount <= 0) {
	        return main_core.Tag.render(_templateObject3 || (_templateObject3 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"store-amount-popup-container\">\n\t\t\t\t\t<p class=\"store-amount-popup-not-found-message\">", "</p>\n\t\t\t\t</div>\n\t\t\t"])), main_core.Loc.getMessage('C_PVG_STORE_AMOUNT_POPUP_EMPTY'));
	      }

	      var stores = skuStoreAmountData.stores;
	      var linkToDetails = skuStoreAmountData === null || skuStoreAmountData === void 0 ? void 0 : skuStoreAmountData.linkToDetails;
	      return main_core.Tag.render(_templateObject4 || (_templateObject4 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<div class=\"store-amount-popup-container\">\n\t\t\t\t", "\n\t\t\t\t", "\n\t\t\t</div>\n\t\t"])), this.getStoreAmountTable(stores, rowId), linkToDetails ? this.getOpenStoreAmountDetailsSliderLabel(linkToDetails, currentSkusCount) : '');
	    }
	  }, {
	    key: "getStoreAmountTable",
	    value: function getStoreAmountTable(stores, rowId) {
	      var _this4 = this;

	      var table = main_core.Tag.render(_templateObject5 || (_templateObject5 = babelHelpers.taggedTemplateLiteral(["<table class=\"main-grid-table\"></table>"])));
	      var tableHead = table.createTHead();
	      tableHead.className = 'main-grid-header';
	      var tableHeadRow = tableHead.insertRow();
	      tableHeadRow.className = 'main-grid-row-head';
	      this.addCellToTable(tableHeadRow, main_core.Loc.getMessage('C_PVG_STORE_AMOUNT_POPUP_STORE'), true, 'left');
	      this.addCellToTable(tableHeadRow, main_core.Loc.getMessage('C_PVG_STORE_AMOUNT_POPUP_QUANTITY_COMMON1'), true);

	      if (this.isShowedStoreReserve) {
	        this.addCellToTable(tableHeadRow, main_core.Loc.getMessage('C_PVG_STORE_AMOUNT_POPUP_QUANTITY_RESERVED'), true);
	        this.addCellToTable(tableHeadRow, main_core.Loc.getMessage('C_PVG_STORE_AMOUNT_POPUP_QUANTITY_AVAILABLE'), true);
	      }

	      var tableBody = table.createTBody();
	      stores.forEach(function (store) {
	        var tableRow = tableBody.insertRow();
	        tableRow.className = 'main-grid-row main-grid-row-body';

	        _this4.addCellToTable(tableRow, store.title, false, 'left');

	        _this4.addCellToTable(tableRow, store.quantityCommon, false);

	        if (_this4.isShowedStoreReserve) {
	          var quantityReservedNode = main_core.Tag.render(_templateObject6 || (_templateObject6 = babelHelpers.taggedTemplateLiteral(["<a class=\"main-grid-cell-content-catalog-reserved-quantity\">", "</a>"])), store.quantityReserved);
	          main_core.Event.bind(quantityReservedNode, 'click', _this4.openDealsWithReservedProductSlider.bind(_this4, rowId, store.storeId));

	          _this4.addCellToTable(tableRow, quantityReservedNode, false);

	          var quantityAvailable = parseInt(store.quantityAvailable, 10);
	          var viewQuantityAvailable = quantityAvailable <= 0 ? "<span class=\"text--danger\">".concat(quantityAvailable, "</span>") : quantityAvailable;

	          _this4.addCellToTable(tableRow, viewQuantityAvailable, false);
	        }
	      });
	      return table;
	    }
	  }, {
	    key: "addCellToTable",
	    value: function addCellToTable(row, textContent, isHead) {
	      var horizontalPosition = arguments.length > 3 && arguments[3] !== undefined ? arguments[3] : 'right';
	      var cellClassName = isHead ? 'main-grid-cell-head main-grid-col-no-sortable main-grid-cell-' : 'main-grid-cell main-grid-cell-';
	      var innerClassName = isHead ? 'main-grid-cell-head-container' : 'main-grid-cell-content';
	      var cell = row.insertCell();
	      cell.className = cellClassName + horizontalPosition;
	      cell.appendChild(main_core.Tag.render(_templateObject7 || (_templateObject7 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<div class=\"main-grid-cell-inner\">\n\t\t\t\t<span class=\"", "\">\n\t\t\t\t\t", "\n\t\t\t\t</span>\n\t\t\t</div>\n\t\t"])), innerClassName, textContent));
	    }
	  }, {
	    key: "getOpenStoreAmountDetailsSliderLabel",
	    value: function getOpenStoreAmountDetailsSliderLabel(linkToDetails, currentSkusCount) {
	      var openSliderLabel = main_core.Tag.render(_templateObject8 || (_templateObject8 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<span class=\"ui-link ui-link-secondary ui-link-dashed ui-link-open-store-amount-slider\">\n\t\t\t\t", "\n\t\t\t</span>\n\t\t"])), main_core.Loc.getMessage('C_PVG_STORE_AMOUNT_POPUP_OPEN_SLIDER_BUTTON', {
	        '#STORE_COUNT#': currentSkusCount
	      }));
	      main_core.Event.bind(openSliderLabel, 'click', this.openStoreAmountSlider.bind(this, linkToDetails));
	      return openSliderLabel;
	    }
	  }, {
	    key: "openStoreAmountSlider",
	    value: function openStoreAmountSlider(linkToDetails) {
	      BX.SidePanel.Instance.open(linkToDetails, {
	        width: 700,
	        allowChangeHistory: false,
	        cacheable: false
	      });
	    }
	  }, {
	    key: "destroyStoreAmountPopups",
	    value: function destroyStoreAmountPopups() {
	      var rows = this.getGrid().getRows().getRows();
	      rows.forEach(function (row) {
	        if (row.isBodyChild() && !row.isTemplate()) {
	          var _popup$destroy;

	          var popupId = row.getId() + '-store-amount';
	          var popup = main_popup.PopupManager.getPopupById(popupId);
	          popup === null || popup === void 0 ? void 0 : (_popup$destroy = popup.destroy) === null || _popup$destroy === void 0 ? void 0 : _popup$destroy.call(popup);
	        }
	      });
	    }
	  }, {
	    key: "emitEditedRowsEvent",
	    value: function emitEditedRowsEvent() {
	      if (this.getGrid().getRows().isSelected()) {
	        main_core_events.EventEmitter.emit('Grid::thereEditedRows', []);
	      } else {
	        main_core_events.EventEmitter.emit('Grid::noEditedRows', []);
	      }
	    }
	  }, {
	    key: "disableEdit",
	    value: function disableEdit() {
	      if (this.isNew) {
	        return;
	      }

	      this.getGrid().getRows().getRows().forEach(function (current) {
	        if (!main_core.Dom.hasClass(current.getNode(), 'main-grid-row-new')) {
	          current.editCancel();
	          current.unselect();
	        }
	      });
	      this.emitEditedRowsEvent();
	    }
	  }, {
	    key: "enableEdit",
	    value: function enableEdit() {
	      var _this5 = this;

	      if (this.isReadOnly) {
	        return;
	      }

	      this.getGrid().getRows().selectAll();
	      this.getGrid().getRows().editSelected();
	      this.getGrid().getRows().getRows().forEach(function (item) {
	        return _this5.enableBarcodeEditor(item);
	      });
	    }
	  }, {
	    key: "prepareNewNodes",
	    value: function prepareNewNodes() {
	      var _this6 = this;

	      this.getGrid().getRows().getBodyChild().map(function (row) {
	        var newNode = row.getNode();

	        _this6.markNodeAsNew(newNode);

	        _this6.addSkuListCreationItem(newNode);

	        _this6.modifyCustomSkuProperties(newNode);

	        _this6.disableCheckbox(row);
	      });
	    }
	  }, {
	    key: "disableCheckbox",
	    value: function disableCheckbox(row) {
	      var checkbox = row.getCheckbox();

	      if (main_core.Type.isDomNode(checkbox)) {
	        checkbox.setAttribute('disabled', 'disabled');
	      }
	    }
	  }, {
	    key: "markNodeAsNew",
	    value: function markNodeAsNew(node) {
	      main_core.Dom.addClass(node, 'main-grid-row-new');
	    }
	  }, {
	    key: "bindInlineEdit",
	    value: function bindInlineEdit() {
	      var _this7 = this;

	      this.getGrid().getRows().getBodyChild().forEach(function (item) {
	        return main_core.Event.bind(item.node, 'click', function (event) {
	          return _this7.toggleInlineEdit(item, event);
	        });
	      });
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
	    key: "bindCreateSkuProperty",
	    value: function bindCreateSkuProperty() {
	      if (!this.canHaveSku) {
	        return;
	      }

	      var createPropertyNode = document.getElementById(this.createPropertyId);
	      var control = this.getEditorInstance().getControlByIdRecursive('variation_grid');

	      if (main_core.Type.isDomNode(createPropertyNode) && control) {
	        control._createChildButton = createPropertyNode;
	        main_core.Event.bind(createPropertyNode, 'click', control.onCreateFieldBtnClick.bind(control));
	      }

	      var createPropertyHintNode = document.getElementById(this.createPropertyHintId);
	      main_core.Event.bind(createPropertyHintNode, 'click', this.showHelp.bind(this));
	    }
	  }, {
	    key: "showHelp",
	    value: function showHelp(event) {
	      if (main_core.Reflection.getClass('top.BX.Helper')) {
	        top.BX.Helper.show('redirect=detail&code=11657102');
	        event.preventDefault();
	      }
	    } // ToDo auto focus on input under event.point?

	  }, {
	    key: "toggleInlineEdit",
	    value: function toggleInlineEdit(item, event) {
	      var _this8 = this;

	      var changed = false;

	      if (item.isEdit()) {
	        if (this.hasClickedOnCheckboxArea(item, event.target)) {
	          changed = true;
	          this.deactivateInlineEdit(item);
	        }
	      } else {
	        if (event.target.nodeName !== 'A') {
	          changed = true;
	          this.activateInlineEdit(item);
	        }

	        if (this.isSimple) {
	          var _item$getNode, _item$getNode2;

	          (_item$getNode = item.getNode()) === null || _item$getNode === void 0 ? void 0 : _item$getNode.querySelectorAll('.main-grid-editor.main-dropdown.main-grid-editor-dropdown').forEach(function (item) {
	            var id = item.id;

	            if (main_core.Type.isNil(id) || id.indexOf('SKU_GRID_PROPERTY_') === -1) {
	              return;
	            }

	            main_core.Event.unbindAll(item);
	            main_core.Event.bind(item, 'click', _this8.openSimpleProductRestrictionPopup.bind(_this8));
	          });
	          (_item$getNode2 = item.getNode()) === null || _item$getNode2 === void 0 ? void 0 : _item$getNode2.querySelectorAll('.catalog-productcard-select-container .catalog-productcard-select-block').forEach(function (item) {
	            item.onclick = null;
	            main_core.Event.unbindAll(item);
	            main_core.Event.bind(item, 'click', _this8.openSimpleProductRestrictionPopup.bind(_this8));
	          });
	        }
	      }

	      if (changed) {
	        this.emitEditedRowsEvent();
	        this.getGrid().adjustRows();
	        this.getGrid().updateCounterSelected();
	        this.getGrid().updateCounterDisplayed();
	        this.getGrid().adjustCheckAllCheckboxes();
	      }
	    }
	  }, {
	    key: "hasClickedOnCheckboxArea",
	    value: function hasClickedOnCheckboxArea(item, target) {
	      if (!main_core.Type.isDomNode(target)) {
	        return;
	      }

	      var cells = item.getCells();

	      for (var i in cells) {
	        if (cells.hasOwnProperty(i) && cells[i].contains(item.getCheckbox()) && (cells[i] === target || cells[i].contains(target))) {
	          return true;
	        }
	      }

	      return false;
	    }
	  }, {
	    key: "activateInlineEdit",
	    value: function activateInlineEdit(item) {
	      item.select();
	      item.edit();
	      this.enableBarcodeEditor(item);
	      this.addSkuListCreationItem(item.getNode());
	    }
	  }, {
	    key: "deactivateInlineEdit",
	    value: function deactivateInlineEdit(item) {
	      var _this9 = this;

	      item.editCancel();
	      item.unselect(); // disable multi-selection(and self re-selection) while disabling editing

	      this.getGrid().clickPrevent = true;
	      setTimeout(function () {
	        _this9.getGrid().clickPrevent = false;
	      }, 100);
	    }
	  }, {
	    key: "enableBarcodeEditor",
	    value: function enableBarcodeEditor(item) {
	      var _item$getCellById;

	      var barcodeNode = (_item$getCellById = item.getCellById('SKU_GRID_BARCODE')) === null || _item$getCellById === void 0 ? void 0 : _item$getCellById.querySelector('[data-role="barcode-selector"]');

	      if (barcodeNode) {
	        var _item$editData;

	        barcodeNode.innerHTML = '';
	        var inputWrapper = main_core.Tag.render(_templateObject9 || (_templateObject9 = babelHelpers.taggedTemplateLiteral(["<div style=\"display: none\"></div>"])));
	        main_core.Dom.append(inputWrapper, barcodeNode);
	        var barcodes = (_item$editData = item.editData) === null || _item$editData === void 0 ? void 0 : _item$editData.SKU_GRID_BARCODE_VALUES;
	        var items = [];

	        if (main_core.Type.isArray(barcodes)) {
	          barcodes.forEach(function (barcode) {
	            var id = main_core.Text.toNumber(barcode.ID);
	            var title = barcode.BARCODE;
	            items.push({
	              entityId: 'productBarcode',
	              id: id,
	              title: title
	            });
	            var input = main_core.Tag.render(_templateObject10 || (_templateObject10 = babelHelpers.taggedTemplateLiteral(["<input type=\"hidden\">"])));
	            input.name = id;
	            input.value = title;
	            inputWrapper.appendChild(input);
	          });
	        }

	        var createBarcode = function createBarcode(event) {
	          if (blurThrottle) {
	            clearTimeout(blurThrottle);
	          }

	          var selector = event.getTarget();
	          var value = selector.getTextBoxValue();
	          value.split(' ').forEach(function (title) {
	            if (!main_core.Type.isStringFilled(title)) {
	              return;
	            }

	            var id = main_core.Text.getRandom();
	            selector.addTag({
	              id: id,
	              title: title,
	              entityId: 'productBarcode'
	            });
	            var input = main_core.Tag.render(_templateObject11 || (_templateObject11 = babelHelpers.taggedTemplateLiteral(["<input type=\"hidden\">"])));
	            input.name = id;
	            input.value = title;
	            inputWrapper.appendChild(input);
	          });
	          hideBarcodeInput();
	        };

	        var blurThrottle = null;

	        var hideBarcodeInput = function hideBarcodeInput() {
	          tagSelector.hideCreateButton();
	          tagSelector.clearTextBox();
	          tagSelector.showAddButton();
	          tagSelector.hideTextBox();
	        };

	        var tagSelector = new ui_entitySelector.TagSelector({
	          placeholder: main_core.Loc.getMessage('C_PVG_STORE_CREATE_BARCODE_PLACEHOLDER'),
	          addButtonCaption: main_core.Loc.getMessage('C_PVG_STORE_ADD_NEW_BARCODE'),
	          addButtonCaptionMore: main_core.Loc.getMessage('C_PVG_STORE_ADD_ONE_MORE_BARCODE'),
	          items: items,
	          events: {
	            onAddButtonClick: function onAddButtonClick(event) {
	              var selector = event.getTarget();
	              selector.showCreateButton();
	            },
	            onBeforeTagRemove: function onBeforeTagRemove(event) {
	              var _data$tag;

	              var data = event.getData();
	              var barcodeId = (_data$tag = data.tag) === null || _data$tag === void 0 ? void 0 : _data$tag.id;

	              if (!main_core.Type.isNil(barcodeId)) {
	                var name = 'input[name="' + barcodeId + '"]';
	                var input = inputWrapper.querySelector(name);

	                if (input) {
	                  input.parentNode.removeChild(input);
	                }
	              }
	            },
	            onCreateButtonClick: createBarcode,
	            onEnter: createBarcode,
	            onMetaEnter: createBarcode,
	            onBlur: function onBlur() {
	              blurThrottle = setTimeout(hideBarcodeInput, 300);
	            }
	          }
	        });
	        tagSelector.renderTo(barcodeNode);
	      }
	    }
	  }, {
	    key: "modifyCustomSkuProperties",
	    value: function modifyCustomSkuProperties(node) {
	      var postfix = '_' + node.getAttribute('data-id');
	      node.querySelectorAll('input[type="radio"]').forEach(function (input) {
	        input.id += postfix;
	        input.setAttribute('name', input.getAttribute('name') + postfix);
	      });
	      node.querySelectorAll('label[data-role]').forEach(function (label) {
	        label.setAttribute('for', label.getAttribute('for') + postfix);
	      });
	    }
	  }, {
	    key: "addSkuListCreationItem",
	    value: function addSkuListCreationItem(node) {
	      node.querySelectorAll('[data-role="dropdownContent"] ul').forEach(function (listNode) {
	        if (!listNode.querySelector('[data-role="createItem"]')) {
	          var propertyId = listNode.getAttribute('data-propertyId');
	          var createItem = main_core.Tag.render(_templateObject12 || (_templateObject12 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t\t<li data-role=\"createItem\"\n\t\t\t\t\t\t class=\"catalog-productcard-popup-select-item catalog-productcard-popup-select-item-new\"\n\t\t\t\t\t\t onclick=\"BX.Catalog.VariationGrid.firePropertyModification(", ")\">\n\t\t\t\t\t\t<label class=\"catalog-productcard-popup-select-label\">\n\t\t\t\t\t\t\t<span class=\"catalog-productcard-popup-select-add\"></span>\n\t\t\t\t\t\t\t<span class=\"catalog-productcard-popup-select-text\">\n\t\t\t\t\t\t\t\t", "\n\t\t\t\t\t\t\t</span>\n\t\t\t\t\t\t</label>\n\t\t\t\t\t</li>"])), propertyId, main_core.Loc.getMessage('C_PVG_ADD_NEW_PROPERTY_VALUE_BUTTON'));
	          listNode.appendChild(createItem);
	        }
	      });
	    }
	  }, {
	    key: "addRowToGrid",
	    value: function addRowToGrid() {
	      var originalTemplate = this.redefineTemplateEditData();
	      var grid = this.getGrid();
	      var newRow = grid.prependRowEditor();
	      this.disableCheckbox(newRow);
	      var newNode = newRow.getNode();
	      grid.getRows().reset();

	      if (main_core.Type.isDomNode(newNode)) {
	        var newRowDataId = main_core.Text.getRandom();
	        this.gridEditData[newRowDataId] = _objectSpread({}, this.gridEditData['template_0']);
	        newNode.setAttribute('data-id', newRowDataId);
	        this.markNodeAsNew(newNode);
	        this.modifyCustomSkuProperties(newNode);
	        this.addSkuListCreationItem(newNode);
	        this.setDeleteButton(newNode);
	        this.enableBarcodeEditor(newRow);
	        newRow.makeCountable();
	      }

	      if (originalTemplate) {
	        this.setOriginalTemplateEditData(originalTemplate);
	      }

	      main_core_events.EventEmitter.emit('Grid::thereEditedRows', []);
	      grid.adjustRows();
	      grid.updateCounterDisplayed();
	      grid.updateCounterSelected();
	      this.updateCounterTotal();
	    }
	  }, {
	    key: "updateCounterTotal",
	    value: function updateCounterTotal() {
	      var grid = this.getGrid();
	      var counterTotalTextContainer = grid.getCounterTotal().querySelector('.main-grid-panel-content-text');
	      counterTotalTextContainer.textContent = grid.getRows().getCountDisplayed();
	    }
	  }, {
	    key: "setDeleteButton",
	    value: function setDeleteButton(row) {
	      var _row$dataset;

	      var actionCellContentContainer = row.querySelector('.main-grid-cell-action .main-grid-cell-content');
	      var rowId = row === null || row === void 0 ? void 0 : (_row$dataset = row.dataset) === null || _row$dataset === void 0 ? void 0 : _row$dataset.id;

	      if (rowId) {
	        var deleteButton = main_core.Tag.render(_templateObject13 || (_templateObject13 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<span\n\t\t\t\t\tclass=\"main-grid-delete-button\"\n\t\t\t\t\tonclick=\"", "\"\n\t\t\t\t></span>\n\t\t\t"])), this.removeNewRowFromGrid.bind(this, rowId));
	        main_core.Dom.append(deleteButton, actionCellContentContainer);
	      }
	    }
	  }, {
	    key: "removeNewRowFromGrid",
	    value: function removeNewRowFromGrid(rowId) {
	      if (!main_core.Type.isStringFilled(rowId)) {
	        return;
	      }

	      var gridRow = this.getGrid().getRows().getById(rowId);

	      if (gridRow) {
	        main_core.Dom.remove(gridRow.getNode());
	        this.getGrid().getRows().reset();
	        this.getGrid().updateCounterDisplayed();
	        this.getGrid().updateCounterSelected();
	        this.updateCounterTotal();
	        this.emitEditedRowsEvent();
	      }
	    }
	  }, {
	    key: "removeRowFromGrid",
	    value: function removeRowFromGrid(skuId) {
	      var data = {
	        'id': skuId,
	        'action': 'deleteRow'
	      };
	      this.getGrid().reloadTable('POST', data);
	    }
	  }, {
	    key: "getGridEditData",
	    value: function getGridEditData() {
	      return this.getGrid().arParams.EDITABLE_DATA;
	    } // rewrite edit data because of grid component cuts necessary fields (VIEW_HTML/EDIT_HTML)

	  }, {
	    key: "setGridEditData",
	    value: function setGridEditData(data) {
	      this.getGrid().arParams.EDITABLE_DATA = data;
	    }
	  }, {
	    key: "setOriginalTemplateEditData",
	    value: function setOriginalTemplateEditData(data) {
	      this.getGrid().arParams.EDITABLE_DATA[GRID_TEMPLATE_ROW] = data;
	    }
	  }, {
	    key: "redefineTemplateEditData",
	    value: function redefineTemplateEditData() {
	      var newRowData = this.getEditDataFromSelectedValues();

	      if (!newRowData) {
	        newRowData = this.getEditDataFromNotSelectedValues();
	      }

	      if (newRowData) {
	        newRowData = _objectSpread({}, newRowData);
	        this.prepareNewRowData(newRowData);
	        var data = this.getGridEditData();
	        var originalTemplateData = data[GRID_TEMPLATE_ROW];
	        var customEditData = this.prepareCustomEditData(originalTemplateData);
	        this.setOriginalTemplateEditData(_objectSpread(_objectSpread(_objectSpread({}, originalTemplateData), newRowData), customEditData));
	        return originalTemplateData;
	      }

	      return null;
	    }
	  }, {
	    key: "getEditDataFromSelectedValues",
	    value: function getEditDataFromSelectedValues() {
	      var rowNodes = this.getGrid().getRows().getSelected();
	      return rowNodes.length ? rowNodes[0].editGetValues() : null;
	    }
	  }, {
	    key: "getEditDataFromNotSelectedValues",
	    value: function getEditDataFromNotSelectedValues() {
	      var values = this.getGrid().arParams.EDITABLE_DATA;
	      var id = Object.keys(values).reverse().find(function (index) {
	        return index !== GRID_TEMPLATE_ROW && main_core.Type.isPlainObject(values[index]);
	      });
	      return id ? values[id] : null;
	    }
	  }, {
	    key: "prepareNewRowData",
	    value: function prepareNewRowData(newRowData) {
	      for (var i in newRowData) {
	        if (newRowData.hasOwnProperty(i) && (i.includes('[VIEW_HTML]') || i.includes('[EDIT_HTML]'))) {
	          delete newRowData[i];
	        }
	      }

	      newRowData['SKU_GRID_BARCODE'] = '<div data-role="barcode-selector"></div>';
	    }
	  }, {
	    key: "prepareCustomEditData",
	    value: function prepareCustomEditData(originalEditData) {
	      var customEditData = {};

	      for (var i in originalEditData) {
	        if (originalEditData.hasOwnProperty(i) && i.includes('[EDIT_HTML]')) {
	          // modify file input instance ids (due to collisions with default id)
	          if (originalEditData[i].indexOf('new BX.UI.ImageInput') >= 0) {
	            var filePrefix = 'bx_file_' + i.replace('[EDIT_HTML]', '').toLowerCase() + '_';
	            var matches = originalEditData[i].match(new RegExp('\'(' + filePrefix + '[A-Za-z0-9_]+)\''));

	            if (matches[1]) {
	              customEditData[i] = originalEditData[i].replace(new RegExp(matches[1], 'g'), filePrefix + this.getRandomInt());
	            }
	          }
	        }
	      }

	      return customEditData;
	    }
	  }, {
	    key: "getRandomInt",
	    value: function getRandomInt() {
	      var max = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : 100000;
	      return Math.floor(Math.random() * Math.floor(max));
	    }
	  }, {
	    key: "getHeaderNames",
	    value: function getHeaderNames() {
	      var headers = [];
	      var cells = this.getGrid().getRows().getHeadFirstChild().getCells();
	      Array.from(cells).forEach(function (header) {
	        if ('name' in header.dataset) {
	          headers.push(header.dataset.name);
	        }
	      });
	      return headers;
	    }
	  }, {
	    key: "addPropertyToGridHeader",
	    value: function addPropertyToGridHeader(item) {
	      var _this10 = this;

	      BX.ajax.runComponentAction('bitrix:catalog.productcard.variation.grid', 'addPropertyHeader', {
	        mode: 'ajax',
	        data: {
	          gridId: this.getGrid().getId(),
	          propertyCode: item.id,
	          anchor: item.anchor || null,
	          currentHeaders: this.getHeaderNames()
	        }
	      }).then(function (response) {
	        _this10.reloadGrid();
	      });
	    }
	  }, {
	    key: "reloadGrid",
	    value: function reloadGrid() {
	      this.getGrid().reload();
	    }
	  }, {
	    key: "onGridUpdated",
	    value: function onGridUpdated(event) {
	      var _this11 = this;

	      this.getGrid().getSettingsWindow().getItems().forEach(function (column) {
	        if (_this11.getHeaderNames().indexOf(column.node.dataset.name) !== -1) {
	          column.state.selected = true;
	          column.checkbox.checked = true;
	        } else {
	          column.state.selected = false;
	          column.checkbox.checked = false;
	        }
	      });
	    }
	  }, {
	    key: "onPropertySave",
	    value: function onPropertySave(event) {
	      var _event$getCompatData = event.getCompatData(),
	          _event$getCompatData2 = babelHelpers.slicedToArray(_event$getCompatData, 1),
	          sliderEvent = _event$getCompatData2[0];

	      if (sliderEvent.getEventId() === 'PropertyCreationForm:onAdd') {
	        var eventArgs = sliderEvent.getData();
	        this.addPropertyToGridHeader({
	          id: eventArgs.fields.CODE
	        });
	      }

	      if (sliderEvent.getEventId() === 'PropertyCreationForm:onModify') {
	        this.reloadGrid();
	      }
	    }
	  }, {
	    key: "showPropertySettingsSlider",
	    value: function showPropertySettingsSlider(event) {
	      var _event$getData5 = event.getData(),
	          _event$getData6 = babelHelpers.slicedToArray(_event$getData5, 1),
	          propertyId = _event$getData6[0];

	      var link = this.modifyPropertyLink.replace('#PROPERTY_ID#', propertyId);
	      BX.SidePanel.Instance.open(link, {
	        width: 550,
	        allowChangeHistory: false,
	        cacheable: false
	      });
	    }
	  }, {
	    key: "isGridInEditMode",
	    value: function isGridInEditMode() {
	      return this.getGrid().getRows().getBodyChild().filter(function (row) {
	        return row.isShown() && row.isEdit();
	      }).length > 0;
	    }
	  }], [{
	    key: "firePropertyModification",
	    value: function firePropertyModification(propertyId, menuId) {
	      if (menuId) {
	        var menu = main_popup.MenuManager.getMenuById(menuId);

	        if (menu) {
	          menu.close();
	          menu.destroy();
	        }
	      } else {
	        var popup = main_popup.PopupManager.getCurrentPopup();

	        if (popup) {
	          popup.close();
	        }
	      }

	      main_core_events.EventEmitter.emit('VariationGrid::propertyModify', [propertyId]);
	    }
	  }]);
	  return VariationGrid;
	}();

	function _getSimpleProductRestrictionContent() {
	  var text = main_core.Loc.getMessage('C_PVG_SIMPLE_PRODUCT_POPUP_TEXT', {
	    '#COPY_BUTTON_NAME#': "<b>".concat(main_core.Loc.getMessage('C_PVG_SIMPLE_PRODUCT_POPUP_BUTTON_COPY'), "</b>"),
	    '#LINK_INFO#': "<a href=\"\">".concat(main_core.Loc.getMessage('C_PVG_SIMPLE_PRODUCT_POPUP_DOC_LINK_INFO'), "</a>")
	  });
	  var content = main_core.Tag.render(_templateObject14 || (_templateObject14 = babelHelpers.taggedTemplateLiteral(["<span>", "</span>"])), text);
	  main_core.Event.bind(content.querySelector('a'), 'click', function (event) {
	    top.BX.Helper.show("redirect=detail&code=16172654");
	    event.preventDefault();
	  });
	  return main_core.Tag.render(_templateObject15 || (_templateObject15 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<div class=\"catalog-simple-popup-wrapper\">\n\t\t\t\t<h3>", "</h3>\n\t\t\t\t<div class=\"catalog-simple-popup-label-text\">", "</div>\n\t\t\t\t<div class=\"catalog-simple-popup-link-block\">\n\t\t\t\t\t<a class=\"ui-link ui-link-primary \" target=\"_blank\" href=\"\">\n\t\t\t\t\t</a>\n\t\t\t\t</div>\n\t\t\t</div>\n\t\t"])), main_core.Loc.getMessage('C_PVG_SIMPLE_PRODUCT_POPUP_TITLE'), content);
	}

	main_core.Reflection.namespace('BX.Catalog').VariationGrid = VariationGrid;

}((this.window = this.window || {}),BX,BX.Event,BX.Main,BX.UI.Dialogs,BX.UI.EntitySelector));
//# sourceMappingURL=script.js.map
