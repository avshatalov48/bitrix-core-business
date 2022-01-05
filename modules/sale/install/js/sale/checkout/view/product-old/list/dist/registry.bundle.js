this.BX = this.BX || {};
this.BX.Sale = this.BX.Sale || {};
this.BX.Sale.Checkout = this.BX.Sale.Checkout || {};
this.BX.Sale.Checkout.View = this.BX.Sale.Checkout.View || {};
(function (exports,ui_vue,sale_checkout_const) {
    'use strict';

    ui_vue.Vue.component('sale-checkout-view-product-row', {
      props: ['item', 'index', 'mode'],
      computed: {
        isDeleted: function isDeleted() {
          return this.item.deleted === 'Y';
        },
        isLocked: function isLocked() {
          return this.item.status === sale_checkout_const.Loader.status.wait;
        },
        buttonMinusDisabled: function buttonMinusDisabled() {
          return this.item.quantity - this.item.product.ratio < this.item.product.ratio;
        },
        buttonPlusDisabled: function buttonPlusDisabled() {
          return this.item.quantity + this.item.product.ratio > this.item.product.availableQuantity;
        },
        getConstMode: function getConstMode() {
          return sale_checkout_const.Application.mode;
        },
        getObjectClass: function getObjectClass() {
          var classes = ['checkout-item'];

          if (this.isDeleted) {
            classes.push('checkout-item-deleted');
          }

          if (this.isLocked) {
            classes.push('checkout-item-locked');
          }

          return classes;
        }
      },
      // language=Vue
      template: "\n      <tr :class=\"getObjectClass\">\n      <template v-if=\"isDeleted\">\n        <td colspan=\"2\">\n          <sale-checkout-view-product-info_deleted :item=\"item\" :index=\"index\"/>\n        </td>\n      </template>\n      <template v-else>\n        <td>\n          <template v-if=\"mode === getConstMode.edit\">\n            <sale-checkout-view-product-info :item=\"item\" :index=\"index\">\n              <template v-slot:button-minus><sale-checkout-view-element-button-minus :class=\"{'checkout-item-quantity-btn-disabled': buttonMinusDisabled}\" :index=\"index\"/></template>\n              <template v-slot:button-plus><sale-checkout-view-element-button-plus :class=\"{'checkout-item-quantity-btn-disabled': buttonPlusDisabled}\" :index=\"index\"/></template>\n            </sale-checkout-view-product-info>\n          </template>\n          <template v-else>\n            <sale-checkout-view-product-info :item=\"item\" :index=\"index\"/>\n          </template>\n        </td>\n        <td>\n          <sale-checkout-view-product-price :item=\"item\" :index=\"index\" />\n          <sale-checkout-view-element-button-remove :index=\"index\" v-if=\"mode === getConstMode.edit\"/>\n        </td>\n      </template>\n      </tr>\n\t"
    });

    ui_vue.Vue.component('sale-checkout-view-product-list', {
      props: ['items', 'mode'],
      // language=Vue
      template: "\n\t\t<tbody>\n\t\t\t<sale-checkout-view-product-row v-for=\"(item, index) in items\" :key=\"index\" \n\t\t\t\t\t\t\t\t\t\t\t:item=\"item\" :index=\"index\" :mode=\"mode\" />\n\t\t</tbody>\n\t"
    });

}((this.BX.Sale.Checkout.View.Product = this.BX.Sale.Checkout.View.Product || {}),BX,BX.Sale.Checkout.Const));
//# sourceMappingURL=registry.bundle.js.map
