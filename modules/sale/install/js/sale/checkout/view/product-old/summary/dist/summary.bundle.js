this.BX = this.BX || {};
this.BX.Sale = this.BX.Sale || {};
this.BX.Sale.Checkout = this.BX.Sale.Checkout || {};
(function (exports,ui_vue,currency_currencyCore) {
    'use strict';

    ui_vue.Vue.component('sale-checkout-view-product-summary', {
      props: ['total'],
      methods: {
        hasDiscount: function hasDiscount() {
          return this.total.discount.sum !== 0;
        }
      },
      computed: {
        localize: function localize() {
          return Object.freeze(ui_vue.Vue.getFilteredPhrases('CHECKOUT_VIEW_SUMMARY_'));
        },
        priceFormatted: function priceFormatted() {
          return currency_currencyCore.CurrencyCore.currencyFormat(this.total.price, this.total.currency, true);
        },
        basePriceFormatted: function basePriceFormatted() {
          return currency_currencyCore.CurrencyCore.currencyFormat(this.total.basePrice, this.total.currency, true);
        },
        discountSumFormatted: function discountSumFormatted() {
          return currency_currencyCore.CurrencyCore.currencyFormat(this.total.discount.sum, this.total.currency, true);
        }
      },
      // language=Vue
      template: "\n\t\t<tbody>\n\t\t<tr class=\"checkout-item-summary\">\n\t\t\t<td>\n\t\t\t\t<div class=\"checkout-summary\">\n\t\t\t\t\t<span>{{localize.CHECKOUT_VIEW_SUMMARY_BASKET_ITEMS}}</span>\n\t\t\t\t</div>\n\t\t\t</td>\n\t\t\t<td>\n\t\t\t\t<div class=\"checkout-item-price-block\">\n\t\t\t\t\t<div v-if=\"hasDiscount()\"\n\t\t\t\t\t\t class=\"checkout-item-price-discount-container\">\n\t\t\t\t\t\t<span class=\"checkout-item-price-discount\" v-html=\"basePriceFormatted\"></span>\n\t\t\t\t\t\t<span class=\"checkout-item-price-discount-diff\" v-html=\"'-' + discountSumFormatted\"></span>\n\t\t\t\t\t</div>\n\t\t\t\t\t<span class=\"checkout-item-price\" v-html=\"priceFormatted\"></span>\n\t\t\t\t</div>\n\t\t\t</td>\n\t\t</tr>\n\t\t</tbody>\n\t"
    });

}((this.BX.Sale.Checkout.View = this.BX.Sale.Checkout.View || {}),BX,BX.Currency));
//# sourceMappingURL=summary.bundle.js.map
