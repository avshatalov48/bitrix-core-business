BX.namespace('BX.Sale.DomainVerificationForm');

(function() {
	'use strict';

	BX.Sale.DomainVerificationForm = {
		init: function(params) {
			this.signedParameters = params.signedParameters;
			this.saveButton = BX(params.saveButtonId);
			this.closeButton = BX(params.closeButtonId);
			this.form = BX(params.formId);
			this.fileInputButton = BX(params.fileInputId);
			this.fileInputLabel = BX(params.fileLabelId);

			this.bindEvents();
		},

		bindEvents: function()
		{
			BX.addCustomEvent('button-click', BX.proxy(this.buttonFormClick, this));

			BX.bind(this.fileInputButton, 'change', BX.delegate((event) => {
				const files = event.target.files;
				let labelText = BX.Loc.getMessage('SALE_DVF_TEMPLATE_FILE_NOT_SELECTED');

				if (files.length > 0)
				{
					let fileName = files[0].name;

					if (fileName.length > 40)
					{
						fileName = `${fileName.slice(0, 9)}...${fileName.slice(-9)}`;
					}

					labelText = fileName;
				}

				this.fileInputLabel.innerHTML = labelText;
			}, this));
		},

		buttonFormClick: function(button)
		{
			if (button.TYPE === 'save' || button.TYPE === 'close')
			{
				button.WAIT = false;
			}

			if (button.TYPE === 'save')
			{
				button.WAIT = true;
				this.submitForm();
			}
		},

		submitForm: function()
		{
			this.form.submit();
		},

		showLoader: function()
		{
			if (this.loader === undefined)
			{
				this.loader = new BX.Loader({
					target: document.querySelector('.ui-slider-page'),
				});
			}

			this.loader.show();
		},

		deleteDomainAction: function(id) {
			this.showLoader();

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