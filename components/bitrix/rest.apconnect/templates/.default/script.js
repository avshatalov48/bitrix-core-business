;(function () {
	BX.namespace('BX.Rest.ApConnect');
	if (!BX.Rest.ApConnect)
	{
		return;
	}
	/**
	 * Ap Connect.
	 */
	function ApConnect()
	{
	}

	ApConnect.prototype = {
		init: function(params)
		{
			this.isRestAvailable = params.isRestAvailable === 'Y';
			this.formId = params.formId;
			this.landingCode = params.landingCode;

			if (!this.isRestAvailable && this.landingCode !== '')
			{
				this.eventHold = true;
				BX.bind(
					BX(this.formId),
					'submit',
					BX.delegate(
						function (event)
						{
							if (this.eventHold)
							{
								event.preventDefault();
								this.openLanding();
							}
						},
						this
					)
				);
			}
		},

		openLanding: function()
		{
			if (this.landingCode !=='')
			{
				top.BX.UI.InfoHelper.show(this.landingCode);
				this.eventHold = true;
			}
		}
	}

	BX.Rest.ApConnect =  new ApConnect();
})(window);