this.BX = this.BX || {};
this.BX.Sale = this.BX.Sale || {};
this.BX.Sale.Checkout = this.BX.Sale.Checkout || {};
(function (exports,sale_checkout_view_element_input,main_core,ui_vue,sale_checkout_const) {
    'use strict';

    ui_vue.BitrixVue.component('sale-checkout-view-property-note_error', {
      props: ['message'],
      template: "\n        <div class=\"invalid-feedback\">\n            {{message}}\n        </div>\n\t"
    });

    ui_vue.BitrixVue.component('sale-checkout-view-property-list_edit', {
      props: ['items', 'errors'],
      computed: {
        localize: function localize() {
          return Object.freeze(ui_vue.BitrixVue.getFilteredPhrases('CHECKOUT_VIEW_PROPERTY_LIST_'));
        }
      },
      methods: {
        getErrorMessage: function getErrorMessage(item) {
          var error = this.errors.find(function (error) {
            return error.propertyId === item.id;
          });
          return typeof error !== 'undefined' ? error.message : null;
        },
        isPhone: function isPhone(item) {
          return item.type === sale_checkout_const.Property.type.phone;
        },
        isName: function isName(item) {
          return item.type === sale_checkout_const.Property.type.name;
        },
        isEmail: function isEmail(item) {
          return item.type === sale_checkout_const.Property.type.email;
        },
        isFailure: function isFailure(item) {
          return item.validated === sale_checkout_const.Property.validate.failure;
        }
      },
      // language=Vue
      template: "\n\t\t<div class=\"checkout-basket-section checkout-basket-section-personal-form\">\n\t\t\t<h2 class=\"checkout-basket-title\">{{localize.CHECKOUT_VIEW_PROPERTY_LIST_VIEW_SHIPPING_CONTACTS}}</h2>\n\t\t\t\t<template v-for=\"(item, index) in items\">\n\t\t\t\t  <div class=\"form-group\" v-if=\"isName(item)\">\n\t\t\t\t\t<sale-checkout-view-element-input-property-text :item=\"item\" :index=\"index\" :autocomplete=\"'name'\"/>\n\t\t\t\t\t<sale-checkout-view-property-note_error v-if=\"isFailure(item)\"\n\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t:message=\"getErrorMessage(item)\"/>\n\t\t\t\t  </div>\n\t\t\t\t</template>\n\t\t\t\t<template v-for=\"(item, index) in items\">\n\t\t\t\t  <div class=\"form-group\" v-if=\"isPhone(item)\">\n\t\t\t\t\t<sale-checkout-view-element-input-property-phone :item=\"item\" :index=\"index\"/>\n\t\t\t\t\t<sale-checkout-view-property-note_error v-if=\"isFailure(item)\"\n\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t:message=\"getErrorMessage(item)\"/>\n\t\t\t\t  </div>\n\t\t\t\t</template>\n\t\t\t\t<template v-for=\"(item, index) in items\">\n\t\t\t\t  <div class=\"form-group\" v-if=\"isEmail(item)\">\n\t\t\t\t\t<sale-checkout-view-element-input-property-text :item=\"item\" :index=\"index\" :autocomplete=\"'email'\" />\n\t\t\t\t\t<sale-checkout-view-property-note_error v-if=\"isFailure(item)\"\n\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t:message=\"getErrorMessage(item)\"/>\n\t\t\t\t  </div>\n\t\t\t\t</template>\n\t\n\t\t\t\t<template v-for=\"(item, index) in items\">\n\t\t\t\t  <div class=\"form-group\" v-if=\"isPhone(item) === false && isName(item) === false && isEmail(item) === false\">\n\t\t\t\t\t<sale-checkout-view-element-input-property-text :item=\"item\" :index=\"index\" :autocomplete=\"'off'\"/>\n\t\t\t\t\t<sale-checkout-view-property-note_error v-if=\"isFailure(item)\"\n\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t:message=\"getErrorMessage(item)\"/>\n\t\t\t\t  </div>\n\t\t\t\t</template>\n\t\t</div>\n\t"
    });

    ui_vue.BitrixVue.component('sale-checkout-view-property-list_view', {
      props: ['items', 'number'],
      computed: {
        localize: function localize() {
          return Object.freeze(ui_vue.BitrixVue.getFilteredPhrases('CHECKOUT_VIEW_PROPERTY_LIST_VIEW_'));
        },
        getTitle: function getTitle() {
          var message = this.localize.CHECKOUT_VIEW_PROPERTY_LIST_VIEW_ORDER_TITLE;
          return message.replace('#ORDER_NUMBER#', this.number);
        },
        getPropertiesShort: function getPropertiesShort() {
          var properties = [];

          for (var propertyId in this.items) {
            if (main_core.Type.isStringFilled(this.items[propertyId].value)) {
              properties.push(this.items[propertyId].value);
            }
          }

          return properties.join(', ');
        }
      },
      template: "\n\t\t<div class=\"checkout-basket-section\">\n\t\t<h2 class=\"checkout-basket-title\">{{localize.CHECKOUT_VIEW_PROPERTY_LIST_VIEW_SHIPPING_CONTACTS}}</h2>\n\t\n\t\t\t\t\t\t<div class=\"checkout-item-personal-order-info\">\n\t\t\t\t\t\t\t<div class=\"checkout-item-personal-order-payment\">\n<!--\t\t\t\t\t\t\t\t<div v-for=\"(item, index) in items\" :key=\"index\">{{item.name}}: <b>{{item.value}}</b></div>-->\n\t\t\t\t\t\t\t\t<div>{{getPropertiesShort}}</div>\n\t\t\t\t\t\t\t</div>\n<!--\t\t\t\t\t\t\t<div class=\"checkout-item-personal-order-shipping\">-->\n<!--\t\t\t\t\t\t\t\t<strong>{{localize.CHECKOUT_VIEW_PROPERTY_LIST_VIEW_SHIPPING_METHOD}}</strong>-->\n<!--\t\t\t\t\t\t\t\t<div>{{localize.CHECKOUT_VIEW_PROPERTY_LIST_VIEW_SHIPPING_METHOD_DESCRIPTION}}</div>-->\n<!--\t\t\t\t\t\t\t</div>-->\n\t\t\t\t\t\t</div>\n\t\t\t\n\t\t</div>\n\t"
    });

    ui_vue.BitrixVue.component('sale-checkout-view-property', {
      props: ['items', 'mode', 'order', 'errors'],
      computed: {
        getConstMode: function getConstMode() {
          return sale_checkout_const.Application.mode;
        }
      },
      template: "\n\t\t<div>\n\t\t    <template v-if=\"mode === getConstMode.edit\">\n\t\t         <sale-checkout-view-property-list_edit :items=\"items\" :errors=\"errors\"/>\n\t\t    </template>\n\t\t    <template v-else>\n\t\t        <sale-checkout-view-property-list_view :items=\"items\" :number=\"order.accountNumber\"/>\n            </template>\n        </div>\n\t"
    });

}((this.BX.Sale.Checkout.View = this.BX.Sale.Checkout.View || {}),BX.Sale.Checkout.View.Element.Input,BX,BX,BX.Sale.Checkout.Const));
//# sourceMappingURL=registry.bundle.js.map
