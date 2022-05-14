import { BitrixVue } from 'ui.vue';
import { MixinPaymentInfoButton } from 'sale.payment-pay.mixins.payment-system';

BitrixVue.component('sale-payment_pay-components-payment_system-payment_info-button', {
	props:
	{
		loading: {
			type: Boolean,
			default: false,
			required: false,
		},
	},
	mixins:[MixinPaymentInfoButton],
	// language=Vue
	template: `
		<button :class="classes" @click="onClick($event)">
			<slot></slot>
		</button>
	`,
});
