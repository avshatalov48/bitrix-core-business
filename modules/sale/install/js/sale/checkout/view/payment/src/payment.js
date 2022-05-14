import { BitrixVue } from 'ui.vue';

import './pay-system-application'
import './payment-paid-application'

BitrixVue.component('sale-checkout-view-payment', {
	props: ['order', 'payments', 'paySystems', 'check', 'config'],
	methods:
	{
		hasPaymentPaidY()
		{
			return this
			.getPaymentPaidY()
				.length > 0
		},
		getPaymentPaidY()
		{
			const result = [];
			let list = this.payments;
			list.forEach((fields) =>
			{
				if(fields.paid !== 'N')
				{
					result.push(fields)
				}
			})
			return result
		},
	},
	// language=Vue
	template: `
	  <div>
	  <template v-if="hasPaymentPaidY()">
        <sale-checkout-view-payment-payment_paid_application :order="order" :payments="getPaymentPaidY()" :paySystems="paySystems" :check="check" :config="config"/>
      </template>
      <template v-else>
        <sale-checkout-view-payment-pay_system_application :order="order" :paySystems="paySystems" :config="config"/>
	  </template>
	</div>
	`
});