BX.namespace("BX.Sale.Admin.OrderInfo");

BX.Sale.Admin.OrderInfo =
{
	getFieldsUpdaters: function()
	{
		return {
			"BUYER_FIO": BX.Sale.Admin.OrderInfo.setBuyerName,
			"BUYER_PHONE": BX.Sale.Admin.OrderInfo.setBuyerPhone,
			"BUYER_EMAIL": BX.Sale.Admin.OrderInfo.setBuyerEmail,
			"STATUS_ID": BX.Sale.Admin.OrderInfo.setOrderStatus,
			"TOTAL_PRICES": BX.Sale.Admin.OrderInfo.setTotalPrices
		};
	},

	setTotalPrices:function(prices)
	{
		BX.Sale.Admin.OrderInfo.setPriceBasket(prices.PRICE_BASKET);
		BX.Sale.Admin.OrderInfo.setPriceBasketDiscount(prices.PRICE_BASKET_DISCOUNTED);
		BX.Sale.Admin.OrderInfo.setDeliveryPrice(prices.PRICE_DELIVERY_DISCOUNTED);
		BX.Sale.Admin.OrderInfo.setPrice(prices.SUM_UNPAID);
	},

	setBuyerName: function(name)
	{
		var span = BX("order_info_buyer_name");

		if(span)
			span.innerHTML = BX.util.htmlspecialchars(name);
	},

	setBuyerPhone: function(phone)
	{
		var span = BX("order_info_buyer_phone");

		if(!span)
			return;

		var callText = '';

		if(phone)
		{
			if(!(phone instanceof Array))
			{
				phone = [phone];
			}

			for(var i = 0, l = phone.length; i < l; i++)
			{
				phone[i] = phone[i].replace(/'/g, "");
				phone[i] = BX.util.htmlspecialchars(phone[i]);

				if(callText.length > 0)
					callText += ', ';

						callText += '<a href="javascript:void(0)" onclick="BX.Sale.Admin.OrderEditPage.desktopMakeCall(\''+phone[i]+'\');">'+
					phone[i]+
				'</a>';
			}
		}

		span.innerHTML = callText;
	},

	setBuyerEmail: function(email)
	{
		var span = BX("order_info_buyer_email");

		if(span)
		{
			email = BX.util.htmlspecialchars(email);
			span.innerHTML = '<a href="mailto:'+email+'">'+email+'</a>';
		}
	},

	setOrderStatus: function(statusId)
	{
		var span = BX("order_info_order_status_name");

		if(span && BX.Sale.Admin.OrderEditPage.statusesNames && BX.Sale.Admin.OrderEditPage.statusesNames[statusId])
			span.innerHTML = BX.util.htmlspecialchars(BX.Sale.Admin.OrderEditPage.statusesNames[statusId]);
	},

	setPrice: function(price)
	{
		var span = BX("order_info_buyer_price");

		if(span)
			span.innerHTML = BX.Sale.Admin.OrderEditPage.currencyFormat(price);
	},

	setPriceBasketDiscount: function(price)
	{
		var span = BX("order_info_price_basket_discount");

		if(span)
			span.innerHTML = BX.Sale.Admin.OrderEditPage.currencyFormat(price);
	},

	setDeliveryPrice: function(price)
	{
		var span = BX("order_info_delivery_price");

		if(span)
			span.innerHTML = BX.Sale.Admin.OrderEditPage.currencyFormat(price);
	},

	setPriceBasket: function (price)
	{
		var span = BX("order_info_price_basket");

		if(span)
			span.innerHTML = BX.Sale.Admin.OrderEditPage.currencyFormat(price);
	},

	setIconLamp: function(type, entityId, state)
	{
		var lamp = BX("sale-admin-order-icon-"+type+"-"+entityId);

		if(lamp)
			lamp.className = "adm-bus-orderinfoblock-content-last-icon "+state;
	}
};
