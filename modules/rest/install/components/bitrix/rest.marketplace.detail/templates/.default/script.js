BX.namespace("BX.Rest.Marketplace");

BX.Rest.Marketplace.Detail = (function()
{
	var Detail = function(params)
	{
		params = typeof params === "object" ? params : {};

		this.ajaxPath = params.ajaxPath || null;
		this.siteId = params.siteId || null;
		this.appName = params.appName || "";
		this.appCode = params.appCode || "";

		if (BX.type.isDomNode(BX("detail_cont")))
		{
			var employeeInstButton = BX("detail_cont").getElementsByClassName("js-employee-install-button");

			if (BX.type.isDomNode(employeeInstButton[0]))
			{
				BX.bind(employeeInstButton[0], "click", BX.proxy(function(){
					this.sendInstallRequest(BX.proxy_context);
				},this));
			}
		}
	};

	Detail.prototype.sendInstallRequest = function(element)
	{
		BX.PopupWindowManager.create("mp-detail-block", element, {
			content: BX.message("MARKETPLACE_APP_INSTALL_REQUEST"),
			angle: {offset : 35 },
			offsetTop:8,
			autoHide:true
		}).show();

		BX.ajax({
			method: "POST",
			dataType: "json",
			url: this.ajaxPath,
			data: {
				sessid : BX.bitrix_sessid(),
				site_id : this.siteId,
				action: "sendInstallRequest",
				appName: this.appName,
				appCode: this.appCode
			},
			onsuccess: function()
			{

			},
			onfailure: function()
			{
			}
		});
	};

	return Detail;
})();





