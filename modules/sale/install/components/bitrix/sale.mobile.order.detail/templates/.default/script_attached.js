__MASaleOrderDetail = function(params) {

	for(var key in params)
		this[key] = params[key];

};

__MASaleOrderDetail.prototype.setOrderHtml = function(orderHtml)
{
	var orderDomObj = BX("order_detail_"+this.id);

	if(orderDomObj)
		orderDomObj.innerHTML = orderHtml;
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

	//app.showPopupLoader({"text":"loading order"});

	BX.ajax({
		timeout:   30,
		method:   'POST',
		dataType: 'html',
		url:       this.ajaxUrl,
		data:      postData,
		onsuccess: function(result) {
			//app.hidePopupLoader();
			if(result)
			{
				_this.setOrderHtml(result);
			}
			else
			{
			//	alert(" __MASaleOrderDetail.prototype.updateOrder !result"); //develop
			}
		},
		onfailure: function(){
			//alert("__MASaleOrderDetail.prototype.updateOrder failure"); //develop
		}
	});
};

__MASaleOrderDetail.prototype.getHistory = function(id)
{
	var _this = this;

	postData = {
		action: 'get_history',
		id: id
	};

	//app.showPopupLoader({"text":"loading history"});

	BX.ajax({
		timeout:   30,
		method:   'POST',
		dataType: 'html',
		url:       this.ajaxUrl,
		data:      postData,
		onsuccess: function(result) {
			//app.hidePopupLoader();
			if(result)
			{
				_this.setOrderHtml(result);
			}
			else
			{
				//alert(" __MASaleOrderDetail.prototype.getHistory !result"); //develop
			}
		},
		onfailure: function(){
			//alert("__MASaleOrderDetail.prototype.getHistory failure"); //develop
		}
	});
};

__MASaleOrderDetail.prototype.getTransact = function(id)
{
	var _this = this;

	postData = {
		action: 'get_transact',
		id: id
	};

	//app.showPopupLoader({"text":"loading transactions"});

	BX.ajax({
		timeout:   30,
		method:   'POST',
		dataType: 'html',
		url:       this.ajaxUrl,
		data:      postData,
		onsuccess: function(result) {
			//app.hidePopupLoader();
			if(result)
			{
				_this.setOrderHtml(result);
			}
			else
			{
				//alert(" __MASaleOrderDetail.prototype.getTransact !result"); //develop
			}
		},
		onfailure: function(){
			//alert("__MASaleOrderDetail.prototype.getTransact failure"); //develop
		}
	});
};

__MASaleOrderDetail.prototype.dialogShow = function(dialog)
{
	app.showModalDialog({
		url: this.dialogUrl+"?action=get_"+dialog+"_dialog&id="+this.id
	});
};