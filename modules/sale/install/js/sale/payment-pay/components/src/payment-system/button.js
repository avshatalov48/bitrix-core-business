import { BitrixVue } from 'ui.vue';
import { MixinButton } from 'sale.payment-pay.mixins.payment-system';

BitrixVue.component('sale-payment_pay-components-payment_system-button', {
	props:
	{
		loading: {
			type: Boolean,
			default: false,
			required: false,
		},
	},
	mixins:[MixinButton],
	// language=Vue
	template: `
		<div :class="classes" @click="onClick($event)">
			<slot></slot>
		</div>
	`,
});
