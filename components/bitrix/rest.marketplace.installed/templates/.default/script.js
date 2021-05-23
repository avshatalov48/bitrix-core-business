'use strict';

BX.namespace("BX.Rest.Markeplace.Installed");

BX.Rest.Markeplace.Installed = {
	init: function (params)
	{
		if (typeof params === "object" && params)
		{
			this.ajaxPath = params.ajaxPath || "";
			this.filterId = params.filterId || "";
		}
	},

	initEvents: function()
	{
		BX.addCustomEvent('BX.Main.Filter:apply', this.onApplyFilter);
	},

	onApplyFilter: function (id, data, ctx, promise, params)
	{
		if (id !== BX.Rest.Markeplace.Installed.filterId)
			return;

		params.autoResolve = false;

		var loader = new BX.Loader({
			target: BX("mp-installed-block"),
			offset: {top: "150px"}
		});
		loader.show();

		BX.ajax({
			method: 'POST',
			dataType: 'html',
			url: BX.Rest.Markeplace.Installed.ajaxPath,
			data: {
				action: "setFilter",
				sessid: BX.bitrix_sessid()
			},
			onsuccess: BX.proxy(function (html) {
				BX("mp-installed-block").innerHTML = html;
				loader.hide();

				promise.fulfill();
			}, this),
			onfailure: function () {
				promise.reject();
			}
		});
	}
};