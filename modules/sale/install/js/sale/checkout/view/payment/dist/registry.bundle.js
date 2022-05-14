this.BX = this.BX || {};
this.BX.Sale = this.BX.Sale || {};
this.BX.Sale.Checkout = this.BX.Sale.Checkout || {};
this.BX.Sale.Checkout.View = this.BX.Sale.Checkout.View || {};
(function (exports,ui_vue,main_core,currency_currencyCore) {
	'use strict';

	ui_vue.BitrixVue.component('sale-checkout-view-payment-pay_system_application', {
	  props: ['order', 'paySystems', 'config'],
	  methods: {
	    prepare: function prepare(items) {
	      var paymentProcess = {
	        returnUrl: this.config.returnUrl,
	        orderId: this.order.id,
	        accessCode: this.order.hash,
	        allowPaymentRedirect: true
	      };
	      var paySystems = items.map(function (item) {
	        return {
	          ID: item.id,
	          NAME: item.name,
	          LOGOTIP: item.picture
	        };
	      });
	      return {
	        app: {
	          paySystems: paySystems
	        },
	        paymentProcess: paymentProcess
	      };
	    }
	  },
	  // language=Vue
	  template: "\n\t\t<sale-payment_pay-components-application-pay_system :options=\"prepare(paySystems)\"/>\n\t"
	});

	ui_vue.BitrixVue.component('sale-checkout-view-payment-payment_paid_application', {
	  props: ['order', 'payments', 'paySystems', 'check', 'config'],
	  methods: {
	    getChecksByPaymentId: function getChecksByPaymentId(paymentId) {
	      var result = [];
	      var list = this.check;

	      if (main_core.Type.isArrayFilled(list)) {
	        list.forEach(function (fields) {
	          if (fields.paymentId === paymentId) {
	            result.push({
	              status: fields.status,
	              link: fields.link,
	              id: fields.id,
	              dateFormatted: fields.dateFormatted
	            });
	          }
	        });
	      }

	      return result;
	    },
	    getFirstPaymentPaidY: function getFirstPaymentPaidY() {
	      return this.payments[0];
	    },
	    getPaySystemById: function getPaySystemById(id) {
	      var paySystem = this.paySystems.find(function (item) {
	        return item.id === id;
	      });
	      return !!paySystem ? paySystem : null;
	    },
	    prepare: function prepare() {
	      var result = null;
	      var item = this.getFirstPaymentPaidY();

	      if (item !== null) {
	        var paySystem = this.getPaySystemById(item.paySystemId);
	        var list = [];
	        list.push({
	          ID: paySystem.id,
	          NAME: paySystem.name,
	          LOGOTIP: paySystem.picture
	        });
	        var app = {
	          paySystems: list,
	          title: this.getTitle(item)
	        };
	        var payment = {
	          sumFormatted: this.sumFormatted(item),
	          paid: item.paid === 'Y',
	          checks: this.getChecksByPaymentId(item.id)
	        };
	        var paymentProcess = {
	          returnUrl: this.config.returnUrl,
	          orderId: this.order.id,
	          accessCode: this.order.hash,
	          allowPaymentRedirect: true,
	          paymentId: item.id
	        };
	        result = {
	          app: app,
	          payment: payment,
	          paymentProcess: paymentProcess
	        };
	      }

	      return result;
	    },
	    sumFormatted: function sumFormatted(item) {
	      return currency_currencyCore.CurrencyCore.currencyFormat(item.sum, item.currency, true);
	    },
	    getTitle: function getTitle(item) {
	      return this.localize.CHECKOUT_VIEW_PAYMENT_PAYMENT_INFO.replace('#DATE_INSERT#', item.dateBillFormatted).replace('#ACCOUNT_NUMBER#', item.accountNumber);
	    }
	  },
	  computed: {
	    localize: function localize() {
	      return Object.freeze(ui_vue.BitrixVue.getFilteredPhrases('CHECKOUT_VIEW_PAYMENT'));
	    }
	  },
	  // language=Vue
	  template: "\n\t\t<sale-payment_pay-components-application-payment :options=\"prepare()\"/>\n\t"
	});

	ui_vue.BitrixVue.component('sale-checkout-view-payment', {
	  props: ['order', 'payments', 'paySystems', 'check', 'config'],
	  methods: {
	    hasPaymentPaidY: function hasPaymentPaidY() {
	      return this.getPaymentPaidY().length > 0;
	    },
	    getPaymentPaidY: function getPaymentPaidY() {
	      var result = [];
	      var list = this.payments;
	      list.forEach(function (fields) {
	        if (fields.paid !== 'N') {
	          result.push(fields);
	        }
	      });
	      return result;
	    }
	  },
	  // language=Vue
	  template: "\n\t  <div>\n\t  <template v-if=\"hasPaymentPaidY()\">\n        <sale-checkout-view-payment-payment_paid_application :order=\"order\" :payments=\"getPaymentPaidY()\" :paySystems=\"paySystems\" :check=\"check\" :config=\"config\"/>\n      </template>\n      <template v-else>\n        <sale-checkout-view-payment-pay_system_application :order=\"order\" :paySystems=\"paySystems\" :config=\"config\"/>\n\t  </template>\n\t</div>\n\t"
	});

}((this.BX.Sale.Checkout.View.Payment = this.BX.Sale.Checkout.View.Payment || {}),BX,BX,BX.Currency));
//# sourceMappingURL=registry.bundle.js.map
