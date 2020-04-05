;(function () {

	BX.namespace('BX.Sender');

	BX.Sender.B24License = {
		getTitle: function (code)
		{
			return BX.message('SENDER_B24_LICENSE_' + code.toUpperCase() + '_TITLE');
		},
		getText: function (code)
		{
			var messageCode = 'SENDER_B24_LICENSE_%code%_TEXT'.replace('%code%', code.toUpperCase());
			var mess = BX.message(messageCode);
			if (!mess)
			{
				mess = BX.message(messageCode + '1');
			}

			return mess;
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
				'<span>' + (this.getText(code) ? this.getText(code) : this.getText('Ad')) + '</span>',
				true
			);
		}
	};

})(window);