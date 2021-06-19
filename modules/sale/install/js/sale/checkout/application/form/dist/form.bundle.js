this.BX = this.BX || {};
this.BX.Sale = this.BX.Sale || {};
this.BX.Sale.Checkout = this.BX.Sale.Checkout || {};
(function (exports,sale_checkout_lib,main_core,ui_vue_vuex,sale_checkout_controller,sale_checkout_model,ui_vue,main_core_events,sale_checkout_const) {
	'use strict';

	ui_vue.Vue.component('sale-checkout-form', {
	  data: function data() {
	    return {
	      stage: sale_checkout_const.Application.stage,
	      mode: sale_checkout_const.Application.mode,
	      status: sale_checkout_const.Loader.status,
	      totalIsShow: 'N'
	    };
	  },
	  //showBackdrop
	  computed: {
	    checkoutButtonEnabled: function checkoutButtonEnabled() {
	      var properties = [];
	      var list = this.$store.getters['property/getProperty'];

	      for (var listKey in list) {
	        if (list[listKey].value.length > 0) {
	          properties.push(list[listKey].value);
	        }
	      }

	      return properties.length > 0;
	    },
	    hasPS: function hasPS() {
	      var result = [];
	      var list = this.$store.getters['pay-system/getPaySystem'];
	      list.forEach(function (fields) {
	        if (fields.type !== sale_checkout_const.PaySystem.type.cash) {
	          result.push(fields);
	        }
	      });
	      return result.length > 0;
	    },
	    needCheckConsent: function needCheckConsent() {
	      return this.getConsent.id > 0;
	    },
	    getBasket: function getBasket() {
	      return this.$store.getters['basket/getBasket'];
	    },
	    getOrder: function getOrder() {
	      return this.$store.getters['order/getOrder'];
	    },
	    getProperty: function getProperty() {
	      return this.$store.getters['property/getProperty'];
	    },
	    getPropertyErrors: function getPropertyErrors() {
	      return this.$store.getters['property/getErrors'];
	    },
	    getTotal: function getTotal() {
	      var total = this.$store.getters['basket/getTotal'];
	      return {
	        price: total.price,
	        basePrice: total.basePrice,
	        discount: this.$store.getters['basket/getDiscount'],
	        currency: this.$store.getters['basket/getCurrency']
	      };
	    },
	    getConsent: function getConsent() {
	      return this.$store.getters['consent/get'];
	    },
	    getStage: function getStage() {
	      return this.$store.getters['application/getStage'];
	    },
	    getStatus: function getStatus() {
	      return this.$store.getters['application/getStatus'];
	    },
	    getBasketConfig: function getBasketConfig() {
	      return {
	        status: this.$store.getters['basket/getStatus']
	      };
	    },
	    getPaymentConfig: function getPaymentConfig() {
	      return {
	        status: this.$store.getters['pay-system/getStatus'],
	        returnUrl: this.$store.getters['application/getPathLocation'],
	        mainPage: this.$store.getters['application/getPathMainPage']
	      };
	    },
	    getSuccessfulConfig: function getSuccessfulConfig() {
	      return {
	        mainPage: this.$store.getters['application/getPathMainPage']
	      };
	    },
	    getEmptyCartConfig: function getEmptyCartConfig() {
	      return {
	        path: this.$store.getters['application/getPath']
	      };
	    },
	    getTitleCheckoutButton: function getTitleCheckoutButton() {
	      return {
	        title: this.$store.getters['application/getTitleCheckoutButton']
	      };
	    },
	    getErrors: function getErrors() {
	      return this.$store.getters['application/getErrors'];
	    }
	  },
	  created: function created() {
	    var _this = this;

	    main_core_events.EventEmitter.subscribe(sale_checkout_const.EventType.basket.backdropTotalOpen, function (event) {
	      _this.totalIsShow = 'Y';
	    });
	    main_core_events.EventEmitter.subscribe(sale_checkout_const.EventType.basket.backdropTotalClose, function (event) {
	      _this.totalIsShow = 'N';
	    });
	  },
	  // language=Vue
	  template: "\n      <div class=\"checkout-container-wrapper\">\n\t\t  <div class=\"checkout-basket-container\">\n\t\t\t<template v-if=\"getStage === stage.edit\">\n\t\t\t  <sale-checkout-view-product :items=\"getBasket\" :total=\"getTotal\" :mode=\"mode.edit\" :config=\"getBasketConfig\"/>\n\t\t\t  <sale-checkout-view-property :items=\"getProperty\" :mode=\"mode.edit\" :errors=\"getPropertyErrors\"/>\n\t\t\t  <sale-checkout-view-user_consent :item=\"getConsent\" v-if=\"needCheckConsent\"/>\n\t\t\t  <template v-if=\"checkoutButtonEnabled\">\n\t\t\t\t<sale-checkout-view-element-button-checkout :title=\"getTitleCheckoutButton.title\" :wait=\"getStatus === status.wait\"/>\n\t\t\t  </template>\n\t\t\t  <template v-else>\n\t\t\t\t<sale-checkout-view-element-button-checkout_disabled :title=\"getTitleCheckoutButton.title\"/>\n\t\t\t  </template>\n\t\t\t  <sale-checkout-view-alert-list :errors=\"getErrors\"/>\n\t\t\t</template>\n\t\t\t<template v-else-if=\"getStage === stage.success\">\n\t\t\t  <template v-if=\"hasPS\">\n\t\t\t\t<sale-checkout-view-successful :items=\"getProperty\" :order=\"getOrder\" :config=\"getSuccessfulConfig\"/>\n\t\t\t  </template>\n\t\t\t  <template v-else>\n\t\t\t\t<sale-checkout-view-successful-without-ps :items=\"getProperty\" :order=\"getOrder\" :config=\"getSuccessfulConfig\"/>\n\t\t\t  </template>\n\t\t\t</template>\n\t\t\t<template v-else-if=\"getStage === stage.payed\">\n              <sale-checkout-view-successful_ps_return :items=\"getProperty\" :order=\"getOrder\" :total=\"getTotal\" :config=\"getSuccessfulConfig\"/>\n\t\t\t</template>\n\t\t\t<template v-else-if=\"getStage === stage.view\">\n\t\t\t  <sale-checkout-view-product :items=\"getBasket\" :total=\"getTotal\" :mode=\"mode.view\" :config=\"getBasketConfig\"/>\n\t\t\t  <sale-checkout-view-property :items=\"getProperty\" :mode=\"mode.view\" :order=\"getOrder\"/>\n\t\t\t  <sale-checkout-view-product-summary :total=\"getTotal\" :mode=\"mode.view\"/>\n\t\t\t  <sale-checkout-view-payment :order=\"getOrder\" :config=\"getPaymentConfig\"/>\n\t\t\t</template>\n\t\t\t<template v-else-if=\"getStage === stage.empty\">\n\t\t\t  <sale-checkout-view-empty_cart :config=\"getEmptyCartConfig\"/>\n\t\t\t</template>\n\t\t  </div>\n\t\t  <template v-if=\"getStage === stage.view\">\n\t\t\t<sale-checkout-view-total :total=\"getTotal\" :showBackdrop=\"totalIsShow\"/>\n\t\t  </template>\n      </div>\n\t"
	});

	function _templateObject() {
	  var data = babelHelpers.taggedTemplateLiteral(["<div class=\"\"></div>"]);

	  _templateObject = function _templateObject() {
	    return data;
	  };

	  return data;
	}
	var FormApplication = /*#__PURE__*/function () {
	  function FormApplication() {
	    var _this = this;

	    var options = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};
	    babelHelpers.classCallCheck(this, FormApplication);
	    this.wrapper = main_core.Tag.render(_templateObject());
	    this.init().then(function () {
	      return _this.prepareParams({
	        options: options
	      });
	    }).then(function () {
	      _this.initStore().then(function (result) {
	        return _this.initTemplate(result).then(function () {
	          return _this.initController();
	        });
	      }).catch(function (error) {
	        return FormApplication.showError(error);
	      });
	    });
	  }

	  babelHelpers.createClass(FormApplication, [{
	    key: "init",
	    value: function init() {
	      return Promise.resolve();
	    }
	  }, {
	    key: "prepareParams",
	    value: function prepareParams(params) {
	      this.options = params.options;
	      return Promise.resolve();
	    }
	  }, {
	    key: "initStore",
	    value: function initStore() {
	      var builder = new ui_vue_vuex.VuexBuilder();
	      var contextVariablesBasket = {
	        product: this.options.product
	      };
	      var contextVariablesApp = {
	        path: this.options.path,
	        common: this.options.common,
	        option: this.options.option,
	        messages: this.options.messages
	      };
	      contextVariablesApp.path.location = sale_checkout_lib.Url.getCurrentUrl();
	      return builder.addModel(sale_checkout_model.Order.create()).addModel(sale_checkout_model.Basket.create().setVariables(contextVariablesBasket)).addModel(sale_checkout_model.Property.create()).addModel(sale_checkout_model.PaySystem.create()).addModel(sale_checkout_model.Application.create().setVariables(contextVariablesApp)).addModel(sale_checkout_model.Consent.create()).build();
	    }
	  }, {
	    key: "layout",
	    value: function layout() {
	      return this.wrapper;
	    }
	  }, {
	    key: "initController",
	    value: function initController() {
	      this.controller = new sale_checkout_controller.Application({
	        store: this.store
	      });
	      return new Promise(function (resolve) {
	        return resolve();
	      });
	    }
	  }, {
	    key: "initTemplate",
	    value: function initTemplate(result) {
	      var _this2 = this;

	      return new Promise(function (resolve) {
	        var context = _this2;
	        _this2.store = result.store;
	        _this2.templateEngine = ui_vue.Vue.create({
	          el: _this2.wrapper,
	          store: _this2.store,
	          data: {
	            options: _this2.options
	          },
	          created: function created() {
	            this.$app = context;
	            var data = {};

	            if (context.options.basket.length > 0) {
	              data = {
	                order: this.options.order,
	                basket: this.options.basket,
	                paySystem: this.options.paySystem,
	                total: this.options.total,
	                currency: this.options.currency,
	                discount: this.options.discount,
	                property: this.options.property,
	                consent: this.options.consent,
	                consentStatus: this.options.consentStatus
	              };
	            }

	            data.stage = this.options.stage;
	            context.setModelData(data);
	          },
	          mounted: function mounted() {
	            resolve();
	          },
	          template: "<sale-checkout-form/>"
	        });
	      });
	    }
	  }, {
	    key: "setModelData",
	    value: function setModelData(data) {
	      var _this3 = this;

	      //region: application model
	      if (main_core.Type.isString(data.stage)) {
	        this.store.dispatch('application/setStage', {
	          stage: data.stage
	        });
	      } //endregion
	      //region: order model


	      if (main_core.Type.isObject(data.order)) {
	        this.store.dispatch('order/set', data.order);
	      } //endregion
	      //region: basket model


	      if (main_core.Type.isObject(data.basket)) {
	        data.basket.forEach(function (fields, index) {
	          _this3.store.dispatch('basket/changeItem', {
	            index: index,
	            fields: fields
	          });
	        });
	      }

	      if (main_core.Type.isString(data.currency)) {
	        this.store.dispatch('basket/setCurrency', {
	          currency: data.currency
	        });
	      }

	      if (main_core.Type.isObject(data.discount)) {
	        this.store.dispatch('basket/setDiscount', data.discount);
	      }

	      if (main_core.Type.isObject(data.total)) {
	        this.store.dispatch('basket/setTotal', data.total);
	      } //endregion
	      //region: property model


	      if (main_core.Type.isObject(data.property)) {
	        data.property.forEach(function (fields, index) {
	          _this3.store.dispatch('property/changeItem', {
	            index: index,
	            fields: fields
	          });
	        });
	      } //endregion
	      // region: paySystem model


	      if (main_core.Type.isObject(data.paySystem)) {
	        data.paySystem.forEach(function (fields, index) {
	          _this3.store.dispatch('pay-system/changeItem', {
	            index: index,
	            fields: fields
	          });
	        });
	      } //endregion
	      //region: consent


	      if (main_core.Type.isString(data.consentStatus)) {
	        this.store.dispatch('consent/setStatus', data.consentStatus);
	      }

	      if (main_core.Type.isObject(data.consent)) {
	        this.store.dispatch('consent/set', data.consent);
	      } //endregion
	      // region: errors


	      if (main_core.Type.isObject(data.errors)) {
	        this.store.commit('basket/setErrors', data.errors);
	      } //endregion

	    }
	  }], [{
	    key: "showError",
	    value: function showError(error) {
	      console.error(error);
	    }
	  }]);
	  return FormApplication;
	}();

	exports.FormApplication = FormApplication;

}((this.BX.Sale.Checkout.Application = this.BX.Sale.Checkout.Application || {}),BX.Sale.Checkout.Lib,BX,BX,BX.Sale.Checkout.Controller,BX.Sale.Checkout.Model,BX,BX.Event,BX.Sale.Checkout.Const));
//# sourceMappingURL=form.bundle.js.map
