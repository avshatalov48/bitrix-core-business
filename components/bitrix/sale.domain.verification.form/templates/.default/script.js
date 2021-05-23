BX.namespace('BX.Sale.DomainVerificationForm');

(function() {
	'use strict';

	BX.Sale.DomainVerificationForm = {
		init: function(params) {
			this.signedParameters = params.signedParameters;
			this.saveButton = BX(params.saveButtonId);
			this.closeButton = BX(params.closeButtonId);
			this.form = BX(params.formId);

			this.bindEvents();
		},

		bindEvents: function() {
			BX.addCustomEvent('button-click', BX.proxy(this.buttonFormClick, this));
		},

		buttonFormClick: function(button)
		{
			if (button.TYPE === 'save' || button.TYPE === 'close')
			{
				button.WAIT = false;
			}

			if (button.TYPE === 'save')
			{
				this.submitForm();
			}
		},

		submitForm: function()
		{
			this.form.submit();
		},

		deleteDomainAction: function(id) {
			BX.ajax.runComponentAction('bitrix:sale.domain.verification.form', 'deleteDomain', {
				mode: 'ajax',
				signedParameters: this.signedParameters,
				data: {
					id: id
				},
			}).then(function (response) {
				BX.SidePanel.Instance.reload();
			}, function (response) {
				var errors = "";
				for (var i in response.errors)
				{
					if (response.errors.hasOwnProperty(i))
					{
						errors += (response.errors[i].message + "\n");
					}
				}
				alert(errors);
			});
		}
	}
})();