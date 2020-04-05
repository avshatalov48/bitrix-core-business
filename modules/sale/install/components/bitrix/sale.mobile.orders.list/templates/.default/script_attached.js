/* Orders list object begin*/
__MASaleOrdersList = function(params) {

	for(var key in params)
		this[key] = params[key];

	this.bottomReached = false;
	this.bottomOrdersRequesting = false;
	var d = new Date();
	this.lastUpdateTS = d.getTime();
};

__MASaleOrdersList.prototype.onPullHandler = function(data)
{
	//alert("__MASaleOrdersList.prototype.onPullHandler");

	var argsCheck = !!(
		data
		&& data.module_id
		&& (data.module_id === 'sale')
		&& data.command
		&& (
			(data.command === 'order_add')
			|| (data.command === 'order_update')
			|| (data.command === 'order_delete')
		)
		&& data.params
		&& data.params.ORDER_ID
		&& data.params.event_GUID
	);

	this.refreshData(data.command, data.params.TASK_ID);

	if (!argsCheck )
		return;
}

__MASaleOrdersList.prototype.refreshData = function ()
{
	//alert("__MASaleOrdersList.prototype.refreshData");
};

__MASaleOrdersList.prototype.ajaxRequest = function (postData, callback)
{
	var _this = this;
	//app.showPopupLoader({"text":"loading"});

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
						callback.call(_this, result);
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
__MASaleOrdersList.prototype.getUpdatedOrders = function ()
{
	var _this = this;

	postData = {
		action: 'get_updated_orders',
		timestamp: this.lastUpdateTS
	};

	if(this.filter)
		postData["filter"] = this.filter;

	this.ajaxRequest(postData, function(result){

						if(!result.orders)
							return;

						for(var i=0, l=result.orders.length; i<l; i++)
						{
							var orderDomObj = BX("order-"+result.orders[i]["ID"]);

							if(orderDomObj)
								_this.updateOrder(result.orders[i]);
							else
								_this.addOrder(result.orders[i]);
						}
	});
};

__MASaleOrdersList.prototype.deleteOrder = function(orderId)
{
	//alert("__MASaleOrdersList.prototype.deleteOrder");
	var orderObj = BX("order-"+orderId);

	if(orderObj)
	{
		orderObj.parentNode.removeChild(orderObj);
		return true;
	}

	return false;
};


__MASaleOrdersList.prototype.updateOrder = function(orderObj)
{
	if(!orderObj)
		return false;

	var order = new __MASaleOrderItem(orderObj, this);
	var updatedOrderDomObj = BX("order-"+orderObj["ID"]);

	if(!updatedOrderDomObj)
		return false;

	updatedOrderDomObj.id = updatedOrderDomObj.id+"_old";

	var newOrderDomObj = order.getDomObj();

	updatedOrderDomObj.parentNode.replaceChild(newOrderDomObj,updatedOrderDomObj);

	return true;
};

__MASaleOrdersList.prototype.addOrder = function(orderObj, beforeOrderId)
{
	if(!orderObj)
	{
		//alert("__MASaleOrdersList.prototype.addOrder !orderObj"); //develop
		return false;
	}

	if(BX("order-"+orderObj["ID"]))
	{
		//alert("__MASaleOrdersList.prototype.addOrder order "+orderObj["ID"]+" alredy exist"); //develop
		return false;
	}

	var order = new __MASaleOrderItem(orderObj, this);
	var orderDomObj = order.getDomObj();
	var ordersListObj = BX("orders-list");

	if(beforeOrderId)
	{
		var beforeOrderObj = BX("order-"+beforeOrderId);

		if(beforeOrderObj)
			ordersListObj.insertBefore(orderDomObj, beforeOrder);
		//else //develop
//			alert("__MASaleOrdersList.prototype.addOrder order-"+beforeOrderId+" not exists"); //develop


	}
	else
	{
		ordersListObj.appendChild(orderDomObj);
		this.lastOrder = orderObj["ID"];
	}

	return true;
};

__MASaleOrdersList.prototype.getUpdateOrder = function(params)
{
	var _this = this;

	postData = {
		action: 'get_order',
		id: params.id
	};

	this.ajaxRequest(postData, function(result){ _this.updateOrder(result); });

};

__MASaleOrdersList.prototype.getBottomOrders = function()
{
	if(this.bottomReached)
		return;

	if(this.bottomOrdersRequesting)
	{
		this.packSize *=2;
		return;
	}

	this.bottomOrdersRequesting = true;

	var _this = this;

	postData = {
		action: 'get_orders',
		last: this.lastOrder,
		pack_size: this.packSize
	};

	if(this.filter)
		postData["filter"] = this.filter;

	this.ajaxRequest(postData, function(result){

					_this.bottomOrdersRequesting = false;

					if(result["bottomReached"])
					{
						_this.bottomReached = true;
					}
					else if(!result.orders)
					{
						//alert("getBottomOrders !result"); //develop
					}
					else
					{
						for(var i=0, l=result.orders.length; i<l; i++)
							_this.addOrder(result.orders[i]);
					}
			});

};
/* Orders list object end*/

/* Order object*/
__MASaleOrderItem = function(order, ordersList) {
	this.order = order;
	this.ordersList = ordersList;

	if(this.order["ALLOW_DELIVERY"] == 'Y')
		this.order["TMPL_DELIVERY_ALLOWED"] = 'allowed';
	else
		this.order["TMPL_DELIVERY_ALLOWED"] = 'notallowed';

	if(this.order["PAYED"] == 'Y')
		this.order["TMPL_PAYED"] = '';
	else
		this.order["TMPL_PAYEDD"] = 'notallowed';

	if(this.ordersList.orderDetailPath)
		this.order["ORDER_DETAIL_LINK"] = this.ordersList.orderDetailPath+"?id="+this.order["ID"];
};

__MASaleOrderItem.prototype.getDomObj = function()
{
	var domObj = document.createElement("DIV");
	domObj.className = "order_itemlist_item_container";
	domObj.id = "order-"+this.order["ID"];

	if(this.order["STATUS_ID"] == 'F')
	{
		BX.addClass(domObj, "order_completed");
	}
	else
	{
		BX.addClass(domObj, "order_"+this.order["ADD_ORDER_STEP"]);
	}

	domObj.innerHTML = this.getHtml();

	return domObj;
};

__MASaleOrderItem.prototype.getTemplate = function()
{
	if(this.order["STATUS_ID"] == 'F')
		return this.ordersList.orderTemplCompleted;

	return this.ordersList.orderTempl;
};

__MASaleOrderItem.prototype.getHtml = function()
{
	var template = this.getTemplate();

	return this.getPreparedTemplate(template, this.order);
};

__MASaleOrderItem.prototype.getPreparedTemplate = function(strTmpl, aFields)
{
	var retStr = strTmpl;

	for(var key in aFields)
		retStr = retStr.replace('##'+key+'##', aFields[key]);

	return retStr;
};

/*Order object end*/
