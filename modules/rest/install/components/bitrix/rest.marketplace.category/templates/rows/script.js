BX.namespace("BX.Rest.Markeplace.CategoryRows");
BX.Rest.Markeplace.CategoryRows = {
	currentAppCode: null,

	init: function ()
	{
		BX.addCustomEvent("Rest:AppLayout:ApplicationInstall", BX.proxy(function (param, result)
		{
			this.changeButton();
		}, this));
	},

	setCurrentApp: function (code)
	{
		if (!code)
			return;

		this.currentAppCode = code;
	},

	changeButton: function ()
	{
		var button = document.querySelector("[data-id='btn-" + this.currentAppCode + "']");
		if (BX.type.isDomNode(button))
		{
			if (BX.hasClass(button, "ui-btn-success"))
			{
				BX.addClass(button, "ui-btn-light-border");
				BX.removeClass(button, "ui-btn-success");
				button.innerHTML = BX.message("MARKETPLACE_CATEGORY_ROWS_DEINSTALL");
			}
			else
			{
				BX.addClass(button, "ui-btn-success");
				BX.removeClass(button, "ui-btn-light-border");
				button.innerHTML = BX.message("MARKETPLACE_CATEGORY_ROWS_INSTALL");
			}
		}
	}
};