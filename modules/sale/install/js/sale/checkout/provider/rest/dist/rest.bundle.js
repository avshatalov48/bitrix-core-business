this.BX = this.BX || {};
this.BX.Sale = this.BX.Sale || {};
this.BX.Sale.Checkout = this.BX.Sale.Checkout || {};
(function (exports,main_core,main_core_events,sale_checkout_const) {
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

        if (babelHelpers["typeof"](params.store) === 'object' && params.store) {
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

    function _classPrivateMethodInitSpec(obj, privateSet) { _checkPrivateRedeclaration(obj, privateSet); privateSet.add(obj); }

    function _checkPrivateRedeclaration(obj, privateCollection) { if (privateCollection.has(obj)) { throw new TypeError("Cannot initialize the same private elements twice on an object"); } }

    function _classPrivateMethodGet(receiver, privateSet, fn) { if (!privateSet.has(receiver)) { throw new TypeError("attempted to get private field on non-instance"); } return fn; }

    var _setModelBasketByItem = /*#__PURE__*/new WeakSet();

    var _setModelBasketForActionError = /*#__PURE__*/new WeakSet();

    var _setModelBasketForAction = /*#__PURE__*/new WeakSet();

    var _hasErrorAction = /*#__PURE__*/new WeakSet();

    var _getAction = /*#__PURE__*/new WeakSet();

    var _getErrorsAction = /*#__PURE__*/new WeakSet();

    var _getTypeAction = /*#__PURE__*/new WeakSet();

    var _hasActionInPool = /*#__PURE__*/new WeakSet();

    var _hasActionInPoolItem = /*#__PURE__*/new WeakSet();

    var _findItemById = /*#__PURE__*/new WeakSet();

    var _changeBasketItem = /*#__PURE__*/new WeakSet();

    var _prepareBasketItemFields = /*#__PURE__*/new WeakSet();

    var _refreshModelBasketTotal = /*#__PURE__*/new WeakSet();

    var _refreshModelBasketDiscount = /*#__PURE__*/new WeakSet();

    var _refreshModelProperty = /*#__PURE__*/new WeakSet();

    var _refreshModelBasket = /*#__PURE__*/new WeakSet();

    var _prepareBasketErrors = /*#__PURE__*/new WeakSet();

    var _preparePropertyErrors = /*#__PURE__*/new WeakSet();

    var _prepareGeneralErrors = /*#__PURE__*/new WeakSet();

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

        _classPrivateMethodInitSpec(babelHelpers.assertThisInitialized(_this), _prepareGeneralErrors);

        _classPrivateMethodInitSpec(babelHelpers.assertThisInitialized(_this), _preparePropertyErrors);

        _classPrivateMethodInitSpec(babelHelpers.assertThisInitialized(_this), _prepareBasketErrors);

        _classPrivateMethodInitSpec(babelHelpers.assertThisInitialized(_this), _refreshModelBasket);

        _classPrivateMethodInitSpec(babelHelpers.assertThisInitialized(_this), _refreshModelProperty);

        _classPrivateMethodInitSpec(babelHelpers.assertThisInitialized(_this), _refreshModelBasketDiscount);

        _classPrivateMethodInitSpec(babelHelpers.assertThisInitialized(_this), _refreshModelBasketTotal);

        _classPrivateMethodInitSpec(babelHelpers.assertThisInitialized(_this), _prepareBasketItemFields);

        _classPrivateMethodInitSpec(babelHelpers.assertThisInitialized(_this), _changeBasketItem);

        _classPrivateMethodInitSpec(babelHelpers.assertThisInitialized(_this), _findItemById);

        _classPrivateMethodInitSpec(babelHelpers.assertThisInitialized(_this), _hasActionInPoolItem);

        _classPrivateMethodInitSpec(babelHelpers.assertThisInitialized(_this), _hasActionInPool);

        _classPrivateMethodInitSpec(babelHelpers.assertThisInitialized(_this), _getTypeAction);

        _classPrivateMethodInitSpec(babelHelpers.assertThisInitialized(_this), _getErrorsAction);

        _classPrivateMethodInitSpec(babelHelpers.assertThisInitialized(_this), _getAction);

        _classPrivateMethodInitSpec(babelHelpers.assertThisInitialized(_this), _hasErrorAction);

        _classPrivateMethodInitSpec(babelHelpers.assertThisInitialized(_this), _setModelBasketForAction);

        _classPrivateMethodInitSpec(babelHelpers.assertThisInitialized(_this), _setModelBasketForActionError);

        _classPrivateMethodInitSpec(babelHelpers.assertThisInitialized(_this), _setModelBasketByItem);

        return _this;
      }

      babelHelpers.createClass(BasketRestHandler, [{
        key: "handleRecalculateBasket",
        value: function handleRecalculateBasket(response, pool) {
          var _this2 = this;

          return new Promise(function (resolve, reject) {
            if (response.data.needFullRecalculation === 'Y') {
              main_core_events.EventEmitter.emit(sale_checkout_const.EventType.basket.needRefresh, {});
            }

            var needRefresh = _this2.store.getters['basket/getNeedRefresh'];

            _classPrivateMethodGet(_this2, _setModelBasketForAction, _setModelBasketForAction2).call(_this2, response.data, pool).then(function () {
              return resolve();
            });

            _classPrivateMethodGet(_this2, _setModelBasketForActionError, _setModelBasketForActionError2).call(_this2, response.data).then(function () {
              return resolve();
            });

            if (needRefresh === 'Y') {
              if (pool.isEmpty()) {
                _classPrivateMethodGet(_this2, _setModelBasketByItem, _setModelBasketByItem2).call(_this2, response.data, pool);

                main_core_events.EventEmitter.emit(sale_checkout_const.EventType.basket.refreshAfter, {});
              }
            }
          });
        }
      }, {
        key: "handleSaveOrderSuccess",
        value: function handleSaveOrderSuccess(data) {
          var _this3 = this;

          main_core_events.EventEmitter.emit(sale_checkout_const.EventType.order.success);
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
        key: "setModelPropertyError",
        value: function setModelPropertyError(properties) {
          var _this4 = this;

          if (main_core.Type.isArrayFilled(properties)) {
            this.store.commit('property/setErrors', properties);
            this.store.getters['property/getProperty'].forEach(function (fields, index) {
              if (typeof properties.find(function (item) {
                return item.propertyId === fields.id;
              }) !== 'undefined') {
                fields.validated = sale_checkout_const.Property.validate.failure;
              } else {
                fields.validated = sale_checkout_const.Property.validate.unvalidated;
              }

              _this4.store.dispatch('property/changeItem', {
                index: index,
                fields: fields
              });
            });
          } else {
            this.store.commit('property/clearErrors');
            this.store.getters['property/getProperty'].forEach(function (fields, index) {
              fields.validated = sale_checkout_const.Property.validate.unvalidated;

              _this4.store.dispatch('property/changeItem', {
                index: index,
                fields: fields
              });
            });
          }
        }
      }, {
        key: "handleSaveOrderError",
        value: function handleSaveOrderError(errors) {
          var _this5 = this;

          return new Promise(function (resolve, reject) {
            if (main_core.Type.isArrayFilled(errors)) {
              var general = _classPrivateMethodGet(_this5, _prepareGeneralErrors, _prepareGeneralErrors2).call(_this5, errors);

              var properties = _classPrivateMethodGet(_this5, _preparePropertyErrors, _preparePropertyErrors2).call(_this5, errors);

              if (general.length > 0) {
                _this5.store.commit('application/setErrors', general);
              } else {
                _this5.store.commit('application/clearErrors');
              }

              _this5.setModelPropertyError(properties);
            }
          });
        }
      }]);
      return BasketRestHandler;
    }(BaseRestHandler);

    function _setModelBasketByItem2(data, pool) {
      var _this6 = this;

      return new Promise(function (resolve, reject) {
        if (main_core.Type.isObject(data) && main_core.Type.isArray(data.basketItems)) {
          var items = data.basketItems;
          var collection = _this6.store.getters['basket/getBasket']; //refresh

          collection.forEach(function (fields, index) {
            var item = _classPrivateMethodGet(_this6, _findItemById, _findItemById2).call(_this6, fields.id, items);

            if (main_core.Type.isObject(item)) {
              var _fields = _classPrivateMethodGet(_this6, _prepareBasketItemFields, _prepareBasketItemFields2).call(_this6, item);

              _classPrivateMethodGet(_this6, _changeBasketItem, _changeBasketItem2).call(_this6, _fields, index);
            }
          });

          if (main_core.Type.isObject(data) && main_core.Type.isObject(data.orderPriceTotal)) {
            _classPrivateMethodGet(_this6, _refreshModelBasketTotal, _refreshModelBasketTotal2).call(_this6, data);

            _classPrivateMethodGet(_this6, _refreshModelBasketDiscount, _refreshModelBasketDiscount2).call(_this6, data);
          }
        }

        resolve();
      });
    }

    function _setModelBasketForActionError2(data) {
      var _this7 = this;

      return new Promise(function (resolve, reject) {
        if (main_core.Type.isObject(data) && main_core.Type.isObject(data.actions)) {
          var actions = data.actions;
          var collection = _this7.store.getters['basket/getBasket'];

          var list = _classPrivateMethodGet(_this7, _prepareBasketErrors, _prepareBasketErrors2).call(_this7, collection, actions);

          if (list.length > 0) {
            _this7.store.commit('basket/setErrors', list);
          } else {
            _this7.store.commit('basket/clearErrors');
          }
        }

        resolve();
      });
    }

    function _setModelBasketForAction2(data, pool) {
      var _this8 = this;

      return new Promise(function (resolve, reject) {
        if (main_core.Type.isObject(data) && main_core.Type.isArray(data.basketItems)) {
          var items = data.basketItems;
          var actions = data.actions;
          var collection = _this8.store.getters['basket/getBasket'];
          var poolList = pool.get();
          collection.forEach(function (fields, index) {
            var item;

            var typeAction = _classPrivateMethodGet(_this8, _getTypeAction, _getTypeAction2).call(_this8, actions, index);

            if (main_core.Type.isString(typeAction)) {
              if (typeAction === sale_checkout_const.Pool.action.quantity) {
                item = null; //not refresh

                var exists = _classPrivateMethodGet(_this8, _hasActionInPool, _hasActionInPool2).call(_this8, index, sale_checkout_const.Pool.action.quantity, poolList);

                if (exists === false) {
                  item = _classPrivateMethodGet(_this8, _findItemById, _findItemById2).call(_this8, fields.id, items);
                }
              } else if (typeAction === sale_checkout_const.Pool.action.restore) {
                item = _classPrivateMethodGet(_this8, _findItemById, _findItemById2).call(_this8, actions[index].fields.id, items);
              } else if (typeAction === sale_checkout_const.Pool.action["delete"]) {
                fields.status = sale_checkout_const.Loader.status.none;

                _classPrivateMethodGet(_this8, _changeBasketItem, _changeBasketItem2).call(_this8, fields, index).then(function () {
                  return main_core_events.EventEmitter.emit(sale_checkout_const.EventType.basket.removeProduct, {
                    index: index
                  });
                });
              } else if (typeAction === sale_checkout_const.Pool.action.offer) {
                item = null; //not refresh

                var _exists = _classPrivateMethodGet(_this8, _hasActionInPool, _hasActionInPool2).call(_this8, index, sale_checkout_const.Pool.action.offer, poolList);

                if (_exists === false) {
                  item = _classPrivateMethodGet(_this8, _findItemById, _findItemById2).call(_this8, fields.id, items);
                }
              }

              if (main_core.Type.isObject(item)) {
                var _fields2 = _classPrivateMethodGet(_this8, _prepareBasketItemFields, _prepareBasketItemFields2).call(_this8, item);

                _fields2.status = sale_checkout_const.Loader.status.none;

                _classPrivateMethodGet(_this8, _changeBasketItem, _changeBasketItem2).call(_this8, _fields2, index).then(function () {
                  if (typeAction === sale_checkout_const.Pool.action.restore) {
                    main_core_events.EventEmitter.emit(sale_checkout_const.EventType.basket.restoreProduct, {
                      index: index
                    });
                  }
                });
              }
            }
          });

          if (main_core.Type.isObject(data) && main_core.Type.isObject(data.orderPriceTotal)) {
            _classPrivateMethodGet(_this8, _refreshModelBasketTotal, _refreshModelBasketTotal2).call(_this8, data);

            _classPrivateMethodGet(_this8, _refreshModelBasketDiscount, _refreshModelBasketDiscount2).call(_this8, data);
          }
        }

        resolve();
      });
    }

    function _getAction2(actions, index) {
      return actions.hasOwnProperty(index) ? actions[index] : null;
    }

    function _getErrorsAction2(actions, index) {
      var action = _classPrivateMethodGet(this, _getAction, _getAction2).call(this, actions, index);

      if (action !== null) {
        return action.hasOwnProperty('errors') ? action.errors : null;
      } else {
        return null;
      }
    }

    function _getTypeAction2(actions, index) {
      var types = Object.values(sale_checkout_const.Pool.action);

      var action = _classPrivateMethodGet(this, _getAction, _getAction2).call(this, actions, index);

      if (action !== null) {
        var type = action.type.toString();
        return types.includes(type) ? type : null;
      }

      return null;
    }

    function _hasActionInPool2(index, type, poolList) {
      var item = poolList.hasOwnProperty(index) ? poolList[index] : null;

      if (main_core.Type.isArray(item)) {
        return _classPrivateMethodGet(this, _hasActionInPoolItem, _hasActionInPoolItem2).call(this, item, type);
      }

      return false;
    }

    function _hasActionInPoolItem2(item, type) {
      return item.some(function (item) {
        return item.hasOwnProperty(type);
      });
    }

    function _findItemById2(id, items) {
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
    }

    function _changeBasketItem2(fields, index) {
      return this.store.dispatch('basket/changeItem', {
        index: index,
        fields: fields
      });
    }

    function _prepareBasketItemFields2(item) {
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
        sku: item.sku,
        product: {
          id: item.catalogProduct.id,
          detailPageUrl: item.detailPageUrl,
          picture: main_core.Type.isObject(item.catalogProduct.frontImage) ? item.catalogProduct.frontImage.src : null,
          ratio: item.catalogProduct.ratio,
          availableQuantity: item.catalogProduct.availableQuantity,
          type: item.catalogProduct.type,
          checkMaxQuantity: item.catalogProduct.checkMaxQuantity
        }
      };
    }

    function _refreshModelBasketTotal2(data) {
      var total = data.orderPriceTotal;
      this.store.dispatch('basket/setTotal', {
        price: total.orderPrice,
        basePrice: total.priceWithoutDiscountValue
      });
    }

    function _refreshModelBasketDiscount2(data) {
      var total = data.orderPriceTotal;
      this.store.dispatch('basket/setDiscount', {
        sum: total.basketPriceDiscountDiffValue
      });
    }

    function _refreshModelProperty2(data) {
      var _this9 = this;

      this.store.commit('property/clearProperty');

      if (main_core.Type.isObject(data) && main_core.Type.isArray(data.properties)) {
        data.properties.forEach(function (item, index) {
          var fields = {
            id: item.id,
            name: item.name,
            type: item.type,
            value: item.value[0] //TODO

          };

          _this9.store.dispatch('property/changeItem', {
            index: index,
            fields: fields
          });
        });
      }
    }

    function _refreshModelBasket2(data) {
      var _this10 = this;

      return new Promise(function (resolve, reject) {
        _this10.store.commit('basket/clearBasket');

        if (main_core.Type.isObject(data) && main_core.Type.isArray(data.basketItems)) {
          var items = data.basketItems;
          items.forEach(function (item, index) {
            var fields = _classPrivateMethodGet(_this10, _prepareBasketItemFields, _prepareBasketItemFields2).call(_this10, item);

            _classPrivateMethodGet(_this10, _changeBasketItem, _changeBasketItem2).call(_this10, fields, index);
          });
        }

        if (main_core.Type.isObject(data) && main_core.Type.isObject(data.orderPriceTotal)) {
          _classPrivateMethodGet(_this10, _refreshModelBasketTotal, _refreshModelBasketTotal2).call(_this10, data);

          _classPrivateMethodGet(_this10, _refreshModelBasketDiscount, _refreshModelBasketDiscount2).call(_this10, data);
        }

        resolve();
      });
    }

    function _prepareBasketErrors2(collection, actions) {
      var _this11 = this;

      var result = [];
      collection.forEach(function (fields, index) {
        var list = _classPrivateMethodGet(_this11, _getErrorsAction, _getErrorsAction2).call(_this11, actions, index);

        if (list !== null) {
          result.push({
            list: list,
            index: index
          });
        }
      });
      return result;
    }

    function _preparePropertyErrors2(errors) {
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
    }

    function _prepareGeneralErrors2(errors) {
      var result = [];
      errors.forEach(function (fields) {
        if (parseInt(fields.code) === 0 || fields.code === 'ORDER') {
          result.push({
            message: fields.message
          });
        }
      });
      return result;
    }

    exports.BasketRestHandler = BasketRestHandler;

}((this.BX.Sale.Checkout.Provider = this.BX.Sale.Checkout.Provider || {}),BX,BX.Event,BX.Sale.Checkout.Const));
//# sourceMappingURL=rest.bundle.js.map
