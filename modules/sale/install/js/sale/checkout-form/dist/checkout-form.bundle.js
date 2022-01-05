this.BX = this.BX || {};
this.BX.Sale = this.BX.Sale || {};
(function (exports,rest_client,main_core,main_core_events) {
	'use strict';

	function _createForOfIteratorHelper(o, allowArrayLike) { var it; if (typeof Symbol === "undefined" || o[Symbol.iterator] == null) { if (Array.isArray(o) || (it = _unsupportedIterableToArray(o)) || allowArrayLike && o && typeof o.length === "number") { if (it) o = it; var i = 0; var F = function F() {}; return { s: F, n: function n() { if (i >= o.length) return { done: true }; return { done: false, value: o[i++] }; }, e: function e(_e) { throw _e; }, f: F }; } throw new TypeError("Invalid attempt to iterate non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); } var normalCompletion = true, didErr = false, err; return { s: function s() { it = o[Symbol.iterator](); }, n: function n() { var step = it.next(); normalCompletion = step.done; return step; }, e: function e(_e2) { didErr = true; err = _e2; }, f: function f() { try { if (!normalCompletion && it.return != null) it.return(); } finally { if (didErr) throw err; } } }; }

	function _unsupportedIterableToArray(o, minLen) { if (!o) return; if (typeof o === "string") return _arrayLikeToArray(o, minLen); var n = Object.prototype.toString.call(o).slice(8, -1); if (n === "Object" && o.constructor) n = o.constructor.name; if (n === "Map" || n === "Set") return Array.from(o); if (n === "Arguments" || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(n)) return _arrayLikeToArray(o, minLen); }

	function _arrayLikeToArray(arr, len) { if (len == null || len > arr.length) len = arr.length; for (var i = 0, arr2 = new Array(len); i < len; i++) { arr2[i] = arr[i]; } return arr2; }

	var Model = /*#__PURE__*/function () {
	  function Model(fields) {
	    babelHelpers.classCallCheck(this, Model);
	    babelHelpers.defineProperty(this, "fields", null);
	    babelHelpers.defineProperty(this, "originalFields", null);
	    this.initFields(fields);
	  }

	  babelHelpers.createClass(Model, [{
	    key: "initFields",
	    value: function initFields(fields) {
	      this.fields = new Map(Object.entries(fields));
	      this.originalFields = new Map(Object.entries(fields));
	    }
	  }, {
	    key: "hasField",
	    value: function hasField(name) {
	      return this.fields.has(name);
	    }
	  }, {
	    key: "getFields",
	    value: function getFields() {
	      var fields = {};

	      var _iterator = _createForOfIteratorHelper(this.fields),
	          _step;

	      try {
	        for (_iterator.s(); !(_step = _iterator.n()).done;) {
	          var _step$value = babelHelpers.slicedToArray(_step.value, 2),
	              k = _step$value[0],
	              v = _step$value[1];

	          fields[k] = v;
	        }
	      } catch (err) {
	        _iterator.e(err);
	      } finally {
	        _iterator.f();
	      }

	      return fields;
	    }
	  }, {
	    key: "getField",
	    value: function getField(name) {
	      var defaultValue = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : null;
	      return this.fields.has(name) ? this.fields.get(name) : defaultValue;
	    }
	  }, {
	    key: "getOriginalField",
	    value: function getOriginalField(name) {
	      return this.originalFields.has(name) ? this.originalFields.get(name) : null;
	    }
	  }, {
	    key: "setField",
	    value: function setField(name, value) {
	      this.fields.set(name, value);
	      return this.getOriginalField(name) === value;
	    }
	  }]);
	  return Model;
	}();

	function _templateObject() {
	  var data = babelHelpers.taggedTemplateLiteral(["<div></div>"]);

	  _templateObject = function _templateObject() {
	    return data;
	  };

	  return data;
	}
	var BaseBlock = /*#__PURE__*/function (_EventEmitter) {
	  babelHelpers.inherits(BaseBlock, _EventEmitter);

	  function BaseBlock(form) {
	    var _this;

	    var settings = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : {};
	    babelHelpers.classCallCheck(this, BaseBlock);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(BaseBlock).call(this));
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "form", null);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "settings", null);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "cache", new main_core.Cache.MemoryCache());
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "mode", BaseBlock.EDIT_MODE);

	    _this.setEventNamespace('BX.Sale.CheckoutForm.Block');

	    _this.form = form;
	    _this.settings = settings;
	    return _this;
	  }

	  babelHelpers.createClass(BaseBlock, [{
	    key: "getForm",
	    value: function getForm() {
	      return this.form;
	    }
	  }, {
	    key: "getModel",
	    value: function getModel() {
	      return this.getForm().getModel();
	    }
	  }, {
	    key: "getCache",
	    value: function getCache() {
	      return this.cache;
	    }
	  }, {
	    key: "getWrapper",
	    value: function getWrapper() {
	      var _this2 = this;

	      return this.getCache().remember('wrapper', function () {
	        var wrapper;

	        if (_this2.hasSetting('wrapperId')) {
	          wrapper = document.getElementById(_this2.getSetting('wrapperId'));

	          if (!main_core.Type.isDomNode(wrapper)) {
	            throw new Error("Can't find block wrapper with id '".concat(_this2.getSetting('wrapperId'), "'."));
	          }
	        } else {
	          wrapper = main_core.Tag.render(_templateObject());

	          _this2.getForm().getContainer().appendChild(wrapper);
	        }

	        return wrapper;
	      });
	    }
	  }, {
	    key: "hasSetting",
	    value: function hasSetting(name) {
	      return name in this.settings;
	    }
	  }, {
	    key: "getSetting",
	    value: function getSetting(name) {
	      var defaultValue = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : null;
	      return this.settings[name] || defaultValue;
	    }
	  }, {
	    key: "getMode",
	    value: function getMode() {
	      return this.mode;
	    }
	  }, {
	    key: "setMode",
	    value: function setMode(mode) {
	      this.mode = mode;
	    }
	  }, {
	    key: "getType",
	    value: function getType() {
	      return this.getSetting('type');
	    }
	  }, {
	    key: "getStage",
	    value: function getStage() {
	      return this.getSetting('stage', Stage.INITIAL);
	    }
	  }, {
	    key: "isSuccess",
	    value: function isSuccess() {
	      return this.getSetting('type') === BlockType.SUCCESS;
	    }
	  }, {
	    key: "refreshLayout",
	    value: function refreshLayout() {
	      var forceLayout = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : false;
	      var mode;
	      var formStage = this.getForm().getStage();
	      var blockStage = this.getStage();

	      if (main_core.Type.isPlainObject(blockStage)) {
	        var viewStage = blockStage.view,
	            editStage = blockStage.edit,
	            hideStage = blockStage.hide;
	        var currentStage = 0;

	        while (currentStage <= formStage) {
	          if (currentStage === hideStage) {
	            mode = undefined;
	          } else if (currentStage === editStage) {
	            mode = BaseBlock.EDIT_MODE;
	          } else if (currentStage === viewStage) {
	            mode = BaseBlock.VIEW_MODE;
	          }

	          currentStage++;
	        }
	      } else if (main_core.Type.isNumber(blockStage)) {
	        if (blockStage <= formStage) {
	          mode = BaseBlock.EDIT_MODE;
	        }
	      }

	      this.clearLayout();

	      if (mode || forceLayout) {
	        if (mode) {
	          this.setMode(mode);
	        }

	        this.layout();
	      }
	    }
	  }, {
	    key: "clearLayout",
	    value: function clearLayout() {
	      if (this.getCache().has('wrapper')) {
	        var wrapper = this.getWrapper();

	        if (main_core.Type.isDomNode(wrapper)) {
	          main_core.Event.unbindAll(wrapper);

	          if (this.hasSetting('wrapperId')) {
	            main_core.Dom.clean(wrapper);
	          } else {
	            main_core.Dom.remove(wrapper);
	          }

	          this.getCache().delete('wrapper');
	        }
	      }
	    }
	  }, {
	    key: "layout",
	    value: function layout() {
	      throw new Error('Not implemented method.');
	    }
	  }]);
	  return BaseBlock;
	}(main_core_events.EventEmitter);
	babelHelpers.defineProperty(BaseBlock, "VIEW_MODE", 'view');
	babelHelpers.defineProperty(BaseBlock, "EDIT_MODE", 'edit');

	function _templateObject5() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<div class=\"checkout-item-price-discount-container d-flex justify-content-between align-items-center\">\n\t\t\t\t<span class=\"checkout-item-price-discount\">", "</span>\n\t\t\t\t<span class=\"checkout-item-price-discount-diff\">-", "</span>\n\t\t\t</div>\n\t\t"]);

	  _templateObject5 = function _templateObject5() {
	    return data;
	  };

	  return data;
	}

	function _templateObject4() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<table>\n\t\t\t\t<tr class=\"checkout-item-summary\">\n\t\t\t\t\t<td>\n\t\t\t\t\t\t<div class=\"checkout-summary\">\n\t\t\t\t\t\t\t<span>", "</span>\n<!--\t\t\t\t\t\t\t<span class=\"checkout-icon-helper\"></span>-->\n\t\t\t\t\t\t</div>\n\t\t\t\t\t</td>\n\t\t\t\t\t<td>\n\t\t\t\t\t\t<div class=\"checkout-item-price-block\">\n\t\t\t\t\t\t\t", "\n\t\t\t\t\t\t\t<span class=\"checkout-item-price\">", "</span>\n\t\t\t\t\t\t</div>\n\t\t\t\t\t</td>\n\t\t\t\t</tr>\n\t\t\t</table>\n\t\t"]);

	  _templateObject4 = function _templateObject4() {
	    return data;
	  };

	  return data;
	}

	function _templateObject3() {
	  var data = babelHelpers.taggedTemplateLiteral(["<div class=\"checkout-item-props\">", ": ", "</div>"]);

	  _templateObject3 = function _templateObject3() {
	    return data;
	  };

	  return data;
	}

	function _templateObject2() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<div class=\"checkout-item-price-discount-container d-flex justify-content-between align-items-center\">\n\t\t\t\t<span class=\"checkout-item-price-discount\">", "</span>\n\t\t\t\t<span class=\"checkout-item-price-discount-diff\">-", "</span>\n\t\t\t</div>\n\t\t"]);

	  _templateObject2 = function _templateObject2() {
	    return data;
	  };

	  return data;
	}

	function _templateObject$1() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<table>\n\t\t\t\t\t<tr class=\"checkout-item\">\n\t\t\t\t\t\t<td>\n\t\t\t\t\t\t\t<div class=\"checkout-item-info\">\n\t\t\t\t\t\t\t\t<div class=\"checkout-item-image-block\">\n\t\t\t\t\t\t\t\t\t<div \n\t\t\t\t\t\t\t\t\t\tclass=\"checkout-item-remove-btn\" \n\t\t\t\t\t\t\t\t\t\tdata-item-id=\"", "\"\n\t\t\t\t\t\t\t\t\t\tonclick=\"", "\"\n\t\t\t\t\t\t\t\t\t\t>\n\t\t\t\t\t\t\t\t\t\t<svg class=\"checkout-item-remove-btn-icon\"  width=\"8\" height=\"9\" viewBox=\"0 0 8 9\" fill=\"none\" xmlns=\"http://www.w3.org/2000/svg\">\n\t\t\t\t\t\t\t\t\t\t\t<path fill-rule=\"evenodd\" clip-rule=\"evenodd\" d=\"M1.18631 0.79834L0.0958433 1.88881L2.70705 4.50001L0.0957031 7.11136L1.18617 8.20182L3.79752 5.59048L6.40848 8.20145L7.49895 7.11098L4.88798 4.50001L7.49881 1.88918L6.40834 0.798718L3.79752 3.40955L1.18631 0.79834Z\" fill=\"#C4C4C4\"/>\n\t\t\t\t\t\t\t\t\t\t</svg>\n\t\t\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t\t\t\t<img src=\"", "\" alt=\"\" class=\"checkout-item-image\">\n\t\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t\t\t\n\t\t\t\t\t\t\t\t<div class=\"checkout-item-name-block\">\n\t\t\t\t\t\t\t\t\t<h2 class=\"checkout-item-name\">", "</h2>\n\t\t\t\t\t\t\t\t\t<div>", "</div>\n\t\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t\t\t\n\t\t\t\t\t\t\t\t<div class=\"checkout-item-quantity-block\">\n\t\t\t\t\t\t\t\t\t<div class=\"checkout-item-quantity-field-container\">\n<!--\t\t\t\t\t\t\t\t\t\t<div class=\"checkout-item-quantity-btn-minus no-select\"></div>-->\n\t\t\t\t\t\t\t\t\t\t<div class=\"checkout-item-quantity-field-block\">\n\t\t\t\t\t\t\t\t\t\t<input class=\"checkout-item-quantity-field\" type=\"text\" inputmode=\"numeric\" value=\"", "\">\n\t\t\t\t\t\t\t\t\t\t</div>\n<!--\t\t\t\t\t\t\t\t\t\t<div class=\"checkout-item-quantity-btn-plus no-select\"></div>-->\n\t\t\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t\t\t\t<span class=\"checkout-item-quantity-description\">\n\t\t\t\t\t\t\t\t\t\t<span class=\"checkout-item-quantity-description-text\">", "</span>\n\t\t\t\t\t\t\t\t\t\t<span class=\"checkout-item-quantity-description-price\"></span>\n\t\t\t\t\t\t\t\t\t</span>\n\t\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t</td>\n\t\t\t\t\t\t<td>\n\t\t\t\t\t\t\t<div class=\"checkout-item-price-block\">\n\t\t\t\t\t\t\t\t", "\n\t\t\t\t\t\t\t\t<span class=\"checkout-item-price\">", "</span>\n\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t</td>\n\t\t\t\t\t</tr>\n\t\t\t\t</table>\n\t\t\t"]);

	  _templateObject$1 = function _templateObject() {
	    return data;
	  };

	  return data;
	}
	var Basket = /*#__PURE__*/function (_BaseBlock) {
	  babelHelpers.inherits(Basket, _BaseBlock);

	  function Basket(form) {
	    var _this;

	    var options = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : {};
	    babelHelpers.classCallCheck(this, Basket);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(Basket).call(this, form, options));
	    _this.deleteItemHandler = _this.delete.bind(babelHelpers.assertThisInitialized(_this));
	    return _this;
	  }

	  babelHelpers.createClass(Basket, [{
	    key: "delete",
	    value: function _delete(event) {
	      var _this2 = this;

	      var div = event.target;

	      if (!main_core.Type.isDomNode(div)) {
	        return;
	      }

	      var itemId = div.getAttribute('data-item-id');
	      main_core.ajax.runAction('sale.entity.deletebasketitem', {
	        data: {
	          id: itemId
	        }
	      }).then(function (response) {
	        var redirectPath = _this2.getForm().getParameter('currentPage');

	        if (main_core.Type.isStringFilled(redirectPath)) {
	          document.location.href = redirectPath;
	        }
	      });
	    }
	  }, {
	    key: "layout",
	    value: function layout() {
	      this.getWrapper().appendChild( // workaround: Tag.render`` can't render table with dynamic rows content
	      main_core.Dom.create('table', {
	        attrs: {
	          className: 'checkout-item-list'
	        },
	        children: [].concat(babelHelpers.toConsumableArray(this.getProducts()), [this.getTotalNode()])
	      }));
	    }
	  }, {
	    key: "getBasketItems",
	    value: function getBasketItems() {
	      return this.getForm().getSchemeField('basketItems', []);
	    }
	  }, {
	    key: "getBasketPositionsCount",
	    value: function getBasketPositionsCount() {
	      return this.getBasketItems().length;
	    }
	  }, {
	    key: "getProducts",
	    value: function getProducts() {
	      var _this3 = this;

	      var itemNodes = [];
	      this.getBasketItems().forEach(function (item) {
	        var discountNode = _this3.getItemDiscountNode(item);

	        var propsNode = _this3.getItemPropsNode(item);

	        var imageSrc = item.catalogProduct.frontImage ? item.catalogProduct.frontImage.src : '';
	        var itemNode = main_core.Tag.render(_templateObject$1(), item.id, _this3.deleteItemHandler, imageSrc, main_core.Text.encode(item.name), propsNode, item.quantity, main_core.Text.encode(item.measureText), discountNode, item.sum);
	        itemNodes.push(_this3.getFirstRowFromTable(itemNode));
	      });
	      return itemNodes;
	    }
	  }, {
	    key: "getItemDiscountNode",
	    value: function getItemDiscountNode(item) {
	      if (item.sumDiscountDiff === 0) {
	        return '';
	      }

	      return main_core.Tag.render(_templateObject2(), item.sumBaseFormated, item.sumDiscountDiffFormated);
	    }
	  }, {
	    key: "getItemPropsNode",
	    value: function getItemPropsNode(item) {
	      if (item.props === 0) {
	        return '';
	      }

	      var propsItems = {};
	      var propsItemsDom = [];
	      item.props.forEach(function (i) {
	        propsItems[i] = {
	          name: i.name,
	          value: i.value
	        };
	        var domRender = main_core.Tag.render(_templateObject3(), propsItems[i].name, propsItems[i].value);
	        propsItemsDom.push(domRender);
	      });
	      return propsItemsDom;
	    }
	  }, {
	    key: "getTotalData",
	    value: function getTotalData() {
	      return this.getForm().getSchemeField('orderPriceTotal', {});
	    }
	  }, {
	    key: "getTotalNode",
	    value: function getTotalNode() {
	      var total = this.getTotalData();
	      var discountNode = this.getTotalDiscountNode(total);
	      var subTotalNode = main_core.Tag.render(_templateObject4(), main_core.Loc.getMessage('SALE_BLOCKS_BASKET_ITEMS'), discountNode, total.orderPriceFormated);
	      return this.getFirstRowFromTable(subTotalNode);
	    }
	  }, {
	    key: "getTotalDiscountNode",
	    value: function getTotalDiscountNode(total) {
	      if (total.basketPriceDiscountDiffValue === 0) {
	        return '';
	      }

	      return main_core.Tag.render(_templateObject5(), total.priceWithoutDiscount, total.basketPriceDiscountDiff);
	    } // workaround: Tag.render`` can't render tr/td nodes without table node

	  }, {
	    key: "getFirstRowFromTable",
	    value: function getFirstRowFromTable(table) {
	      return table.rows[0];
	    }
	  }]);
	  return Basket;
	}(BaseBlock);

	function _templateObject5$1() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<table>\n\t\t\t\t<tr class=\"checkout-summary-item checkout-summary-item-total\">\n\t\t\t\t\t<td>\n\t\t\t\t\t\t<div class=\"checkout-summary\">\n\t\t\t\t\t\t\t<span>", "</span>\n\t\t\t\t\t\t</div>\n\t\t\t\t\t</td>\n\t\t\t\t\t<td>\n\t\t\t\t\t\t<div class=\"checkout-item-price-block\">\n<!--\t\t\t\t\t\t\t<span class=\"checkout-item-price\">", "</span>-->\n\t\t\t\t\t\t\t<span class=\"checkout-item-price\">", "</span>\n\t\t\t\t\t\t</div>\n\t\t\t\t\t</td>\n\t\t\t\t</tr>\n\t\t\t</table>\n\t\t"]);

	  _templateObject5$1 = function _templateObject5() {
	    return data;
	  };

	  return data;
	}

	function _templateObject4$1() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<table>\n\t\t\t\t<tr class=\"checkout-summary-item\">\n\t\t\t\t\t<td>\n\t\t\t\t\t\t<div class=\"checkout-summary\">\n\t\t\t\t\t\t\t<span>", "</span>\n\t\t\t\t\t\t\t<span class=\"checkout-icon-helper\"></span>\n\t\t\t\t\t\t</div>\n\t\t\t\t\t</td>\n\t\t\t\t\t<td>\n\t\t\t\t\t\t<div class=\"checkout-item-price-block\">\n\t\t\t\t\t\t\t<span class=\"checkout-item-price\">$13.99</span>\n\t\t\t\t\t\t</div>\n\t\t\t\t\t</td>\n\t\t\t\t</tr>\n\t\t\t</table>\n\t\t"]);

	  _templateObject4$1 = function _templateObject4() {
	    return data;
	  };

	  return data;
	}

	function _templateObject3$1() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<table>\n\t\t\t\t<tr class=\"checkout-summary-item\">\n\t\t\t\t\t<td>\n\t\t\t\t\t\t<div class=\"checkout-summary\">\n\t\t\t\t\t\t\t<span>", "</span>\n<!--\t\t\t\t\t\t\t<span class=\"checkout-icon-helper\"></span>-->\n\t\t\t\t\t\t\t<div class=\"checkout-summary-item-description\">", "</div>\n\t\t\t\t\t\t</div>\n\t\t\t\t\t</td>\n\t\t\t\t\t<td>\n\t\t\t\t\t\t<div class=\"checkout-item-price-block\">\n\t\t\t\t\t\t\t<span class=\"checkout-item-price\">$10.55</span>\n\t\t\t\t\t\t</div>\n\t\t\t\t\t</td>\n\t\t\t\t</tr>\n\t\t\t</table>\n\t\t"]);

	  _templateObject3$1 = function _templateObject3() {
	    return data;
	  };

	  return data;
	}

	function _templateObject2$1() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<table>\n\t\t\t\t<tr class=\"checkout-summary-item checkout-summary-item-discount\">\n\t\t\t\t\t<td>\n\t\t\t\t\t\t<div class=\"checkout-summary\">\n\t\t\t\t\t\t\t<span>", "</span>\n<!--\t\t\t\t\t\t\t<span class=\"checkout-icon-helper\"></span>-->\n\t\t\t\t\t\t</div>\n\t\t\t\t\t</td>\n\t\t\t\t\t<td>\n\t\t\t\t\t\t<div class=\"checkout-item-price-block\">\n\t\t\t\t\t\t\t<span class=\"checkout-summary-item-price-discount\">-", "</span>\n\t\t\t\t\t\t</div>\n\t\t\t\t\t</td>\n\t\t\t\t</tr>\n\t\t\t</table>\n\t\t"]);

	  _templateObject2$1 = function _templateObject2() {
	    return data;
	  };

	  return data;
	}

	function _templateObject$2() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<table>\n\t\t\t\t<tr class=\"checkout-summary-item checkout-summary-item-subtotal\">\n\t\t\t\t\t<td>\n\t\t\t\t\t\t<div class=\"checkout-summary\">\n\t\t\t\t\t\t\t<span>", "</span>\n<!--\t\t\t\t\t\t\t<span class=\"checkout-icon-helper\"></span>-->\n\t\t\t\t\t\t</div>\n\t\t\t\t\t</td>\n\t\t\t\t\t<td>\n\t\t\t\t\t\t<div class=\"checkout-item-price-block\">\n\t\t\t\t\t\t\t<span class=\"checkout-item-price\">", "</span>\n\t\t\t\t\t\t</div>\n\t\t\t\t\t</td>\n\t\t\t\t</tr>\n\t\t\t</table>\n\t\t"]);

	  _templateObject$2 = function _templateObject() {
	    return data;
	  };

	  return data;
	}
	var Total = /*#__PURE__*/function (_BaseBlock) {
	  babelHelpers.inherits(Total, _BaseBlock);

	  function Total() {
	    babelHelpers.classCallCheck(this, Total);
	    return babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(Total).apply(this, arguments));
	  }

	  babelHelpers.createClass(Total, [{
	    key: "layout",
	    value: function layout() {
	      var total = this.getForm().getSchemeField('orderPriceTotal');
	      this.getWrapper().appendChild( // workaround: Tag.render`` can't render table with dynamic rows content
	      main_core.Dom.create('table', {
	        attrs: {
	          className: 'checkout-summary-list'
	        },
	        children: [this.getBasketTotalNode(total), this.getDiscountNode(total), this.getShippingNode(total), this.getTaxesNode(total), this.getSummaryNode(total)]
	      }));
	    }
	  }, {
	    key: "getBasketPositionsCount",
	    value: function getBasketPositionsCount() {
	      return this.getForm().getSchemeField('basketItems', []).length;
	    }
	  }, {
	    key: "getBasketTotalNode",
	    value: function getBasketTotalNode(total) {
	      return this.getFirstRowFromTable(main_core.Tag.render(_templateObject$2(), main_core.Loc.getMessage('SALE_BLOCKS_TOTAL_ITEMS'), total.priceWithoutDiscount));
	    }
	  }, {
	    key: "getDiscountNode",
	    value: function getDiscountNode(total) {
	      if (total.discountPrice === 0) {
	        return '';
	      }

	      return this.getFirstRowFromTable(main_core.Tag.render(_templateObject2$1(), main_core.Loc.getMessage('SALE_BLOCKS_TOTAL_DISCOUNT'), total.discountPriceFormated));
	    }
	  }, {
	    key: "getShippingNode",
	    value: function getShippingNode(total) {
	      {
	        return '';
	      }

	      return this.getFirstRowFromTable(main_core.Tag.render(_templateObject3$1(), main_core.Loc.getMessage('SALE_BLOCKS_TOTAL_NAME'), main_core.Loc.getMessage('SALE_BLOCKS_TOTAL_DESCRIPTION')));
	    }
	  }, {
	    key: "getTaxesNode",
	    value: function getTaxesNode(total) {
	      {
	        return '';
	      }

	      return this.getFirstRowFromTable(main_core.Tag.render(_templateObject4$1(), main_core.Loc.getMessage('SALE_BLOCKS_TOTAL_TAXES')));
	    }
	  }, {
	    key: "getSummaryNode",
	    value: function getSummaryNode(total) {
	      return this.getFirstRowFromTable(main_core.Tag.render(_templateObject5$1(), main_core.Loc.getMessage('SALE_BLOCKS_TOTAL_TOTAL'), total.orderTotalPriceFormated, total.orderPriceFormated));
	    } // workaround: Tag.render`` can't render tr/td nodes without table node

	  }, {
	    key: "getFirstRowFromTable",
	    value: function getFirstRowFromTable(table) {
	      return table.rows[0];
	    }
	  }]);
	  return Total;
	}(BaseBlock);

	var Loader = /*#__PURE__*/function (_BaseBlock) {
	  babelHelpers.inherits(Loader, _BaseBlock);

	  function Loader() {
	    babelHelpers.classCallCheck(this, Loader);
	    return babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(Loader).apply(this, arguments));
	  }

	  babelHelpers.createClass(Loader, [{
	    key: "layout",
	    value: function layout() {
	      this.getWrapper();
	      this.clearLayout();
	    }
	  }]);
	  return Loader;
	}(BaseBlock);

	function _templateObject2$2() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<div class=\"checkout-checkout-method-list\">\n\t\t\t\t", "\n\t\t\t</div>\n\t\t"]);

	  _templateObject2$2 = function _templateObject2() {
	    return data;
	  };

	  return data;
	}

	function _templateObject$3() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"checkout-checkout-method\">\n\t\t\t\t\t<div class=\"checkout-checkout-method-image-block\">\n\t\t\t\t\t\t<img src=\"", "\" alt=\"\" class=\"checkout-checkout-method-img\">\n\t\t\t\t\t</div>\n\t\t\t\t\t<div class=\"checkout-checkout-method-name-block\">\n\t\t\t\t\t\t<div class=\"checkout-checkout-method-name\">", "</div>\n\t\t\t\t\t\t<div class=\"checkout-checkout-method-description\">", "</div>\n\t\t\t\t\t</div>\n\t\t\t\t\t<div class=\"checkout-checkout-method-btn-block\">\n\t\t\t\t\t\t<button \n\t\t\t\t\t\t\tclass=\"btn btn-primary checkout-checkout-btn btn-sm rounded-pill\"\n\t\t\t\t\t\t\tdata-paysystem-id=\"", "\"\n\t\t\t\t\t\t\tonclick=\"", "\"\n\t\t\t\t\t\t>Checkout</button>\n<!--\t\t\t\t\t\t<button class=\"checkout-checkout-btn checkout-checkout-btn-selected btn btn-sm rounded-pill\">Selected</button>-->\n\t\t\t\t\t</div>\n\t\t\t\t</div>\n\t\t\t"]);

	  _templateObject$3 = function _templateObject() {
	    return data;
	  };

	  return data;
	}
	var Payments = /*#__PURE__*/function (_BaseBlock) {
	  babelHelpers.inherits(Payments, _BaseBlock);

	  function Payments() {
	    babelHelpers.classCallCheck(this, Payments);
	    return babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(Payments).apply(this, arguments));
	  }

	  babelHelpers.createClass(Payments, [{
	    key: "refreshLayout",
	    value: function refreshLayout() {
	      var forceLayout = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : false;
	      var mode;
	      var formStage = this.getForm().getStage();
	      var blockStage = this.getStage();

	      if (main_core.Type.isPlainObject(blockStage)) {
	        var viewStage = blockStage.view,
	            editStage = blockStage.edit,
	            hideStage = blockStage.hide;
	        var currentStage = 0;

	        while (currentStage <= formStage) {
	          if (currentStage === hideStage) {
	            mode = undefined;
	          } else if (currentStage === editStage) {
	            mode = BaseBlock.EDIT_MODE;
	          } else if (currentStage === viewStage) {
	            mode = BaseBlock.VIEW_MODE;
	          }

	          currentStage++;
	        }
	      } else if (main_core.Type.isNumber(blockStage)) {
	        if (blockStage <= formStage) {
	          mode = BaseBlock.EDIT_MODE;
	        }
	      }

	      this.clearLayout();

	      if (mode || forceLayout) {
	        if (mode) {
	          this.setMode(mode);
	        }

	        this.layout();
	      }
	    }
	  }, {
	    key: "layout",
	    value: function layout() {
	      var access = this.getForm().getSchemeField('hash');
	      var paySystemReturnUrl = this.getForm().getParameter('paySystemReturnUrl');
	      var payments = this.getForm().getField('payments');
	      var paymentId = 0;
	      Object.keys(payments).forEach(function (id) {
	        paymentId = id;
	        return false;
	      });
	      main_core.ajax.runAction('sale.entity.paymentpay', {
	        data: {
	          fields: {
	            paymentId: paymentId,
	            accessCode: access,
	            returnUrl: paySystemReturnUrl
	          }
	        }
	      }).then(this.getPaySystemsList.bind(this)); //${this.getPaySystemNodes()}
	    }
	  }, {
	    key: "getPaySystemsList",
	    value: function getPaySystemsList(response) {
	      var _this = this;

	      var wrapper = this.getWrapper();

	      if (BX.type.isPlainObject(response.data) && BX.type.isNotEmptyString(response.data.html)) {
	        BX.html(wrapper, response.data.html);
	        BX.addCustomEvent('onChangePaySystems', function () {
	          _this.getForm().refreshLayout();
	        });
	      }
	    }
	  }, {
	    key: "getPaySystems",
	    value: function getPaySystems() {
	      return this.getForm().getSchemeField('paySystems', []);
	    }
	  }, {
	    key: "getPaySystemNodes",
	    value: function getPaySystemNodes() {
	      var _this2 = this;

	      var paySystemNodes = [];
	      this.getPaySystems().forEach(function (item) {
	        paySystemNodes.push(main_core.Tag.render(_templateObject$3(), item.logotipSrc, main_core.Text.encode(item.name), main_core.Text.encode(item.description), item.id, _this2.handleCheckoutClick.bind(_this2)));
	      });
	      return main_core.Tag.render(_templateObject2$2(), paySystemNodes);
	    }
	  }, {
	    key: "handleCheckoutClick",
	    value: function handleCheckoutClick(event) {
	      var paySystemId = main_core.Text.toNumber(event.target.getAttribute('data-paysystem-id'));
	      this.getForm().setFieldNoDemand('paySystemId', paySystemId);
	      event.target.setAttribute('disabled', 'disabled');
	      main_core.Dom.addClass(event.target, 'checkout-checkout-btn-selected');
	      event.target.innerHTML = '<span class="spinner-border spinner-border-sm mr-2"></span>' + event.target.innerHTML;
	      var payments = this.getForm().getField('payments');
	      var paymentId = 0;
	      Object.keys(payments).forEach(function (id) {
	        paymentId = id;
	        return false;
	      });
	      main_core.ajax.runAction('sale.entity.paymentpay', {
	        data: {
	          fields: {
	            ID: paymentId
	          }
	        }
	      }).then(function (response) {
	        if (BX.type.isPlainObject(response.data) && BX.type.isNotEmptyString(response.data.html)) {
	          BX.html(event.target, response.data.html);
	        }
	      });
	    }
	  }]);
	  return Payments;
	}(BaseBlock);

	function _templateObject4$2() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"checkout-btn-container\">\n\t\t\t\t\t<button\n\t\t\t\t\t\tclass=\"btn btn-primary product-item-detail-buy-button btn-md rounded-pill\"\n\t\t\t\t\t\tonclick=\"", "\"\n\t\t\t\t\t\t>", "</button>\n\t\t\t\t</div>\n\t\t\t"]);

	  _templateObject4$2 = function _templateObject4() {
	    return data;
	  };

	  return data;
	}

	function _templateObject3$2() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t \t<label class=\"checkout-agreement-container\">\n\t\t \t\t<div class=\"checkout-agreement-block\">\n\t\t \t\t\t<input type=\"checkbox\" class=\"checkout-agreement-input\" checked=\"checked\">\n\t\t \t\t</div>\n\t\t \t\t<div class=\"checkout-agreement-block\">\n\t\t \t\t\t<div class=\"checkout-agreement-text\">", "</div>\n\t\t \t\t</div>\n\t\t \t</label>\n\t\t "]);

	  _templateObject3$2 = function _templateObject3() {
	    return data;
	  };

	  return data;
	}

	function _templateObject2$3() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t\t\t<div>\n\t\t\t\t\t\t\t", "\n\t\t\t\t\t\t\t", "\n\t\t\t\t\t\t</div>\n\t\t\t\t\t"]);

	  _templateObject2$3 = function _templateObject2() {
	    return data;
	  };

	  return data;
	}

	function _templateObject$4() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t\t\t<div>\n\t\t\t\t\t\t\t", "\n\t\t\t\t\t\t\t", "\n\t\t\t\t\t\t</div>\n\t\t\t\t\t"]);

	  _templateObject$4 = function _templateObject() {
	    return data;
	  };

	  return data;
	}
	var PlaceOrder = /*#__PURE__*/function (_BaseBlock) {
	  babelHelpers.inherits(PlaceOrder, _BaseBlock);

	  function PlaceOrder(form) {
	    var _this;

	    var options = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : {};
	    babelHelpers.classCallCheck(this, PlaceOrder);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(PlaceOrder).call(this, form, options));
	    _this.saveOrderHandler = _this.saveOrder.bind(babelHelpers.assertThisInitialized(_this));

	    var properties = _this.getForm().getParameter('userConsentPropertyData');

	    _this.userConsent = {
	      id: 1,
	      title: main_core.Loc.getMessage('SALE_BLOCKS_PLACE_ORDER_NOW'),
	      isLoaded: 'Y',
	      autoSave: 'Y',
	      isChecked: 'Y',
	      submitEventName: 'onUserConsent',
	      fields: main_core.Type.isArrayFilled(properties) ? JSON.stringify(properties) : []
	    };
	    _this.isAllowedSubmitting = _this.userConsent.isChecked === 'Y';
	    return _this;
	  }

	  babelHelpers.createClass(PlaceOrder, [{
	    key: "layout_",
	    value: function layout_() {
	      this.getWrapper().appendChild(main_core.Tag.render(_templateObject$4(), this.getConsent(), this.getSaveButton()));
	    }
	  }, {
	    key: "layout",
	    value: function layout() {
	      var _this2 = this;

	      var wrapper = this.getWrapper();
	      main_core.ajax.runAction('sale.entity.userconsentrequest', {
	        data: {
	          fields: this.userConsent
	        }
	      }).then(function (response) {
	        if (BX.type.isPlainObject(response.data) && BX.type.isNotEmptyString(response.data.html)) {
	          var consent = response.data.html;
	          wrapper.appendChild(main_core.Tag.render(_templateObject2$3(), consent, _this2.getSaveButton()));

	          if (BX.UserConsent !== undefined) {
	            var control = BX.UserConsent.load(wrapper);
	            BX.addCustomEvent(control, BX.UserConsent.events.accepted, function () {
	              return _this2.isAllowedSubmitting = true;
	            });
	            BX.addCustomEvent(control, BX.UserConsent.events.refused, function () {
	              return _this2.isAllowedSubmitting = false;
	            });
	          }
	        }
	      });
	    }
	  }, {
	    key: "getConsent",
	    value: function getConsent() {
	      // todo replace with existing consent api
	      return main_core.Tag.render(_templateObject3$2(), main_core.Loc.getMessage('SALE_BLOCKS_PLACE_ORDER_TEXT'));
	    }
	  }, {
	    key: "getSaveButton",
	    value: function getSaveButton() {
	      var _this3 = this;

	      return this.getCache().remember('save-button', function () {
	        return main_core.Tag.render(_templateObject4$2(), _this3.saveOrderHandler, main_core.Loc.getMessage('SALE_BLOCKS_PLACE_ORDER_NOW'));
	      });
	    }
	  }, {
	    key: "saveOrder",
	    value: function saveOrder() {
	      BX.onCustomEvent(this.userConsent.submitEventName, []);

	      if (this.isAllowedSubmitting) {
	        this.getSaveButton().disabled = true;
	        this.getForm().requestSave();
	      }
	    }
	  }]);
	  return PlaceOrder;
	}(BaseBlock);

	function _templateObject3$3() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t\t<input \n\t\t\t\t\t\ttype=\"", "\"\n\t\t\t\t\t\tclass=\"form-control form-control-lg\"\n\t\t\t\t\t\tplaceholder=\"", "\"\n\t\t\t\t\t\tvalue=\"", "\"\n\t\t\t\t\t\tdata-property-id=\"", "\"\n\t\t\t\t\t\tonchange=\"", "\"\n\t\t\t\t\t\tonfocusout=\"", "\">\n\t\t\t"]);

	  _templateObject3$3 = function _templateObject3() {
	    return data;
	  };

	  return data;
	}

	function _templateObject2$4() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<div class=\"checkout-form-container\">\n\t\t\t\t<div class=\"checkout-form-header\">\n\t\t\t\t\t<div class=\"checkout-form-title\">", "</div>\n\t\t\t\t</div>\n\t\t\t\t<div class=\"checkout-form-block\">\n\t\t\t\t\t<form>\n\t\t\t\t\t\t<div class=\"form-group\">\n\t\t\t\t\t\t\t", "\n\t\t\t\t\t\t</div>\n\t\t\t\t\t</form>\n\t\t\t\t</div>\n\t\t\t</div>\n\t\t"]);

	  _templateObject2$4 = function _templateObject2() {
	    return data;
	  };

	  return data;
	}

	function _templateObject$5() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<div style=\"border-bottom: 1px solid #cecece;\">\n\t\t\t\t<tr class=\"checkout-summary-item\">\n\t\t\t\t\t<td colspan=\"2\">\n\t\t\t\t\t\t<div class=\"checkout-item-personal-order-info\">\n\t\t\t\t\t\t\t<div class=\"checkout-item-personal-order-payment\">\n\t\t\t\t\t\t\t\t<strong>", "</strong>\n\t\t\t\t\t\t\t\t<div>", "</div>\n\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t\t<div class=\"checkout-item-personal-order-shipping\">\n\t\t\t\t\t\t\t\t<strong>", "</strong>\n\t\t\t\t\t\t\t\t<div>", "</div>\n\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t</div>\n\t\t\t\t\t</td>\n\t\t\t\t</tr>\n\t\t\t</div>\n\t\t"]);

	  _templateObject$5 = function _templateObject() {
	    return data;
	  };

	  return data;
	}
	var Properties = /*#__PURE__*/function (_BaseBlock) {
	  babelHelpers.inherits(Properties, _BaseBlock);

	  function Properties() {
	    babelHelpers.classCallCheck(this, Properties);
	    return babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(Properties).apply(this, arguments));
	  }

	  babelHelpers.createClass(Properties, [{
	    key: "layout",
	    value: function layout() {
	      this.getWrapper().appendChild(this.getMode() === BaseBlock.VIEW_MODE ? this.getViewLayout() : this.getEditLayout());
	    }
	  }, {
	    key: "getPropertiesShort",
	    value: function getPropertiesShort() {
	      var propertyValues = this.getForm().getField('properties');
	      var properties = [];

	      for (var propertyId in propertyValues) {
	        if (propertyValues.hasOwnProperty(propertyId) && main_core.Type.isStringFilled(propertyValues[propertyId])) {
	          properties.push(propertyValues[propertyId]);
	        }
	      }

	      return properties.join(', ');
	    }
	  }, {
	    key: "getViewLayout",
	    value: function getViewLayout() {
	      var orderNumber = this.getForm().getSchemeField('accountNumber');
	      var propertiesInfo = this.getPropertiesShort();
	      return main_core.Tag.render(_templateObject$5(), main_core.Loc.getMessage('SALE_BLOCKS_PROPERTIES_ORDER_TITLE').replace('#ORDER_NUMBER#', orderNumber), propertiesInfo, main_core.Loc.getMessage('SALE_BLOCKS_PROPERTIES_SHIPPING_METHOD'), main_core.Loc.getMessage('SALE_BLOCKS_PROPERTIES_SHIPPING_METHOD_DESCRIPTION'));
	    }
	  }, {
	    key: "getEditLayout",
	    value: function getEditLayout() {
	      return main_core.Tag.render(_templateObject2$4(), main_core.Loc.getMessage('SALE_BLOCKS_PROPERTIES_TITLE'), this.getProperties());
	    }
	  }, {
	    key: "getProperties",
	    value: function getProperties() {
	      var _this = this;

	      var properties = [];
	      this.getForm().getSchemeField('properties', []).forEach(function (item) {
	        if (item.type === 'STRING') {
	          var value = _this.getForm().getField('properties', {})[item.id] || '';
	          var type = item.isPhone === 'Y' ? 'tel' : 'text';
	          var propertyNode = main_core.Tag.render(_templateObject3$3(), type, main_core.Text.encode(item.name), main_core.Text.encode(value), item.id, _this.onChangeHandler.bind(_this), _this.onFocusOutHandler.bind(_this));
	          BX.addCustomEvent("BX.Sale.Checkout.Property.Error:onSave_" + item.id, function () {
	            main_core.Dom.addClass(propertyNode, 'border-danger');
	            main_core.Dom.removeClass(propertyNode, 'border-success');
	          });
	          properties.push(propertyNode);
	        }
	      });
	      return properties;
	    }
	  }, {
	    key: "onChangeHandler",
	    value: function onChangeHandler(event) {
	      var input = event.target;

	      if (!main_core.Type.isDomNode(input)) {
	        return;
	      }

	      var propertyId = input.getAttribute('data-property-id');
	      var properties = this.getForm().getField('properties');
	      properties[propertyId] = input.value;
	      this.getForm().setFieldNoDemand('properties', properties);
	    }
	  }, {
	    key: "onFocusOutHandler",
	    value: function onFocusOutHandler(event) {
	      var input = event.target;

	      if (!main_core.Type.isDomNode(input)) {
	        return;
	      }

	      if (main_core.Type.isStringFilled(input.value)) {
	        main_core.Dom.addClass(input, 'border-success');
	        main_core.Dom.removeClass(input, 'border-danger');
	      } else {
	        main_core.Dom.addClass(input, 'border-danger');
	        main_core.Dom.removeClass(input, 'border-success');
	      }
	    }
	  }]);
	  return Properties;
	}(BaseBlock);

	function _templateObject8() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<div class=\"checkout-order-status-btn-container\">\n\t\t\t\t<button\n\t\t\t\t\tclass=\"btn btn-checkout-order-status btn-md rounded-pill\"\n\t\t\t\t\tonclick=\"", "\"\n\t\t\t\t>", "</button>\n\t\t\t</div>\n\t\t"]);

	  _templateObject8 = function _templateObject8() {
	    return data;
	  };

	  return data;
	}

	function _templateObject7() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<div class=\"checkout-order-status-btn-container\">\n\t\t\t\t<button\n\t\t\t\t\tclass=\"btn btn-checkout-order-status btn-md rounded-pill\"\n\t\t\t\t\tonclick=\"", "\"\n\t\t\t\t>", "</button>\n\t\t\t</div>\n\t\t"]);

	  _templateObject7 = function _templateObject7() {
	    return data;
	  };

	  return data;
	}

	function _templateObject6() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<div class=\"checkout-order-section-separator\">", "</div>\n\t\t"]);

	  _templateObject6 = function _templateObject6() {
	    return data;
	  };

	  return data;
	}

	function _templateObject5$2() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<div class=\"checkout-order-common-container\">\n\t\t\t\t<div class=\"checkout-order-common-row\">\n\t\t\t\t\t<svg class=\"checkout-order-common-row-icon\" width=\"26\" height=\"27\" viewBox=\"0 0 26 27\" fill=\"none\" xmlns=\"http://www.w3.org/2000/svg\">\n\t\t\t\t\t\t<path fill-rule=\"evenodd\" clip-rule=\"evenodd\" d=\"M12.9124 26.6569C20.0093 26.6569 25.7624 20.9038 25.7624 13.807C25.7624 6.71014 20.0093 0.957031 12.9124 0.957031C5.81561 0.957031 0.0625 6.71014 0.0625 13.807C0.0625 20.9038 5.81561 26.6569 12.9124 26.6569Z\" fill=\"white\"/>\n\t\t\t\t\t\t<path fill-rule=\"evenodd\" clip-rule=\"evenodd\" d=\"M10.8218 19.498L5.72461 14.5304L7.50861 12.7918L10.8218 16.0207L18.3182 8.71484L20.1022 10.4535L10.8218 19.498Z\" fill=\"#65A90F\"/>\n\t\t\t\t\t</svg>\n\t\t\t\t\t<div>", "</div>\n\t\t\t\t</div>\n\t\t\t\t<div class=\"checkout-order-common-row\">\n\t\t\t\t\t<div>", "</div>\n\t\t\t\t</div>\n\t\t\t</div>\n\t\t"]);

	  _templateObject5$2 = function _templateObject5() {
	    return data;
	  };

	  return data;
	}

	function _templateObject4$3() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<div class=\"checkout-order-common-container\">\n\t\t\t\t<div class=\"checkout-order-common-row\">\n\t\t\t\t\t<svg class=\"checkout-order-common-row-icon\" width=\"26\" height=\"27\" viewBox=\"0 0 26 27\" fill=\"none\" xmlns=\"http://www.w3.org/2000/svg\">\n\t\t\t\t\t\t<path fill-rule=\"evenodd\" clip-rule=\"evenodd\" d=\"M12.9124 26.6569C20.0093 26.6569 25.7624 20.9038 25.7624 13.807C25.7624 6.71014 20.0093 0.957031 12.9124 0.957031C5.81561 0.957031 0.0625 6.71014 0.0625 13.807C0.0625 20.9038 5.81561 26.6569 12.9124 26.6569Z\" fill=\"white\"/>\n\t\t\t\t\t\t<path fill-rule=\"evenodd\" clip-rule=\"evenodd\" d=\"M10.8218 19.498L5.72461 14.5304L7.50861 12.7918L10.8218 16.0207L18.3182 8.71484L20.1022 10.4535L10.8218 19.498Z\" fill=\"#65A90F\"/>\n\t\t\t\t\t</svg>\n\t\t\t\t\t<div>", "</div>\n\t\t\t\t</div>\n\t\t\t</div>\n\t\t"]);

	  _templateObject4$3 = function _templateObject4() {
	    return data;
	  };

	  return data;
	}

	function _templateObject3$4() {
	  var data = babelHelpers.taggedTemplateLiteral(["\t\t\t\n\t\t\t<div class=\"checkout-order-status-text\">\n\t\t\t\t<strong>", " #", "</strong>\n\t\t\t</div>\n\t\t"]);

	  _templateObject3$4 = function _templateObject3() {
	    return data;
	  };

	  return data;
	}

	function _templateObject2$5() {
	  var data = babelHelpers.taggedTemplateLiteral(["\t\t\t\n\t\t\t<div class=\"checkout-order-status-text\">\n\t\t\t\t<strong>", " #", "</strong> ", "\n\t\t\t</div>\n\t\t"]);

	  _templateObject2$5 = function _templateObject2() {
	    return data;
	  };

	  return data;
	}

	function _templateObject$6() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<div class=\"checkout-order-status-successful\">\n\t\t\t\t<svg class=\"checkout-order-status-icon\" width=\"105\" height=\"106\" viewBox=\"0 0 105 106\" fill=\"none\" xmlns=\"http://www.w3.org/2000/svg\">\n\t\t\t\t\t<path opacity=\"0.6\" stroke=\"#fff\" stroke-width=\"3\" fill-rule=\"evenodd\" clip-rule=\"evenodd\" d=\"M52.5 104C80.6665 104 103.5 81.1665 103.5 53C103.5 24.8335 80.6665 2 52.5 2C24.3335 2 1.5 24.8335 1.5 53C1.5 81.1665 24.3335 104 52.5 104Z\"/>\n\t\t\t\t\t<path fill=\"#fff\" fill-rule=\"evenodd\" clip-rule=\"evenodd\" d=\"M45.517 72L28.5 55.4156L34.4559 49.611L45.517 60.3909L70.5441 36L76.5 41.8046L45.517 72Z\"/>\n\t\t\t\t</svg>\t\t\t\t\n\t\t\t\t", "\n\t\t\t\t", "\n\t\t\t</div>\n\t\t"]);

	  _templateObject$6 = function _templateObject() {
	    return data;
	  };

	  return data;
	}
	var Success = /*#__PURE__*/function (_BaseBlock) {
	  babelHelpers.inherits(Success, _BaseBlock);

	  function Success() {
	    babelHelpers.classCallCheck(this, Success);
	    return babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(Success).apply(this, arguments));
	  }

	  babelHelpers.createClass(Success, [{
	    key: "layout",
	    value: function layout() {
	      this.getWrapper().appendChild(main_core.Tag.render(_templateObject$6(), this.showBeforePayment(), this.showAfterPayment()));
	      main_core.Dom.addClass(document.body, 'container-overflow-hidden');
	    }
	  }, {
	    key: "clearLayout",
	    value: function clearLayout() {
	      babelHelpers.get(babelHelpers.getPrototypeOf(Success.prototype), "clearLayout", this).call(this);
	      main_core.Dom.removeClass(document.body, 'container-overflow-hidden');
	    }
	  }, {
	    key: "showOrderStatus",
	    value: function showOrderStatus() {
	      var orderNumber = this.getForm().getSchemeField('ACCOUNT_NUMBER');
	      return main_core.Tag.render(_templateObject2$5(), main_core.Loc.getMessage('SALE_BLOCKS_SUCCESS_ORDER'), main_core.Text.encode(orderNumber), main_core.Loc.getMessage('SALE_BLOCKS_SUCCESS_ORDER_CREATED'));
	    }
	  }, {
	    key: "showPaymentStatus",
	    value: function showPaymentStatus() {
	      var orderNumber = this.getForm().getSchemeField('ACCOUNT_NUMBER');
	      return main_core.Tag.render(_templateObject3$4(), main_core.Loc.getMessage('SALE_BLOCKS_SUCCESS_ORDER'), main_core.Text.encode(orderNumber));
	    }
	  }, {
	    key: "showManagerWillCall",
	    value: function showManagerWillCall() {
	      return main_core.Tag.render(_templateObject4$3(), main_core.Loc.getMessage('SALE_BLOCKS_SUCCESS_CALL'));
	    }
	  }, {
	    key: "showPaymentSum",
	    value: function showPaymentSum() {
	      var total = this.getForm().getSchemeField('ORDER_PRICE_TOTAL');
	      return main_core.Tag.render(_templateObject5$2(), main_core.Loc.getMessage('SALE_BLOCKS_SUCCESS_TO_PAID').replace('#PAID#', total.orderTotalPriceFormated), main_core.Loc.getMessage('SALE_BLOCKS_SUCCESS_DELIVERY'));
	    }
	  }, {
	    key: "showSeparator",
	    value: function showSeparator() {
	      return main_core.Tag.render(_templateObject6(), main_core.Loc.getMessage('SALE_BLOCKS_SUCCESS_OR'));
	    }
	  }, {
	    key: "showContinueProcessing",
	    value: function showContinueProcessing() {
	      return main_core.Tag.render(_templateObject7(), this.onContinueProcessingHandler.bind(this), main_core.Loc.getMessage('SALE_BLOCKS_SUCCESS_CHECKOUT'));
	    }
	  }, {
	    key: "onContinueProcessingHandler",
	    value: function onContinueProcessingHandler(event) {
	      // todo setField()
	      var url = this.getForm().parameters['paySystemReturnUrl']; //todo

	      url = this.addLinkParam(url, 'orderId', this.getForm().getSchemeField('orderId'));
	      url = this.addLinkParam(url, 'access', this.getForm().getSchemeField('hash'));
	      this.getForm().parameters['paySystemReturnUrl'] = url; // todo refresh layout with paysystems

	      this.getForm().refreshLayout(); // todo

	      delete BX.UserConsent;
	      this.pushState({
	        orderId: this.getForm().getSchemeField('orderId'),
	        access: this.getForm().getSchemeField('hash')
	      });
	    }
	  }, {
	    key: "getCurrentUrl",
	    value: function getCurrentUrl() {
	      return window.location.protocol + "//" + window.location.hostname + (window.location.port != '' ? ':' + window.location.port : '') + window.location.pathname + window.location.search;
	    }
	  }, {
	    key: "addLinkParam",
	    value: function addLinkParam(link, name, value) {
	      if (!link.length) {
	        return '?' + name + '=' + value;
	      }

	      link = BX.Uri.removeParam(link, name);

	      if (link.indexOf('?') != -1) {
	        return link + '&' + name + '=' + value;
	      }

	      return link + '?' + name + '=' + value;
	    }
	  }, {
	    key: "pushState",
	    value: function pushState(params) {
	      var url = '';
	      url = this.getCurrentUrl();
	      url = this.addLinkParam(url, 'orderId', params.orderId);
	      url = this.addLinkParam(url, 'access', params.access);
	      window.history.pushState(null, null, url);
	    }
	  }, {
	    key: "showContinueShopping",
	    value: function showContinueShopping() {
	      return main_core.Tag.render(_templateObject8(), this.onContinueShoppingHandler.bind(this), main_core.Loc.getMessage('SALE_BLOCKS_SUCCESS_CONTINUE'));
	    }
	  }, {
	    key: "onContinueShoppingHandler",
	    value: function onContinueShoppingHandler(event) {
	      event.target.disable = true;
	      var redirectPath = this.getForm().getParameter('emptyBasketHintPath');

	      if (main_core.Type.isStringFilled(redirectPath)) {
	        document.location.href = redirectPath;
	      }
	    }
	  }, {
	    key: "isContinueProcessingEnabled",
	    value: function isContinueProcessingEnabled() {
	      return this.getForm().getParameter('showContinueProcessing', false);
	    }
	  }, {
	    key: "isPaymentSelected",
	    value: function isPaymentSelected() {
	      return this.getForm().getField('paySystemId', 0) > 0;
	    }
	  }, {
	    key: "hasPaySystems",
	    value: function hasPaySystems() {
	      return this.getForm().getSchemeField('paySystems', []).length > 0;
	    }
	  }, {
	    key: "showBeforePayment",
	    value: function showBeforePayment() {
	      if (!this.isContinueProcessingEnabled || this.isPaymentSelected()) {
	        return '';
	      }

	      if (this.hasPaySystems()) {
	        return [this.showOrderStatus(), this.showManagerWillCall(), this.showSeparator(), this.showContinueProcessing()];
	      } else {
	        return [this.showOrderStatus(), this.showManagerWillCall()];
	      }
	    }
	  }, {
	    key: "showAfterPayment",
	    value: function showAfterPayment() {
	      if (!this.isPaymentSelected()) {
	        return '';
	      }

	      return [this.showPaymentStatus(), this.showPaymentSum(), this.showContinueShopping()];
	    }
	  }]);
	  return Success;
	}(BaseBlock);

	var BlockType = function BlockType() {
	  babelHelpers.classCallCheck(this, BlockType);
	};
	babelHelpers.defineProperty(BlockType, "BASKET", 'basket');
	babelHelpers.defineProperty(BlockType, "LOADER", 'loader');
	babelHelpers.defineProperty(BlockType, "PAYMENTS", 'payments');
	babelHelpers.defineProperty(BlockType, "PLACE_ORDER", 'place-order');
	babelHelpers.defineProperty(BlockType, "PROPERTIES", 'properties');
	babelHelpers.defineProperty(BlockType, "SUCCESS", 'success');
	babelHelpers.defineProperty(BlockType, "TOTAL", 'total');
	var blocks = [{
	  type: BlockType.BASKET,
	  entity: Basket
	}, {
	  type: BlockType.LOADER,
	  entity: Loader
	}, {
	  type: BlockType.PAYMENTS,
	  entity: Payments
	}, {
	  type: BlockType.PLACE_ORDER,
	  entity: PlaceOrder
	}, {
	  type: BlockType.PROPERTIES,
	  entity: Properties
	}, {
	  type: BlockType.SUCCESS,
	  entity: Success
	}, {
	  type: BlockType.TOTAL,
	  entity: Total
	}];

	var Factory = /*#__PURE__*/function (_EventEmitter) {
	  babelHelpers.inherits(Factory, _EventEmitter);

	  function Factory() {
	    babelHelpers.classCallCheck(this, Factory);
	    return babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(Factory).apply(this, arguments));
	  }

	  babelHelpers.createClass(Factory, null, [{
	    key: "create",
	    value: function create(type, form) {
	      var options = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : {};
	      this.emit('BX.Sale.Checkout.Factory:onBeforeCreateBlock', blocks);
	      var entity = blocks.find(function (item) {
	        return item.type === type;
	      })['entity'];

	      if (!entity) {
	        var eventData = {};
	        this.emit('BX.Sale.Checkout.Factory:onCreate', eventData);

	        if (eventData[type]) {
	          entity = eventData[type];
	        }
	      }

	      if (main_core.Type.isFunction(entity)) {
	        return new entity(form, options);
	      }

	      return null;
	    }
	  }]);
	  return Factory;
	}(main_core_events.EventEmitter);

	function _createForOfIteratorHelper$1(o, allowArrayLike) { var it; if (typeof Symbol === "undefined" || o[Symbol.iterator] == null) { if (Array.isArray(o) || (it = _unsupportedIterableToArray$1(o)) || allowArrayLike && o && typeof o.length === "number") { if (it) o = it; var i = 0; var F = function F() {}; return { s: F, n: function n() { if (i >= o.length) return { done: true }; return { done: false, value: o[i++] }; }, e: function e(_e) { throw _e; }, f: F }; } throw new TypeError("Invalid attempt to iterate non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); } var normalCompletion = true, didErr = false, err; return { s: function s() { it = o[Symbol.iterator](); }, n: function n() { var step = it.next(); normalCompletion = step.done; return step; }, e: function e(_e2) { didErr = true; err = _e2; }, f: function f() { try { if (!normalCompletion && it.return != null) it.return(); } finally { if (didErr) throw err; } } }; }

	function _unsupportedIterableToArray$1(o, minLen) { if (!o) return; if (typeof o === "string") return _arrayLikeToArray$1(o, minLen); var n = Object.prototype.toString.call(o).slice(8, -1); if (n === "Object" && o.constructor) n = o.constructor.name; if (n === "Map" || n === "Set") return Array.from(o); if (n === "Arguments" || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(n)) return _arrayLikeToArray$1(o, minLen); }

	function _arrayLikeToArray$1(arr, len) { if (len == null || len > arr.length) len = arr.length; for (var i = 0, arr2 = new Array(len); i < len; i++) { arr2[i] = arr[i]; } return arr2; }

	var Scheme = /*#__PURE__*/function () {
	  function Scheme(fields) {
	    babelHelpers.classCallCheck(this, Scheme);
	    babelHelpers.defineProperty(this, "fields", null);
	    this.initFields(fields);
	  }

	  babelHelpers.createClass(Scheme, [{
	    key: "initFields",
	    value: function initFields(fields) {
	      this.fields = new Map(Object.entries(fields));
	    }
	  }, {
	    key: "hasField",
	    value: function hasField(name) {
	      return this.fields.has(name);
	    }
	  }, {
	    key: "getFields",
	    value: function getFields() {
	      var fields = {};

	      var _iterator = _createForOfIteratorHelper$1(this.fields),
	          _step;

	      try {
	        for (_iterator.s(); !(_step = _iterator.n()).done;) {
	          var _step$value = babelHelpers.slicedToArray(_step.value, 2),
	              k = _step$value[0],
	              v = _step$value[1];

	          fields[k] = v;
	        }
	      } catch (err) {
	        _iterator.e(err);
	      } finally {
	        _iterator.f();
	      }

	      return fields;
	    }
	  }, {
	    key: "getField",
	    value: function getField(name) {
	      var defaultValue = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : null;
	      return this.fields.has(name) ? this.fields.get(name) : defaultValue;
	    }
	  }]);
	  return Scheme;
	}();

	var Stage = function Stage() {
	  babelHelpers.classCallCheck(this, Stage);
	};
	babelHelpers.defineProperty(Stage, "INITIAL", 1);
	babelHelpers.defineProperty(Stage, "VIEW", 2);

	var Form = /*#__PURE__*/function (_EventEmitter) {
	  babelHelpers.inherits(Form, _EventEmitter);

	  function Form(model, scheme) {
	    var _this;

	    var parameters = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : {};
	    babelHelpers.classCallCheck(this, Form);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(Form).call(this));
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "model", null);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "scheme", null);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "parameters", null);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "stage", null);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "container", null);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "blocks", null);

	    _this.setEventNamespace('BX.Sale.CheckoutForm');

	    _this.model = model;
	    _this.scheme = scheme;
	    _this.parameters = parameters;
	    return _this;
	  }

	  babelHelpers.createClass(Form, [{
	    key: "setStage",
	    value: function setStage(stage) {
	      this.stage = stage;
	      return this;
	    }
	  }, {
	    key: "setModel",
	    value: function setModel(fields) {
	      this.model.initFields(fields);
	    } // todo model and fields

	  }, {
	    key: "hasField",
	    value: function hasField(name) {
	      return this.model.hasField(name);
	    }
	  }, {
	    key: "getField",
	    value: function getField(name, defaultValue) {
	      return this.model.getField(name, defaultValue);
	    }
	  }, {
	    key: "setField",
	    value: function setField(name, value) {
	      var isChanged = this.setFieldNoDemand(name, value);

	      if (isChanged) {
	        this.requestRefresh();
	      }

	      return isChanged;
	    }
	  }, {
	    key: "setFieldNoDemand",
	    value: function setFieldNoDemand(name, value) {
	      return this.model.setField(name, value);
	    }
	  }, {
	    key: "setScheme",
	    value: function setScheme(fields) {
	      for (var name in fields) {
	        if (fields.hasOwnProperty(name)) {
	          this.scheme.fields.set(name, fields[name]);
	        }
	      } // todo init when all fields come from api request
	      // this.scheme.initFields(fields);

	    }
	  }, {
	    key: "getSchemeField",
	    value: function getSchemeField(name, defaultValue) {
	      return this.scheme.getField(name, defaultValue);
	    }
	  }, {
	    key: "getParameter",
	    value: function getParameter(name) {
	      var defaultValue = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : null;
	      return this.parameters[name] || defaultValue;
	    }
	  }, {
	    key: "getStage",
	    value: function getStage() {
	      return this.stage;
	    }
	  }, {
	    key: "getContainer",
	    value: function getContainer() {
	      return this.container;
	    }
	  }, {
	    key: "setContainer",
	    value: function setContainer(container) {
	      if (!main_core.Type.isDomNode(container)) {
	        throw new Error('Wrong target node to render');
	      }

	      var oldContainer = this.getContainer();

	      if (main_core.Type.isDomNode(oldContainer)) {
	        this.clearContainer(oldContainer);
	      }

	      this.container = container;
	    }
	  }, {
	    key: "clearContainer",
	    value: function clearContainer(container) {
	      main_core.Event.unbindAll(container);
	      main_core.Dom.clean(container);
	    }
	  }, {
	    key: "buildBlocks",
	    value: function buildBlocks() {
	      var _this2 = this;

	      var blocks = [];
	      this.getParameter('blocks', []).forEach(function (setting) {
	        blocks.push(Factory.create(setting.type, _this2, setting));
	      });
	      return blocks;
	    }
	  }, {
	    key: "getBlocks",
	    value: function getBlocks() {
	      if (this.blocks === null) {
	        this.blocks = this.buildBlocks();
	      }

	      return this.blocks;
	    }
	  }, {
	    key: "refreshLayout",
	    value: function refreshLayout() {
	      this.getBlocks().forEach(function (block) {
	        block.refreshLayout();
	      });
	    }
	  }, {
	    key: "renderTo",
	    value: function renderTo(target) {
	      if (main_core.Type.isString(target)) {
	        target = document.getElementById(target);
	      }

	      this.setContainer(target);
	      this.refreshLayout();
	    }
	  }, {
	    key: "layoutSuccessBlock",
	    value: function layoutSuccessBlock() {
	      var finalBlock = this.getBlocks().find(function (block) {
	        return block.isSuccess();
	      });

	      if (finalBlock) {
	        finalBlock.refreshLayout(true);
	      }
	    }
	  }, {
	    key: "requestRefresh",
	    value: function requestRefresh() {
	      main_core.ajax.runAction('sale.entity.refreshorder', {
	        data: {
	          fields: this.prepareFields()
	        }
	      }).then(this.handleRefreshResponse.bind(this));
	    }
	  }, {
	    key: "handleRefreshResponse",
	    value: function handleRefreshResponse(response) {
	      if (response.status === 'success') {
	        var modelFields = this.extractModelFields(response.data);
	        this.setModel(modelFields);
	        var schemeFields = this.extractSchemeFields(response.data);
	        this.setModel(schemeFields);
	        this.refreshLayout();
	      }
	    }
	  }, {
	    key: "getPropertyErrorCollection",
	    value: function getPropertyErrorCollection() {
	      var collection = this.getField('ERROR_COLLECTION', {});
	      return collection.hasOwnProperty('PROPERTIES') && main_core.Type.isArrayFilled(collection.PROPERTIES) ? collection.PROPERTIES : [];
	    }
	  }, {
	    key: "verify",
	    value: function verify() {
	      return this.verifyProperty();
	    }
	  }, {
	    key: "verifyProperty",
	    value: function verifyProperty() {
	      var list = [];
	      var properties = this.getField('properties');
	      this.getSchemeField('properties', []).forEach(function (item) {
	        if (item.type === 'STRING' && item.required === 'Y') {
	          //console.log('properties', properties[item.ID]);
	          if (main_core.Type.isStringFilled(properties[item.id]) === false) {
	            list.push({
	              id: item.id,
	              message: ''
	            });
	          }
	        }
	      });
	      this.setFieldNoDemand('ERROR_COLLECTION', {
	        PROPERTIES: list
	      });
	      return main_core.Type.isArrayFilled(list) === false;
	    }
	  }, {
	    key: "requestSave",
	    value: function requestSave() {
	      var _this3 = this;

	      if (this.verify()) {
	        main_core.ajax.runAction('sale.entity.saveorder', {
	          data: {
	            fields: this.prepareFields()
	          }
	        }).then(this.handleSaveResponse.bind(this), function (response) {
	          if (response.status === 'error') {
	            _this3.fillErrorCollection(response.errors);

	            _this3.getPropertyErrorCollection().forEach(function (error) {
	              BX.onCustomEvent("BX.Sale.Checkout.Property.Error:onSave_" + error.id);
	            });
	          }
	        });
	      } else {
	        this.getPropertyErrorCollection().forEach(function (error) {
	          BX.onCustomEvent("BX.Sale.Checkout.Property.Error:onSave_" + error.id);
	        });
	      }
	    }
	  }, {
	    key: "handleSaveResponse",
	    value: function handleSaveResponse(response) {
	      if (response.status === 'success') {
	        var modelFields = this.extractModelFields(response.data);
	        this.setModel(modelFields);
	        var schemeFields = this.extractSchemeFields(response.data);
	        this.setScheme(schemeFields);
	        this.layoutSuccessBlock();
	        this.stage++;
	      }
	    }
	  }, {
	    key: "fillErrorCollection",
	    value: function fillErrorCollection(errors) {
	      var list = [];

	      if (main_core.Type.isArrayFilled(errors)) {
	        errors.forEach(function (error) {
	          if (error.code === 'properties') {
	            list.push({
	              id: error.customData.id,
	              message: error.customData.message
	            });
	          }
	        });
	        this.setFieldNoDemand('ERROR_COLLECTION', {
	          PROPERTIES: list
	        });
	      }
	    }
	  }, {
	    key: "prepareFields",
	    value: function prepareFields() {
	      var fields = {
	        'siteId': this.getSchemeField('siteId'),
	        'products': this.getField('basketItems'),
	        'properties': this.getField('properties')
	      };
	      var userId = this.getSchemeField('userId');

	      if (userId) {
	        fields['userId'] = userId;
	      }

	      return fields;
	    }
	  }, {
	    key: "extractModelFields",
	    value: function extractModelFields(data) {
	      var basketItems = {};
	      data.basketItems.forEach(function (item) {
	        basketItems[item.id] = {
	          productId: item.productId,
	          quantity: item.quantity,
	          props: item.props
	        };
	      });
	      var properties = {};
	      data.properties.forEach(function (item) {
	        properties[item.orderPropsId] = item.value;
	      });
	      var payments = {};
	      data.payments.forEach(function (item) {
	        payments[item.id] = {
	          id: item.id,
	          sum: item.sum
	        };
	      });
	      return {
	        basketItems: basketItems,
	        properties: properties,
	        payments: payments
	      };
	    }
	  }, {
	    key: "extractSchemeFields",
	    value: function extractSchemeFields(data) {
	      return {
	        siteId: data.lid,
	        userId: data.userId,
	        accountNumber: data.accountNumber,
	        orderId: data.id,
	        paySystems: data.paySystems,
	        //SIGNED_PARAMETERS: data.SIGNED_PARAMETERS,
	        hash: data.hash // ToDo TOTAL, BASKET_ITEMS, PROPERTIES

	      };
	    }
	  }]);
	  return Form;
	}(main_core_events.EventEmitter);

	var EntityType = function EntityType() {
	  babelHelpers.classCallCheck(this, EntityType);
	};
	babelHelpers.defineProperty(EntityType, "FORM", 'form');
	babelHelpers.defineProperty(EntityType, "MODEL", 'model');
	babelHelpers.defineProperty(EntityType, "SCHEME", 'scheme');
	var entities = [{
	  type: EntityType.FORM,
	  entity: Form
	}, {
	  type: EntityType.MODEL,
	  entity: Model
	}, {
	  type: EntityType.SCHEME,
	  entity: Scheme
	}];

	var Factory$1 = /*#__PURE__*/function (_EventEmitter) {
	  babelHelpers.inherits(Factory, _EventEmitter);

	  function Factory() {
	    babelHelpers.classCallCheck(this, Factory);
	    return babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(Factory).apply(this, arguments));
	  }

	  babelHelpers.createClass(Factory, null, [{
	    key: "createForm",
	    value: function createForm(model, scheme, parameters) {
	      var modelEntity = this.create(EntityType.MODEL, model);
	      var schemeEntity = this.create(EntityType.SCHEME, scheme);
	      return this.create(EntityType.FORM, modelEntity, schemeEntity, parameters);
	    }
	  }, {
	    key: "create",
	    value: function create(type) {
	      this.emit('BX.Sale.Checkout.Factory:onBeforeCreate', entities);
	      var entity = entities.find(function (item) {
	        return item.type === type;
	      })['entity'];

	      if (!entity) {
	        var eventData = {};
	        this.emit('BX.Sale.Checkout.Factory:onCreate', eventData);

	        if (eventData[type]) {
	          entity = eventData[type];
	        }
	      }

	      if (main_core.Type.isFunction(entity)) {
	        for (var _len = arguments.length, options = new Array(_len > 1 ? _len - 1 : 0), _key = 1; _key < _len; _key++) {
	          options[_key - 1] = arguments[_key];
	        }

	        return babelHelpers.construct(entity, options);
	      }

	      return null;
	    }
	  }]);
	  return Factory;
	}(main_core_events.EventEmitter);

	exports.Factory = Factory$1;
	exports.EntityType = EntityType;
	exports.Stage = Stage;

}((this.BX.Sale.CheckoutForm = this.BX.Sale.CheckoutForm || {}),BX,BX,BX.Event));
//# sourceMappingURL=checkout-form.bundle.js.map
