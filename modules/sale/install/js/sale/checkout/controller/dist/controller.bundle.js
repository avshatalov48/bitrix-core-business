this.BX = this.BX || {};
this.BX.Sale = this.BX.Sale || {};
this.BX.Sale.Checkout = this.BX.Sale.Checkout || {};
(function (exports,main_core,sale_checkout_provider_rest,sale_checkout_const,sale_checkout_lib) {
    'use strict';

    var Basket = /*#__PURE__*/function () {
      function Basket() {
        babelHelpers.classCallCheck(this, Basket);
        this.pool = new sale_checkout_lib.Pool();
        this.timer = new sale_checkout_lib.Timer();
        this.running = 'N';
      }

      babelHelpers.createClass(Basket, [{
        key: "isRunning",
        value: function isRunning() {
          return this.running === 'Y';
        }
      }, {
        key: "setRunningY",
        value: function setRunningY() {
          this.running = 'Y';
        }
      }, {
        key: "setRunningN",
        value: function setRunningN() {
          this.running = 'N';
        }
      }, {
        key: "setStore",
        value: function setStore(store) {
          this.store = store;
          return this;
        }
      }, {
        key: "setProvider",
        value: function setProvider(provider) {
          this.provider = provider;
          return this;
        }
      }, {
        key: "executeRestAnswer",
        value: function executeRestAnswer(command, result, extra) {
          return this.provider.execute(command, result, extra);
        }
      }, {
        key: "getItem",
        value: function getItem(index) {
          return this.store.getters['basket/get'](index);
        }
      }, {
        key: "getBasket",
        value: function getBasket() {
          return this.store.getters['basket/getBasket'];
        }
      }, {
        key: "changeItem",
        value: function changeItem(product) {
          this.store.dispatch('basket/changeItem', {
            index: product.index,
            fields: product.fields
          });
        }
      }, {
        key: "setQuantity",
        value: function setQuantity(index, quantity) {
          var fields = this.getItem(index);
          fields.quantity = quantity;
          fields.baseSum = this.round(fields.basePrice * fields.quantity);
          fields.sum = this.round(fields.price * fields.quantity);
          fields.discount.sum = this.round(fields.discount.price * fields.quantity);
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
        key: "removeItem",
        value: function removeItem(product) {
          return this.store.dispatch('basket/removeItem', {
            index: product.index
          });
        }
      }, {
        key: "round",
        value: function round(value) {
          var precision = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : 10;
          var factor = Math.pow(10, precision);
          return Math.round(value * factor) / factor;
        }
      }, {
        key: "handlerOrderSuccess",
        value: function handlerOrderSuccess() {
          BX.onCustomEvent('OnBasketChange');
        }
      }, {
        key: "handlerRemove",
        value: function handlerRemove(event) {
          var index = event.getData().index;
          var fields = this.getItem(index);
          fields.deleted = 'Y';
          fields.status = sale_checkout_const.Loader.status.wait;
          this.pool.add(sale_checkout_const.Pool.action.delete, index, {
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

          this.pool.add(sale_checkout_const.Pool.action.restore, index, fields);
          this.changeItem({
            index: index,
            fields: fields
          });
          this.shelveCommit();
        }
      }, {
        key: "handlerQuantityPlus",
        value: function handlerQuantityPlus(event) {
          var index = event.getData().index;
          var fields = this.getItem(index);
          var quantity = fields.quantity;
          var ratio = fields.product.ratio;
          var available = fields.product.availableQuantity;
          quantity = quantity + ratio;

          if (available > 0 && quantity > available) {
            quantity = available;
          }

          quantity = sale_checkout_lib.Basket.toFixed(quantity, ratio, available);

          if (fields.quantity < quantity) {
            this.setQuantity(index, quantity);
          }
        }
      }, {
        key: "handlerQuantityMinus",
        value: function handlerQuantityMinus(event) {
          var index = event.getData().index;
          var fields = this.getItem(index);
          var quantity = fields.quantity;
          var ratio = fields.product.ratio;
          var available = fields.product.availableQuantity;
          quantity = quantity - ratio;

          if (ratio > 0 && quantity < ratio) {
            quantity = ratio;
          }

          if (available > 0 && quantity > available) {
            quantity = available;
          }

          quantity = sale_checkout_lib.Basket.toFixed(quantity, ratio, available);

          if (quantity >= ratio) {
            this.setQuantity(index, quantity);
          }
        }
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
              }).catch();
            } else {
              resolve();
            }
          });
        }
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
      }, {
        key: "getStatus",
        value: function getStatus() {
          return this.store.getters['basket/getStatus'];
        }
      }, {
        key: "setStatusWait",
        value: function setStatusWait() {
          var app = {
            status: sale_checkout_const.Loader.status.wait
          };
          return this.store.dispatch('basket/setStatus', app);
        }
      }, {
        key: "setStatusNone",
        value: function setStatusNone() {
          var app = {
            status: sale_checkout_const.Loader.status.none
          };
          return this.store.dispatch('basket/setStatus', app);
        }
      }, {
        key: "handlerNeedRefreshY",
        value: function handlerNeedRefreshY() {
          this.setNeedRefreshY();
          this.setStatusWait();
        }
      }, {
        key: "handlerNeedRefreshN",
        value: function handlerNeedRefreshN() {
          this.setNeedRefreshN();
          this.setStatusNone();
        }
      }, {
        key: "setNeedRefreshY",
        value: function setNeedRefreshY() {
          var app = {
            needRefresh: 'Y'
          };
          return this.store.dispatch('basket/setNeedRefresh', app);
        }
      }, {
        key: "setNeedRefreshN",
        value: function setNeedRefreshN() {
          var app = {
            needRefresh: 'N'
          };
          return this.store.dispatch('basket/setNeedRefresh', app);
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

      babelHelpers.createClass(Application, [{
        key: "init",
        value: function init(option) {
          this.store = option.store;
          this.timer = new sale_checkout_lib.Timer();
          return new Promise(function (resolve, reject) {
            return resolve();
          });
        }
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
      }, {
        key: "iniController",
        value: function iniController() {
          this.basket = new Basket().setStore(this.store).setProvider(this.provider);
          return new Promise(function (resolve, reject) {
            return resolve();
          });
        }
      }, {
        key: "executeRestAnswer",
        value: function executeRestAnswer(command, result, extra) {
          return this.provider.execute(command, result, extra);
        }
      }, {
        key: "subscribeToEvents",
        value: function subscribeToEvents() {
          var _this2 = this;

          main_core.Event.EventEmitter.subscribe(sale_checkout_const.EventType.order.success, function (e) {
            return _this2.basket.handlerOrderSuccess(e);
          }); // Event.EventEmitter.subscribe(EventType.basket.removeProduct, Runtime.debounce((e)=>this.basket.handlerSuccessRemove(e), 500, this));

          main_core.Event.EventEmitter.subscribe(sale_checkout_const.EventType.basket.buttonRemoveProduct, main_core.Runtime.debounce(function (e) {
            return _this2.basket.handlerRemove(e);
          }, 500, this));
          main_core.Event.EventEmitter.subscribe(sale_checkout_const.EventType.basket.buttonPlusProduct, function (e) {
            return _this2.basket.handlerQuantityPlus(e);
          });
          main_core.Event.EventEmitter.subscribe(sale_checkout_const.EventType.basket.buttonMinusProduct, function (e) {
            return _this2.basket.handlerQuantityMinus(e);
          });
          main_core.Event.EventEmitter.subscribe(sale_checkout_const.EventType.basket.buttonRestoreProduct, main_core.Runtime.debounce(function (e) {
            return _this2.basket.handlerRestore(e);
          }, 500, this));
          main_core.Event.EventEmitter.subscribe(sale_checkout_const.EventType.basket.needRefresh, function (e) {
            return _this2.basket.handlerNeedRefreshY(e);
          });
          main_core.Event.EventEmitter.subscribe(sale_checkout_const.EventType.basket.refreshAfter, function (e) {
            return _this2.basket.handlerNeedRefreshN(e);
          }); // Event.EventEmitter.subscribe(EventType.property.validate,           (e) => this.handlerValidateProperty(e));

          main_core.Event.EventEmitter.subscribe(sale_checkout_const.EventType.consent.refused, function () {
            return _this2.handlerConsentRefused();
          });
          main_core.Event.EventEmitter.subscribe(sale_checkout_const.EventType.consent.accepted, function () {
            return _this2.handlerConsentAccepted();
          }); // Event.EventEmitter.subscribe(EventType.application.none, () => this.handlerApplicationStatusNone());
          // Event.EventEmitter.subscribe(EventType.application.wait, () => this.handlerApplicationStatusWait());

          main_core.Event.EventEmitter.subscribe(sale_checkout_const.EventType.element.buttonCheckout, main_core.Runtime.debounce(function () {
            return _this2.handlerCheckout();
          }, 1000, this));
          main_core.Event.EventEmitter.subscribe(sale_checkout_const.EventType.element.buttonShipping, main_core.Runtime.debounce(function () {
            return _this2.handlerShipping();
          }, 1000, this));
          main_core.Event.EventEmitter.subscribe(sale_checkout_const.EventType.paysystem.beforeInitList, function () {
            return _this2.paySystemSetStatusWait();
          });
          main_core.Event.EventEmitter.subscribe(sale_checkout_const.EventType.paysystem.afterInitList, function () {
            return _this2.paySystemSetStatusNone();
          });
          return new Promise(function (resolve, reject) {
            return resolve();
          });
        }
      }, {
        key: "subscribeToStoreChanges",
        value: function subscribeToStoreChanges() {
          // this.store.subscribe((mutation, state) => {
          //     const { payload, type } = mutation;
          //     if (type === 'basket/setNeedRefresh')
          //     {
          //     	alert('@@');
          //     	this.getData();
          //     }
          // });
          return new Promise(function (resolve, reject) {
            return resolve();
          });
        }
      }, {
        key: "paySystemSetStatusWait",
        value: function paySystemSetStatusWait() {
          var paySystem = {
            status: sale_checkout_const.Loader.status.wait
          };
          return this.store.dispatch('pay-system/setStatus', paySystem);
        }
      }, {
        key: "paySystemSetStatusNone",
        value: function paySystemSetStatusNone() {
          var paySystem = {
            status: sale_checkout_const.Loader.status.none
          };
          return this.store.dispatch('pay-system/setStatus', paySystem);
        }
      }, {
        key: "appSetStatusWait",
        value: function appSetStatusWait() {
          var app = {
            status: sale_checkout_const.Loader.status.wait
          };
          return this.store.dispatch('application/setStatus', app);
        }
      }, {
        key: "appSetStatusNone",
        value: function appSetStatusNone() {
          var app = {
            status: sale_checkout_const.Loader.status.none
          };
          return this.store.dispatch('application/setStatus', app);
        }
      }, {
        key: "handlerConsentAccepted",
        value: function handlerConsentAccepted() {
          this.store.dispatch('consent/setStatus', sale_checkout_const.Consent.status.accepted);
        }
      }, {
        key: "handlerConsentRefused",
        value: function handlerConsentRefused() {
          this.store.dispatch('consent/setStatus', sale_checkout_const.Consent.status.refused);
        }
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
            }).catch(function () {
              return _this3.appSetStatusNone();
            });
          }
        }
      }, {
        key: "handlerShipping",
        value: function handlerShipping() {
          this.store.dispatch('application/setStage', {
            stage: sale_checkout_const.Application.stage.view
          }); // todo

          delete BX.UserConsent;
        }
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
          }).catch(function (result) {
            return _this4.executeRestAnswer(cmd, {
              error: result.errors
            });
          });
        }
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

}((this.BX.Sale.Checkout.Controller = this.BX.Sale.Checkout.Controller || {}),BX,BX.Sale.Checkout.Provider,BX.Sale.Checkout.Const,BX.Sale.Checkout.Lib));
//# sourceMappingURL=controller.bundle.js.map
