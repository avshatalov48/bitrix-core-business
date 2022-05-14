import { BitrixVue } from 'ui.vue';
import { Type } from 'main.core';
import {CurrencyCore} from 'currency.currency-core';

import 'sale.payment-pay.components';

BitrixVue.component('sale-checkout-view-payment-payment_paid_application', {
	props: ['order', 'payments', 'paySystems', 'check', 'config'],
	methods:
	{
		getChecksByPaymentId(paymentId)
		{
			const result = [];
			let list = this.check;

			if(Type.isArrayFilled(list))
			{
				list.forEach((fields) =>
				{
					if(fields.paymentId === paymentId)
					{
						result.push({
							status: fields.status,
							link: fields.link,
							id: fields.id,
							dateFormatted: fields.dateFormatted
						})
					}
				})
			}
			return result
		},

		getFirstPaymentPaidY()
		{
			return this.payments[0];
		},

		getPaySystemById(id)
		{
			let paySystem = this.paySystems.find(item => item.id === id)
			return !!paySystem ? paySystem:null
		},

		prepare()
		{
			let result = null

			let item = this.getFirstPaymentPaidY()
			if(item !== null)
			{
				let paySystem = this.getPaySystemById(item.paySystemId)

				let list = [];
				list.push({
					ID: paySystem.id,
					NAME: paySystem.name,
					LOGOTIP: paySystem.picture
				})

				let app = {
					paySystems: list,
					title: this.getTitle(item)
				}

				let payment = {
					sumFormatted: this.sumFormatted(item),
					paid: item.paid === 'Y',
					checks: this.getChecksByPaymentId(item.id),
				};

				let paymentProcess = {
					returnUrl: this.config.returnUrl,
					orderId: this.order.id,
					accessCode: this.order.hash,
					allowPaymentRedirect: true,
					paymentId: item.id
				}

				result = {
					app,
					payment,
					paymentProcess
				}
			}

			return result
		},
		sumFormatted(item)
		{
			return CurrencyCore.currencyFormat(item.sum, item.currency, true);
		},
		getTitle(item)
		{
			return this.localize.CHECKOUT_VIEW_PAYMENT_PAYMENT_INFO
				.replace('#DATE_INSERT#', item.dateBillFormatted)
				.replace('#ACCOUNT_NUMBER#', item.accountNumber)
		}
	},
	computed:
	{
		localize()
		{
			return Object.freeze(
				BitrixVue.getFilteredPhrases('CHECKOUT_VIEW_PAYMENT'))
		}
	},
	// language=Vue
	template: `
		<sale-payment_pay-components-application-payment :options="prepare()"/>
	`
});