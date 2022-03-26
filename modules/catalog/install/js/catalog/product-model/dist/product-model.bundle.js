this.BX = this.BX || {};
(function (exports,main_core_events,catalog_productCalculator,main_core,catalog_productModel) {
	'use strict';

	var ErrorCollection = /*#__PURE__*/function () {
	  function ErrorCollection() {
	    var model = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};
	    babelHelpers.classCallCheck(this, ErrorCollection);
	    babelHelpers.defineProperty(this, "errors", new Map());
	    this.model = model;
	  }

	  babelHelpers.createClass(ErrorCollection, [{
	    key: "getErrors",
	    value: function getErrors() {
	      return Object.fromEntries(this.errors);
	    }
	  }, {
	    key: "setError",
	    value: function setError(code, text) {
	      this.errors.set(code, {
	        code: code,
	        text: text
	      });
	      this.model.onErrorCollectionChange();
	      return this;
	    }
	  }, {
	    key: "removeError",
	    value: function removeError(code) {
	      if (this.errors.has(code)) {
	        this.errors.delete(code, text);
	      }

	      this.model.onErrorCollectionChange();
	      return this;
	    }
	  }, {
	    key: "clearErrors",
	    value: function clearErrors() {
	      this.errors.clear();
	      this.model.onErrorCollectionChange();
	      return this;
	    }
	  }, {
	    key: "hasErrors",
	    value: function hasErrors() {
	      return this.errors.size > 0;
	    }
	  }]);
	  return ErrorCollection;
	}();

	function _classPrivateFieldInitSpec(obj, privateMap, value) { _checkPrivateRedeclaration(obj, privateMap); privateMap.set(obj, value); }

	function _checkPrivateRedeclaration(obj, privateCollection) { if (privateCollection.has(obj)) { throw new TypeError("Cannot initialize the same private elements twice on an object"); } }

	var _isEnabledSaving = /*#__PURE__*/new WeakMap();

	var _preview = /*#__PURE__*/new WeakMap();

	var _editInput = /*#__PURE__*/new WeakMap();

	var ImageCollection = /*#__PURE__*/function () {
	  function ImageCollection() {
	    var model = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};
	    babelHelpers.classCallCheck(this, ImageCollection);

	    _classPrivateFieldInitSpec(this, _isEnabledSaving, {
	      writable: true,
	      value: false
	    });

	    _classPrivateFieldInitSpec(this, _preview, {
	      writable: true,
	      value: ''
	    });

	    _classPrivateFieldInitSpec(this, _editInput, {
	      writable: true,
	      value: ''
	    });

	    this.model = model;
	  }

	  babelHelpers.createClass(ImageCollection, [{
	    key: "isEnableFileSaving",
	    value: function isEnableFileSaving() {
	      return babelHelpers.classPrivateFieldGet(this, _isEnabledSaving);
	    }
	  }, {
	    key: "enableFileSaving",
	    value: function enableFileSaving() {
	      babelHelpers.classPrivateFieldSet(this, _isEnabledSaving, true);
	    }
	  }, {
	    key: "getMorePhotoValues",
	    value: function getMorePhotoValues() {
	      return this.morePhoto;
	    }
	  }, {
	    key: "setMorePhotoValues",
	    value: function setMorePhotoValues(values) {
	      this.morePhoto = main_core.Type.isPlainObject(values) ? values : {};
	    }
	  }, {
	    key: "removeMorePhotoItem",
	    value: function removeMorePhotoItem(fileId) {
	      for (var index in this.morePhoto) {
	        var value = this.morePhoto[index];

	        if (!main_core.Type.isObject(value)) {
	          value = main_core.Text.toInteger(value);
	        }

	        if (main_core.Type.isNumber(value) && value === main_core.Text.toInteger(fileId) || main_core.Type.isObject(value) && value.fileId === fileId) {
	          delete this.morePhoto[index];
	          return true;
	        }
	      }

	      return false;
	    }
	  }, {
	    key: "setPreview",
	    value: function setPreview(html) {
	      babelHelpers.classPrivateFieldSet(this, _preview, main_core.Type.isStringFilled(html) ? html : '');
	      return this;
	    }
	  }, {
	    key: "setEditInput",
	    value: function setEditInput(html) {
	      babelHelpers.classPrivateFieldSet(this, _editInput, main_core.Type.isStringFilled(html) ? html : '');
	      return this;
	    }
	  }, {
	    key: "getPreview",
	    value: function getPreview() {
	      return babelHelpers.classPrivateFieldGet(this, _preview) || '';
	    }
	  }, {
	    key: "getEditInput",
	    value: function getEditInput() {
	      return babelHelpers.classPrivateFieldGet(this, _editInput) || '';
	    }
	  }, {
	    key: "addMorePhotoItem",
	    value: function addMorePhotoItem(fileId, value) {
	      this.morePhoto[fileId] = value;
	    }
	  }]);
	  return ImageCollection;
	}();

	var FieldCollection = /*#__PURE__*/function () {
	  function FieldCollection() {
	    var model = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};
	    babelHelpers.classCallCheck(this, FieldCollection);
	    babelHelpers.defineProperty(this, "changedFields", new Map());
	    babelHelpers.defineProperty(this, "fields", new Map());
	    this.model = model;
	  }

	  babelHelpers.createClass(FieldCollection, [{
	    key: "getFields",
	    value: function getFields() {
	      return Object.fromEntries(this.fields);
	    }
	  }, {
	    key: "getField",
	    value: function getField(fieldName) {
	      return this.fields.get(fieldName);
	    }
	  }, {
	    key: "setField",
	    value: function setField(fieldName, value) {
	      var oldValue = this.fields.get(fieldName);
	      this.fields.set(fieldName, value);

	      if (!this.changedFields.has(fieldName) && oldValue !== value) {
	        this.changedFields.set(fieldName, oldValue);
	      }

	      return this;
	    }
	  }, {
	    key: "isChanged",
	    value: function isChanged() {
	      return this.changedFields.size > 0;
	    }
	  }, {
	    key: "clearChanged",
	    value: function clearChanged() {
	      var _this = this;

	      var savingFieldNames = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : null;

	      if (main_core.Type.isNil(savingFieldNames)) {
	        this.changedFields.clear();
	      } else {
	        savingFieldNames.forEach(function (name) {
	          _this.removeFromChanged(name);
	        });
	      }

	      return this;
	    }
	  }, {
	    key: "removeFromChanged",
	    value: function removeFromChanged(fieldName) {
	      this.changedFields.delete(fieldName);
	      return this;
	    }
	  }, {
	    key: "getChangedFields",
	    value: function getChangedFields() {
	      var _this2 = this;

	      var changedFieldValues = {};
	      this.fields.forEach(function (value, key) {
	        if (_this2.changedFields.has(key)) {
	          changedFieldValues[key] = value;
	        }
	      });
	      return babelHelpers.objectSpread({}, changedFieldValues);
	    }
	  }, {
	    key: "getChangedValues",
	    value: function getChangedValues() {
	      var changedFieldValues = {};
	      this.changedFields.forEach(function (value, key) {
	        changedFieldValues[key] = value;
	      });
	      return babelHelpers.objectSpread({}, changedFieldValues);
	    }
	  }, {
	    key: "initFields",
	    value: function initFields(fields) {
	      var _this3 = this;

	      this.fields.clear();
	      this.clearChanged();

	      if (main_core.Type.isObject(fields)) {
	        Object.keys(fields).forEach(function (key) {
	          _this3.fields.set(key, fields[key]);
	        });
	      }

	      return this;
	    }
	  }]);
	  return FieldCollection;
	}();

	function _classPrivateFieldInitSpec$1(obj, privateMap, value) { _checkPrivateRedeclaration$1(obj, privateMap); privateMap.set(obj, value); }

	function _checkPrivateRedeclaration$1(obj, privateCollection) { if (privateCollection.has(obj)) { throw new TypeError("Cannot initialize the same private elements twice on an object"); } }

	var _map = /*#__PURE__*/new WeakMap();

	var StoreCollection = /*#__PURE__*/function () {
	  function StoreCollection() {
	    var model = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};
	    babelHelpers.classCallCheck(this, StoreCollection);

	    _classPrivateFieldInitSpec$1(this, _map, {
	      writable: true,
	      value: new Map()
	    });

	    this.model = model;
	  }

	  babelHelpers.createClass(StoreCollection, [{
	    key: "init",
	    value: function init(map) {
	      var _this = this;

	      Object.keys(map).forEach(function (key) {
	        var item = map[key];

	        if (item['STORE_ID'] > 0) {
	          babelHelpers.classPrivateFieldGet(_this, _map).set(main_core.Text.toNumber(item['STORE_ID']), {
	            AMOUNT: main_core.Text.toNumber(item['AMOUNT']),
	            QUANTITY_RESERVED: main_core.Text.toNumber(item['QUANTITY_RESERVED']),
	            STORE_ID: main_core.Text.toNumber(item['STORE_ID']),
	            STORE_TITLE: main_core.Text.encode(item['STORE_TITLE'])
	          });
	        }
	      });
	    }
	  }, {
	    key: "refresh",
	    value: function refresh() {
	      var _this2 = this;

	      this.clear();

	      if (this.model.getSkuId() > 0) {
	        main_core.ajax.runAction('catalog.storeSelector.getProductStores', {
	          json: {
	            productId: this.model.getSkuId()
	          }
	        }).then(function (response) {
	          response.data.forEach(function (item) {
	            if (!main_core.Type.isNil(item['STORE_ID'])) {
	              babelHelpers.classPrivateFieldGet(_this2, _map).set(main_core.Text.toNumber(item['STORE_ID']), {
	                AMOUNT: main_core.Text.toNumber(item['AMOUNT']),
	                QUANTITY_RESERVED: main_core.Text.toNumber(item['QUANTITY_RESERVED']),
	                STORE_ID: main_core.Text.toNumber(item['STORE_ID']),
	                STORE_TITLE: item['STORE_TITLE']
	              });
	            }
	          });

	          _this2.model.onChangeStoreData();
	        });
	      }
	    }
	  }, {
	    key: "getStoreAmount",
	    value: function getStoreAmount(storeId) {
	      var _babelHelpers$classPr;

	      return ((_babelHelpers$classPr = babelHelpers.classPrivateFieldGet(this, _map).get(main_core.Text.toNumber(storeId))) === null || _babelHelpers$classPr === void 0 ? void 0 : _babelHelpers$classPr.AMOUNT) || 0;
	    }
	  }, {
	    key: "getStoreReserved",
	    value: function getStoreReserved(storeId) {
	      var _babelHelpers$classPr2;

	      return ((_babelHelpers$classPr2 = babelHelpers.classPrivateFieldGet(this, _map).get(main_core.Text.toNumber(storeId))) === null || _babelHelpers$classPr2 === void 0 ? void 0 : _babelHelpers$classPr2.QUANTITY_RESERVED) || 0;
	    }
	  }, {
	    key: "getStoreAvailableAmount",
	    value: function getStoreAvailableAmount(storeId) {
	      return this.getStoreAmount(storeId) - this.getStoreReserved(storeId);
	    }
	  }, {
	    key: "getMaxFilledStore",
	    value: function getMaxFilledStore() {
	      var result = {
	        'STORE_ID': 0,
	        'AMOUNT': 0,
	        'STORE_TITLE': '',
	        'QUANTITY_RESERVED': 0
	      };
	      babelHelpers.classPrivateFieldGet(this, _map).forEach(function (item) {
	        result = item.AMOUNT > result.AMOUNT ? item : result;
	      });
	      return result;
	    }
	  }, {
	    key: "clear",
	    value: function clear() {
	      babelHelpers.classPrivateFieldGet(this, _map).clear();
	      return this;
	    }
	  }]);
	  return StoreCollection;
	}();

	var _templateObject;

	function _classPrivateMethodInitSpec(obj, privateSet) { _checkPrivateRedeclaration$2(obj, privateSet); privateSet.add(obj); }

	function _classPrivateFieldInitSpec$2(obj, privateMap, value) { _checkPrivateRedeclaration$2(obj, privateMap); privateMap.set(obj, value); }

	function _checkPrivateRedeclaration$2(obj, privateCollection) { if (privateCollection.has(obj)) { throw new TypeError("Cannot initialize the same private elements twice on an object"); } }

	function _classPrivateMethodGet(receiver, privateSet, fn) { if (!privateSet.has(receiver)) { throw new TypeError("attempted to get private field on non-instance"); } return fn; }
	var instances = new Map();

	var _fieldCollection = /*#__PURE__*/new WeakMap();

	var _errorCollection = /*#__PURE__*/new WeakMap();

	var _imageCollection = /*#__PURE__*/new WeakMap();

	var _storeCollection = /*#__PURE__*/new WeakMap();

	var _calculator = /*#__PURE__*/new WeakMap();

	var _offerId = /*#__PURE__*/new WeakMap();

	var _skuTree = /*#__PURE__*/new WeakMap();

	var _getDefaultCalculationFields = /*#__PURE__*/new WeakSet();

	var _updateProduct = /*#__PURE__*/new WeakSet();

	var _createProduct = /*#__PURE__*/new WeakSet();

	var ProductModel = /*#__PURE__*/function () {
	  babelHelpers.createClass(ProductModel, null, [{
	    key: "getById",
	    value: function getById(id) {
	      return instances.get(id) || null;
	    }
	  }]);

	  function ProductModel() {
	    var options = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};
	    babelHelpers.classCallCheck(this, ProductModel);

	    _classPrivateMethodInitSpec(this, _createProduct);

	    _classPrivateMethodInitSpec(this, _updateProduct);

	    _classPrivateMethodInitSpec(this, _getDefaultCalculationFields);

	    _classPrivateFieldInitSpec$2(this, _fieldCollection, {
	      writable: true,
	      value: null
	    });

	    _classPrivateFieldInitSpec$2(this, _errorCollection, {
	      writable: true,
	      value: null
	    });

	    _classPrivateFieldInitSpec$2(this, _imageCollection, {
	      writable: true,
	      value: null
	    });

	    _classPrivateFieldInitSpec$2(this, _storeCollection, {
	      writable: true,
	      value: null
	    });

	    _classPrivateFieldInitSpec$2(this, _calculator, {
	      writable: true,
	      value: null
	    });

	    _classPrivateFieldInitSpec$2(this, _offerId, {
	      writable: true,
	      value: null
	    });

	    _classPrivateFieldInitSpec$2(this, _skuTree, {
	      writable: true,
	      value: null
	    });

	    this.options = options || {};
	    this.id = this.options.id || main_core.Text.getRandom();
	    babelHelpers.classPrivateFieldSet(this, _errorCollection, new ErrorCollection(this));
	    babelHelpers.classPrivateFieldSet(this, _imageCollection, new ImageCollection(this));
	    babelHelpers.classPrivateFieldSet(this, _fieldCollection, new FieldCollection(this));
	    babelHelpers.classPrivateFieldSet(this, _storeCollection, new StoreCollection(this));

	    if (main_core.Type.isObject(options.fields)) {
	      this.initFields(options.fields, false);
	    }

	    if (main_core.Type.isNil(options.storeMap)) {
	      babelHelpers.classPrivateFieldGet(this, _storeCollection).refresh();
	    } else {
	      babelHelpers.classPrivateFieldGet(this, _storeCollection).init(options.storeMap);
	    }

	    if (main_core.Type.isObject(options.skuTree)) {
	      this.setSkuTree(options.skuTree);
	    }

	    if (main_core.Type.isObject(options.imageInfo)) ;

	    babelHelpers.classPrivateFieldSet(this, _calculator, new catalog_productCalculator.ProductCalculator(_classPrivateMethodGet(this, _getDefaultCalculationFields, _getDefaultCalculationFields2).call(this), {
	      currencyId: this.options.currency,
	      pricePrecision: this.options.pricePrecision || 2,
	      commonPrecision: this.options.pricePrecision || 2
	    }));
	    babelHelpers.classPrivateFieldGet(this, _calculator).setCalculationStrategy(new catalog_productCalculator.TaxForPriceStrategy(babelHelpers.classPrivateFieldGet(this, _calculator)));
	    instances.set(this.id, this);
	  }

	  babelHelpers.createClass(ProductModel, [{
	    key: "getOption",
	    value: function getOption(name) {
	      var defaultValue = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : null;
	      return this.options[name] || defaultValue;
	    }
	  }, {
	    key: "setOption",
	    value: function setOption(name) {
	      var value = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : null;
	      this.options[name] = value;
	      return this;
	    }
	  }, {
	    key: "setSkuTree",
	    value: function setSkuTree() {
	      var skuTree = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : null;
	      babelHelpers.classPrivateFieldSet(this, _skuTree, skuTree);
	      return this;
	    }
	  }, {
	    key: "clearSkuTree",
	    value: function clearSkuTree() {
	      babelHelpers.classPrivateFieldSet(this, _skuTree, null);
	      return this;
	    }
	  }, {
	    key: "getSkuTree",
	    value: function getSkuTree() {
	      return babelHelpers.classPrivateFieldGet(this, _skuTree);
	    }
	  }, {
	    key: "getCalculator",
	    value: function getCalculator() {
	      return babelHelpers.classPrivateFieldGet(this, _calculator);
	    }
	  }, {
	    key: "getErrorCollection",
	    value: function getErrorCollection() {
	      return babelHelpers.classPrivateFieldGet(this, _errorCollection);
	    }
	  }, {
	    key: "getImageCollection",
	    value: function getImageCollection() {
	      return babelHelpers.classPrivateFieldGet(this, _imageCollection);
	    }
	  }, {
	    key: "getFields",
	    value: function getFields() {
	      return babelHelpers.classPrivateFieldGet(this, _fieldCollection).getFields();
	    }
	  }, {
	    key: "getStoreCollection",
	    value: function getStoreCollection() {
	      return babelHelpers.classPrivateFieldGet(this, _storeCollection);
	    }
	  }, {
	    key: "getField",
	    value: function getField(fieldName) {
	      return babelHelpers.classPrivateFieldGet(this, _fieldCollection).getField(fieldName);
	    }
	  }, {
	    key: "setField",
	    value: function setField(fieldName, value) {
	      babelHelpers.classPrivateFieldGet(this, _fieldCollection).setField(fieldName, value);

	      if ((fieldName === 'SKU_ID' || fieldName === 'PRODUCT_ID') && this.getSkuId() !== babelHelpers.classPrivateFieldGet(this, _offerId)) {
	        babelHelpers.classPrivateFieldSet(this, _offerId, this.getSkuId());

	        if (babelHelpers.classPrivateFieldGet(this, _offerId) > 0) {
	          babelHelpers.classPrivateFieldGet(this, _storeCollection).refresh();
	        }
	      }

	      return this;
	    }
	  }, {
	    key: "setFields",
	    value: function setFields(fields) {
	      var _this = this;

	      Object.keys(fields).forEach(function (key) {
	        _this.setField(key, fields[key]);
	      });
	      return this;
	    }
	  }, {
	    key: "initFields",
	    value: function initFields(fields) {
	      var refreshStoreInfo = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : true;
	      babelHelpers.classPrivateFieldGet(this, _fieldCollection).initFields(fields);
	      babelHelpers.classPrivateFieldSet(this, _offerId, this.getSkuId());

	      if (refreshStoreInfo) {
	        babelHelpers.classPrivateFieldGet(this, _storeCollection).refresh();
	      }

	      return this;
	    }
	  }, {
	    key: "removeField",
	    value: function removeField(fieldName) {
	      babelHelpers.classPrivateFieldGet(this, _fieldCollection).removeField(fieldName);
	      return this;
	    }
	  }, {
	    key: "isChanged",
	    value: function isChanged() {
	      return babelHelpers.classPrivateFieldGet(this, _fieldCollection).isChanged();
	    }
	  }, {
	    key: "isNew",
	    value: function isNew() {
	      return this.getOption('isNew', false);
	    }
	  }, {
	    key: "getSkuId",
	    value: function getSkuId() {
	      return this.getField('SKU_ID') || this.getProductId();
	    }
	  }, {
	    key: "getProductId",
	    value: function getProductId() {
	      return this.getField('PRODUCT_ID') || null;
	    }
	  }, {
	    key: "isCatalogExisted",
	    value: function isCatalogExisted() {
	      return this.getSkuId() > 0;
	    }
	  }, {
	    key: "isEmpty",
	    value: function isEmpty() {
	      return this.getProductId() === null && !this.isSimple();
	    }
	  }, {
	    key: "isSimple",
	    value: function isSimple() {
	      return this.getOption('isSimpleModel', null);
	    }
	  }, {
	    key: "getIblockId",
	    value: function getIblockId() {
	      return this.getOption('iblockId', 0);
	    }
	  }, {
	    key: "getBasePriceId",
	    value: function getBasePriceId() {
	      return this.getOption('basePriceId', 0);
	    }
	  }, {
	    key: "getCurrency",
	    value: function getCurrency() {
	      return this.getOption('currency', null);
	    }
	  }, {
	    key: "getDetailPath",
	    value: function getDetailPath() {
	      return this.getOption('detailPath', '');
	    }
	  }, {
	    key: "setDetailPath",
	    value: function setDetailPath(value) {
	      this.options['detailPath'] = value || '';
	    }
	  }, {
	    key: "showSaveNotifier",
	    value: function showSaveNotifier(id, options) {
	      if (!this.isCatalogExisted()) {
	        return;
	      }

	      var title = options.title || '';
	      var closeEventName = BX.UI.Notification.Event.getFullName('onClose');
	      var cancelEventName = BX.UI.Notification.Event.getFullName('onCancel');
	      new Promise(function (resolve) {
	        var currentBalloon = BX.UI.Notification.Center.getBalloonByCategory(ProductModel.SAVE_NOTIFICATION_CATEGORY);

	        if (currentBalloon && currentBalloon.getId() !== id) {
	          setTimeout(function () {
	            currentBalloon.close();
	            setTimeout(resolve, 400);
	          }, 200);
	        } else {
	          resolve();
	        }
	      }).then(function () {
	        var notify = BX.UI.Notification.Center.getBalloonById(id);

	        if (!notify) {
	          var notificationOptions = {
	            id: id,
	            closeButton: true,
	            category: ProductModel.SAVE_NOTIFICATION_CATEGORY,
	            autoHideDelay: 4000,
	            content: main_core.Tag.render(_templateObject || (_templateObject = babelHelpers.taggedTemplateLiteral(["<div>", "</div>"])), title)
	          };

	          if (options.disableCancel !== true) {
	            notificationOptions.actions = [{
	              title: options.declineCancelTitle || main_core.Loc.getMessage('CATALOG_PRODUCT_MODEL_SAVING_NOTIFICATION_DECLINE_SAVE'),
	              events: {
	                click: function click(event, balloon) {
	                  BX.removeAllCustomEvents(notify, closeEventName);
	                  balloon.fireEvent('onCancel');
	                  balloon.close();
	                }
	              }
	            }];
	          }

	          notify = BX.UI.Notification.Center.notify(notificationOptions);
	        }

	        BX.removeAllCustomEvents(notify, closeEventName);
	        notify.addEvent('onClose', function () {
	          var _options$events;

	          if (main_core.Type.isFunction(options === null || options === void 0 ? void 0 : (_options$events = options.events) === null || _options$events === void 0 ? void 0 : _options$events.onSave)) {
	            options.events.onSave();
	          }
	        });
	        BX.removeAllCustomEvents(notify, cancelEventName);
	        notify.addEvent('onCancel', function () {
	          var _options$events2;

	          if (main_core.Type.isFunction(options === null || options === void 0 ? void 0 : (_options$events2 = options.events) === null || _options$events2 === void 0 ? void 0 : _options$events2.onCancel)) {
	            options.events.onCancel();
	          }
	        });
	        notify.show();
	      });
	    }
	  }, {
	    key: "save",
	    value: function save(savingFieldNames) {
	      var _this2 = this;

	      if (!this.isSaveable()) {
	        return;
	      }

	      return new Promise(function (resolve, reject) {
	        var ajaxResult;

	        if (_this2.isSimple()) {
	          ajaxResult = _classPrivateMethodGet(_this2, _createProduct, _createProduct2).call(_this2);
	        } else {
	          ajaxResult = _classPrivateMethodGet(_this2, _updateProduct, _updateProduct2).call(_this2, savingFieldNames);
	        }

	        ajaxResult.then(function (event) {
	          babelHelpers.classPrivateFieldGet(_this2, _fieldCollection).clearChanged(savingFieldNames);
	          resolve(event);
	        }).catch(reject);
	      });
	    }
	  }, {
	    key: "isSaveable",
	    value: function isSaveable() {
	      return this.getOption('isSaveable', true) && !this.isEmpty();
	    }
	  }, {
	    key: "onErrorCollectionChange",
	    value: function onErrorCollectionChange() {
	      main_core_events.EventEmitter.emit(this, 'onErrorsChange');
	    }
	  }, {
	    key: "onChangeStoreData",
	    value: function onChangeStoreData() {
	      main_core_events.EventEmitter.emit(this, 'onChangeStoreData');
	    }
	  }], [{
	    key: "getLastActiveSaveNotification",
	    value: function getLastActiveSaveNotification() {
	      return BX.UI.Notification.Center.getBalloonByCategory(ProductModel.SAVE_NOTIFICATION_CATEGORY);
	    }
	  }]);
	  return ProductModel;
	}();

	function _getDefaultCalculationFields2() {
	  var defaultPrice = main_core.Text.toNumber(babelHelpers.classPrivateFieldGet(this, _fieldCollection).getField('PRICE'));
	  var basePrice = main_core.Type.isNumber(babelHelpers.classPrivateFieldGet(this, _fieldCollection).getField('BASE_PRICE')) ? main_core.Text.toNumber(babelHelpers.classPrivateFieldGet(this, _fieldCollection).getField('BASE_PRICE')) : defaultPrice;
	  return {
	    'QUANTITY': main_core.Text.toNumber(babelHelpers.classPrivateFieldGet(this, _fieldCollection).getField('QUANTITY')),
	    'BASE_PRICE': basePrice,
	    'PRICE': defaultPrice,
	    'PRICE_NETTO': basePrice,
	    'PRICE_BRUTTO': defaultPrice,
	    'PRICE_EXCLUSIVE': babelHelpers.classPrivateFieldGet(this, _fieldCollection).getField('PRICE_EXCLUSIVE') || defaultPrice,
	    'DISCOUNT_TYPE_ID': babelHelpers.classPrivateFieldGet(this, _fieldCollection).getField('DISCOUNT_TYPE_ID') || catalog_productCalculator.DiscountType.PERCENTAGE,
	    'DISCOUNT_RATE': main_core.Text.toNumber(babelHelpers.classPrivateFieldGet(this, _fieldCollection).getField('DISCOUNT_RATE')),
	    'DISCOUNT_SUM': main_core.Text.toNumber(babelHelpers.classPrivateFieldGet(this, _fieldCollection).getField('DISCOUNT_SUM')),
	    'TAX_INCLUDED': babelHelpers.classPrivateFieldGet(this, _fieldCollection).getField('TAX_INCLUDED') || 'N',
	    'TAX_RATE': main_core.Text.toNumber(babelHelpers.classPrivateFieldGet(this, _fieldCollection).getField('TAX_RATE')) || 0,
	    'CUSTOMIZED': babelHelpers.classPrivateFieldGet(this, _fieldCollection).getField('CUSTOMIZED') || 'N'
	  };
	}

	function _updateProduct2(savingFieldNames) {
	  var _this3 = this;

	  if (this.getIblockId() <= 0 || !babelHelpers.classPrivateFieldGet(this, _fieldCollection).isChanged()) {
	    return;
	  }

	  var savedFields = {};

	  if (!main_core.Type.isArray(savingFieldNames) || savingFieldNames.length === 0) {
	    savedFields = babelHelpers.classPrivateFieldGet(this, _fieldCollection).getChangedFields();
	  } else {
	    var changedFields = babelHelpers.classPrivateFieldGet(this, _fieldCollection).getChangedFields();
	    Object.keys(changedFields).forEach(function (key) {
	      if (savingFieldNames.includes(key)) {
	        if (key === 'PRICE' || key === 'BASE_PRICE') {
	          savedFields['PRICES'] = savedFields['PRICES'] || {};
	          savedFields['PRICES'][_this3.getBasePriceId()] = {
	            PRICE: changedFields[key],
	            CURRENCY: _this3.getCurrency()
	          };
	        } else {
	          savedFields[key] = changedFields[key];
	        }
	      }
	    });
	  }

	  return main_core.ajax.runAction('catalog.productSelector.updateSku', {
	    json: {
	      id: this.getSkuId(),
	      updateFields: savedFields,
	      oldFields: babelHelpers.classPrivateFieldGet(this, _fieldCollection).getChangedValues()
	    }
	  });
	}

	function _createProduct2() {
	  var fields = {
	    NAME: babelHelpers.classPrivateFieldGet(this, _fieldCollection).getField('NAME', ''),
	    IBLOCK_ID: this.getIblockId()
	  };
	  var price = babelHelpers.classPrivateFieldGet(this, _fieldCollection).getField('BASE_PRICE', null);

	  if (!main_core.Type.isNil(price)) {
	    fields['PRICE'] = price;
	  }

	  var barcode = babelHelpers.classPrivateFieldGet(this, _fieldCollection).getField('BARCODE', null);

	  if (!main_core.Type.isNil(barcode)) {
	    fields['BARCODE'] = barcode;
	  }

	  fields['CURRENCY'] = this.getCurrency();
	  var currency = babelHelpers.classPrivateFieldGet(this, _fieldCollection).getField('CURRENCY', null);

	  if (main_core.Type.isStringFilled(currency)) {
	    fields['CURRENCY'] = currency;
	  }

	  return main_core.ajax.runAction('catalog.productSelector.createProduct', {
	    json: {
	      fields: fields
	    }
	  });
	}

	babelHelpers.defineProperty(ProductModel, "SAVE_NOTIFICATION_CATEGORY", 'MODEL_SAVE');

	exports.ProductModel = ProductModel;

}((this.BX.Catalog = this.BX.Catalog || {}),BX.Event,BX.Catalog,BX,BX.Catalog));
//# sourceMappingURL=product-model.bundle.js.map
