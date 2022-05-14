import { EventType } from 'sale.payment-pay.const';
import { EventEmitter } from 'main.core.events';
import { VirtualForm, PaymentProcess } from 'sale.payment-pay.lib';
import { StageType } from 'sale.payment-pay.const';

export default {
	methods:
	{
		//region payment-process
		prepareParamsPaymentInfo(settings)
		{
			return {
				paySystem: settings.get('app.paySystems', [])[0],
				title: settings.get('app.title'),
				sum: settings.get('payment.sumFormatted'),
				paid: settings.get('payment.paid'),
				checks: settings.get('payment.checks', []),
			}
		},
		prepareParamsPaymentProcess(settings)
		{
			return {
				returnUrl: settings.get('paymentProcess.returnUrl'),
				orderId: settings.get('paymentProcess.orderId'),
				paymentId: settings.get('paymentProcess.paymentId'),
				accessCode: settings.get('paymentProcess.accessCode'),
				allowPaymentRedirect: settings.get('paymentProcess.allowPaymentRedirect'),
			}
		},
		startPayment(paySystemId)
		{
			if (this.loading)
			{
				return false;
			}

			this.loading = true;
			this.stages.paySystemList.selectedPaySystem = paySystemId;
			this.backendProvider.paySystemId = paySystemId;
			this.paymentProcess.start();
		},
		initPayment()
		{
			this.initBackendProvider();
			this.initPaymentProcess();
		},
		initBackendProvider()
		{
			throw new Error("Method 'initBackendProvider' must be overloaded");
		},
		initPaymentProcess()
		{
			this.paymentProcess = new PaymentProcess({
				backendProvider: this.backendProvider,
				allowPaymentRedirect: this.paymentProcess.allowPaymentRedirect,
			});
		},
		//endregion

		//region pay-system
		subscribeToGlobalEvents()
		{
			EventEmitter.subscribe(EventType.payment.reset, (e) => { this.resetView(this.props) });
			EventEmitter.subscribe(EventType.payment.error, (e) => { this.handlePaymentError(e.getData()) });
			EventEmitter.subscribe(EventType.payment.success, (e) => { this.handlePaymentSuccess(e.getData()) });
			EventEmitter.subscribe(EventType.global.paySystemAjaxError, (e) => { this.handlePaySystemAjaxError(e.getData()) });
			EventEmitter.subscribe(EventType.global.paySystemUpdateTemplate, (e) => { this.handlePaySystemUpdateTemplate(e.getData()) });
		},
		handlePaymentError(response)
		{
			this.stages.paySystemErrors.errors = response.errors || [];
			this.stage = StageType.errors;
		},
		handlePaymentSuccess(response)
		{
			this.stages.paySystemResult.html = response.data.html || null;
			this.stages.paySystemResult.fields = response.data.fields || null;
			this.stage = StageType.result;
		},
		handlePaySystemAjaxError(data)
		{
			this.stages.paySystemErrors.errors = data || [];
			this.stage = StageType.errors;
		},
		handlePaySystemUpdateTemplate(data)
		{
			VirtualForm.createFromNode(this.$el).submit();
		},
		resetView(props)
		{
			this.stages = this.prepareParamsStages(props);
			this.stage = this.setStageType(props);
			this.loading = false;
		},
		currentPS(selected, list)
		{
			return list.find(ps => ps.ID === selected);
		},
		prepareParamsStages(props)
		{
			throw new Error("Method 'initStages' must be overloaded")
		},
		setStageType(props)
		{
			throw new Error("Method 'setStageType' must be overloaded")
		}
		//endregion
	},
};