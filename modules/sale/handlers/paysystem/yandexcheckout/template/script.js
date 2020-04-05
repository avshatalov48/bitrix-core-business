/**
 * Class BX.Sale.Yandexcheckout
 */
(function() {
	'use strict';

	if (!BX.Sale)
		BX.Sale = {};

	if (BX.Sale.Yandexcheckout)
		return;

	BX.Sale.Yandexcheckout = {
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
				PAYSYSTEM_ID: this.paySystemId
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
						this.showErrorTemplate();
						BX.onCustomEvent('onPaySystemAjaxError');
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
			var data = BX.processHTML(html);
			BX.loadCSS(data['STYLE']);
			this.paysystemBlockNode.innerHTML = data['HTML'];

			for (var i in data['SCRIPT'])
			{
				if (data['SCRIPT'].hasOwnProperty(i))
				{
					BX.evalGlobal(data['SCRIPT'][i]['JS']);
				}
			}
		},

		showErrorTemplate: function()
		{
			var resultDiv = BX.create('div', {
				props: {className: 'alert alert-danger'},
				text: BX.message('SALE_HANDLERS_PAY_SYSTEM_YANDEX_CHECKOUT_ERROR_MESSAGE')
			});

			this.paysystemBlockNode.innerHTML = '';
			this.paysystemBlockNode.appendChild(resultDiv);
		},

		PaymentPhoneForm: function(params)
		{
			this.init = function(params)
			{
				this.phoneFormatDataUrl = params.phoneFormatDataUrl || null;
				this.phoneCountryCode = params.phoneCountryCode || null;

				// Form
				this.form = BX(params.form);

				if(this.form)
				{
					this.initPhoneControls();
				}
			};

			this.initPhoneControls = function()
			{
				var inputList = BX.convert.nodeListToArray(this.form.querySelectorAll('.js-paysystem-yandex-input-phone'));
				inputList.forEach(function(inputNode){
					this.initPhoneControl(inputNode, false);
				}, this);
			};

			this.initPhoneControl = function(node)
			{
				if(!node)
				{
					return;
				}

				var dataNode = node.nextElementSibling;

				new BXMaskedPhone({
					url: this.phoneFormatDataUrl,
					country: this.phoneCountryCode,
					'maskedInput': {
						input: node,
						dataInput: dataNode
					}
				});

				this.initDisplayedToDataControlEvents(node, dataNode);
			};

			this.initDisplayedToDataControlEvents = function(displayedNode, dataNode)
			{
				BX.bind(displayedNode, 'blur', function () {
					BX.fireEvent(dataNode, 'blur');
				});
				BX.bind(displayedNode, 'focus', function () {
					BX.fireEvent(dataNode, 'focus');
				});
			};

			this.init(params);
		}
	}
})();