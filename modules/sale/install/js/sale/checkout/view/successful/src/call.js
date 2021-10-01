import { BitrixVue } from 'ui.vue';

BitrixVue.component('sale-checkout-view-successful-call', {
	computed:
		{
			localize() {
				return Object.freeze(
					BitrixVue.getFilteredPhrases('CHECKOUT_VIEW_SUCCESSFUL_CALL_'))
			},
		},
	// language=Vue
	template: `
		<div class="checkout-order-common-container">
			<div class="checkout-order-common-row">
				<svg class="checkout-order-common-row-icon" width="26" height="27" viewBox="0 0 26 27" fill="none" xmlns="http://www.w3.org/2000/svg">
					<path fill-rule="evenodd" clip-rule="evenodd" d="M12.9124 26.6569C20.0093 26.6569 25.7624 20.9038 25.7624 13.807C25.7624 6.71014 20.0093 0.957031 12.9124 0.957031C5.81561 0.957031 0.0625 6.71014 0.0625 13.807C0.0625 20.9038 5.81561 26.6569 12.9124 26.6569Z" fill="white"/>
					<path fill-rule="evenodd" clip-rule="evenodd" d="M10.8218 19.498L5.72461 14.5304L7.50861 12.7918L10.8218 16.0207L18.3182 8.71484L20.1022 10.4535L10.8218 19.498Z" fill="#65A90F"/>
				</svg>
				<div>{{localize.CHECKOUT_VIEW_SUCCESSFUL_CALL_MANAGER}}</div>
			</div>
		</div>
`
});