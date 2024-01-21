/* eslint-disable */
this.BX = this.BX || {};
this.BX.Catalog = this.BX.Catalog || {};
(function (exports,ui_designTokens,main_core,catalog_skuTree,main_core_events) {
	'use strict';

	var _templateObject, _templateObject2, _templateObject3, _templateObject4, _templateObject5, _templateObject6, _templateObject7, _templateObject8;
	var SkuProperty = /*#__PURE__*/function () {
	  function SkuProperty(options) {
	    babelHelpers.classCallCheck(this, SkuProperty);
	    babelHelpers.defineProperty(this, "skuSelectHandler", this.handleSkuSelect.bind(this));
	    this.parent = options.parent || null;
	    if (!this.parent) {
	      throw new Error('Parent is not defined.');
	    }
	    this.property = options.property || {};
	    this.offers = options.offers || [];
	    this.existingValues = options.existingValues || [];
	    this.nodeDescriptions = [];
	    this.hideUnselected = options.hideUnselected;
	  }
	  babelHelpers.createClass(SkuProperty, [{
	    key: "getId",
	    value: function getId() {
	      return this.property.ID;
	    }
	  }, {
	    key: "getSelectedSkuId",
	    value: function getSelectedSkuId() {
	      return this.parent.getSelectedSkuId();
	    }
	  }, {
	    key: "hasSkuValues",
	    value: function hasSkuValues() {
	      return this.property.VALUES.length;
	    }
	  }, {
	    key: "renderPictureSku",
	    value: function renderPictureSku(propertyValue, uniqueId) {
	      var propertyName = main_core.Type.isStringFilled(propertyValue.NAME) ? main_core.Text.encode(propertyValue.NAME) : '';
	      var nameNode = '';
	      if (main_core.Type.isStringFilled(propertyName)) {
	        nameNode = main_core.Tag.render(_templateObject || (_templateObject = babelHelpers.taggedTemplateLiteral(["<span class=\"ui-ctl-label-text\">", "</span>"])), propertyName);
	      }
	      var iconNode = '';
	      if (propertyValue.PICT && propertyValue.PICT.SRC) {
	        var style = "background-image: url('" + propertyValue.PICT.SRC + "');";
	        iconNode = main_core.Tag.render(_templateObject2 || (_templateObject2 = babelHelpers.taggedTemplateLiteral(["<span class=\"ui-ctl-label-img\" style=\"", "\"></span>"])), style);
	      } else if (nameNode) {
	        nameNode.style.paddingLeft = '0';
	      } else {
	        nameNode = main_core.Tag.render(_templateObject3 || (_templateObject3 = babelHelpers.taggedTemplateLiteral(["<span class=\"ui-ctl-label-text\">-</span>"])));
	      }
	      var titleItem = this.parent.isShortView && main_core.Type.isStringFilled(this.property.NAME) ? main_core.Text.encode(this.property.NAME) : propertyName;
	      return main_core.Tag.render(_templateObject4 || (_templateObject4 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<label \tclass=\"ui-ctl ui-ctl-radio-selector\"\n\t\t\t\t\tonclick=\"", "\"\n\t\t\t\t\ttitle=\"", "\"\n\t\t\t\t\tdata-property-id=\"", "\"\n\t\t\t\t\tdata-property-value=\"", "\">\n\t\t\t\t<input type=\"radio\"\n\t\t\t\t\tdisabled=\"", "\"\n\t\t\t\t\tname=\"property-", "-", "-", "\"\n\t\t\t\t\tclass=\"ui-ctl-element\">\n\t\t\t\t<span class=\"ui-ctl-inner\">\n\t\t\t\t\t", "\n\t\t\t\t\t", "\n\t\t\t\t</span>\n\t\t\t</label>\n\t\t"])), this.skuSelectHandler, titleItem, this.getId(), propertyValue.ID, !this.parent.isSelectable(), this.getSelectedSkuId(), this.getId(), uniqueId, iconNode, nameNode);
	    }
	  }, {
	    key: "renderTextSku",
	    value: function renderTextSku(propertyValue, uniqueId) {
	      var propertyName = main_core.Type.isStringFilled(propertyValue.NAME) ? main_core.Text.encode(propertyValue.NAME) : '-';
	      var titleItem = this.parent.isShortView && main_core.Type.isStringFilled(this.property.NAME) ? main_core.Text.encode(this.property.NAME) : propertyName;
	      return main_core.Tag.render(_templateObject5 || (_templateObject5 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<label \tclass=\"ui-ctl ui-ctl-radio-selector\"\n\t\t\t\t\tonclick=\"", "\"\n\t\t\t\t\ttitle=\"", "\"\n\t\t\t\t\tdata-property-id=\"", "\"\n\t\t\t\t\tdata-property-value=\"", "\">\n\t\t\t\t<input type=\"radio\"\n\t\t\t\t\tdisabled=\"", "\"\n\t\t\t\t\tname=\"property-", "-", "-", "\"\n\t\t\t\t\tclass=\"ui-ctl-element\">\n\t\t\t\t<span class=\"ui-ctl-inner\">\n\t\t\t\t\t<span class=\"ui-ctl-label-text\">", "</span>\n\t\t\t\t</span>\n\t\t\t</label>\n\t\t"])), this.skuSelectHandler, titleItem, this.getId(), propertyValue.ID, !this.parent.isSelectable(), this.getSelectedSkuId(), this.getId(), uniqueId, propertyName);
	    }
	  }, {
	    key: "layout",
	    value: function layout() {
	      if (!this.hasSkuValues()) {
	        return;
	      }
	      this.skuList = this.renderProperties();
	      this.toggleSkuPropertyValues();
	      var title = !this.parent.isShortView ? main_core.Tag.render(_templateObject6 || (_templateObject6 = babelHelpers.taggedTemplateLiteral(["<div class=\"product-item-detail-info-container-title\">", "</div>"])), main_core.Text.encode(this.property.NAME)) : '';
	      return main_core.Tag.render(_templateObject7 || (_templateObject7 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<div class=\"product-item-detail-info-container\">\n\t\t\t\t", "\n\t\t\t\t<div class=\"product-item-scu-container\">\n\t\t\t\t\t", "\n\t\t\t\t</div>\n\t\t\t</div>\n\t\t"])), title, this.skuList);
	    }
	  }, {
	    key: "renderProperties",
	    value: function renderProperties() {
	      var _this = this;
	      var skuList = main_core.Tag.render(_templateObject8 || (_templateObject8 = babelHelpers.taggedTemplateLiteral(["<div class=\"product-item-scu-list ui-ctl-spacing-right\"></div>"])));
	      this.property.VALUES.forEach(function (propertyValue) {
	        var propertyValueId = propertyValue.ID;
	        var node;
	        var uniqueId = main_core.Text.getRandom();
	        if (!propertyValueId || _this.existingValues.includes(propertyValueId)) {
	          if (_this.property.SHOW_MODE === 'PICT') {
	            main_core.Dom.addClass(skuList, 'product-item-scu-list--pick-color');
	            node = _this.renderPictureSku(propertyValue, uniqueId);
	          } else {
	            main_core.Dom.addClass(skuList, 'product-item-scu-list--pick-size');
	            node = _this.renderTextSku(propertyValue, uniqueId);
	          }
	          _this.nodeDescriptions.push({
	            propertyValueId: propertyValueId,
	            node: node
	          });
	          skuList.appendChild(node);
	        }
	      });
	      return skuList;
	    }
	  }, {
	    key: "toggleSkuPropertyValues",
	    value: function toggleSkuPropertyValues() {
	      var _this2 = this;
	      var selectedSkuProperty = this.parent.getSelectedSkuProperty(this.getId());
	      var activeSkuProperties = this.parent.getActiveSkuProperties(this.getId());
	      this.nodeDescriptions.forEach(function (item) {
	        var id = main_core.Text.toNumber(item.propertyValueId);
	        var input = item.node.querySelector('input[type="radio"]');
	        if (selectedSkuProperty === id) {
	          input.checked = true;
	          main_core.Dom.addClass(item.node, 'selected');
	        } else {
	          input.checked = false;
	          main_core.Dom.removeClass(item.node, 'selected');
	        }
	        if (_this2.hideUnselected && selectedSkuProperty !== id || !activeSkuProperties.includes(item.propertyValueId)) {
	          main_core.Dom.style(item.node, {
	            display: 'none'
	          });
	        } else {
	          main_core.Dom.style(item.node, {
	            display: null
	          });
	        }
	      });
	    }
	  }, {
	    key: "handleSkuSelect",
	    value: function handleSkuSelect(event) {
	      var _this3 = this;
	      event.stopPropagation();
	      var selectedSkuProperty = event.target.closest('[data-property-id]');
	      if (!this.parent.isSelectable() || main_core.Dom.hasClass(selectedSkuProperty, 'selected')) {
	        return;
	      }
	      var propertyId = main_core.Text.toNumber(selectedSkuProperty.getAttribute('data-property-id'));
	      var propertyValue = main_core.Text.toNumber(selectedSkuProperty.getAttribute('data-property-value'));
	      this.parent.setSelectedProperty(propertyId, propertyValue);
	      this.parent.getSelectedSku().then(function (selectedSkuData) {
	        main_core_events.EventEmitter.emit('SkuProperty::onChange', [selectedSkuData, _this3.property]);
	        if (_this3.parent) {
	          _this3.parent.emit('SkuProperty::onChange', [selectedSkuData, _this3.property]);
	        }
	      });
	      this.parent.toggleSkuProperties();
	    }
	  }]);
	  return SkuProperty;
	}();

	var _templateObject$1;
	function _createForOfIteratorHelper(o, allowArrayLike) { var it = typeof Symbol !== "undefined" && o[Symbol.iterator] || o["@@iterator"]; if (!it) { if (Array.isArray(o) || (it = _unsupportedIterableToArray(o)) || allowArrayLike && o && typeof o.length === "number") { if (it) o = it; var i = 0; var F = function F() {}; return { s: F, n: function n() { if (i >= o.length) return { done: true }; return { done: false, value: o[i++] }; }, e: function e(_e) { throw _e; }, f: F }; } throw new TypeError("Invalid attempt to iterate non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); } var normalCompletion = true, didErr = false, err; return { s: function s() { it = it.call(o); }, n: function n() { var step = it.next(); normalCompletion = step.done; return step; }, e: function e(_e2) { didErr = true; err = _e2; }, f: function f() { try { if (!normalCompletion && it["return"] != null) it["return"](); } finally { if (didErr) throw err; } } }; }
	function _unsupportedIterableToArray(o, minLen) { if (!o) return; if (typeof o === "string") return _arrayLikeToArray(o, minLen); var n = Object.prototype.toString.call(o).slice(8, -1); if (n === "Object" && o.constructor) n = o.constructor.name; if (n === "Map" || n === "Set") return Array.from(o); if (n === "Arguments" || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(n)) return _arrayLikeToArray(o, minLen); }
	function _arrayLikeToArray(arr, len) { if (len == null || len > arr.length) len = arr.length; for (var i = 0, arr2 = new Array(len); i < len; i++) arr2[i] = arr[i]; return arr2; }
	function ownKeys(object, enumerableOnly) { var keys = Object.keys(object); if (Object.getOwnPropertySymbols) { var symbols = Object.getOwnPropertySymbols(object); enumerableOnly && (symbols = symbols.filter(function (sym) { return Object.getOwnPropertyDescriptor(object, sym).enumerable; })), keys.push.apply(keys, symbols); } return keys; }
	function _objectSpread(target) { for (var i = 1; i < arguments.length; i++) { var source = null != arguments[i] ? arguments[i] : {}; i % 2 ? ownKeys(Object(source), !0).forEach(function (key) { babelHelpers.defineProperty(target, key, source[key]); }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(target, Object.getOwnPropertyDescriptors(source)) : ownKeys(Object(source)).forEach(function (key) { Object.defineProperty(target, key, Object.getOwnPropertyDescriptor(source, key)); }); } return target; }
	function _classStaticPrivateMethodGet(receiver, classConstructor, method) { _classCheckPrivateStaticAccess(receiver, classConstructor); return method; }
	function _classCheckPrivateStaticAccess(receiver, classConstructor) { if (receiver !== classConstructor) { throw new TypeError("Private static access of wrong provenance"); } }
	var iblockSkuProperties = new Map();
	var iblockSkuList = new Map();
	var propertyPromises = new Map();
	var SkuTree = /*#__PURE__*/function (_EventEmitter) {
	  babelHelpers.inherits(SkuTree, _EventEmitter);
	  function SkuTree(options) {
	    var _this$skuTree;
	    var _this;
	    babelHelpers.classCallCheck(this, SkuTree);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(SkuTree).call(this));
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "selectedValues", {});
	    _this.setEventNamespace('BX.Catalog.SkuTree');
	    _this.id = main_core.Text.getRandom();
	    _this.skuTree = options.skuTree || {};
	    _this.productId = (_this$skuTree = _this.skuTree) === null || _this$skuTree === void 0 ? void 0 : _this$skuTree.PRODUCT_ID;
	    _this.skuTreeOffers = _this.skuTree.OFFERS || [];
	    if (!main_core.Type.isNil(options.skuTree.OFFERS_JSON) && !main_core.Type.isArrayFilled(_this.skuTreeOffers)) {
	      _this.skuTreeOffers = JSON.parse(_this.skuTree.OFFERS_JSON);
	    }
	    _this.iblockId = _this.skuTree.IBLOCK_ID || SkuTree.DEFAULT_IBLOCK_ID;
	    if (!iblockSkuProperties.has(_this.iblockId)) {
	      if (main_core.Type.isObject(_this.skuTree.OFFERS_PROP)) {
	        iblockSkuProperties.set(_this.iblockId, _this.skuTree.OFFERS_PROP);
	      } else {
	        iblockSkuProperties.set(_this.iblockId, {});
	        var promise = new Promise(function (resolve) {
	          main_core.ajax.runAction('catalog.skuTree.getIblockProperties', {
	            json: {
	              iblockId: _this.iblockId
	            }
	          }).then(function (result) {
	            iblockSkuProperties.set(_this.iblockId, result.data);
	            resolve();
	            propertyPromises["delete"](_classStaticPrivateMethodGet(SkuTree, SkuTree, _getIblockPropertiesRequestName).call(SkuTree, _this.iblockId));
	          });
	        });
	        propertyPromises.set(_classStaticPrivateMethodGet(SkuTree, SkuTree, _getIblockPropertiesRequestName).call(SkuTree, _this.iblockId), promise);
	      }
	    }
	    _this.selectable = options.selectable !== false;
	    _this.isShortView = options.isShortView === true;
	    _this.hideUnselected = options.hideUnselected === true;
	    if (_this.hasSku()) {
	      _this.selectedValues = _this.skuTree.SELECTED_VALUES || _objectSpread({}, _this.skuTreeOffers[0].TREE);
	    }
	    _this.existingValues = _this.skuTree.EXISTING_VALUES || {};
	    if (!main_core.Type.isNil(options.skuTree.EXISTING_VALUES_JSON) && main_core.Type.isNil(options.skuTree.EXISTING_VALUES)) {
	      _this.existingValues = JSON.parse(options.skuTree.EXISTING_VALUES_JSON);
	    }
	    for (var key in _this.existingValues) {
	      if (_this.existingValues[key].length === 1 && _this.existingValues[key][0] === 0) {
	        delete _this.existingValues[key];
	      }
	    }
	    return _this;
	  }
	  babelHelpers.createClass(SkuTree, [{
	    key: "getProperties",
	    value: function getProperties() {
	      return iblockSkuProperties.get(this.iblockId);
	    }
	  }, {
	    key: "isSelectable",
	    value: function isSelectable() {
	      return this.selectable;
	    }
	  }, {
	    key: "getSelectedValues",
	    value: function getSelectedValues() {
	      return this.selectedValues;
	    }
	  }, {
	    key: "setSelectedProperty",
	    value: function setSelectedProperty(propertyId, propertyValue) {
	      this.selectedValues[propertyId] = main_core.Text.toNumber(propertyValue);
	      var remainingProperties = this.getRemainingProperties(propertyId);
	      if (remainingProperties.length) {
	        var _iterator = _createForOfIteratorHelper(remainingProperties),
	          _step;
	        try {
	          for (_iterator.s(); !(_step = _iterator.n()).done;) {
	            var remainingPropertyId = _step.value;
	            var filterProperties = this.getFilterProperties(remainingPropertyId);
	            var skuItems = this.filterSku(filterProperties);
	            if (skuItems.length) {
	              var found = false;
	              var _iterator2 = _createForOfIteratorHelper(skuItems),
	                _step2;
	              try {
	                for (_iterator2.s(); !(_step2 = _iterator2.n()).done;) {
	                  var sku = _step2.value;
	                  if (sku.TREE[remainingPropertyId] === this.selectedValues[remainingPropertyId]) {
	                    found = true;
	                  }
	                }
	              } catch (err) {
	                _iterator2.e(err);
	              } finally {
	                _iterator2.f();
	              }
	              if (!found) {
	                this.selectedValues[remainingPropertyId] = skuItems[0].TREE[remainingPropertyId];
	              }
	            }
	          }
	        } catch (err) {
	          _iterator.e(err);
	        } finally {
	          _iterator.f();
	        }
	      }
	    }
	  }, {
	    key: "getRemainingProperties",
	    value: function getRemainingProperties(propertyId) {
	      var filter = [];
	      var found = false;
	      for (var _i = 0, _Object$values = Object.values(this.getProperties()); _i < _Object$values.length; _i++) {
	        var prop = _Object$values[_i];
	        if (prop.ID === propertyId) {
	          found = true;
	        } else if (found) {
	          filter.push(prop.ID);
	        }
	      }
	      return filter;
	    }
	  }, {
	    key: "hasSku",
	    value: function hasSku() {
	      return main_core.Type.isArrayFilled(this.skuTreeOffers);
	    }
	  }, {
	    key: "hasSkuProps",
	    value: function hasSkuProps() {
	      return Object.values(this.getProperties()).length > 0;
	    }
	  }, {
	    key: "getSelectedSkuId",
	    value: function getSelectedSkuId() {
	      var _this2 = this;
	      if (!this.hasSku()) {
	        return;
	      }
	      var item = this.skuTreeOffers.filter(function (item) {
	        return JSON.stringify(item.TREE) === JSON.stringify(_this2.selectedValues);
	      })[0];
	      return item === null || item === void 0 ? void 0 : item.ID;
	    }
	  }, {
	    key: "getSelectedSku",
	    value: function getSelectedSku() {
	      var _this3 = this;
	      return new Promise(function (resolve, reject) {
	        var skuId = _this3.getSelectedSkuId();
	        if (skuId <= 0) {
	          reject();
	          return;
	        }
	        if (iblockSkuList.has(skuId)) {
	          var skuData = iblockSkuList.get(skuId);
	          resolve(skuData);
	        } else {
	          if (propertyPromises.has(_classStaticPrivateMethodGet(SkuTree, SkuTree, _getSkuRequestName).call(SkuTree, skuId))) {
	            propertyPromises.get(_classStaticPrivateMethodGet(SkuTree, SkuTree, _getSkuRequestName).call(SkuTree, skuId)).then(function (skuFields) {
	              resolve(skuFields);
	            });
	          } else {
	            var skuRequest = main_core.ajax.runAction('catalog.skuTree.getSku', {
	              json: {
	                skuId: skuId
	              }
	            }).then(function (result) {
	              var skuData = result.data;
	              iblockSkuList.set(skuId, skuData);
	              resolve(skuData);
	              propertyPromises["delete"](_classStaticPrivateMethodGet(SkuTree, SkuTree, _getSkuRequestName).call(SkuTree, skuId), skuRequest);
	            });
	            propertyPromises.set(_classStaticPrivateMethodGet(SkuTree, SkuTree, _getSkuRequestName).call(SkuTree, skuId), skuRequest);
	          }
	        }
	      });
	    }
	  }, {
	    key: "getActiveSkuProperties",
	    value: function getActiveSkuProperties(propertyId) {
	      var activeSkuProperties = [];
	      var filterProperties = this.getFilterProperties(propertyId);
	      this.filterSku(filterProperties).forEach(function (item) {
	        if (!activeSkuProperties.includes(item.TREE[propertyId])) {
	          activeSkuProperties.push(item.TREE[propertyId]);
	        }
	      });
	      return activeSkuProperties;
	    }
	  }, {
	    key: "getFilterProperties",
	    value: function getFilterProperties(propertyId) {
	      var filter = [];
	      for (var _i2 = 0, _Object$values2 = Object.values(this.getProperties()); _i2 < _Object$values2.length; _i2++) {
	        var prop = _Object$values2[_i2];
	        if (prop.ID === propertyId) {
	          break;
	        }
	        filter.push(prop.ID);
	      }
	      return filter;
	    }
	  }, {
	    key: "filterSku",
	    value: function filterSku(filter) {
	      if (filter.length === 0) {
	        return this.skuTreeOffers;
	      }
	      var selectedValues = this.getSelectedValues();
	      return this.skuTreeOffers.filter(function (sku) {
	        var _iterator3 = _createForOfIteratorHelper(filter),
	          _step3;
	        try {
	          for (_iterator3.s(); !(_step3 = _iterator3.n()).done;) {
	            var propertyId = _step3.value;
	            if (sku.TREE[propertyId] !== selectedValues[propertyId]) {
	              return false;
	            }
	          }
	        } catch (err) {
	          _iterator3.e(err);
	        } finally {
	          _iterator3.f();
	        }
	        return true;
	      });
	    }
	  }, {
	    key: "getSelectedSkuProperty",
	    value: function getSelectedSkuProperty(propertyId) {
	      return main_core.Text.toNumber(this.selectedValues[propertyId]);
	    }
	  }, {
	    key: "layout",
	    value: function layout() {
	      var _this4 = this;
	      var container = main_core.Tag.render(_templateObject$1 || (_templateObject$1 = babelHelpers.taggedTemplateLiteral(["<div class=\"product-item-scu-wrapper\" id=\"", "\"></div>"])), this.id);
	      if (this.isShortView) {
	        main_core.Dom.addClass(container, '--short-format');
	      }
	      this.skuProperties = [];
	      if (this.hasSku()) {
	        new Promise(function (resolve) {
	          if (propertyPromises.has(_classStaticPrivateMethodGet(SkuTree, SkuTree, _getIblockPropertiesRequestName).call(SkuTree, _this4.iblockId))) {
	            propertyPromises.get(_classStaticPrivateMethodGet(SkuTree, SkuTree, _getIblockPropertiesRequestName).call(SkuTree, _this4.iblockId)).then(resolve);
	          } else {
	            resolve();
	          }
	        }).then(function () {
	          if (!_this4.hasSkuProps()) {
	            return;
	          }
	          var skuProperties = _this4.getProperties();
	          for (var i in skuProperties) {
	            if (skuProperties.hasOwnProperty(i) && !main_core.Type.isNil(_this4.existingValues[i])) {
	              var skuProperty = new SkuProperty({
	                parent: _this4,
	                property: skuProperties[i],
	                existingValues: main_core.Type.isArray(_this4.existingValues[i]) ? _this4.existingValues[i] : Object.values(_this4.existingValues[i]),
	                offers: _this4.skuTreeOffers,
	                hideUnselected: _this4.hideUnselected
	              });
	              main_core.Dom.append(skuProperty.layout(), container);
	              _this4.skuProperties.push(skuProperty);
	            }
	          }
	          main_core_events.EventEmitter.emit('BX.Catalog.SkuTree::onSkuLoaded', {
	            id: _this4.id
	          });
	        });
	      }
	      return container;
	    }
	  }, {
	    key: "toggleSkuProperties",
	    value: function toggleSkuProperties() {
	      this.skuProperties.forEach(function (property) {
	        return property.toggleSkuPropertyValues();
	      });
	    }
	  }]);
	  return SkuTree;
	}(main_core_events.EventEmitter);
	function _getIblockPropertiesRequestName(iblockId) {
	  return 'IblockPropertiesRequest_' + iblockId;
	}
	function _getSkuRequestName(skuId) {
	  return 'SkuFieldsRequest_' + skuId;
	}
	babelHelpers.defineProperty(SkuTree, "DEFAULT_IBLOCK_ID", 0);

	exports.SkuTree = SkuTree;

}((this.BX.Catalog.SkuTree = this.BX.Catalog.SkuTree || {}),BX,BX,BX.Catalog.SkuTree,BX.Event));
//# sourceMappingURL=sku-tree.bundle.js.map
