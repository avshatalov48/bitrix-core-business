import {Vue} from 'ui.vue';
import './style.css'

Vue.component('sale-payment_pay-payment_method-list', {
	props: ['items'],
	data()
	{
		return {
			list: []
		}
	},
	computed:
	{
		localize() {
			return Object.freeze(
				Vue.getFilteredPhrases('PAYMENT_PAY_PAYMENT_METHOD_'))
		}
	},
	methods:
		{
			showDescription(item)
			{
				item.SHOW_DESCRIPTION = item.SHOW_DESCRIPTION === 'Y' ? 'N':'Y';
			},
			isShow(item)
			{
				return item.SHOW_DESCRIPTION === 'Y'
			},
			getLogoSrc(item)
			{
				return (
					item.LOGOTIP
						? item.LOGOTIP
						: '/bitrix/js/sale/payment-pay/payment-method/images/default_logo.png'
				);
			}
		},
	// language=Vue
	template: `
		<div class="checkout-basket-section">
			<h2 class="checkout-basket-title">{{localize.PAYMENT_PAY_PAYMENT_METHOD_1}}</h2>
			<div class="checkout-basket-pay-method-list">
				<div class="checkout-basket-pay-method-item-container" v-for="(item, index) in items">
					<div class="checkout-basket-pay-method-item-logo-block">
						<div class="checkout-basket-pay-method-logo" :style="'background-image: url(\\'' + getLogoSrc(item) + '\\')'"></div>
					</div>
					<div class="checkout-basket-pay-method-text-block">
						<div class="checkout-basket-pay-method-text">{{item.NAME}}</div>
					</div>
					<div class="checkout-basket-pay-method-btn-block">
						<button class="checkout-checkout-btn-info border btn btn-sm rounded-pill" @click='showDescription(item)'>{{localize.PAYMENT_PAY_PAYMENT_METHOD_2}}</button>
					</div>
					<div class="checkout-basket-pay-method-description" v-if="isShow(item)">{{item.DESCRIPTION}}
					</div>
				</div>
			</div>
		</div>
	`
});
