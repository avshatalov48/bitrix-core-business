this.BX = this.BX || {};
this.BX.Sale = this.BX.Sale || {};
this.BX.Sale.Checkout = this.BX.Sale.Checkout || {};
(function (exports,ui_vue,sale_checkout_const,main_core,sale_checkout_view_mixins) {
	'use strict';

	ui_vue.Vue.component('sale-checkout-view-payment', {
	  props: ['order', 'config'],
	  mixins: [sale_checkout_view_mixins.MixinLoader],
	  methods: {
	    getBlockHtml: function getBlockHtml() {
	      var _this = this;

	      var fields = {
	        accessCode: this.order.hash,
	        orderId: this.order.id,
	        returnUrl: this.config.returnUrl
	      };
	      main_core.ajax.runComponentAction(sale_checkout_const.Component.bitrixSaleOrderCheckout, sale_checkout_const.RestMethod.saleEntityPaymentPay, {
	        data: {
	          fields: fields
	        }
	      }).then(function (response) {
	        var html = response.data.html;
	        var wrapper = _this.$refs.paymentSystemList;
	        BX.html(wrapper, html);
	        main_core.Event.EventEmitter.emit(sale_checkout_const.EventType.paysystem.afterInitList, {});
	        BX.addCustomEvent('onChangePaySystems', function () {
	          main_core.Event.EventEmitter.emit(sale_checkout_const.EventType.paysystem.beforeInitList, {});

	          _this.getBlockHtml();
	        });
	      });
	    }
	  },
	  mounted: function mounted() {
	    this.getBlockHtml();
	  },
	  // language=Vue
	  template: "\n\t\t<div style='position: relative;' ref=\"container\">\n\t\t\t<div ref=\"paymentSystemList\"/>\n\t\t</div>\n\t"
	});

}((this.BX.Sale.Checkout.View = this.BX.Sale.Checkout.View || {}),BX,BX.Sale.Checkout.Const,BX,BX.Sale.Checkout.View.Mixins));
//# sourceMappingURL=payment.bundle.js.map
