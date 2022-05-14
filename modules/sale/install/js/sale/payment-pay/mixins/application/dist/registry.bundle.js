this.BX = this.BX || {};
this.BX.Sale = this.BX.Sale || {};
this.BX.Sale.PaymentPay = this.BX.Sale.PaymentPay || {};
this.BX.Sale.PaymentPay.Mixins = this.BX.Sale.PaymentPay.Mixins || {};
(function (exports,sale_paymentPay_const,main_core_events,sale_paymentPay_lib) {
	'use strict';

	var methods = {
	  methods: {
	    //region payment-process
	    prepareParamsPaymentInfo: function prepareParamsPaymentInfo(settings) {
	      return {
	        paySystem: settings.get('app.paySystems', [])[0],
	        title: settings.get('app.title'),
	        sum: settings.get('payment.sumFormatted'),
	        paid: settings.get('payment.paid'),
	        checks: settings.get('payment.checks', [])
	      };
	    },
	    prepareParamsPaymentProcess: function prepareParamsPaymentProcess(settings) {
	      return {
	        returnUrl: settings.get('paymentProcess.returnUrl'),
	        orderId: settings.get('paymentProcess.orderId'),
	        paymentId: settings.get('paymentProcess.paymentId'),
	        accessCode: settings.get('paymentProcess.accessCode'),
	        allowPaymentRedirect: settings.get('paymentProcess.allowPaymentRedirect')
	      };
	    },
	    startPayment: function startPayment(paySystemId) {
	      if (this.loading) {
	        return false;
	      }

	      this.loading = true;
	      this.stages.paySystemList.selectedPaySystem = paySystemId;
	      this.backendProvider.paySystemId = paySystemId;
	      this.paymentProcess.start();
	    },
	    initPayment: function initPayment() {
	      this.initBackendProvider();
	      this.initPaymentProcess();
	    },
	    initBackendProvider: function initBackendProvider() {
	      throw new Error("Method 'initBackendProvider' must be overloaded");
	    },
	    initPaymentProcess: function initPaymentProcess() {
	      this.paymentProcess = new sale_paymentPay_lib.PaymentProcess({
	        backendProvider: this.backendProvider,
	        allowPaymentRedirect: this.paymentProcess.allowPaymentRedirect
	      });
	    },
	    //endregion
	    //region pay-system
	    subscribeToGlobalEvents: function subscribeToGlobalEvents() {
	      var _this = this;

	      main_core_events.EventEmitter.subscribe(sale_paymentPay_const.EventType.payment.reset, function (e) {
	        _this.resetView(_this.props);
	      });
	      main_core_events.EventEmitter.subscribe(sale_paymentPay_const.EventType.payment.error, function (e) {
	        _this.handlePaymentError(e.getData());
	      });
	      main_core_events.EventEmitter.subscribe(sale_paymentPay_const.EventType.payment.success, function (e) {
	        _this.handlePaymentSuccess(e.getData());
	      });
	      main_core_events.EventEmitter.subscribe(sale_paymentPay_const.EventType.global.paySystemAjaxError, function (e) {
	        _this.handlePaySystemAjaxError(e.getData());
	      });
	      main_core_events.EventEmitter.subscribe(sale_paymentPay_const.EventType.global.paySystemUpdateTemplate, function (e) {
	        _this.handlePaySystemUpdateTemplate(e.getData());
	      });
	    },
	    handlePaymentError: function handlePaymentError(response) {
	      this.stages.paySystemErrors.errors = response.errors || [];
	      this.stage = sale_paymentPay_const.StageType.errors;
	    },
	    handlePaymentSuccess: function handlePaymentSuccess(response) {
	      this.stages.paySystemResult.html = response.data.html || null;
	      this.stages.paySystemResult.fields = response.data.fields || null;
	      this.stage = sale_paymentPay_const.StageType.result;
	    },
	    handlePaySystemAjaxError: function handlePaySystemAjaxError(data) {
	      this.stages.paySystemErrors.errors = data || [];
	      this.stage = sale_paymentPay_const.StageType.errors;
	    },
	    handlePaySystemUpdateTemplate: function handlePaySystemUpdateTemplate(data) {
	      sale_paymentPay_lib.VirtualForm.createFromNode(this.$el).submit();
	    },
	    resetView: function resetView(props) {
	      this.stages = this.prepareParamsStages(props);
	      this.stage = this.setStageType(props);
	      this.loading = false;
	    },
	    currentPS: function currentPS(selected, list) {
	      return list.find(function (ps) {
	        return ps.ID === selected;
	      });
	    },
	    prepareParamsStages: function prepareParamsStages(props) {
	      throw new Error("Method 'initStages' must be overloaded");
	    },
	    setStageType: function setStageType(props) {
	      throw new Error("Method 'setStageType' must be overloaded");
	    } //endregion

	  }
	};

	exports.MixinMethods = methods;

}((this.BX.Sale.PaymentPay.Mixins.Application = this.BX.Sale.PaymentPay.Mixins.Application || {}),BX.Sale.PaymentPay.Const,BX.Event,BX.Sale.PaymentPay.Lib));
//# sourceMappingURL=registry.bundle.js.map
