BX.namespace("BX.Sale.Admin.FinanceInfo");

BX.Sale.Admin.FinanceInfo =
{
	currency: "",

	getFieldsUpdaters: function()
	{
		return {
			"PRICE": BX.Sale.Admin.FinanceInfo.setPrice,
			"PAYABLE": BX.Sale.Admin.FinanceInfo.setPayable,
			"SUM_PAID": BX.Sale.Admin.FinanceInfo.setSumPaid,
			"BUYER_BUDGET": BX.Sale.Admin.FinanceInfo.setUserBudget
		};
	},

	setUserBudget: function(budget)
	{
		var nodeInput = BX('sale-order-financeinfo-user-budget-input');
		if (nodeInput)
			nodeInput.value = budget;

		var node = BX("sale-order-financeinfo-user-budget");
		if(node)
		{
			node.innerHTML = BX.Sale.Admin.OrderEditPage.currencyFormat(budget);
			var container = node.parentNode.parentNode.parentNode;

			if(container)
			{
				if(budget > 0)
					node.parentNode.parentNode.parentNode.style.display = "";
				else
					node.parentNode.parentNode.parentNode.style.display = "none";
			}
		}
	},

	/* Price */
	setPrice: function(price)
	{
		var inp = BX("sale-order-financeinfo-price"),
			sumPaid = BX.Sale.Admin.FinanceInfo.getSumPaid();

		if(inp)
			inp.price = price;

		BX.Sale.Admin.FinanceInfo.setPriceView(price);
		BX.Sale.Admin.FinanceInfo.setPayable(parseFloat(price)-parseFloat(sumPaid));
	},

	getPrice: function()
	{
		var inp = BX("sale-order-financeinfo-price"),
			result = 0;

		if(inp)
			result = inp.value;

		return result;
	},

	setPriceView: function(value)
	{
		var tr = BX("sale-order-financeinfo-price-view");

		if(tr)
			tr.innerHTML = BX.Sale.Admin.OrderEditPage.currencyFormat(value);
	},

	/* Sum paid */
	setSumPaid: function(value)
	{
		var inp = BX("sale-order-financeinfo-sum-paid"),
			price = BX.Sale.Admin.FinanceInfo.getPrice();

		if(inp)
			inp.value = value;

		BX.Sale.Admin.FinanceInfo.setSumPaidView(value);
		BX.Sale.Admin.FinanceInfo.setPayable(parseFloat(price)-parseFloat(value));
	},

	setSumPaidView: function(value)
	{
		var tr = BX("sale-order-financeinfo-sum-paid-view");

		if(tr)
			tr.innerHTML = BX.Sale.Admin.OrderEditPage.currencyFormat(value);
	},

	getSumPaid: function()
	{
		var inp = BX("sale-order-financeinfo-sum-paid"),
			result = 0;

		if(inp)
			result = inp.value;

		return result;
	},

	/* Payable */
	setPayable: function(value)
	{
		var inp = BX("sale-order-financeinfo-payable");

		if(inp)
			inp.value = value;

		BX.Sale.Admin.FinanceInfo.setPayableView(value);
	},

	setPayableView: function(value)
	{
		var tr = BX("sale-order-financeinfo-payable-view");

		if(tr)
			tr.innerHTML = BX.Sale.Admin.OrderEditPage.currencyFormat(value);
	}
};
