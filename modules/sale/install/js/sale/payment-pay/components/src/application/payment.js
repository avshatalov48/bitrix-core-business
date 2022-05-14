import { BitrixVue } from 'ui.vue';
import { Settings } from 'sale.payment-pay.lib';
import { StageType } from 'sale.payment-pay.const';
import { MixinMethods } from 'sale.payment-pay.mixins.application';
import { BackendProvider } from 'sale.payment-pay.backend-provider';

BitrixVue.component('sale-payment_pay-components-application-payment', {
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
				paymentInfo: {
					paySystem: settings.get('app.paySystems', [])[0],
					title: settings.get('app.title'),
					sum: settings.get('payment.sumFormatted'),
					paid: settings.get('payment.paid'),
					checks: settings.get('payment.checks', []),
				},
				paySystemList:{
					selectedPaySystem: null
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
			return StageType.paymentInfo;
		}
	},
	// language=Vue
	template: `
		<div class="salescenter-payment-pay-app">
			<sale-payment_pay-components-payment_system-payment_info
                v-if="stage === stageType.paymentInfo"
				:paySystem="stages.paymentInfo.paySystem"
                :title="stages.paymentInfo.title"
				:sum="stages.paymentInfo.sum"
				:paid="stages.paymentInfo.paid"
				:loading="loading"
				:checks="stages.paymentInfo.checks"
                @start-payment="startPayment($event)">
			</sale-payment_pay-components-payment_system-payment_info>
            <sale-payment_pay-components-payment_system-error_box
                v-if="stage === stageType.errors"
                :errors="stages.paySystemErrors.errors">
            	<sale-payment_pay-components-payment_system-reset_panel @reset="resetView()"/>
            </sale-payment_pay-components-payment_system-error_box>
            <sale-payment_pay-components-payment_system-pay_system_result
                v-if="stage === stageType.result"
                :html="stages.paySystemResult.html"
                :fields="stages.paySystemResult.fields">
            	<sale-payment_pay-components-payment_system-reset_panel @reset="resetView()"/>
            </sale-payment_pay-components-payment_system-pay_system_result>
		</div>
	`,
});