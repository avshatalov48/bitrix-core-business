this.BX = this.BX || {};
this.BX.Sale = this.BX.Sale || {};
this.BX.Sale.Checkout = this.BX.Sale.Checkout || {};
(function (exports,sale_checkout_const,main_core_events,ui_vue,currency_currencyCore) {
    'use strict';

    ui_vue.BitrixVue.component('sale-checkout-view-total-basket', {
      props: ['total'],
      computed: {
        localize: function localize() {
          return Object.freeze(ui_vue.BitrixVue.getFilteredPhrases('CHECKOUT_VIEW_TOTAL_'));
        },
        basePriceFormatted: function basePriceFormatted() {
          return currency_currencyCore.CurrencyCore.currencyFormat(this.total.basePrice, this.total.currency, true);
        }
      },
      // language=Vue
      template: "\n      <tr class=\"checkout-basket-total-item checkout-basket-total-item-subtotal\">\n        <td>\n          <div class=\"checkout-basket-total-item-summary\">\n            <span>{{localize.CHECKOUT_VIEW_TOTAL_TOTAL_ITEMS}}</span>\n          </div>\n        </td>\n        <td>\n          <div class=\"checkout-basket-total-item-price-block\">\n            <span class=\"checkout-basket-total-item-price\" v-html=\"basePriceFormatted\"/>\n          </div>\n        </td>\n      </tr>\n    "
    });

    ui_vue.BitrixVue.component('sale-checkout-view-total-discount', {
      props: ['total'],
      computed: {
        localize: function localize() {
          return Object.freeze(ui_vue.BitrixVue.getFilteredPhrases('CHECKOUT_VIEW_TOTAL_'));
        },
        discountSumFormatted: function discountSumFormatted() {
          return currency_currencyCore.CurrencyCore.currencyFormat(this.total.discount.sum, this.total.currency, true);
        }
      },
      // language=Vue
      template: "\n      <tr class=\"checkout-basket-total-item checkout-basket-total-item-discount\">\n        <td>\n          <div class=\"checkout-basket-total-item-summary\">\n            <span>{{localize.CHECKOUT_VIEW_TOTAL_TOTAL_PROFIT}}</span>\n          </div>\n        </td>\n        <td>\n          <div class=\"checkout-basket-total-price-block\">\n            <span class=\"checkout-basket-total-item-price-discount\" v-html=\"'-' + discountSumFormatted\"/>\n          </div>\n        </td>\n      </tr>\n    "
    });

    ui_vue.BitrixVue.component('sale-checkout-view-total-summary', {
      props: ['total'],
      computed: {
        localize: function localize() {
          return Object.freeze(ui_vue.BitrixVue.getFilteredPhrases('CHECKOUT_VIEW_TOTAL_'));
        },
        priceFormatted: function priceFormatted() {
          return currency_currencyCore.CurrencyCore.currencyFormat(this.total.price, this.total.currency, true);
        }
      },
      // language=Vue
      template: "\n       <tr class=\"checkout-basket-total-item checkout-basket-total-item-total\">\n        <td>\n          <div class=\"checkout-basket-total-item-summary\">\n            <span>{{localize.CHECKOUT_VIEW_TOTAL_TOTAL_SUMMARY}}</span>\n          </div>\n        </td>\n        <td>\n          <div class=\"checkout-basket-total-price-block\">\n            <span class=\"checkout-basket-total-item-price\" v-html=\"priceFormatted\"/>\n          </div>\n        </td>\n      </tr>\n    "
    });

    ui_vue.BitrixVue.component('sale-checkout-view-total', {
      props: ['total', 'showBackdrop'],
      computed: {
        localize: function localize() {
          return Object.freeze(ui_vue.BitrixVue.getFilteredPhrases('CHECKOUT_VIEW_TOTAL_CLOSE'));
        },
        hasDiscount: function hasDiscount() {
          return this.total.discount.sum !== 0;
        },
        getObjectClass: function getObjectClass() {
          var classes = ['checkout-basket-total-container'];

          if (this.isBackdrop) {
            classes.push('active');
          }

          return classes;
        },
        isBackdrop: function isBackdrop() {
          return this.showBackdrop === 'Y';
        }
      },
      methods: {
        backdropTotalClose: function backdropTotalClose() {
          main_core_events.EventEmitter.emit(sale_checkout_const.EventType.basket.backdropTotalClose);
        }
      },
      // language=Vue
      template: "\n      <div :class=\"getObjectClass\">\n\t\t  <div class=\"checkout-basket-total-overlay\" @click=\"backdropTotalClose\"></div>\n          <div class=\"checkout-basket-total-backdrop-container\">\n            <div class=\"checkout-basket-total-backdrop-header justify-content-between align-items-center checkout-basket-mobile-only\">\n              <div class=\"checkout-basket-total-backdrop-header-separate\"></div>\n              <div class=\"checkout-basket-total-backdrop-swipe-btn-container\">\n                <div class=\"checkout-basket-total-backdrop-swipe-btn\"></div>\n              </div>\n              <div class=\"checkout-basket-total-backdrop-close-btn-container\" @click=\"backdropTotalClose\">\n\t\t\t\t<span class=\"checkout-basket-total-backdrop-close-btn\">\n\t\t\t\t\t<span class=\"checkout-basket-total-backdrop-close-btn-text\" >{{localize.CHECKOUT_VIEW_TOTAL_CLOSE}}</span>\n\t\t\t\t</span>\n              </div>\n            </div>\n\t\t\t  <div class=\"checkout-basket-total-inner\">\n\t\t\t\t\t<table class=\"checkout-basket-total-list\">\n\t\t\t\t\t\t<sale-checkout-view-total-basket :total=\"total\"/>\n\t\t\t\t\t\t<sale-checkout-view-total-discount :total=\"total\" v-if=\"hasDiscount\"/>\n\t\t\t\t\t\t<sale-checkout-view-total-summary :total=\"total\"/>\n\t\t\t\t\t</table>\n\t\t\t  </div>\n\t\t  </div>\n      </div>\n\t"
    });

}((this.BX.Sale.Checkout.View = this.BX.Sale.Checkout.View || {}),BX.Sale.Checkout.Const,BX.Event,BX,BX.Currency));
//# sourceMappingURL=total.bundle.js.map
