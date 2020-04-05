;(function () {
	BX.namespace('BX.Rest.Configuration.Import');
	if (!BX.Rest.Configuration.Import)
	{
		return;
	}
	/**
	 * Import.
	 *
	 */
	function Import()
	{
	}

	Import.prototype =
		{
			init: function (params)
			{
				this.id = params.id;
				this.signedParameters = params.signedParameters;

				BX.bind(
					BX(this.id + '-file-upload'),
					'change',
					BX.delegate(
						function (event) {
							this.submitForm(event);
						},
						this
					)
				);
			},

			submitForm: function (event) {
				event.preventDefault();
				BX(this.id + '-file-form').submit();
			}
		};

	BX.Rest.Configuration.Import =  new Import();
})(window);