import {Vue} from 'ui.vue';

Vue.component('sale-checkout-view-product-props_list', {
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
			<div  v-for="(item, index) in list" v-if="isShow(item)" class="checkout-item-props" :key="index">{{item.name}}: {{item.value}}</div>
		</div>
	`
});