(function (w) {
	function MainUserConsentListManager(params)
	{
		this.init = function (params)
		{
			BX.addCustomEvent(window, 'main-user-consent-to-list', function () {
				if (!BX.Bitrix24 || !BX.Bitrix24.Slider)
				{
					return;
				}

				BX.Bitrix24.Slider.close();
			});

			BX.ready(this.initSlider.bind(this));
		};

		this.initSlider = function()
		{
			if (!BX.Bitrix24 || !BX.Bitrix24.Slider)
			{
				return;
			}

			var cont = BX('MAIN_USER_CONSENT_AGREEMENT_GRID');
			if (!cont)
			{
				return;
			}

			var list = cont.querySelectorAll('a[data-bx-slider-href]');
			list = BX.convert.nodeListToArray(list);
			list.forEach(function (node) {
				var _this = this;
				BX.bind(node, 'click', function (e) {
					e.preventDefault();
					_this.open(node.href);
				});
			}, this);
		};

		this.remove = function(agreementId, uiGridId)
		{
			var grid = BX.Main.gridManager.getInstanceById(uiGridId);
			grid.removeRow(agreementId.toString());
		};

		this.open = function(url)
		{
			if (!BX.Bitrix24 || !BX.Bitrix24.Slider)
			{
				window.location.href = url;
				return;
			}

			BX.Bitrix24.Slider.open(url);
		};

		this.init(params);
	}

	w.BX.UserConsentListManager = new MainUserConsentListManager();
})(window);