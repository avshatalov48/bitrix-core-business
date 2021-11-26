import { BitrixVue } from 'ui.vue';
import { EventEmitter } from 'main.core.events'

import { EventType } from 'sale.checkout.const';

BitrixVue.component('sale-checkout-view-element-input-product_item_quantity', {
	props: ['item', 'index'],
	data()
	{
		return {
			quantity: this.item.quantity
		}
	},
	methods:
		{
			validate()
			{
				EventEmitter.emit(EventType.basket.inputChangeQuantityProduct, {index: this.index});
			},
			onKeyDown(e)
			{
				if (['Enter'].indexOf(e.key) >= 0)
				{
					this.$refs.container.blur();
				}
			}
		},
	computed:
		{
			checkedClassObject()
			{
				return {'checkout-item-quantity-field': true}
			}
		},
	// language=Vue
	template: `
      <input :class="checkedClassObject" 
			 type="text" 
			 inputmode="numeric" 
             @blur="validate"
			 @keydown="onKeyDown"
             v-model="item.quantity"
             ref="container"
	  />
	`
});