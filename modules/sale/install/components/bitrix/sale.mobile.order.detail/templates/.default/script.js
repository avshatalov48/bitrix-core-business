__MASaleOrderDetail = function(params) {

	for(var key in params)
		this[key] = params[key];
};

__MASaleOrderDetail.prototype.setOrderHtml = function(orderHtml)
{
	var orderDomObj = BX("detail_info_body_"+this.id);

	if(orderDomObj)
		orderDomObj.innerHTML = orderHtml;
};

__MASaleOrderDetail.prototype.ajaxRequest = function (postData, callback)
{
	var _this = this;
	postData["sessid"]=BX.bitrix_sessid();

	BX.ajax({
		timeout:   30,
		method:   'POST',
		dataType: 'html',
		url:       this.ajaxUrl,
		data:      postData,
		onsuccess: function(result) {
			if(result)
			{
				if(callback && typeof callback == 'function')
					callback.call(_this, result);
			}
		}
	});
};

__MASaleOrderDetail.prototype.updateOrder = function(params)
{
	var _this = this;

	if(!this.id || !params.id || this.id != params.id)
		return;

	postData = {
		action: 'get_order_html',
		id: params.id
	};

	postData["show_upper_buttons"] = this.showUpperButtons ? 'Y' : 'N';

	this.ajaxRequest(postData, function(result){ _this.setOrderHtml(result); });

};

__MASaleOrderDetail.prototype.getHistory = function(id)
{
	var _this = this;

	postData = {
		action: 'get_history',
		id: id
	};

	this.ajaxRequest(postData, function(result){ _this.setOrderHtml(result); });
};

__MASaleOrderDetail.prototype.getTransact = function(id)
{
	var _this = this;

	postData = {
		action: 'get_transact',
		id: id
	};

	this.ajaxRequest(postData, function(result){ _this.setOrderHtml(result); });
};

__MASaleOrderDetail.prototype.dialogShow = function(dialog)
{
	app.showModalDialog({
		url: this.dialogUrl+"?action=get_"+dialog+"_dialog&id="+this.id
	});
};

__MASaleOrderDetail.prototype.onItemCancelChange = function(params)
{
	if(!this.detailMenuItems || !this.detailMenuItems.items )
		return;

	for(var i in this.detailMenuItems.items)
	{
		if(this.detailMenuItems.items[i].icon == 'cancel')
		{
			this.detailMenuItems.items[i].name = params.cancel =='N' ? this.messages.cancel : this.messages.cancelCancel;
			this.menuShow();
		}
	}
};

__MASaleOrderDetail.prototype.menuShow = function()
{
	if(this.detailMenuItems)
	{
		app.menuCreate(this.detailMenuItems);

		app.addButtons({
			menuButton:
			{
				type:     'context-menu',
				style:    'custom',
				callback: function()
				{
					app.menuShow();
				}
			}
		});
	}
};