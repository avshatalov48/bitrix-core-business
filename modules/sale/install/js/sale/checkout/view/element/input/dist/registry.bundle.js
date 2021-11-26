this.BX = this.BX || {};
this.BX.Sale = this.BX.Sale || {};
this.BX.Sale.Checkout = this.BX.Sale.Checkout || {};
this.BX.Sale.Checkout.View = this.BX.Sale.Checkout.View || {};
this.BX.Sale.Checkout.View.Element = this.BX.Sale.Checkout.View.Element || {};
(function (exports,ui_type,ui_vue,main_core_events,sale_checkout_const) {
	'use strict';

	ui_vue.BitrixVue.component('sale-checkout-view-element-input-product_item_quantity', {
	  props: ['item', 'index'],
	  data: function data() {
	    return {
	      quantity: this.item.quantity
	    };
	  },
	  methods: {
	    validate: function validate() {
	      main_core_events.EventEmitter.emit(sale_checkout_const.EventType.basket.inputChangeQuantityProduct, {
	        index: this.index
	      });
	    },
	    onKeyDown: function onKeyDown(e) {
	      if (['Enter'].indexOf(e.key) >= 0) {
	        this.$refs.container.blur();
	      }
	    }
	  },
	  computed: {
	    checkedClassObject: function checkedClassObject() {
	      return {
	        'checkout-item-quantity-field': true
	      };
	    }
	  },
	  // language=Vue
	  template: "\n      <input :class=\"checkedClassObject\" \n\t\t\t type=\"text\" \n\t\t\t inputmode=\"numeric\" \n             @blur=\"validate\"\n\t\t\t @keydown=\"onKeyDown\"\n             v-model=\"item.quantity\"\n             ref=\"container\"\n\t  />\n\t"
	});

	ui_vue.BitrixVue.component('sale-checkout-view-element-input-property-phone', {
	  props: ['item', 'index'],
	  methods: {
	    validate: function validate() {
	      main_core_events.EventEmitter.emit(sale_checkout_const.EventType.property.validate, {
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

	ui_vue.BitrixVue.component('sale-checkout-view-element-input-property-text', {
	  props: ['item', 'index', 'autocomplete'],
	  methods: {
	    validate: function validate() {
	      main_core_events.EventEmitter.emit(sale_checkout_const.EventType.property.validate, {
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

}((this.BX.Sale.Checkout.View.Element.Input = this.BX.Sale.Checkout.View.Element.Input || {}),BX.Ui,BX,BX.Event,BX.Sale.Checkout.Const));
//# sourceMappingURL=registry.bundle.js.map
