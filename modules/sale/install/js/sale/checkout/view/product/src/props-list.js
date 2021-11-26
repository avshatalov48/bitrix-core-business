import { BitrixVue } from 'ui.vue';

BitrixVue.component('sale-checkout-view-product-props_list', {
	props: ['list'],
	methods:
		{
			isShow(item)
			{
				return item.name !== '' && item.value !== '';
			}
		},
	// language=Vue
	template: `
		<div>
			<div v-for="(item, index) in list" v-if="isShow(item)" class="checkout-basket-item-props" :key="index">{{item.name}}: <strong>{{item.value}}</strong></div>
		</div>
	`
});
