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
		showPopup: function (code, id = null)
		{
			if (!B24 || !B24.licenseInfoPopup)
			{
				return;
			}
			switch (code)
			{
				case 'Ad':
					if (id === 'toloka')
					{
						code = 'limit_integration_yandex_toloka';
					}
					else
					{
						code = 'limit_crm_marketing_adv';
					}
					break;
				case 'Rc':
					code = 'limit_crm_marketing_sales_generator';
					break;
				case 'Mail_limit':
				case 'Mailing':
					code = 'limit_crm_marketing_email'
					break;
				default:
					code = 'limit_crm_marketing_sms';
					break;
			}

			BX.UI.InfoHelper.show(code);
		}
	};

})(window);