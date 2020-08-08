this.BX = this.BX || {};
this.BX.Sale = this.BX.Sale || {};
this.BX.Sale.Admin = this.BX.Sale.Admin || {};
(function (exports,sale_barcode,main_core) {
	'use strict';

	var WidgetFabric = /*#__PURE__*/function () {
	  function WidgetFabric() {
	    babelHelpers.classCallCheck(this, WidgetFabric);
	  }

	  babelHelpers.createClass(WidgetFabric, null, [{
	    key: "createWidget",
	    value: function createWidget(props) {
	      var items = props.items.slice(0, props.rowsCount);
	      return new sale_barcode.Widget({
	        rowData: WidgetFabric._createBarcodeWidgetRows(items, props.isSupportedMarkingCode),
	        headData: WidgetFabric._createBarcodeWidgetHead(props.isSupportedMarkingCode, props.useStoreControl),
	        rowsCount: props.rowsCount,
	        orderId: props.orderId,
	        basketId: props.basketId,
	        storeId: props.storeId,
	        isBarcodeMulti: props.isBarcodeMulti,
	        readonly: props.readonly
	      });
	    }
	  }, {
	    key: "_createBarcodeWidgetHead",
	    value: function _createBarcodeWidgetHead(isSupportedMarkingCode, useStoreControl) {
	      var result = {};

	      if (useStoreControl) {
	        result['barcode'] = {
	          title: BX.message('SALE_JS_ADMIN_ORDER_CONF_BARCODE')
	        };
	      }

	      if (isSupportedMarkingCode) {
	        result['markingCode'] = {
	          title: BX.message('SALE_JS_ADMIN_ORDER_CONF_MARKING_CODE')
	        };
	      }

	      return result;
	    }
	  }, {
	    key: "_createBarcodeWidgetRows",
	    value: function _createBarcodeWidgetRows(items, isSupportedMarkingCode) {
	      var result = [];
	      items.forEach(function (item) {
	        var itemData = {
	          id: item.id
	        };
	        itemData.barcode = item.barcode;

	        if (isSupportedMarkingCode) {
	          itemData.markingCode = item.markingCode;
	        }

	        result.push(itemData);
	      });
	      return result;
	    }
	  }]);
	  return WidgetFabric;
	}();

	function _templateObject2() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<div class=\"sale-shipment-basket-barcodes-dialog\">\n\t\t\t\t<div class=\"sale-shipment-basket-barcodes-dialog-product-name\">", "</div>\n\t\t\t\t", "\n\t\t\t\t", "\n\t\t\t</div>"]);

	  _templateObject2 = function _templateObject2() {
	    return data;
	  };

	  return data;
	}

	function _templateObject() {
	  var data = babelHelpers.taggedTemplateLiteral(["<div class=\"sale-shipment-basket-barcodes-dialog-store-name\">", "</div>"]);

	  _templateObject = function _templateObject() {
	    return data;
	  };

	  return data;
	}

	var Dialog = /*#__PURE__*/function () {
	  function Dialog(props) {
	    babelHelpers.classCallCheck(this, Dialog);
	    this._onClose = props.onClose || null;
	    this._columnsCount = props.columnsCount;
	    this._dialog = this._create(props.widget, props.productName, props.storeName);
	  }

	  babelHelpers.createClass(Dialog, [{
	    key: "show",
	    value: function show() {
	      this._dialog.Show();

	      this._dialog.adjustSizeEx();
	    }
	  }, {
	    key: "_getWidth",
	    value: function _getWidth() {
	      return this._columnsCount === 1 ? 280 : 400;
	    }
	  }, {
	    key: "_createStoreRow",
	    value: function _createStoreRow(storeName) {
	      var result = '';

	      if (storeName.length > 0) {
	        result = main_core.Tag.render(_templateObject(), BX.util.htmlspecialchars(storeName));
	      }

	      return result;
	    }
	  }, {
	    key: "_create",
	    value: function _create(widget, productName, storeName) {
	      var _this = this;

	      var content = main_core.Tag.render(_templateObject2(), BX.util.htmlspecialchars(productName), this._createStoreRow(storeName), widget.render());
	      var dialog = new BX.CDialog({
	        'content': content,
	        'title': BX.message('SALE_JS_ADMIN_ORDER_CONF_INPUT_BARCODES'),
	        'width': this._getWidth(),
	        'height': 400,
	        'resizable': false,
	        'buttons': [new BX.CWindowButton({
	          'title': BX.message('SALE_JS_ADMIN_ORDER_CONF_CLOSE'),
	          'action': function action() {
	            if (_this._onClose) {
	              _this._onClose(widget);
	            }

	            BX.WindowManager.Get().Close();
	          },
	          className: 'btnCloseBarcodeDialog'
	        })]
	      }); //fully remove dialog and content after it will be closed

	      BX.addCustomEvent(dialog, 'onWindowClose', function (dialog) {
	        dialog.DIV.parentNode.removeChild(dialog.DIV);
	      });
	      return dialog;
	    }
	  }]);
	  return Dialog;
	}();

	function _templateObject6() {
	  var data = babelHelpers.taggedTemplateLiteral(["", ""]);

	  _templateObject6 = function _templateObject6() {
	    return data;
	  };

	  return data;
	}

	function _templateObject5() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t\t<div>\t\t\t\t\t\n\t\t\t\t\t\t", "\n\t\t\t\t\t\t", "\n\t\t\t\t\t\t", "\n\t\t\t\t\t</div>"]);

	  _templateObject5 = function _templateObject5() {
	    return data;
	  };

	  return data;
	}

	function _templateObject4() {
	  var data = babelHelpers.taggedTemplateLiteral(["<span style=\"cursor: pointer; border-bottom: 1px dashed;\" onclick=\"", "\">", "</span>"]);

	  _templateObject4 = function _templateObject4() {
	    return data;
	  };

	  return data;
	}

	function _templateObject3() {
	  var data = babelHelpers.taggedTemplateLiteral(["<input type=\"button\" value=\"", "\" onclick=\"", "\">"]);

	  _templateObject3 = function _templateObject3() {
	    return data;
	  };

	  return data;
	}

	function _templateObject2$1() {
	  var data = babelHelpers.taggedTemplateLiteral(["<div>", "", "</div>"]);

	  _templateObject2$1 = function _templateObject2() {
	    return data;
	  };

	  return data;
	}

	function _templateObject$1() {
	  var data = babelHelpers.taggedTemplateLiteral(["<div></div>"]);

	  _templateObject$1 = function _templateObject() {
	    return data;
	  };

	  return data;
	}

	var BarcodeView = /*#__PURE__*/function () {
	  function BarcodeView(props) {
	    babelHelpers.classCallCheck(this, BarcodeView);
	    this._basketId = props.basketId;
	    this._product = props.product;
	    this._index = props.index;
	    this._orderId = props.orderId;
	    this._type = props.type;
	    this._useStoreControl = props.useStoreControl;
	    this._dataFieldTemplate = props.dataFieldTemplate || '';
	    this._itemNode = null;
	    this._hiddensContainer = null;
	    this._initialStoreId = 0;
	    var barcodeInfo = [];

	    if (this._product.BARCODE_INFO) {
	      var stores = Object.keys(this._product.BARCODE_INFO);
	      this._initialStoreId = stores[this._index - 1];

	      if (this._initialStoreId) {
	        barcodeInfo = this._product.BARCODE_INFO[this._initialStoreId];
	      }
	    }

	    this._items = this._initItems(barcodeInfo);
	  }

	  babelHelpers.createClass(BarcodeView, [{
	    key: "_initItems",
	    value: function _initItems(storeBarcodeInfo) {
	      if (storeBarcodeInfo.length <= 0) {
	        return [];
	      }

	      var result = [];

	      if (this._isSupportedMarkingCode() || this._isBarcodeMulti()) {
	        storeBarcodeInfo.forEach(function (item) {
	          result.push({
	            id: item.ID,
	            barcode: item.BARCODE,
	            markingCode: item.MARKING_CODE
	          });
	        });
	      } else {
	        var item = storeBarcodeInfo[0];
	        result = [{
	          id: item.ID,
	          barcode: item.BARCODE,
	          markingCode: item.MARKING_CODE
	        }];
	      }

	      return result;
	    }
	  }, {
	    key: "render",
	    value: function render() {
	      this._itemNode = this._renderItemNode();
	      this._hiddensContainer = main_core.Tag.render(_templateObject$1());

	      this._renderHiddens();

	      return main_core.Tag.render(_templateObject2$1(), this._itemNode, this._hiddensContainer);
	    }
	  }, {
	    key: "_renderItemNode",
	    value: function _renderItemNode() {
	      var result = null;

	      if (this._type === BarcodeView.TYPE_BUTTON) {
	        result = main_core.Tag.render(_templateObject3(), BX.message('SALE_JS_ADMIN_ORDER_CONF_BARCODES'), this._onClick.bind(this));
	      } else if (this._type === BarcodeView.TYPE_LINK) {
	        result = main_core.Tag.render(_templateObject4(), this._onClick.bind(this), BX.message('SALE_JS_ADMIN_ORDER_CONF_BARCODE'));
	      } else if (this._type === BarcodeView.TYPE_INPUT) {
	        var widget = this._createWidget(1);

	        result = widget.render();
	      } else {
	        throw new Error('Wrong BarcodeView type');
	      }

	      return result;
	    }
	  }, {
	    key: "_getActualBarcodesQuantity",
	    value: function _getActualBarcodesQuantity() {
	      return this._items.length;
	    }
	  }, {
	    key: "_getActualStoreId",
	    value: function _getActualStoreId() {
	      return this._initialStoreId;
	    }
	  }, {
	    key: "_onClick",
	    value: function _onClick() {
	      var dialog = new Dialog({
	        widget: this._createWidget(),
	        productName: this._product.NAME,
	        storeName: this._getStoreName(this._getActualStoreId()),
	        columnsCount: this._getColumnsCount()
	      });
	      dialog.show();
	    }
	  }, {
	    key: "_getColumnsCount",
	    value: function _getColumnsCount() {
	      return this._isSupportedMarkingCode() && this._useStoreControl ? 2 : 1;
	    }
	  }, {
	    key: "_getStoreName",
	    value: function _getStoreName(storeId) {
	      if (this._product.STORES && Array.isArray(this._product.STORES)) {
	        var stores = this._product.STORES;

	        for (var i = 0, l = stores.length; i < l; i++) {
	          if (parseInt(stores[i].STORE_ID) === parseInt(storeId)) {
	            return stores[i].STORE_NAME;
	          }
	        }
	      }

	      return '';
	    }
	  }, {
	    key: "_isBarcodeMulti",
	    value: function _isBarcodeMulti() {
	      return this._product.BARCODE_MULTI === 'Y';
	    }
	  }, {
	    key: "_isSupportedMarkingCode",
	    value: function _isSupportedMarkingCode() {
	      return this._product.IS_SUPPORTED_MARKING_CODE === 'Y';
	    }
	  }, {
	    key: "_createWidget",
	    value: function _createWidget(rowsCount) {
	      return WidgetFabric.createWidget({
	        items: this._items,
	        rowsCount: rowsCount,
	        orderId: this._orderId,
	        basketId: this._basketId,
	        readonly: true,
	        useStoreControl: this._useStoreControl,
	        storeId: this._getActualStoreId(),
	        isBarcodeMulti: this._isBarcodeMulti(),
	        isSupportedMarkingCode: this._isSupportedMarkingCode()
	      });
	    }
	  }, {
	    key: "_renderHiddens",
	    value: function _renderHiddens() {
	      var _this = this;

	      if (!this._dataFieldTemplate) {
	        return;
	      }

	      this._hiddensContainer.innerHTML = '';
	      var iterator = 0;

	      this._items.forEach(function (item) {
	        _this._hiddensContainer.appendChild(main_core.Tag.render(_templateObject5(), _this._createHiddenInput('VALUE', iterator, item.barcode), _this._createHiddenInput('ID', iterator, item.id), _this._createHiddenInput('MARKING_CODE', iterator, item.markingCode)));

	        iterator++;
	      });
	    }
	  }, {
	    key: "_createHiddenInput",
	    value: function _createHiddenInput(dataType, iterator, value) {
	      var strInput = this._dataFieldTemplate.replace('#ITERATOR#', iterator).replace('#DATA_TYPE#', dataType).replace('#DATA_TYPE_LOWER#', dataType.toLowerCase());

	      var input = main_core.Tag.render(_templateObject6(), strInput);
	      input.setAttribute('value', value);
	      return input;
	    }
	  }]);
	  return BarcodeView;
	}();

	babelHelpers.defineProperty(BarcodeView, "TYPE_BUTTON", 'button');
	babelHelpers.defineProperty(BarcodeView, "TYPE_LINK", 'link');
	babelHelpers.defineProperty(BarcodeView, "TYPE_INPUT", 'input');

	var BarcodeEdit = /*#__PURE__*/function (_BarcodeView) {
	  babelHelpers.inherits(BarcodeEdit, _BarcodeView);

	  function BarcodeEdit(props) {
	    var _this;

	    babelHelpers.classCallCheck(this, BarcodeEdit);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(BarcodeEdit).call(this, props));
	    _this._dataFieldTemplate = props.dataFieldTemplate;
	    _this._useStoreControl = props.useStoreControl; //We need some actual information from form fields

	    _this._getActualBarcodeQuantityMethod = props.getActualBarcodeQuantityMethod;
	    _this._getActualStoreIdByIndexMethod = props.getActualStoreIdByIndexMethod;
	    return _this;
	  }

	  babelHelpers.createClass(BarcodeEdit, [{
	    key: "_getActualBarcodesQuantity",
	    value: function _getActualBarcodesQuantity() {
	      var result = 1;

	      if (this._isBarcodeMulti() || this._isSupportedMarkingCode()) {
	        result = this._getActualBarcodeQuantityMethod(this._basketId, this._index);
	      }

	      return result;
	    }
	  }, {
	    key: "_getActualStoreId",
	    value: function _getActualStoreId() {
	      return this._getActualStoreIdByIndexMethod(this._basketId, this._index);
	    }
	  }, {
	    key: "_onClick",
	    value: function _onClick() {
	      var dialog = new Dialog({
	        widget: this._createWidget(this._getActualBarcodesQuantity()),
	        productName: this._product.NAME,
	        storeName: this._getStoreName(this._getActualStoreId()),
	        onClose: this._onDialogClose.bind(this),
	        columnsCount: this._getColumnsCount()
	      });
	      dialog.show();
	    }
	  }, {
	    key: "_createWidget",
	    value: function _createWidget(rowsCount) {
	      var widget = WidgetFabric.createWidget({
	        items: this._items,
	        rowsCount: rowsCount,
	        orderId: this._orderId,
	        basketId: this._basketId,
	        readonly: false,
	        useStoreControl: this._useStoreControl,
	        storeId: this._getActualStoreId(),
	        isBarcodeMulti: this._isBarcodeMulti(),
	        isSupportedMarkingCode: this._isSupportedMarkingCode()
	      });
	      widget.onChangeSubscribe(this._onWidgetChanged.bind(this));
	      return widget;
	    }
	  }, {
	    key: "_onWidgetChanged",
	    value: function _onWidgetChanged(event) {
	      var widget = event.data;

	      this._getWidgetData(widget);
	    }
	  }, {
	    key: "_getWidgetData",
	    value: function _getWidgetData(widget) {
	      var _this2 = this;

	      this._items = [];
	      widget.getItemsData().forEach(function (itemData) {
	        _this2._items.push({
	          id: itemData.id,
	          barcode: itemData.barcode.value,
	          markingCode: itemData.markingCode.value
	        });
	      });

	      this._renderHiddens(this._items);
	    }
	  }, {
	    key: "_onDialogClose",
	    value: function _onDialogClose(widget) {
	      this._getWidgetData(widget);
	    }
	  }]);
	  return BarcodeEdit;
	}(BarcodeView);

	exports.ShipmentBasketBarcodeView = BarcodeView;
	exports.ShipmentBasketBarcodeEdit = BarcodeEdit;

}((this.BX.Sale.Admin.Order = this.BX.Sale.Admin.Order || {}),BX.Sale.Barcode,BX));
//# sourceMappingURL=admin_order.bundle.js.map
