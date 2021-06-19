this.BX = this.BX || {};
this.BX.Catalog = this.BX.Catalog || {};
(function (exports,main_core,catalog_skuTree,main_core_events) {
	'use strict';

	function _templateObject7() {
	  var data = babelHelpers.taggedTemplateLiteral(["<div class=\"product-item-scu-list ui-ctl-spacing-right\"></div>"]);

	  _templateObject7 = function _templateObject7() {
	    return data;
	  };

	  return data;
	}

	function _templateObject6() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<div class=\"product-item-detail-info-container\">\n\t\t\t\t<div class=\"product-item-detail-info-container-title\">", "</div>\n\t\t\t\t<div class=\"product-item-scu-container\">\n\t\t\t\t\t", "\n\t\t\t\t</div>\n\t\t\t</div>\n\t\t"]);

	  _templateObject6 = function _templateObject6() {
	    return data;
	  };

	  return data;
	}

	function _templateObject5() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<label \tclass=\"ui-ctl ui-ctl-radio-selector\"\n\t\t\t\t\tonclick=\"", "\"\n\t\t\t\t\ttitle=\"", "\"\n\t\t\t\t\tdata-property-id=\"", "\"\n\t\t\t\t\tdata-property-value=\"", "\">\n\t\t\t\t<input type=\"radio\"\n\t\t\t\t\tdisabled=\"", "\"\n\t\t\t\t\tname=\"property-", "-", "-", "\"\n\t\t\t\t\tclass=\"ui-ctl-element\">\n\t\t\t\t<span class=\"ui-ctl-inner\">\n\t\t\t\t\t<span class=\"ui-ctl-label-text\">", "</span>\n\t\t\t\t</span>\n\t\t\t</label>\n\t\t"]);

	  _templateObject5 = function _templateObject5() {
	    return data;
	  };

	  return data;
	}

	function _templateObject4() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<label \tclass=\"ui-ctl ui-ctl-radio-selector\"\n\t\t\t\t\tonclick=\"", "\"\n\t\t\t\t\ttitle=\"", "\"\n\t\t\t\t\tdata-property-id=\"", "\"\n\t\t\t\t\tdata-property-value=\"", "\">\n\t\t\t\t<input type=\"radio\"\n\t\t\t\t\tdisabled=\"", "\"\n\t\t\t\t\tname=\"property-", "-", "-", "\"\n\t\t\t\t\tclass=\"ui-ctl-element\">\n\t\t\t\t<span class=\"ui-ctl-inner\">\n\t\t\t\t\t", "\n\t\t\t\t\t", "\n\t\t\t\t</span>\n\t\t\t</label>\n\t\t"]);

	  _templateObject4 = function _templateObject4() {
	    return data;
	  };

	  return data;
	}

	function _templateObject3() {
	  var data = babelHelpers.taggedTemplateLiteral(["<span class=\"ui-ctl-label-text\">-</span>"]);

	  _templateObject3 = function _templateObject3() {
	    return data;
	  };

	  return data;
	}

	function _templateObject2() {
	  var data = babelHelpers.taggedTemplateLiteral(["<span class=\"ui-ctl-label-img\" style=\"", "\"></span>"]);

	  _templateObject2 = function _templateObject2() {
	    return data;
	  };

	  return data;
	}

	function _templateObject() {
	  var data = babelHelpers.taggedTemplateLiteral(["<span class=\"ui-ctl-label-text\">", "</span>"]);

	  _templateObject = function _templateObject() {
	    return data;
	  };

	  return data;
	}

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
	      return this.parent.getSelectedSku().ID;
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
	        nameNode = main_core.Tag.render(_templateObject(), propertyName);
	      }

	      var iconNode = '';

	      if (propertyValue.PICT && propertyValue.PICT.SRC) {
	        var style = "background-image: url('" + propertyValue.PICT.SRC + "');";
	        iconNode = main_core.Tag.render(_templateObject2(), style);
	      } else if (nameNode) {
	        nameNode.style.paddingLeft = '0';
	      } else {
	        nameNode = main_core.Tag.render(_templateObject3());
	      }

	      return main_core.Tag.render(_templateObject4(), this.skuSelectHandler, propertyName, this.getId(), propertyValue.ID, !this.parent.isSelectable(), this.getSelectedSkuId(), this.getId(), uniqueId, iconNode, nameNode);
	    }
	  }, {
	    key: "renderTextSku",
	    value: function renderTextSku(propertyValue, uniqueId) {
	      var propertyName = main_core.Type.isStringFilled(propertyValue.NAME) ? main_core.Text.encode(propertyValue.NAME) : '-';
	      return main_core.Tag.render(_templateObject5(), this.skuSelectHandler, propertyName, this.getId(), propertyValue.ID, !this.parent.isSelectable(), this.getSelectedSkuId(), this.getId(), uniqueId, propertyName);
	    }
	  }, {
	    key: "layout",
	    value: function layout() {
	      if (!this.hasSkuValues()) {
	        return;
	      }

	      this.skuList = this.renderProperties();
	      this.toggleSkuPropertyValues();
	      return main_core.Tag.render(_templateObject6(), main_core.Text.encode(this.property.NAME), this.skuList);
	    }
	  }, {
	    key: "renderProperties",
	    value: function renderProperties() {
	      var _this = this;

	      var skuList = main_core.Tag.render(_templateObject7());
	      this.property.VALUES.forEach(function (propertyValue) {
	        var propertyValueId = propertyValue.ID;
	        var node;
	        var uniqueId = main_core.Text.getRandom();

	        if (!propertyValueId || _this.existingValues.includes(propertyValueId)) {
	          if (_this.property.SHOW_MODE === 'PICT') {
	            node = _this.renderPictureSku(propertyValue, uniqueId);
	          } else {
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

	        if (_this2.hideUnselected && selectedSkuProperty !== id || !activeSkuProperties.includes(id)) {
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
	      event.stopPropagation();
	      var selectedSkuProperty = event.target.closest('[data-property-id]');

	      if (!this.parent.isSelectable() || main_core.Dom.hasClass(selectedSkuProperty, 'selected')) {
	        return;
	      }

	      var propertyId = main_core.Text.toNumber(selectedSkuProperty.getAttribute('data-property-id'));
	      var propertyValue = main_core.Text.toNumber(selectedSkuProperty.getAttribute('data-property-value'));
	      this.parent.setSelectedProperty(propertyId, propertyValue);
	      this.parent.toggleSkuProperties();
	      main_core_events.EventEmitter.emit('SkuProperty::onChange', [this.parent.getSelectedSku(), this.property]);

	      if (this.parent) {
	        this.parent.emit('SkuProperty::onChange', [this.parent.getSelectedSku(), this.property]);
	      }
	    }
	  }]);
	  return SkuProperty;
	}();

	function _templateObject$1() {
	  var data = babelHelpers.taggedTemplateLiteral(["<div class=\"product-item-scu-wrapper\"></div>"]);

	  _templateObject$1 = function _templateObject() {
	    return data;
	  };

	  return data;
	}

	function _createForOfIteratorHelper(o, allowArrayLike) { var it; if (typeof Symbol === "undefined" || o[Symbol.iterator] == null) { if (Array.isArray(o) || (it = _unsupportedIterableToArray(o)) || allowArrayLike && o && typeof o.length === "number") { if (it) o = it; var i = 0; var F = function F() {}; return { s: F, n: function n() { if (i >= o.length) return { done: true }; return { done: false, value: o[i++] }; }, e: function e(_e) { throw _e; }, f: F }; } throw new TypeError("Invalid attempt to iterate non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); } var normalCompletion = true, didErr = false, err; return { s: function s() { it = o[Symbol.iterator](); }, n: function n() { var step = it.next(); normalCompletion = step.done; return step; }, e: function e(_e2) { didErr = true; err = _e2; }, f: function f() { try { if (!normalCompletion && it.return != null) it.return(); } finally { if (didErr) throw err; } } }; }

	function _unsupportedIterableToArray(o, minLen) { if (!o) return; if (typeof o === "string") return _arrayLikeToArray(o, minLen); var n = Object.prototype.toString.call(o).slice(8, -1); if (n === "Object" && o.constructor) n = o.constructor.name; if (n === "Map" || n === "Set") return Array.from(o); if (n === "Arguments" || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(n)) return _arrayLikeToArray(o, minLen); }

	function _arrayLikeToArray(arr, len) { if (len == null || len > arr.length) len = arr.length; for (var i = 0, arr2 = new Array(len); i < len; i++) { arr2[i] = arr[i]; } return arr2; }
	var SkuTree = /*#__PURE__*/function (_EventEmitter) {
	  babelHelpers.inherits(SkuTree, _EventEmitter);

	  function SkuTree(options) {
	    var _this;

	    babelHelpers.classCallCheck(this, SkuTree);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(SkuTree).call(this));
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "selectedValues", {});

	    _this.setEventNamespace('BX.Catalog.SkuTree');

	    _this.skuTree = options.skuTree || {};
	    _this.selectable = options.selectable !== false;
	    _this.hideUnselected = options.hideUnselected === true;

	    if (_this.hasSku()) {
	      _this.selectedValues = _this.skuTree.SELECTED_VALUES || babelHelpers.objectSpread({}, _this.skuTree.OFFERS[0].TREE);
	    }

	    return _this;
	  }

	  babelHelpers.createClass(SkuTree, [{
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
	      this.selectedValues[propertyId] = propertyValue;
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

	      for (var _i = 0, _Object$values = Object.values(this.skuTree.OFFERS_PROP); _i < _Object$values.length; _i++) {
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
	      return main_core.Type.isArrayFilled(this.skuTree.OFFERS);
	    }
	  }, {
	    key: "hasSkuProps",
	    value: function hasSkuProps() {
	      return main_core.Type.isPlainObject(this.skuTree.OFFERS_PROP) && Object.keys(this.skuTree.OFFERS_PROP).length;
	    }
	  }, {
	    key: "getSelectedSku",
	    value: function getSelectedSku() {
	      var _this2 = this;

	      if (!this.hasSku()) {
	        return null;
	      }

	      return this.skuTree.OFFERS.filter(function (item) {
	        return JSON.stringify(item.TREE) === JSON.stringify(_this2.selectedValues);
	      })[0];
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

	      for (var _i2 = 0, _Object$values2 = Object.values(this.skuTree.OFFERS_PROP); _i2 < _Object$values2.length; _i2++) {
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
	        return this.skuTree.OFFERS;
	      }

	      var selectedValues = this.getSelectedValues();
	      return this.skuTree.OFFERS.filter(function (sku) {
	        var _iterator3 = _createForOfIteratorHelper(filter),
	            _step3;

	        try {
	          for (_iterator3.s(); !(_step3 = _iterator3.n()).done;) {
	            var prop = _step3.value;

	            if (sku.TREE[prop] !== selectedValues[prop]) {
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
	      return this.getSelectedSku()['TREE'][propertyId];
	    }
	  }, {
	    key: "layout",
	    value: function layout() {
	      var container = main_core.Tag.render(_templateObject$1());
	      this.skuProperties = [];

	      if (this.hasSku() && this.hasSkuProps()) {
	        for (var i in this.skuTree.OFFERS_PROP) {
	          if (this.skuTree.OFFERS_PROP.hasOwnProperty(i)) {
	            var skuProperty = new SkuProperty({
	              parent: this,
	              property: this.skuTree.OFFERS_PROP[i],
	              existingValues: this.skuTree.EXISTING_VALUES[i],
	              offers: this.skuTree.OFFERS,
	              hideUnselected: this.hideUnselected
	            });
	            container.appendChild(skuProperty.layout());
	            this.skuProperties.push(skuProperty);
	          }
	        }
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

	exports.SkuTree = SkuTree;

}((this.BX.Catalog.SkuTree = this.BX.Catalog.SkuTree || {}),BX,BX.Catalog.SkuTree,BX.Event));
//# sourceMappingURL=sku-tree.bundle.js.map
