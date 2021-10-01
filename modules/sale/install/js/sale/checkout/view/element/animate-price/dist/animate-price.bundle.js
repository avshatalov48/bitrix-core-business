this.BX = this.BX || {};
this.BX.Sale = this.BX.Sale || {};
this.BX.Sale.Checkout = this.BX.Sale.Checkout || {};
this.BX.Sale.Checkout.View = this.BX.Sale.Checkout.View || {};
(function (exports,ui_vue,main_core,currency_currencyCore) {
	'use strict';

	ui_vue.BitrixVue.component('sale-checkout-view-element-animate_price', {
	  props: ['sum', 'currency', 'prefix'],
	  data: function data() {
	    return {
	      displaySum: this.sum,
	      interval: false
	    };
	  },
	  computed: {
	    getPrefix: function getPrefix() {
	      return main_core.Type.isString(this.prefix) ? this.prefix : '';
	    },
	    sumFormatted: function sumFormatted() {
	      return currency_currencyCore.CurrencyCore.currencyFormat(this.displaySum, this.currency, true);
	    },
	    getSum: function getSum() {
	      return this.sum;
	    }
	  },
	  methods: {
	    animated: function animated() {
	      var _this = this;

	      clearInterval(this.interval);

	      if (this.sum !== this.displaySum) {
	        this.interval = window.setInterval(function () {
	          if (_this.displaySum !== _this.sum) {
	            var diff = (_this.sum - _this.displaySum) / 5;
	            diff = diff >= 0 ? Math.ceil(diff) : Math.floor(diff);

	            if (diff > 0 && _this.displaySum + diff > _this.sum) {
	              _this.displaySum = _this.sum;
	            } else {
	              _this.displaySum = _this.displaySum + diff;
	            }
	          } else {
	            clearInterval(_this.interval);
	          }
	        }, 10);
	      }
	    }
	  },
	  watch: {
	    getSum: function getSum() {
	      this.animated();
	    }
	  },
	  // language=Vue
	  template: "\n\t\t<div v-html=\"getPrefix + sumFormatted\"/>\n\t"
	});

}((this.BX.Sale.Checkout.View.Element = this.BX.Sale.Checkout.View.Element || {}),BX,BX,BX.Currency));
//# sourceMappingURL=animate-price.bundle.js.map
