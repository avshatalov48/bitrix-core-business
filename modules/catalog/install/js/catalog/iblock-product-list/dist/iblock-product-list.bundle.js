this.BX = this.BX || {};
(function (exports,main_core_events,main_core) {
	'use strict';

	function _createForOfIteratorHelper(o, allowArrayLike) { var it; if (typeof Symbol === "undefined" || o[Symbol.iterator] == null) { if (Array.isArray(o) || (it = _unsupportedIterableToArray(o)) || allowArrayLike && o && typeof o.length === "number") { if (it) o = it; var i = 0; var F = function F() {}; return { s: F, n: function n() { if (i >= o.length) return { done: true }; return { done: false, value: o[i++] }; }, e: function e(_e) { throw _e; }, f: F }; } throw new TypeError("Invalid attempt to iterate non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); } var normalCompletion = true, didErr = false, err; return { s: function s() { it = o[Symbol.iterator](); }, n: function n() { var step = it.next(); normalCompletion = step.done; return step; }, e: function e(_e2) { didErr = true; err = _e2; }, f: function f() { try { if (!normalCompletion && it.return != null) it.return(); } finally { if (didErr) throw err; } } }; }

	function _unsupportedIterableToArray(o, minLen) { if (!o) return; if (typeof o === "string") return _arrayLikeToArray(o, minLen); var n = Object.prototype.toString.call(o).slice(8, -1); if (n === "Object" && o.constructor) n = o.constructor.name; if (n === "Map" || n === "Set") return Array.from(o); if (n === "Arguments" || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(n)) return _arrayLikeToArray(o, minLen); }

	function _arrayLikeToArray(arr, len) { if (len == null || len > arr.length) len = arr.length; for (var i = 0, arr2 = new Array(len); i < len; i++) { arr2[i] = arr[i]; } return arr2; }
	var IblockProductList = /*#__PURE__*/function () {
	  /**
	   * @type {?BX.Main.grid}
	   */
	  function IblockProductList() {
	    var options = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};
	    babelHelpers.classCallCheck(this, IblockProductList);
	    babelHelpers.defineProperty(this, "variations", new Map());
	    babelHelpers.defineProperty(this, "variationsEditData", new Map());
	    babelHelpers.defineProperty(this, "editedVariations", new Map());
	    babelHelpers.defineProperty(this, "morePhotoChangedInputs", new Map());
	    babelHelpers.defineProperty(this, "onSettingsWindowSaveHandler", this.handleOnSettingsWindowSave.bind(this));
	    babelHelpers.defineProperty(this, "onChangeVariationHandler", this.handleOnChangeVariation.bind(this));
	    babelHelpers.defineProperty(this, "onBeforeGridRequestHandler", this.handleOnBeforeGridRequest.bind(this));
	    babelHelpers.defineProperty(this, "onFilterApplyHandler", this.handleOnFilterApply.bind(this));
	    babelHelpers.defineProperty(this, "onSaveImageHandler", this.handleOnSaveImage.bind(this));
	    this.gridId = options.gridId;
	    this.rowIdMask = options.rowIdMask || '#ID#';
	    this.variationFieldNames = options.variationFieldNames || [];
	    this.productVariationMap = options.productVariationMap || {};
	    this.createNewProductHref = options.createNewProductHref || '';
	    this.showCatalogWithOffers = options.showCatalogWithOffers || false;
	    this.addCustomClassToGrid();
	    this.cacheSelectedVariation();
	    main_core_events.EventEmitter.subscribe('BX.Grid.SettingsWindow:save', this.onSettingsWindowSaveHandler);
	    main_core_events.EventEmitter.subscribe('SkuProperty::onChange', this.onChangeVariationHandler);
	    main_core_events.EventEmitter.subscribe('Grid::beforeRequest', this.onBeforeGridRequestHandler);
	    main_core_events.EventEmitter.subscribe('BX.Main.Filter:apply', this.onFilterApplyHandler);
	    main_core_events.EventEmitter.subscribe('Catalog.ImageInput::save', this.onSaveImageHandler);
	  }

	  babelHelpers.createClass(IblockProductList, [{
	    key: "addCustomClassToGrid",
	    value: function addCustomClassToGrid() {
	      main_core.Dom.addClass(this.getGrid().getContainer(), 'catalog-product-grid');
	    }
	  }, {
	    key: "cacheSelectedVariation",
	    value: function cacheSelectedVariation() {
	      var _this = this;

	      this.getGrid().getRows().getBodyChild().forEach(function (row) {
	        var rowId = row.getId();

	        var productId = _this.getProductIdByRowId(rowId);

	        var variationId = _this.getCurrentVariationIdByProduct(productId);

	        if (variationId) {
	          _this.variations.set(variationId, row.getNode().cloneNode(true));

	          _this.variationsEditData.set(variationId, row.getEditData());
	        }
	      });
	    }
	  }, {
	    key: "clearAllVariationCache",
	    value: function clearAllVariationCache() {
	      this.variations.clear();
	      this.variationsEditData.clear();
	      this.editedVariations.clear();
	      this.morePhotoChangedInputs.clear();
	    }
	  }, {
	    key: "clearVariationCache",
	    value: function clearVariationCache(variationId) {
	      this.variations.delete(variationId);
	      this.variationsEditData.delete(variationId);
	      this.editedVariations.delete(variationId);
	    }
	    /**
	     * @returns {?BX.Main.grid}
	     */

	  }, {
	    key: "getGrid",
	    value: function getGrid() {
	      if (!this.grid) {
	        this.grid = BX.Main.gridManager.getInstanceById(this.gridId);
	      }

	      return this.grid;
	    }
	  }, {
	    key: "handleOnSettingsWindowSave",
	    value: function handleOnSettingsWindowSave(event) {
	      var _event$getCompatData = event.getCompatData(),
	          _event$getCompatData2 = babelHelpers.slicedToArray(_event$getCompatData, 1),
	          settingsWindow = _event$getCompatData2[0];

	      var selectedColumns = settingsWindow.getSelectedColumns();
	      this.showCatalogWithOffers = selectedColumns.indexOf('CATALOG_PRODUCT') !== -1;
	    }
	  }, {
	    key: "handleOnChangeVariation",
	    value: function handleOnChangeVariation(event) {
	      var _this2 = this;

	      var _event$getData = event.getData(),
	          _event$getData2 = babelHelpers.slicedToArray(_event$getData, 1),
	          skuFields = _event$getData2[0];

	      var productId = main_core.Text.toNumber(skuFields.PARENT_PRODUCT_ID);
	      var variationId = main_core.Text.toNumber(skuFields.ID);

	      if (productId <= 0 || variationId <= 0) {
	        return;
	      }

	      var productRow = this.getProductRow(productId);

	      if (productRow.isEdit()) {
	        var values = this.getEditedVariationValues(productRow);
	        var currentVariationId = this.getCurrentVariationIdByProduct(productId);
	        this.editedVariations.set(currentVariationId, values);
	      }

	      if (productRow.isEdit() && this.editedVariations.has(variationId)) {
	        var editData = Object.assign(productRow.getEditData(), this.editedVariations.get(variationId));
	        productRow.setEditData(editData);
	        productRow.editCancel();
	        productRow.edit();
	        this.productVariationMap[productId] = variationId;
	        return;
	      }

	      this.getVariation(productId, variationId).then(function (variationNode) {
	        _this2.updateProductRow(productId, variationId, variationNode);

	        _this2.productVariationMap[productId] = variationId;
	      });
	    }
	  }, {
	    key: "getEditedVariationValues",
	    value: function getEditedVariationValues(row) {
	      var _this3 = this;

	      var currentEditorValues = row.getEditorValue();
	      var headRow = this.getHeadRow();
	      var values = {};
	      var morePhotoHtml = null;
	      babelHelpers.toConsumableArray(row.getCells()).forEach(function (cell, index) {
	        var cellName = headRow.getCellNameByCellIndex(index);

	        if (cellName !== 'MORE_PHOTO') {
	          return;
	        }

	        var editorContainer = row.getEditorContainer(cell);

	        if (editorContainer) {
	          var imageBlock = editorContainer.querySelector('.catalog-image-input-wrapper');
	          var id = imageBlock.id;

	          if (_this3.morePhotoChangedInputs.has(id)) {
	            morePhotoHtml = _this3.morePhotoChangedInputs.get(id);
	          } else {
	            morePhotoHtml = imageBlock.outerHTML;
	          }
	        }
	      });

	      for (var name in currentEditorValues) {
	        if (!currentEditorValues.hasOwnProperty(name) || !this.variationFieldNames.includes(name)) {
	          continue;
	        }

	        if (name === 'MORE_PHOTO' && !main_core.Type.isNil(morePhotoHtml)) {
	          values[name] = morePhotoHtml;
	        } else {
	          values[name] = currentEditorValues[name];
	        }
	      }

	      return values;
	    }
	  }, {
	    key: "getVariation",
	    value: function getVariation(productId, variationId) {
	      var _this4 = this;

	      if (this.getProductRow(productId).isEdit() && this.editedVariations.has(variationId)) {
	        return Promise.resolve(this.editedVariations.get(variationId));
	      }

	      if (this.variations.has(variationId)) {
	        return Promise.resolve(this.variations.get(variationId));
	      }

	      return new Promise(function (resolve) {
	        _this4.loadVariation(productId, variationId, resolve);
	      }).then(function (variation) {
	        if (main_core.Type.isDomNode(variation)) {
	          _this4.variations.set(variationId, variation);

	          return variation;
	        }

	        return null;
	      });
	    }
	  }, {
	    key: "loadVariation",
	    value: function loadVariation(productId, variationId, resolve) {
	      var self = this;
	      var url = '';
	      var method = 'POST';
	      var data = {
	        productId: productId,
	        variationId: variationId
	      };
	      this.getProductRow(productId).stateLoad();
	      this.getGrid().getData().request(url, method, data, 'changeVariation', function () {
	        var row = self.getProductRow(productId);

	        if (row) {
	          row.stateUnload();
	          resolve(this.getRowById(row.getId()));
	        }
	      });
	    }
	  }, {
	    key: "getProductIdByRowId",
	    value: function getProductIdByRowId(rowId) {
	      var mask = new RegExp(this.rowIdMask.replace('#ID#', '([0-9]+)'));
	      var matches = rowId.match(mask);
	      return main_core.Type.isArray(matches) ? main_core.Text.toNumber(matches[1]) : 0;
	    }
	  }, {
	    key: "getRowIdByProductId",
	    value: function getRowIdByProductId(id) {
	      return this.rowIdMask.replace('#ID#', id);
	    }
	    /**
	     * @param id
	     * @returns {?BX.Grid.Row}
	     */

	  }, {
	    key: "getProductRow",
	    value: function getProductRow(id) {
	      var rowId = this.getRowIdByProductId(id);
	      return this.getGrid().getRows().getById(rowId);
	    }
	    /**
	     * @returns {?BX.Grid.Row}
	     */

	  }, {
	    key: "getHeadRow",
	    value: function getHeadRow() {
	      return this.getGrid().getRows().getHeadFirstChild();
	    }
	  }, {
	    key: "updateProductRow",
	    value: function updateProductRow(productId, variationId, variationNode) {
	      var _this5 = this;

	      if (!productId || !main_core.Type.isDomNode(variationNode)) {
	        return;
	      }

	      var headRow = this.getHeadRow();
	      var productRow = this.getProductRow(productId);
	      babelHelpers.toConsumableArray(variationNode.cells).forEach(function (cell, index) {
	        var cellName = headRow.getCellNameByCellIndex(index);

	        if (_this5.variationFieldNames.includes(cellName)) {
	          var columnCell = productRow.getCellByIndex(index);

	          if (columnCell) {
	            var cellHtml = productRow.getContentContainer(cell).innerHTML;
	            productRow.getContentContainer(columnCell).innerHTML = cellHtml;
	          }
	        }
	      });

	      if (this.variationsEditData.has(variationId)) {
	        productRow.setEditData(this.variationsEditData.get(variationId));
	      } else {
	        productRow.resetEditData();
	        this.variationsEditData.set(variationId, productRow.getEditData());
	      }

	      if (productRow.isEdit()) {
	        productRow.editCancel();
	        productRow.edit();
	      }
	    }
	  }, {
	    key: "handleOnBeforeGridRequest",
	    value: function handleOnBeforeGridRequest(event) {
	      var _this6 = this;

	      var _event$getData3 = event.getData(),
	          _event$getData4 = babelHelpers.slicedToArray(_event$getData3, 2),
	          gridData = _event$getData4[1];

	      var submitData = BX.prop.get(gridData, 'data', {}); // reload settings, columns or something else

	      if (!submitData.productId && !submitData.FIELDS) {
	        this.clearAllVariationCache();
	      }

	      if (submitData.FIELDS) {
	        this.editedVariations.forEach(function (editFields, variationId) {
	          var rowId = _this6.getRowIdByProductId(variationId);

	          submitData.FIELDS[rowId] = submitData.FIELDS[rowId] || {};
	          Object.keys(editFields).map(function (cellName) {
	            if (cellName.indexOf('CATALOG_GROUP_') >= 0) {
	              var groupPriceId = cellName.replace('CATALOG_GROUP_', '');

	              if (!main_core.Type.isNil(editFields[cellName]['PRICE'])) {
	                submitData['CATALOG_PRICE'] = submitData['CATALOG_PRICE'] || {};
	                submitData['CATALOG_PRICE'][variationId] = submitData['CATALOG_PRICE'][variationId] || {};
	                submitData['CATALOG_PRICE'][variationId][groupPriceId] = editFields[cellName]['PRICE']['VALUE'];
	              }

	              if (!main_core.Type.isNil(editFields[cellName]['CURRENCY'])) {
	                submitData['CATALOG_CURRENCY'] = submitData['CATALOG_CURRENCY'] || {};
	                submitData['CATALOG_CURRENCY'][variationId] = submitData['CATALOG_CURRENCY'][variationId] || {};
	                submitData['CATALOG_CURRENCY'][variationId][groupPriceId] = editFields[cellName]['CURRENCY']['VALUE'];
	              }
	            } else if (cellName !== 'MORE_PHOTO' && cellName !== 'MORE_PHOTO_custom') {
	              submitData.FIELDS[rowId][cellName] = editFields[cellName];
	            }
	          });

	          _this6.clearVariationCache(variationId);
	        });

	        for (var rowId in submitData.FIELDS) {
	          if (!submitData.FIELDS.hasOwnProperty(rowId)) {
	            continue;
	          }

	          var productId = this.getProductIdByRowId(rowId);
	          var variationId = this.getCurrentVariationIdByProduct(productId);
	          var newFilesRegExp = new RegExp(/([0-9A-Za-z_]+?(_n\d+)*)\[([A-Za-z_]+)\]/);
	          var rowFields = submitData.FIELDS[rowId];
	          var morePhotoValues = {};

	          if (!main_core.Type.isNil(rowFields['MORE_PHOTO_custom'])) {
	            for (var key in rowFields['MORE_PHOTO_custom']) {
	              if (!rowFields['MORE_PHOTO_custom'].hasOwnProperty(key)) {
	                continue;
	              }

	              var inputValue = rowFields['MORE_PHOTO_custom'][key];

	              if (newFilesRegExp.test(inputValue.name)) {
	                var fileCounter = void 0,
	                    fileSetting = void 0;

	                var _inputValue$name$matc = inputValue.name.match(newFilesRegExp);

	                var _inputValue$name$matc2 = babelHelpers.slicedToArray(_inputValue$name$matc, 4);

	                fileCounter = _inputValue$name$matc2[1];
	                fileSetting = _inputValue$name$matc2[3];

	                if (fileCounter && fileSetting) {
	                  morePhotoValues[fileCounter] = morePhotoValues[fileCounter] || {};
	                  morePhotoValues[fileCounter][fileSetting] = inputValue.value;
	                }
	              } else {
	                morePhotoValues[inputValue.name] = inputValue.value;
	              }
	            }
	          }

	          rowFields['MORE_PHOTO'] = morePhotoValues;

	          if (variationId && this.showCatalogWithOffers) {
	            var variationRowId = this.getRowIdByProductId(variationId); // clear old cache

	            this.clearVariationCache(variationId);
	            submitData.FIELDS[variationRowId] = {};

	            var _iterator = _createForOfIteratorHelper(this.variationFieldNames),
	                _step;

	            try {
	              for (_iterator.s(); !(_step = _iterator.n()).done;) {
	                var fieldName = _step.value;

	                if (!rowFields.hasOwnProperty(fieldName)) {
	                  continue;
	                }

	                submitData.FIELDS[variationRowId][fieldName] = rowFields[fieldName];
	                delete submitData.FIELDS[rowId][fieldName];
	              }
	            } catch (err) {
	              _iterator.e(err);
	            } finally {
	              _iterator.f();
	            }
	          }
	        }

	        this.morePhotoChangedInputs.clear();
	      }
	    }
	  }, {
	    key: "getCurrentVariationIdByProduct",
	    value: function getCurrentVariationIdByProduct(productId) {
	      return productId in this.productVariationMap ? main_core.Text.toNumber(this.productVariationMap[productId]) : null;
	    }
	  }, {
	    key: "handleOnFilterApply",
	    value: function handleOnFilterApply(event) {
	      var data = event.getData();
	      var filterGridId = data[0];
	      var filter = data[2] instanceof BX.Main.Filter ? event.getData()[2] : null;

	      if (filter && filterGridId === this.gridId) {
	        var filterFields = this.getFilterFields(filter);
	        var sectionId = '0';

	        if (main_core.Type.isArray(filterFields)) {
	          var fieldSectionId = this.getFieldSectionId(filterFields);

	          if (fieldSectionId) {
	            var value = fieldSectionId['VALUE'];

	            if (main_core.Type.isObject(value)) {
	              sectionId = value['VALUE'];
	            }
	          }
	        }

	        this.setNewProductButtonHrefSectionId(sectionId);
	      }
	    }
	  }, {
	    key: "handleOnSaveImage",
	    value: function handleOnSaveImage(event) {
	      var _event$getData5 = event.getData(),
	          _event$getData6 = babelHelpers.slicedToArray(_event$getData5, 3),
	          id = _event$getData6[0],
	          inputId = _event$getData6[1],
	          response = _event$getData6[2];

	      this.morePhotoChangedInputs.set(id, response.data.input);
	    }
	  }, {
	    key: "getFilterFields",
	    value: function getFilterFields(filter) {
	      var presets = filter.getParam('PRESETS');
	      var tmpFilterPreset = null;

	      if (main_core.Type.isArray(presets)) {
	        tmpFilterPreset = presets.find(function (preset) {
	          return preset['ID'] === 'tmp_filter';
	        });
	      }

	      if (tmpFilterPreset) {
	        return tmpFilterPreset['FIELDS'] || null;
	      }

	      return null;
	    }
	  }, {
	    key: "getFieldSectionId",
	    value: function getFieldSectionId(fields) {
	      return fields.find(function (field) {
	        return field['ID'] === 'field_SECTION_ID';
	      });
	    }
	  }, {
	    key: "setNewProductButtonHrefSectionId",
	    value: function setNewProductButtonHrefSectionId(sectionId) {
	      var uri = new main_core.Uri(this.createNewProductHref);
	      uri.setQueryParams({
	        IBLOCK_SECTION_ID: sectionId
	      });
	      var button = document.getElementById('create_new_product_button_' + this.gridId);

	      if (main_core.Type.isDomNode(button)) {
	        button.href = uri.getPath() + uri.getQuery();
	      }
	    }
	  }]);
	  return IblockProductList;
	}();

	exports.IblockProductList = IblockProductList;

}((this.BX.Catalog = this.BX.Catalog || {}),BX.Event,BX));
//# sourceMappingURL=iblock-product-list.bundle.js.map
