/* Orders list object begin*/
__MASaleOrdersList = function(params) {

	//lastOrder, filter, ajaxUrl, dialogUrl, orderDetailPath
	for(var key in params)
		this[key] = params[key];

	this.bottomReached = false; //if recieved all of data
	this.bottomOrdersRequesting = false; //if ajax requesting now

	var d = new Date();
	this.lastUpdateTS = d.getTime(); //todo: timezones?
};


__MASaleOrdersList.prototype.setUpdateTime = function (postData, callback)
{
	var d = new Date();
	this.lastUpdateTS = d.getTime();
	return true;
};

__MASaleOrdersList.prototype.getUpdateTime = function (postData, callback)
{
	return this.lastUpdateTS;
};

__MASaleOrdersList.prototype.ajaxRequest = function (postData, callback)
{
	var _this = this;
	postData.sessid = BX.bitrix_sessid();
	postData.order_detail_path = this.orderDetailPath;

	if(this.filter)
		postData.filter = this.filter;

	//app.showPopupLoader({"text":"loading"}); //develop

	BX.ajax({
		timeout:   30,
		method:   'POST',
		dataType: 'json',
		url:       this.ajaxUrl,
		data:      postData,
		onsuccess: function(result) {
			//app.hidePopupLoader();
			if(result)
			{
				if(result.error)
				{
					//alert("__MASaleOrdersList.prototype.ajaxRequest: result.error"); //develop
				}
				else
				{
					if(callback && typeof callback == 'function')
					{
						callback.call(_this, result);
					}
				}
			}
			else
			{
				//alert("__MASaleOrdersList.prototype.ajaxRequest: !result"); //develop
			}
		},
		onfailure: function(){
			//alert("__MASaleOrdersList.prototype.ajaxRequest: callback error"); //develop
		}
	});
};

/*get orders updated after this.lastUpdateTS */
__MASaleOrdersList.prototype.getUpdatedOrders = function ()
{
	var _this = this;

	postData = {
		action: 'get_updated_orders',
		timestamp: this.getUpdateTime()
	};

	this.ajaxRequest(postData, function(result){

						if(!result.orders)
							return false;

						_this.setUpdateTime();
						for(var i in result.orders)
						{
							var orderDomObj = BX("order-"+i);

							if(orderDomObj)
								_this.updateOrderHtml(i, result.orders[i]);
							else
								_this.addOrderHtml(i, result.orders[i], _this.getFirstOrderId());
						}
	});

	return true;
};

__MASaleOrdersList.prototype.deleteOrder = function(orderId)
{
	var orderObj = BX("order-"+orderId);

	if(!orderObj)
		return false;

	orderObj.parentNode.removeChild(orderObj);
	return true;
};

__MASaleOrdersList.prototype.updateOrderHtml = function(orderId, htmlOrder)
{
	if(!htmlOrder || !orderId)
		return false;

	var oldOrderDomObj = BX("order-"+orderId),
		newOrderDomObj = this.createOrderObj(htmlOrder);

	if(!oldOrderDomObj || !newOrderDomObj)
		return false;

	oldOrderDomObj.parentNode.replaceChild(newOrderDomObj, oldOrderDomObj);

	return true;
};

__MASaleOrdersList.prototype.onOrderAdd = function(orderId)
{
	if(!orderId)
		return false;

	var _this = this;

	postData = {
		action: 'get_order',
		id: orderId
	};

	this.ajaxRequest(postData, function(result){
			if(!result || !result.orders[orderId])
				return false;

			_this.addOrderHtml(orderId, result.orders[orderId], _this.getFirstOrderId());
	});

};

__MASaleOrdersList.prototype.getFirstOrderId = function()
{
	var ordersListObj = BX("orders-list");

	if(!ordersListObj)
		return false;

	var firstOrderId = ordersListObj.childNodes[0];

	if(firstOrderId && firstOrderId.id)
		return firstOrderId.id.substr(6);
	else
		return false;
};


__MASaleOrdersList.prototype.createOrderObj = function(orderHtml)
{
	if(!orderHtml)
		return false;

	var orderDomObjCont = BX("new_orders_container");

	if(!orderDomObjCont)
	{
		orderDomObjCont= document.createElement("DIV");
		orderDomObjCont.id = "new_orders_container";
		orderDomObjCont.style.display = "none";
	}

	orderDomObjCont.innerHTML = orderHtml;

	return orderDomObjCont.childNodes[0];
};

__MASaleOrdersList.prototype.addOrderHtml = function(orderId, orderHtml, beforeOrderId)
{
	if(!orderHtml || !orderId)
		return false;

	if(BX("order-"+orderId))
		return false;


	var orderDomObj = this.createOrderObj(orderHtml),
		ordersListObj = BX("orders-list");

	if(beforeOrderId)
	{
		var beforeOrderObj = BX("order-"+beforeOrderId);

		if(beforeOrderObj)
			ordersListObj.insertBefore(orderDomObj, beforeOrderObj); //todo: if(!beforeOrderObj)
		else
			return false;
	}
	else
	{
		ordersListObj.appendChild(orderDomObj);
		this.lastOrder = orderId;
	}

	return true;
};

__MASaleOrdersList.prototype.onOrderUpdate = function(orderId)
{
	if(!orderId)
		return false;

	var _this = this;

	postData = {
		action: 'get_order',
		id: orderId
	};

	this.ajaxRequest(postData, function(result){
			if(!result || !result.orders[orderId])
				return false;

			_this.updateOrderHtml(orderId, result.orders[orderId]);
	});
};

__MASaleOrdersList.prototype.getBottomOrders = function()
{
	if(this.bottomReached)
		return false;

	if(this.bottomOrdersRequesting)
		return false;

	this.bottomOrdersRequesting = true;

	var _this = this;

	postData = {
		action: 'get_orders',
		last: this.lastOrder
	};

	this.ajaxRequest(postData, function(result){

					_this.bottomOrdersRequesting = false;

					if(result.bottomReached)
						_this.bottomReached = true;

					if(!result.orders)
					{
						//alert("getBottomOrders !result"); //develop
					}
					else
					{
						for(var i in result.orders)
							_this.addOrderHtml(i, result.orders[i]);
					}
			});
};

__MASaleOrdersList.prototype.dialogShow = function(dialog)
{
	app.showModalDialog({
		url: this.dialogUrl+"?action=get_dialog&dialog_name="+dialog
	});
};
/* Orders list object end*/
