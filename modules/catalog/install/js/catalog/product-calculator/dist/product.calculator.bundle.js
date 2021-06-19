this.BX = this.BX || {};
(function (exports,main_core) {
	'use strict';

	var DiscountType = function DiscountType() {
	  babelHelpers.classCallCheck(this, DiscountType);
	};
	babelHelpers.defineProperty(DiscountType, "UNDEFINED", 0);
	babelHelpers.defineProperty(DiscountType, "MONETARY", 1);
	babelHelpers.defineProperty(DiscountType, "PERCENTAGE", 2);

	var initialFields = {
	  QUANTITY: 1,
	  PRICE: 0,
	  PRICE_EXCLUSIVE: 0,
	  PRICE_NETTO: 0,
	  PRICE_BRUTTO: 0,
	  CUSTOMIZED: 'N',
	  DISCOUNT_TYPE_ID: DiscountType.UNDEFINED,
	  DISCOUNT_RATE: 0,
	  DISCOUNT_SUM: 0,
	  DISCOUNT_ROW: 0,
	  TAX_INCLUDED: 'N',
	  TAX_RATE: 0,
	  TAX_SUM: 0,
	  SUM: 0
	};
	var FieldStorage = /*#__PURE__*/function () {
	  function FieldStorage(fields) {
	    babelHelpers.classCallCheck(this, FieldStorage);
	    this.fields = babelHelpers.objectSpread({}, initialFields);

	    if (main_core.Type.isPlainObject(fields)) {
	      this.fields = babelHelpers.objectSpread({}, this.fields, fields);
	    }
	  }

	  babelHelpers.createClass(FieldStorage, [{
	    key: "getFields",
	    value: function getFields() {
	      return main_core.Runtime.clone(this.fields);
	    }
	  }, {
	    key: "getField",
	    value: function getField(name, defaultValue) {
	      return this.fields.hasOwnProperty(name) ? this.fields[name] : defaultValue;
	    }
	  }, {
	    key: "setField",
	    value: function setField(name, value) {
	      this.fields[name] = value;
	    }
	  }, {
	    key: "getPrice",
	    value: function getPrice() {
	      return this.getField('PRICE', 0);
	    }
	  }, {
	    key: "getPriceExclusive",
	    value: function getPriceExclusive() {
	      return this.getField('PRICE_EXCLUSIVE', 0);
	    }
	  }, {
	    key: "getPriceNetto",
	    value: function getPriceNetto() {
	      return this.getField('PRICE_NETTO', 0);
	    }
	  }, {
	    key: "getPriceBrutto",
	    value: function getPriceBrutto() {
	      return this.getField('PRICE_BRUTTO', 0);
	    }
	  }, {
	    key: "getQuantity",
	    value: function getQuantity() {
	      return this.getField('QUANTITY', 1);
	    }
	  }, {
	    key: "getDiscountType",
	    value: function getDiscountType() {
	      return this.getField('DISCOUNT_TYPE_ID', DiscountType.UNDEFINED);
	    }
	  }, {
	    key: "isDiscountUndefined",
	    value: function isDiscountUndefined() {
	      return this.getDiscountType() === DiscountType.UNDEFINED;
	    }
	  }, {
	    key: "isDiscountPercentage",
	    value: function isDiscountPercentage() {
	      return this.getDiscountType() === DiscountType.PERCENTAGE;
	    }
	  }, {
	    key: "isDiscountMonetary",
	    value: function isDiscountMonetary() {
	      return this.getDiscountType() === DiscountType.MONETARY;
	    }
	  }, {
	    key: "isDiscountHandmade",
	    value: function isDiscountHandmade() {
	      return this.isDiscountPercentage() || this.isDiscountMonetary();
	    }
	  }, {
	    key: "getDiscountRate",
	    value: function getDiscountRate() {
	      return this.getField('DISCOUNT_RATE', 0);
	    }
	  }, {
	    key: "getDiscountSum",
	    value: function getDiscountSum() {
	      return this.getField('DISCOUNT_SUM', 0);
	    }
	  }, {
	    key: "getDiscountRow",
	    value: function getDiscountRow() {
	      return this.getField('DISCOUNT_ROW', 0);
	    }
	  }, {
	    key: "isEmptyDiscount",
	    value: function isEmptyDiscount() {
	      if (this.isDiscountPercentage()) {
	        return this.getDiscountRate() === 0;
	      }

	      if (this.isDiscountMonetary()) {
	        return this.getDiscountSum() === 0;
	      }

	      return this.isDiscountUndefined();
	    }
	  }, {
	    key: "getTaxIncluded",
	    value: function getTaxIncluded() {
	      return this.getField('TAX_INCLUDED', 'N');
	    }
	  }, {
	    key: "isTaxIncluded",
	    value: function isTaxIncluded() {
	      return this.getTaxIncluded() === 'Y';
	    }
	  }, {
	    key: "getTaxRate",
	    value: function getTaxRate() {
	      return this.getField('TAX_RATE', 0);
	    }
	  }, {
	    key: "getTaxSum",
	    value: function getTaxSum() {
	      return this.getField('TAX_SUM', 0);
	    }
	  }, {
	    key: "getSum",
	    value: function getSum() {
	      return this.getField('SUM', 0);
	    }
	  }]);
	  return FieldStorage;
	}();

	var TaxForPriceStrategy = /*#__PURE__*/function () {
	  function TaxForPriceStrategy(productCalculator) {
	    babelHelpers.classCallCheck(this, TaxForPriceStrategy);
	    babelHelpers.defineProperty(this, "calculator", null);
	    this.calculator = productCalculator;
	  }

	  babelHelpers.createClass(TaxForPriceStrategy, [{
	    key: "getFieldStorage",
	    value: function getFieldStorage() {
	      return new FieldStorage(this.calculator.getFields());
	    }
	  }, {
	    key: "getPricePrecision",
	    value: function getPricePrecision() {
	      return this.calculator.getPricePrecision();
	    }
	  }, {
	    key: "getCommonPrecision",
	    value: function getCommonPrecision() {
	      return this.calculator.getCommonPrecision();
	    }
	  }, {
	    key: "getQuantityPrecision",
	    value: function getQuantityPrecision() {
	      return this.calculator.getQuantityPrecision();
	    }
	  }, {
	    key: "calculatePrice",
	    value: function calculatePrice(value) {
	      if (value < 0) {
	        throw new Error('Price must be equal or greater than zero.');
	      }

	      value = this.roundPrice(value);
	      var fieldStorage = this.getFieldStorage();

	      if (fieldStorage.isTaxIncluded()) {
	        fieldStorage.setField('PRICE_BRUTTO', value);
	      } else {
	        fieldStorage.setField('PRICE_NETTO', value);
	      }

	      this.updatePrice(fieldStorage);
	      this.activateCustomized(fieldStorage);
	      return fieldStorage.getFields();
	    }
	  }, {
	    key: "calculateQuantity",
	    value: function calculateQuantity(value) {
	      if (value < 0) {
	        throw new Error('Quantity must be equal or greater than zero.');
	      }

	      value = this.round(value, this.getQuantityPrecision());
	      var fieldStorage = this.getFieldStorage();
	      fieldStorage.setField('QUANTITY', value);
	      this.updateRowDiscount(fieldStorage);
	      this.updateTax(fieldStorage);
	      this.updateSum(fieldStorage);
	      return fieldStorage.getFields();
	    }
	  }, {
	    key: "calculateDiscount",
	    value: function calculateDiscount(value) {
	      var fieldStorage = this.getFieldStorage();

	      if (value === 0.0) {
	        this.clearResultPrices(fieldStorage);
	      } else if (fieldStorage.isDiscountPercentage()) {
	        fieldStorage.setField('DISCOUNT_RATE', value);
	        this.updateResultPrices(fieldStorage);
	        fieldStorage.setField('DISCOUNT_SUM', this.roundPrice(fieldStorage.getPriceNetto() - fieldStorage.getPriceExclusive()));
	      } else if (fieldStorage.isDiscountMonetary()) {
	        fieldStorage.setField('DISCOUNT_SUM', value);
	        this.updateResultPrices(fieldStorage);
	        fieldStorage.setField('DISCOUNT_RATE', this.round(this.calculateDiscountRate(fieldStorage.getPriceNetto(), fieldStorage.getPriceExclusive()), this.getCommonPrecision()));
	      }

	      this.updateRowDiscount(fieldStorage);
	      this.updateTax(fieldStorage);
	      this.updateSum(fieldStorage);
	      this.activateCustomized(fieldStorage);
	      return fieldStorage.getFields();
	    }
	  }, {
	    key: "calculateDiscountType",
	    value: function calculateDiscountType(value) {
	      var fieldStorage = this.getFieldStorage();
	      fieldStorage.setField('DISCOUNT_TYPE_ID', value);
	      this.updateResultPrices(fieldStorage);
	      this.updateDiscount(fieldStorage);
	      this.updateRowDiscount(fieldStorage);
	      this.updateTax(fieldStorage);
	      this.updateSum(fieldStorage);
	      this.activateCustomized(fieldStorage);
	      return fieldStorage.getFields();
	    }
	  }, {
	    key: "calculateRowDiscount",
	    value: function calculateRowDiscount(value) {
	      var fieldStorage = this.getFieldStorage();
	      fieldStorage.setField('DISCOUNT_ROW', value);

	      if (value !== 0 && fieldStorage.getQuantity() === 0) {
	        fieldStorage.setField('QUANTITY', 1);
	      }

	      fieldStorage.setField('DISCOUNT_TYPE_ID', DiscountType.MONETARY);

	      if (value === 0 || fieldStorage.getQuantity() === 0) {
	        fieldStorage.setField('DISCOUNT_SUM', 0);
	      } else {
	        fieldStorage.setField('DISCOUNT_SUM', this.roundPrice(fieldStorage.getDiscountRow() / fieldStorage.getQuantity()));
	      }

	      this.updateResultPrices(fieldStorage);
	      this.updateDiscount(fieldStorage);
	      this.updateRowDiscount(fieldStorage);
	      this.updateTax(fieldStorage);
	      this.updateSum(fieldStorage);
	      this.activateCustomized(fieldStorage);
	      return fieldStorage.getFields();
	    }
	  }, {
	    key: "calculateTax",
	    value: function calculateTax(value) {
	      var fieldStorage = this.getFieldStorage();
	      fieldStorage.setField('TAX_RATE', value);
	      this.updateBasePrices(fieldStorage);
	      this.updateResultPrices(fieldStorage);

	      if (fieldStorage.isTaxIncluded()) {
	        this.updateDiscount(fieldStorage);
	        this.updateRowDiscount(fieldStorage);
	      }

	      this.updateTax(fieldStorage);
	      this.updateSum(fieldStorage);
	      this.activateCustomized(fieldStorage);
	      return fieldStorage.getFields();
	    }
	  }, {
	    key: "calculateTaxIncluded",
	    value: function calculateTaxIncluded(value) {
	      var fieldStorage = this.getFieldStorage();

	      if (fieldStorage.getTaxIncluded() !== value) {
	        fieldStorage.setField('TAX_INCLUDED', value);

	        if (fieldStorage.isTaxIncluded()) {
	          fieldStorage.setField('PRICE_BRUTTO', fieldStorage.getPriceNetto());
	        } else {
	          fieldStorage.setField('PRICE_NETTO', fieldStorage.getPriceBrutto());
	        }
	      }

	      this.updatePrice(fieldStorage);
	      this.activateCustomized(fieldStorage);
	      return fieldStorage.getFields();
	    }
	  }, {
	    key: "calculateRowSum",
	    value: function calculateRowSum(value) {
	      var fieldStorage = this.getFieldStorage();
	      fieldStorage.setField('SUM', value);

	      if (fieldStorage.getQuantity() === 0) {
	        fieldStorage.setField('QUANTITY', 1);
	      }

	      var discountSum = this.roundPrice(fieldStorage.getPriceNetto() - fieldStorage.getSum() / (fieldStorage.getQuantity() * (1 + fieldStorage.getTaxRate() / 100)));
	      fieldStorage.setField('DISCOUNT_SUM', discountSum);
	      fieldStorage.setField('DISCOUNT_TYPE_ID', DiscountType.MONETARY);

	      if (fieldStorage.isEmptyDiscount()) {
	        this.clearResultPrices(fieldStorage);
	      } else if (fieldStorage.isDiscountHandmade()) {
	        this.updateResultPrices(fieldStorage);
	      }

	      this.updateDiscount(fieldStorage);
	      this.updateRowDiscount(fieldStorage);
	      this.updateTax(fieldStorage);
	      this.activateCustomized(fieldStorage);
	      return fieldStorage.getFields();
	    }
	  }, {
	    key: "updatePrice",
	    value: function updatePrice(fieldStorage) {
	      this.updateBasePrices(fieldStorage);

	      if (fieldStorage.isEmptyDiscount()) {
	        this.clearResultPrices(fieldStorage);
	      } else if (fieldStorage.isDiscountHandmade()) {
	        this.updateResultPrices(fieldStorage);
	      }

	      this.updateDiscount(fieldStorage);
	      this.updateRowDiscount(fieldStorage);
	      this.updateTax(fieldStorage);
	      this.updateSum(fieldStorage);
	    }
	  }, {
	    key: "clearResultPrices",
	    value: function clearResultPrices(fieldStorage) {
	      fieldStorage.setField('PRICE_EXCLUSIVE', fieldStorage.getPriceNetto());
	      fieldStorage.setField('PRICE', fieldStorage.getPriceBrutto());
	      fieldStorage.setField('DISCOUNT_RATE', 0.0);
	      fieldStorage.setField('DISCOUNT_SUM', 0.0);
	    }
	  }, {
	    key: "calculatePriceWithoutDiscount",
	    value: function calculatePriceWithoutDiscount(price, discount, discountType) {
	      var result = 0.0;

	      switch (discountType) {
	        case DiscountType.PERCENTAGE:
	          result = price - price * discount / 100;
	          break;

	        case DiscountType.MONETARY:
	          result = price - discount;
	          break;
	      }

	      return result;
	    }
	  }, {
	    key: "updateBasePrices",
	    value: function updateBasePrices(fieldStorage) {
	      if (fieldStorage.isTaxIncluded()) {
	        fieldStorage.setField('PRICE_NETTO', this.roundPrice(this.calculatePriceWithoutTax(fieldStorage.getPriceBrutto(), fieldStorage.getTaxRate())));
	      } else {
	        fieldStorage.setField('PRICE_BRUTTO', this.roundPrice(this.calculatePriceWithTax(fieldStorage.getPriceNetto(), fieldStorage.getTaxRate())));
	      }
	    }
	  }, {
	    key: "updateResultPrices",
	    value: function updateResultPrices(fieldStorage) {
	      // price without tax
	      var exclusivePrice;

	      if (fieldStorage.isDiscountPercentage()) {
	        exclusivePrice = this.calculatePriceWithoutDiscount(fieldStorage.getPriceNetto(), fieldStorage.getDiscountRate(), DiscountType.PERCENTAGE);
	      } else if (fieldStorage.isDiscountMonetary()) {
	        exclusivePrice = this.calculatePriceWithoutDiscount(fieldStorage.getPriceNetto(), fieldStorage.getDiscountSum(), DiscountType.MONETARY);
	      } else {
	        exclusivePrice = fieldStorage.getPriceExclusive();
	      }

	      fieldStorage.setField('PRICE_EXCLUSIVE', this.roundPrice(exclusivePrice));
	      fieldStorage.setField('PRICE', this.roundPrice(this.calculatePriceWithTax(exclusivePrice, fieldStorage.getTaxRate())));
	    }
	  }, {
	    key: "activateCustomized",
	    value: function activateCustomized(fieldStorage) {
	      fieldStorage.setField('CUSTOMIZED', 'Y');
	    }
	  }, {
	    key: "updateDiscount",
	    value: function updateDiscount(fieldStorage) {
	      if (fieldStorage.isEmptyDiscount()) {
	        this.clearResultPrices(fieldStorage);
	      } else if (fieldStorage.isDiscountPercentage()) {
	        fieldStorage.setField('DISCOUNT_SUM', this.round(fieldStorage.getPriceNetto() - fieldStorage.getPriceExclusive()));
	      } else if (fieldStorage.isDiscountMonetary()) {
	        fieldStorage.setField('DISCOUNT_RATE', this.round(this.calculateDiscountRate(fieldStorage.getPriceNetto(), fieldStorage.getPriceNetto() - fieldStorage.getDiscountSum()), this.getCommonPrecision()));
	      }
	    }
	  }, {
	    key: "updateRowDiscount",
	    value: function updateRowDiscount(fieldStorage) {
	      fieldStorage.setField('DISCOUNT_ROW', this.roundPrice(fieldStorage.getDiscountSum() * fieldStorage.getQuantity()));
	    }
	  }, {
	    key: "updateTax",
	    value: function updateTax(fieldStorage) {
	      var sum;

	      if (fieldStorage.isTaxIncluded()) {
	        sum = fieldStorage.getPrice() * fieldStorage.getQuantity() * (1 - 1 / (1 + fieldStorage.getTaxRate() / 100));
	      } else {
	        sum = fieldStorage.getPriceExclusive() * fieldStorage.getQuantity() * (fieldStorage.getTaxRate() / 100);
	      }

	      fieldStorage.setField('TAX_SUM', this.roundPrice(sum));
	    }
	  }, {
	    key: "updateSum",
	    value: function updateSum(fieldStorage) {
	      var sum;

	      if (fieldStorage.isTaxIncluded()) {
	        sum = fieldStorage.getPrice() * fieldStorage.getQuantity();
	      } else {
	        sum = this.calculatePriceWithTax(fieldStorage.getPriceExclusive() * fieldStorage.getQuantity(), fieldStorage.getTaxRate());
	      }

	      fieldStorage.setField('SUM', this.roundPrice(sum));
	    }
	  }, {
	    key: "calculateDiscountRate",
	    value: function calculateDiscountRate(originalPrice, price) {
	      if (originalPrice === 0.0) {
	        return 0.0;
	      }

	      if (price === 0.0) {
	        return originalPrice > 0 ? 100.0 : -100.0;
	      }

	      return (originalPrice - price) / originalPrice * 100;
	    }
	  }, {
	    key: "calculatePriceWithoutTax",
	    value: function calculatePriceWithoutTax(price, taxRate) {
	      // Tax is not included in price
	      return price / (1 + taxRate / 100);
	    }
	  }, {
	    key: "calculatePriceWithTax",
	    value: function calculatePriceWithTax(price, taxRate) {
	      // Tax is included in price
	      return price + price * taxRate / 100;
	    }
	  }, {
	    key: "round",
	    value: function round(value) {
	      var precision = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : ProductCalculator.DEFAULT_PRECISION;
	      var factor = Math.pow(10, precision);
	      return Math.round(value * factor) / factor;
	    }
	  }, {
	    key: "roundPrice",
	    value: function roundPrice(value) {
	      return this.round(value, this.getPricePrecision());
	    }
	  }]);
	  return TaxForPriceStrategy;
	}();

	function _classPrivateMethodGet(receiver, privateSet, fn) { if (!privateSet.has(receiver)) { throw new TypeError("attempted to get private field on non-instance"); } return fn; }

	var _fields = new WeakMap();

	var _strategy = new WeakMap();

	var _settings = new WeakMap();

	var _getSetting = new WeakSet();

	var ProductCalculator = /*#__PURE__*/function () {
	  function ProductCalculator() {
	    var fields = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};
	    var settings = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : {};
	    babelHelpers.classCallCheck(this, ProductCalculator);

	    _getSetting.add(this);

	    _fields.set(this, {
	      writable: true,
	      value: {}
	    });

	    _strategy.set(this, {
	      writable: true,
	      value: {}
	    });

	    _settings.set(this, {
	      writable: true,
	      value: {}
	    });

	    this.setFields(fields);
	    this.setSettings(settings);
	    this.setCalculationStrategy(new TaxForPriceStrategy(this));
	  }

	  babelHelpers.createClass(ProductCalculator, [{
	    key: "setField",
	    value: function setField(name, value) {
	      babelHelpers.classPrivateFieldGet(this, _fields)[name] = value;
	      return this;
	    }
	  }, {
	    key: "setCalculationStrategy",
	    value: function setCalculationStrategy() {
	      var strategy = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};
	      babelHelpers.classPrivateFieldSet(this, _strategy, strategy);
	      return this;
	    }
	  }, {
	    key: "setFields",
	    value: function setFields(fields) {
	      for (var name in fields) {
	        if (fields.hasOwnProperty(name)) {
	          this.setField(name, fields[name]);
	        }
	      }

	      return this;
	    }
	  }, {
	    key: "getFields",
	    value: function getFields() {
	      return babelHelpers.objectSpread({}, babelHelpers.classPrivateFieldGet(this, _fields));
	    }
	  }, {
	    key: "setSettings",
	    value: function setSettings() {
	      var settings = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};
	      babelHelpers.classPrivateFieldSet(this, _settings, babelHelpers.objectSpread({}, settings));
	      return this;
	    }
	  }, {
	    key: "getSettings",
	    value: function getSettings() {
	      return babelHelpers.objectSpread({}, babelHelpers.classPrivateFieldGet(this, _settings));
	    }
	  }, {
	    key: "getPricePrecision",
	    value: function getPricePrecision() {
	      return _classPrivateMethodGet(this, _getSetting, _getSetting2).call(this, 'pricePrecision', ProductCalculator.DEFAULT_PRECISION);
	    }
	  }, {
	    key: "getCommonPrecision",
	    value: function getCommonPrecision() {
	      return _classPrivateMethodGet(this, _getSetting, _getSetting2).call(this, 'commonPrecision', ProductCalculator.DEFAULT_PRECISION);
	    }
	  }, {
	    key: "getQuantityPrecision",
	    value: function getQuantityPrecision() {
	      return _classPrivateMethodGet(this, _getSetting, _getSetting2).call(this, 'quantityPrecision', ProductCalculator.DEFAULT_PRECISION);
	    }
	  }, {
	    key: "calculatePrice",
	    value: function calculatePrice(value) {
	      return babelHelpers.classPrivateFieldGet(this, _strategy).calculatePrice(value);
	    }
	  }, {
	    key: "calculateQuantity",
	    value: function calculateQuantity(value) {
	      return babelHelpers.classPrivateFieldGet(this, _strategy).calculateQuantity(value);
	    }
	  }, {
	    key: "calculateDiscount",
	    value: function calculateDiscount(value) {
	      return babelHelpers.classPrivateFieldGet(this, _strategy).calculateDiscount(value);
	    }
	  }, {
	    key: "calculateDiscountType",
	    value: function calculateDiscountType(value) {
	      return babelHelpers.classPrivateFieldGet(this, _strategy).calculateDiscountType(value);
	    }
	  }, {
	    key: "calculateRowDiscount",
	    value: function calculateRowDiscount(value) {
	      return babelHelpers.classPrivateFieldGet(this, _strategy).calculateRowDiscount(value);
	    }
	  }, {
	    key: "calculateTax",
	    value: function calculateTax(value) {
	      return babelHelpers.classPrivateFieldGet(this, _strategy).calculateTax(value);
	    }
	  }, {
	    key: "calculateTaxIncluded",
	    value: function calculateTaxIncluded(value) {
	      return babelHelpers.classPrivateFieldGet(this, _strategy).calculateTaxIncluded(value);
	    }
	  }, {
	    key: "calculateRowSum",
	    value: function calculateRowSum(value) {
	      return babelHelpers.classPrivateFieldGet(this, _strategy).calculateRowSum(value);
	    }
	  }]);
	  return ProductCalculator;
	}();
	babelHelpers.defineProperty(ProductCalculator, "DEFAULT_PRECISION", 2);

	var _getSetting2 = function _getSetting2(name, defaultValue) {
	  return babelHelpers.classPrivateFieldGet(this, _settings).hasOwnProperty(name) ? babelHelpers.classPrivateFieldGet(this, _settings)[name] : defaultValue;
	};

	var TaxForSumStrategy = /*#__PURE__*/function (_TaxForPriceStrategy) {
	  babelHelpers.inherits(TaxForSumStrategy, _TaxForPriceStrategy);

	  function TaxForSumStrategy() {
	    babelHelpers.classCallCheck(this, TaxForSumStrategy);
	    return babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(TaxForSumStrategy).apply(this, arguments));
	  }

	  babelHelpers.createClass(TaxForSumStrategy, [{
	    key: "calculatePriceWithoutTax",
	    value: function calculatePriceWithoutTax(price, taxRate) {
	      return price;
	    }
	  }, {
	    key: "updateResultPrices",
	    value: function updateResultPrices(fieldStorage) {
	      var exclusivePrice;

	      if (fieldStorage.isDiscountPercentage()) {
	        exclusivePrice = this.calculatePriceWithoutDiscount(fieldStorage.getPriceNetto(), fieldStorage.getDiscountRate(), DiscountType.PERCENTAGE);
	      } else if (fieldStorage.isDiscountMonetary()) {
	        exclusivePrice = this.calculatePriceWithoutDiscount(fieldStorage.getPriceNetto(), fieldStorage.getDiscountSum(), DiscountType.MONETARY);
	      } else {
	        exclusivePrice = fieldStorage.getPriceExclusive();
	      }

	      fieldStorage.setField('PRICE_EXCLUSIVE', this.roundPrice(exclusivePrice));

	      if (fieldStorage.isTaxIncluded()) {
	        fieldStorage.setField('PRICE', this.roundPrice(exclusivePrice));
	      } else {
	        fieldStorage.setField('PRICE', this.roundPrice(this.calculatePriceWithTax(exclusivePrice, fieldStorage.getTaxRate())));
	      }
	    }
	  }]);
	  return TaxForSumStrategy;
	}(TaxForPriceStrategy);

	exports.DiscountType = DiscountType;
	exports.ProductCalculator = ProductCalculator;
	exports.TaxForSumStrategy = TaxForSumStrategy;
	exports.TaxForPriceStrategy = TaxForPriceStrategy;

}((this.BX.Catalog = this.BX.Catalog || {}),BX));
//# sourceMappingURL=product.calculator.bundle.js.map
