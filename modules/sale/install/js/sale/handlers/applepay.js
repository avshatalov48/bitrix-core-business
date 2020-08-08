BX.namespace('BX.Sale.PaymentApplePay');

(function() {
	'use strict';

	BX.Sale.PaymentApplePay = {
		STATUS_SUCCESS: 'success',
		STATUS_FAILURE: 'fail',

		init: function(parameters)
		{
			this.ajaxUrl = parameters.ajaxUrl;
			this.salePaySystemWrapperNode = BX(parameters.salePaySystemWrapperId);
			this.paymentButtonNode = BX(parameters.paymentButtonId);
			this.paymentButtonWrapperNode = BX(parameters.paymentButtonWrapperId);

			this.params = parameters.params;
			this.message = parameters.message;

			if (this.isApplePayAvailable())
			{
				this.showApplePayButton();
				this.initApplePayPayment();
			}
			else
			{
				this.showNotAvailableMessage();
			}
		},

		bindEvents: function()
		{
			BX.bind(this.paymentButtonNode, 'click', BX.proxy(this.makePayment, this));
		},

		createPaymentRequest: function()
		{
			var methodData = [{
				supportedMethods: [this.params.SUPPORTED_METHOD],
				data: {
					version: 3,
					merchantIdentifier: this.params.MERCHANT_ID,
					merchantCapabilities: this.params.MERCHANT_CAPABILITIES,
					supportedNetworks: ["amex", "discover", "masterCard", "visa"],
					countryCode: this.params.COUNTRY_CODE,
					currencyCode: this.params.CURRENCY
				}
			}];

			var paymentDetails = {
				total: {
					label: this.params.DISPLAY_NAME,
					amount: { value: this.params.TOTAL_SUM, currency: this.params.CURRENCY },
				},
				displayItems: [
					{
						label:  this.message.ORDER_TITLE + ' ' + this.params.ORDER_ID,
						amount: { value: this.params.TOTAL_SUM, currency: this.params.CURRENCY },
					}
				]
			};

			var paymentOptions = {
				requestPayerName: false,
				requestPayerEmail: false,
				requestPayerPhone: false,
				requestShipping: false
			};

			return new PaymentRequest(methodData, paymentDetails, paymentOptions);
		},

		isApplePayAvailable: function()
		{
			return !!(window.PaymentRequest && window.ApplePaySession && ApplePaySession.canMakePayments());
		},

		initApplePayPayment: function ()
		{
			var request;

			if (!window.PaymentRequest)
				return;

			request = this.createPaymentRequest();
			request.canMakePayment()
				.then(function(result) {
					if (result || this.isApplePayAvailable()) {
						this.bindEvents();
					}
				}.bind(this))
				.catch(function() {
					this.showNotAvailableMessage();
				}.bind(this));
		},

		showApplePayButton: function()
		{
			this.paymentButtonNode.style.display = 'inline-block';
		},

		showNotAvailableMessage: function()
		{
			var messageNode = document.createElement('div');
			messageNode.innerHTML = this.message.PAY_SYSTEM_NOT_AVAILABLE;
			messageNode.classList.add("alert");
			messageNode.classList.add("alert-danger");
			this.paymentButtonWrapperNode.appendChild(messageNode);
		},

		makePayment: function()
		{
			var request = this.createPaymentRequest();

			request.onmerchantvalidation = function (event) {
				var sessionPromise = this.fetchPaymentSession(event.validationURL);
				event.complete(sessionPromise);
			}.bind(this);

			try {
				request.show().then(function(response) {
					this.processPayment(response);
				}.bind(this));
			}
			catch (err)
			{
				alert(this.message.ERROR_MESSAGE);
			}
		},

		fetchPaymentSession: function(validationURL)
		{
			var postData = {
				PAYMENT_ID: this.params.PAYMENT_ID,
				PAYSYSTEM_ID: this.params.PAYSYSTEM_ID,
				action: this.params.GET_SESSION_ACTION,
				url: validationURL,
				merchantIdentifier: this.params.MERCHANT_ID,
				displayName: this.params.DISPLAY_NAME,
				initiativeContext: this.params.DOMAIN_NAME
			};

			return this.send(postData);
		},

		processPayment: function(response)
		{
			var postData = {
				PAYMENT_ID: this.params.PAYMENT_ID,
				PAYSYSTEM_ID: this.params.PAYSYSTEM_ID,
				action: this.params.MAKE_PAYMENT_ACTION,
				paymentData: JSON.stringify(response.details.token.paymentData)
			};

			this.send(postData)
				.then(function() {
					response.complete(this.STATUS_SUCCESS);
					this.showSuccessfulTemplate();
				}.bind(this))
				.catch(function (error) {
					response.complete(this.STATUS_FAILURE);
					this.showErrorTemplate(error.message);
				}.bind(this));
		},

		send: function (postData)
		{
			return new Promise(function (resolve, reject) {
				BX.ajax({
					method: 'POST',
					dataType: 'json',
					url: this.ajaxUrl,
					data: postData,
					onsuccess: BX.proxy(function(result) {
						if (result.status === 'success')
						{
							resolve(result.data);
						}
						else if (result.status === 'error')
						{
							reject(new Error(result.errors.join("<br>")));
						}
					}),
					onfailure: BX.proxy(function() {
						reject(new Error(this.message.ERROR_MESSAGE));
					})
				});
			}.bind(this));
		},

		showSuccessfulTemplate: function()
		{
			var successfulBlock = document.createElement('div');
			successfulBlock.innerHTML = this.message.PAYMENT_APPROVED;
			successfulBlock.innerHTML = successfulBlock.innerHTML + "<br>";
			successfulBlock.innerHTML = successfulBlock.innerHTML + this.message.PAID_MESSAGE;
			successfulBlock.classList.add("alert");
			successfulBlock.classList.add("alert-success");
			this.salePaySystemWrapperNode.innerHTML = '';
			this.salePaySystemWrapperNode.appendChild(successfulBlock);
		},

		showErrorTemplate: function(errorMessage)
		{
			var errorBlock = document.createElement('div');
			errorBlock.innerHTML = errorMessage;
			errorBlock.classList.add("alert");
			errorBlock.classList.add("alert-danger");
			this.salePaySystemWrapperNode.innerHTML = '';
			this.salePaySystemWrapperNode.appendChild(errorBlock);
		},
	};
})();