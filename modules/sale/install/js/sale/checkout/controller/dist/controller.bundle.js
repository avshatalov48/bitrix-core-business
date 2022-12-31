this.BX = this.BX || {};
this.BX.Sale = this.BX.Sale || {};
this.BX.Sale.Checkout = this.BX.Sale.Checkout || {};
(function (exports,main_core,main_core_events,sale_checkout_provider_rest,sale_checkout_const,sale_checkout_lib) {
    'use strict';

    var Basket = /*#__PURE__*/function () {
      function Basket() {
        babelHelpers.classCallCheck(this, Basket);
        this.pool = this.getPool();
        this.timer = this.getTimer();
        this.running = 'N';
      }
      /**
       * @private
       */


      babelHelpers.createClass(Basket, [{
        key: "getPool",
        value: function getPool() {
          return new sale_checkout_lib.Pool();
        }
        /**
         * @private
         */

      }, {
        key: "getTimer",
        value: function getTimer() {
          return new sale_checkout_lib.Timer();
        }
        /**
         * @private
         */

      }, {
        key: "isRunning",
        value: function isRunning() {
          return this.running === 'Y';
        }
        /**
         * @private
         */

      }, {
        key: "setRunningY",
        value: function setRunningY() {
          this.running = 'Y';
        }
        /**
         * @private
         */

      }, {
        key: "setRunningN",
        value: function setRunningN() {
          this.running = 'N';
        }
        /**
         * @private
         */

      }, {
        key: "setStore",
        value: function setStore(store) {
          this.store = store;
          return this;
        }
        /**
         * @private
         */

      }, {
        key: "setProvider",
        value: function setProvider(provider) {
          this.provider = provider;
          return this;
        }
        /**
         * @private
         */

      }, {
        key: "executeRestAnswer",
        value: function executeRestAnswer(command, result, extra) {
          return this.provider.execute(command, result, extra);
        }
        /**
         * @private
         */

      }, {
        key: "getItem",
        value: function getItem(index) {
          return this.store.getters['basket/get'](index);
        }
        /**
         * @private
         */

      }, {
        key: "getBasket",
        value: function getBasket() {
          return this.store.getters['basket/getBasket'];
        }
        /**
         * @private
         */

      }, {
        key: "getBasketCollection",
        value: function getBasketCollection() {
          return this.getBasket().filter(function (item) {
            return item.deleted === 'N';
          });
        }
        /**
         * @private
         */

      }, {
        key: "changeItem",
        value: function changeItem(product) {
          this.store.dispatch('basket/changeItem', {
            index: product.index,
            fields: product.fields
          });
        }
        /**
         * @private
         */

      }, {
        key: "setQuantity",
        value: function setQuantity(index, quantity) {
          var fields = this.getItem(index);
          fields.quantity = quantity;
          fields.baseSum = this.round(fields.basePrice * fields.quantity);
          fields.sum = this.round(fields.price * fields.quantity);
          fields.discount.sum = this.round(fields.discount.price * fields.quantity);
          this.refreshDiscount();
          this.refreshTotal();
          this.pool.add(sale_checkout_const.Pool.action.quantity, index, {
            id: fields.id,
            value: fields.quantity
          });
          this.changeItem({
            index: index,
            fields: fields
          });
          this.shelveCommit();
        }
      }, {
        key: "refreshDiscount",
        value: function refreshDiscount() {
          var basket = this.getBasket();

          if (basket.length > 0) {
            this.store.dispatch('basket/setDiscount', {
              sum: basket.reduce(function (result, value) {
                return result + value.discount.sum;
              }, 0)
            });
          }
        }
      }, {
        key: "refreshTotal",
        value: function refreshTotal() {
          var basket = this.getBasketCollection();

          if (basket.length > 0) {
            this.store.dispatch('basket/setTotal', {
              price: basket.reduce(function (result, value) {
                return result + value.sum;
              }, 0),
              basePrice: basket.reduce(function (result, value) {
                return result + value.baseSum;
              }, 0)
            });
          }
        }
        /**
         * @private
         */

      }, {
        key: "removeItem",
        value: function removeItem(product) {
          return this.store.dispatch('basket/removeItem', {
            index: product.index
          });
        }
        /**
         * @private
         */

      }, {
        key: "round",
        value: function round(value) {
          var precision = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : 10;
          var factor = Math.pow(10, precision);
          return Math.round(value * factor) / factor;
        }
      }, {
        key: "emitOnBasketChange",
        value: function emitOnBasketChange() {
          BX.onCustomEvent('OnBasketChange');
        }
        /**
         * @private
         */

      }, {
        key: "handlerOrderSuccess",
        value: function handlerOrderSuccess() {
          this.emitOnBasketChange();
        }
        /**
         * @private
         */

      }, {
        key: "handlerRemoveProductSuccess",
        value: function handlerRemoveProductSuccess() {
          this.emitOnBasketChange();
        }
        /**
         * @private
         */

      }, {
        key: "handlerRestoreProductSuccess",
        value: function handlerRestoreProductSuccess() {
          this.emitOnBasketChange();
        }
        /**
         * @private
         */

      }, {
        key: "handlerRemove",
        value: function handlerRemove(event) {
          var index = event.getData().index;
          var fields = this.getItem(index);
          fields.deleted = 'Y';
          fields.status = sale_checkout_const.Loader.status.wait;
          this.pool.add(sale_checkout_const.Pool.action["delete"], index, {
            id: fields.id,
            fields: {
              value: 'Y'
            }
          });
          this.changeItem({
            index: index,
            fields: fields
          });
          this.shelveCommit();
        }
        /**
         * @private
         */

      }, {
        key: "handlerSuccessRemove",
        value: function handlerSuccessRemove(event) {
          var _this = this;

          var index = event.getData().index;
          this.timer.create(5000, index + '_DELETE', function () {
            return _this.removeItem({
              index: index
            }).then(function () {
              if (_this.getBasket().length === 0) {
                _this.store.dispatch('application/setStage', {
                  stage: sale_checkout_const.Application.stage.empty
                });
              }
            });
          });
        }
        /**
         * @private
         */

      }, {
        key: "handlerRestore",
        value: function handlerRestore(event) {
          var index = event.getData().index;
          var fields = this.getItem(index);
          this.timer.clean({
            index: index + '_DELETE'
          });
          fields.deleted = 'N';
          fields.status = sale_checkout_const.Loader.status.wait; //todo: send all fields ?

          this.pool.add(sale_checkout_const.Pool.action.restore, index, {
            basePrice: fields.basePrice,
            baseSum: fields.baseSum,
            currency: fields.currency,
            discount: fields.discount,
            id: fields.id,
            measureText: fields.measureText,
            module: fields.module,
            name: fields.name,
            price: fields.price,
            product: fields.product,
            productProviderClass: fields.productProviderClass,
            props: fields.props,
            quantity: fields.quantity,
            sum: fields.sum
          });
          this.changeItem({
            index: index,
            fields: fields
          });
          this.shelveCommit();
        }
        /**
         * @private
         */

      }, {
        key: "handlerChangeQuantity",
        value: function handlerChangeQuantity(event) {
          // let data = event.getData().data;
          var index = event.getData().index;
          var fields = this.getItem(index);
          var quantity = fields.quantity;
          var ratio = fields.product.ratio;
          var available = fields.product.availableQuantity;
          quantity = sale_checkout_lib.Basket.roundValue(quantity);
          ratio = sale_checkout_lib.Basket.roundValue(ratio);
          quantity = isNaN(quantity) ? 0 : quantity;

          if (ratio > 0 && quantity < ratio) {
            quantity = ratio;
          }

          if (sale_checkout_lib.Product.isService(fields)) ; else {
            // for products
            if (sale_checkout_lib.Product.isLimitedQuantity(fields)) {
              if (available > 0 && quantity > available) {
                quantity = available;
              }
            }
          }

          quantity = sale_checkout_lib.Basket.toFixed(quantity, ratio, available);

          if (fields.quantity !== quantity) {
            this.setQuantity(index, quantity);
          }
        }
        /**
         * @private
         */

      }, {
        key: "handlerQuantityPlus",
        value: function handlerQuantityPlus(event) {
          var index = event.getData().index;
          var fields = this.getItem(index);
          var quantity = fields.quantity;
          var ratio = fields.product.ratio;
          var available = fields.product.availableQuantity;
          quantity = sale_checkout_lib.Basket.roundValue(quantity);
          ratio = sale_checkout_lib.Basket.roundValue(ratio);
          quantity = quantity + ratio;

          if (sale_checkout_lib.Basket.isValueFloat(quantity)) {
            quantity = sale_checkout_lib.Basket.roundFloatValue(quantity);
          }

          if (sale_checkout_lib.Product.isService(fields)) ; else {
            // for products
            if (sale_checkout_lib.Product.isLimitedQuantity(fields)) {
              if (available > 0 && quantity > available) {
                quantity = available;
              }
            }
          }

          quantity = sale_checkout_lib.Basket.toFixed(quantity, ratio, available);

          if (fields.quantity < quantity) {
            this.setQuantity(index, quantity);
          }
        }
        /**
         * @private
         */

      }, {
        key: "handlerQuantityMinus",
        value: function handlerQuantityMinus(event) {
          var index = event.getData().index;
          var fields = this.getItem(index);
          var quantity = fields.quantity;
          var ratio = fields.product.ratio;
          var available = fields.product.availableQuantity;
          quantity = sale_checkout_lib.Basket.roundValue(quantity);
          ratio = sale_checkout_lib.Basket.roundValue(ratio);
          var delta = quantity = quantity - ratio;

          if (sale_checkout_lib.Basket.isValueFloat(quantity)) {
            quantity = sale_checkout_lib.Basket.roundFloatValue(quantity);
            delta = sale_checkout_lib.Basket.roundFloatValue(delta);
          }

          if (ratio > 0 && quantity < ratio) {
            quantity = ratio;
          }

          if (sale_checkout_lib.Product.isService(fields)) ; else {
            // for products
            if (sale_checkout_lib.Product.isLimitedQuantity(fields)) {
              if (available > 0 && quantity > available) {
                quantity = available;
              }
            }
          }

          quantity = sale_checkout_lib.Basket.toFixed(quantity, ratio, available);

          if (delta >= ratio) {
            this.setQuantity(index, quantity);
          }
        }
        /**
         * @private
         */

      }, {
        key: "commit",
        value: function commit() {
          var _this2 = this;

          return new Promise(function (resolve, reject) {
            var fields = {};

            if (_this2.pool.isEmpty() === false) {
              fields = _this2.pool.get();

              _this2.pool.clean();

              var component = sale_checkout_const.Component.bitrixSaleOrderCheckout;
              var cmd = sale_checkout_const.RestMethod.saleEntityRecalculateBasket;
              main_core.ajax.runComponentAction(component, cmd, {
                data: {
                  actions: fields
                },
                signedParameters: _this2.store.getters['application/getSignedParameters']
              }).then(function (result) {
                return _this2.executeRestAnswer(cmd, result, _this2.pool).then(function () {
                  return _this2.commit().then(function () {
                    return resolve();
                  });
                });
              })["catch"]();
            } else {
              resolve();
            }
          });
        }
        /**
         * @private
         */

      }, {
        key: "shelveCommit",
        value: function shelveCommit() {
          var _this3 = this;

          var index = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : 'BASKET';

          if (this.isRunning() === false) {
            this.timer.create(300, index, function () {
              _this3.setRunningY();

              _this3.commit().then(function () {
                return _this3.setRunningN();
              });
            });
          }
        }
        /**
         * @private
         */

      }, {
        key: "getStatus",
        value: function getStatus() {
          return this.store.getters['basket/getStatus'];
        }
        /**
         * @private
         */

      }, {
        key: "setStatusWait",
        value: function setStatusWait() {
          var app = {
            status: sale_checkout_const.Loader.status.wait
          };
          return this.store.dispatch('basket/setStatus', app);
        }
        /**
         * @private
         */

      }, {
        key: "setStatusNone",
        value: function setStatusNone() {
          var app = {
            status: sale_checkout_const.Loader.status.none
          };
          return this.store.dispatch('basket/setStatus', app);
        }
        /**
         * @private
         */

      }, {
        key: "handlerNeedRefreshY",
        value: function handlerNeedRefreshY() {
          this.setNeedRefreshY();
          this.setStatusWait();
        }
        /**
         * @private
         */

      }, {
        key: "handlerNeedRefreshN",
        value: function handlerNeedRefreshN() {
          this.setNeedRefreshN();
          this.setStatusNone();
        }
        /**
         * @private
         */

      }, {
        key: "setNeedRefreshY",
        value: function setNeedRefreshY() {
          var app = {
            needRefresh: 'Y'
          };
          return this.store.dispatch('basket/setNeedRefresh', app);
        }
        /**
         * @private
         */

      }, {
        key: "setNeedRefreshN",
        value: function setNeedRefreshN() {
          var app = {
            needRefresh: 'N'
          };
          return this.store.dispatch('basket/setNeedRefresh', app);
        }
        /**
         * @private
         */

      }, {
        key: "handlerChangeSku",
        value: function handlerChangeSku(event) {
          var offerId = event.getData().data[0].ID;
          var index = event.getData().index;
          var fields = this.getItem(index);
          fields.status = sale_checkout_const.Loader.status.wait;
          this.pool.add(sale_checkout_const.Pool.action.offer, index, {
            id: fields.id,
            fields: {
              offerId: offerId
            }
          });
          this.changeItem({
            index: index,
            fields: fields
          });
          this.shelveCommit();
        }
      }]);
      return Basket;
    }();

    var Application = /*#__PURE__*/function () {
      function Application(option) {
        var _this = this;

        babelHelpers.classCallCheck(this, Application);
        this.init(option).then(function () {
          return _this.initProvider();
        }).then(function () {
          return _this.iniController();
        }).then(function () {
          return _this.subscribeToEvents();
        }).then(function () {
          return _this.subscribeToStoreChanges();
        });
      }
      /**
       * @private
       */


      babelHelpers.createClass(Application, [{
        key: "init",
        value: function init(option) {
          this.store = option.store;
          return new Promise(function (resolve, reject) {
            return resolve();
          });
        }
        /**
         * @private
         */

      }, {
        key: "initProvider",
        value: function initProvider() {
          this.provider = sale_checkout_provider_rest.BasketRestHandler.create({
            store: this.store
          });
          return new Promise(function (resolve, reject) {
            return resolve();
          });
        }
        /**
         * @private
         */

      }, {
        key: "iniController",
        value: function iniController() {
          this.basket = new Basket().setStore(this.store).setProvider(this.provider);
          return new Promise(function (resolve, reject) {
            return resolve();
          });
        }
        /**
         * @private
         */

      }, {
        key: "executeRestAnswer",
        value: function executeRestAnswer(command, result, extra) {
          return this.provider.execute(command, result, extra);
        }
        /**
         * @private
         */

      }, {
        key: "subscribeToEvents",
        value: function subscribeToEvents() {
          var _this2 = this;

          main_core_events.EventEmitter.subscribe(sale_checkout_const.EventType.order.success, function (e) {
            return _this2.basket.handlerOrderSuccess(e);
          });
          main_core_events.EventEmitter.subscribe(sale_checkout_const.EventType.basket.removeProduct, function (e) {
            return _this2.basket.handlerRemoveProductSuccess(e);
          });
          main_core_events.EventEmitter.subscribe(sale_checkout_const.EventType.basket.restoreProduct, function (e) {
            return _this2.basket.handlerRestoreProductSuccess(e);
          });
          main_core_events.EventEmitter.subscribe(sale_checkout_const.EventType.basket.buttonRemoveProduct, main_core.Runtime.debounce(function (e) {
            return _this2.basket.handlerRemove(e);
          }, 500, this));
          main_core_events.EventEmitter.subscribe(sale_checkout_const.EventType.basket.buttonPlusProduct, function (e) {
            return _this2.basket.handlerQuantityPlus(e);
          });
          main_core_events.EventEmitter.subscribe(sale_checkout_const.EventType.basket.buttonMinusProduct, function (e) {
            return _this2.basket.handlerQuantityMinus(e);
          });
          main_core_events.EventEmitter.subscribe(sale_checkout_const.EventType.basket.inputChangeQuantityProduct, function (e) {
            return _this2.basket.handlerChangeQuantity(e);
          });
          main_core_events.EventEmitter.subscribe(sale_checkout_const.EventType.basket.buttonRestoreProduct, main_core.Runtime.debounce(function (e) {
            return _this2.basket.handlerRestore(e);
          }, 500, this));
          main_core_events.EventEmitter.subscribe(sale_checkout_const.EventType.basket.needRefresh, function (e) {
            return _this2.basket.handlerNeedRefreshY(e);
          });
          main_core_events.EventEmitter.subscribe(sale_checkout_const.EventType.basket.refreshAfter, function (e) {
            return _this2.basket.handlerNeedRefreshN(e);
          });
          main_core_events.EventEmitter.subscribe(sale_checkout_const.EventType.basket.changeSku, function (e) {
            return _this2.basket.handlerChangeSku(e);
          });
          main_core_events.EventEmitter.subscribe(sale_checkout_const.EventType.consent.refused, function () {
            return _this2.handlerConsentRefused();
          });
          main_core_events.EventEmitter.subscribe(sale_checkout_const.EventType.consent.accepted, function () {
            return _this2.handlerConsentAccepted();
          });
          main_core_events.EventEmitter.subscribe(sale_checkout_const.EventType.property.validate, function (e) {
            return _this2.handlerValidateProperty(e);
          });
          main_core_events.EventEmitter.subscribe(sale_checkout_const.EventType.element.buttonCheckout, main_core.Runtime.debounce(function () {
            return _this2.handlerCheckout();
          }, 1000, this));
          main_core_events.EventEmitter.subscribe(sale_checkout_const.EventType.element.buttonShipping, main_core.Runtime.debounce(function () {
            return _this2.handlerShipping();
          }, 1000, this));
          main_core_events.EventEmitter.subscribe(sale_checkout_const.EventType.paysystem.beforeInitList, function () {
            return _this2.paySystemSetStatusWait();
          });
          main_core_events.EventEmitter.subscribe(sale_checkout_const.EventType.paysystem.afterInitList, function () {
            return _this2.paySystemSetStatusNone();
          });
        }
        /**
         * @private
         */

      }, {
        key: "subscribeToStoreChanges",
        value: function subscribeToStoreChanges() {
          // this.store.subscribe((mutation, state) => {
          //	 const { payload, type } = mutation;
          //	 if (type === 'basket/setNeedRefresh')
          //	 {
          //	 	alert('@@');
          //	 	this.getData();
          //	 }
          // });
          return new Promise(function (resolve, reject) {
            return resolve();
          });
        }
        /**
         * @private
         */

      }, {
        key: "paySystemSetStatusWait",
        value: function paySystemSetStatusWait() {
          var paySystem = {
            status: sale_checkout_const.Loader.status.wait
          };
          return this.store.dispatch('pay-system/setStatus', paySystem);
        }
        /**
         * @private
         */

      }, {
        key: "paySystemSetStatusNone",
        value: function paySystemSetStatusNone() {
          var paySystem = {
            status: sale_checkout_const.Loader.status.none
          };
          return this.store.dispatch('pay-system/setStatus', paySystem);
        }
        /**
         * @private
         */

      }, {
        key: "appSetStatusWait",
        value: function appSetStatusWait() {
          var app = {
            status: sale_checkout_const.Loader.status.wait
          };
          return this.store.dispatch('application/setStatus', app);
        }
        /**
         * @private
         */

      }, {
        key: "appSetStatusNone",
        value: function appSetStatusNone() {
          var app = {
            status: sale_checkout_const.Loader.status.none
          };
          return this.store.dispatch('application/setStatus', app);
        }
        /**
         * @private
         */

      }, {
        key: "handlerConsentAccepted",
        value: function handlerConsentAccepted() {
          this.store.dispatch('consent/setStatus', sale_checkout_const.Consent.status.accepted);
        }
        /**
         * @private
         */

      }, {
        key: "handlerConsentRefused",
        value: function handlerConsentRefused() {
          this.store.dispatch('consent/setStatus', sale_checkout_const.Consent.status.refused);
        }
        /**
         * @private
         */

      }, {
        key: "handlerCheckout",
        value: function handlerCheckout() {
          var _this3 = this;

          BX.onCustomEvent(sale_checkout_const.Consent.validate.submit, []);
          var consent = this.store.getters['consent/get'];
          var consentStatus = this.store.getters['consent/getStatus'];
          var allowed = consent.id > 0 ? consentStatus === sale_checkout_const.Consent.status.accepted : true;

          if (allowed) {
            // this.propertiesValidate();
            // this.propertiesIsValid() ? alert('propsSuccess'):alert('propsError')
            this.appSetStatusWait();
            this.saveOrder().then(function () {
              _this3.appSetStatusNone().then(function () {
                var order = _this3.store.getters['order/getOrder'];

                if (order.id > 0) {
                  var url = sale_checkout_lib.History.pushState(_this3.store.getters['application/getPathLocation'], {
                    accountNumber: order.accountNumber,
                    access: order.hash
                  });

                  _this3.store.dispatch('application/setPathLocation', url);
                }
              });
            })["catch"](function () {
              return _this3.appSetStatusNone();
            });
          }
        }
        /**
         * @private
         */

      }, {
        key: "handlerShipping",
        value: function handlerShipping() {
          this.store.dispatch('application/setStage', {
            stage: sale_checkout_const.Application.stage.view
          }); // todo

          delete BX.UserConsent;
          var order = this.store.getters['order/getOrder'];

          if (order.id > 0) {
            var component = sale_checkout_const.Component.bitrixSaleOrderCheckout;
            var cmd = sale_checkout_const.RestMethod.saleEntityPaymentPay;
            return main_core.ajax.runComponentAction(component, cmd, {
              data: {
                fields: {
                  orderId: order.id,
                  accessCode: order.hash
                }
              },
              signedParameters: this.store.getters['application/getSignedParameters']
            });
          }
        }
        /**
         * @private
         */

      }, {
        key: "saveOrder",
        value: function saveOrder() {
          var _this4 = this;

          var component = sale_checkout_const.Component.bitrixSaleOrderCheckout;
          var cmd = sale_checkout_const.RestMethod.saleEntitySaveOrder;
          return main_core.ajax.runComponentAction(component, cmd, {
            data: {
              fields: {
                siteId: this.store.getters['application/getSiteId'],
                personTypeId: this.store.getters['application/getPersonTypeId'],
                tradingPlatformId: this.store.getters['application/getTradingPlatformId'],
                properties: this.preparePropertyFields(this.getPropertyList())
              }
            },
            signedParameters: this.store.getters['application/getSignedParameters']
          }).then(function (result) {
            return _this4.executeRestAnswer(cmd, result);
          })["catch"](function (result) {
            return _this4.executeRestAnswer(cmd, {
              error: result.errors
            });
          });
        }
        /**
         * @private
         */

      }, {
        key: "handlerValidateProperty",
        value: function handlerValidateProperty(event) {
          var property = {};
          property.index = event.getData().index;
          property.fields = this.getPropertyItem(property.index);
          this.changeValidatedProperty(property);
        }
        /**
         * @private
         */

      }, {
        key: "getPropertyItem",
        value: function getPropertyItem(index) {
          return this.store.getters['property/get'](index);
        }
        /**
         * @private
         */

      }, {
        key: "changeValidatedProperty",
        value: function changeValidatedProperty(property) {
          var fields = property.fields;
          var errors = this.store.getters['property/getErrors'];

          if (this.propertyDataValidate(fields)) {
            errors = this.deletePropertyError(fields, errors);
          } else {
            errors = this.addPropertyError(fields, errors);
          }

          this.provider.setModelPropertyError(errors);
        }
        /**
         * @private
         */

      }, {
        key: "propertyDataValidate",
        value: function propertyDataValidate(fields) {
          return !(fields.required === 'Y' && fields.value === '');
        }
        /**
         * @private
         */

      }, {
        key: "deletePropertyError",
        value: function deletePropertyError(fields, errors) {
          for (var errorIndex in errors) {
            if (errors[errorIndex]['propertyId'] === fields.id) {
              errors.splice(errorIndex, 1);
            }
          }

          return errors;
        }
        /**
         * @private
         */

      }, {
        key: "addPropertyError",
        value: function addPropertyError(fields, errors) {
          var errorIds = errors.map(function (item) {
            return item.propertyId;
          });

          if (!errorIds.includes(fields.id)) {
            errors.push({
              propertyId: fields.id
            });
          }

          return errors;
        }
        /**
         * @private
         */

      }, {
        key: "getPropertyList",
        value: function getPropertyList() {
          var result = [];
          var list = this.store.getters['property/getProperty'];

          try {
            for (var key in list) {
              if (!list.hasOwnProperty(key)) {
                continue;
              }

              result[list[key].id] = list[key];
            }
          } catch (e) {}

          return result;
        }
        /**
         * @private
         */

      }, {
        key: "preparePropertyFields",
        value: function preparePropertyFields(list) {
          var fields = {};
          list.forEach(function (property, inx) {
            fields[inx] = property.value;
          });
          return fields;
        }
      }]);
      return Application;
    }();

    exports.Basket = Basket;
    exports.Application = Application;

}((this.BX.Sale.Checkout.Controller = this.BX.Sale.Checkout.Controller || {}),BX,BX.Event,BX.Sale.Checkout.Provider,BX.Sale.Checkout.Const,BX.Sale.Checkout.Lib));
//# sourceMappingURL=controller.bundle.js.map
