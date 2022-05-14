this.BX = this.BX || {};
this.BX.Sale = this.BX.Sale || {};
this.BX.Sale.Checkout = this.BX.Sale.Checkout || {};
(function (exports,ui_vue,ui_vue_vuex,main_core,sale_checkout_const) {
    'use strict';

    var Order = /*#__PURE__*/function (_VuexBuilderModel) {
      babelHelpers.inherits(Order, _VuexBuilderModel);

      function Order() {
        babelHelpers.classCallCheck(this, Order);
        return babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(Order).apply(this, arguments));
      }

      babelHelpers.createClass(Order, [{
        key: "getName",
        value: function getName() {
          return 'order';
        }
      }, {
        key: "getState",
        value: function getState() {
          return {
            order: Order.getBaseItem(),
            errors: []
          };
        }
      }, {
        key: "validate",
        value: function validate(fields) {
          var result = {};

          if (main_core.Type.isObject(fields.order)) {
            result.order = this.validateOrder(fields.order);
          }

          return result;
        }
      }, {
        key: "validateOrder",
        value: function validateOrder(fields) {
          var result = {};

          if (main_core.Type.isNumber(fields.id) || main_core.Type.isString(fields.id)) {
            result.id = parseInt(fields.id);
          }

          if (main_core.Type.isNumber(fields.accountNumber) || main_core.Type.isString(fields.accountNumber)) {
            result.accountNumber = fields.accountNumber.toString();
          }

          if (main_core.Type.isString(fields.hash)) {
            result.hash = fields.hash.toString();
          }

          if (main_core.Type.isString(fields.payed)) {
            result.payed = fields.payed.toString() === 'Y' ? 'Y' : 'N';
          }

          return result;
        }
      }, {
        key: "getActions",
        value: function getActions() {
          var _this = this;

          return {
            set: function set(_ref, payload) {
              var commit = _ref.commit;
              payload = _this.validate({
                order: payload
              });
              commit('set', payload);
            }
          };
        }
      }, {
        key: "getGetters",
        value: function getGetters() {
          return {
            getOrder: function getOrder(state) {
              return state.order;
            }
          };
        }
      }, {
        key: "getMutations",
        value: function getMutations() {
          return {
            set: function set(state, payload) {
              var item = Order.getBaseItem();
              state.order = Object.assign(item, payload.order);
            }
          };
        }
      }], [{
        key: "getBaseItem",
        value: function getBaseItem() {
          return {
            id: 0,
            payed: 'N',
            accountNumber: null,
            hash: null
          };
        }
      }]);
      return Order;
    }(ui_vue_vuex.VuexBuilderModel);

    var Check = /*#__PURE__*/function (_VuexBuilderModel) {
      babelHelpers.inherits(Check, _VuexBuilderModel);

      function Check() {
        babelHelpers.classCallCheck(this, Check);
        return babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(Check).apply(this, arguments));
      }

      babelHelpers.createClass(Check, [{
        key: "getName",
        value: function getName() {
          return 'check';
        }
      }, {
        key: "getState",
        value: function getState() {
          return {
            check: [],
            status: sale_checkout_const.Loader.status.none
          };
        }
      }, {
        key: "validate",
        value: function validate(fields) {
          var result = {};

          if (main_core.Type.isObject(fields.check)) {
            result.check = this.validateCheck(fields.check);
          }

          if (main_core.Type.isString(fields.status)) {
            result.status = fields.status.toString();
          }

          return result;
        }
      }, {
        key: "validateCheck",
        value: function validateCheck(fields) {
          var result = {};

          if (main_core.Type.isNumber(fields.id) || main_core.Type.isString(fields.id)) {
            result.id = parseInt(fields.id);
          }

          if (main_core.Type.isNumber(fields.paymentId) || main_core.Type.isString(fields.paymentId)) {
            result.paymentId = parseInt(fields.paymentId);
          }

          if (main_core.Type.isString(fields.dateFormatted)) {
            result.dateFormatted = fields.dateFormatted.toString();
          }

          if (main_core.Type.isString(fields.link)) {
            result.link = fields.link.toString();
          }

          if (main_core.Type.isString(fields.status)) {
            var allowed = Object.values(sale_checkout_const.Check.status);
            var status = fields.status.toString();
            result.status = allowed.includes(status) ? status : sale_checkout_const.Check.status.new;
          }

          return result;
        }
      }, {
        key: "getActions",
        value: function getActions() {
          var _this = this;

          return {
            setStatus: function setStatus(_ref, payload) {
              var commit = _ref.commit;
              payload = _this.validate(payload);
              var status = Object.values(sale_checkout_const.Loader.status);
              payload.status = status.includes(payload.status) ? payload.status : sale_checkout_const.Loader.status.none;
              commit('setStatus', payload);
            },
            addItem: function addItem(_ref2, payload) {
              var commit = _ref2.commit;
              payload.fields = _this.validateCheck(payload.fields);
              commit('addItem', payload);
            },
            changeItem: function changeItem(_ref3, payload) {
              var commit = _ref3.commit;
              payload.fields = _this.validateCheck(payload.fields);
              commit('updateItem', payload);
            },
            removeItem: function removeItem(_ref4, payload) {
              var commit = _ref4.commit;
              commit('deleteItem', payload);
            }
          };
        }
      }, {
        key: "getGetters",
        value: function getGetters() {
          return {
            getStatus: function getStatus(state) {
              return state.status;
            },
            getCheck: function getCheck(state) {
              return state.check;
            }
          };
        }
      }, {
        key: "getMutations",
        value: function getMutations() {
          return {
            setStatus: function setStatus(state, payload) {
              var item = {
                status: sale_checkout_const.Loader.status.none
              };
              item = Object.assign(item, payload);
              state.status = item.status;
            },
            addItem: function addItem(state, payload) {
              var item = Check.getBaseItem();
              item = Object.assign(item, payload.fields);
              state.check.push(item);
            },
            updateItem: function updateItem(state, payload) {
              if (typeof state.check[payload.index] === 'undefined') {
                ui_vue.Vue.set(state.check, payload.index, Check.getBaseItem());
              }

              state.check[payload.index] = Object.assign(state.check[payload.index], payload.fields);
            },
            deleteItem: function deleteItem(state, payload) {
              state.check.splice(payload.index, 1);
            },
            clearCheck: function clearCheck(state) {
              state.check = [];
            }
          };
        }
      }], [{
        key: "getBaseItem",
        value: function getBaseItem() {
          return {
            id: 0,
            paymentId: 0,
            dateFormatted: null,
            status: sale_checkout_const.Check.status.new,
            link: null
          };
        }
      }]);
      return Check;
    }(ui_vue_vuex.VuexBuilderModel);

    var Basket = /*#__PURE__*/function (_VuexBuilderModel) {
      babelHelpers.inherits(Basket, _VuexBuilderModel);

      function Basket() {
        babelHelpers.classCallCheck(this, Basket);
        return babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(Basket).apply(this, arguments));
      }

      babelHelpers.createClass(Basket, [{
        key: "getName",
        value: function getName() {
          return 'basket';
        }
      }, {
        key: "getState",
        value: function getState() {
          return {
            basket: [],
            status: sale_checkout_const.Loader.status.none,
            needRefresh: 'N',
            currency: null,
            discount: Basket.getDiscountItem(),
            total: Basket.getTotalItem(),
            errors: []
          };
        }
      }, {
        key: "getBaseItem",
        value: function getBaseItem() {
          return {
            id: 0,
            name: null,
            quantity: 0,
            measureText: null,
            currency: null,
            module: null,
            productProviderClass: null,
            sum: 0.0,
            // finalSum,    basket sum with discounts and taxes => basketItem->getPrice() * basketItem->getQuantity()
            price: 0.0,
            // finalPrice,  basket price with discounts and taxes => basketItem->getPrice()
            baseSum: 0.0,
            // baseSum,     basket sum without discounts and taxes => basketItem->getBasePrice() * basketItem->getQuantity()
            basePrice: 0.0,
            // basePrice,   basket price without discounts and taxes => basketItem->getBasePrice()
            discount: Basket.getDiscountItem(),
            props: [],
            sku: Basket.getSkuItem(),
            product: this.getProductItem(),
            deleted: "N",
            status: sale_checkout_const.Loader.status.none
          };
        }
      }, {
        key: "getProductItem",
        value: function getProductItem() {
          return {
            id: 0,
            picture: this.getVariable('product.noImage', null),
            detailPageUrl: "",
            availableQuantity: 0,
            ratio: 0
          };
        }
      }, {
        key: "validate",
        value: function validate(fields) {
          var result = {};

          if (main_core.Type.isObject(fields.basket)) {
            result.basket = this.validateBasket(fields.basket);
          }

          if (main_core.Type.isString(fields.status)) {
            result.status = fields.status.toString();
          }

          if (main_core.Type.isString(fields.needRefresh)) {
            result.needRefresh = fields.needRefresh.toString() === 'Y' ? 'Y' : 'N';
          }

          if (main_core.Type.isString(fields.currency)) {
            result.currency = fields.currency.toString();
          }

          if (main_core.Type.isObject(fields.discount)) {
            result.discount = this.validateTotalDiscount(fields.discount);
          }

          if (main_core.Type.isObject(fields.total)) {
            result.total = this.validateTotal(fields.total);
          }

          return result;
        }
      }, {
        key: "validateBasket",
        value: function validateBasket(fields) {
          var _this = this;

          var result = {};

          if (main_core.Type.isString(fields.status)) {
            var allowed = Object.values(sale_checkout_const.Loader.status);
            var status = fields.status.toString();
            result.status = allowed.includes(status) ? status : sale_checkout_const.Loader.status.none;
          }

          if (main_core.Type.isString(fields.deleted)) {
            result.deleted = fields.deleted.toString() === 'Y' ? 'Y' : 'N';
          }

          if (main_core.Type.isNumber(fields.id) || main_core.Type.isString(fields.id)) {
            result.id = parseInt(fields.id);
          }

          if (main_core.Type.isString(fields.name)) {
            result.name = fields.name.toString();
          }

          if (main_core.Type.isNumber(fields.quantity) || main_core.Type.isString(fields.quantity)) {
            result.quantity = parseFloat(fields.quantity);
          }

          if (main_core.Type.isString(fields.measureText)) {
            result.measureText = fields.measureText.toString();
          }

          if (main_core.Type.isNumber(fields.sum) || main_core.Type.isString(fields.sum)) {
            result.sum = parseFloat(fields.sum);
          }

          if (main_core.Type.isNumber(fields.price) || main_core.Type.isString(fields.price)) {
            result.price = parseFloat(fields.price);
          }

          if (main_core.Type.isNumber(fields.baseSum) || main_core.Type.isString(fields.baseSum)) {
            result.baseSum = parseFloat(fields.baseSum);
          }

          if (main_core.Type.isNumber(fields.basePrice) || main_core.Type.isString(fields.basePrice)) {
            result.basePrice = parseFloat(fields.basePrice);
          }

          if (main_core.Type.isString(fields.currency)) {
            result.currency = fields.currency.toString();
          }

          if (main_core.Type.isString(fields.module)) {
            result.module = fields.module.toString();
          }

          if (main_core.Type.isString(fields.productProviderClass)) {
            result.productProviderClass = fields.productProviderClass.toString();
          }

          if (main_core.Type.isObject(fields.product)) {
            result.product = this.validateProduct(fields.product);
          }

          if (main_core.Type.isObject(fields.props)) {
            result.props = [];
            fields.props.forEach(function (item) {
              var fields = _this.validateProps(item);

              result.props.push(fields);
            });
          }

          if (main_core.Type.isObject(fields.sku)) {
            result.sku = this.validateSku(fields.sku);
          }

          if (main_core.Type.isObject(fields.discount)) {
            result.discount = this.validateDiscount(fields.discount);
          }

          return result;
        }
      }, {
        key: "validateSku",
        value: function validateSku(fields) {
          var result = {};

          if (main_core.Type.isObject(fields.tree)) {
            result.tree = fields.tree;
          }

          if (main_core.Type.isNumber(fields.parentProductId) || main_core.Type.isString(fields.parentProductId)) {
            result.parentProductId = parseInt(fields.parentProductId);
          }

          return result;
        }
      }, {
        key: "validateDiscount",
        value: function validateDiscount(fields) {
          var result = {};

          if (main_core.Type.isNumber(fields.sum) || main_core.Type.isString(fields.sum)) {
            result.sum = parseFloat(fields.sum);
          }

          if (main_core.Type.isNumber(fields.price) || main_core.Type.isString(fields.price)) {
            result.price = parseFloat(fields.price);
          }

          return result;
        }
      }, {
        key: "validateTotalDiscount",
        value: function validateTotalDiscount(fields) {
          var result = {};

          if (main_core.Type.isNumber(fields.sum) || main_core.Type.isString(fields.sum)) {
            result.sum = parseFloat(fields.sum);
          }

          return result;
        }
      }, {
        key: "validateTotal",
        value: function validateTotal(fields) {
          var result = {};

          if (main_core.Type.isNumber(fields.price) || main_core.Type.isString(fields.price)) {
            result.price = parseFloat(fields.price);
          }

          if (main_core.Type.isNumber(fields.basePrice) || main_core.Type.isString(fields.basePrice)) {
            result.basePrice = parseFloat(fields.basePrice);
          }

          return result;
        }
      }, {
        key: "validateProduct",
        value: function validateProduct(fields) {
          var result = {};

          try {
            for (var field in fields) {
              if (!fields.hasOwnProperty(field)) {
                continue;
              }

              if (field === 'id') {
                if (main_core.Type.isNumber(fields.id) || main_core.Type.isString(fields.id)) {
                  result[field] = fields.id;
                }
              } else if (field === 'picture') {
                if (main_core.Type.isString(fields.picture) && fields.picture.length > 0) {
                  result[field] = fields.picture.toString();
                }
              } else if (field === 'detailPageUrl') {
                if (main_core.Type.isString(fields.detailPageUrl)) {
                  result[field] = fields.detailPageUrl.toString();
                }
              } else if (field === 'availableQuantity') {
                if (main_core.Type.isNumber(fields.availableQuantity) || main_core.Type.isString(fields.availableQuantity)) {
                  result.availableQuantity = parseFloat(fields.availableQuantity);
                }
              } else if (field === 'ratio') {
                if (main_core.Type.isNumber(fields.ratio) || main_core.Type.isString(fields.ratio)) {
                  result.ratio = parseFloat(fields.ratio);
                }
              } else {
                result[field] = fields[field];
              }
            }
          } catch (e) {}

          return result;
        }
      }, {
        key: "validateProps",
        value: function validateProps(fields) {
          var result = {};

          if (main_core.Type.isNumber(fields.id) || main_core.Type.isString(fields.id)) {
            result.id = parseInt(fields.id);
          }

          if (main_core.Type.isString(fields.name)) {
            result.name = fields.name.toString();
          }

          if (main_core.Type.isString(fields.code)) {
            result.code = fields.code.toString();
          }

          if (main_core.Type.isString(fields.value)) {
            result.value = fields.value.toString();
          }

          if (main_core.Type.isNumber(fields.sort) || main_core.Type.isString(fields.sort)) {
            result.sort = parseInt(fields.sort);
          }

          return result;
        }
      }, {
        key: "getActions",
        value: function getActions() {
          var _this2 = this;

          return {
            setTradingPlatformId: function setTradingPlatformId(_ref, payload) {
              var commit = _ref.commit;
              payload = _this2.validate(payload);
              commit('setTradingPlatformId', payload);
            },
            setStatus: function setStatus(_ref2, payload) {
              var commit = _ref2.commit;
              payload = _this2.validate(payload);
              var allowed = Object.values(sale_checkout_const.Loader.status);
              payload.status = allowed.includes(payload.status) ? payload.status : sale_checkout_const.Loader.status.none;
              commit('setStatus', payload);
            },
            setNeedRefresh: function setNeedRefresh(_ref3, payload) {
              var commit = _ref3.commit;
              payload = _this2.validate(payload);
              commit('setNeedRefresh', payload);
            },
            addItem: function addItem(_ref4, payload) {
              var commit = _ref4.commit;
              payload.fields = _this2.validateBasket(payload.fields);
              commit('addItem', payload);
            },
            changeItem: function changeItem(_ref5, payload) {
              var commit = _ref5.commit;
              payload.fields = _this2.validateBasket(payload.fields);
              commit('updateItem', payload);
            },
            removeItem: function removeItem(_ref6, payload) {
              var commit = _ref6.commit;
              commit('deleteItem', payload);
            },
            setFUserId: function setFUserId(_ref7, payload) {
              var commit = _ref7.commit;
              payload = _this2.validate(payload);
              commit('setFUserId', payload);
            },
            setCurrency: function setCurrency(_ref8, payload) {
              var commit = _ref8.commit;
              payload = _this2.validate(payload);
              commit('setCurrency', payload);
            },
            setDiscount: function setDiscount(_ref9, payload) {
              var commit = _ref9.commit;
              payload = _this2.validateDiscount(payload);
              commit('setDiscount', payload);
            },
            setTotal: function setTotal(_ref10, payload) {
              var commit = _ref10.commit;
              payload = _this2.validateTotal(payload);
              commit('setTotal', payload);
            }
          };
        }
      }, {
        key: "getGetters",
        value: function getGetters() {
          var _this3 = this;

          return {
            getStatus: function getStatus(state) {
              return state.status;
            },
            getNeedRefresh: function getNeedRefresh(state) {
              return state.needRefresh;
            },
            get: function get(state) {
              return function (id) {
                if (!state.basket[id] || state.basket[id].length <= 0) {
                  return [];
                }

                return state.basket[id];
              };
            },
            getBasket: function getBasket(state) {
              return state.basket;
            },
            getBaseItem: function getBaseItem(state) {
              return _this3.getBaseItem();
            },
            getCurrency: function getCurrency(state) {
              return state.currency;
            },
            getDiscount: function getDiscount(state) {
              return state.discount;
            },
            getTotal: function getTotal(state) {
              return state.total;
            },
            getErrors: function getErrors(state) {
              return state.errors;
            }
          };
        }
      }, {
        key: "getMutations",
        value: function getMutations() {
          var _this4 = this;

          return {
            setStatus: function setStatus(state, payload) {
              var item = {
                status: sale_checkout_const.Loader.status.none
              };
              item = Object.assign(item, payload);
              state.status = item.status;
            },
            setNeedRefresh: function setNeedRefresh(state, payload) {
              var item = {
                needRefresh: 'N'
              };
              item = Object.assign(item, payload);
              state.needRefresh = item.needRefresh;
            },
            setCurrency: function setCurrency(state, payload) {
              var item = {
                currency: null
              };
              item = Object.assign(item, payload);
              state.currency = item.currency;
            },
            setDiscount: function setDiscount(state, payload) {
              var item = Basket.getDiscountTotalItem();
              item = Object.assign(item, payload);
              state.discount = Object.assign(item, payload);
            },
            setTotal: function setTotal(state, payload) {
              var item = Basket.getTotalItem();
              item = Object.assign(item, payload);
              state.total = Object.assign(item, payload);
            },
            addItem: function addItem(state, payload) {
              var item = _this4.getBaseItem();

              item = Object.assign(item, payload.fields);

              if (main_core.Type.isObject(payload.fields.product)) {
                item.product = Object.assign(item.product, payload.fields.product);
              }

              if (main_core.Type.isObject(item.props)) {
                item.props.forEach(function (fields, index) {
                  var prop = Basket.getPropsItem();
                  prop = Object.assign(prop, fields);
                  item.props[index] = prop;
                });
              }

              if (main_core.Type.isObject(payload.fields.sku)) {
                var _item = Basket.getSkuItem();

                _item = Object.assign(_item, payload.fields.sku);
                payload.fields.sku = _item;
              }

              state.basket.push(item);
              state.basket.forEach(function (item, index) {
                item.sort = index + 1;
              });
            },
            updateItem: function updateItem(state, payload) {
              if (typeof state.basket[payload.index] === 'undefined') {
                ui_vue.Vue.set(state.basket, payload.index, _this4.getBaseItem());
              }

              if (main_core.Type.isObject(payload.fields.product)) {
                payload.fields.product = Object.assign(state.basket[payload.index].product, payload.fields.product);
              }

              if (main_core.Type.isObject(payload.fields.props)) {
                payload.fields.props.forEach(function (fields, index) {
                  var item = Basket.getPropsItem();
                  item = Object.assign(item, fields);
                  payload.fields.props[index] = item;
                });
              }

              if (main_core.Type.isObject(payload.fields.sku)) {
                var item = Basket.getSkuItem();
                item = Object.assign(item, payload.fields.sku);
                payload.fields.sku = item;
              }

              state.basket[payload.index] = Object.assign(state.basket[payload.index], payload.fields);
            },
            deleteItem: function deleteItem(state, payload) {
              // delete state.basket[payload.index];
              state.basket.splice(payload.index, 1);
            },
            clearBasket: function clearBasket(state) {
              state.basket = [];
            },
            clearDiscount: function clearDiscount(state) {
              state.discount = Basket.getDiscountItem();
            },
            clearTotal: function clearTotal(state) {
              state.total = Basket.getTotalItem();
            },
            setErrors: function setErrors(state, payload) {
              state.errors = payload;
            },
            clearErrors: function clearErrors(state) {
              state.errors = [];
            }
          };
        }
      }], [{
        key: "getSkuItem",
        value: function getSkuItem() {
          return {
            parentProductId: 0,
            tree: {}
          };
        }
      }, {
        key: "getPropsItem",
        value: function getPropsItem() {
          return {
            code: "",
            id: 0,
            value: "",
            sort: 0,
            name: ""
          };
        }
      }, {
        key: "getDiscountItem",
        value: function getDiscountItem() {
          return {
            sum: 0,
            // => (basketItem->getBasePrice() * basketItem->getQuantity()) - (basketItem->getPrice() * basketItem->getQuantity())
            price: 0 // => basketItem->getDiscountPrice();

          };
        }
      }, {
        key: "getDiscountTotalItem",
        value: function getDiscountTotalItem() {
          return {
            sum: 0 // => order->getDiscountPrice() + (basket->getBasePrice() - basket->getPrice())

          };
        }
      }, {
        key: "getTotalItem",
        value: function getTotalItem() {
          return {
            price: 0.0,
            //finalPrice, basket price with discounts and taxes => basket->getPrice()
            basePrice: 0.0 //basePrice,  basket price without discounts => basket->getBasePrice();

          };
        }
      }, {
        key: "isFloat",
        value: function isFloat(value) {
          return parseInt(value) !== parseFloat(value);
        }
      }]);
      return Basket;
    }(ui_vue_vuex.VuexBuilderModel);

    var Property = /*#__PURE__*/function (_VuexBuilderModel) {
      babelHelpers.inherits(Property, _VuexBuilderModel);

      function Property() {
        babelHelpers.classCallCheck(this, Property);
        return babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(Property).apply(this, arguments));
      }

      babelHelpers.createClass(Property, [{
        key: "getName",
        value: function getName() {
          return 'property';
        }
      }, {
        key: "getState",
        value: function getState() {
          return {
            property: [],
            errors: []
          };
        }
      }, {
        key: "validate",
        value: function validate(fields) {
          var result = {};

          if (main_core.Type.isNumber(fields.id) || main_core.Type.isString(fields.id)) {
            result.id = parseInt(fields.id);
          }

          if (main_core.Type.isString(fields.name)) {
            result.name = fields.name.toString();
          }

          if (main_core.Type.isString(fields.type)) {
            var allowed = Object.values(sale_checkout_const.Property.type);
            var type = fields.type.toString();
            result.type = allowed.includes(type) ? type : sale_checkout_const.Property.type.undefined;
          }

          if (main_core.Type.isString(fields.value)) {
            result.value = fields.value.toString();
          }

          if (main_core.Type.isString(fields.validated)) {
            result.validated = fields.validated.toString();
          }

          if (main_core.Type.isNumber(fields.personTypeId) || main_core.Type.isString(fields.personTypeId)) {
            result.personTypeId = parseInt(fields.personTypeId);
          }

          return result;
        }
      }, {
        key: "getActions",
        value: function getActions() {
          var _this = this;

          return {
            addItem: function addItem(_ref, payload) {
              var commit = _ref.commit;
              payload.fields = _this.validate(payload.fields);
              commit('addItem', payload);
            },
            changeItem: function changeItem(_ref2, payload) {
              var commit = _ref2.commit;
              payload.fields = _this.validate(payload.fields);
              commit('updateItem', payload);
            },
            removeItem: function removeItem(_ref3, payload) {
              var commit = _ref3.commit;
              commit('deleteItem', payload);
            }
          };
        }
      }, {
        key: "getGetters",
        value: function getGetters() {
          return {
            get: function get(state) {
              return function (id) {
                if (!state.property[id] || state.property[id].length <= 0) {
                  return [];
                }

                return state.property[id];
              };
            },
            getProperty: function getProperty(state) {
              return state.property;
            },
            getBaseItem: function getBaseItem(state) {
              return Property.getBaseItem();
            },
            getErrors: function getErrors(state) {
              return state.errors;
            }
          };
        }
      }, {
        key: "getMutations",
        value: function getMutations() {
          var _this2 = this;

          return {
            addItem: function addItem(state, payload) {
              payload = _this2.prepareFields(payload);
              var item = Property.getBaseItem();
              item = Object.assign(item, payload);
              state.property.unshift(item);
              state.property.forEach(function (item, index) {
                item.sort = index + 1;
              });
            },
            updateItem: function updateItem(state, payload) {
              if (typeof state.property[payload.index] === 'undefined') {
                ui_vue.Vue.set(state.property, payload.index, Property.getBaseItem());
              }

              payload = _this2.prepareFields(payload);
              state.property[payload.index] = Object.assign(state.property[payload.index], payload.fields);
            },
            deleteItem: function deleteItem(state, payload) {
              state.property.splice(payload.index, 1);
            },
            clearProperty: function clearProperty(state) {
              state.property = [];
            },
            setErrors: function setErrors(state, payload) {
              state.errors = payload;
            },
            clearErrors: function clearErrors(state) {
              state.errors = [];
            }
          };
        }
      }, {
        key: "prepareFields",
        value: function prepareFields(fields) {
          var result = {};

          try {
            for (var field in fields) {
              if (!fields.hasOwnProperty(field)) {
                continue;
              }

              if (field === 'validated') {
                var validate = Object.values(sale_checkout_const.Property.validate);
                fields.validated = validate.includes(fields.validated) ? fields.validated : sale_checkout_const.Property.validate.unvalidated;
                result[field] = fields.validated;
              } else {
                result[field] = fields[field];
              }
            }
          } catch (e) {}

          return result;
        }
      }], [{
        key: "getBaseItem",
        value: function getBaseItem() {
          return {
            id: 0,
            name: "",
            type: sale_checkout_const.Property.type.undefined,
            value: "",
            validated: sale_checkout_const.Property.validate.unvalidated
          };
        }
      }]);
      return Property;
    }(ui_vue_vuex.VuexBuilderModel);

    var Payment = /*#__PURE__*/function (_VuexBuilderModel) {
      babelHelpers.inherits(Payment, _VuexBuilderModel);

      function Payment() {
        babelHelpers.classCallCheck(this, Payment);
        return babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(Payment).apply(this, arguments));
      }

      babelHelpers.createClass(Payment, [{
        key: "getName",
        value: function getName() {
          return 'payment';
        }
      }, {
        key: "getState",
        value: function getState() {
          return {
            payment: [],
            errors: []
          };
        }
      }, {
        key: "validate",
        value: function validate(fields) {
          var result = {};

          if (main_core.Type.isNumber(fields.id) || main_core.Type.isString(fields.id)) {
            result.id = parseInt(fields.id);
          }

          if (main_core.Type.isNumber(fields.sum) || main_core.Type.isString(fields.sum)) {
            result.sum = parseFloat(fields.sum);
          }

          if (main_core.Type.isString(fields.paid)) {
            result.paid = fields.paid.toString() === 'Y' ? 'Y' : 'N';
          }

          if (main_core.Type.isString(fields.currency)) {
            result.currency = fields.currency.toString();
          }

          if (main_core.Type.isNumber(fields.accountNumber) || main_core.Type.isString(fields.accountNumber)) {
            result.accountNumber = fields.accountNumber.toString();
          }

          if (main_core.Type.isString(fields.dateBillFormatted)) {
            result.dateBillFormatted = fields.dateBillFormatted.toString();
          }

          if (main_core.Type.isNumber(fields.paySystemId) || main_core.Type.isString(fields.paySystemId)) {
            result.paySystemId = parseInt(fields.paySystemId);
          }

          return result;
        }
      }, {
        key: "getActions",
        value: function getActions() {
          var _this = this;

          return {
            addItem: function addItem(_ref, payload) {
              var commit = _ref.commit;
              payload.fields = _this.validate(payload.fields);
              commit('addItem', payload);
            },
            changeItem: function changeItem(_ref2, payload) {
              var commit = _ref2.commit;
              payload.fields = _this.validate(payload.fields);
              commit('updateItem', payload);
            },
            removeItem: function removeItem(_ref3, payload) {
              var commit = _ref3.commit;
              commit('deleteItem', payload);
            }
          };
        }
      }, {
        key: "getGetters",
        value: function getGetters() {
          return {
            get: function get(state) {
              return function (id) {
                if (!state.payment[id] || state.payment[id].length <= 0) {
                  return [];
                }

                return state.payment[id];
              };
            },
            getPayment: function getPayment(state) {
              return state.payment;
            },
            getErrors: function getErrors(state) {
              return state.errors;
            }
          };
        }
      }, {
        key: "getMutations",
        value: function getMutations() {
          return {
            addItem: function addItem(state, payload) {
              var item = Payment.getBaseItem();
              item = Object.assign(item, payload.fields);
              state.payment.push(item);
            },
            updateItem: function updateItem(state, payload) {
              if (typeof state.payment[payload.index] === 'undefined') {
                ui_vue.Vue.set(state.payment, payload.index, Payment.getBaseItem());
              }

              state.payment[payload.index] = Object.assign(state.payment[payload.index], payload.fields);
            },
            deleteItem: function deleteItem(state, payload) {
              state.payment.splice(payload.index, 1);
            },
            clearPayment: function clearPayment(state) {
              state.payment = [];
            }
          };
        }
      }], [{
        key: "getBaseItem",
        value: function getBaseItem() {
          return {
            id: 0,
            sum: 0.0,
            paid: 'N',
            currency: null,
            accountNumber: null,
            dateBillFormatted: null,
            paySystemId: 0
          };
        }
      }]);
      return Payment;
    }(ui_vue_vuex.VuexBuilderModel);

    var PaySystem = /*#__PURE__*/function (_VuexBuilderModel) {
      babelHelpers.inherits(PaySystem, _VuexBuilderModel);

      function PaySystem() {
        babelHelpers.classCallCheck(this, PaySystem);
        return babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(PaySystem).apply(this, arguments));
      }

      babelHelpers.createClass(PaySystem, [{
        key: "getName",
        value: function getName() {
          return 'pay-system';
        }
      }, {
        key: "getState",
        value: function getState() {
          return {
            paySystem: [],
            status: sale_checkout_const.Loader.status.none
          };
        }
      }, {
        key: "validate",
        value: function validate(fields) {
          var result = {};

          if (main_core.Type.isObject(fields.paySystem)) {
            result.paySystem = this.validatePaySystem(fields.paySystem);
          }

          if (main_core.Type.isString(fields.status)) {
            result.status = fields.status.toString();
          }

          return result;
        }
      }, {
        key: "validatePaySystem",
        value: function validatePaySystem(fields) {
          var result = {};

          if (main_core.Type.isNumber(fields.id) || main_core.Type.isString(fields.id)) {
            result.id = parseInt(fields.id);
          }

          if (main_core.Type.isString(fields.name)) {
            result.name = fields.name.toString();
          }

          if (main_core.Type.isString(fields.logotypeSrc) && fields.logotypeSrc.length > 0) {
            result.picture = fields.logotypeSrc.toString();
          }

          if (main_core.Type.isString(fields.type)) {
            var allowed = Object.values(sale_checkout_const.PaySystem.type);
            var type = fields.type.toString();
            result.type = allowed.includes(type) ? type : sale_checkout_const.PaySystem.type.undefined;
          }

          return result;
        }
      }, {
        key: "getActions",
        value: function getActions() {
          var _this = this;

          return {
            setStatus: function setStatus(_ref, payload) {
              var commit = _ref.commit;
              payload = _this.validate(payload);
              var status = Object.values(sale_checkout_const.Loader.status);
              payload.status = status.includes(payload.status) ? payload.status : sale_checkout_const.Loader.status.none;
              commit('setStatus', payload);
            },
            addItem: function addItem(_ref2, payload) {
              var commit = _ref2.commit;
              payload.fields = _this.validatePaySystem(payload.fields);
              commit('addItem', payload);
            },
            changeItem: function changeItem(_ref3, payload) {
              var commit = _ref3.commit;
              payload.fields = _this.validatePaySystem(payload.fields);
              commit('updateItem', payload);
            },
            removeItem: function removeItem(_ref4, payload) {
              var commit = _ref4.commit;
              commit('deleteItem', payload);
            }
          };
        }
      }, {
        key: "getGetters",
        value: function getGetters() {
          return {
            getStatus: function getStatus(state) {
              return state.status;
            },
            getPaySystem: function getPaySystem(state) {
              return state.paySystem;
            }
          };
        }
      }, {
        key: "getMutations",
        value: function getMutations() {
          return {
            setStatus: function setStatus(state, payload) {
              var item = {
                status: sale_checkout_const.Loader.status.none
              };
              item = Object.assign(item, payload);
              state.status = item.status;
            },
            addItem: function addItem(state, payload) {
              var item = PaySystem.getBaseItem();
              item = Object.assign(item, payload.fields);
              state.paySystem.push(item);
            },
            updateItem: function updateItem(state, payload) {
              if (typeof state.paySystem[payload.index] === 'undefined') {
                ui_vue.Vue.set(state.paySystem, payload.index, PaySystem.getBaseItem());
              }

              state.paySystem[payload.index] = Object.assign(state.paySystem[payload.index], payload.fields);
            },
            deleteItem: function deleteItem(state, payload) {
              state.paySystem.splice(payload.index, 1);
            },
            clearPaySystem: function clearPaySystem(state) {
              state.paySystem = [];
            }
          };
        }
      }], [{
        key: "getBaseItem",
        value: function getBaseItem() {
          return {
            id: 0,
            name: null,
            type: sale_checkout_const.PaySystem.type.undefined,
            picture: null
          };
        }
      }]);
      return PaySystem;
    }(ui_vue_vuex.VuexBuilderModel);

    var Application = /*#__PURE__*/function (_VuexBuilderModel) {
      babelHelpers.inherits(Application, _VuexBuilderModel);

      function Application() {
        babelHelpers.classCallCheck(this, Application);
        return babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(Application).apply(this, arguments));
      }

      babelHelpers.createClass(Application, [{
        key: "getName",
        value: function getName() {
          return 'application';
        }
      }, {
        key: "getState",
        value: function getState() {
          return {
            stage: sale_checkout_const.Application.stage.undefined,
            status: sale_checkout_const.Loader.status.none,
            path: {
              emptyCart: this.getVariable('path.emptyCart', null),
              mainPage: this.getVariable('path.mainPage', null),
              location: this.getVariable('path.location', null)
            },
            common: {
              siteId: this.getVariable('common.siteId', null),
              personTypeId: this.getVariable('common.personTypeId', 0),
              tradingPlatformId: this.getVariable('common.tradingPlatformId', null)
            },
            option: {
              signedParameters: this.getVariable('option.signedParameters', null)
            },
            message: {
              buttonCheckoutTitle: this.getVariable('messages.buttonCheckoutTitle', null)
            },
            errors: []
          };
        }
      }, {
        key: "validate",
        value: function validate(fields) {
          var result = {};

          if (main_core.Type.isString(fields.stage)) {
            result.stage = fields.stage.toString();
          }

          if (main_core.Type.isString(fields.status)) {
            result.status = fields.status.toString();
          }

          if (main_core.Type.isObject(fields.path)) {
            result.path = this.validatePaths(fields.path);
          }

          if (main_core.Type.isObject(fields.common)) {
            result.common = this.validateCommon(fields.common);
          }

          if (main_core.Type.isObject(fields.options)) {
            result.options = this.validateOptions(fields.options);
          }

          return result;
        }
      }, {
        key: "validateCommon",
        value: function validateCommon(fields) {
          var result = {};

          if (main_core.Type.isString(fields.siteId)) {
            result.siteId = fields.siteId.toString();
          }

          if (main_core.Type.isNumber(fields.tradingPlatformId) || main_core.Type.isString(fields.tradingPlatformId)) {
            result.tradingPlatformId = parseInt(fields.tradingPlatformId);
          }

          if (main_core.Type.isNumber(fields.personTypeId) || main_core.Type.isString(fields.personTypeId)) {
            result.personTypeId = parseInt(fields.personTypeId);
          }

          return result;
        }
      }, {
        key: "validatePaths",
        value: function validatePaths(fields) {
          var result = {};

          if (main_core.Type.isString(fields.productNoImage)) {
            result.productNoImage = fields.productNoImage.toString();
          }

          if (main_core.Type.isString(fields.emptyCart)) {
            result.emptyCart = fields.emptyCart.toString();
          }

          if (main_core.Type.isString(fields.mainPage)) {
            result.mainPage = fields.mainPage.toString();
          }

          if (main_core.Type.isString(fields.location)) {
            result.location = fields.location.toString();
          }

          return result;
        }
      }, {
        key: "validateOptions",
        value: function validateOptions(fields) {
          var result = {};

          if (main_core.Type.isString(fields.signedParameters)) {
            result.signedParameters = fields.signedParameters.toString();
          }

          return result;
        }
      }, {
        key: "getActions",
        value: function getActions() {
          var _this = this;

          return {
            setPathLocation: function setPathLocation(_ref, payload) {
              var commit = _ref.commit;
              payload = _this.validatePaths({
                location: payload
              });
              commit('setPathLocation', payload.location);
            },
            setStatus: function setStatus(_ref2, payload) {
              var commit = _ref2.commit;
              payload = _this.validate(payload);
              var status = [sale_checkout_const.Loader.status.none, sale_checkout_const.Loader.status.wait];
              payload.status = status.includes(payload.status) ? payload.status : sale_checkout_const.Loader.status.none;
              commit('setStatus', payload);
            },
            setStage: function setStage(_ref3, payload) {
              var commit = _ref3.commit;
              payload = _this.validate(payload);
              var allowed = Object.values(sale_checkout_const.Application.stage);
              payload.stage = allowed.includes(payload.stage) ? payload.stage : sale_checkout_const.Application.stage.undefined;
              commit('setStage', payload);
            }
          };
        }
      }, {
        key: "getGetters",
        value: function getGetters() {
          return {
            getErrors: function getErrors(state) {
              return state.errors;
            },
            getPath: function getPath(state) {
              return state.path;
            },
            getSignedParameters: function getSignedParameters(state) {
              return state.option.signedParameters;
            },
            getPathLocation: function getPathLocation(state, getters) {
              return getters.getPath.location;
            },
            getPathMainPage: function getPathMainPage(state, getters) {
              return getters.getPath.mainPage;
            },
            getTradingPlatformId: function getTradingPlatformId(state) {
              return state.common.tradingPlatformId;
            },
            getTitleCheckoutButton: function getTitleCheckoutButton(state) {
              return state.message.buttonCheckoutTitle;
            },
            getSiteId: function getSiteId(state) {
              return state.common.siteId;
            },
            getPersonTypeId: function getPersonTypeId(state) {
              return state.common.personTypeId;
            },
            getStatus: function getStatus(state) {
              return state.status;
            },
            getStage: function getStage(state) {
              return state.stage;
            }
          };
        }
      }, {
        key: "getMutations",
        value: function getMutations() {
          return {
            setPathLocation: function setPathLocation(state, payload) {
              state.path.location = payload;
            },
            setStatus: function setStatus(state, payload) {
              var item = {
                status: sale_checkout_const.Loader.status.none
              };
              item = Object.assign(item, payload);
              state.status = item.status;
            },
            setStage: function setStage(state, payload) {
              var item = {
                stage: sale_checkout_const.Application.stage.undefined
              };
              item = Object.assign(item, payload);
              state.stage = item.stage;
            },
            setErrors: function setErrors(state, payload) {
              state.errors = payload;
            },
            clearErrors: function clearErrors(state) {
              state.errors = [];
            }
          };
        }
      }]);
      return Application;
    }(ui_vue_vuex.VuexBuilderModel);

    var Consent = /*#__PURE__*/function (_VuexBuilderModel) {
      babelHelpers.inherits(Consent, _VuexBuilderModel);

      function Consent() {
        babelHelpers.classCallCheck(this, Consent);
        return babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(Consent).apply(this, arguments));
      }

      babelHelpers.createClass(Consent, [{
        key: "getName",
        value: function getName() {
          return 'consent';
        }
      }, {
        key: "getState",
        value: function getState() {
          return {
            status: sale_checkout_const.Consent.status.init,
            consent: Consent.getBaseItem(),
            errors: []
          };
        }
      }, {
        key: "validate",
        value: function validate(fields) {
          var result = {};

          if (main_core.Type.isString(fields.status)) {
            result.status = fields.status.toString();
          }

          if (main_core.Type.isObject(fields.consent)) {
            result.consent = this.validateConsent(fields.consent);
          }

          return result;
        }
      }, {
        key: "validateConsent",
        value: function validateConsent(fields) {
          var result = {};

          if (main_core.Type.isNumber(fields.id) || main_core.Type.isString(fields.id)) {
            result.id = parseInt(fields.id);
          }

          if (main_core.Type.isString(fields.title)) {
            result.title = fields.title.toString();
          }

          if (main_core.Type.isString(fields.isLoaded)) {
            result.isLoaded = fields.isLoaded.toString();
          }

          if (main_core.Type.isString(fields.autoSave)) {
            result.autoSave = fields.autoSave.toString();
          }

          if (main_core.Type.isString(fields.isChecked)) {
            result.isChecked = fields.isChecked.toString();
          }

          if (main_core.Type.isString(fields.submitEventName)) {
            result.submitEventName = fields.submitEventName.toString();
          }

          if (main_core.Type.isArrayFilled(fields.params)) {
            result.params = this.validateParams(fields.params);
          }

          return result;
        }
      }, {
        key: "validateParams",
        value: function validateParams(fields) {
          var result = [];

          try {
            for (var key in fields) {
              if (!fields.hasOwnProperty(key)) {
                continue;
              }

              if (main_core.Type.isNumber(fields[key]) || main_core.Type.isString(fields[key])) {
                result[key] = fields[key];
              }
            }
          } catch (e) {}

          return result;
        }
      }, {
        key: "getActions",
        value: function getActions() {
          var _this = this;

          return {
            setStatus: function setStatus(_ref, payload) {
              var commit = _ref.commit;
              payload = _this.validate({
                status: payload
              });
              var status = Object.values(sale_checkout_const.Consent.status);
              payload.status = status.includes(payload.status) ? payload.status : sale_checkout_const.Consent.status.init;
              commit('setStatus', payload);
            },
            set: function set(_ref2, payload) {
              var commit = _ref2.commit;
              payload = _this.validate({
                consent: payload
              });
              commit('set', payload);
            }
          };
        }
      }, {
        key: "getGetters",
        value: function getGetters() {
          return {
            getStatus: function getStatus(state) {
              return state.status;
            },
            get: function get(state) {
              return state.consent;
            }
          };
        }
      }, {
        key: "getMutations",
        value: function getMutations() {
          return {
            setStatus: function setStatus(state, payload) {
              state.status = payload.status;
            },
            set: function set(state, payload) {
              var item = Consent.getBaseItem();
              state.consent = Object.assign(item, payload.consent);
            },
            setErrors: function setErrors(state, payload) {
              state.errors = payload;
            },
            clearErrors: function clearErrors(state) {
              state.errors = [];
            }
          };
        }
      }], [{
        key: "getBaseItem",
        value: function getBaseItem() {
          return {
            id: 0,
            title: '',
            isLoaded: '',
            autoSave: '',
            isChecked: '',
            submitEventName: '',
            params: []
          };
        }
      }]);
      return Consent;
    }(ui_vue_vuex.VuexBuilderModel);

    exports.Order = Order;
    exports.Check = Check;
    exports.Basket = Basket;
    exports.Property = Property;
    exports.Payment = Payment;
    exports.PaySystem = PaySystem;
    exports.Application = Application;
    exports.Consent = Consent;

}((this.BX.Sale.Checkout.Model = this.BX.Sale.Checkout.Model || {}),BX,BX,BX,BX.Sale.Checkout.Const));
//# sourceMappingURL=model.bundle.js.map
