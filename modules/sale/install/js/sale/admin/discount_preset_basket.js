BX.namespace("BX.Sale.Admin.PresetBasket");

BX.Sale.Admin.PresetBasket = function (params)
{
	this.products = params.products;

	this.productsOffersSkuParams = {};

	if(params.productsOffersSkuParams)
	{
		for(var i in params.productsOffersSkuParams)
		{
			if(params.productsOffersSkuParams.hasOwnProperty(i))
				this.setProductsOffersSkuParams(i, params.productsOffersSkuParams[i]);
		}
	}

	BX.Sale.Admin.OrderBasket.call(this, params);

	// if (Object.keys(this.products).length == 0)
	// {
	// 	var tbl = BX(this.tableId);
	// 	tbl.appendChild(this.createEmptyFooter());
	// }
};

BX.Sale.Admin.PresetBasket.prototype = Object.create(BX.Sale.Admin.OrderBasketEdit.prototype);

BX.Sale.Admin.PresetBasket.prototype.getFieldsUpdaters = function()
{
	return {
		// "SUM_PAID": {context: this, callback: this.setSumPaid},
		// "PAYABLE": {context: this, callback: this.setSumUnPaid},
		// "TOTAL_PRICES": {context: this, callback: this.setTotalPrices},
		// "DELIVERY_PRICE": {context: this, callback: this.setDeliveryPrice},
		// "DELIVERY_PRICE_DISCOUNT": {context: this, callback: this.setDeliveryPriceDiscount},
		"BASKET": {context: this, callback: this.setBasket}
		// "DISCOUNTS_LIST":  {context: this, callback: this.setDiscounts}
	};
};

BX.Sale.Admin.OrderBasketEdit.prototype.setBasket = function(basket)
{
	if(!basket)
		return;

	var i, l;

	if(basket.IBLOCKS_SKU_PARAMS)
		for(i in basket.IBLOCKS_SKU_PARAMS)
			if(basket.IBLOCKS_SKU_PARAMS.hasOwnProperty(i))
				this.setIblocksSkuParams(i, basket.IBLOCKS_SKU_PARAMS[i]);

	if(basket.IBLOCKS_SKU_PARAMS_ORDER)
		for(i in basket.IBLOCKS_SKU_PARAMS_ORDER)
			if(basket.IBLOCKS_SKU_PARAMS_ORDER.hasOwnProperty(i))
				this.setIblocksSkuParamsOrder(i, basket.IBLOCKS_SKU_PARAMS_ORDER[i]);

	if(basket.PRODUCTS_OFFERS_SKU)
		for(i in basket.PRODUCTS_OFFERS_SKU)
			if(basket.PRODUCTS_OFFERS_SKU.hasOwnProperty(i))
				this.setProductsOffersSkuParams(i, basket.PRODUCTS_OFFERS_SKU[i]);

	//just update some fields
	if(basket.PRICES_UPDATED && basket.PRICES_UPDATED.length > 0)
	{
		//update price fields if price was changed
		for(i in basket.PRICES_UPDATED)
			if(basket.PRICES_UPDATED.hasOwnProperty(i))
				this.updateProductPriceCell(basket.ITEMS[i]);

		//update discounts.... always
		for(i in basket.ITEMS)
			if(basket.ITEMS.hasOwnProperty(i))
				this.updateProductDiscountsCell(basket.ITEMS[i]);

		//add new if it exists
		if(basket.NEW_ITEM_BASKET_CODE && basket.ITEMS[basket.NEW_ITEM_BASKET_CODE])
			this.productSet(basket.ITEMS[basket.NEW_ITEM_BASKET_CODE], true);
	}
	else if(basket.LIGHT && basket.LIGHT == 'Y')
	{
		for(i in this.products)
		{
			if(!this.products.hasOwnProperty(i))
				continue;

			if(typeof basket.ITEMS[i] == 'undefined')
				this.productDelete(i);
		}

		if(basket.ADDED_PRODUCTS)
		{
			for(i in basket.ADDED_PRODUCTS)
			{
				if(basket.ADDED_PRODUCTS.hasOwnProperty(i))
				{
					this.productSet(basket.ITEMS[basket.ADDED_PRODUCTS[i]], true);
					delete(basket.ITEMS[basket.ADDED_PRODUCTS[i]]);
				}
			}
		}

		if(basket.ITEMS)
		{
			for(i in basket.ITEMS)
				if(basket.ITEMS.hasOwnProperty(i))
					this.productUpdateLight(basket.ITEMS[i]);
		}
	}
	else if(basket.ITEMS_ORDER && basket.ITEMS_ORDER.length)
	{
		for(i in this.products)
			if(this.products.hasOwnProperty(i))
				this.productDelete(i);

		for(i = 0, l=basket.ITEMS_ORDER.length-1; i <= l; i++)
			this.productSet(basket.ITEMS[basket.ITEMS_ORDER[i]], true);
	}

	if(!basket.ITEMS || !basket.ITEMS_ORDER || !basket.ITEMS_ORDER.length)
	{
		this.showEmptyRow();
	}

	this.hideLoadingRow();
};

BX.Sale.Admin.PresetBasket.prototype.createFieldPrice = function(basketCode, product, fieldId)
{
	var price;

	if(typeof(this.customPrices[basketCode]) == "undefined")
		price = product.PRICE;
	else
		price = this.customPrices[basketCode];

	var spanFormattedPrice = BX.create('span', {
			props:{
				className: "formated_price",
				id: this.idPrefix+"sale-order-basket-product-"+basketCode+"-formatted_price"
			},
			html: BX.Sale.Admin.OrderEditPage.currencyFormat(price),
			style: {whiteSpace: 'nowrap'}
		}),
		spanView = BX.create('span', {
			props:{
				className: "default_price_product"
			},
			children: [
				spanFormattedPrice
			]
		}),
		currencySpan = BX.create('span', {
			text: ""
		}),
		basePrice = BX.create('div',{
			props: {
				className: "base_price",
				id: this.idPrefix+"sale-order-basket-product-"+basketCode+"-base_price"
			},
			style: {display: ((parseFloat(product.BASE_PRICE) > 0 && parseFloat(product.PRICE) != parseFloat(product.BASE_PRICE)) ? "": "none")},
			children: [
				BX.create('span',{
					html: BX.Sale.Admin.OrderEditPage.currencyFormat(product.BASE_PRICE)
				})
			]
		}),
		priceNotes = BX.create('div',{
			props: {className: "base_price_title"},
			style: {display: (((product.CUSTOM_PRICE && product.CUSTOM_PRICE == "Y") || product.NOTES) ? "": "none")},
			text: ((product.CUSTOM_PRICE && product.CUSTOM_PRICE == "Y") ? BX.message("SALE_ORDER_BASKET_BASE_CATALOG_PRICE") : product.NOTES)
		}),
		containerDiv = BX.create('span', {
			props:{
				className: "edit_price"
			},
			children: [
				spanView,
				currencySpan,
				basePrice,
				priceNotes
			]
		});

	return containerDiv;
};

