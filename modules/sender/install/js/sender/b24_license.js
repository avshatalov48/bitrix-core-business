;(function () {

	BX.namespace('BX.Sender');

	BX.Sender.B24License = {
		getTitle: function (code)
		{
			return BX.message('SENDER_B24_LICENSE_' + code.toUpperCase() + '_TITLE');
		},
		getText: function (code)
		{
			return BX.message('SENDER_B24_LICENSE_' + code.toUpperCase() + '_TEXT');
		},
		showDefaultPopup: function ()
		{
			this.showPopup('Ad');
		},
		showMailLimitPopup: function ()
		{
			this.showPopup('Mail_limit');
		},
		showPopup: function (code)
		{
			if (!B24 || !B24.licenseInfoPopup)
			{
				return;
			}

			B24.licenseInfoPopup.show(
				code,
				this.getTitle(code),
				'<span>' + this.getText(code) + '</span>',
				true
			);
		}
	};

})(window);