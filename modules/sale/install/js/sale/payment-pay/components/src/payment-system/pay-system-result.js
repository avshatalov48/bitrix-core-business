import { BitrixVue } from 'ui.vue';

BitrixVue.component('sale-payment_pay-components-payment_system-pay_system_result', {
	props:
	{
		html: {
			type: String,
			default: null,
			required: false,
		},
		fields: {
			type: Object,
			default: null,
			required: false,
		}
	},
	computed:
	{
		localize()
		{
			return Object.freeze(
				BitrixVue.getFilteredPhrases('PAYMENT_PAY_PAYMENT_SYSTEM_COMPONENTS_'))
		},
	},
	mounted()
	{
		if (this.html)
		{
			BX.html(this.$refs.paySystemResultTemplate, this.html);
		}
	},
	// language=Vue
	template: `
		<div class="payment-mobile-change-method">
			<template v-if="html">
				<div ref="paySystemResultTemplate"></div>
			</template>
			<template v-else>
				<div class="checkout-basket-section">
					<div class="page-section-title">{{ localize.PAYMENT_PAY_PAYMENT_SYSTEM_COMPONENTS_1 }}</div>
					<div class="checkout-basket-personal-order-info" v-if="fields">
						<div class="checkout-basket-personal-order-info-item" v-if="fields.SUM_WITH_CURRENCY">
							<span>{{ localize.PAYMENT_PAY_PAYMENT_SYSTEM_COMPONENTS_2 }}</span>
							<strong v-html="fields.SUM_WITH_CURRENCY"></strong>
						</div>
						<div class="checkout-basket-personal-order-info-item" v-if="fields.PAY_SYSTEM_NAME">
							<span>{{ localize.PAYMENT_PAY_PAYMENT_SYSTEM_COMPONENTS_3 }}</span>
							<strong>{{ fields.PAY_SYSTEM_NAME }}</strong>
						</div>
					</div>
				</div>
			</template>
		</div>
	`,
});
