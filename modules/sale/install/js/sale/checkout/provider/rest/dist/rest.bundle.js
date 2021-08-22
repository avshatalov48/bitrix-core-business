this.BX = this.BX || {};
this.BX.Sale = this.BX.Sale || {};
this.BX.Sale.Checkout = this.BX.Sale.Checkout || {};
(function (exports,main_core,sale_checkout_const) {
    'use strict';

    var BaseRestHandler = /*#__PURE__*/function () {
      babelHelpers.createClass(BaseRestHandler, null, [{
        key: "create",
        value: function create() {
          var params = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};
          return new this(params);
        }
      }]);

      function BaseRestHandler() {
        var params = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};
        babelHelpers.classCallCheck(this, BaseRestHandler);

        if (babelHelpers.typeof(params.store) === 'object' && params.store) {
          this.store = params.store;
        }
      }

      babelHelpers.createClass(BaseRestHandler, [{
        key: "execute",
        value: function execute(command, result) {
          var extra = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : {};
          command = 'handle' + command.split('.').map(function (element) {
            return element.charAt(0).toUpperCase() + element.slice(1);
          }).join('');

          if (result.error) {
            if (typeof this[command + 'Error'] === 'function') {
              return this[command + 'Error'](result.error, extra);
            }
          } else {
            if (typeof this[command + 'Success'] === 'function') {
              return this[command + 'Success'](result.data, extra);
            }
          }

          return typeof this[command] === 'function' ? this[command](result, extra) : null;
        }
      }]);
      return BaseRestHandler;
    }();

    function _classPrivateMethodGet(receiver, privateSet, fn) { if (!privateSet.has(receiver)) { throw new TypeError("attempted to get private field on non-instance"); } return fn; }

    var _setModelBasketByItem = new WeakSet();

    var _setModelBasketForAction = new WeakSet();

    var _getTypeAction = new WeakSet();

    var _hasActionInPool = new WeakSet();

    var _hasActionInPoolItem = new WeakSet();

    var _findItemById = new WeakSet();

    var _changeBasketItem = new WeakSet();

    var _prepareBasketItemFields = new WeakSet();

    var _refreshModelBasketTotal = new WeakSet();

    var _refreshModelBasketDiscount = new WeakSet();

    var _refreshModelProperty = new WeakSet();

    var _refreshModelBasket = new WeakSet();

    var _preparePropertyErrors = new WeakSet();

    var _prepareGeneralErrors = new WeakSet();

    var BasketRestHandler = /*#__PURE__*/function (_BaseRestHandler) {
      babelHelpers.inherits(BasketRestHandler, _BaseRestHandler);

      function BasketRestHandler() {
        var _babelHelpers$getProt;

        var _this;

        babelHelpers.classCallCheck(this, BasketRestHandler);

        for (var _len = arguments.length, args = new Array(_len), _key = 0; _key < _len; _key++) {
          args[_key] = arguments[_key];
        }

        _this = babelHelpers.possibleConstructorReturn(this, (_babelHelpers$getProt = babelHelpers.getPrototypeOf(BasketRestHandler)).call.apply(_babelHelpers$getProt, [this].concat(args)));

        _prepareGeneralErrors.add(babelHelpers.assertThisInitialized(_this));

        _preparePropertyErrors.add(babelHelpers.assertThisInitialized(_this));

        _refreshModelBasket.add(babelHelpers.assertThisInitialized(_this));

        _refreshModelProperty.add(babelHelpers.assertThisInitialized(_this));

        _refreshModelBasketDiscount.add(babelHelpers.assertThisInitialized(_this));

        _refreshModelBasketTotal.add(babelHelpers.assertThisInitialized(_this));

        _prepareBasketItemFields.add(babelHelpers.assertThisInitialized(_this));

        _changeBasketItem.add(babelHelpers.assertThisInitialized(_this));

        _findItemById.add(babelHelpers.assertThisInitialized(_this));

        _hasActionInPoolItem.add(babelHelpers.assertThisInitialized(_this));

        _hasActionInPool.add(babelHelpers.assertThisInitialized(_this));

        _getTypeAction.add(babelHelpers.assertThisInitialized(_this));

        _setModelBasketForAction.add(babelHelpers.assertThisInitialized(_this));

        _setModelBasketByItem.add(babelHelpers.assertThisInitialized(_this));

        return _this;
      }

      babelHelpers.createClass(BasketRestHandler, [{
        key: "handleRecalculateBasket",
        value: function handleRecalculateBasket(response, pool) {
          var _this2 = this;

          return new Promise(function (resolve, reject) {
            if (response.data.needFullRecalculation === 'Y') {
              main_core.Event.EventEmitter.emit(sale_checkout_const.EventType.basket.needRefresh, {});
            }

            var needRefresh = _this2.store.getters['basket/getNeedRefresh'];

            _classPrivateMethodGet(_this2, _setModelBasketForAction, _setModelBasketForAction2).call(_this2, response.data, pool).then(function () {
              return resolve();
            });

            if (needRefresh === 'Y') {
              if (pool.isEmpty()) {
                _classPrivateMethodGet(_this2, _setModelBasketByItem, _setModelBasketByItem2).call(_this2, response.data, pool);

                main_core.Event.EventEmitter.emit(sale_checkout_const.EventType.basket.refreshAfter, {});
              }
            }
          });
        }
      }, {
        key: "handleSaveOrderSuccess",
        value: function handleSaveOrderSuccess(data) {
          var _this3 = this;

          main_core.Event.EventEmitter.emit(sale_checkout_const.EventType.order.success);
          this.store.dispatch('application/setStage', {
            stage: sale_checkout_const.Application.stage.success
          });
          this.store.dispatch('order/set', {
            id: data.order.id,
            hash: data.hash,
            accountNumber: data.order.accountNumber
          });
          return _classPrivateMethodGet(this, _refreshModelBasket, _refreshModelBasket2).call(this, data).then(function () {
            return _classPrivateMethodGet(_this3, _refreshModelProperty, _refreshModelProperty2).call(_this3, data);
          });
        }
      }, {
        key: "handleSaveOrderError",
        value: function handleSaveOrderError(errors) {
          var _this4 = this;

          return new Promise(function (resolve, reject) {
            if (main_core.Type.isArrayFilled(errors)) {
              var general = _classPrivateMethodGet(_this4, _prepareGeneralErrors, _prepareGeneralErrors2).call(_this4, errors);

              var properties = _classPrivateMethodGet(_this4, _preparePropertyErrors, _preparePropertyErrors2).call(_this4, errors);

              if (general.length > 0) {
                _this4.store.commit('application/setErrors', general);
              } else {
                _this4.store.commit('application/clearErrors');
              }

              if (properties.length > 0) {
                _this4.store.commit('property/setErrors', properties);

                _this4.store.getters['property/getProperty'].forEach(function (fields, index) {
                  if (typeof properties.find(function (item) {
                    return item.propertyId === fields.id;
                  }) !== 'undefined') {
                    fields.validated = sale_checkout_const.Property.validate.failure;
                  } else {
                    if (fields.validated !== sale_checkout_const.Property.validate.unvalidated) {
                      fields.validated = sale_checkout_const.Property.validate.successful;
                    }
                  }

                  _this4.store.dispatch('property/changeItem', {
                    index: index,
                    fields: fields
                  });
                });
              } else {
                _this4.store.commit('property/clearErrors');

                _this4.store.getters['property/getProperty'].forEach(function (fields, index) {
                  if (fields.validated !== sale_checkout_const.Property.validate.unvalidated) {
                    fields.validated = sale_checkout_const.Property.validate.successful;
                  }

                  _this4.store.dispatch('property/changeItem', {
                    index: index,
                    fields: fields
                  });
                });
              }
            }
          });
        }
      }]);
      return BasketRestHandler;
    }(BaseRestHandler);

    var _setModelBasketByItem2 = function _setModelBasketByItem2(data, pool) {
      var _this5 = this;

      return new Promise(function (resolve, reject) {
        if (main_core.Type.isObject(data) && main_core.Type.isArray(data.basketItems)) {
          var items = data.basketItems;
          var collection = _this5.store.getters['basket/getBasket']; //refresh

          collection.forEach(function (fields, index) {
            var item = _classPrivateMethodGet(_this5, _findItemById, _findItemById2).call(_this5, fields.id, items);

            if (main_core.Type.isObject(item)) {
              var _fields = _classPrivateMethodGet(_this5, _prepareBasketItemFields, _prepareBasketItemFields2).call(_this5, item);

              _classPrivateMethodGet(_this5, _changeBasketItem, _changeBasketItem2).call(_this5, _fields, index);
            }
          });

          if (main_core.Type.isObject(data) && main_core.Type.isObject(data.orderPriceTotal)) {
            _classPrivateMethodGet(_this5, _refreshModelBasketTotal, _refreshModelBasketTotal2).call(_this5, data);

            _classPrivateMethodGet(_this5, _refreshModelBasketDiscount, _refreshModelBasketDiscount2).call(_this5, data);
          }
        }

        resolve();
      });
    };

    var _setModelBasketForAction2 = function _setModelBasketForAction2(data, pool) {
      var _this6 = this;

      return new Promise(function (resolve, reject) {
        if (main_core.Type.isObject(data) && main_core.Type.isArray(data.basketItems)) {
          var items = data.basketItems;
          var actions = data.actions;
          var collection = _this6.store.getters['basket/getBasket'];
          var poolList = pool.get();
          collection.forEach(function (fields, index) {
            var item;

            var action = _classPrivateMethodGet(_this6, _getTypeAction, _getTypeAction2).call(_this6, actions, index);

            if (main_core.Type.isString(action)) {
              if (action === sale_checkout_const.Pool.action.quantity) {
                item = null; //not refresh

                var exists = _classPrivateMethodGet(_this6, _hasActionInPool, _hasActionInPool2).call(_this6, index, sale_checkout_const.Pool.action.quantity, poolList);

                if (exists === false) {
                  item = _classPrivateMethodGet(_this6, _findItemById, _findItemById2).call(_this6, fields.id, items);
                }
              } else if (action === sale_checkout_const.Pool.action.restore) {
                item = _classPrivateMethodGet(_this6, _findItemById, _findItemById2).call(_this6, actions[index].fields.id, items);
              } else if (action === sale_checkout_const.Pool.action.delete) {
                fields.status = sale_checkout_const.Loader.status.none;

                _classPrivateMethodGet(_this6, _changeBasketItem, _changeBasketItem2).call(_this6, fields, index).then(function () {
                  return main_core.Event.EventEmitter.emit(sale_checkout_const.EventType.basket.removeProduct, {
                    index: index
                  });
                });
              }

              if (main_core.Type.isObject(item)) {
                var _fields2 = _classPrivateMethodGet(_this6, _prepareBasketItemFields, _prepareBasketItemFields2).call(_this6, item);

                _fields2.status = sale_checkout_const.Loader.status.none;

                _classPrivateMethodGet(_this6, _changeBasketItem, _changeBasketItem2).call(_this6, _fields2, index);
              }
            }
          });

          if (main_core.Type.isObject(data) && main_core.Type.isObject(data.orderPriceTotal)) {
            _classPrivateMethodGet(_this6, _refreshModelBasketTotal, _refreshModelBasketTotal2).call(_this6, data);

            _classPrivateMethodGet(_this6, _refreshModelBasketDiscount, _refreshModelBasketDiscount2).call(_this6, data);
          }
        }

        resolve();
      });
    };

    var _getTypeAction2 = function _getTypeAction2(actions, index) {
      var types = Object.values(sale_checkout_const.Pool.action);

      if (actions.hasOwnProperty(index)) {
        var type = actions[index].type.toString();
        return types.includes(type) ? type : null;
      }

      return null;
    };

    var _hasActionInPool2 = function _hasActionInPool2(index, type, poolList) {
      var item = poolList.hasOwnProperty(index) ? poolList[index] : null;

      if (main_core.Type.isArray(item)) {
        return _classPrivateMethodGet(this, _hasActionInPoolItem, _hasActionInPoolItem2).call(this, item, type);
      }

      return false;
    };

    var _hasActionInPoolItem2 = function _hasActionInPoolItem2(item, type) {
      return item.some(function (item) {
        return item.hasOwnProperty(type);
      });
    };

    var _findItemById2 = function _findItemById2(id, items) {
      id = parseInt(id);

      for (var index in items) {
        if (!items.hasOwnProperty(index)) {
          continue;
        }

        items[index].id = parseInt(items[index].id);

        if (items[index].id === id) {
          return items[index];
        }
      }

      return null;
    };

    var _changeBasketItem2 = function _changeBasketItem2(fields, index) {
      return this.store.dispatch('basket/changeItem', {
        index: index,
        fields: fields
      });
    };

    var _prepareBasketItemFields2 = function _prepareBasketItemFields2(item) {
      return {
        id: item.id,
        name: item.name,
        quantity: item.quantity,
        measureText: item.measureText,
        sum: item.sum,
        price: item.price,
        module: item.module,
        productProviderClass: item.productProviderClass,
        baseSum: item.sumBase,
        basePrice: item.basePrice,
        currency: item.currency,
        discount: {
          sum: item.sumDiscountDiff,
          price: item.discountPrice
        },
        props: item.props,
        product: {
          id: item.catalogProduct.id,
          detailPageUrl: item.detailPageUrl,
          picture: main_core.Type.isObject(item.catalogProduct.frontImage) ? item.catalogProduct.frontImage.src : null,
          ratio: item.catalogProduct.ratio,
          availableQuantity: item.catalogProduct.availableQuantity
        }
      };
    };

    var _refreshModelBasketTotal2 = function _refreshModelBasketTotal2(data) {
      var total = data.orderPriceTotal;
      this.store.dispatch('basket/setTotal', {
        price: total.orderPrice,
        basePrice: total.priceWithoutDiscountValue
      });
    };

    var _refreshModelBasketDiscount2 = function _refreshModelBasketDiscount2(data) {
      var total = data.orderPriceTotal;
      this.store.dispatch('basket/setDiscount', {
        sum: total.basketPriceDiscountDiffValue
      });
    };

    var _refreshModelProperty2 = function _refreshModelProperty2(data) {
      var _this7 = this;

      this.store.commit('property/clearProperty');

      if (main_core.Type.isObject(data) && main_core.Type.isArray(data.properties)) {
        data.properties.forEach(function (item, index) {
          var fields = {
            id: item.id,
            name: item.name,
            type: item.type,
            value: item.value[0] //TODO

          };

          _this7.store.dispatch('property/changeItem', {
            index: index,
            fields: fields
          });
        });
      }
    };

    var _refreshModelBasket2 = function _refreshModelBasket2(data) {
      var _this8 = this;

      return new Promise(function (resolve, reject) {
        _this8.store.commit('basket/clearBasket');

        if (main_core.Type.isObject(data) && main_core.Type.isArray(data.basketItems)) {
          var items = data.basketItems;
          items.forEach(function (item, index) {
            var fields = _classPrivateMethodGet(_this8, _prepareBasketItemFields, _prepareBasketItemFields2).call(_this8, item);

            _classPrivateMethodGet(_this8, _changeBasketItem, _changeBasketItem2).call(_this8, fields, index);
          });
        }

        if (main_core.Type.isObject(data) && main_core.Type.isObject(data.orderPriceTotal)) {
          _classPrivateMethodGet(_this8, _refreshModelBasketTotal, _refreshModelBasketTotal2).call(_this8, data);

          _classPrivateMethodGet(_this8, _refreshModelBasketDiscount, _refreshModelBasketDiscount2).call(_this8, data);
        }

        resolve();
      });
    };

    var _preparePropertyErrors2 = function _preparePropertyErrors2(errors) {
      var result = [];
      errors.forEach(function (fields) {
        if (fields.code === 'PROPERTIES') {
          if (fields.hasOwnProperty('customData')) {
            var id = parseInt(fields.customData.id);
            result.push({
              message: fields.message,
              propertyId: id
            });
          }
        }
      });
      return result;
    };

    var _prepareGeneralErrors2 = function _prepareGeneralErrors2(errors) {
      var result = [];
      errors.forEach(function (fields) {
        if (parseInt(fields.code) === 0 || fields.code === 'ORDER') {
          result.push({
            message: fields.message
          });
        }
      });
      return result;
    };

    exports.BasketRestHandler = BasketRestHandler;

}((this.BX.Sale.Checkout.Provider = this.BX.Sale.Checkout.Provider || {}),BX,BX.Sale.Checkout.Const));
//# sourceMappingURL=rest.bundle.js.map
