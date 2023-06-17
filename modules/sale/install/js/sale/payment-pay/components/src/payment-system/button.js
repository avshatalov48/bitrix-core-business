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
	computed:
	{
		classes()
		{
			return {
				'order-payment-method-item-button': true,
				'btn': true,
				'btn-primary': true,
				'rounded-pill': true,
				'pay-mode': true,
				'order-payment-loader': this.loading,
			};
		},
		buttonClasses()
		{
			return {
				'loading-button-text': this.loading,
			};
		},
	},
	mixins:[MixinButton],
	// language=Vue
	template: `
		<div :class="classes" @click="onClick($event)">
			<span :class="buttonClasses"><slot></slot></span>
		</div>
	`,
});
