import { BitrixVue } from 'ui.vue';
import { MixinCheck } from 'sale.payment-pay.mixins.payment-system';

BitrixVue.component('sale-payment_pay-components-payment_system-check', {
	props:
	{
		status: {
			type: String,
			default: '',
			required: false,
		},
		link: {
			type: String,
			default: '',
			required: false,
		},
		title: {
			type: String,
			required: true,
		},
	},
	mixins:[MixinCheck],
	// language=Vue
	template: `
		<div class="mb-2" :class="{'check-print': processing}">
			<a :href="link" target="_blank" class="check-link" v-if="downloadable">{{ title }}</a>
			<span v-else>{{ title }}</span>
		</div>
	`,
});