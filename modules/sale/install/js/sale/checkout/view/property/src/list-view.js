import { BitrixVue } from 'ui.vue';
import { Type } from 'main.core';
import { Property as Const } from 'sale.checkout.const';

BitrixVue.component('sale-checkout-view-property-list_view', {
    props: ['items', 'number', 'propertyVariants'],
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
		},
	},
	methods: {
		resolveValue(item)
		{
			if (item.type === Const.type.checkbox)
			{
				return item.value === 'Y'
					? this.localize.CHECKOUT_VIEW_PROPERTY_LIST_VIEW_CHECKBOX_Y
					: this.localize.CHECKOUT_VIEW_PROPERTY_LIST_VIEW_CHECKBOX_N
			}
			else if (item.type === Const.type.enum)
			{
				return this.propertyVariants.find(variant => variant.value === item.value && variant.propertyId === item.id).name
			}

			return item.value;
		}
	},
    template: `
		<div class="checkout-basket-section">
		<h2 class="checkout-basket-title">{{localize.CHECKOUT_VIEW_PROPERTY_LIST_VIEW_PROPERTIES}}</h2>
	
						<div class="checkout-item-personal-order-info">
							<div class="checkout-item-personal-order-payment">
								<div v-for="(item, index) in items" :key="index">{{item.name}}: <b>{{resolveValue(item)}}</b></div>
<!--								<div>{{getPropertiesShort}}</div>-->
							</div>
<!--							<div class="checkout-item-personal-order-shipping">-->
<!--								<strong>{{localize.CHECKOUT_VIEW_PROPERTY_LIST_VIEW_SHIPPING_METHOD}}</strong>-->
<!--								<div>{{localize.CHECKOUT_VIEW_PROPERTY_LIST_VIEW_SHIPPING_METHOD_DESCRIPTION}}</div>-->
<!--							</div>-->
						</div>
			
		</div>
	`
});

