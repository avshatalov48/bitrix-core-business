/**
 * Class BX.Sale.PaySystemCashbox
 */
(function(window) {

	if (!BX.Sale)
		BX.Sale = {};

	if (BX.Sale.PaySystemCashbox) return;

	BX.Sale.PaySystemCashbox = {

		ajaxUrl: "sale_pay_system_cashbox_ajax.php",

		toggleCashboxSetting: function (button) {
			if (button.checked)
			{
				this.showCashboxSettings();
			}
			else
			{
				this.hideCashBoxSettings();
			}
		},

		showCashboxSettings: function ()
		{
			var containerList = document.querySelectorAll('tbody[data^="pay-system-cashbox"]');
			containerList.forEach(function (container) {
				container.style.display = '';
			})
		},

		hideCashBoxSettings: function ()
		{
			var containerList = document.querySelectorAll('tbody[data^="pay-system-cashbox"]');
			containerList.forEach(function (container) {
				container.style.display = 'none';
			})
		},

		reloadSettings: function ()
		{
			var paySystemId = '';
			if (BX('ID'))
			{
				paySystemId = BX('ID').value;
			}

			var cashboxHandler = '';
			if (BX('HANDLER'))
			{
				cashboxHandler = BX('HANDLER').value;
			}

			var kkmId = '';
			if (BX('KKM_ID'))
			{
				kkmId = BX('KKM_ID').value;
			}

			if (paySystemId === '' || cashboxHandler === '' || kkmId === '')
			{
				return;
			}

			BX.showWait();

			BX.ajax({
				data: {
					'action': 'reload_settings',
					'paySystemId': paySystemId,
					'handler': cashboxHandler,
					'kkmId': kkmId,
					'sessid': BX.bitrix_sessid()
				},
				method: 'POST',
				dataType: 'json',
				url: this.ajaxUrl,
				onsuccess: BX.delegate(function(result) {
					BX.closeWait();
					if (result && result.hasOwnProperty('HTML') && BX('cashbox_edit_edit_table'))
					{
						BX.html(BX('cashbox_edit_edit_table'), result.HTML);
					}
				}, this)
			});
		}
	}
})(window);
