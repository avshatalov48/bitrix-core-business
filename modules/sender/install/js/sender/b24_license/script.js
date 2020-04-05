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
			var mess = '';
			if (BX.message[messageCode])
			{
				mess = BX.message(messageCode);
			}
			if (!mess)
			{
				messageCode = messageCode + '1';
			}
			if (BX.message[messageCode])
			{
				mess = BX.message(messageCode);
			}

			return mess;
		},
		showDefaultPopup: function ()
		{
			this.showPopup('Ad');
		},
		showMailingPopup: function ()
		{
			this.showPopup('Mailing');
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