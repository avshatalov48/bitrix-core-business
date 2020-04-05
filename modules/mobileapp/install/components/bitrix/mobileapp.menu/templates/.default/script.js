Menu = {
	currentItem: null,
	ajaxUrl: null,
	pushParams: null,

	init : function(params)
	{
		this.currentItem = params.currentItem;
		this.ajaxUrl = params.ajaxUrl;
		this.pushParams = params.pushParams;

		var items = document.getElementById("menu-items"),
			that = this;

		items.addEventListener("click", function(event) {that.onItemClick(event); }, false);
	},

	onItemClick : function(event)
	{
		var target = event.target;
		if (target && target.nodeType && target.nodeType == 1 && BX.hasClass(target, "menu-item"))
		{
			if (this.currentItem != null)
				this.unselectItem(this.currentItem);
			this.selectItem(target);

			var url = target.getAttribute("data-url");
			var pageId = target.getAttribute("data-pageid");

			if(BX.type.isNotEmptyString(url) && BX.type.isNotEmptyString(pageId))
				app.loadPage(url, pageId);
			else if(BX.type.isNotEmptyString(url))
				app.loadPage(url);

			this.currentItem = target;
		}

	},

	getPushParamsHead : function(params)
	{
		var result = '';

		if(params)
		{
			var pushParamsHead = (params+'').split("_");

			if(pushParamsHead[0])
				result = pushParamsHead[0];
		}

		return result;
	},

	onOpenPush : function(params)
	{
		if(!this.pushParams || !params.params)
			return;

		var pushParamsHead = this.getPushParamsHead(params.params);

		if(!pushParamsHead)
			return;

		if(this.pushParams[pushParamsHead])
		{
			url = this.pushParams[pushParamsHead]["data-url"];
			url += (url.indexOf('?') >= 0 ? '&' : '?') + 'on_open_push=Y';
			if(BX.type.isNotEmptyString(url) && BX.type.isNotEmptyString(this.pushParams[pushParamsHead]["data-pageid"]))
				app.loadPageStart({
					url: url,
					page_id: this.pushParams[pushParamsHead]["data-pageid"]
				});
			else if(BX.type.isNotEmptyString(url))
				app.loadPageStart({url: url});
		}
	},

	selectItem : function(item)
	{
		if (!BX.hasClass(item, "menu-item-selected"))
			BX.addClass(item, "menu-item-selected");
	},

	unselectItem : function(item)
	{
		BX.removeClass(item,"menu-item-selected");
	},

	getToken : function ()
	{
		var _this = this,
			dt = "APPLE";

		if (platform != "ios")
			dt = "GOOGLE";

		var params = {
			callback: function (token)
			{
				var postData = {
					action: "save_device_token",
					device_name: device.name,
					uuid: device.uuid,
					device_token: token,
					device_type: dt,
					sessid: BX.bitrix_sessid()
				};

				BX.ajax({
					timeout:   30,
					method:   'POST',
					dataType: 'json',
					url:       _this.ajaxUrl,
					data:      postData,
					onsuccess: function(result)
					{
						//TODO
					},
					onfailure: function()
					{
						//TODO
					}
				});
			}
		};

		return app.exec("getToken", params);
	}
};
