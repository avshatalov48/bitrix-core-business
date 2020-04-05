BX.namespace("BX.Marketplace.LocalappList");

BX.Marketplace.LocalappList = {
	init: function(params)
	{
		this.ajaxUrl = params.url || "";
	},
	delete: function(id)
	{
		if (confirm(BX.message("APPLIST_DELETE_CONFIRM")))
		{
			BX.ajax({
				method: 'POST',
				url: this.ajaxUrl,
				dataType: 'json',
				data: {
					appId: id,
					action: "delete",
					sessid: BX.bitrix_sessid()
				},
				onsuccess: function(json)
				{
					if (json.error)
					{
						alert(BX.message("APPLIST_DELETE_ERROR"));
					}
					else
					{
						BX.reload();
					}
				}
			});
		}
	}
};