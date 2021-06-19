this.BX = this.BX || {};
this.BX.Sale = this.BX.Sale || {};
this.BX.Sale.Checkout = this.BX.Sale.Checkout || {};
this.BX.Sale.Checkout.View = this.BX.Sale.Checkout.View || {};
this.BX.Sale.Checkout.View.Element = this.BX.Sale.Checkout.View.Element || {};
(function (exports,ui_vue,main_core,sale_checkout_const) {
    'use strict';

    ui_vue.Vue.component('sale-checkout-view-element-button-remove', {
      props: ['index'],
      computed: {
        localize: function localize() {
          return Object.freeze(ui_vue.Vue.getFilteredPhrases('CHECKOUT_VIEW_ELEMENT_BUTTON_REMOVE_'));
        }
      },
      methods: {
        remove: function remove() {
          main_core.Event.EventEmitter.emit(sale_checkout_const.EventType.basket.buttonRemoveProduct, {
            index: this.index
          });
        }
      },
      // language=Vue
      template: "\n\t\t<span class=\"checkout-basket-item-remove-btn checkout-basket-desktop-only\" @click=\"remove\">{{localize.CHECKOUT_VIEW_ELEMENT_BUTTON_REMOVE_NAME}}</span>\n\t"
    });

}((this.BX.Sale.Checkout.View.Element.Button = this.BX.Sale.Checkout.View.Element.Button || {}),BX,BX,BX.Sale.Checkout.Const));
//# sourceMappingURL=remove.bundle.js.map
