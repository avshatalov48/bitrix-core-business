// eslint-disable-next-line max-lines-per-function
(function() {
	BX.namespace('BX.Wizard');
	BX.Wizard.PgSql = {
		nextButtonID: '',
		formID: '',
		LANG: '',
		path: '',
		sessid: '',
		connection: '',

		init(params)
		{
			if (BX.Type.isObject(params))
			{
				this.nextButtonID = params.nextButtonID || '';
				this.formID = params.formID || '';
				this.LANG = params.LANG;
				this.path = params.path || '';
				this.sessid = params.sessid || '';
				this.connection = params.connection || '';
			}
		},

		action(action, nextStep = '')
		{
			BX.ajax({
				method: 'POST',
				dataType: 'html',
				url: `${this.path}/scripts/${action}.php`,
				data: {
					sessid: this.sessid,
					lang: this.LANG,
					connection: this.connection,
					next: nextStep,
				},
				onsuccess: BX.delegate((result) => {
					const obContainer = document.getElementById('output');
					if (obContainer)
					{
						obContainer.innerHTML = result;
						if (result.includes('pgwiz_err'))
						{
							document.forms[this.formID].NextStepID.value = document.forms[this.formID].CurrentStepID.value;
							const obNextButton = document.forms[this.formID][this.nextButtonID];
							obNextButton.disabled = false;
							obNextButton.value = BX.Loc.getMessage('PGWIZ_RETRYSTEP_BUTTONTITLE');
						}
					}
				}, this),
				onfailure: BX.delegate((type, status, config) => {
					const obContainer = document.getElementById('output');
					if (obContainer)
					{
						obContainer.innerHTML = BX.Loc.getMessage('PGWIZ_FIX_AND_RETRY');
						document.forms[this.formID].NextStepID.value = document.forms[this.formID].CurrentStepID.value;
						const obNextButton = document.forms[this.formID][this.nextButtonID];
						obNextButton.disabled = false;
						obNextButton.value = BX.Loc.getMessage('PGWIZ_RETRYSTEP_BUTTONTITLE');
					}
				}, this),
			});
		},

		RunError()
		{
			const obErrorMessage = document.getElementById('error_message');
			if (obErrorMessage)
			{
				BX.Dom.style(obErrorMessage, 'display', 'inline');
			}
		},

		RunAgain()
		{
			const obOut = document.getElementById('output');
			const obErrorMessage = document.getElementById('error_message');

			obOut.innerHTML = '';
			BX.Dom.style(obErrorMessage, 'display', 'none');
			window.Run(1);
		},

		DisableButton()
		{
			const obNextButton = document.forms[this.formID][this.nextButtonID];
			obNextButton.disabled = true;
		},

		EnableButton()
		{
			const obNextButton = document.forms[this.formID][this.nextButtonID];
			obNextButton.disabled = false;
		},
	};
})();
