import { BitrixVue } from 'ui.vue';
import { MixinPaymentInfo } from 'sale.payment-pay.mixins.payment-system';

import './check'
import './payment-info/button'
import './payment-info/pay-system-small-card'

BitrixVue.component('sale-payment_pay-components-payment_system-payment_info', {
	props:
	{
		paySystem: Object,
		title: String,
		sum: String,
		loading: Boolean,
		paid: Boolean,
		checks: Array,
	},
	computed:
	{
		localize()
		{
			return Object.freeze(
				BitrixVue.getFilteredPhrases('PAYMENT_PAY_PAYMENT_SYSTEM_COMPONENTS_'))
		},
		totalSum()
		{
			return this.localize.PAYMENT_PAY_PAYMENT_SYSTEM_COMPONENTS_10.replace('#SUM#', this.sum);
		}
	},
	mixins:[MixinPaymentInfo],
	// language=Vue
	template: `
		<div>
			<div class="order-payment-container">
				<div class="order-payment-title" v-if="title">{{ title }}</div>
				<div class="order-payment-inner d-flex align-items-center justify-content-between">
					<sale-payment_pay-components-payment_system-payment_info-pay_system_small_card :name="paySystem.NAME" :logo="paySystem.LOGOTIP"/>
					<div class="order-payment-status d-flex align-items-center" v-if="paid">
						<div class="order-payment-status-ok"></div>
						<div>{{ localize.PAYMENT_PAY_PAYMENT_SYSTEM_COMPONENTS_5 }}</div>
					</div>
					<div class="order-payment-price" v-html="totalSum"></div>
				</div>
				<hr v-if="checks.length > 0">
				<sale-payment_pay-components-payment_system-check
					v-for="check in checks" :key="check.id"
					:title="getCheckTitle(check)"
					:link="check.link"
					:status="check.status"/>
				<div class="order-payment-buttons-container" v-if="!paid">
					<sale-payment_pay-components-payment_system-payment_info-button
						:loading="loading"
						@click="onClick()">
						{{ localize.PAYMENT_PAY_PAYMENT_SYSTEM_COMPONENTS_4 }}
					</sale-payment_pay-components-payment_system-payment_info-button>
				</div>	
			</div>
		</div>
	`,
});
