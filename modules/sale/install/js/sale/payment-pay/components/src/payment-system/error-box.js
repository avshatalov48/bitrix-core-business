import { BitrixVue } from 'ui.vue';

BitrixVue.component('sale-payment_pay-components-payment_system-error_box', {
	props:
	{
		errors: Array
	},
	computed:
	{
		localize()
		{
			return Object.freeze(
				BitrixVue.getFilteredPhrases('PAYMENT_PAY_PAYMENT_SYSTEM_COMPONENTS_'))
		},
	},
	// language=Vue
	template: `
		<div>
			<div class="alert alert-danger">
				<slot name="errors-header">
					<div>{{ localize.PAYMENT_PAY_PAYMENT_SYSTEM_COMPONENTS_8 }}</div>
				</slot>
				<slot name="errors-footer">
					<div>{{ localize.PAYMENT_PAY_PAYMENT_SYSTEM_COMPONENTS_9 }}</div>
				</slot>
			</div>
		</div>
	`,
});