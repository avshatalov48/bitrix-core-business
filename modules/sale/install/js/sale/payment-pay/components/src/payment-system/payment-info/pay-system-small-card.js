import { BitrixVue } from 'ui.vue';

BitrixVue.component('sale-payment_pay-components-payment_system-payment_info-pay_system_small_card', {
	props: {
		logo: String,
		name: String,
	},
	template: `
		<div class="order-payment-operator">
			<img :src="logo" :alt="name" v-if="logo">
			<div class="order-payment-pay-system-name" v-else>{{ name }}</div>
		</div>
	`,
});