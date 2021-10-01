import { BitrixVue } from 'ui.vue';
import { Type } from 'main.core';

BitrixVue.component('sale-checkout-view-property-list_view', {
    props: ['items', 'number'],
	computed:
	{
		localize() {
			return Object.freeze(
				BitrixVue.getFilteredPhrases('CHECKOUT_VIEW_PROPERTY_LIST_VIEW_'))
		},
		getTitle()
		{
			let message = this.localize.CHECKOUT_VIEW_PROPERTY_LIST_VIEW_ORDER_TITLE;
			return message.replace('#ORDER_NUMBER#', this.number);
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
		<div class="checkout-basket-section">
		<h2 class="checkout-basket-title">{{localize.CHECKOUT_VIEW_PROPERTY_LIST_VIEW_SHIPPING_CONTACTS}}</h2>
	
						<div class="checkout-item-personal-order-info">
							<div class="checkout-item-personal-order-payment">
<!--								<div v-for="(item, index) in items" :key="index">{{item.name}}: <b>{{item.value}}</b></div>-->
								<div>{{getPropertiesShort}}</div>
							</div>
<!--							<div class="checkout-item-personal-order-shipping">-->
<!--								<strong>{{localize.CHECKOUT_VIEW_PROPERTY_LIST_VIEW_SHIPPING_METHOD}}</strong>-->
<!--								<div>{{localize.CHECKOUT_VIEW_PROPERTY_LIST_VIEW_SHIPPING_METHOD_DESCRIPTION}}</div>-->
<!--							</div>-->
						</div>
			
		</div>
	`
});

