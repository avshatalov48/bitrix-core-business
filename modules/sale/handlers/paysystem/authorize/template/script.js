/**
 * Class BX.Sale.Authorize
 */
(function() {
	'use strict';

	if (!BX.Sale)
		BX.Sale = {};

	if (BX.Sale.Authorize)
		return;

	BX.Sale.Authorize = {
		init: function(params)
		{
			this.formNode = BX(params.formId);
			this.paysystemBlockNode = BX(params.paysystemBlockId);
			this.ajaxUrl = params.ajaxUrl;
			this.paymentId = params.paymentId;
			this.paySystemId = params.paySystemId;
			this.isAllowedSubmitting = true;

			this.bindEvents();
		},

		bindEvents: function()
		{
			BX.bind(this.formNode, 'submit', BX.proxy(this.sendRequest, this));
		},

		sendRequest: function(e)
		{
			e.preventDefault();

			if (!this.isAllowedSubmitting)
			{
				return;
			}

			var data,
				formData = this.getAllFormData(),
				submitButton = this.formNode.querySelector('input[type="submit"]'),
				i;

			if (submitButton)
			{
				submitButton.disabled = true;
			}
			this.isAllowedSubmitting = false;

			data = {
				sessid: BX.bitrix_sessid(),
				PAYMENT_ID: this.paymentId,
				PAYSYSTEM_ID: this.paySystemId,
			};

			for (i in formData)
			{
				if (formData.hasOwnProperty(i))
				{
					data[i] = formData[i];
				}
			}

			BX.ajax({
				method: "POST",
				dataType: 'json',
				url: this.ajaxUrl,
				data: data,
				onsuccess: BX.proxy(function (result) {
					if (result.status === 'success')
					{
						this.isAllowedSubmitting = true;
						this.updateTemplateHtml(result.template);
					}
					else if (result.status === 'error')
					{
						this.isAllowedSubmitting = true;
						this.showErrorTemplate(result.buyerErrors);
						BX.onCustomEvent('onPaySystemAjaxError', [result.buyerErrors]);
					}
				}, this)
			});
		},

		getAllFormData: function()
		{
			var prepared = BX.ajax.prepareForm(this.formNode),
				i;

			for (i in prepared.data)
			{
				if (prepared.data.hasOwnProperty(i) && i === '')
				{
					delete prepared.data[i];
				}
			}

			return !!prepared && prepared.data ? prepared.data : {};
		},

		updateTemplateHtml: function (html)
		{
			BX.html(this.paysystemBlockNode, html).then(function(){
				BX.onCustomEvent('onPaySystemUpdateTemplate');
			}.bind(this));
		},

		showErrorTemplate: function(errors)
		{
			var errorsList = [
				BX.message('SALE_HPS_AUTHORIZE_ERROR_MESSAGE_HEADER'),
			];
			if (errors)
			{
				for (var error in errors)
				{
					if (errors.hasOwnProperty(error))
					{
						errorsList.push(errors[error]);
					}
				}
			}

			errorsList.push(BX.message('SALE_HPS_AUTHORIZE_ERROR_MESSAGE_FOOTER'));

			var resultDiv = BX.create('div', {
				props: {className: 'alert alert-danger'},
				html: errorsList.join('<br />'),
			});

			this.paysystemBlockNode.innerHTML = '';
			this.paysystemBlockNode.appendChild(resultDiv);
		},
	}
})();
