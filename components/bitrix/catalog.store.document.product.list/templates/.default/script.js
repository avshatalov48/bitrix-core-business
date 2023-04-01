this.BX = this.BX || {};
this.BX.Catalog = this.BX.Catalog || {};
this.BX.Catalog.Store = this.BX.Catalog.Store || {};
(function (exports,main_popup,main_core_events,currency_currencyCore,catalog_productSelector,catalog_storeSelector,catalog_documentCard,catalog_productModel,main_core,spotlight,ui_tour,ui_notification) {
	'use strict';

	catalog_documentCard = catalog_documentCard && catalog_documentCard.hasOwnProperty('default') ? catalog_documentCard['default'] : catalog_documentCard;

	var _templateObject;

	var HintPopup = /*#__PURE__*/function () {
	  function HintPopup(editor) {
	    babelHelpers.classCallCheck(this, HintPopup);
	    this.editor = editor;
	  }

	  babelHelpers.createClass(HintPopup, [{
	    key: "load",
	    value: function load(node, text) {
	      if (!this.hintPopup) {
	        this.hintPopup = new main_popup.Popup('ui-hint-popup-' + this.editor.getId(), null, {
	          darkMode: true,
	          closeIcon: true,
	          animation: 'fading-slide'
	        });
	      }

	      this.hintPopup.setBindElement(node);
	      this.hintPopup.adjustPosition();
	      this.hintPopup.setContent(main_core.Tag.render(_templateObject || (_templateObject = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<div class='ui-hint-content'>", "</div>\n\t\t"])), main_core.Text.encode(text)));
	      return this.hintPopup;
	    }
	  }, {
	    key: "show",
	    value: function show() {
	      if (this.hintPopup) {
	        this.hintPopup.show();
	      }
	    }
	  }, {
	    key: "close",
	    value: function close() {
	      if (this.hintPopup) {
	        this.hintPopup.close();
	      }
	    }
	  }]);
	  return HintPopup;
	}();

	function ownKeys(object, enumerableOnly) { var keys = Object.keys(object); if (Object.getOwnPropertySymbols) { var symbols = Object.getOwnPropertySymbols(object); enumerableOnly && (symbols = symbols.filter(function (sym) { return Object.getOwnPropertyDescriptor(object, sym).enumerable; })), keys.push.apply(keys, symbols); } return keys; }

	function _objectSpread(target) { for (var i = 1; i < arguments.length; i++) { var source = null != arguments[i] ? arguments[i] : {}; i % 2 ? ownKeys(Object(source), !0).forEach(function (key) { babelHelpers.defineProperty(target, key, source[key]); }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(target, Object.getOwnPropertyDescriptors(source)) : ownKeys(Object(source)).forEach(function (key) { Object.defineProperty(target, key, Object.getOwnPropertyDescriptor(source, key)); }); } return target; }

	function _classPrivateFieldInitSpec(obj, privateMap, value) { _checkPrivateRedeclaration(obj, privateMap); privateMap.set(obj, value); }

	function _checkPrivateRedeclaration(obj, privateCollection) { if (privateCollection.has(obj)) { throw new TypeError("Cannot initialize the same private elements twice on an object"); } }

	var _fields = /*#__PURE__*/new WeakMap();

	var PriceCalculator = /*#__PURE__*/function () {
	  function PriceCalculator(fields) {
	    babelHelpers.classCallCheck(this, PriceCalculator);

	    _classPrivateFieldInitSpec(this, _fields, {
	      writable: true,
	      value: {
	        basePrice: 0,
	        finalPrice: 0,
	        extra: null,
	        extraType: PriceCalculator.EXTRA_TYPE_PERCENTAGE
	      }
	    });

	    babelHelpers.classPrivateFieldSet(this, _fields, _objectSpread(_objectSpread({}, babelHelpers.classPrivateFieldGet(this, _fields)), fields));
	  }

	  babelHelpers.createClass(PriceCalculator, [{
	    key: "getBasePrice",
	    value: function getBasePrice() {
	      return babelHelpers.classPrivateFieldGet(this, _fields).basePrice;
	    }
	  }, {
	    key: "getFinalPrice",
	    value: function getFinalPrice() {
	      return babelHelpers.classPrivateFieldGet(this, _fields).finalPrice;
	    }
	  }, {
	    key: "getExtra",
	    value: function getExtra() {
	      return babelHelpers.classPrivateFieldGet(this, _fields).extra;
	    }
	  }, {
	    key: "getExtraType",
	    value: function getExtraType() {
	      return babelHelpers.classPrivateFieldGet(this, _fields).extraType;
	    }
	  }, {
	    key: "calculateBasePrice",
	    value: function calculateBasePrice(basePrice) {
	      babelHelpers.classPrivateFieldGet(this, _fields).basePrice = basePrice;
	      babelHelpers.classPrivateFieldGet(this, _fields).extra = main_core.Text.toNumber(babelHelpers.classPrivateFieldGet(this, _fields).extra);

	      if (babelHelpers.classPrivateFieldGet(this, _fields).extraType === PriceCalculator.EXTRA_TYPE_MONETARY) {
	        babelHelpers.classPrivateFieldGet(this, _fields).finalPrice = babelHelpers.classPrivateFieldGet(this, _fields).basePrice + babelHelpers.classPrivateFieldGet(this, _fields).extra;
	      } else {
	        babelHelpers.classPrivateFieldGet(this, _fields).finalPrice = babelHelpers.classPrivateFieldGet(this, _fields).basePrice * (1 + babelHelpers.classPrivateFieldGet(this, _fields).extra / 100);
	      }

	      return this;
	    }
	  }, {
	    key: "calculateFinalPrice",
	    value: function calculateFinalPrice(finalPrice) {
	      babelHelpers.classPrivateFieldGet(this, _fields).finalPrice = finalPrice;
	      var basePrice = main_core.Text.toNumber(babelHelpers.classPrivateFieldGet(this, _fields).basePrice);

	      if (basePrice <= 0) {
	        babelHelpers.classPrivateFieldGet(this, _fields).extraType = PriceCalculator.EXTRA_TYPE_MONETARY;
	      }

	      if (babelHelpers.classPrivateFieldGet(this, _fields).extraType === PriceCalculator.EXTRA_TYPE_MONETARY) {
	        babelHelpers.classPrivateFieldGet(this, _fields).extra = babelHelpers.classPrivateFieldGet(this, _fields).finalPrice - basePrice;
	      } else {
	        babelHelpers.classPrivateFieldGet(this, _fields).extra = (babelHelpers.classPrivateFieldGet(this, _fields).finalPrice / basePrice - 1) * 100;
	      }

	      return this;
	    }
	  }, {
	    key: "calculateExtra",
	    value: function calculateExtra(extra) {
	      babelHelpers.classPrivateFieldGet(this, _fields).extra = extra;

	      if (main_core.Type.isNil(extra)) {
	        return this;
	      }

	      return this.calculateBasePrice(babelHelpers.classPrivateFieldGet(this, _fields).basePrice);
	    }
	  }, {
	    key: "calculateExtraType",
	    value: function calculateExtraType(extraType) {
	      if (extraType !== PriceCalculator.EXTRA_TYPE_MONETARY) {
	        extraType = PriceCalculator.EXTRA_TYPE_PERCENTAGE;
	      }

	      babelHelpers.classPrivateFieldGet(this, _fields).extraType = extraType;
	      return this.calculateFinalPrice(babelHelpers.classPrivateFieldGet(this, _fields).finalPrice);
	    }
	  }]);
	  return PriceCalculator;
	}();
	babelHelpers.defineProperty(PriceCalculator, "EXTRA_TYPE_PERCENTAGE", 1);
	babelHelpers.defineProperty(PriceCalculator, "EXTRA_TYPE_MONETARY", 2);

	var _templateObject$1;
	var AccessDeniedInput = /*#__PURE__*/function () {
	  function AccessDeniedInput(options) {
	    babelHelpers.classCallCheck(this, AccessDeniedInput);
	    this.text = options.text || main_core.Loc.getMessage('CATALOG_DOCUMENT_PRODUCT_LIST_ACCESS_DENIED_TEXT');
	    this.hint = options.hint;
	    this.isReadOnly = options.isReadOnly === true;
	  }

	  babelHelpers.createClass(AccessDeniedInput, [{
	    key: "renderTo",
	    value: function renderTo(node) {
	      var className = this.isReadOnly ? 'ui-ctl-no-border catalog-document-product-list-access-denied-readonly' : 'ui-ctl-disabled catalog-document-product-list-access-denied';
	      var block = main_core.Tag.render(_templateObject$1 || (_templateObject$1 = babelHelpers.taggedTemplateLiteral(["\n\t\t<div\n\t\t\tclass=\"ui-ctl ui-ctl-w100 ui-ctl-before-icon ui-ctl-after-icon ", "\"\n\t\t\tdata-hint=\"", "\"\n\t\t\tdata-hint-no-icon\n\t\t>\n\t\t\t<div class=\"ui-ctl-before catalog-document-product-list-access-denied-lock\"></div>\n\t\t\t<div class=\"ui-ctl-after catalog-document-product-list-access-denied-hint\"></div>\n\t\t\t<div class=\"ui-ctl-element\">", "</div>\n\t\t</div>\n\t\t"])), className, this.hint, this.text);
	      node.innerHTML = '';
	      node.appendChild(block);
	      BX.UI.Hint.createInstance({
	        popupParameters: {
	          angle: {
	            offset: 100
	          }
	        }
	      }).init();
	    }
	  }]);
	  return AccessDeniedInput;
	}();

	var _templateObject$2, _templateObject2, _templateObject3;

	function _createForOfIteratorHelper(o, allowArrayLike) { var it = typeof Symbol !== "undefined" && o[Symbol.iterator] || o["@@iterator"]; if (!it) { if (Array.isArray(o) || (it = _unsupportedIterableToArray(o)) || allowArrayLike && o && typeof o.length === "number") { if (it) o = it; var i = 0; var F = function F() {}; return { s: F, n: function n() { if (i >= o.length) return { done: true }; return { done: false, value: o[i++] }; }, e: function e(_e) { throw _e; }, f: F }; } throw new TypeError("Invalid attempt to iterate non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); } var normalCompletion = true, didErr = false, err; return { s: function s() { it = it.call(o); }, n: function n() { var step = it.next(); normalCompletion = step.done; return step; }, e: function e(_e2) { didErr = true; err = _e2; }, f: function f() { try { if (!normalCompletion && it["return"] != null) it["return"](); } finally { if (didErr) throw err; } } }; }

	function _unsupportedIterableToArray(o, minLen) { if (!o) return; if (typeof o === "string") return _arrayLikeToArray(o, minLen); var n = Object.prototype.toString.call(o).slice(8, -1); if (n === "Object" && o.constructor) n = o.constructor.name; if (n === "Map" || n === "Set") return Array.from(o); if (n === "Arguments" || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(n)) return _arrayLikeToArray(o, minLen); }

	function _arrayLikeToArray(arr, len) { if (len == null || len > arr.length) len = arr.length; for (var i = 0, arr2 = new Array(len); i < len; i++) { arr2[i] = arr[i]; } return arr2; }

	function _classPrivateMethodInitSpec(obj, privateSet) { _checkPrivateRedeclaration$1(obj, privateSet); privateSet.add(obj); }

	function _checkPrivateRedeclaration$1(obj, privateCollection) { if (privateCollection.has(obj)) { throw new TypeError("Cannot initialize the same private elements twice on an object"); } }

	function _classPrivateMethodGet(receiver, privateSet, fn) { if (!privateSet.has(receiver)) { throw new TypeError("attempted to get private field on non-instance"); } return fn; }
	var MODE_EDIT = 'EDIT';
	var MODE_SET = 'SET';

	var _initActions = /*#__PURE__*/new WeakSet();

	var _initSelector = /*#__PURE__*/new WeakSet();

	var _initBarcode = /*#__PURE__*/new WeakSet();

	var _initPriceExtra = /*#__PURE__*/new WeakSet();

	var _initStoreSelector = /*#__PURE__*/new WeakSet();

	var _onStoreFieldChange = /*#__PURE__*/new WeakSet();

	var _getRealValues = /*#__PURE__*/new WeakSet();

	var _getCalculator = /*#__PURE__*/new WeakSet();

	var _handleProductErrorsChange = /*#__PURE__*/new WeakSet();

	var _handleBeforeCreateProduct = /*#__PURE__*/new WeakSet();

	var _handleSpotlightClose = /*#__PURE__*/new WeakSet();

	var _handleBarcodeQrClose = /*#__PURE__*/new WeakSet();

	var _handleBarcodeScannerInstallCheck = /*#__PURE__*/new WeakSet();

	var _handleBarcodeChange = /*#__PURE__*/new WeakSet();

	var _subscribeFieldToValidator = /*#__PURE__*/new WeakSet();

	var _isProductCountCorrect = /*#__PURE__*/new WeakSet();

	var _getNodeChildByDataName = /*#__PURE__*/new WeakSet();

	var _needInventory = /*#__PURE__*/new WeakSet();

	var _needBarcode = /*#__PURE__*/new WeakSet();

	var _isRowAccessDenied = /*#__PURE__*/new WeakSet();

	var _hideFields = /*#__PURE__*/new WeakSet();

	var _isPurchasingPriceAccessDenied = /*#__PURE__*/new WeakSet();

	var _hidePurchasingPrice = /*#__PURE__*/new WeakSet();

	var Row = /*#__PURE__*/function () {
	  function Row(id, fields, settings, editor) {
	    babelHelpers.classCallCheck(this, Row);

	    _classPrivateMethodInitSpec(this, _hidePurchasingPrice);

	    _classPrivateMethodInitSpec(this, _isPurchasingPriceAccessDenied);

	    _classPrivateMethodInitSpec(this, _hideFields);

	    _classPrivateMethodInitSpec(this, _isRowAccessDenied);

	    _classPrivateMethodInitSpec(this, _needBarcode);

	    _classPrivateMethodInitSpec(this, _needInventory);

	    _classPrivateMethodInitSpec(this, _getNodeChildByDataName);

	    _classPrivateMethodInitSpec(this, _isProductCountCorrect);

	    _classPrivateMethodInitSpec(this, _subscribeFieldToValidator);

	    _classPrivateMethodInitSpec(this, _handleBarcodeChange);

	    _classPrivateMethodInitSpec(this, _handleBarcodeScannerInstallCheck);

	    _classPrivateMethodInitSpec(this, _handleBarcodeQrClose);

	    _classPrivateMethodInitSpec(this, _handleSpotlightClose);

	    _classPrivateMethodInitSpec(this, _handleBeforeCreateProduct);

	    _classPrivateMethodInitSpec(this, _handleProductErrorsChange);

	    _classPrivateMethodInitSpec(this, _getCalculator);

	    _classPrivateMethodInitSpec(this, _getRealValues);

	    _classPrivateMethodInitSpec(this, _onStoreFieldChange);

	    _classPrivateMethodInitSpec(this, _initStoreSelector);

	    _classPrivateMethodInitSpec(this, _initPriceExtra);

	    _classPrivateMethodInitSpec(this, _initBarcode);

	    _classPrivateMethodInitSpec(this, _initSelector);

	    _classPrivateMethodInitSpec(this, _initActions);

	    babelHelpers.defineProperty(this, "fields", {});
	    babelHelpers.defineProperty(this, "storeSelectors", []);
	    babelHelpers.defineProperty(this, "externalActions", []);
	    babelHelpers.defineProperty(this, "cache", new main_core.Cache.MemoryCache());
	    babelHelpers.defineProperty(this, "modeChanges", {
	      EDIT: MODE_EDIT,
	      SET: MODE_SET
	    });
	    babelHelpers.defineProperty(this, "validatingFields", new Map());
	    this.setId(id);
	    this.setSettings(settings);
	    this.setEditor(editor);
	    this.setModel(fields, settings);
	    this.initFields(fields);

	    _classPrivateMethodGet(this, _initSelector, _initSelector2).call(this);

	    _classPrivateMethodGet(this, _initBarcode, _initBarcode2).call(this); // this.#initPriceExtra();


	    _classPrivateMethodGet(this, _initStoreSelector, _initStoreSelector2).call(this, this.getSettingValue('storeHeaderMap', {}));

	    _classPrivateMethodGet(this, _initActions, _initActions2).call(this);

	    _classPrivateMethodGet(this, _hideFields, _hideFields2).call(this);

	    requestAnimationFrame(this.initHandlers.bind(this));
	  }

	  babelHelpers.createClass(Row, [{
	    key: "getNode",
	    value: function getNode() {
	      var _this = this;

	      return this.cache.remember('node', function () {
	        var rowId = _this.getField('ID', 0);

	        return _this.getEditorContainer().querySelector('[data-id="' + rowId + '"]');
	      });
	    }
	  }, {
	    key: "getSelector",
	    value: function getSelector() {
	      return this.mainSelector;
	    }
	  }, {
	    key: "getBarcodeSelector",
	    value: function getBarcodeSelector() {
	      return this.barcodeSelector;
	    }
	  }, {
	    key: "getId",
	    value: function getId() {
	      return this.id;
	    }
	  }, {
	    key: "setId",
	    value: function setId(id) {
	      this.id = id;
	    }
	  }, {
	    key: "getSettings",
	    value: function getSettings() {
	      return this.settings;
	    }
	  }, {
	    key: "setSettings",
	    value: function setSettings(settings) {
	      this.settings = main_core.Type.isPlainObject(settings) ? settings : {};
	    }
	  }, {
	    key: "getSettingValue",
	    value: function getSettingValue(name, defaultValue) {
	      return this.settings.hasOwnProperty(name) ? this.settings[name] : defaultValue;
	    }
	  }, {
	    key: "setSettingValue",
	    value: function setSettingValue(name, value) {
	      this.settings[name] = value;
	    }
	  }, {
	    key: "setEditor",
	    value: function setEditor(editor) {
	      this.editor = editor;
	    }
	  }, {
	    key: "getEditor",
	    value: function getEditor() {
	      return this.editor;
	    }
	  }, {
	    key: "getEditorContainer",
	    value: function getEditorContainer() {
	      return this.getEditor().getContainer();
	    }
	  }, {
	    key: "getHintPopup",
	    value: function getHintPopup() {
	      return this.getEditor().getHintPopup();
	    }
	  }, {
	    key: "initHandlers",
	    value: function initHandlers() {
	      var editor = this.getEditor();
	      this.getNode().querySelectorAll('input').forEach(function (node) {
	        main_core.Event.bind(node, 'input', editor.changeProductFieldHandler);
	        main_core.Event.bind(node, 'change', editor.changeProductFieldHandler); // disable drag-n-drop events for text fields

	        main_core.Event.bind(node, 'mousedown', function (event) {
	          return event.stopPropagation();
	        });
	        main_core.Event.bind(node, 'blur', editor.blurProductFieldHandler);
	      });
	      this.getNode().querySelectorAll('select').forEach(function (node) {
	        main_core.Event.bind(node, 'change', editor.changeProductFieldHandler); // disable drag-n-drop events for select fields

	        main_core.Event.bind(node, 'mousedown', function (event) {
	          return event.stopPropagation();
	        });
	      });
	    }
	  }, {
	    key: "initHandlersForSelectors",
	    value: function initHandlersForSelectors() {
	      var _this2 = this;

	      var editor = this.getEditor();
	      var selectorNames = ['MAIN_INFO', 'BARCODE_INFO'];
	      var storeFields = this.getSettingValue('storeHeaderMap', {});
	      selectorNames = [].concat(babelHelpers.toConsumableArray(selectorNames), babelHelpers.toConsumableArray(Object.keys(storeFields)));
	      selectorNames.forEach(function (name) {
	        _this2.getNode().querySelectorAll('[data-name="' + name + '"] input[type="text"]').forEach(function (node) {
	          main_core.Event.bind(node, 'input', editor.changeProductFieldHandler);
	          main_core.Event.bind(node, 'change', editor.changeProductFieldHandler); // disable drag-n-drop events for select fields

	          main_core.Event.bind(node, 'mousedown', function (event) {
	            return event.stopPropagation();
	          });
	        });
	      });
	    }
	  }, {
	    key: "layoutBarcode",
	    value: function layoutBarcode() {
	      var barcodeWrapper = this.getNode().querySelector('[data-name="BARCODE_INFO"]');

	      if (this.barcodeSelector && barcodeWrapper) {
	        barcodeWrapper.innerHTML = '';

	        if (_classPrivateMethodGet(this, _needBarcode, _needBarcode2).call(this)) {
	          this.barcodeSelector.renderTo(barcodeWrapper);
	        }
	      }
	    }
	  }, {
	    key: "layoutStoreSelector",
	    value: function layoutStoreSelector(fieldNames) {
	      var _this3 = this;

	      Object.keys(fieldNames).forEach(function (rowName) {
	        var selectorId = _this3.getId() + '_' + rowName;

	        _this3.storeSelectors.forEach(function (selector) {
	          if (selector.getId() === selectorId) {
	            var storeWrapper = _this3.getNode().querySelector('[data-name="' + rowName + '"]');

	            if (storeWrapper) {
	              storeWrapper.innerHTML = '';

	              if (_classPrivateMethodGet(_this3, _needInventory, _needInventory2).call(_this3)) {
	                selector.renderTo(storeWrapper);
	              }
	            }
	          }
	        });
	      });
	    }
	  }, {
	    key: "setRowNumber",
	    value: function setRowNumber(number) {
	      this.getNode().querySelectorAll('.main-grid-row-number').forEach(function (node) {
	        node.textContent = number + '.';
	      });
	    }
	  }, {
	    key: "getFields",
	    value: function getFields() {
	      var fields = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : [];
	      var result;

	      if (!main_core.Type.isArrayFilled(fields)) {
	        result = main_core.Runtime.clone(this.fields);
	      } else {
	        result = {};

	        var _iterator = _createForOfIteratorHelper(fields),
	            _step;

	        try {
	          for (_iterator.s(); !(_step = _iterator.n()).done;) {
	            var fieldName = _step.value;
	            result[fieldName] = this.getField(fieldName);
	          }
	        } catch (err) {
	          _iterator.e(err);
	        } finally {
	          _iterator.f();
	        }
	      } // merge with real values


	      var realValues = _classPrivateMethodGet(this, _getRealValues, _getRealValues2).call(this);

	      for (var _fieldName in realValues) {
	        if (Object.hasOwnProperty.call(realValues, _fieldName) && Object.hasOwnProperty.call(result, _fieldName)) {
	          result[_fieldName] = realValues[_fieldName];
	        }
	      }

	      return result;
	    }
	    /**
	     * Get real values field.
	     *
	     * Stores the real values of rows that are hidden due to lack of user access.
	     *
	     * @returns
	     */

	  }, {
	    key: "initFields",
	    value: function initFields(fields) {
	      this.getModel().initFields(fields, false);
	      this.setFields(fields);
	    }
	  }, {
	    key: "setFields",
	    value: function setFields(fields) {
	      for (var name in fields) {
	        if (fields.hasOwnProperty(name)) {
	          this.setField(name, fields[name]);
	        }
	      }
	    }
	  }, {
	    key: "getField",
	    value: function getField(name, defaultValue) {
	      if (name !== 'REAL_VALUES') {
	        var realValues = _classPrivateMethodGet(this, _getRealValues, _getRealValues2).call(this);

	        if (realValues && Object.hasOwnProperty.call(realValues, name)) {
	          return realValues[name];
	        }
	      }

	      return this.fields.hasOwnProperty(name) ? this.fields[name] : defaultValue;
	    }
	  }, {
	    key: "setField",
	    value: function setField(name, value) {
	      var changeModel = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : true;
	      this.fields[name] = value;

	      if (changeModel) {
	        this.getModel().setField(name, value);
	      }
	    }
	  }, {
	    key: "getUiFieldId",
	    value: function getUiFieldId(field) {
	      return this.getId() + '_' + field;
	    }
	  }, {
	    key: "getBasePrice",
	    value: function getBasePrice() {
	      return this.getField('BASE_PRICE', 0);
	    }
	  }, {
	    key: "getAmount",
	    value: function getAmount() {
	      return this.getField('AMOUNT', 1);
	    }
	  }, {
	    key: "updateFieldByEvent",
	    value: function updateFieldByEvent(fieldCode, event) {
	      var target = event.target;
	      var value = target.type === 'checkbox' ? target.checked : target.value;
	      var mode = event.type === 'input' || event.type === 'change' ? MODE_EDIT : MODE_SET;
	      this.updateField(fieldCode, value, mode);
	    }
	  }, {
	    key: "updateDropdownField",
	    value: function updateDropdownField(fieldCode, value) {
	      this.updateField(fieldCode, value, MODE_EDIT);
	    }
	  }, {
	    key: "updateField",
	    value: function updateField(fieldCode, value) {
	      var mode = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : MODE_SET;
	      this.resetExternalActions();
	      this.updateFieldValue(fieldCode, value, mode);
	      this.executeExternalActions();
	    }
	  }, {
	    key: "updateFieldValue",
	    value: function updateFieldValue(code, value) {
	      var mode = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : MODE_SET;

	      switch (code) {
	        case 'SKU_ID':
	          this.changeProductId(value);
	          break;

	        case 'BASE_PRICE':
	          this.changeBasePrice(value, mode);
	          break;
	        // case 'BASE_PRICE_EXTRA':
	        // 	this.changeExtra(value, mode);
	        // 	break;

	        case 'PURCHASING_PRICE':
	          this.changePurchasingPrice(value, mode);
	          break;

	        case 'AMOUNT':
	          this.changeAmount(value, mode);
	          break;

	        case 'MEASURE_CODE':
	          this.changeMeasureCode(value, mode);
	          break;

	        case 'BARCODE':
	          this.changeBarcode(value, mode);
	          break;

	        case 'STORE_FROM':
	        case 'STORE_TO':
	          this.changeStore(value, code);
	          break;

	        case 'STORE_FROM_TITLE':
	        case 'STORE_TO_TITLE':
	          this.changeStoreName(value, code);
	          break;

	        case 'NAME':
	        case 'MAIN_INFO':
	          this.changeProductName(value, mode);
	          break;

	        case 'SORT':
	          this.changeSort(value, mode);
	          break;
	      }
	    }
	  }, {
	    key: "updateFieldByName",
	    value: function updateFieldByName(field, value) {
	      switch (field) {
	        case 'TAX_INCLUDED':
	          this.setTaxIncluded(value);
	          break;
	      }
	    }
	  }, {
	    key: "changeProductId",
	    value: function changeProductId(value) {
	      var preparedValue = this.parseInt(value);
	      this.setProductId(preparedValue);
	    }
	  }, {
	    key: "handleCopyAction",
	    value: function handleCopyAction(event, menuItem) {
	      var _this$getEditor;

	      (_this$getEditor = this.getEditor()) === null || _this$getEditor === void 0 ? void 0 : _this$getEditor.copyRow(this);
	      var menu = menuItem.getMenuWindow();

	      if (menu) {
	        menu.destroy();
	      }
	    }
	  }, {
	    key: "handleDeleteAction",
	    value: function handleDeleteAction(event, menuItem) {
	      var _this$getEditor2;

	      (_this$getEditor2 = this.getEditor()) === null || _this$getEditor2 === void 0 ? void 0 : _this$getEditor2.deleteRow(this);
	      var menu = menuItem.getMenuWindow();

	      if (menu) {
	        menu.destroy();
	      }

	      this.unsubscribeEvents();

	      _classPrivateMethodGet(this, _handleProductErrorsChange, _handleProductErrorsChange2).call(this);
	    }
	  }, {
	    key: "unsubscribeEvents",
	    value: function unsubscribeEvents() {
	      this.getBarcodeSelector().unsubscribeEvents();
	    }
	  }, {
	    key: "handleSelectExtraPriceType",
	    value: function handleSelectExtraPriceType(event, menuItem) {
	      this.changeExtraType(menuItem.type, MODE_EDIT);
	      var menu = menuItem.getMenuWindow();

	      if (menu) {
	        menu.destroy();
	      }
	    }
	  }, {
	    key: "changeExtraType",
	    value: function changeExtraType(value) {
	      var mode = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : MODE_SET;
	      var text = '%';

	      if (value === PriceCalculator.EXTRA_TYPE_MONETARY) {
	        text = this.getEditor().getCurrencyText();
	      } else {
	        value = PriceCalculator.EXTRA_TYPE_PERCENTAGE;
	      }

	      if (value === this.getField('BASE_PRICE_EXTRA_RATE')) {
	        return;
	      }

	      if (mode === MODE_EDIT) {
	        var calculator = _classPrivateMethodGet(this, _getCalculator, _getCalculator2).call(this).calculateExtraType(value);

	        this.changeExtra(calculator.getExtra());
	        this.changeBasePrice(calculator.getFinalPrice());
	      }

	      var node = this.getNode().querySelector('[data-name="BASE_PRICE_EXTRA_RATE"]');

	      if (main_core.Type.isDomNode(node)) {
	        node.innerHTML = text;
	      }

	      this.setField('BASE_PRICE_EXTRA_RATE', value);
	    }
	  }, {
	    key: "changeExtra",
	    value: function changeExtra(value) {
	      var mode = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : MODE_SET;
	      var preparedValue = main_core.Type.isNil(value) || value === '' ? null : this.parseFloat(value, this.getPricePrecision());
	      this.setField('BASE_PRICE_EXTRA', preparedValue);

	      if (preparedValue === null) {
	        return;
	      }

	      if (mode === MODE_EDIT) {
	        var calculator = _classPrivateMethodGet(this, _getCalculator, _getCalculator2).call(this).calculateExtra(preparedValue);

	        this.changeBasePrice(calculator.getFinalPrice());
	      } else {
	        var node = this.getNode().querySelector('[data-name="BASE_PRICE_EXTRA"]');

	        if (main_core.Type.isDomNode(node)) {
	          node.value = preparedValue;
	        }
	      }
	    }
	  }, {
	    key: "changeBasePrice",
	    value: function changeBasePrice(value) {
	      var mode = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : MODE_SET;
	      var preparedValue = this.parseFloat(value, this.getPricePrecision());
	      this.setBasePrice(preparedValue, mode); // if (mode === MODE_EDIT)
	      // {
	      // 	const calculator =
	      // 		this.#getCalculator()
	      // 			.calculateFinalPrice(preparedValue)
	      // 	;
	      //
	      // 	this.changeExtra(calculator.getExtra());
	      // 	this.changeExtraType(calculator.getExtraType());
	      // }
	    }
	  }, {
	    key: "changePurchasingPrice",
	    value: function changePurchasingPrice(value) {
	      var mode = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : MODE_SET;

	      if (_classPrivateMethodGet(this, _isPurchasingPriceAccessDenied, _isPurchasingPriceAccessDenied2).call(this)) {
	        return;
	      }

	      var preparedValue = this.parseFloat(value, this.getPricePrecision());
	      this.setPurchasingPrice(preparedValue, mode); // const currentExtra = this.getField('BASE_PRICE_EXTRA');
	      // if (mode === MODE_EDIT && !Type.isNil(currentExtra) && currentExtra !== '')
	      // {
	      // 	const calculator =
	      // 		this.#getCalculator()
	      // 			.calculateBasePrice(preparedValue)
	      // 	;
	      //
	      // 	this.changeBasePrice(calculator.getFinalPrice());
	      // }
	    }
	  }, {
	    key: "changeAmount",
	    value: function changeAmount(value) {
	      var mode = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : MODE_SET;
	      var preparedValue = this.parseFloat(value, this.getQuantityPrecision());
	      this.setAmount(preparedValue, mode);
	    }
	  }, {
	    key: "changeMeasureCode",
	    value: function changeMeasureCode(value) {
	      var _this4 = this;

	      var mode = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : MODE_SET;
	      this.getEditor().getMeasures().filter(function (item) {
	        return item.CODE === value;
	      }).forEach(function (item) {
	        return _this4.setMeasure(item, mode);
	      });
	    }
	  }, {
	    key: "changeBarcode",
	    value: function changeBarcode(value) {
	      var mode = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : MODE_SET;
	      var preparedValue = value.toString();
	      var isChangedValue = this.getField('BARCODE') !== preparedValue;

	      if (isChangedValue && mode === MODE_SET) {
	        this.setField('BARCODE', preparedValue);
	        this.setField('DOC_BARCODE', preparedValue);
	        this.addActionProductChange();
	      } else if (mode === MODE_EDIT) {
	        this.setField('DOC_BARCODE', preparedValue);
	        this.addActionProductChange();
	      }
	    }
	  }, {
	    key: "changeStore",
	    value: function changeStore(value, code) {
	      var preparedValue = main_core.Text.toNumber(value);
	      var isChangedValue = this.getField(code) !== preparedValue;

	      if (isChangedValue) {
	        this.setField(code, preparedValue);
	        this.setStoreAmount(value, code);
	        this.layoutStoreSelector(this.getSettingValue('storeHeaderMap', {}));
	        this.addActionProductChange();
	      }
	    }
	  }, {
	    key: "changeStoreName",
	    value: function changeStoreName(value, code) {
	      var preparedValue = value.toString();
	      this.setField(code, preparedValue);
	      this.addActionProductChange();
	    }
	  }, {
	    key: "changeProductName",
	    value: function changeProductName(value) {
	      var mode = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : MODE_SET;
	      var preparedValue = value.toString();
	      var isChangedValue = this.getField('NAME') !== preparedValue;

	      if (isChangedValue && mode === MODE_SET) {
	        this.setField('NAME', preparedValue);
	        this.addActionProductChange();
	      }
	    }
	  }, {
	    key: "changeSort",
	    value: function changeSort(value) {
	      var mode = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : MODE_SET;
	      var preparedValue = this.parseInt(value);

	      if (mode === MODE_SET) {
	        this.setField('SORT', preparedValue);
	      }

	      var isChangedValue = this.getField('SORT') !== preparedValue;

	      if (isChangedValue) {
	        this.addActionProductChange();
	      }
	    }
	  }, {
	    key: "refreshFieldsLayout",
	    value: function refreshFieldsLayout() {
	      var _this$getSelector, _this$getSelector2, _this$getBarcodeSelec;

	      var exceptFields = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : [];

	      for (var field in this.fields) {
	        if (this.fields.hasOwnProperty(field) && !exceptFields.includes(field)) {
	          this.updateUiField(field, this.fields[field]);
	        }
	      }

	      this.updateUiMeasure(this.getField('MEASURE_CODE'), this.getField('MEASURE_NAME'));
	      (_this$getSelector = this.getSelector()) === null || _this$getSelector === void 0 ? void 0 : _this$getSelector.reloadFileInput();
	      (_this$getSelector2 = this.getSelector()) === null || _this$getSelector2 === void 0 ? void 0 : _this$getSelector2.layout();
	      (_this$getBarcodeSelec = this.getBarcodeSelector()) === null || _this$getBarcodeSelec === void 0 ? void 0 : _this$getBarcodeSelec.layout();
	      this.updateUiStoreValues();
	    }
	  }, {
	    key: "setModel",
	    value: function setModel() {
	      var fields = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};
	      var selectorId = 'catalog_document_grid_' + this.getId();

	      if (selectorId) {
	        var model = catalog_productModel.ProductModel.getById(selectorId);

	        if (model) {
	          this.model = model;
	        }
	      }

	      if (!(this.model instanceof catalog_productModel.ProductModel)) {
	        this.model = new catalog_productModel.ProductModel({
	          id: selectorId,
	          currency: this.getEditor().getCurrencyId(),
	          iblockId: fields['IBLOCK_ID'],
	          basePriceId: fields['BASE_PRICE_ID'],
	          skuTree: main_core.Type.isStringFilled(fields['SKU_TREE']) ? JSON.parse(fields['SKU_TREE']) : null,
	          storeMap: fields['STORE_AMOUNT_MAP'],
	          fields: fields
	        });

	        if (main_core.Type.isObject(fields['IMAGE_INFO'])) {
	          this.model.getImageCollection().setPreview(fields['IMAGE_INFO']['preview']);
	          this.model.getImageCollection().setEditInput(fields['IMAGE_INFO']['input']);
	          this.model.getImageCollection().setMorePhotoValues(fields['IMAGE_INFO']['values']);
	        }

	        if (!main_core.Type.isNil(fields['DETAIL_URL'])) {
	          this.model.setDetailPath(fields['DETAIL_URL']);
	        }
	      }

	      main_core_events.EventEmitter.subscribe(this.model, 'onErrorsChange', main_core.Runtime.debounce(_classPrivateMethodGet(this, _handleProductErrorsChange, _handleProductErrorsChange2), 500, this));
	      main_core_events.EventEmitter.subscribe(this.model, 'onChangeStoreData', this.updateUiStoreValues.bind(this));
	    }
	  }, {
	    key: "getModel",
	    value: function getModel() {
	      return this.model;
	    }
	  }, {
	    key: "setProductId",
	    value: function setProductId(value) {
	      var isChangedValue = this.getField('PRODUCT_ID') !== value;

	      if (isChangedValue) {
	        this.setField('PRODUCT_ID', value);
	        this.setField('SKU_ID', value);
	        this.updateUiStoreValues();
	        this.addActionProductChange();
	        this.addActionUpdateTotal();

	        _classPrivateMethodGet(this, _hidePurchasingPrice, _hidePurchasingPrice2).call(this);
	      }
	    }
	  }, {
	    key: "setBasePrice",
	    value: function setBasePrice(value) {
	      var mode = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : MODE_SET;
	      // price can't be less than zero
	      value = Math.max(value, 0);

	      if (mode === MODE_SET) {
	        this.updateUiField('BASE_PRICE', value.toFixed(this.getPricePrecision()));
	      }

	      this.setField('BASE_PRICE', value);
	      this.addActionProductChange();
	      this.addActionUpdateTotal();
	      this.updateRowTotalPrice();
	    }
	  }, {
	    key: "updateRowTotalPrice",
	    value: function updateRowTotalPrice() {
	      var field = this.getEditor().getSettingValue('totalCalculationSumField', 'PURCHASING_PRICE');
	      var value = this.getAmount() * this.getField(field, 0);
	      value = Math.max(value, 0);
	      this.setField('TOTAL_PRICE', value);
	      this.updateUiField('TOTAL_PRICE', value.toFixed(this.getPricePrecision()));
	    }
	  }, {
	    key: "updateProductStoreValues",
	    value: function updateProductStoreValues() {
	      var _this5 = this;

	      this.storeSelectors.forEach(function (selector) {
	        selector.setProductId(_this5.getModel().getSkuId());
	      });
	    }
	  }, {
	    key: "updateUiStoreValues",
	    value: function updateUiStoreValues() {
	      var _this6 = this;

	      var storeHeaderMap = this.getSettingValue('storeHeaderMap', {});
	      Object.keys(storeHeaderMap).forEach(function (key) {
	        var fieldName = storeHeaderMap[key];

	        var value = _this6.getField(fieldName);

	        if (fieldName === 'STORE_FROM') {
	          var currentAmount = _this6.model.getStoreCollection().getStoreAmount(value);

	          if (currentAmount <= 0) {
	            var maxStore = _this6.model.getStoreCollection().getMaxFilledStore();

	            var storeSelector = catalog_storeSelector.StoreSelector.getById(_this6.getId() + '_' + key);

	            if (maxStore.AMOUNT > currentAmount && storeSelector) {
	              storeSelector.onStoreSelect(maxStore.STORE_ID, maxStore.STORE_TITLE);
	              value = maxStore.STORE_ID;
	            }
	          }
	        }

	        _this6.setStoreAmount(value, fieldName);
	      });
	      this.layoutStoreSelector(this.getSettingValue('storeHeaderMap', {}));
	    }
	  }, {
	    key: "setStoreAmount",
	    value: function setStoreAmount(value, fieldName) {
	      var _this7 = this;

	      var mode = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : MODE_SET;

	      if (!this.model.getStoreCollection().isInited()) {
	        return;
	      } // price can't be less than zero


	      if (mode === MODE_SET) {
	        var amount;
	        var amounts = {
	          '_AMOUNT': function _AMOUNT() {
	            return _this7.model.getStoreCollection().getStoreAmount(value);
	          },
	          '_RESERVED': function _RESERVED() {
	            return _this7.model.getStoreCollection().getStoreReserved(value);
	          },
	          '_AVAILABLE_AMOUNT': function _AVAILABLE_AMOUNT() {
	            return _this7.model.getStoreCollection().getStoreAvailableAmount(value);
	          }
	        };

	        for (var postfix in amounts) {
	          if (Object.hasOwnProperty.call(amounts, postfix)) {
	            var wrapper = _classPrivateMethodGet(this, _getNodeChildByDataName, _getNodeChildByDataName2).call(this, fieldName + postfix);

	            if (wrapper) {
	              wrapper.innerHTML = '';

	              if (_classPrivateMethodGet(this, _needInventory, _needInventory2).call(this)) {
	                amount = amounts[postfix]() || 0;
	                var amountWithMeasure = amount + ' ' + main_core.Text.encode(this.getField('MEASURE_NAME'));
	                var htmlAmount = amountWithMeasure;

	                if (postfix === '_AVAILABLE_AMOUNT') {
	                  htmlAmount = amount > 0 ? amountWithMeasure : "<span class=\"text--danger\">".concat(amountWithMeasure, "</span>");
	                }

	                wrapper.innerHTML = htmlAmount;
	              }
	            }
	          }
	        }
	      }
	    }
	  }, {
	    key: "setPurchasingPrice",
	    value: function setPurchasingPrice(value) {
	      var mode = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : MODE_SET;

	      if (_classPrivateMethodGet(this, _isPurchasingPriceAccessDenied, _isPurchasingPriceAccessDenied2).call(this)) {
	        return;
	      } // price can't be less than zero


	      value = Math.max(value, 0);

	      if (mode === MODE_SET) {
	        this.updateUiField('PURCHASING_PRICE', value.toFixed(this.getPricePrecision()));
	      }

	      this.setField('PURCHASING_PRICE', value);
	      this.addActionProductChange();
	      this.addActionUpdateTotal();
	      this.updateRowTotalPrice();
	    }
	  }, {
	    key: "setAmount",
	    value: function setAmount(value) {
	      var mode = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : MODE_SET;

	      if (mode === MODE_SET) {
	        this.updateUiInputField('AMOUNT', value);
	      }

	      var isChangedValue = this.getField('AMOUNT') !== value;

	      if (isChangedValue) {
	        this.setField('AMOUNT', value);
	        this.addActionProductChange();
	        this.addActionUpdateTotal();
	        this.updateRowTotalPrice();
	      }
	    }
	  }, {
	    key: "setMeasure",
	    value: function setMeasure(measure) {
	      var _this8 = this;

	      var mode = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : MODE_SET;

	      if (this.model.isEmpty()) {
	        this.setField('MEASURE_CODE', measure.CODE);
	        this.setField('MEASURE_NAME', measure.SYMBOL);
	        this.updateUiMeasure(measure.CODE, measure.SYMBOL);
	        return;
	      }

	      if (mode === MODE_EDIT) {
	        this.getModel().showSaveNotifier('measureChanger_' + this.getId(), {
	          title: main_core.Loc.getMessage('CATALOG_PRODUCT_MODEL_SAVING_NOTIFICATION_MEASURE_CHANGED_QUERY'),
	          declineCancelTitle: main_core.Loc.getMessage('CATALOG_PRODUCT_MODEL_SAVING_NOTIFICATION_DECLINE_SAVE'),
	          events: {
	            onSave: function onSave() {
	              _this8.setField('MEASURE_CODE', measure.CODE);

	              _this8.setField('MEASURE_NAME', measure.SYMBOL);

	              _this8.updateUiMeasure(_this8.getField('MEASURE_CODE'), _this8.getField('MEASURE_NAME'));

	              _this8.getModel().save(['MEASURE_CODE', 'MEASURE_NAME']);
	            },
	            onCancel: function onCancel() {
	              _this8.updateUiMeasure(_this8.getField('MEASURE_CODE'), _this8.getField('MEASURE_NAME'));
	            }
	          }
	        });
	      } else {
	        this.updateUiMeasure(measure.CODE, measure.SYMBOL);
	      }

	      this.addActionProductChange();
	    } // controls

	  }, {
	    key: "getInputByFieldName",
	    value: function getInputByFieldName(fieldName) {
	      var fieldId = this.getUiFieldId(fieldName);
	      var item = document.getElementById(fieldId);

	      if (!main_core.Type.isElementNode(item)) {
	        item = this.getNode().querySelector('[name="' + fieldId + '"]');
	      }

	      return item;
	    }
	  }, {
	    key: "getInputWrapperByFieldName",
	    value: function getInputWrapperByFieldName(fieldName) {
	      var inputBlock = this.getInputByFieldName(fieldName);

	      if (main_core.Type.isElementNode(inputBlock)) {
	        return main_core.Type.isElementNode(inputBlock.parentNode) ? inputBlock.parentNode : inputBlock;
	      }

	      return undefined;
	    }
	  }, {
	    key: "updateUiInputField",
	    value: function updateUiInputField(name, value) {
	      var item = this.getInputByFieldName(name);

	      if (main_core.Type.isElementNode(item)) {
	        item.value = value;
	      }
	    }
	  }, {
	    key: "updateUiCheckboxField",
	    value: function updateUiCheckboxField(name, value) {
	      var item = this.getInputByFieldName(name);

	      if (main_core.Type.isElementNode(item)) {
	        item.checked = value === 'Y';
	      }
	    }
	  }, {
	    key: "getMoneyFieldDropdownApi",
	    value: function getMoneyFieldDropdownApi(name) {
	      if (!main_core.Reflection.getClass('BX.Main.dropdownManager')) {
	        return null;
	      }

	      return BX.Main.dropdownManager.getById(this.getId() + '_' + name + '_control');
	    }
	  }, {
	    key: "updateMoneyFieldUiWithDropdownApi",
	    value: function updateMoneyFieldUiWithDropdownApi(dropdown, value) {
	      if (dropdown.getValue() === value) {
	        return;
	      }

	      if (dropdown.menu) {
	        dropdown.menu.destroy();
	      }

	      var item = dropdown.menu.itemsContainer.querySelector('[data-value="' + value + '"]');
	      var menuItem = item && dropdown.getMenuItem(item);

	      if (menuItem) {
	        dropdown.refresh(menuItem);
	        dropdown.selectItem(menuItem);
	      }
	    }
	  }, {
	    key: "updateUiMoneyField",
	    value: function updateUiMoneyField(name, value, text) {
	      var item = this.getInputByFieldName(name);

	      if (!main_core.Type.isElementNode(item)) {
	        return;
	      }

	      item.dataset.value = value;
	      var span = item.querySelector('span.main-dropdown-inner');

	      if (!main_core.Type.isElementNode(span)) {
	        return;
	      }

	      span.innerHTML = text;
	    }
	  }, {
	    key: "updateUiMeasure",
	    value: function updateUiMeasure(code, name) {
	      this.updateUiMoneyField('MEASURE_CODE', code, main_core.Text.encode(name));
	      this.updateUiStoreValues();
	    }
	  }, {
	    key: "updateUiHtmlField",
	    value: function updateUiHtmlField(name, html) {
	      var item = this.getNode().querySelector('[data-name="' + name + '"]');

	      if (main_core.Type.isElementNode(item)) {
	        item.innerHTML = html;
	      }
	    }
	  }, {
	    key: "updateUiCurrencyFields",
	    value: function updateUiCurrencyFields() {
	      var _this9 = this;

	      var currencyText = this.getEditor().getCurrencyText();
	      var currencyId = '' + this.getEditor().getCurrencyId();
	      var currencyFieldNames = ['BASE_PRICE_CURRENCY', 'PURCHASING_PRICE_CURRENCY'];
	      currencyFieldNames.forEach(function (name) {
	        var dropdownValues = [];
	        dropdownValues.push({
	          NAME: currencyText,
	          VALUE: currencyId
	        });
	        main_core.Dom.attr(_this9.getInputByFieldName(name), 'data-items', dropdownValues);

	        _this9.updateUiMoneyField(name, currencyId, currencyText);
	      });
	    }
	  }, {
	    key: "updateUiField",
	    value: function updateUiField(field, value) {
	      var uiName = this.getUiFieldName(field);

	      if (!uiName) {
	        return;
	      }

	      var uiType = this.getUiFieldType(field);

	      if (!uiType) {
	        return;
	      }

	      switch (uiType) {
	        case 'input':
	          this.updateUiInputField(uiName, value);
	          break;

	        case 'money':
	          value = BX.util.number_format(value, this.getPricePrecision(), ".", "");
	          this.updateUiInputField(uiName, value);
	          break;

	        case 'money_html':
	          value = currency_currencyCore.CurrencyCore.currencyFormat(value, this.getEditor().getCurrencyId(), true);
	          this.updateUiHtmlField(uiName, value);
	          break;
	      }
	    }
	  }, {
	    key: "getUiFieldName",
	    value: function getUiFieldName(field) {
	      var result = null;

	      switch (field) {
	        case 'AMOUNT':
	        case 'MEASURE_CODE':
	        case 'BASE_PRICE':
	        case 'PURCHASING_PRICE':
	        case 'TOTAL_PRICE':
	          result = field;
	          break;
	      }

	      return result;
	    }
	  }, {
	    key: "getUiFieldType",
	    value: function getUiFieldType(field) {
	      var moneyFields = ['BASE_PRICE', 'PURCHASING_PRICE', 'TOTAL_PRICE'];

	      if (moneyFields.includes(field)) {
	        var _this$getEditor3, _column$editable;

	        var column = (_this$getEditor3 = this.getEditor()) === null || _this$getEditor3 === void 0 ? void 0 : _this$getEditor3.getColumnInfo(field);

	        if ((column === null || column === void 0 ? void 0 : (_column$editable = column.editable) === null || _column$editable === void 0 ? void 0 : _column$editable.TYPE) === 'MONEY') {
	          return 'money';
	        }

	        return 'money_html';
	      } else if (field === 'AMOUNT') {
	        return 'input';
	      }

	      return null;
	    } // proxy

	  }, {
	    key: "parseInt",
	    value: function parseInt(value) {
	      var defaultValue = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : 0;
	      return this.getEditor().parseInt(value, defaultValue);
	    }
	  }, {
	    key: "parseFloat",
	    value: function parseFloat(value, precision) {
	      var defaultValue = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : 0;
	      return this.getEditor().parseFloat(value, precision, defaultValue);
	    }
	  }, {
	    key: "getPricePrecision",
	    value: function getPricePrecision() {
	      return this.getEditor().getPricePrecision();
	    }
	  }, {
	    key: "getQuantityPrecision",
	    value: function getQuantityPrecision() {
	      return this.getEditor().getQuantityPrecision();
	    }
	  }, {
	    key: "getCommonPrecision",
	    value: function getCommonPrecision() {
	      return this.getEditor().getCommonPrecision();
	    }
	  }, {
	    key: "resetExternalActions",
	    value: function resetExternalActions() {
	      this.externalActions.length = 0;
	    }
	  }, {
	    key: "addExternalAction",
	    value: function addExternalAction(action) {
	      this.externalActions.push(action);
	    }
	  }, {
	    key: "addActionProductChange",
	    value: function addActionProductChange() {
	      this.addExternalAction({
	        type: this.getEditor().actions.productChange,
	        id: this.getId()
	      });
	    }
	  }, {
	    key: "addActionUpdateTotal",
	    value: function addActionUpdateTotal() {
	      this.addExternalAction({
	        type: this.getEditor().actions.updateTotal
	      });
	    }
	  }, {
	    key: "executeExternalActions",
	    value: function executeExternalActions() {
	      if (this.externalActions.length === 0) {
	        return;
	      }

	      this.getEditor().executeActions(this.externalActions);
	      this.resetExternalActions();
	    }
	  }, {
	    key: "isEmptyRow",
	    value: function isEmptyRow() {
	      return !main_core.Type.isStringFilled(this.getField('NAME', '').trim()) && this.model.isEmpty() && this.getBasePrice() <= 0;
	    }
	  }, {
	    key: "validate",
	    value: function validate() {
	      var errorsList = [];

	      if (!_classPrivateMethodGet(this, _isProductCountCorrect, _isProductCountCorrect2).call(this, this.getAmount())) {
	        _classPrivateMethodGet(this, _subscribeFieldToValidator, _subscribeFieldToValidator2).call(this, 'AMOUNT', _classPrivateMethodGet(this, _isProductCountCorrect, _isProductCountCorrect2));

	        errorsList.push(main_core.Loc.getMessage('CATALOG_DOCUMENT_PRODUCT_LIST_INVALID_AMOUNT_2'));
	      }

	      return errorsList;
	    }
	  }]);
	  return Row;
	}();

	function _initActions2() {
	  var _this10 = this;

	  if (this.getEditor().isReadOnly() || this.getField('EDITABLE') === false) {
	    return;
	  }

	  var actionCellContentContainer = this.getNode().querySelector('.main-grid-cell-action .main-grid-cell-content');

	  if (main_core.Type.isDomNode(actionCellContentContainer)) {
	    var actionsButton = main_core.Tag.render(_templateObject$2 || (_templateObject$2 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<a\n\t\t\t\t\thref=\"#\"\n\t\t\t\t\tclass=\"main-grid-row-action-button\"\n\t\t\t\t></a>\n\t\t\t"])));
	    main_core.Event.bind(actionsButton, 'click', function (event) {
	      var menuItems = [{
	        text: main_core.Loc.getMessage('CATALOG_DOCUMENT_PRODUCT_LIST_COPY_ACTION'),
	        onclick: _this10.handleCopyAction.bind(_this10)
	      }, {
	        text: main_core.Loc.getMessage('CATALOG_DOCUMENT_PRODUCT_LIST_DELETE_ACTION'),
	        onclick: _this10.handleDeleteAction.bind(_this10)
	      }];
	      main_popup.PopupMenu.show({
	        id: _this10.getId() + '_actions_popup',
	        bindElement: actionsButton,
	        items: menuItems
	      });
	      event.preventDefault();
	      event.stopPropagation();
	    });
	    main_core.Dom.append(actionsButton, actionCellContentContainer);
	  }
	}

	function _initSelector2() {
	  var selectorOptions = {
	    iblockId: this.model.getIblockId(),
	    basePriceId: this.model.getBasePriceId(),
	    currency: this.model.getCurrency(),
	    model: this.model,
	    config: {
	      ENABLE_SEARCH: true,
	      IS_ALLOWED_CREATION_PRODUCT: this.getSettingValue('isAllowedCreationProduct', true),
	      ENABLE_IMAGE_INPUT: true,
	      ROLLBACK_INPUT_AFTER_CANCEL: true,
	      ENABLE_INPUT_DETAIL_LINK: true,
	      ROW_ID: this.getId(),
	      ENABLE_SKU_SELECTION: true,
	      ENABLE_EMPTY_PRODUCT_ERROR: true,
	      RESTRICTED_PRODUCT_TYPES: this.getEditor().getRestrictedProductTypes(),
	      URL_BUILDER_CONTEXT: this.editor.getSettingValue('productUrlBuilderContext')
	    },
	    mode: catalog_productSelector.ProductSelector.MODE_EDIT
	  };
	  this.mainSelector = new catalog_productSelector.ProductSelector('catalog_document_grid_' + this.getId(), selectorOptions);
	  var mainInfoNode = this.getNode().querySelector('[data-name="MAIN_INFO"]');

	  if (mainInfoNode) {
	    var numberSelector = mainInfoNode.querySelector('.main-grid-row-number');

	    if (!main_core.Type.isDomNode(numberSelector)) {
	      mainInfoNode.appendChild(main_core.Tag.render(_templateObject2 || (_templateObject2 = babelHelpers.taggedTemplateLiteral(["<div class=\"main-grid-row-number\"></div>"]))));
	    }

	    var selectorWrapper = mainInfoNode.querySelector('.main-grid-row-product-selector');

	    if (!main_core.Type.isDomNode(selectorWrapper)) {
	      selectorWrapper = main_core.Tag.render(_templateObject3 || (_templateObject3 = babelHelpers.taggedTemplateLiteral(["<div class=\"main-grid-row-product-selector\"></div>"])));
	      mainInfoNode.appendChild(selectorWrapper);
	    }

	    this.mainSelector.renderTo(selectorWrapper);
	  }

	  main_core_events.EventEmitter.subscribe(this.mainSelector, 'onBeforeCreate', _classPrivateMethodGet(this, _handleBeforeCreateProduct, _handleBeforeCreateProduct2).bind(this));
	}

	function _initBarcode2() {
	  var selectorOptions = {
	    iblockId: this.model.getIblockId(),
	    basePriceId: this.model.getBasePriceId(),
	    currency: this.model.getCurrency(),
	    model: this.model,
	    inputFieldName: 'BARCODE',
	    type: catalog_productSelector.ProductSelector.INPUT_FIELD_BARCODE,
	    config: {
	      ENABLE_SEARCH: true,
	      IS_ALLOWED_CREATION_PRODUCT: this.getSettingValue('isAllowedCreationProduct', true),
	      ENABLE_INFO_SPOTLIGHT: this.editor.getSettingValue('showBarcodeSpotlightInfo', true),
	      ENABLE_BARCODE_QR_AUTH: this.editor.getSettingValue('showBarcodeQrAuth', true),
	      IS_INSTALLED_MOBILE_APP: this.editor.getSettingValue('isInstalledMobileApp', null),
	      ENABLE_IMAGE_INPUT: false,
	      ROLLBACK_INPUT_AFTER_CANCEL: true,
	      ENABLE_INPUT_DETAIL_LINK: false,
	      ROW_ID: this.getId(),
	      ENABLE_SKU_SELECTION: false,
	      ENABLE_SKU_TREE: false,
	      ENABLE_EMPTY_PRODUCT_ERROR: false,
	      RESTRICTED_PRODUCT_TYPES: this.getEditor().getRestrictedProductTypes()
	    },
	    mode: catalog_productSelector.ProductSelector.MODE_EDIT,
	    scannerToken: this.getEditor().scannerToken
	  };
	  this.barcodeSelector = new catalog_productSelector.ProductSelector('catalog_document_grid_' + this.getId() + '_barcode', selectorOptions);
	  main_core_events.EventEmitter.subscribe(this.barcodeSelector, 'onBeforeCreate', _classPrivateMethodGet(this, _handleBeforeCreateProduct, _handleBeforeCreateProduct2).bind(this));
	  main_core_events.EventEmitter.subscribe(this.barcodeSelector, 'onSpotlightClose', _classPrivateMethodGet(this, _handleSpotlightClose, _handleSpotlightClose2).bind(this));
	  main_core_events.EventEmitter.subscribe(this.barcodeSelector, 'onBarcodeQrClose', _classPrivateMethodGet(this, _handleBarcodeQrClose, _handleBarcodeQrClose2).bind(this));
	  main_core_events.EventEmitter.subscribe(this.barcodeSelector, 'onBarcodeScannerInstallChecked', _classPrivateMethodGet(this, _handleBarcodeScannerInstallCheck, _handleBarcodeScannerInstallCheck2).bind(this));
	  main_core_events.EventEmitter.subscribe(this.barcodeSelector, 'onBarcodeChange', _classPrivateMethodGet(this, _handleBarcodeChange, _handleBarcodeChange2).bind(this));
	  this.layoutBarcode();
	}

	function _initStoreSelector2(fieldNames) {
	  var _this12 = this;

	  Object.keys(fieldNames).forEach(function (rowName) {
	    var selectorOptions = {
	      inputFieldId: fieldNames[rowName],
	      inputFieldTitle: fieldNames[rowName] + '_TITLE',
	      isDisabledEmpty: true,
	      config: {
	        ENABLE_SEARCH: true,
	        ENABLE_INPUT_DETAIL_LINK: false,
	        ROW_ID: _this12.getId()
	      },
	      mode: catalog_storeSelector.StoreSelector.MODE_EDIT,
	      model: _this12.model
	    };
	    var storeSelector = new catalog_storeSelector.StoreSelector(_this12.getId() + '_' + rowName, selectorOptions);
	    main_core_events.EventEmitter.subscribe(storeSelector, 'onChange', main_core.Runtime.debounce(_classPrivateMethodGet(_this12, _onStoreFieldChange, _onStoreFieldChange2).bind(_this12), 500, _this12));
	    main_core_events.EventEmitter.subscribe(storeSelector, 'onClear', main_core.Runtime.debounce(_classPrivateMethodGet(_this12, _onStoreFieldChange, _onStoreFieldChange2).bind(_this12), 500, _this12));

	    _this12.storeSelectors.push(storeSelector);
	  });
	  this.layoutStoreSelector(fieldNames);
	}

	function _onStoreFieldChange2(event) {
	  var _this13 = this;

	  var data = event.getData();
	  data.fields.forEach(function (item) {
	    _this13.updateField(item.NAME, item.VALUE);
	  });
	}

	function _getRealValues2() {
	  if (!!this.realValues) {
	    return this.realValues;
	  }

	  try {
	    var value = this.getField('REAL_VALUES');

	    if (value) {
	      var parsedValue = JSON.parse(atob(value));

	      if (main_core.Type.isPlainObject(parsedValue)) {
	        this.realValues = parsedValue;
	      }
	    }
	  } catch (e) {
	    console.error('Cannot parse REAL_VALUE: ' + e.getMessage());
	  }

	  return this.realValues;
	}

	function _getCalculator2() {
	  var extra = main_core.Type.isNumber(this.getModel().getField('BASE_PRICE_EXTRA')) ? this.getModel().getField('BASE_PRICE_EXTRA') : null;
	  return new PriceCalculator({
	    basePrice: main_core.Text.toNumber(this.getModel().getField('PURCHASING_PRICE')),
	    finalPrice: main_core.Text.toNumber(this.getModel().getField('BASE_PRICE')),
	    extra: extra,
	    extraType: main_core.Text.toNumber(this.getModel().getField('BASE_PRICE_EXTRA_RATE'))
	  });
	}

	function _handleProductErrorsChange2() {
	  var errors = this.getModel().getErrorCollection().getErrors();

	  for (var code in errors) {
	    if (code === catalog_productSelector.ProductSelector.ErrorCodes.NOT_SELECTED_PRODUCT || code === catalog_storeSelector.StoreSelector.ErrorCodes.NOT_SELECTED_STORE) {
	      this.getSelector().layoutErrors();
	    }
	  }

	  this.getEditor().handleProductErrorsChange();
	}

	function _handleBeforeCreateProduct2(event) {
	  var _event$getData = event.getData(),
	      model = _event$getData.model;

	  model.setField('BARCODE', this.barcodeSelector.getNameInputFilledValue());
	  model.setField('NAME', this.mainSelector.getNameInputFilledValue());
	}

	function _handleSpotlightClose2(event) {
	  this.editor.closeBarcodeSpotlights();
	}

	function _handleBarcodeQrClose2(event) {
	  this.editor.closeBarcodeQrAuths();
	}

	function _handleBarcodeScannerInstallCheck2(event) {
	  this.editor.enableSendBarcodeMobilePush();
	}

	function _handleBarcodeChange2(event) {
	  var _event$getData2 = event.getData(),
	      value = _event$getData2.value;

	  this.changeBarcode(value, MODE_EDIT);
	}

	function _subscribeFieldToValidator2(fieldName, validatorCallback) {
	  var _this14 = this;

	  var fieldInput = this.getInputByFieldName(fieldName);
	  var fieldWrapper = this.getInputWrapperByFieldName(fieldName);

	  if (validatorCallback(fieldInput.valueAsNumber) || this.validatingFields.get(fieldName)) {
	    return;
	  }

	  this.validatingFields.set(fieldName, true);
	  fieldWrapper.classList.add('main-grid-editor-cell-danger');

	  var validator = function validator(eventObject) {
	    if (Boolean(validatorCallback(eventObject.target.valueAsNumber))) {
	      _this14.validatingFields.set(fieldName, false);

	      main_core.Event.unbind(fieldInput, 'blur', validator);
	      fieldWrapper.classList.remove('main-grid-editor-cell-danger');
	    }
	  };

	  main_core.Event.bind(fieldInput, 'blur', validator);
	}

	function _isProductCountCorrect2(amountValue) {
	  return amountValue > 0;
	}

	function _getNodeChildByDataName2(name) {
	  return this.getNode().querySelector("[data-name=\"".concat(name, "\"]"));
	}

	function _needInventory2() {
	  return !this.getModel().isService();
	}

	function _needBarcode2() {
	  return !this.getModel().isService();
	}

	function _isRowAccessDenied2() {
	  return this.getField('ACCESS_DENIED') === true;
	}

	function _hideFields2() {
	  var _this15 = this;

	  if (!_classPrivateMethodGet(this, _isRowAccessDenied, _isRowAccessDenied2).call(this)) {
	    _classPrivateMethodGet(this, _hidePurchasingPrice, _hidePurchasingPrice2).call(this);

	    return;
	  }

	  var hiddenFields = this.getEditor().getSettingValue('hiddenFields');
	  var columnIndexes = this.getEditor().getGridColumnIndexes();
	  hiddenFields.forEach(function (fieldName) {
	    var columnIndex = columnIndexes[fieldName];

	    if (columnIndex === undefined) {
	      return;
	    }

	    var item = _this15.getNode().querySelector(".main-grid-cell:nth-child(".concat(columnIndex + 1, ") .main-grid-cell-content"));

	    if (main_core.Type.isElementNode(item)) {
	      item.innerHTML = '';
	    }
	  });
	  var fieldWithHintIndex = columnIndexes['AMOUNT'];

	  if (fieldWithHintIndex) {
	    var fieldWithHintNode = this.getNode().querySelector(".main-grid-cell:nth-child(".concat(fieldWithHintIndex + 1, ") .main-grid-cell-content"));

	    if (fieldWithHintNode) {
	      var input = new AccessDeniedInput({
	        hint: main_core.Loc.getMessage('CATALOG_DOCUMENT_PRODUCT_LIST_ACCESS_DENIED_STORE_HINT'),
	        isReadOnly: this.getEditor().isReadOnly()
	      });
	      input.renderTo(fieldWithHintNode);
	    }
	  }
	}

	function _isPurchasingPriceAccessDenied2() {
	  return this.getField('ACCESS_DENIED_TO_PURCHASING_PRICE') === true;
	}

	function _hidePurchasingPrice2() {
	  if (!_classPrivateMethodGet(this, _isPurchasingPriceAccessDenied, _isPurchasingPriceAccessDenied2).call(this)) {
	    return;
	  }

	  var columnIndexes = this.getEditor().getGridColumnIndexes();
	  var fieldWithHintIndex = columnIndexes['PURCHASING_PRICE'];

	  if (fieldWithHintIndex) {
	    var fieldWithHintNode = this.getNode().querySelector(".main-grid-cell:nth-child(".concat(fieldWithHintIndex + 1, ")"));

	    if (fieldWithHintNode) {
	      var priceNode = fieldWithHintNode.querySelector('.main-grid-editor-container');

	      if (priceNode) {
	        priceNode.remove();
	      }

	      var contentNode = fieldWithHintNode.querySelector('.main-grid-cell-content');

	      if (contentNode) {
	        var input = new AccessDeniedInput({
	          hint: main_core.Loc.getMessage('CATALOG_DOCUMENT_PRODUCT_LIST_ACCESS_DENIED_PURCHASING_PRICE_HINT'),
	          isReadOnly: this.getEditor().isReadOnly()
	        });
	        input.renderTo(contentNode);
	        contentNode.style.display = 'block';
	      }
	    }
	  }
	}

	var PageEventsManager = /*#__PURE__*/function () {
	  function PageEventsManager(settings) {
	    babelHelpers.classCallCheck(this, PageEventsManager);
	    babelHelpers.defineProperty(this, "_settings", {});
	    this._settings = settings ? settings : {};
	    this.eventHandlers = {};
	  }

	  babelHelpers.createClass(PageEventsManager, [{
	    key: "registerEventHandler",
	    value: function registerEventHandler(eventName, eventHandler) {
	      if (!this.eventHandlers[eventName]) this.eventHandlers[eventName] = [];
	      this.eventHandlers[eventName].push(eventHandler);
	      BX.addCustomEvent(this, eventName, eventHandler);
	    }
	  }, {
	    key: "fireEvent",
	    value: function fireEvent(eventName, eventParams) {
	      BX.onCustomEvent(this, eventName, eventParams);
	    }
	  }, {
	    key: "unregisterEventHandlers",
	    value: function unregisterEventHandlers(eventName) {
	      if (this.eventHandlers[eventName]) {
	        for (var i = 0; i < this.eventHandlers[eventName].length; i++) {
	          BX.removeCustomEvent(this, eventName, this.eventHandlers[eventName][i]);
	        }

	        delete this.eventHandlers[eventName];
	      }
	    }
	  }]);
	  return PageEventsManager;
	}();

	var _templateObject$3, _templateObject2$1, _templateObject3$1, _templateObject4$1;

	function _classPrivateMethodInitSpec$1(obj, privateSet) { _checkPrivateRedeclaration$2(obj, privateSet); privateSet.add(obj); }

	function _classPrivateFieldInitSpec$1(obj, privateMap, value) { _checkPrivateRedeclaration$2(obj, privateMap); privateMap.set(obj, value); }

	function _checkPrivateRedeclaration$2(obj, privateCollection) { if (privateCollection.has(obj)) { throw new TypeError("Cannot initialize the same private elements twice on an object"); } }

	function _classPrivateMethodGet$1(receiver, privateSet, fn) { if (!privateSet.has(receiver)) { throw new TypeError("attempted to get private field on non-instance"); } return fn; }

	var _target = /*#__PURE__*/new WeakMap();

	var _settings = /*#__PURE__*/new WeakMap();

	var _editor = /*#__PURE__*/new WeakMap();

	var _cache = /*#__PURE__*/new WeakMap();

	var _getSetting = /*#__PURE__*/new WeakSet();

	var _prepareSettingsContent = /*#__PURE__*/new WeakSet();

	var _getSettingItem = /*#__PURE__*/new WeakSet();

	var _setSetting = /*#__PURE__*/new WeakSet();

	var _requestGridSettings = /*#__PURE__*/new WeakSet();

	var _showNotification = /*#__PURE__*/new WeakSet();

	var SettingsPopup = /*#__PURE__*/function () {
	  function SettingsPopup(target) {
	    var settings = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : [];
	    var editor = arguments.length > 2 ? arguments[2] : undefined;
	    babelHelpers.classCallCheck(this, SettingsPopup);

	    _classPrivateMethodInitSpec$1(this, _showNotification);

	    _classPrivateMethodInitSpec$1(this, _requestGridSettings);

	    _classPrivateMethodInitSpec$1(this, _setSetting);

	    _classPrivateMethodInitSpec$1(this, _getSettingItem);

	    _classPrivateMethodInitSpec$1(this, _prepareSettingsContent);

	    _classPrivateMethodInitSpec$1(this, _getSetting);

	    _classPrivateFieldInitSpec$1(this, _target, {
	      writable: true,
	      value: void 0
	    });

	    _classPrivateFieldInitSpec$1(this, _settings, {
	      writable: true,
	      value: void 0
	    });

	    _classPrivateFieldInitSpec$1(this, _editor, {
	      writable: true,
	      value: void 0
	    });

	    _classPrivateFieldInitSpec$1(this, _cache, {
	      writable: true,
	      value: new main_core.Cache.MemoryCache()
	    });

	    babelHelpers.classPrivateFieldSet(this, _target, target);
	    babelHelpers.classPrivateFieldSet(this, _settings, settings);
	    babelHelpers.classPrivateFieldSet(this, _editor, editor);
	  }

	  babelHelpers.createClass(SettingsPopup, [{
	    key: "show",
	    value: function show() {
	      this.getPopup().show();
	    }
	  }, {
	    key: "getPopup",
	    value: function getPopup() {
	      var _this = this;

	      return babelHelpers.classPrivateFieldGet(this, _cache).remember('settings-popup', function () {
	        return new main_popup.Popup(babelHelpers.classPrivateFieldGet(_this, _editor).getId() + '_' + Math.random() * 100, babelHelpers.classPrivateFieldGet(_this, _target), {
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
	          content: _classPrivateMethodGet$1(_this, _prepareSettingsContent, _prepareSettingsContent2).call(_this)
	        });
	      });
	    }
	  }, {
	    key: "updateCheckboxState",
	    value: function updateCheckboxState() {
	      var _this2 = this;

	      var popupContainer = this.getPopup().getContentContainer();
	      babelHelpers.classPrivateFieldGet(this, _settings).filter(function (item) {
	        return item.action === 'grid' && main_core.Type.isArray(item.columns);
	      }).forEach(function (item) {
	        var allColumnsExist = true;
	        item.columns.forEach(function (columnName) {
	          if (!babelHelpers.classPrivateFieldGet(_this2, _editor).getGrid().getColumnHeaderCellByName(columnName)) {
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
	  return SettingsPopup;
	}();

	function _getSetting2(id) {
	  return babelHelpers.classPrivateFieldGet(this, _settings).filter(function (item) {
	    return item.id === id;
	  })[0];
	}

	function _prepareSettingsContent2() {
	  var _this3 = this;

	  var content = main_core.Tag.render(_templateObject$3 || (_templateObject$3 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<div class='ui-entity-editor-popup-create-field-list'></div>\n\t\t"])));
	  babelHelpers.classPrivateFieldGet(this, _settings).forEach(function (item) {
	    content.append(_classPrivateMethodGet$1(_this3, _getSettingItem, _getSettingItem2).call(_this3, item));
	  });
	  return content;
	}

	function _getSettingItem2(item) {
	  var input = main_core.Tag.render(_templateObject2$1 || (_templateObject2$1 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<input type=\"checkbox\">\n\t\t"])));
	  input.checked = item.checked;
	  input.dataset.settingId = item.id;
	  var descriptionNode = main_core.Type.isStringFilled(item.desc) ? main_core.Tag.render(_templateObject3$1 || (_templateObject3$1 = babelHelpers.taggedTemplateLiteral(["<span class=\"ui-entity-editor-popup-create-field-item-desc\">", "</span>"])), item.desc) : '';
	  var setting = main_core.Tag.render(_templateObject4$1 || (_templateObject4$1 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<label class=\"ui-ctl-block ui-entity-editor-popup-create-field-item ui-ctl-w100\">\n\t\t\t\t<div class=\"ui-ctl-w10\" style=\"text-align: center\">", "</div>\n\t\t\t\t<div class=\"ui-ctl-w75\">\n\t\t\t\t\t<span class=\"ui-entity-editor-popup-create-field-item-title\">", "</span>\n\t\t\t\t\t", "\n\t\t\t\t</div>\n\t\t\t</label>\n\t\t"])), input, item.title, descriptionNode);
	  main_core.Event.bind(setting, 'change', _classPrivateMethodGet$1(this, _setSetting, _setSetting2).bind(this));
	  return setting;
	}

	function _setSetting2(event) {
	  var settingItem = _classPrivateMethodGet$1(this, _getSetting, _getSetting2).call(this, event.target.dataset.settingId);

	  if (!settingItem) {
	    return;
	  }

	  var settingEnabled = event.target.checked;

	  _classPrivateMethodGet$1(this, _requestGridSettings, _requestGridSettings2).call(this, settingItem, settingEnabled);
	}

	function _requestGridSettings2(setting, enabled) {
	  var _this4 = this;

	  var headers = [];
	  var cells = babelHelpers.classPrivateFieldGet(this, _editor).getGrid().getRows().getHeadFirstChild().getCells();
	  Array.from(cells).forEach(function (header) {
	    if ('name' in header.dataset) {
	      headers.push(header.dataset.name);
	    }
	  });
	  main_core.ajax.runComponentAction(babelHelpers.classPrivateFieldGet(this, _editor).getComponentName(), 'setGridSetting', {
	    mode: 'class',
	    data: {
	      signedParameters: babelHelpers.classPrivateFieldGet(this, _editor).getSignedParameters(),
	      settingId: setting.id,
	      selected: enabled,
	      currentHeaders: headers
	    }
	  }).then(function () {
	    setting.checked = enabled;

	    if (setting.id === 'ADD_NEW_ROW_TOP') {
	      var panel = enabled ? 'top' : 'bottom';
	      babelHelpers.classPrivateFieldGet(_this4, _editor).setSettingValue('newRowPosition', panel);
	      var activePanel = babelHelpers.classPrivateFieldGet(_this4, _editor).changeActivePanelButtons(panel);
	      var settingButton = activePanel.querySelector('[data-role="product-list-settings-button"]');

	      _this4.getPopup().setBindElement(settingButton);
	    } else {
	      babelHelpers.classPrivateFieldGet(_this4, _editor).reloadGrid();
	    }

	    _this4.getPopup().close();

	    var message = enabled ? main_core.Loc.getMessage('CATALOG_DOCUMENT_PRODUCT_LIST_SETTING_ENABLED') : main_core.Loc.getMessage('CATALOG_DOCUMENT_PRODUCT_LIST_SETTING_DISABLED');

	    _classPrivateMethodGet$1(_this4, _showNotification, _showNotification2).call(_this4, message.replace('#NAME#', setting.title), {
	      category: 'popup-settings'
	    });
	  });
	}

	function _showNotification2(content, options) {
	  options = options || {};
	  BX.UI.Notification.Center.notify({
	    content: content,
	    stack: options.stack || null,
	    position: 'top-right',
	    width: 'auto',
	    category: options.category || null,
	    autoHideDelay: options.autoHideDelay || 3000
	  });
	}

	function _classPrivateMethodInitSpec$2(obj, privateSet) { _checkPrivateRedeclaration$3(obj, privateSet); privateSet.add(obj); }

	function _classPrivateFieldInitSpec$2(obj, privateMap, value) { _checkPrivateRedeclaration$3(obj, privateMap); privateMap.set(obj, value); }

	function _checkPrivateRedeclaration$3(obj, privateCollection) { if (privateCollection.has(obj)) { throw new TypeError("Cannot initialize the same private elements twice on an object"); } }

	function _classPrivateMethodGet$2(receiver, privateSet, fn) { if (!privateSet.has(receiver)) { throw new TypeError("attempted to get private field on non-instance"); } return fn; }

	var _gridGetter = /*#__PURE__*/new WeakMap();

	var _contentContainer = /*#__PURE__*/new WeakMap();

	var _bindGridNodeVisionChange = /*#__PURE__*/new WeakSet();

	var _getPossibleToValidateFieldNodes = /*#__PURE__*/new WeakSet();

	var _fieldNodeIsInGridVision = /*#__PURE__*/new WeakSet();

	var _bindSpotlightToNode = /*#__PURE__*/new WeakSet();

	var _freezeGridContainer = /*#__PURE__*/new WeakSet();

	var _tieTourToNode = /*#__PURE__*/new WeakSet();

	var FieldHintManager = /*#__PURE__*/function () {
	  function FieldHintManager(contentContainer, gridGetter) {
	    babelHelpers.classCallCheck(this, FieldHintManager);

	    _classPrivateMethodInitSpec$2(this, _tieTourToNode);

	    _classPrivateMethodInitSpec$2(this, _freezeGridContainer);

	    _classPrivateMethodInitSpec$2(this, _bindSpotlightToNode);

	    _classPrivateMethodInitSpec$2(this, _fieldNodeIsInGridVision);

	    _classPrivateMethodInitSpec$2(this, _getPossibleToValidateFieldNodes);

	    _classPrivateMethodInitSpec$2(this, _bindGridNodeVisionChange);

	    babelHelpers.defineProperty(this, "fieldHintIsBusy", false);
	    babelHelpers.defineProperty(this, "activeHintGuide", null);

	    _classPrivateFieldInitSpec$2(this, _gridGetter, {
	      writable: true,
	      value: void 0
	    });

	    _classPrivateFieldInitSpec$2(this, _contentContainer, {
	      writable: true,
	      value: void 0
	    });

	    babelHelpers.classPrivateFieldSet(this, _contentContainer, contentContainer);
	    babelHelpers.classPrivateFieldSet(this, _gridGetter, gridGetter);
	  }

	  babelHelpers.createClass(FieldHintManager, [{
	    key: "processFieldTour",
	    value: function processFieldTour(fieldNode, tourData, endTourHandler) {
	      var _this = this;

	      var addictedFieldNodes = arguments.length > 3 && arguments[3] !== undefined ? arguments[3] : [];

	      if (this.fieldHintIsBusy) {
	        return;
	      }

	      this.fieldHintIsBusy = true; // When click action in progress tour will be closed -> 'onClose' tour method will be executed

	      tourData.events = {
	        onClose: function onClose() {
	          endTourHandler();
	          _this.fieldHintIsBusy = false;
	          _this.activeHintGuide = null;
	        }
	      };

	      if (_classPrivateMethodGet$2(this, _fieldNodeIsInGridVision, _fieldNodeIsInGridVision2).call(this, fieldNode)) {
	        var tourObject = _classPrivateMethodGet$2(this, _tieTourToNode, _tieTourToNode2).call(this, fieldNode, tourData);

	        _classPrivateMethodGet$2(this, _freezeGridContainer, _freezeGridContainer2).call(this, function () {
	          tourObject.close();
	        });
	      } else {
	        var gridContainer = babelHelpers.classPrivateFieldGet(this, _gridGetter).call(this).getContainer();
	        var leftArrow = gridContainer.querySelector('.main-grid-ear-left');
	        var rightArrow = gridContainer.querySelector('.main-grid-ear-right');
	        var fieldPos = fieldNode.getClientRects()[0].x;
	        var gridPos = gridContainer.getClientRects()[0].x;
	        var spotlight$$1 = null;

	        if (fieldPos > gridPos) {
	          spotlight$$1 = _classPrivateMethodGet$2(this, _bindSpotlightToNode, _bindSpotlightToNode2).call(this, rightArrow);
	        } else {
	          spotlight$$1 = _classPrivateMethodGet$2(this, _bindSpotlightToNode, _bindSpotlightToNode2).call(this, leftArrow);
	        }

	        _classPrivateMethodGet$2(this, _bindGridNodeVisionChange, _bindGridNodeVisionChange2).call(this, fieldNode, function () {
	          spotlight$$1.close();

	          var tourObject = _classPrivateMethodGet$2(_this, _tieTourToNode, _tieTourToNode2).call(_this, fieldNode, tourData);

	          _classPrivateMethodGet$2(_this, _freezeGridContainer, _freezeGridContainer2).call(_this, function () {
	            tourObject.close();
	          });
	        }, [], addictedFieldNodes);
	      }
	    }
	  }, {
	    key: "getActiveHint",
	    value: function getActiveHint() {
	      if (!this.fieldHintIsBusy) {
	        return null;
	      } else if (this.activeHintGuide instanceof ui_tour.Guide) {
	        return this.activeHintGuide;
	      }

	      return null;
	    }
	  }]);
	  return FieldHintManager;
	}();

	function _bindGridNodeVisionChange2(observedNode, onSuccessVisionCallback) {
	  var _classPrivateMethodGe,
	      _this2 = this;

	  var callbackParams = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : [];
	  var addictedNodes = arguments.length > 3 && arguments[3] !== undefined ? arguments[3] : [];

	  var observedNodes = (_classPrivateMethodGe = _classPrivateMethodGet$2(this, _getPossibleToValidateFieldNodes, _getPossibleToValidateFieldNodes2)).call.apply(_classPrivateMethodGe, [this, observedNode].concat(babelHelpers.toConsumableArray(addictedNodes)));

	  var observer = function observer(event) {
	    var _classPrivateMethodGe2;

	    if ((_classPrivateMethodGe2 = _classPrivateMethodGet$2(_this2, _fieldNodeIsInGridVision, _fieldNodeIsInGridVision2)).call.apply(_classPrivateMethodGe2, [_this2].concat(babelHelpers.toConsumableArray(observedNodes)))) {
	      main_core.Event.unbind(babelHelpers.classPrivateFieldGet(_this2, _gridGetter).call(_this2).getScrollContainer(), 'scroll', observer);
	      main_core.Event.unbind(window, 'resize', observer);
	      onSuccessVisionCallback.apply(void 0, babelHelpers.toConsumableArray(callbackParams));
	    }
	  };

	  main_core.Event.bind(babelHelpers.classPrivateFieldGet(this, _gridGetter).call(this).getScrollContainer(), 'scroll', observer);
	  main_core.Event.bind(window, 'resize', observer);
	}

	function _getPossibleToValidateFieldNodes2(mainNode) {
	  var _babelHelpers$classPr, _babelHelpers$classPr2;

	  var nodesTuple = [];

	  for (var _len = arguments.length, addictedNodes = new Array(_len > 1 ? _len - 1 : 0), _key = 1; _key < _len; _key++) {
	    addictedNodes[_key - 1] = arguments[_key];
	  }

	  for (var _i = 0, _addictedNodes = addictedNodes; _i < _addictedNodes.length; _i++) {
	    var addictedNode = _addictedNodes[_i];
	    nodesTuple.push({
	      node: addictedNode,
	      nodeRect: addictedNode.getClientRects()[0]
	    });
	  }

	  var mainNodeTupleEl = {
	    node: mainNode,
	    nodeRect: mainNode.getClientRects()[0]
	  };
	  nodesTuple.push(mainNodeTupleEl);
	  nodesTuple.sort(function (firstEl, secondEl) {
	    var firstX = firstEl.nodeRect.x;
	    var secondX = secondEl.nodeRect.x;

	    if (firstX < secondX) {
	      return -1;
	    } else if (firstX > secondX) {
	      return 1;
	    } else {
	      return 0;
	    }
	  });
	  var gridRect = (_babelHelpers$classPr = babelHelpers.classPrivateFieldGet(this, _gridGetter).call(this)) === null || _babelHelpers$classPr === void 0 ? void 0 : (_babelHelpers$classPr2 = _babelHelpers$classPr.getContainer().getClientRects()) === null || _babelHelpers$classPr2 === void 0 ? void 0 : _babelHelpers$classPr2[0];

	  function widthIsValid(leftPos, rightPos) {
	    return Math.abs(leftPos - rightPos) < gridRect.width;
	  }

	  while (nodesTuple.length > 1 && !widthIsValid(nodesTuple[0].nodeRect.x, nodesTuple[nodesTuple.length - 1].nodeRect.x)) {
	    var firstEl = nodesTuple[0];
	    var lastEl = nodesTuple[nodesTuple.length - 1];

	    if (firstEl === mainNodeTupleEl) {
	      nodesTuple.pop();
	    } else if (lastEl === mainNodeTupleEl) {
	      nodesTuple.shift();
	    } else {
	      var firstElDistance = mainNodeTupleEl.nodeRect.x - firstEl.nodeRect.x;
	      var lastElDistance = lastEl.nodeRect.x - mainNodeTupleEl.nodeRect.x;

	      if (firstElDistance >= lastElDistance) {
	        nodesTuple.shift();
	      } else {
	        nodesTuple.pop();
	      }
	    }
	  }

	  return nodesTuple.map(function (el) {
	    return el.node;
	  });
	}

	function _fieldNodeIsInGridVision2() {
	  var _babelHelpers$classPr3, _babelHelpers$classPr4;

	  var gridRect = (_babelHelpers$classPr3 = babelHelpers.classPrivateFieldGet(this, _gridGetter).call(this)) === null || _babelHelpers$classPr3 === void 0 ? void 0 : (_babelHelpers$classPr4 = _babelHelpers$classPr3.getContainer().getClientRects()) === null || _babelHelpers$classPr4 === void 0 ? void 0 : _babelHelpers$classPr4[0];

	  if (gridRect === undefined) {
	    return false;
	  }

	  var gridLeftEdge = gridRect.x;
	  var gridRightEdge = gridRect.x + gridRect.width;

	  for (var _len2 = arguments.length, fieldNodes = new Array(_len2), _key2 = 0; _key2 < _len2; _key2++) {
	    fieldNodes[_key2] = arguments[_key2];
	  }

	  for (var _i2 = 0, _fieldNodes = fieldNodes; _i2 < _fieldNodes.length; _i2++) {
	    var _fieldNode$getClientR;

	    var fieldNode = _fieldNodes[_i2];
	    var fieldRect = (_fieldNode$getClientR = fieldNode.getClientRects()) === null || _fieldNode$getClientR === void 0 ? void 0 : _fieldNode$getClientR[0];

	    if (fieldRect === undefined) {
	      return false;
	    }

	    var fieldLeftEdge = fieldRect.x;
	    var fieldRightEdge = fieldRect.x + fieldRect.width;

	    if (fieldLeftEdge < gridLeftEdge || fieldRightEdge > gridRightEdge) {
	      return false;
	    }
	  }

	  return true;
	}

	function _bindSpotlightToNode2(targetNode) {
	  var spotlight$$1 = new BX.SpotLight({
	    id: 'arrow_spotlight',
	    targetElement: targetNode,
	    autoSave: true,
	    targetVertex: "middle-center",
	    zIndex: 200
	  });
	  spotlight$$1.show();
	  spotlight$$1.container.style.pointerEvents = "none";
	  return spotlight$$1;
	}

	function _freezeGridContainer2(onCloseCallback) {
	  var _this3 = this;

	  var callbackParams = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : [];
	  var gridContainer = babelHelpers.classPrivateFieldGet(this, _gridGetter).call(this).getContainer();
	  var leftArrow = gridContainer.querySelector('.main-grid-ear-left');
	  var rightArrow = gridContainer.querySelector('.main-grid-ear-right');
	  gridContainer.style.pointerEvents = "none";
	  leftArrow.style.pointerEvents = "none";
	  rightArrow.style.pointerEvents = "none";

	  var clickObserver = function clickObserver(event) {
	    gridContainer.style.pointerEvents = "auto";
	    leftArrow.style.pointerEvents = "auto";
	    rightArrow.style.pointerEvents = "auto";
	    main_core.Event.unbind(babelHelpers.classPrivateFieldGet(_this3, _contentContainer), 'click', clickObserver);
	    onCloseCallback.apply(void 0, babelHelpers.toConsumableArray(callbackParams));
	  };

	  setTimeout(function () {
	    main_core.Event.bind(babelHelpers.classPrivateFieldGet(_this3, _contentContainer), 'click', clickObserver);
	  }, 500);
	}

	function _tieTourToNode2(tourTarget, tourData) {
	  var guide = new ui_tour.Guide({
	    steps: [Object.assign({
	      target: tourTarget
	    }, tourData)],
	    onEvents: true
	  });
	  this.activeHintGuide = guide;
	  guide.showNextStep();
	  return guide;
	}

	function ownKeys$1(object, enumerableOnly) { var keys = Object.keys(object); if (Object.getOwnPropertySymbols) { var symbols = Object.getOwnPropertySymbols(object); enumerableOnly && (symbols = symbols.filter(function (sym) { return Object.getOwnPropertyDescriptor(object, sym).enumerable; })), keys.push.apply(keys, symbols); } return keys; }

	function _objectSpread$1(target) { for (var i = 1; i < arguments.length; i++) { var source = null != arguments[i] ? arguments[i] : {}; i % 2 ? ownKeys$1(Object(source), !0).forEach(function (key) { babelHelpers.defineProperty(target, key, source[key]); }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(target, Object.getOwnPropertyDescriptors(source)) : ownKeys$1(Object(source)).forEach(function (key) { Object.defineProperty(target, key, Object.getOwnPropertyDescriptor(source, key)); }); } return target; }

	function _createForOfIteratorHelper$1(o, allowArrayLike) { var it = typeof Symbol !== "undefined" && o[Symbol.iterator] || o["@@iterator"]; if (!it) { if (Array.isArray(o) || (it = _unsupportedIterableToArray$1(o)) || allowArrayLike && o && typeof o.length === "number") { if (it) o = it; var i = 0; var F = function F() {}; return { s: F, n: function n() { if (i >= o.length) return { done: true }; return { done: false, value: o[i++] }; }, e: function e(_e) { throw _e; }, f: F }; } throw new TypeError("Invalid attempt to iterate non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); } var normalCompletion = true, didErr = false, err; return { s: function s() { it = it.call(o); }, n: function n() { var step = it.next(); normalCompletion = step.done; return step; }, e: function e(_e2) { didErr = true; err = _e2; }, f: function f() { try { if (!normalCompletion && it["return"] != null) it["return"](); } finally { if (didErr) throw err; } } }; }

	function _unsupportedIterableToArray$1(o, minLen) { if (!o) return; if (typeof o === "string") return _arrayLikeToArray$1(o, minLen); var n = Object.prototype.toString.call(o).slice(8, -1); if (n === "Object" && o.constructor) n = o.constructor.name; if (n === "Map" || n === "Set") return Array.from(o); if (n === "Arguments" || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(n)) return _arrayLikeToArray$1(o, minLen); }

	function _arrayLikeToArray$1(arr, len) { if (len == null || len > arr.length) len = arr.length; for (var i = 0, arr2 = new Array(len); i < len; i++) { arr2[i] = arr[i]; } return arr2; }

	function _classPrivateMethodInitSpec$3(obj, privateSet) { _checkPrivateRedeclaration$4(obj, privateSet); privateSet.add(obj); }

	function _classPrivateFieldInitSpec$3(obj, privateMap, value) { _checkPrivateRedeclaration$4(obj, privateMap); privateMap.set(obj, value); }

	function _checkPrivateRedeclaration$4(obj, privateCollection) { if (privateCollection.has(obj)) { throw new TypeError("Cannot initialize the same private elements twice on an object"); } }

	function _classStaticPrivateMethodGet(receiver, classConstructor, method) { _classCheckPrivateStaticAccess(receiver, classConstructor); return method; }

	function _classCheckPrivateStaticAccess(receiver, classConstructor) { if (receiver !== classConstructor) { throw new TypeError("Private static access of wrong provenance"); } }

	function _classPrivateMethodGet$3(receiver, privateSet, fn) { if (!privateSet.has(receiver)) { throw new TypeError("attempted to get private field on non-instance"); } return fn; }
	var GRID_TEMPLATE_ROW = 'template_0';
	var DEFAULT_PRECISION = 2;

	var isEmptyObject = function isEmptyObject(obj) {
	  if (!main_core.Type.isPlainObject(obj)) {
	    return false;
	  }

	  for (var key in obj) {
	    return false;
	  }

	  return true;
	};

	var _fieldHintManager = /*#__PURE__*/new WeakMap();

	var _initSupportCustomRowActions = /*#__PURE__*/new WeakSet();

	var _childrenHasErrors = /*#__PURE__*/new WeakSet();

	var _processSetStoryAction = /*#__PURE__*/new WeakSet();

	var Editor = /*#__PURE__*/function () {
	  function Editor(id) {
	    babelHelpers.classCallCheck(this, Editor);

	    _classPrivateMethodInitSpec$3(this, _processSetStoryAction);

	    _classPrivateMethodInitSpec$3(this, _childrenHasErrors);

	    _classPrivateMethodInitSpec$3(this, _initSupportCustomRowActions);

	    babelHelpers.defineProperty(this, "products", []);
	    babelHelpers.defineProperty(this, "productsWasInitiated", false);
	    babelHelpers.defineProperty(this, "cache", new main_core.Cache.MemoryCache());

	    _classPrivateFieldInitSpec$3(this, _fieldHintManager, {
	      writable: true,
	      value: void 0
	    });

	    babelHelpers.defineProperty(this, "actions", {
	      productChange: 'productChange',
	      productListChanged: 'productListChanged',
	      updateListField: 'listField',
	      updateTotal: 'total'
	    });
	    babelHelpers.defineProperty(this, "updateFieldForList", null);
	    babelHelpers.defineProperty(this, "productRowAddHandler", this.handleProductRowAdd.bind(this));
	    babelHelpers.defineProperty(this, "productRowCreateHandler", this.handleProductRowCreate.bind(this));
	    babelHelpers.defineProperty(this, "showBarcodeSettingsPopupHandler", this.handleShowBarcodeSettingsPopup.bind(this));
	    babelHelpers.defineProperty(this, "showSettingsPopupHandler", this.handleShowSettingsPopup.bind(this));
	    babelHelpers.defineProperty(this, "onSaveHandler", this.handleOnSave.bind(this));
	    babelHelpers.defineProperty(this, "onEditorSubmit", this.handleEditorSubmit.bind(this));
	    babelHelpers.defineProperty(this, "onFocusToProductList", this.handleProductListFocus.bind(this));
	    babelHelpers.defineProperty(this, "onBeforeGridRequestHandler", this.handleOnBeforeGridRequest.bind(this));
	    babelHelpers.defineProperty(this, "onGridUpdatedHandler", this.handleOnGridUpdated.bind(this));
	    babelHelpers.defineProperty(this, "onGridRowMovedHandler", this.handleOnGridRowMoved.bind(this));
	    babelHelpers.defineProperty(this, "onBeforeProductChangeHandler", this.handleOnBeforeProductChange.bind(this));
	    babelHelpers.defineProperty(this, "onProductChangeHandler", this.handleOnProductChange.bind(this));
	    babelHelpers.defineProperty(this, "onProductClearHandler", this.handleOnProductClear.bind(this));
	    babelHelpers.defineProperty(this, "dropdownChangeHandler", this.handleDropdownChange.bind(this));
	    babelHelpers.defineProperty(this, "onScanEmitHandler", this.handleMobileScanEvent.bind(this));
	    babelHelpers.defineProperty(this, "changeProductFieldHandler", this.handleFieldChange.bind(this));
	    babelHelpers.defineProperty(this, "blurProductFieldHandler", this.handleFieldBlur.bind(this));
	    babelHelpers.defineProperty(this, "updateTotalDataDelayedHandler", main_core.Runtime.debounce(this.updateTotalDataDelayed, 100, this));
	    this.setId(id);
	  }

	  babelHelpers.createClass(Editor, [{
	    key: "init",
	    value: function init() {
	      var _this$scannerToken;

	      var config = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};
	      this.setSettings(config);
	      this.scannerToken = (_this$scannerToken = this.scannerToken) !== null && _this$scannerToken !== void 0 ? _this$scannerToken : main_core.Text.getRandom(16);

	      if (this.canEdit()) {
	        this.addFirstRowIfEmpty();
	        this.enableEdit();
	      }

	      this.initForm();
	      this.initProducts();
	      this.initGridData();
	      this.paintColumns();
	      babelHelpers.classPrivateFieldSet(this, _fieldHintManager, new FieldHintManager(this.getContainer(), this.getGrid.bind(this)));
	      main_core_events.EventEmitter.emit('DocumentProductListController', [this]);

	      _classPrivateMethodGet$3(this, _initSupportCustomRowActions, _initSupportCustomRowActions2).call(this);

	      this.subscribeDomEvents();
	      this.subscribeCustomEvents();
	      this.getContainer().querySelectorAll('.catalog-document-product-list-add-block').forEach(function (buttonBlock) {
	        BX.UI.Hint.init(buttonBlock);
	      });
	    }
	  }, {
	    key: "subscribeDomEvents",
	    value: function subscribeDomEvents() {
	      var _this = this;

	      var container = this.getContainer();

	      if (main_core.Type.isElementNode(container)) {
	        container.querySelectorAll('[data-role="product-list-add-button"]').forEach(function (addButton) {
	          main_core.Event.bind(addButton, 'click', _this.productRowAddHandler);
	        });

	        if (this.getSettingValue('enabledCreateProductButton', true)) {
	          container.querySelectorAll('[data-role="product-list-create-button"]').forEach(function (addButton) {
	            main_core.Event.bind(addButton, 'click', _this.productRowCreateHandler);
	          });
	        }

	        container.querySelectorAll('[data-role="product-list-settings-button"]').forEach(function (configButton) {
	          main_core.Event.bind(configButton, 'click', _this.showSettingsPopupHandler);
	        });
	        container.querySelectorAll('[data-role="product-list-barcode-settings-button"]').forEach(function (configButton) {
	          main_core.Event.bind(configButton, 'click', _this.showBarcodeSettingsPopupHandler);
	        });
	      }
	    }
	  }, {
	    key: "unsubscribeDomEvents",
	    value: function unsubscribeDomEvents() {
	      var _this2 = this;

	      var container = this.getContainer();

	      if (main_core.Type.isElementNode(container)) {
	        container.querySelectorAll('[data-role="product-list-select-button"]').forEach(function (selectButton) {
	          main_core.Event.unbind(selectButton, 'click', _this2.productSelectionPopupHandler);
	        });
	        container.querySelectorAll('[data-role="product-list-add-button"]').forEach(function (createButton) {
	          main_core.Event.unbind(createButton, 'click', _this2.productRowCreateHandler);
	        });
	        container.querySelectorAll('[data-role="product-list-barcode-settings-button"]').forEach(function (addButton) {
	          main_core.Event.unbind(addButton, 'click', _this2.productRowAddHandler);
	        });
	        container.querySelectorAll('[data-role="product-list-settings-button"]').forEach(function (configButton) {
	          main_core.Event.unbind(configButton, 'click', _this2.showSettingsPopupHandler);
	        });
	      }
	    }
	  }, {
	    key: "subscribeCustomEvents",
	    value: function subscribeCustomEvents() {
	      main_core_events.EventEmitter.subscribe('BX.UI.EntityEditor:onSave', this.onSaveHandler);
	      main_core_events.EventEmitter.subscribe('BX.UI.EntityEditorAjax:onSubmit', this.onEditorSubmit);
	      main_core_events.EventEmitter.subscribe('onFocusToProductList', this.onFocusToProductList);
	      main_core_events.EventEmitter.subscribe('Grid::beforeRequest', this.onBeforeGridRequestHandler);
	      main_core_events.EventEmitter.subscribe('Grid::updated', this.onGridUpdatedHandler);
	      main_core_events.EventEmitter.subscribe('Grid::rowMoved', this.onGridRowMovedHandler);
	      main_core_events.EventEmitter.subscribe('BX.Catalog.ProductSelector:onBeforeChange', this.onBeforeProductChangeHandler);
	      main_core_events.EventEmitter.subscribe('BX.Catalog.ProductSelector:onChange', this.onProductChangeHandler);
	      main_core_events.EventEmitter.subscribe('BX.Catalog.ProductSelector:onClear', this.onProductClearHandler);
	      main_core_events.EventEmitter.subscribe('Dropdown::change', this.dropdownChangeHandler);
	      main_core_events.EventEmitter.subscribe('BarcodeScanner::onScanEmit', this.onScanEmitHandler);
	    }
	  }, {
	    key: "unsubscribeCustomEvents",
	    value: function unsubscribeCustomEvents() {
	      main_core_events.EventEmitter.unsubscribe('BX.UI.EntityEditor:onSave', this.onSaveHandler);
	      main_core_events.EventEmitter.unsubscribe('BX.UI.EntityEditorAjax:onSubmit', this.onEditorSubmit);
	      main_core_events.EventEmitter.unsubscribe('onFocusToProductList', this.onFocusToProductList);
	      main_core_events.EventEmitter.unsubscribe('Grid::beforeRequest', this.onBeforeGridRequestHandler);
	      main_core_events.EventEmitter.unsubscribe('Grid::updated', this.onGridUpdatedHandler);
	      main_core_events.EventEmitter.unsubscribe('Grid::rowMoved', this.onGridRowMovedHandler);
	      main_core_events.EventEmitter.unsubscribe('BX.Catalog.ProductSelector:onBeforeChange', this.onBeforeProductChangeHandler);
	      main_core_events.EventEmitter.unsubscribe('BX.Catalog.ProductSelector:onChange', this.onProductChangeHandler);
	      main_core_events.EventEmitter.unsubscribe('BX.Catalog.ProductSelector:onClear', this.onProductClearHandler);
	      main_core_events.EventEmitter.unsubscribe('Dropdown::change', this.dropdownChangeHandler);
	      main_core_events.EventEmitter.unsubscribe('BarcodeScanner::onScanEmit', this.onScanEmitHandler);
	    }
	  }, {
	    key: "handleMobileScanEvent",
	    value: function handleMobileScanEvent(event) {
	      var _this$getProductById, _this$getProductById$;

	      var params = event.getData();

	      if (this.scannerToken !== params.id || !main_core.Type.isStringFilled(params.barcode)) {
	        return;
	      }

	      var _iterator = _createForOfIteratorHelper$1(this.products),
	          _step;

	      try {
	        for (_iterator.s(); !(_step = _iterator.n()).done;) {
	          var _product$getBarcodeSe, _product$getBarcodeSe2;

	          var product = _step.value;

	          if (((_product$getBarcodeSe = product.getBarcodeSelector()) === null || _product$getBarcodeSe === void 0 ? void 0 : (_product$getBarcodeSe2 = _product$getBarcodeSe.searchInput) === null || _product$getBarcodeSe2 === void 0 ? void 0 : _product$getBarcodeSe2.getNameInput()) === document.activeElement) {
	            var _product$getBarcodeSe3;

	            (_product$getBarcodeSe3 = product.getBarcodeSelector().searchInput) === null || _product$getBarcodeSe3 === void 0 ? void 0 : _product$getBarcodeSe3.applyScannerData(params.barcode);
	            return;
	          }
	        }
	      } catch (err) {
	        _iterator.e(err);
	      } finally {
	        _iterator.f();
	      }

	      var _iterator2 = _createForOfIteratorHelper$1(this.products),
	          _step2;

	      try {
	        for (_iterator2.s(); !(_step2 = _iterator2.n()).done;) {
	          var _product$getBarcodeSe4, _product$getBarcodeSe5, _product$getSelector, _product$getSelector$;

	          var _product = _step2.value;

	          if (((_product$getBarcodeSe4 = _product.getBarcodeSelector()) === null || _product$getBarcodeSe4 === void 0 ? void 0 : (_product$getBarcodeSe5 = _product$getBarcodeSe4.searchInput) === null || _product$getBarcodeSe5 === void 0 ? void 0 : _product$getBarcodeSe5.getNameInput().value) === '' && ((_product$getSelector = _product.getSelector()) === null || _product$getSelector === void 0 ? void 0 : (_product$getSelector$ = _product$getSelector.searchInput) === null || _product$getSelector$ === void 0 ? void 0 : _product$getSelector$.getNameInput().value) === '') {
	            var _product$getBarcodeSe6;

	            (_product$getBarcodeSe6 = _product.getBarcodeSelector().searchInput) === null || _product$getBarcodeSe6 === void 0 ? void 0 : _product$getBarcodeSe6.applyScannerData(params.barcode);
	            return;
	          }
	        }
	      } catch (err) {
	        _iterator2.e(err);
	      } finally {
	        _iterator2.f();
	      }

	      var newRowId = this.addProductRow();
	      (_this$getProductById = this.getProductById(newRowId)) === null || _this$getProductById === void 0 ? void 0 : (_this$getProductById$ = _this$getProductById.getBarcodeSelector().searchInput) === null || _this$getProductById$ === void 0 ? void 0 : _this$getProductById$.applyScannerData(params.barcode);
	    }
	  }, {
	    key: "selectProductInRow",
	    value: function selectProductInRow(id, productId) {
	      var _this3 = this;

	      if (!main_core.Type.isStringFilled(id) || main_core.Text.toNumber(productId) <= 0) {
	        return;
	      }

	      requestAnimationFrame(function () {
	        var _this3$getProductSele;

	        (_this3$getProductSele = _this3.getProductSelector(id)) === null || _this3$getProductSele === void 0 ? void 0 : _this3$getProductSele.onProductSelect(productId);
	      });
	    }
	  }, {
	    key: "handleOnSave",
	    value: function handleOnSave(event) {
	      var notification = catalog_productModel.ProductModel.getLastActiveSaveNotification();

	      if (notification) {
	        notification.close();
	      }

	      var items = [];
	      this.products.forEach(function (product) {
	        var item = {
	          fields: _objectSpread$1({}, product.fields),
	          rowId: product.fields.ROW_ID
	        };
	        items.push(item);
	      });
	      this.setSettingValue('items', items);
	    }
	  }, {
	    key: "handleEditorSubmit",
	    value: function handleEditorSubmit(event) {}
	  }, {
	    key: "handleProductListFocus",
	    value: function handleProductListFocus(event) {
	      if (this.isReadOnly()) {
	        return;
	      }

	      var listHaveEmptyRows = false;

	      var _iterator3 = _createForOfIteratorHelper$1(this.products),
	          _step3;

	      try {
	        for (_iterator3.s(); !(_step3 = _iterator3.n()).done;) {
	          var product = _step3.value;

	          if (product.isEmptyRow()) {
	            listHaveEmptyRows = true;
	            this.focusProductSelector(product.fields['ROW_ID']);
	            break;
	          }
	        }
	      } catch (err) {
	        _iterator3.e(err);
	      } finally {
	        _iterator3.f();
	      }

	      if (!listHaveEmptyRows) {
	        this.handleProductRowAdd();
	      }
	    }
	  }, {
	    key: "onInnerCancel",
	    value: function onInnerCancel() {
	      this.reloadGrid(false);
	    }
	  }, {
	    key: "changeActivePanelButtons",
	    value: function changeActivePanelButtons(panelCode) {
	      var container = this.getContainer();
	      var activePanel = container.querySelector('.catalog-document-product-list-add-block-' + panelCode);

	      if (main_core.Type.isDomNode(activePanel)) {
	        main_core.Dom.removeClass(activePanel, 'catalog-document-product-list-add-block-hidden');
	        main_core.Dom.addClass(activePanel, 'catalog-document-product-list-add-block-active');
	      }

	      var hiddenPanelCode = panelCode === 'top' ? 'bottom' : 'top';
	      var removePanel = container.querySelector('.catalog-document-product-list-add-block-' + hiddenPanelCode);

	      if (main_core.Type.isDomNode(removePanel)) {
	        main_core.Dom.addClass(removePanel, 'catalog-document-product-list-add-block-hidden');
	        main_core.Dom.removeClass(removePanel, 'catalog-document-product-list-add-block-active');
	      }

	      return activePanel;
	    }
	  }, {
	    key: "reloadGrid",
	    value: function reloadGrid() {
	      var _this4 = this;

	      var useProductsFromRequest = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : true;
	      var isInternalChanging = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : null;

	      if (isInternalChanging === null) {
	        isInternalChanging = !useProductsFromRequest;
	      }

	      this.getGrid().reloadTable('POST', {
	        useProductsFromRequest: useProductsFromRequest
	      }, function () {
	        return _this4.actionUpdateTotalData({
	          isInternalChanging: isInternalChanging
	        });
	      });
	    }
	    /*
	    	keep in mind different actions for this handler:
	    	- native reload by grid actions (columns settings, etc)		- products from request
	    	- rollback													- products from db			this.reloadGrid(false)
	     */

	  }, {
	    key: "handleOnBeforeGridRequest",
	    value: function handleOnBeforeGridRequest(event) {
	      var _this5 = this;

	      var _event$getCompatData = event.getCompatData(),
	          _event$getCompatData2 = babelHelpers.slicedToArray(_event$getCompatData, 2),
	          grid = _event$getCompatData2[0],
	          eventArgs = _event$getCompatData2[1];

	      if (!grid || !grid.parent || grid.parent.getId() !== this.getGridId()) {
	        return;
	      } // reload by native grid actions (columns settings, etc), otherwise by this.reloadGrid()


	      var isNativeAction = !('useProductsFromRequest' in eventArgs.data);
	      var useProductsFromRequest = isNativeAction ? true : eventArgs.data.useProductsFromRequest;
	      eventArgs.url = this.getReloadUrl();
	      eventArgs.method = 'POST';
	      eventArgs.sessid = BX.bitrix_sessid();
	      eventArgs.data = _objectSpread$1(_objectSpread$1({}, eventArgs.data), {}, {
	        useProductsFromRequest: useProductsFromRequest,
	        signedParameters: this.getSignedParameters(),
	        products: useProductsFromRequest ? this.getProductsFields(_classStaticPrivateMethodGet(Editor, Editor, _getAjaxFields).call(Editor)) : null
	      });
	      var isDeletingRequest = false;

	      if (eventArgs.data['action_button_' + eventArgs.gridId] === 'delete') {
	        isDeletingRequest = true;
	      }

	      this.clearEditor();

	      if (isNativeAction) {
	        main_core_events.EventEmitter.subscribeOnce('Grid::updated', function (event) {
	          var _event$getCompatData3 = event.getCompatData(),
	              _event$getCompatData4 = babelHelpers.slicedToArray(_event$getCompatData3, 1),
	              grid = _event$getCompatData4[0];

	          if (!grid || grid.getId() !== _this5.getGridId()) {
	            return;
	          }

	          _this5.actionUpdateTotalData({
	            isInternalChanging: false
	          });

	          if (isDeletingRequest) {
	            _this5.executeActions([{
	              type: _this5.actions.productListChanged
	            }]);
	          }
	        });
	      }
	    }
	  }, {
	    key: "handleOnGridUpdated",
	    value: function handleOnGridUpdated(event) {
	      var _event$getCompatData5 = event.getCompatData(),
	          _event$getCompatData6 = babelHelpers.slicedToArray(_event$getCompatData5, 1),
	          grid = _event$getCompatData6[0];

	      if (!grid || grid.getId() !== this.getGridId()) {
	        return;
	      }

	      this.getSettingsPopup().updateCheckboxState();
	    }
	  }, {
	    key: "handleOnGridRowMoved",
	    value: function handleOnGridRowMoved(event) {
	      var _event$getCompatData7 = event.getCompatData(),
	          _event$getCompatData8 = babelHelpers.slicedToArray(_event$getCompatData7, 3),
	          ids = _event$getCompatData8[0],
	          grid = _event$getCompatData8[2];

	      if (!grid || grid.getId() !== this.getGridId()) {
	        return;
	      }

	      var changed = this.resortProductsByIds(ids);

	      if (changed) {
	        this.refreshSortFields();
	        this.numerateRows();
	        this.executeActions([{
	          type: this.actions.productListChanged
	        }]);
	      }
	    }
	  }, {
	    key: "initPageEventsManager",
	    value: function initPageEventsManager() {
	      var componentId = this.getSettingValue('componentId');
	      this.pageEventsManager = new PageEventsManager({
	        id: componentId
	      });
	    }
	  }, {
	    key: "getPageEventsManager",
	    value: function getPageEventsManager() {
	      if (!this.pageEventsManager) {
	        this.initPageEventsManager();
	      }

	      return this.pageEventsManager;
	    }
	  }, {
	    key: "canEdit",
	    value: function canEdit() {
	      return this.getSettingValue('allowEdit', false) === true;
	    }
	  }, {
	    key: "enableEdit",
	    value: function enableEdit() {
	      // Cannot use editSelected because checkboxes have been removed
	      var rows = this.getGrid().getRows().getRows();
	      rows.forEach(function (current) {
	        if (!current.isHeadChild() && !current.isTemplate() && !isEmptyObject(current.getEditData())) {
	          current.edit();
	        }
	      });
	    }
	  }, {
	    key: "addFirstRowIfEmpty",
	    value: function addFirstRowIfEmpty() {
	      var _this6 = this;

	      if (this.getGrid().getRows().getCountDisplayed() === 0) {
	        requestAnimationFrame(function () {
	          return _this6.addProductRow();
	        });
	      }
	    }
	  }, {
	    key: "clearEditor",
	    value: function clearEditor() {
	      this.unsubscribeProductsEvents();
	      this.products = [];
	      this.productsWasInitiated = false;
	      this.destroySettingsPopup();
	      this.unsubscribeDomEvents();
	      this.unsubscribeCustomEvents();
	      main_core.Event.unbindAll(this.container);
	    }
	  }, {
	    key: "wasProductsInitiated",
	    value: function wasProductsInitiated() {
	      return this.productsWasInitiated;
	    }
	  }, {
	    key: "unsubscribeProductsEvents",
	    value: function unsubscribeProductsEvents() {
	      this.products.forEach(function (current) {
	        var productSelector = current.getSelector();

	        if (productSelector) {
	          productSelector.unsubscribeEvents();
	        }

	        var barcodeSelector = current.getBarcodeSelector();

	        if (barcodeSelector) {
	          barcodeSelector.unsubscribeEvents();
	        }
	      });
	    }
	  }, {
	    key: "destroy",
	    value: function destroy() {
	      this.setForm(null);
	      this.clearController();
	      this.clearEditor();
	    }
	  }, {
	    key: "setController",
	    value: function setController(controller) {
	      if (this.controller === controller) {
	        return;
	      }

	      if (this.controller) {
	        this.controller.clearProductList();
	      }

	      this.controller = controller;
	    }
	  }, {
	    key: "clearController",
	    value: function clearController() {
	      this.controller = null;
	    }
	  }, {
	    key: "getId",
	    value: function getId() {
	      return this.id;
	    }
	  }, {
	    key: "setId",
	    value: function setId(id) {
	      this.id = id;
	    }
	    /* settings tools */

	  }, {
	    key: "getSettings",
	    value: function getSettings() {
	      return this.settings;
	    }
	  }, {
	    key: "setSettings",
	    value: function setSettings(settings) {
	      this.settings = settings ? settings : {};
	    }
	  }, {
	    key: "getSettingValue",
	    value: function getSettingValue(name, defaultValue) {
	      return this.settings.hasOwnProperty(name) ? this.settings[name] : defaultValue;
	    }
	  }, {
	    key: "setSettingValue",
	    value: function setSettingValue(name, value) {
	      this.settings[name] = value;
	    }
	  }, {
	    key: "getComponentName",
	    value: function getComponentName() {
	      return this.getSettingValue('componentName', '');
	    }
	  }, {
	    key: "getReloadUrl",
	    value: function getReloadUrl() {
	      return this.getSettingValue('reloadUrl', '');
	    }
	  }, {
	    key: "getSignedParameters",
	    value: function getSignedParameters() {
	      return this.getSettingValue('signedParameters', '');
	    }
	  }, {
	    key: "getContainerId",
	    value: function getContainerId() {
	      return this.getSettingValue('containerId', '');
	    }
	  }, {
	    key: "getGridId",
	    value: function getGridId() {
	      return this.getSettingValue('gridId', '');
	    }
	  }, {
	    key: "getLanguageId",
	    value: function getLanguageId() {
	      return this.getSettingValue('languageId', '');
	    }
	  }, {
	    key: "getSiteId",
	    value: function getSiteId() {
	      return this.getSettingValue('siteId', '');
	    }
	  }, {
	    key: "getCatalogId",
	    value: function getCatalogId() {
	      return this.getSettingValue('catalogId', 0);
	    }
	  }, {
	    key: "isReadOnly",
	    value: function isReadOnly() {
	      return this.getSettingValue('readOnly', true);
	    }
	  }, {
	    key: "setReadOnly",
	    value: function setReadOnly(readOnly) {
	      this.setSettingValue('readOnly', readOnly);
	    }
	  }, {
	    key: "getCurrencyId",
	    value: function getCurrencyId() {
	      return this.getSettingValue('currencyId', '');
	    }
	  }, {
	    key: "setCurrencyId",
	    value: function setCurrencyId(currencyId) {
	      this.setSettingValue('currencyId', currencyId);
	      return currency_currencyCore.CurrencyCore.loadCurrencyFormat(currencyId);
	    }
	  }, {
	    key: "isSalesOrdersDocument",
	    value: function isSalesOrdersDocument() {
	      var salesOrdersDocumentTypeCodes = ['REALIZATION', 'W'];
	      return salesOrdersDocumentTypeCodes.includes(this.settings.documentType);
	    }
	  }, {
	    key: "changeCurrencyId",
	    value: function changeCurrencyId(currencyId) {
	      var _this7 = this;

	      var oldCurrencyId = this.getCurrencyId();

	      if (oldCurrencyId === currencyId) {
	        return;
	      }

	      this.setCurrencyId(currencyId).then(function () {
	        var products = [];

	        _this7.products.forEach(function (product) {
	          product.getModel().setOption('currency', currencyId);
	          products.push({
	            fields: product.getFields(),
	            id: product.getId()
	          });
	        });

	        if (products.length > 0) {
	          main_core.ajax.runComponentAction(_this7.getComponentName(), 'calculateProductPrices', {
	            mode: 'class',
	            signedParameters: _this7.getSignedParameters(),
	            data: {
	              products: products,
	              currencyId: currencyId,
	              oldCurrencyId: oldCurrencyId
	            }
	          }).then(_this7.onCalculatePricesResponse.bind(_this7));
	        }

	        var editData = _this7.getGridEditData();

	        var templateRow = editData[GRID_TEMPLATE_ROW];
	        templateRow['CURRENCY'] = _this7.getCurrencyId();
	        var templateFieldNames = ['BASE_PRICE', 'PURCHASING_PRICE'];
	        templateFieldNames.forEach(function (field) {
	          if (templateRow[field] && templateRow[field]['CURRENCY']) {
	            templateRow[field]['CURRENCY']['VALUE'] = _this7.getCurrencyId();
	          }
	        });

	        _this7.setGridEditData(editData);
	      });
	    }
	  }, {
	    key: "onCalculatePricesResponse",
	    value: function onCalculatePricesResponse(response) {
	      var products = response.data;
	      this.products.forEach(function (product) {
	        if (main_core.Type.isObject(products[product.getId()])) {
	          product.updateField('BASE_PRICE', products[product.getId()]['BASE_PRICE']);
	          product.updateField('PURCHASING_PRICE', products[product.getId()]['PURCHASING_PRICE']);
	          product.updateUiCurrencyFields();
	        }
	      });
	      this.updateTotalDataDelayed();
	      this.updateTotalUiCurrency();
	    }
	  }, {
	    key: "updateTotalUiCurrency",
	    value: function updateTotalUiCurrency() {
	      var _this8 = this;

	      var totalBlock = BX(this.getSettingValue('totalBlockContainerId', null));

	      if (main_core.Type.isElementNode(totalBlock)) {
	        var totalsList = ['totalCost'];
	        totalBlock.querySelectorAll('.catalog-document-product-list-result-grid-total').forEach(function (row) {
	          var _iterator4 = _createForOfIteratorHelper$1(totalsList),
	              _step4;

	          try {
	            for (_iterator4.s(); !(_step4 = _iterator4.n()).done;) {
	              var totalId = _step4.value;
	              var valueElement = row.querySelector('[data-total="' + totalId + '"]');

	              if (valueElement) {
	                row.innerHTML = currency_currencyCore.CurrencyCore.getPriceControl(valueElement, _this8.getCurrencyId());
	              }
	            }
	          } catch (err) {
	            _iterator4.e(err);
	          } finally {
	            _iterator4.f();
	          }
	        });
	      }
	    }
	  }, {
	    key: "getCurrencyText",
	    value: function getCurrencyText() {
	      var currencyId = this.getCurrencyId();

	      if (!main_core.Type.isStringFilled(currencyId)) {
	        return '';
	      }

	      var format = currency_currencyCore.CurrencyCore.getCurrencyFormat(currencyId);
	      return format && format.FORMAT_STRING.replace(/(^|[^&])#/, '$1').trim() || '';
	    }
	  }, {
	    key: "getDataFieldName",
	    value: function getDataFieldName() {
	      return this.getSettingValue('dataFieldName', '');
	    }
	  }, {
	    key: "getDataSettingsFieldName",
	    value: function getDataSettingsFieldName() {
	      var field = this.getDataFieldName();
	      return main_core.Type.isStringFilled(field) ? field + '_SETTINGS' : '';
	    }
	  }, {
	    key: "getPricePrecision",
	    value: function getPricePrecision() {
	      return this.getSettingValue('pricePrecision', DEFAULT_PRECISION);
	    }
	  }, {
	    key: "getQuantityPrecision",
	    value: function getQuantityPrecision() {
	      return this.getSettingValue('quantityPrecision', DEFAULT_PRECISION);
	    }
	  }, {
	    key: "getCommonPrecision",
	    value: function getCommonPrecision() {
	      return this.getSettingValue('commonPrecision', DEFAULT_PRECISION);
	    }
	  }, {
	    key: "getMeasures",
	    value: function getMeasures() {
	      return this.getSettingValue('measures', []);
	    }
	  }, {
	    key: "getDefaultMeasure",
	    value: function getDefaultMeasure() {
	      return this.getSettingValue('defaultMeasure', {});
	    }
	  }, {
	    key: "getRowIdPrefix",
	    value: function getRowIdPrefix() {
	      return this.getSettingValue('rowIdPrefix', 'catalog_entity_product_list_');
	    }
	    /* settings tools finish */

	    /* calculate tools */

	  }, {
	    key: "parseInt",
	    value: function (_parseInt) {
	      function parseInt(_x) {
	        return _parseInt.apply(this, arguments);
	      }

	      parseInt.toString = function () {
	        return _parseInt.toString();
	      };

	      return parseInt;
	    }(function (value) {
	      var defaultValue = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : 0;
	      var result;
	      var isNumberValue = main_core.Type.isNumber(value);
	      var isStringValue = main_core.Type.isStringFilled(value);

	      if (!isNumberValue && !isStringValue) {
	        return defaultValue;
	      }

	      if (isStringValue) {
	        value = value.replace(/^\s+|\s+$/g, '');
	        var isNegative = value.indexOf('-') === 0;
	        result = parseInt(value.replace(/[^\d]/g, ''), 10);

	        if (isNaN(result)) {
	          result = defaultValue;
	        } else {
	          if (isNegative) {
	            result = -result;
	          }
	        }
	      } else {
	        result = parseInt(value, 10);

	        if (isNaN(result)) {
	          result = defaultValue;
	        }
	      }

	      return result;
	    })
	  }, {
	    key: "parseFloat",
	    value: function (_parseFloat) {
	      function parseFloat(_x2) {
	        return _parseFloat.apply(this, arguments);
	      }

	      parseFloat.toString = function () {
	        return _parseFloat.toString();
	      };

	      return parseFloat;
	    }(function (value) {
	      var precision = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : DEFAULT_PRECISION;
	      var defaultValue = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : 0.0;
	      var result;
	      var isNumberValue = main_core.Type.isNumber(value);
	      var isStringValue = main_core.Type.isStringFilled(value);

	      if (!isNumberValue && !isStringValue) {
	        return defaultValue;
	      }

	      if (isStringValue) {
	        value = value.replace(/^\s+|\s+$/g, '');
	        var dot = value.indexOf('.');
	        var comma = value.indexOf(',');
	        var isNegative = value.indexOf('-') === 0;

	        if (dot < 0 && comma >= 0) {
	          var s1 = value.substr(0, comma);
	          var decimalLength = value.length - comma - 1;

	          if (decimalLength > 0) {
	            s1 += '.' + value.substr(comma + 1, decimalLength);
	          }

	          value = s1;
	        }

	        value = value.replace(/[^\d.]+/g, '');
	        result = parseFloat(value);

	        if (isNaN(result)) {
	          result = defaultValue;
	        }

	        if (isNegative) {
	          result = -result;
	        }
	      } else {
	        result = parseFloat(value);
	      }

	      if (precision >= 0) {
	        result = this.round(result, precision);
	      }

	      return result;
	    })
	  }, {
	    key: "round",
	    value: function round(value) {
	      var precision = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : DEFAULT_PRECISION;
	      var factor = Math.pow(10, precision);
	      return Math.round(value * factor) / factor;
	    }
	    /* calculate tools finish */

	  }, {
	    key: "getContainer",
	    value: function getContainer() {
	      var _this9 = this;

	      return this.cache.remember('container', function () {
	        return document.getElementById(_this9.getContainerId());
	      });
	    }
	  }, {
	    key: "initForm",
	    value: function initForm() {
	      var formId = this.getSettingValue('formId', '');
	      var form = main_core.Type.isStringFilled(formId) ? BX('form_' + formId) : null;

	      if (main_core.Type.isElementNode(form)) {
	        this.setForm(form);
	      }
	    }
	  }, {
	    key: "isExistForm",
	    value: function isExistForm() {
	      return main_core.Type.isElementNode(this.getForm());
	    }
	  }, {
	    key: "getForm",
	    value: function getForm() {
	      return this.form;
	    }
	  }, {
	    key: "setForm",
	    value: function setForm(form) {
	      this.form = form;
	    }
	  }, {
	    key: "initFormFields",
	    value: function initFormFields() {
	      var container = this.getForm();

	      if (main_core.Type.isElementNode(container)) {
	        var field = this.getDataField();

	        if (!main_core.Type.isElementNode(field)) {
	          this.initDataField();
	        }

	        var settingsField = this.getDataSettingsField();

	        if (!main_core.Type.isElementNode(settingsField)) {
	          this.initDataSettingsField();
	        }
	      }
	    }
	  }, {
	    key: "initFormField",
	    value: function initFormField(fieldName) {
	      var container = this.getForm();

	      if (main_core.Type.isElementNode(container) && main_core.Type.isStringFilled(fieldName)) {
	        container.appendChild(main_core.Dom.create('input', {
	          attrs: {
	            type: "hidden",
	            name: fieldName
	          }
	        }));
	      }
	    }
	  }, {
	    key: "removeFormFields",
	    value: function removeFormFields() {
	      var field = this.getDataField();

	      if (main_core.Type.isElementNode(field)) {
	        main_core.Dom.remove(field);
	      }

	      var settingsField = this.getDataSettingsField();

	      if (main_core.Type.isElementNode(settingsField)) {
	        main_core.Dom.remove(settingsField);
	      }
	    }
	  }, {
	    key: "initDataField",
	    value: function initDataField() {
	      this.initFormField(this.getDataFieldName());
	    }
	  }, {
	    key: "initDataSettingsField",
	    value: function initDataSettingsField() {
	      this.initFormField(this.getDataSettingsFieldName());
	    }
	  }, {
	    key: "getFormField",
	    value: function getFormField(fieldName) {
	      var container = this.getForm();

	      if (main_core.Type.isElementNode(container) && main_core.Type.isStringFilled(fieldName)) {
	        return container.querySelector('input[name="' + fieldName + '"]');
	      }

	      return null;
	    }
	  }, {
	    key: "getDataField",
	    value: function getDataField() {
	      return this.getFormField(this.getDataFieldName());
	    }
	  }, {
	    key: "getDataSettingsField",
	    value: function getDataSettingsField() {
	      return this.getFormField(this.getDataSettingsFieldName());
	    }
	  }, {
	    key: "getProductCount",
	    value: function getProductCount() {
	      return this.products.filter(function (item) {
	        return !item.isEmptyRow();
	      }).length;
	    }
	  }, {
	    key: "initProducts",
	    value: function initProducts() {
	      var list = this.getSettingValue('items', []);

	      var _iterator5 = _createForOfIteratorHelper$1(list),
	          _step5;

	      try {
	        for (_iterator5.s(); !(_step5 = _iterator5.n()).done;) {
	          var item = _step5.value;

	          var fields = _objectSpread$1({}, item.fields);

	          this.products.push(new Row(item.rowId, fields, this.getSettingValue('rowSettings', {}), this));
	        }
	      } catch (err) {
	        _iterator5.e(err);
	      } finally {
	        _iterator5.f();
	      }

	      this.numerateRows();
	      this.productsWasInitiated = true;
	      this.updateTotalDataDelayed();
	    }
	  }, {
	    key: "numerateRows",
	    value: function numerateRows() {
	      this.products.forEach(function (product, index) {
	        product.setRowNumber(index + 1);
	      });
	    }
	  }, {
	    key: "getGrid",
	    value: function getGrid() {
	      var _this10 = this;

	      return this.cache.remember('grid', function () {
	        var gridId = _this10.getGridId();

	        if (!main_core.Reflection.getClass('BX.Main.gridManager.getInstanceById')) {
	          throw Error("Cannot find grid with '".concat(gridId, "' id."));
	        }

	        return BX.Main.gridManager.getInstanceById(gridId);
	      });
	    }
	  }, {
	    key: "getGridColumnIndexes",
	    value: function getGridColumnIndexes() {
	      var _this11 = this;

	      return this.cache.remember('getGridColumnIndexes', function () {
	        var result = {};

	        var columns = _this11.getGrid().getHead().querySelectorAll('.main-grid-cell-head');

	        for (var i = 0; i < columns.length; i++) {
	          var node = columns[i];
	          var columnName = node.dataset.name;

	          if (columnName) {
	            result[columnName] = i;
	          }
	        }

	        return result;
	      });
	    }
	  }, {
	    key: "initGridData",
	    value: function initGridData() {
	      var gridEditData = this.getSettingValue('templateGridEditData', null);

	      if (gridEditData) {
	        this.setGridEditData(gridEditData);
	      }
	    }
	  }, {
	    key: "paintColumns",
	    value: function paintColumns() {
	      var paintedColumns = this.getSettingValue('paintedColumns', null);
	      var grid = this.getGrid();

	      if (grid && main_core.Type.isArray(paintedColumns)) {
	        paintedColumns.forEach(function (columnName) {
	          var rows = grid.getRows().getRows();
	          rows.forEach(function (current) {
	            var cell = current.getCellById(columnName);

	            if (cell) {
	              main_core.Dom.addClass(cell, 'main-grid-cell-light-blue-background');
	            }
	          });
	        });
	      }
	    }
	  }, {
	    key: "getGridEditData",
	    value: function getGridEditData() {
	      return this.getGrid().arParams.EDITABLE_DATA;
	    }
	  }, {
	    key: "getColumnInfo",
	    value: function getColumnInfo(code) {
	      var _this$getGrid, _this$getGrid$arParam;

	      return ((_this$getGrid = this.getGrid()) === null || _this$getGrid === void 0 ? void 0 : (_this$getGrid$arParam = _this$getGrid.arParams) === null || _this$getGrid$arParam === void 0 ? void 0 : _this$getGrid$arParam.COLUMNS_ALL[code]) || {};
	    }
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
	    key: "handleProductErrorsChange",
	    value: function handleProductErrorsChange() {
	      if (_classPrivateMethodGet$3(this, _childrenHasErrors, _childrenHasErrors2).call(this)) {
	        this.controller.disableSaveButton();
	      } else {
	        this.controller.enableSaveButton();
	      }
	    }
	  }, {
	    key: "handleFieldChange",
	    value: function handleFieldChange(event) {
	      var row = event.target.closest('tr');

	      if (row && row.hasAttribute('data-id')) {
	        var product = this.getProductById(row.getAttribute('data-id'));

	        if (product) {
	          var fieldCode = event.target.getAttribute('data-name');

	          if (!main_core.Type.isStringFilled(fieldCode)) {
	            var cell = event.target.closest('td');
	            fieldCode = this.getFieldCodeByGridCell(row, cell);
	          }

	          if (fieldCode) {
	            product.updateFieldByEvent(fieldCode, event);
	          }
	        }
	      }
	    }
	  }, {
	    key: "handleFieldBlur",
	    value: function handleFieldBlur(event) {
	      var row = event.target.closest('tr');
	      var value = event.target.value;
	      var fieldCode = event.target.getAttribute('data-name');

	      if (!main_core.Type.isStringFilled(fieldCode)) {
	        var cell = event.target.closest('td');
	        fieldCode = this.getFieldCodeByGridCell(row, cell);
	      }

	      if (this.isSalesOrdersDocument() && fieldCode === 'AMOUNT' && value <= 0) {
	        event.target.value = 1;
	        this.handleFieldChange(event);
	        BX.UI.Notification.Center.notify({
	          width: 'auto',
	          content: main_core.Loc.getMessage('CATALOG_DOCUMENT_PRODUCT_LIST_INVALID_AMOUNT_REALIZATION')
	        });
	      }
	    }
	  }, {
	    key: "handleDropdownChange",
	    value: function handleDropdownChange(event) {
	      var _event$getData = event.getData(),
	          _event$getData2 = babelHelpers.slicedToArray(_event$getData, 5),
	          dropdownId = _event$getData2[0],
	          value = _event$getData2[4];

	      var regExp = new RegExp(this.getRowIdPrefix() + '([A-Za-z0-9]+)_(\\w+)_control', 'i');
	      var matches = dropdownId.match(regExp);

	      if (matches) {
	        var _matches = babelHelpers.slicedToArray(matches, 3),
	            rowId = _matches[1],
	            fieldCode = _matches[2];

	        var product = this.getProductById(rowId);

	        if (product) {
	          product.updateDropdownField(fieldCode, value);
	        }
	      }
	    }
	  }, {
	    key: "getProductById",
	    value: function getProductById(id) {
	      var rowId = this.getRowIdPrefix() + id;
	      return this.getProductByRowId(rowId);
	    }
	  }, {
	    key: "getProductByRowId",
	    value: function getProductByRowId(rowId) {
	      return this.products.find(function (row) {
	        return row.getId() === rowId;
	      });
	    }
	  }, {
	    key: "getFieldCodeByGridCell",
	    value: function getFieldCodeByGridCell(row, cell) {
	      if (!main_core.Type.isDomNode(row) || !main_core.Type.isDomNode(cell)) {
	        return null;
	      }

	      var grid = this.getGrid();

	      if (grid) {
	        var headRow = grid.getRows().getHeadFirstChild();
	        var index = babelHelpers.toConsumableArray(row.cells).indexOf(cell);
	        return headRow.getCellNameByCellIndex(index);
	      }

	      return null;
	    }
	  }, {
	    key: "addProductRow",
	    value: function addProductRow() {
	      var anchorProduct = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : null;
	      var row = this.createGridProductRow();
	      var newId = row.getId();

	      if (anchorProduct) {
	        var _this$getGrid$getRows;

	        var anchorRowNode = (_this$getGrid$getRows = this.getGrid().getRows().getById(anchorProduct.getField('ID'))) === null || _this$getGrid$getRows === void 0 ? void 0 : _this$getGrid$getRows.getNode();

	        if (anchorRowNode) {
	          anchorRowNode.parentNode.insertBefore(row.getNode(), anchorRowNode.nextSibling);
	        }
	      }

	      this.initializeNewProductRow(newId, anchorProduct);
	      this.getGrid().bindOnRowEvents();
	      return newId;
	    }
	  }, {
	    key: "handleProductRowAdd",
	    value: function handleProductRowAdd() {
	      var id = this.addProductRow();
	      this.focusProductSelector(id);
	    }
	  }, {
	    key: "handleProductRowCreate",
	    value: function handleProductRowCreate() {}
	  }, {
	    key: "handleShowBarcodeSettingsPopup",
	    value: function handleShowBarcodeSettingsPopup() {
	      this.getSettingsPopup().show();
	    }
	  }, {
	    key: "handleShowSettingsPopup",
	    value: function handleShowSettingsPopup() {
	      this.getSettingsPopup().show();
	    }
	  }, {
	    key: "destroySettingsPopup",
	    value: function destroySettingsPopup() {
	      if (this.cache.has('settings-popup')) {
	        this.cache.get('settings-popup').getPopup().destroy();
	        this.cache["delete"]('settings-popup');
	      }
	    }
	  }, {
	    key: "getSettingsPopup",
	    value: function getSettingsPopup() {
	      var _this12 = this;

	      return this.cache.remember('settings-popup', function () {
	        return new SettingsPopup(_this12.getContainer().querySelector('.catalog-document-product-list-add-block-active [data-role="product-list-settings-button"]'), _this12.getSettingValue('popupSettings', []), _this12);
	      });
	    }
	  }, {
	    key: "getHintPopup",
	    value: function getHintPopup() {
	      var _this13 = this;

	      return this.cache.remember('hint-popup', function () {
	        return new HintPopup(_this13);
	      });
	    }
	  }, {
	    key: "createGridProductRow",
	    value: function createGridProductRow() {
	      var newId = main_core.Text.getRandom();
	      var originalTemplate = this.redefineTemplateEditData(newId);
	      var grid = this.getGrid();
	      var newRow;

	      if (this.getSettingValue('newRowPosition') === 'bottom') {
	        newRow = grid.appendRowEditor();
	      } else {
	        newRow = grid.prependRowEditor();
	      }

	      var newNode = newRow.getNode();

	      if (main_core.Type.isDomNode(newNode)) {
	        newNode.setAttribute('data-id', newId);
	        newRow.makeCountable();
	      }

	      if (originalTemplate) {
	        this.setOriginalTemplateEditData(originalTemplate);
	      }

	      main_core_events.EventEmitter.emit('Grid::thereEditedRows', []);
	      grid.adjustRows();
	      grid.updateCounterDisplayed();
	      grid.updateCounterSelected();
	      return newRow;
	    }
	  }, {
	    key: "handleDeleteRow",
	    value: function handleDeleteRow(rowId, event) {
	      event.preventDefault();
	      var row = this.getProductByRowId(rowId);

	      if (row) {
	        this.deleteRow(rowId);
	      }
	    }
	  }, {
	    key: "redefineTemplateEditData",
	    value: function redefineTemplateEditData(newId) {
	      var data = this.getGridEditData();
	      var originalTemplateData = data[GRID_TEMPLATE_ROW];
	      var customEditData = this.prepareCustomEditData(originalTemplateData, newId);
	      this.setOriginalTemplateEditData(_objectSpread$1(_objectSpread$1({}, originalTemplateData), customEditData));
	      return originalTemplateData;
	    }
	  }, {
	    key: "prepareCustomEditData",
	    value: function prepareCustomEditData(originalEditData, newId) {
	      var customEditData = {};
	      var templateIdMask = this.getSettingValue('templateIdMask', '');

	      for (var i in originalEditData) {
	        if (originalEditData.hasOwnProperty(i)) {
	          if (main_core.Type.isStringFilled(originalEditData[i]) && originalEditData[i].indexOf(templateIdMask) >= 0) {
	            customEditData[i] = originalEditData[i].replace(new RegExp(templateIdMask, 'g'), newId);
	          } else if (main_core.Type.isPlainObject(originalEditData[i])) {
	            customEditData[i] = this.prepareCustomEditData(originalEditData[i], newId);
	          } else {
	            customEditData[i] = originalEditData[i];
	          }
	        }
	      }

	      return customEditData;
	    }
	  }, {
	    key: "initializeNewProductRow",
	    value: function initializeNewProductRow(newId) {
	      var anchorProduct = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : null;
	      var fields = {};

	      if (anchorProduct !== null) {
	        fields = Object.assign(fields, anchorProduct === null || anchorProduct === void 0 ? void 0 : anchorProduct.getFields());
	      } else {
	        fields = _objectSpread$1(_objectSpread$1({}, this.getSettingValue('templateItemFields', {})), {
	          CURRENCY: this.getCurrencyId()
	        });
	      }

	      if (main_core.Type.isNil(anchorProduct) && this.products.length > 0) {
	        var previousRow = this.getSettingValue('newRowPosition') === 'bottom' ? this.products[this.products.length - 1] : this.products[0];
	        var stores = this.getSettingValue('stores', {});
	        var storeFields = previousRow.getSettingValue('storeHeaderMap', {});
	        Object.values(storeFields).forEach(function (field) {
	          var previousStoreValue = previousRow.getField(field);

	          if (main_core.Type.isNil(stores[previousStoreValue])) {
	            return;
	          }

	          fields[field] = previousRow.getField(field);
	          var titleName = field + '_TITLE';
	          fields[titleName] = previousRow.getField(titleName);
	        });
	      }

	      var rowId = this.getRowIdPrefix() + newId;
	      fields.ID = newId;
	      fields.ROW_ID = newId;

	      if (main_core.Type.isObject(fields.IMAGE_INFO)) {
	        delete fields.IMAGE_INFO.input;
	      }

	      var product = new Row(rowId, fields, this.getSettingValue('rowSettings', {}), this);

	      if (anchorProduct instanceof Row) {
	        this.products.splice(1 + this.products.indexOf(anchorProduct), 0, product);
	        product.refreshFieldsLayout();
	      } else if (this.getSettingValue('newRowPosition') === 'bottom') {
	        this.products.push(product);
	      } else {
	        this.products.unshift(product);
	      }

	      this.refreshSortFields();
	      this.numerateRows();
	      product.updateUiCurrencyFields();
	      this.updateTotalUiCurrency();
	      return product;
	    }
	  }, {
	    key: "getProductSelector",
	    value: function getProductSelector(newId) {
	      return this.getProductById(newId).getSelector();
	    }
	  }, {
	    key: "focusProductSelector",
	    value: function focusProductSelector(newId) {
	      var _this14 = this;

	      requestAnimationFrame(function () {
	        var _this14$getProductSel;

	        (_this14$getProductSel = _this14.getProductSelector(newId)) === null || _this14$getProductSel === void 0 ? void 0 : _this14$getProductSel.searchInDialog().focusName();
	      });
	    }
	  }, {
	    key: "handleOnBeforeProductChange",
	    value: function handleOnBeforeProductChange(event) {
	      var data = event.getData();
	      var product = this.getProductByRowId(data.rowId);

	      if (product) {
	        this.getGrid().tableFade();
	        product.resetExternalActions();
	      }
	    }
	  }, {
	    key: "handleOnProductChange",
	    value: function handleOnProductChange(event) {
	      var data = event.getData();
	      var productRow = this.getProductByRowId(data.rowId);

	      if (productRow && data.fields) {
	        var _productRow$getSelect, _productRow$getBarcod;

	        delete data.fields.ID;
	        productRow.setFields(data.fields);
	        Object.keys(data.fields).forEach(function (key) {
	          productRow.updateFieldValue(key, data.fields[key]);
	        });
	        productRow.setField('IS_NEW', data.isNew ? 'Y' : 'N');
	        (_productRow$getSelect = productRow.getSelector()) === null || _productRow$getSelect === void 0 ? void 0 : _productRow$getSelect.layout();
	        (_productRow$getBarcod = productRow.getBarcodeSelector()) === null || _productRow$getBarcod === void 0 ? void 0 : _productRow$getBarcod.layout();
	        productRow.updateProductStoreValues();
	        productRow.initHandlersForSelectors();
	        productRow.layoutStoreSelector(productRow.getSettingValue('storeHeaderMap', {}));
	        productRow.layoutBarcode();
	        productRow.executeExternalActions();

	        if (this.isSalesOrdersDocument()) {
	          productRow.changeAmount(productRow.getAmount() > 0 ? productRow.getAmount() : 1);
	        }

	        this.getGrid().tableUnfade();
	      } else {
	        this.getGrid().tableUnfade();
	      }
	    }
	  }, {
	    key: "handleOnProductClear",
	    value: function handleOnProductClear(event) {
	      var _event$getData3 = event.getData(),
	          selectorId = _event$getData3.selectorId,
	          rowId = _event$getData3.rowId;

	      var product = this.getProductByRowId(rowId);

	      if (product && product.getSelector().getId() === selectorId) {
	        var _product$getBarcodeSe7;

	        product.initHandlersForSelectors();
	        product.setMeasure(this.getDefaultMeasure());
	        product.changePurchasingPrice(0);
	        product.changeBasePrice(0);
	        product.changeAmount(0);
	        product.updateUiStoreValues();
	        product.updateProductStoreValues();
	        product.changeBarcode('');
	        (_product$getBarcodeSe7 = product.getBarcodeSelector()) === null || _product$getBarcodeSe7 === void 0 ? void 0 : _product$getBarcodeSe7.setConfig('ENABLE_SEARCH', true).layout();
	        product.executeExternalActions();
	      }
	    }
	  }, {
	    key: "compileProductData",
	    value: function compileProductData() {
	      if (!this.isExistForm()) {
	        return;
	      }

	      this.initFormFields();
	      var field = this.getDataField();
	      var settingsField = this.getDataSettingsField();
	      this.cleanProductRows();

	      if (main_core.Type.isElementNode(field) && main_core.Type.isElementNode(settingsField)) {
	        field.value = this.prepareProductDataValue();
	      }
	    }
	  }, {
	    key: "prepareProductDataValue",
	    value: function prepareProductDataValue() {
	      var productDataValue = '';

	      if (this.getProductCount()) {
	        var productData = [];
	        this.products.forEach(function (item) {
	          var itemFields = item.getFields(_classStaticPrivateMethodGet(Editor, Editor, _getAjaxFields).call(Editor));

	          if (!/^[0-9]+$/.test(itemFields['ID'])) {
	            itemFields['ID'] = 0;
	          }

	          itemFields['CUSTOMIZED'] = 'Y';
	          productData.push(itemFields);
	        });
	        productDataValue = JSON.stringify(productData);
	      }

	      return productDataValue;
	    }
	  }, {
	    key: "executeActions",

	    /* actions */
	    value: function executeActions(actions) {
	      if (!main_core.Type.isArrayFilled(actions)) {
	        return;
	      }

	      var _iterator6 = _createForOfIteratorHelper$1(actions),
	          _step6;

	      try {
	        for (_iterator6.s(); !(_step6 = _iterator6.n()).done;) {
	          var item = _step6.value;

	          if (!main_core.Type.isPlainObject(item) || !main_core.Type.isStringFilled(item.type)) {
	            continue;
	          }

	          switch (item.type) {
	            case this.actions.productChange:
	              this.actionSendProductChange(item);
	              break;

	            case this.actions.productListChanged:
	              this.actionSendProductListChanged();
	              break;

	            case this.actions.updateTotal:
	              this.actionUpdateTotalData();
	              break;
	          }
	        }
	      } catch (err) {
	        _iterator6.e(err);
	      } finally {
	        _iterator6.f();
	      }
	    }
	  }, {
	    key: "actionSendProductChange",
	    value: function actionSendProductChange(item) {
	      if (!main_core.Type.isStringFilled(item.id)) {
	        return;
	      }

	      var product = this.getProductByRowId(item.id);

	      if (!product) {
	        return;
	      } // EventEmitter.emit(this, 'ProductList::onChangeFields', {
	      // 	rowId: item.id,
	      // 	productId: product.getField('PRODUCT_ID'),
	      // 	fields: this.getProductByRowId(item.id).getCatalogFields()
	      // });


	      if (this.controller) {
	        this.controller.productChange();
	      }
	    }
	  }, {
	    key: "actionSendProductListChanged",
	    value: function actionSendProductListChanged() {
	      var disableSaveButton = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : false;

	      if (this.controller) {
	        this.controller.productChange(disableSaveButton);
	      }
	    }
	  }, {
	    key: "actionUpdateListField",
	    value: function actionUpdateListField(item) {
	      if (!main_core.Type.isStringFilled(item.field) || !('value' in item)) {
	        return;
	      }

	      this.updateFieldForList = item.field;

	      var _iterator7 = _createForOfIteratorHelper$1(this.products),
	          _step7;

	      try {
	        for (_iterator7.s(); !(_step7 = _iterator7.n()).done;) {
	          var row = _step7.value;
	          row.updateFieldByName(item.field, item.value);
	        }
	      } catch (err) {
	        _iterator7.e(err);
	      } finally {
	        _iterator7.f();
	      }

	      this.updateFieldForList = null;
	    }
	  }, {
	    key: "actionUpdateTotalData",
	    value: function actionUpdateTotalData() {
	      var options = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};
	      this.updateTotalDataDelayedHandler(options);
	    }
	    /* actions finish */

	  }, {
	    key: "updateTotalDataDelayed",
	    value: function updateTotalDataDelayed() {
	      var totalCost = 0;
	      var field = this.getSettingValue('totalCalculationSumField', 'PURCHASING_PRICE');
	      this.products.forEach(function (item) {
	        return totalCost += main_core.Text.toNumber(item.getField(field)) * main_core.Text.toNumber(item.getField('AMOUNT'));
	      });
	      this.setTotalData({
	        totalCost: totalCost
	      });
	    }
	  }, {
	    key: "getProductsFields",
	    value: function getProductsFields() {
	      var fields = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : [];
	      var productFields = [];

	      var _iterator8 = _createForOfIteratorHelper$1(this.products),
	          _step8;

	      try {
	        for (_iterator8.s(); !(_step8 = _iterator8.n()).done;) {
	          var item = _step8.value;
	          productFields.push(item.getFields(fields));
	        }
	      } catch (err) {
	        _iterator8.e(err);
	      } finally {
	        _iterator8.f();
	      }

	      return productFields;
	    }
	  }, {
	    key: "setTotalData",
	    value: function setTotalData(data) {
	      var _this$controller;

	      var item = BX(this.getSettingValue('totalBlockContainerId', null));

	      if (main_core.Type.isElementNode(item)) {
	        var currencyId = this.getCurrencyId();
	        var list = ['totalCost'];

	        for (var _i = 0, _list = list; _i < _list.length; _i++) {
	          var id = _list[_i];
	          var row = item.querySelector('[data-total="' + id + '"]');

	          if (main_core.Type.isElementNode(row) && id in data) {
	            row.innerHTML = currency_currencyCore.CurrencyCore.currencyFormat(data[id], currencyId, false);
	          }
	        }
	      }

	      (_this$controller = this.controller) === null || _this$controller === void 0 ? void 0 : _this$controller.setTotal(data);
	    }
	    /* action tools finish */

	    /* ajax tools */
	    // ajaxRequest(action, data)
	    // {
	    // 	if (!Type.isPlainObject(data.options))
	    // 	{
	    // 		data.options = {};
	    // 	}
	    // 	data.options.ACTION = action;
	    // 	ajax.runComponentAction(
	    // 		this.getComponentName(),
	    // 		action,
	    // 		{
	    // 			mode: 'class',
	    // 			signedParameters: this.getSignedParameters(),
	    // 			data: data
	    // 		}
	    // 	).then(
	    // 		(response) => this.ajaxResultSuccess(response, data.options),
	    // 		(response) => this.ajaxResultFailure(response)
	    // 	);
	    // }
	    //
	    // ajaxResultSuccess(response, requestOptions)
	    // {
	    // 	if (!this.ajaxResultCommonCheck(response))
	    // 	{
	    // 		return;
	    // 	}
	    //
	    // 	switch (response.data.action)
	    // 	{
	    // 		case 'calculateTotalData':
	    // 			// if (Type.isPlainObject(response.data.result))
	    // 			// {
	    // 			// 	this.setTotalData(response.data.result, requestOptions);
	    // 			// }
	    //
	    // 			break;
	    // 		case 'calculateProductPrices':
	    // 			if (Type.isPlainObject(response.data.result))
	    // 			{
	    // 				this.onCalculatePricesResponse(response.data.result);
	    // 			}
	    //
	    // 			break;
	    // 	}
	    // }
	    // ajaxResultFailure(response)
	    // {
	    //
	    // }

	  }, {
	    key: "ajaxResultCommonCheck",
	    value: function ajaxResultCommonCheck(responce) {
	      if (!main_core.Type.isPlainObject(responce)) {
	        return false;
	      }

	      if (!main_core.Type.isStringFilled(responce.status)) {
	        return false;
	      }

	      if (responce.status !== 'success') {
	        return false;
	      }

	      if (!main_core.Type.isPlainObject(responce.data)) {
	        return false;
	      }

	      if (!main_core.Type.isStringFilled(responce.data.action)) {
	        return false;
	      } // noinspection RedundantIfStatementJS


	      if (!('result' in responce.data)) {
	        return false;
	      }

	      return true;
	    }
	  }, {
	    key: "deleteRow",
	    value: function deleteRow(row) {
	      var gridRow = this.getGrid().getRows().getById(row.getField('ID'));

	      if (gridRow) {
	        main_core.Dom.remove(gridRow.getNode());
	        this.getGrid().getRows().reset();
	      }

	      var index = this.products.indexOf(row);

	      if (index > -1) {
	        this.products.splice(index, 1);
	        this.refreshSortFields();
	        this.numerateRows();
	      }

	      main_core_events.EventEmitter.emit('Grid::thereEditedRows', []);
	      this.addFirstRowIfEmpty();
	      this.executeActions([{
	        type: this.actions.productListChanged
	      }, {
	        type: this.actions.updateTotal
	      }]);
	    }
	  }, {
	    key: "copyRow",
	    value: function copyRow(row) {
	      this.addProductRow(row);
	      this.refreshSortFields();
	      this.numerateRows();
	      main_core_events.EventEmitter.emit('Grid::thereEditedRows', []);
	      this.executeActions([{
	        type: this.actions.productListChanged
	      }, {
	        type: this.actions.updateTotal
	      }]);
	    }
	  }, {
	    key: "cleanProductRows",
	    value: function cleanProductRows() {
	      var _this15 = this;

	      this.products.filter(function (item) {
	        return item.isEmptyRow();
	      }).forEach(function (row) {
	        return _this15.deleteRow(row);
	      });
	    }
	  }, {
	    key: "resortProductsByIds",
	    value: function resortProductsByIds(ids) {
	      var changed = false;

	      if (main_core.Type.isArrayFilled(ids)) {
	        this.products.sort(function (a, b) {
	          if (ids.indexOf(a.getField('ID')) > ids.indexOf(b.getField('ID'))) {
	            return 1;
	          }

	          changed = true;
	          return -1;
	        });
	      }

	      return changed;
	    }
	  }, {
	    key: "refreshSortFields",
	    value: function refreshSortFields() {
	      this.products.forEach(function (item, index) {
	        return item.setField('SORT', (index + 1) * 10, false);
	      });
	    }
	  }, {
	    key: "handleOnTabShow",
	    value: function handleOnTabShow() {
	      main_core_events.EventEmitter.emit('onDemandRecalculateWrapper', [this]);
	    }
	  }, {
	    key: "closeBarcodeSpotlights",
	    value: function closeBarcodeSpotlights() {
	      this.products.forEach(function (product) {
	        var _product$getBarcodeSe8;

	        (_product$getBarcodeSe8 = product.getBarcodeSelector()) === null || _product$getBarcodeSe8 === void 0 ? void 0 : _product$getBarcodeSe8.removeSpotlight();
	      });
	      this.setSettingValue('showBarcodeSpotlightInfo', false);
	    }
	  }, {
	    key: "closeBarcodeQrAuths",
	    value: function closeBarcodeQrAuths() {
	      this.products.forEach(function (product) {
	        var _product$getBarcodeSe9;

	        (_product$getBarcodeSe9 = product.getBarcodeSelector()) === null || _product$getBarcodeSe9 === void 0 ? void 0 : _product$getBarcodeSe9.removeQrAuth();
	      });
	      this.setSettingValue('showBarcodeQrAuth', false);
	    }
	  }, {
	    key: "enableSendBarcodeMobilePush",
	    value: function enableSendBarcodeMobilePush() {
	      this.products.forEach(function (product) {
	        var _product$getBarcodeSe10;

	        (_product$getBarcodeSe10 = product.getBarcodeSelector()) === null || _product$getBarcodeSe10 === void 0 ? void 0 : _product$getBarcodeSe10.setConfig('IS_INSTALLED_MOBILE_APP', true);
	      });
	      this.setSettingValue('isInstalledMobileApp', true);
	    }
	  }, {
	    key: "validate",
	    value: function validate() {
	      if (this.getProductCount() === 0) {
	        return [main_core.Loc.getMessage('CATALOG_DOCUMENT_PRODUCT_LIST_IS_EMPTY')];
	      }

	      var errorsArray = [];
	      this.products.forEach(function (product) {
	        errorsArray = errorsArray.concat(product.validate());
	      });
	      return errorsArray;
	    }
	  }, {
	    key: "showFieldTourHint",
	    value: function showFieldTourHint(fieldName, tourData, endTourHandler) {
	      var addictedFields = arguments.length > 3 && arguments[3] !== undefined ? arguments[3] : [];
	      var rowId = arguments.length > 4 && arguments[4] !== undefined ? arguments[4] : '';

	      if (this.products.length > 0) {
	        var productNode = this.products[0].getNode();

	        if (this.getProductByRowId(rowId)) {
	          productNode = this.getProductByRowId(rowId).getNode();
	        }

	        var addictedNodes = [];

	        var _iterator9 = _createForOfIteratorHelper$1(addictedFields),
	            _step9;

	        try {
	          for (_iterator9.s(); !(_step9 = _iterator9.n()).done;) {
	            var _fieldName = _step9.value;

	            var _fieldNode = productNode.querySelector("[data-name=\"".concat(_fieldName, "\"]"));

	            if (_fieldNode !== null) {
	              addictedNodes.push(_fieldNode);
	            }
	          }
	        } catch (err) {
	          _iterator9.e(err);
	        } finally {
	          _iterator9.f();
	        }

	        var fieldNode = productNode.querySelector("[data-name=\"".concat(fieldName, "\"]"));

	        if (fieldNode !== null) {
	          babelHelpers.classPrivateFieldGet(this, _fieldHintManager).processFieldTour(fieldNode, tourData, endTourHandler, addictedNodes);
	        }
	      }
	    }
	  }, {
	    key: "getActiveHint",
	    value: function getActiveHint() {
	      return babelHelpers.classPrivateFieldGet(this, _fieldHintManager).getActiveHint();
	    }
	  }, {
	    key: "getRestrictedProductTypes",
	    value: function getRestrictedProductTypes() {
	      return this.getSettingValue('restrictedProductTypes', []);
	    }
	  }, {
	    key: "processApplyActionButtonClick",
	    value: function processApplyActionButtonClick(actionId) {
	      if (actionId === 'STORE_FROM_INFO' || actionId === 'STORE_TO_INFO') {
	        _classPrivateMethodGet$3(this, _processSetStoryAction, _processSetStoryAction2).call(this, actionId);
	      }
	    }
	  }]);
	  return Editor;
	}();

	function _initSupportCustomRowActions2() {
	  this.getGrid()._clickOnRowActionsButton = function () {};
	}

	function _childrenHasErrors2() {
	  return this.products.filter(function (product) {
	    return product.getModel().getErrorCollection().hasErrors();
	  }).length > 0;
	}

	function _getAjaxFields() {
	  return ['ID', 'SKU_ID', 'AMOUNT', 'PURCHASING_PRICE', 'BASE_PRICE', 'BASE_PRICE_EXTRA', 'BASE_PRICE_EXTRA_RATE', 'DOC_BARCODE', 'BARCODE', 'STORE_TO', 'STORE_FROM', 'BASE_PRICE_ID', 'BASKET_ID', 'DOC_ID', 'ELEMENT_ID', 'IBLOCK_ID', 'MEASURE_CODE', 'MEASURE_NAME', 'NAME', 'OFFERS_IBLOCK_ID', 'PARENT_PRODUCT_ID', 'PRODUCT_ID', 'ROW_ID', 'STORE_FROM_AMOUNT', 'STORE_FROM_AVAILABLE_AMOUNT', 'STORE_FROM_RESERVED', 'STORE_FROM_TITLE', 'STORE_TO_AMOUNT', 'STORE_TO_AVAILABLE_AMOUNT', 'STORE_TO_RESERVED', 'STORE_TO_TITLE', 'TOTAL_PRICE', 'TYPE'];
	}

	function _processSetStoryAction2(actionId) {
	  var _this$getGrid2,
	      _this16 = this;

	  var actionPanel = (_this$getGrid2 = this.getGrid()) === null || _this$getGrid2 === void 0 ? void 0 : _this$getGrid2.getActionsPanel();
	  var actionValues = actionPanel === null || actionPanel === void 0 ? void 0 : actionPanel.getValues();
	  var actionStoreId = actionValues[actionId];

	  if (!actionValues || main_core.Type.isUndefined(actionStoreId)) {
	    return;
	  }

	  var selectedRows = this.getGrid().getRows().getSelected();

	  if (selectedRows.length === 0) {
	    return;
	  }

	  var stores = this.getSettingValue('stores', {});
	  var actionStore = stores[actionStoreId];

	  if (!main_core.Type.isNil(actionStore)) {
	    var actionStoreName = (actionStore === null || actionStore === void 0 ? void 0 : actionStore.TITLE) || '';
	    selectedRows.forEach(function (row) {
	      var selectedItem = _this16.products.find(function (product) {
	        return product.getField('ID') === row.getId();
	      });

	      if (selectedItem) {
	        var storeSelector = catalog_storeSelector.StoreSelector.getById(selectedItem.getId() + '_' + actionId);

	        if (storeSelector) {
	          storeSelector.onStoreSelect(actionStoreId, actionStoreName);
	        }
	      }
	    });
	    var documentTypeMoving = 'M';
	    var messageId = this.settings.documentType !== documentTypeMoving ? 'CATALOG_DOCUMENT_PRODUCT_LIST_ACTION_STORE_CHANGED_HINT' : 'CATALOG_DOCUMENT_PRODUCT_LIST_ACTION_' + actionId + '_CHANGED_HINT';
	    ui_notification.UI.Notification.Center.notify({
	      content: main_core.Loc.getMessage(messageId, {
	        '#STORE_NAME#': main_core.Text.encode(actionStoreName)
	      }),
	      autoHide: true,
	      autoHideDelay: 4000
	    });
	  }

	  var dropdown = actionPanel.getDropdowns().find(function (dropdown) {
	    return dropdown.id === 'actionListId_control';
	  });

	  if (dropdown) {
	    actionPanel.removeItemsRelativeCurrent(dropdown.parentNode);
	    main_core.Dom.attr(dropdown, 'data-value', null);
	    var innerWrapper = dropdown.querySelector('.main-dropdown-inner');

	    if (innerWrapper) {
	      innerWrapper.innerText = main_core.Loc.getMessage('CATALOG_DOCUMENT_PRODUCT_LIST_ACTION_DEFAULT');
	    }
	  }
	}

	exports.Editor = Editor;
	exports.PageEventsManager = PageEventsManager;

}((this.BX.Catalog.Store.ProductList = this.BX.Catalog.Store.ProductList || {}),BX.Main,BX.Event,BX.Currency,BX.Catalog,BX.Catalog,BX.Catalog.DocumentCard,BX.Catalog,BX,BX,BX.UI.Tour,BX));
//# sourceMappingURL=script.js.map
