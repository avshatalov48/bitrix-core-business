import { BitrixVue } from 'ui.vue';
import { EventType } from 'sale.payment-pay.const';
import { EventEmitter } from 'main.core.events';

BitrixVue.component('sale-payment_pay-components-payment_system-popup', {
	props:['paySystem'],
	data()
	{
		return {
			isShow: true
		}
	},
	// language=Vue
	methods:
	{
		close()
		{
			this.isShow = false
			EventEmitter.emit(EventType.payment.reset, {});
		},
	},
	computed:
	{
		localize()
		{
			return Object.freeze(
				BitrixVue.getFilteredPhrases('PAYMENT_PAY_PAYMENT_SYSTEM_COMPONENTS_'))
		},
		getObjectClass()
		{
			let result = [
				'checkout-order-payment-popup-wrap'
			];
			if(this.isShow === true)
			{
				result.push('active-popup-open')
			}

			return result
		},
		logoStyle()
		{
			const src = this.paySystem.LOGOTIP;

			return `background-image: url("${BX.util.htmlspecialchars(src)}")`;
		}
	},
	template: `
	<div :class=getObjectClass>
			<div class="checkout-order-payment-popup-overlay" @click="close()"/>
			<div class="checkout-order-payment-popup">
				<div class="checkout-order-payment-popup-head">
					<div class="checkout-order-payment-close" @click="close()">{{ localize.PAYMENT_PAY_PAYMENT_SYSTEM_COMPONENTS_12 }}</div>
				</div>
				<div class="checkout-order-payment-popup-main">
					<div class="checkout-order-payment-title">
						<div class="checkout-basket-pay-method-item-logo-block">
							<div
								:style=logoStyle
								class="checkout-basket-pay-method-logo"/>
						</div>
					</div>
					<div class="checkout-order-payment-content">
						<slot name="main-content"></slot>
					</div>
					<div class="checkout-order-payment-btn-container">
						<button class="btn btn-primary rounded-pill" @click="close()">{{ localize.PAYMENT_PAY_PAYMENT_SYSTEM_COMPONENTS_13 }}</button>
					</div>
				</div>
			</div>
	</div>`
});
