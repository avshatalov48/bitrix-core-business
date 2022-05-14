import { BitrixVue } from 'ui.vue';

import 'sale.payment-pay.components';

BitrixVue.component('sale-checkout-view-payment-pay_system_application', {
	props: ['order', 'paySystems', 'config'],
	methods:
	{
		prepare(items)
		{
			let paymentProcess = {
				returnUrl: this.config.returnUrl,
				orderId: this.order.id,
				accessCode: this.order.hash,
				allowPaymentRedirect: true
			}

			let paySystems = items.map((item)=>
			{
				return {
					ID: item.id,
					NAME: item.name,
					LOGOTIP: item.picture,
				}
			})

			return {
				app: { paySystems },
				paymentProcess
			}
		}
	},
	// language=Vue
	template: `
		<sale-payment_pay-components-application-pay_system :options="prepare(paySystems)"/>
	`
});