/**
 * Class BX.Sale.Orderdocument
 */
(function() {
	'use strict';

	if (!BX.Sale)
		BX.Sale = {};

	if (BX.Sale.Orderdocument)
		return;

	BX.Sale.Orderdocument = {
		init: function(params)
		{
			this.paysystemBlockNode = BX(params.paysystemBlockId);
			this.ajaxUrl = params.ajaxUrl;
			this.paymentId = params.paymentId;
			this.paySystemId = params.paySystemId;
			this.template = params.template;

			this.reload();
		},

		reload: function()
		{
			var data = {
				sessid: BX.bitrix_sessid(),
				PAYMENT_ID: this.paymentId,
				PAYSYSTEM_ID: this.paySystemId,
				RETURN_URL: this.returnUrl,
				template: this.template,
			};

			BX.ajax({
				method: "POST",
				dataType: 'json',
				url: this.ajaxUrl,
				data: data,
				onsuccess: BX.proxy(function (result) {
					if (result.status === 'success')
					{
						this.updateTemplateHtml(result.template);

						if (result.data.hasOwnProperty('pdfUrl'))
						{
							window.location.href = result.data.pdfUrl;
						}
					}
					else if (result.status === 'error')
					{
						this.showErrorTemplate(result.buyerErrors);
						BX.onCustomEvent('onPaySystemAjaxError', [result.buyerErrors]);
					}
				}, this),
				onfailure: BX.proxy(function () {
					this.showErrorTemplate();
					BX.onCustomEvent('onPaySystemAjaxError');
				}, this)
			});
		},

		updateTemplateHtml: function (html)
		{
			BX.html(this.paysystemBlockNode, html)
		},

		showErrorTemplate: function(errors)
		{
			var errorsList = [
				BX.message('SALE_DOCUMENT_HANDLER_ERROR_MESSAGE_HEADER'),
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

			errorsList.push(BX.message('SALE_DOCUMENT_HANDLER_ERROR_MESSAGE_FOOTER'));

			var resultDiv = BX.create('div', {
				props: {className: 'alert alert-danger'},
				html: errorsList.join('<br />'),
			});

			this.paysystemBlockNode.innerHTML = '';
			this.paysystemBlockNode.appendChild(resultDiv);
		},
	}
})();
