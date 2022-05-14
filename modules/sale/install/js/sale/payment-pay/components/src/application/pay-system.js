import { BitrixVue } from 'ui.vue';
import { Settings } from 'sale.payment-pay.lib';
import { StageType } from 'sale.payment-pay.const';
import { MixinMethods } from 'sale.payment-pay.mixins.application';
import { BackendProvider } from 'sale.payment-pay.backend-provider';

BitrixVue.component('sale-payment_pay-components-application-pay_system', {
	props: {
		options: Object,
	},
	mixins: [MixinMethods],
	data()
	{
		let settings = new Settings(this.options);

		return {
			stageType: StageType,
			stages: this.prepareParamsStages(),
			stage: this.setStageType(),
			loading: false,
			paymentProcess: this.prepareParamsPaymentProcess(settings)
		};
	},
	created()
	{
		this.initPayment();
		this.subscribeToGlobalEvents();
	},
	methods:
	{
		initBackendProvider()
		{
			this.backendProvider = new BackendProvider({
				returnUrl: this.paymentProcess.returnUrl,
				orderId: this.paymentProcess.orderId,
				accessCode: this.paymentProcess.accessCode,
			});
		},
		prepareParamsStages()
		{
			let settings = new Settings(this.options);
			return {
				paySystemList: {
					paySystems: settings.get('app.paySystems', []),
					selectedPaySystem: null,
					// title: settings.get('app.title'),
				},
				paySystemErrors: {
					errors: [],
				},
				paySystemResult: {
					html: null,
					fields: null,
				},
			};
		},
		setStageType()
		{
			return StageType.list;
		}
	},
	// language=Vue
	template: `
		<div class="salescenter-payment-pay-app">
			<template v-if="stage === stageType.list">
				<sale-payment_pay-components-payment_system-pay_system_list
					:paySystems="stages.paySystemList.paySystems"
					:selectedPaySystem="stages.paySystemList.selectedPaySystem"
					:loading="loading"
					@start-payment="startPayment($event)"/>
			</template>
			<template v-else>
				<!--region popup/backdrop -->
				<sale-payment_pay-components-payment_system-backdrop :paySystem="currentPS(stages.paySystemList.selectedPaySystem, stages.paySystemList.paySystems)">
					<template v-slot:main-content>
						<template v-if="stage === stageType.errors">
							<sale-payment_pay-components-payment_system-error_box :errors="stages.paySystemErrors.errors"/>
						</template>

						<template v-else-if="stage === stageType.result">
							<sale-payment_pay-components-payment_system-pay_system_result
								:html="stages.paySystemResult.html"
								:fields="stages.paySystemResult.fields">
									<template v-slot:default><sale-payment_pay-components-payment_system-reset_panel @reset="resetView()"/></template>
							</sale-payment_pay-components-payment_system-pay_system_result>
						</template>
					</template>
				</sale-payment_pay-components-payment_system-backdrop>
				<!--endregion-->
			</template>
		</div>
	`,
});
