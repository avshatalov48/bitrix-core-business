import { BitrixVue } from 'ui.vue';
import { MixinPaySystemRow } from 'sale.payment-pay.mixins.payment-system';

import './button'

BitrixVue.component('sale-payment_pay-components-payment_system-pay_system_row', {
	props:
	{
		loading: Boolean,
		name: String,
		logo: String,
		id: Number,
	},
	computed:
	{
		localize()
		{
			return Object.freeze(
				BitrixVue.getFilteredPhrases('PAYMENT_PAY_PAYMENT_SYSTEM_COMPONENTS_'))
		},
	},
	mixins:[MixinPaySystemRow],
	// language=Vue
	template: `
		<div class="order-pay-method-item-container pay-mode" @click="onClick()">
			<div class="order-pay-method-item-logo-block">
				<div class="order-pay-method-logo" :style="logoStyle"></div>
			</div>
			<div class="order-pay-method-text-block">
				<div class="order-pay-method-text">{{ name }}</div>
			</div>
			<sale-payment_pay-components-payment_system-button :loading="loading">{{ localize.PAYMENT_PAY_PAYMENT_SYSTEM_COMPONENTS_4 }}</sale-payment_pay-components-payment_system-button>
		</div>
	`,
});
