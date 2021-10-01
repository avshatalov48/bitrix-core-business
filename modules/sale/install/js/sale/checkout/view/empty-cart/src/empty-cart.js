import { BitrixVue } from 'ui.vue';

BitrixVue.component('sale-checkout-view-empty_cart', {
	props: ['config'],
	computed:
	{
		localize() {
			return Object.freeze(
				BitrixVue.getFilteredPhrases('CHECKOUT_VIEW_EMPTY_CART_'))
		}
	},
	// language=Vue
	template: `
		<div class="checkout-clear-page">
			<div class="checkout-clear-page-image-container">
				<img src="/bitrix/js/sale/checkout/images/empty_cart.svg?v=2" alt="">
			</div>
			<div class="checkout-clear-page-description">
				{{localize.CHECKOUT_VIEW_EMPTY_CART_DESCRIPTION}}
			</div>
			<div class="checkout-clear-page-btn-container">
				<a class="btn border border-dark btn-md rounded-pill pl-4 pr-4 w-100" id="" :href="config.path.emptyCart">
					{{localize.CHECKOUT_VIEW_EMPTY_CART_START}}
				</a>
			</div>
		</div>
`
});