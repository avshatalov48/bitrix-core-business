this.BX = this.BX || {};
this.BX.Sale = this.BX.Sale || {};
(function (exports,main_core) {
	'use strict';

	/**
	 * Check if barcode exist
	 */
	var Checker = /*#__PURE__*/function () {
	  function Checker() {
	    babelHelpers.classCallCheck(this, Checker);
	  }

	  babelHelpers.createClass(Checker, null, [{
	    key: "isBarcodeExist",

	    /**
	     * @param {string} barcode
	     * @param {integer} basketId
	     * @param {integer} orderId
	     * @param {integer} storeId
	     * @returns {Promise<T>}
	     */
	    value: function isBarcodeExist(barcode, basketId, orderId, storeId) {
	      return BX.ajax.runAction('sale.barcode.isBarcodeExist', {
	        data: {
	          barcode: barcode,
	          basketId: basketId,
	          orderId: orderId,
	          storeId: storeId
	        }
	      }).then( // Success
	      function (response) {
	        if (response.data && typeof response.data.RESULT !== 'undefined') {
	          return response.data.RESULT;
	        }

	        throw new Error('Result is unknown');
	      });
	    }
	  }]);
	  return Checker;
	}();

	function _templateObject2() {
	  var data = babelHelpers.taggedTemplateLiteral(["<div class=\"sale-order-shipment-barcode\">", "</div>"]);

	  _templateObject2 = function _templateObject2() {
	    return data;
	  };

	  return data;
	}

	function _templateObject() {
	  var data = babelHelpers.taggedTemplateLiteral(["<input type=\"text\" onchange=\"", "\"", ">"]);

	  _templateObject = function _templateObject() {
	    return data;
	  };

	  return data;
	}

	var Barcode = /*#__PURE__*/function () {
	  function Barcode(props) {
	    babelHelpers.classCallCheck(this, Barcode);
	    this._id = props.id || 0;
	    this._value = props.value || '';
	    this._readonly = props.readonly;
	    this._node = null;
	    this._inputNode = null;
	    this._isExist = null;
	    this._eventEmitter = new main_core.Event.EventEmitter();
	  }

	  babelHelpers.createClass(Barcode, [{
	    key: "render",
	    value: function render() {
	      var readonly = this._readonly ? ' readonly="readonly"' : '';
	      this._inputNode = main_core.Tag.render(_templateObject(), this.onChange.bind(this), readonly);
	      this._inputNode.value = this._value;
	      this._node = main_core.Tag.render(_templateObject2(), this._inputNode);
	      return this._node;
	    }
	  }, {
	    key: "onChange",
	    value: function onChange() {
	      this._value = this._inputNode.value;

	      this._eventEmitter.emit('onChange', this);
	    }
	  }, {
	    key: "onChangeSubscribe",
	    value: function onChangeSubscribe(callback) {
	      this._eventEmitter.subscribe('onChange', callback);
	    }
	  }, {
	    key: "showExistence",
	    value: function showExistence(isExist) {
	      if (isExist === false) {
	        this._node.classList.remove("exists");

	        this._node.classList.add("not-exists");
	      } else if (isExist === true) {
	        this._node.classList.remove("not-exists");

	        this._node.classList.add("exists");
	      } else if (isExist === null) {
	        this._node.classList.remove("not-exists");

	        this._node.classList.remove("exists");
	      }
	    }
	  }, {
	    key: "id",
	    get: function get() {
	      return this._id;
	    }
	  }, {
	    key: "value",
	    get: function get() {
	      return this._value;
	    },
	    set: function set(value) {
	      this._value = value;
	      this._inputNode.value = value;
	    }
	  }, {
	    key: "isExist",
	    set: function set(isExist) {
	      this._isExist = isExist;
	      this.showExistence(isExist);
	    },
	    get: function get() {
	      return this._isExist;
	    }
	  }]);
	  return Barcode;
	}();

	function _templateObject$1() {
	  var data = babelHelpers.taggedTemplateLiteral(["<input type=\"text\" ", ">"]);

	  _templateObject$1 = function _templateObject() {
	    return data;
	  };

	  return data;
	}

	var Markingcode = /*#__PURE__*/function () {
	  function Markingcode(props) {
	    babelHelpers.classCallCheck(this, Markingcode);
	    this._id = props.id || 0;
	    this._input = null;
	    this._value = props.value || '';
	    this._readonly = props.readonly;
	    this._eventEmitter = new main_core.Event.EventEmitter();
	  }

	  babelHelpers.createClass(Markingcode, [{
	    key: "render",
	    value: function render() {
	      var readonly = this._readonly ? ' readonly="readonly"' : '';
	      this._input = main_core.Tag.render(_templateObject$1(), readonly);
	      this._input.value = this._value;
	      main_core.Event.bind(this._input, 'keypress', this.onKeyPress.bind(this));
	      main_core.Event.bind(this._input, 'change', this.onChange.bind(this));
	      return this._input;
	    }
	  }, {
	    key: "onChange",
	    value: function onChange(e) {
	      this._value = e.target.value;

	      this._eventEmitter.emit('onChange', this);
	    }
	  }, {
	    key: "onKeyPress",
	    value: function onKeyPress(e) {
	      /**
	       * @see https://stackoverflow.com/questions/48296955/ascii-control-character-html-input-text
	       */
	      if (e.charCode === 29) {
	        this._input.value += String.fromCharCode(e.which);
	      }
	    }
	  }, {
	    key: "onChangeSubscribe",
	    value: function onChangeSubscribe(callback) {
	      this._eventEmitter.subscribe('onChange', callback);
	    }
	  }, {
	    key: "id",
	    get: function get() {
	      return this._id;
	    }
	  }, {
	    key: "value",
	    get: function get() {
	      return this._value;
	    }
	  }]);
	  return Markingcode;
	}();

	function _templateObject$2() {
	  var data = babelHelpers.taggedTemplateLiteral(["<table></table>"]);

	  _templateObject$2 = function _templateObject() {
	    return data;
	  };

	  return data;
	}

	var Widget = /*#__PURE__*/function () {
	  function Widget(props) {
	    babelHelpers.classCallCheck(this, Widget);
	    this._headData = props.headData;
	    this._orderId = props.orderId;
	    this._basketId = props.basketId;
	    this._storeId = props.storeId;
	    this._isBarcodeMulti = props.isBarcodeMulti;
	    this._readonly = props.readonly;
	    this._items = this.createItems(props.rowData, props.rowsCount);
	    this._eventEmitter = new main_core.Event.EventEmitter();
	  }

	  babelHelpers.createClass(Widget, [{
	    key: "createItems",
	    value: function createItems(data, count) {
	      var _this = this;

	      var items = [];
	      data.forEach(function (rowData) {
	        items.push(_this.createItemsRow(rowData));
	      });

	      if (data.length < count) {
	        for (var i = 0, l = count - data.length; i < l; i++) {
	          items.push(this.createEmptyRow());
	        }
	      }

	      return items;
	    }
	  }, {
	    key: "createEmptyRow",
	    value: function createEmptyRow() {
	      var result = {
	        id: 0
	      };

	      if (this.isBarcodeNeeded()) {
	        var barcodeItem = new Barcode({});
	        barcodeItem.onChangeSubscribe(this.onBarcodeItemChange.bind(this));
	        result[Widget.COLUMN_TYPE_BARCODE] = barcodeItem;
	      }

	      if (this.isMarkingCodeNeeded) {
	        var markingCodeItem = new Markingcode({});
	        markingCodeItem.onChangeSubscribe(this.onMarkingCodeItemChange.bind(this));
	        result[Widget.COLUMN_TYPE_MARKING_CODE] = markingCodeItem;
	      }

	      return result;
	    }
	  }, {
	    key: "onBarcodeItemChange",
	    value: function onBarcodeItemChange(event) {
	      var _this2 = this;

	      var barcodeValue;

	      if (typeof event.data.value === "string") {
	        barcodeValue = event.data.value;
	      } else {
	        barcodeValue = event.data.value.value;
	      }

	      this.isBarcodeExist(barcodeValue).then(function (result) {
	        var barcodeItem = {
	          isExist: result,
	          value: barcodeValue
	        };

	        if (!_this2._isBarcodeMulti) {
	          _this2.synchronizeBarcodes(barcodeItem.value, barcodeItem.isExist);
	        }

	        _this2.onChange();
	      }).catch(function (data) {
	        BX.debug(data);
	      });
	    }
	  }, {
	    key: "onMarkingCodeItemChange",
	    value: function onMarkingCodeItemChange() {
	      this.onChange();
	    }
	  }, {
	    key: "onChange",
	    value: function onChange() {
	      this._eventEmitter.emit('onChange', this);
	    }
	  }, {
	    key: "onChangeSubscribe",
	    value: function onChangeSubscribe(callback) {
	      this._eventEmitter.subscribe('onChange', callback);
	    }
	  }, {
	    key: "synchronizeBarcodes",
	    value: function synchronizeBarcodes(value, isExist) {
	      this._items.forEach(function (item) {
	        if (item[Widget.COLUMN_TYPE_BARCODE]) {
	          item[Widget.COLUMN_TYPE_BARCODE].value = value;
	          item[Widget.COLUMN_TYPE_BARCODE].isExist = isExist;
	        }
	      });
	    }
	  }, {
	    key: "isBarcodeExist",
	    value: function isBarcodeExist(barcode) {
	      if (barcode) {
	        var storeId = this._isBarcodeMulti ? this.storeId : 0;
	        return BX.Sale.Barcode.Checker.isBarcodeExist(barcode, this.basketId, this.orderId, storeId);
	      } else {
	        return new Promise(function (resolve) {
	          resolve(null);
	        });
	      }
	    }
	  }, {
	    key: "createItemsRow",
	    value: function createItemsRow(rowData) {
	      var result = {
	        id: rowData.id
	      };

	      if (this.isBarcodeNeeded()) {
	        var barcodeItem = new Barcode({
	          id: rowData.id,
	          value: rowData.barcode,
	          widget: this,
	          readonly: this._readonly
	        });
	        barcodeItem.onChangeSubscribe(this.onBarcodeItemChange.bind(this));
	        result[Widget.COLUMN_TYPE_BARCODE] = barcodeItem;
	      }

	      if (this.isMarkingCodeNeeded()) {
	        var markingCodeItem = new Markingcode({
	          id: rowData.id,
	          value: rowData.markingCode,
	          readonly: this._readonly
	        });
	        markingCodeItem.onChangeSubscribe(this.onMarkingCodeItemChange.bind(this));
	        result[Widget.COLUMN_TYPE_MARKING_CODE] = markingCodeItem;
	      }

	      return result;
	    }
	  }, {
	    key: "isBarcodeNeeded",
	    value: function isBarcodeNeeded() {
	      return typeof this._headData[Widget.COLUMN_TYPE_BARCODE] !== 'undefined';
	    }
	  }, {
	    key: "isMarkingCodeNeeded",
	    value: function isMarkingCodeNeeded() {
	      return typeof this._headData[Widget.COLUMN_TYPE_MARKING_CODE] !== 'undefined';
	    }
	  }, {
	    key: "createTh",
	    value: function createTh(type) {
	      var th = document.createElement('th');
	      th.innerHTML = this._headData[type].title;
	      return th;
	    }
	  }, {
	    key: "render",
	    value: function render() {
	      var _this3 = this;

	      var tableNode = main_core.Tag.render(_templateObject$2());
	      var headRow = tableNode.insertRow();

	      if (this.isBarcodeNeeded()) {
	        headRow.appendChild(this.createTh(Widget.COLUMN_TYPE_BARCODE));
	      }

	      if (this.isMarkingCodeNeeded()) {
	        headRow.appendChild(this.createTh(Widget.COLUMN_TYPE_MARKING_CODE));
	      }

	      this._items.forEach(function (row) {
	        var tableRow = tableNode.insertRow(-1);

	        if (_this3.isBarcodeNeeded()) {
	          var cell = tableRow.insertCell();
	          cell.appendChild(row[Widget.COLUMN_TYPE_BARCODE].render());
	        }

	        if (_this3.isMarkingCodeNeeded()) {
	          var _cell = tableRow.insertCell();

	          _cell.appendChild(row[Widget.COLUMN_TYPE_MARKING_CODE].render());
	        }
	      });

	      return tableNode;
	    }
	  }, {
	    key: "getItemsData",
	    value: function getItemsData() {
	      var result = [];

	      this._items.forEach(function (item) {
	        result.push({
	          id: item.id,
	          barcode: {
	            value: item[Widget.COLUMN_TYPE_BARCODE] ? item[Widget.COLUMN_TYPE_BARCODE].value : '',
	            isExist: item[Widget.COLUMN_TYPE_BARCODE] ? item[Widget.COLUMN_TYPE_BARCODE].isExist : false
	          },
	          markingCode: {
	            value: item[Widget.COLUMN_TYPE_MARKING_CODE] ? item[Widget.COLUMN_TYPE_MARKING_CODE].value : ''
	          }
	        });
	      });

	      return result;
	    }
	  }, {
	    key: "orderId",
	    get: function get() {
	      return this._orderId;
	    }
	  }, {
	    key: "basketId",
	    get: function get() {
	      return this._basketId;
	    }
	  }, {
	    key: "storeId",
	    get: function get() {
	      return this._storeId;
	    }
	  }]);
	  return Widget;
	}();

	babelHelpers.defineProperty(Widget, "COLUMN_TYPE_BARCODE", 'barcode');
	babelHelpers.defineProperty(Widget, "COLUMN_TYPE_MARKING_CODE", 'markingCode');

	exports.Checker = Checker;
	exports.Widget = Widget;

}((this.BX.Sale.Barcode = this.BX.Sale.Barcode || {}),BX));
//# sourceMappingURL=barcode.bundle.js.map
