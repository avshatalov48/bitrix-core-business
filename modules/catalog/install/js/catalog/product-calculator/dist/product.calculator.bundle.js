this.BX = this.BX || {};
(function (exports,main_core,catalog_productCalculator) {
	'use strict';

	var DiscountType = function DiscountType() {
	  babelHelpers.classCallCheck(this, DiscountType);
	};
	babelHelpers.defineProperty(DiscountType, "UNDEFINED", 0);
	babelHelpers.defineProperty(DiscountType, "MONETARY", 1);
	babelHelpers.defineProperty(DiscountType, "PERCENTAGE", 2);

	function ownKeys(object, enumerableOnly) { var keys = Object.keys(object); if (Object.getOwnPropertySymbols) { var symbols = Object.getOwnPropertySymbols(object); enumerableOnly && (symbols = symbols.filter(function (sym) { return Object.getOwnPropertyDescriptor(object, sym).enumerable; })), keys.push.apply(keys, symbols); } return keys; }

	function _objectSpread(target) { for (var i = 1; i < arguments.length; i++) { var source = null != arguments[i] ? arguments[i] : {}; i % 2 ? ownKeys(Object(source), !0).forEach(function (key) { babelHelpers.defineProperty(target, key, source[key]); }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(target, Object.getOwnPropertyDescriptors(source)) : ownKeys(Object(source)).forEach(function (key) { Object.defineProperty(target, key, Object.getOwnPropertyDescriptor(source, key)); }); } return target; }

	function _classPrivateMethodInitSpec(obj, privateSet) { _checkPrivateRedeclaration(obj, privateSet); privateSet.add(obj); }

	function _checkPrivateRedeclaration(obj, privateCollection) { if (privateCollection.has(obj)) { throw new TypeError("Cannot initialize the same private elements twice on an object"); } }

	function _classStaticPrivateMethodGet(receiver, classConstructor, method) { _classCheckPrivateStaticAccess(receiver, classConstructor); return method; }

	function _classCheckPrivateStaticAccess(receiver, classConstructor) { if (receiver !== classConstructor) { throw new TypeError("Private static access of wrong provenance"); } }

	function _classPrivateMethodGet(receiver, privateSet, fn) { if (!privateSet.has(receiver)) { throw new TypeError("attempted to get private field on non-instance"); } return fn; }
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

	var _getPricePrecision = /*#__PURE__*/new WeakSet();

	var _getCommonPrecision = /*#__PURE__*/new WeakSet();

	var _getQuantityPrecision = /*#__PURE__*/new WeakSet();

	var _validateValue = /*#__PURE__*/new WeakSet();

	var FieldStorage = /*#__PURE__*/function () {
	  function FieldStorage(fields, calculator) {
	    babelHelpers.classCallCheck(this, FieldStorage);

	    _classPrivateMethodInitSpec(this, _validateValue);

	    _classPrivateMethodInitSpec(this, _getQuantityPrecision);

	    _classPrivateMethodInitSpec(this, _getCommonPrecision);

	    _classPrivateMethodInitSpec(this, _getPricePrecision);

	    this.fields = _objectSpread({}, initialFields);

	    if (main_core.Type.isPlainObject(fields)) {
	      this.fields = _objectSpread(_objectSpread({}, this.fields), fields);
	    }

	    this.calculator = calculator;
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
	      value = _classPrivateMethodGet(this, _validateValue, _validateValue2).call(this, name, value);
	      this.fields[name] = value;
	    }
	  }, {
	    key: "getBasePrice",
	    value: function getBasePrice() {
	      return this.getField('BASE_PRICE', 0);
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

	function _getPricePrecision2() {
	  return this.calculator.getPricePrecision();
	}

	function _getCommonPrecision2() {
	  return this.calculator.getCommonPrecision();
	}

	function _getQuantityPrecision2() {
	  return this.calculator.getQuantityPrecision();
	}

	function _validateValue2(name, value) {
	  var priceFields = ['PRICE', 'PRICE_EXCLUSIVE', 'PRICE_NETTO', 'PRICE_BRUTTO', 'DISCOUNT_SUM', 'DISCOUNT_ROW', 'TAX_SUM', 'SUM'];

	  if (name === 'DISCOUNT_TYPE_ID') {
	    value = value === DiscountType.PERCENTAGE || value === DiscountType.MONETARY ? value : DiscountType.UNDEFINED;
	  } else if (name === 'QUANTITY') {
	    value = _classStaticPrivateMethodGet(FieldStorage, FieldStorage, _round).call(FieldStorage, value, _classPrivateMethodGet(this, _getQuantityPrecision, _getQuantityPrecision2).call(this));
	  } else if (name === 'CUSTOMIZED' || name === 'TAX_INCLUDED') {
	    value = value === 'Y' ? 'Y' : 'N';
	  } else if (name === 'TAX_RATE') {
	    if (main_core.Type.isNil(value)) {
	      return null;
	    }

	    value = _classStaticPrivateMethodGet(FieldStorage, FieldStorage, _round).call(FieldStorage, value, _classPrivateMethodGet(this, _getCommonPrecision, _getCommonPrecision2).call(this));
	  } else if (name === 'DISCOUNT_RATE') {
	    value = _classStaticPrivateMethodGet(FieldStorage, FieldStorage, _round).call(FieldStorage, value, _classPrivateMethodGet(this, _getCommonPrecision, _getCommonPrecision2).call(this));
	  } else if (priceFields.includes(name)) {
	    value = _classStaticPrivateMethodGet(FieldStorage, FieldStorage, _round).call(FieldStorage, value, _classPrivateMethodGet(this, _getPricePrecision, _getPricePrecision2).call(this));
	  }

	  return value;
	}

	function _round(value) {
	  var precision = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : catalog_productCalculator.ProductCalculator.DEFAULT_PRECISION;
	  var factor = Math.pow(10, precision);
	  return Math.round(value * factor) / factor;
	}

	var TaxForPriceStrategy = /*#__PURE__*/function () {
	  function TaxForPriceStrategy(productCalculator) {
	    babelHelpers.classCallCheck(this, TaxForPriceStrategy);
	    babelHelpers.defineProperty(this, "calculator", null);
	    this.calculator = productCalculator;
	  }

	  babelHelpers.createClass(TaxForPriceStrategy, [{
	    key: "getFieldStorage",
	    value: function getFieldStorage() {
	      return new FieldStorage(this.calculator.getFields(), this.calculator);
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
	    key: "calculateBasePrice",
	    value: function calculateBasePrice(value) {
	      if (value < 0) {
	        throw new Error('Price must be equal or greater than zero.');
	      }

	      var fieldStorage = this.getFieldStorage();
	      fieldStorage.setField('BASE_PRICE', value);

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
	    key: "calculatePrice",
	    value: function calculatePrice(value) {
	      return this.calculateBasePrice(value);
	    }
	  }, {
	    key: "calculateQuantity",
	    value: function calculateQuantity(value) {
	      if (value < 0) {
	        throw new Error('Quantity must be equal or greater than zero.');
	      }

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
	      var fieldStorage = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : null;

	      if (!fieldStorage) {
	        fieldStorage = this.getFieldStorage();
	      }

	      if (value === 0.0) {
	        this.clearResultPrices(fieldStorage);
	      } else if (fieldStorage.isDiscountPercentage()) {
	        fieldStorage.setField('DISCOUNT_RATE', value);
	        this.updateResultPrices(fieldStorage);
	        fieldStorage.setField('DISCOUNT_SUM', fieldStorage.getPriceNetto() - fieldStorage.getPriceExclusive());
	      } else if (fieldStorage.isDiscountMonetary()) {
	        fieldStorage.setField('DISCOUNT_SUM', value);
	        this.updateResultPrices(fieldStorage);
	        fieldStorage.setField('DISCOUNT_RATE', this.calculateDiscountRate(fieldStorage.getPriceNetto(), fieldStorage.getPriceExclusive()));
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
	        fieldStorage.setField('DISCOUNT_SUM', fieldStorage.getDiscountRow() / fieldStorage.getQuantity());
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

	      var discountSum = fieldStorage.getPriceNetto() - fieldStorage.getSum() / (fieldStorage.getQuantity() * (1 + fieldStorage.getTaxRate() / 100));
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
	        fieldStorage.setField('PRICE_NETTO', this.calculatePriceWithoutTax(fieldStorage.getPriceBrutto(), fieldStorage.getTaxRate()));
	      } else {
	        fieldStorage.setField('PRICE_BRUTTO', this.calculatePriceWithTax(fieldStorage.getPriceNetto(), fieldStorage.getTaxRate()));
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

	      fieldStorage.setField('PRICE_EXCLUSIVE', exclusivePrice);
	      fieldStorage.setField('PRICE', this.calculatePriceWithTax(exclusivePrice, fieldStorage.getTaxRate()));
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
	        fieldStorage.setField('DISCOUNT_SUM', fieldStorage.getPriceNetto() - fieldStorage.getPriceExclusive());
	      } else if (fieldStorage.isDiscountMonetary()) {
	        fieldStorage.setField('DISCOUNT_RATE', this.calculateDiscountRate(fieldStorage.getPriceNetto(), fieldStorage.getPriceNetto() - fieldStorage.getDiscountSum()));
	      }
	    }
	  }, {
	    key: "updateRowDiscount",
	    value: function updateRowDiscount(fieldStorage) {
	      fieldStorage.setField('DISCOUNT_ROW', fieldStorage.getDiscountSum() * fieldStorage.getQuantity());
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

	      fieldStorage.setField('TAX_SUM', sum);
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

	      fieldStorage.setField('SUM', sum);
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
	  }]);
	  return TaxForPriceStrategy;
	}();

	function ownKeys$1(object, enumerableOnly) { var keys = Object.keys(object); if (Object.getOwnPropertySymbols) { var symbols = Object.getOwnPropertySymbols(object); enumerableOnly && (symbols = symbols.filter(function (sym) { return Object.getOwnPropertyDescriptor(object, sym).enumerable; })), keys.push.apply(keys, symbols); } return keys; }

	function _objectSpread$1(target) { for (var i = 1; i < arguments.length; i++) { var source = null != arguments[i] ? arguments[i] : {}; i % 2 ? ownKeys$1(Object(source), !0).forEach(function (key) { babelHelpers.defineProperty(target, key, source[key]); }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(target, Object.getOwnPropertyDescriptors(source)) : ownKeys$1(Object(source)).forEach(function (key) { Object.defineProperty(target, key, Object.getOwnPropertyDescriptor(source, key)); }); } return target; }

	function _classPrivateMethodInitSpec$1(obj, privateSet) { _checkPrivateRedeclaration$1(obj, privateSet); privateSet.add(obj); }

	function _classPrivateFieldInitSpec(obj, privateMap, value) { _checkPrivateRedeclaration$1(obj, privateMap); privateMap.set(obj, value); }

	function _checkPrivateRedeclaration$1(obj, privateCollection) { if (privateCollection.has(obj)) { throw new TypeError("Cannot initialize the same private elements twice on an object"); } }

	function _classPrivateMethodGet$1(receiver, privateSet, fn) { if (!privateSet.has(receiver)) { throw new TypeError("attempted to get private field on non-instance"); } return fn; }

	var _fields = /*#__PURE__*/new WeakMap();

	var _strategy = /*#__PURE__*/new WeakMap();

	var _settings = /*#__PURE__*/new WeakMap();

	var _getSetting = /*#__PURE__*/new WeakSet();

	var ProductCalculator = /*#__PURE__*/function () {
	  function ProductCalculator() {
	    var fields = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};
	    var settings = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : {};
	    babelHelpers.classCallCheck(this, ProductCalculator);

	    _classPrivateMethodInitSpec$1(this, _getSetting);

	    _classPrivateFieldInitSpec(this, _fields, {
	      writable: true,
	      value: {}
	    });

	    _classPrivateFieldInitSpec(this, _strategy, {
	      writable: true,
	      value: {}
	    });

	    _classPrivateFieldInitSpec(this, _settings, {
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
	      return _objectSpread$1({}, babelHelpers.classPrivateFieldGet(this, _fields));
	    }
	  }, {
	    key: "setSettings",
	    value: function setSettings() {
	      var settings = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};
	      babelHelpers.classPrivateFieldSet(this, _settings, _objectSpread$1({}, settings));
	      return this;
	    }
	  }, {
	    key: "getSettings",
	    value: function getSettings() {
	      return _objectSpread$1({}, babelHelpers.classPrivateFieldGet(this, _settings));
	    }
	  }, {
	    key: "getPricePrecision",
	    value: function getPricePrecision() {
	      return _classPrivateMethodGet$1(this, _getSetting, _getSetting2).call(this, 'pricePrecision', ProductCalculator.DEFAULT_PRECISION);
	    }
	  }, {
	    key: "getCommonPrecision",
	    value: function getCommonPrecision() {
	      return _classPrivateMethodGet$1(this, _getSetting, _getSetting2).call(this, 'commonPrecision', ProductCalculator.DEFAULT_PRECISION);
	    }
	  }, {
	    key: "getQuantityPrecision",
	    value: function getQuantityPrecision() {
	      return _classPrivateMethodGet$1(this, _getSetting, _getSetting2).call(this, 'quantityPrecision', ProductCalculator.DEFAULT_PRECISION);
	    }
	  }, {
	    key: "calculateBasePrice",
	    value: function calculateBasePrice(value) {
	      return babelHelpers.classPrivateFieldGet(this, _strategy).calculateBasePrice(value);
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

	function _getSetting2(name, defaultValue) {
	  return babelHelpers.classPrivateFieldGet(this, _settings).hasOwnProperty(name) ? babelHelpers.classPrivateFieldGet(this, _settings)[name] : defaultValue;
	}

	babelHelpers.defineProperty(ProductCalculator, "DEFAULT_PRECISION", 2);

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

	      fieldStorage.setField('PRICE_EXCLUSIVE', exclusivePrice);

	      if (fieldStorage.isTaxIncluded()) {
	        fieldStorage.setField('PRICE', exclusivePrice);
	      } else {
	        fieldStorage.setField('PRICE', this.calculatePriceWithTax(exclusivePrice, fieldStorage.getTaxRate()));
	      }
	    }
	  }]);
	  return TaxForSumStrategy;
	}(TaxForPriceStrategy);

	exports.DiscountType = DiscountType;
	exports.ProductCalculator = ProductCalculator;
	exports.TaxForSumStrategy = TaxForSumStrategy;
	exports.TaxForPriceStrategy = TaxForPriceStrategy;

}((this.BX.Catalog = this.BX.Catalog || {}),BX,BX.Catalog));
//# sourceMappingURL=product.calculator.bundle.js.map
