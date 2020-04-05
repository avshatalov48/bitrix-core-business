BX.namespace("BX.Marketplace.Hook.Event");

BX.Marketplace.Hook.Event = {
	init: function(params)
	{
		this.ajaxUrl = params.url || "";
	},
	delete: function(id)
	{
		if (confirm(BX.message("REST_HOOK_DELETE_CONFIRM")))
		{
			BX.ajax({
				method: 'POST',
				url: this.ajaxUrl,
				dataType: 'json',
				data: {
					apId: id,
					action: "delete",
					sessid: BX.bitrix_sessid()
				},
				onsuccess: function(json)
				{
					if (json.error)
					{
						alert(BX.message("REST_HOOK_DELETE_ERROR"));
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
