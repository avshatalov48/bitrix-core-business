this.BX = this.BX || {};
this.BX.Sale = this.BX.Sale || {};
this.BX.Sale.Checkout = this.BX.Sale.Checkout || {};
(function (exports,ui_type,main_core,ui_vue,sale_checkout_const) {
    'use strict';

    ui_vue.Vue.component('sale-checkout-view-property-input-text', {
      props: ['item', 'index', 'autocomplete'],
      methods: {
        validate: function validate() {
          main_core.Event.EventEmitter.emit(sale_checkout_const.EventType.property.validate, {
            index: this.index
          });
        }
      },
      computed: {
        checkedClassObject: function checkedClassObject() {
          return this.item.validated === sale_checkout_const.Property.validate.unvalidated ? {} : {
            'is-invalid': this.item.validated === sale_checkout_const.Property.validate.failure,
            'is-valid': this.item.validated === sale_checkout_const.Property.validate.successful
          };
        }
      },
      // language=Vue
      template: "\n        <input class=\"form-control form-control-lg\" :class=\"checkedClassObject\"\n            @blur=\"validate\"\n            type=\"text\" \n            :placeholder=\"item.name\"\n            :autocomplete=\"autocomplete\"\n            v-model=\"item.value\"\n        />\n\t"
    });

    ui_vue.Vue.component('sale-checkout-view-property-input-phone', {
      props: ['item', 'index'],
      methods: {
        validate: function validate() {
          main_core.Event.EventEmitter.emit(sale_checkout_const.EventType.property.validate, {
            index: this.index
          });
        },
        onKeyDown: function onKeyDown(e) {
          var value = e.key;

          if (ui_type.PhoneFilter.replace(value) !== '') {
            return;
          }

          if (['Esc', 'Delete', 'Backspace', 'Tab'].indexOf(e.key) >= 0) {
            return;
          }

          if (e.ctrlKey || e.metaKey) {
            return;
          }

          e.preventDefault();
        },
        onInput: function onInput() {
          var value = ui_type.PhoneFormatter.formatValue(this.value);

          if (this.value !== value) {
            this.value = value;
          }
        }
      },
      computed: {
        checkedClassObject: function checkedClassObject() {
          return this.item.validated === sale_checkout_const.Property.validate.unvalidated ? {} : {
            'is-invalid': this.item.validated === sale_checkout_const.Property.validate.failure,
            'is-valid': this.item.validated === sale_checkout_const.Property.validate.successful
          };
        },
        value: {
          get: function get() {
            return this.item.value;
          },
          set: function set(newValue) {
            this.item.value = newValue;
          }
        }
      },
      // language=Vue
      template: "\n      <input class=\"form-control form-control-lg\" :class=\"checkedClassObject\"\n             @blur=\"validate\"\n             @input=\"onInput\"\n\t\t\t @keydown=\"onKeyDown\"\n             v-model=\"value\"\n             autocomplete=\"tel\"\n\t\t\t :placeholder=\"item.name\"\n      />\n\t"
    });

    ui_vue.Vue.component('sale-checkout-view-property-note_error', {
      props: ['message'],
      template: "\n        <div class=\"invalid-feedback\">\n            {{message}}\n        </div>\n\t"
    });

    ui_vue.Vue.component('sale-checkout-view-property-list_edit', {
      props: ['items', 'errors'],
      computed: {
        localize: function localize() {
          return Object.freeze(ui_vue.Vue.getFilteredPhrases('CHECKOUT_VIEW_PROPERTY_LIST_'));
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
      template: "\n\t\t<div class=\"checkout-basket-section checkout-basket-section-personal-form\">\n\t\t\t<h2 class=\"checkout-basket-title\">{{localize.CHECKOUT_VIEW_PROPERTY_LIST_VIEW_SHIPPING_CONTACTS}}</h2>\n\t\t\t\t<template v-for=\"(item, index) in items\">\n\t\t\t\t  <div class=\"form-group\" v-if=\"isName(item)\">\n\t\t\t\t\t<sale-checkout-view-property-input-text :item=\"item\" :index=\"index\" :autocomplete=\"'name'\"/>\n\t\t\t\t\t<sale-checkout-view-property-note_error v-if=\"isFailure(item)\"\n\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t:message=\"getErrorMessage(item)\"/>\n\t\t\t\t  </div>\n\t\t\t\t</template>\n\t\t\t\t<template v-for=\"(item, index) in items\">\n\t\t\t\t  <div class=\"form-group\" v-if=\"isPhone(item)\">\n\t\t\t\t\t<sale-checkout-view-property-input-phone :item=\"item\" :index=\"index\"/>\n\t\t\t\t\t<sale-checkout-view-property-note_error v-if=\"isFailure(item)\"\n\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t:message=\"getErrorMessage(item)\"/>\n\t\t\t\t  </div>\n\t\t\t\t</template>\n\t\t\t\t<template v-for=\"(item, index) in items\">\n\t\t\t\t  <div class=\"form-group\" v-if=\"isEmail(item)\">\n\t\t\t\t\t<sale-checkout-view-property-input-text :item=\"item\" :index=\"index\" :autocomplete=\"'email'\" />\n\t\t\t\t\t<sale-checkout-view-property-note_error v-if=\"isFailure(item)\"\n\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t:message=\"getErrorMessage(item)\"/>\n\t\t\t\t  </div>\n\t\t\t\t</template>\n\t\n\t\t\t\t<template v-for=\"(item, index) in items\">\n\t\t\t\t  <div class=\"form-group\" v-if=\"isPhone(item) === false && isName(item) === false && isEmail(item) === false\">\n\t\t\t\t\t<sale-checkout-view-property-input-text :item=\"item\" :index=\"index\" :autocomplete=\"'off'\"/>\n\t\t\t\t\t<sale-checkout-view-property-note_error v-if=\"isFailure(item)\"\n\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t:message=\"getErrorMessage(item)\"/>\n\t\t\t\t  </div>\n\t\t\t\t</template>\n\t\t</div>\n\t"
    });

    ui_vue.Vue.component('sale-checkout-view-property-list_view', {
      props: ['items', 'number'],
      computed: {
        localize: function localize() {
          return Object.freeze(ui_vue.Vue.getFilteredPhrases('CHECKOUT_VIEW_PROPERTY_LIST_VIEW_'));
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

    ui_vue.Vue.component('sale-checkout-view-property', {
      props: ['items', 'mode', 'order', 'errors'],
      computed: {
        getConstMode: function getConstMode() {
          return sale_checkout_const.Application.mode;
        }
      },
      template: "\n\t\t<div>\n\t\t    <template v-if=\"mode === getConstMode.edit\">\n\t\t         <sale-checkout-view-property-list_edit :items=\"items\" :errors=\"errors\"/>\n\t\t    </template>\n\t\t    <template v-else>\n\t\t        <sale-checkout-view-property-list_view :items=\"items\" :number=\"order.accountNumber\"/>\n            </template>\n        </div>\n\t"
    });

}((this.BX.Sale.Checkout.View = this.BX.Sale.Checkout.View || {}),BX.Ui,BX,BX,BX.Sale.Checkout.Const));
//# sourceMappingURL=property.bundle.js.map
