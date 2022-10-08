(function (exports,main_core,main_core_events,catalog_productSelector) {
	'use strict';

	function _createForOfIteratorHelper(o, allowArrayLike) { var it = typeof Symbol !== "undefined" && o[Symbol.iterator] || o["@@iterator"]; if (!it) { if (Array.isArray(o) || (it = _unsupportedIterableToArray(o)) || allowArrayLike && o && typeof o.length === "number") { if (it) o = it; var i = 0; var F = function F() {}; return { s: F, n: function n() { if (i >= o.length) return { done: true }; return { done: false, value: o[i++] }; }, e: function e(_e) { throw _e; }, f: F }; } throw new TypeError("Invalid attempt to iterate non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); } var normalCompletion = true, didErr = false, err; return { s: function s() { it = it.call(o); }, n: function n() { var step = it.next(); normalCompletion = step.done; return step; }, e: function e(_e2) { didErr = true; err = _e2; }, f: function f() { try { if (!normalCompletion && it["return"] != null) it["return"](); } finally { if (didErr) throw err; } } }; }

	function _unsupportedIterableToArray(o, minLen) { if (!o) return; if (typeof o === "string") return _arrayLikeToArray(o, minLen); var n = Object.prototype.toString.call(o).slice(8, -1); if (n === "Object" && o.constructor) n = o.constructor.name; if (n === "Map" || n === "Set") return Array.from(o); if (n === "Arguments" || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(n)) return _arrayLikeToArray(o, minLen); }

	function _arrayLikeToArray(arr, len) { if (len == null || len > arr.length) len = arr.length; for (var i = 0, arr2 = new Array(len); i < len; i++) { arr2[i] = arr[i]; } return arr2; }
	var instances = new Map();

	var ProductField = /*#__PURE__*/function () {
	  babelHelpers.createClass(ProductField, null, [{
	    key: "getById",
	    value: function getById(id) {
	      return instances.get(id) || null;
	    }
	  }]);

	  function ProductField(id) {
	    var settings = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : {};
	    babelHelpers.classCallCheck(this, ProductField);
	    babelHelpers.defineProperty(this, "onSelectEditHandler", this.onSelectEdit.bind(this));
	    babelHelpers.defineProperty(this, "onCancelEditHandler", this.onCancelEdit.bind(this));
	    babelHelpers.defineProperty(this, "onBeforeGridRequestHandler", this.onBeforeGridRequest.bind(this));
	    babelHelpers.defineProperty(this, "onUnsubscribeEventsHandler", this.unsubscribeEvents.bind(this));
	    this.selector = new catalog_productSelector.ProductSelector(id, settings);
	    this.componentName = settings.componentName || '';
	    this.signedParameters = settings.signedParameters || '';
	    this.rowIdMask = settings.rowIdMask || '#ID#';
	    this.subscribeEvents();
	    instances.set(id, this);
	  }

	  babelHelpers.createClass(ProductField, [{
	    key: "subscribeEvents",
	    value: function subscribeEvents() {
	      main_core_events.EventEmitter.subscribe('Grid::thereEditedRows', this.onSelectEditHandler);
	      main_core_events.EventEmitter.subscribe('Grid::noEditedRows', this.onCancelEditHandler);
	      main_core_events.EventEmitter.subscribe('Grid::beforeRequest', this.onBeforeGridRequestHandler);
	      main_core_events.EventEmitter.subscribe('Grid::updated', this.onUnsubscribeEventsHandler);
	    }
	  }, {
	    key: "unsubscribeEvents",
	    value: function unsubscribeEvents() {
	      main_core_events.EventEmitter.unsubscribe('Grid::thereEditedRows', this.onSelectEditHandler);
	      main_core_events.EventEmitter.unsubscribe('Grid::noEditedRows', this.onCancelEditHandler);
	      main_core_events.EventEmitter.unsubscribe('Grid::beforeRequest', this.onBeforeGridRequestHandler);
	      main_core_events.EventEmitter.unsubscribe('Grid::updated', this.onUnsubscribeEventsHandler);
	      this.selector.unsubscribeEvents();
	    }
	  }, {
	    key: "getSelector",
	    value: function getSelector() {
	      return this.selector;
	    }
	  }, {
	    key: "onBeforeGridRequest",
	    value: function onBeforeGridRequest(event) {
	      var wrapper = this.getSelector().getWrapper();

	      if (!wrapper) {
	        return;
	      }

	      var _event$getData = event.getData(),
	          _event$getData2 = babelHelpers.slicedToArray(_event$getData, 2),
	          gridData = _event$getData2[1];

	      var submitData = BX.prop.get(gridData, 'data', {});

	      if (!submitData.FIELDS) {
	        return;
	      }

	      var productId = this.getSelector().getModel().getProductId();
	      productId = this.rowIdMask.replace('#ID#', productId);
	      submitData.FIELDS[productId] = submitData.FIELDS[productId] || {};
	      var imageInputContainer = wrapper.querySelector('.ui-image-input-container');

	      if (imageInputContainer) {
	        var inputs = imageInputContainer.querySelectorAll('input');
	        var values = {};
	        var newFilesRegExp = new RegExp(/([0-9A-Za-z_]+?(_n\d+)*)\[([A-Za-z_]+)\]/);

	        var _iterator = _createForOfIteratorHelper(inputs),
	            _step;

	        try {
	          for (_iterator.s(); !(_step = _iterator.n()).done;) {
	            var inputItem = _step.value;

	            if (newFilesRegExp.test(inputItem.name)) {
	              var _inputItem$name$match = inputItem.name.match(newFilesRegExp),
	                  _inputItem$name$match2 = babelHelpers.slicedToArray(_inputItem$name$match, 4),
	                  fileCounter = _inputItem$name$match2[1],
	                  code = _inputItem$name$match2[2],
	                  fileSetting = _inputItem$name$match2[3];

	              if (fileCounter && fileSetting) {
	                values[fileCounter] = values[fileCounter] || {};
	                values[fileCounter][fileSetting] = inputItem.value;
	              }
	            } else {
	              values[inputItem.name] = inputItem.value;
	            }
	          }
	        } catch (err) {
	          _iterator.e(err);
	        } finally {
	          _iterator.f();
	        }

	        submitData.FIELDS[productId] = submitData.FIELDS[productId] || {};

	        if (Object.keys(values).length > 0) {
	          submitData.FIELDS[productId]['MORE_PHOTO'] = values;
	        }
	      }

	      var productNameInput = wrapper.querySelector('input[name="NAME"]');

	      if (productNameInput) {
	        submitData.FIELDS[productId]['NAME'] = productNameInput.value;
	      }
	    }
	  }, {
	    key: "onCancelEdit",
	    value: function onCancelEdit() {
	      this.getSelector().setMode(BX.Catalog.ProductSelector.MODE_VIEW);
	      this.getSelector().clearLayout();
	      this.getSelector().layout();
	      var grid = BX.Main.gridManager.getInstanceById(this.getSelector().getConfig('GRID_ID'));

	      if (!grid) {
	        return;
	      }

	      var row = grid.getRows().getById(this.selector.getConfig('ROW_ID'));

	      if (!row) {
	        return;
	      }

	      var cell = row.getCellById('CATALOG_PRODUCT');

	      if (cell) {
	        main_core.Dom.removeClass(row.getContentContainer(cell), ProductField.EDIT_CLASS);
	      }
	    }
	  }, {
	    key: "onSelectEdit",
	    value: function onSelectEdit() {
	      if (!this.getSelector().getConfig('GRID_ID', null)) {
	        return;
	      }

	      var grid = BX.Main.gridManager.getInstanceById(this.getSelector().getConfig('GRID_ID'));

	      if (!grid) {
	        return;
	      }

	      var row = grid.getRows().getById(this.selector.getConfig('ROW_ID'));

	      if (row && row.isEdit()) {
	        this.getSelector().setMode(BX.Catalog.ProductSelector.MODE_EDIT);
	        this.getSelector().clearLayout();
	        this.getSelector().layout();
	        var cell = row.getCellById('CATALOG_PRODUCT');

	        if (cell) {
	          main_core.Dom.addClass(row.getContentContainer(cell), ProductField.EDIT_CLASS);
	        }
	      }
	    }
	  }]);
	  return ProductField;
	}();

	babelHelpers.defineProperty(ProductField, "EDIT_CLASS", 'catalog-grid-product-field-edit');
	babelHelpers.defineProperty(ProductField, "PRODUCT_MODE", 'product');
	babelHelpers.defineProperty(ProductField, "SKU_MODE", 'sku');
	main_core.Reflection.namespace('BX.Catalog.Grid').ProductField = ProductField;

}((this.window = this.window || {}),BX,BX.Event,BX.Catalog));
//# sourceMappingURL=script.js.map
