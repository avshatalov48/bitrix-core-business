import { BitrixVue } from 'ui.vue';
import { Text, Type } from 'main.core';

BitrixVue.component('sale-checkout-view-successful-property_list', {
	props: ['items', 'order'],
	computed:
	{
		localize()
		{
			return Object.freeze(
				BitrixVue.getFilteredPhrases('CHECKOUT_VIEW_SUCCESSFUL_'))
		},
		getNumber()
		{
			return Text.encode(this.order.accountNumber);
		},
		getTitle()
		{
			let message = this.localize.CHECKOUT_VIEW_SUCCESSFUL_STATUS_ORDER_TITLE;
			return message.replace('#ORDER_NUMBER#', this.getNumber);
		},
		getPropertiesShort()
		{
			const properties = [];
			
			for (let propertyId in this.items)
			{
				if (Type.isStringFilled(this.items[propertyId].value))
				{
					properties.push(this.items[propertyId].value);
				}
			}
			
			return properties.join(', ');
		}
	},
	template: `
		<div class="checkout-order-info">
			<div>{{getTitle}}</div>
			<div>{{getPropertiesShort}}</div>
			<slot name="block-1"/>
		</div>
	`
});