/*
 *
 * BX.Sale.Admin.OrderBasket
 *
 */

BX.namespace("BX.Sale.Admin.OrderBasket");

BX.Sale.Admin.OrderBasket = function (params)
{
	var i,l;

	this.tableId = params.tableId;
	this.objName = params.objName;
	this.idPrefix = params.idPrefix;
	this.productMenu = params.productMenu;
	this.columnsCount = params.columnsCount;
	this.visibleColumns = params.visibleColumns;
	this.isShowXmlId = params.isShowXmlId;
	this.weightUnit = params.weightUnit;

	this.productsCount = 0;
	this.customPrices = {};
	this.createBasketBottom = params.createBasketBottom || false;
	this.createProductBasement = params.createProductBasement || false;
	this.discounts = params.discounts || false;
	this.iblocksSkuParams = params.iblocksSkuParams || {};
	this.iblocksSkuParamsOrder = params.iblocksSkuParamsOrder || {};
	this.mode = params.mode || "edit";
	this.unRemovableFields = params.unRemovableFields || [];
	this.formatQuantity = params.formatQuantity;
	this.qantityUpdaterTimeout = 0;
	this.qantityUpdaterDelay = 750;
	this.canSendUpdateQuantityRequest = true;
	this.lastChangedQuantity = false;

	if(params.iblocksSkuParams)
	{
		for(i in params.iblocksSkuParams)
		{
			if(params.iblocksSkuParams.hasOwnProperty(i))
				this.setIblocksSkuParams(i, params.iblocksSkuParams[i]);
		}
	}

	this.fieldsUpdaters = {};
	if (!this.products)
		this.products = {};

	if(params.productsOrder && params.productsOrder.length)
		for(i=0, l=params.productsOrder.length-1; i<=l; i++)
			if (!!params.products[params.productsOrder[i]])
				this.productSet(params.products[params.productsOrder[i]], true);

	if(this.createBasketBottom)
		this.createBottomRow();

	if(this.discounts)
		this.setDiscounts(this.discounts);

	if(params.totalBlockFields)
	{
		this.totalBlock = new BX.Sale.Admin.OrderBasketEditTotal(
			{
				fields: params.totalBlockFields,
				weightUnit: this.weightUnit
			}
		);
	}
};

BX.Sale.Admin.OrderBasket.prototype.getFieldsUpdaters = function()
{
	return {
		"SUM_PAID": {context: this, callback: this.setSumPaid},
		"PAYABLE": {context: this, callback: this.setSumUnPaid},
		"TOTAL_PRICES": {context: this, callback: this.setTotalPrices},
		"DELIVERY_PRICE": {context: this, callback: this.setDeliveryPrice},
		"DELIVERY_PRICE_DISCOUNT": {context: this, callback: this.setDeliveryPriceDiscount},
		"BASKET": {context: this, callback: this.setBasket},
		"DISCOUNTS_LIST":  {context: this, callback: this.setDiscounts}
	};
};

BX.Sale.Admin.OrderBasket.prototype.setBasket = function(basket)
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

	if(basket.ITEMS && basket.ITEMS_ORDER && basket.ITEMS_ORDER.length)
	{
		for(i=0, l=basket.ITEMS_ORDER.length-1; i<=l; i++)
			if (!!basket.ITEMS[basket.ITEMS_ORDER[i]])
				this.productSet(basket.ITEMS[basket.ITEMS_ORDER[i]], true);
	}
	else
	{
		this.showEmptyRow();
	}

	this.hideLoadingRow();
};

BX.Sale.Admin.OrderBasket.prototype.setTotalPrices = function(prices)
{
	this.totalBlock.setFieldValue("SUM_PAID", prices.SUM_PAID);
	this.totalBlock.setFieldValue("SUM_UNPAID", prices.SUM_UNPAID);
};
BX.Sale.Admin.OrderBasket.prototype.setDeliveryPrice = function(price)
{
	this.totalBlock.setFieldValue("PRICE_DELIVERY", price);
};

BX.Sale.Admin.OrderBasket.prototype.setDeliveryPriceDiscount = function(price)
{
	this.totalBlock.setFieldValue("PRICE_DELIVERY_DISCOUNTED", price);
};

BX.Sale.Admin.OrderBasket.prototype.setSumPaid = function(summ)
{
	this.totalBlock.setFieldValue("SUM_PAID", summ);
};

BX.Sale.Admin.OrderBasket.prototype.setSumUnPaid = function(summ)
{
	this.totalBlock.setFieldValue("SUM_UNPAID", summ);
};

BX.Sale.Admin.OrderBasket.prototype.showEmptyRow = function ()
{
	var row = BX(this.idPrefix+"sale-adm-order-edit-basket-empty-row");

	if(row)
		row.style.display = "";
};

BX.Sale.Admin.OrderBasket.prototype.hideEmptyRow = function ()
{
	var row = BX(this.idPrefix+"sale-adm-order-edit-basket-empty-row");

	if(row)
		row.style.display = "none";
};

BX.Sale.Admin.OrderBasket.prototype.hideLoadingRow = function ()
{
	var row = BX(this.idPrefix+"sale-adm-order-basket-loading-row");

	if(row)
		row.style.display = "none";
};

BX.Sale.Admin.OrderBasket.prototype.createBottomRow = function(products)
{
	if(!this.createBasketBottom)
		return;

	var table = BX(this.tableId),
		td1 = BX.create('td',{
			html: '<strong style="margin-left: 10px;">'+
				BX.message('SALE_ORDER_BASKET_PROD_COUNT')+':&nbsp;'+
				'<span id="'+this.idPrefix+'sale-order-basket-products-count">'+this.productsCount+'</span>'+
			'</strong>'
		}),
		td2 = BX.create('td',{
			props: {
				id: this.idPrefix+"sale-order-basket-bottom-discounts"
			}
		}),
		tbody = BX.create('tbody', {props:{
			"id": this.idPrefix+"sale-order-basket-bottom"
			},
			"style": {
				"textAlign": "left",
				"borderBottom": "1px solid #DDD"
			},
			children: [
				BX.create('tr', {
					children: [
						td1,
						td2
					]
				})
			]
		});


	td1.colSpan = this.mode == "view" ? 1 : 2;
	td2.colSpan = this.columnsCount-1;
	table.appendChild(tbody);
};

BX.Sale.Admin.OrderBasket.prototype.setDiscounts = function(discounts)
{
	this.discounts = discounts;
	var basketDiscounts = BX(this.idPrefix+"sale-order-basket-bottom-discounts"),
		filteredDiscounts = [];

	if(!discounts || !discounts.ORDER || !discounts.ORDER.DISCOUNT_LIST)
		return;

	for(var i=0, l=discounts.ORDER.DISCOUNT_LIST.length; i<l;  i++)
	{
		var deliveryId = discounts.ORDER.DISCOUNT_LIST[i];

		if(discounts.DISCOUNT_LIST
			&& discounts.DISCOUNT_LIST[deliveryId]
			&& discounts.DISCOUNT_LIST[deliveryId].ACTIONS_DESCR
			&& discounts.DISCOUNT_LIST[deliveryId].ACTIONS_DESCR.BASKET
		)
		{
			filteredDiscounts[i] = discounts.DISCOUNT_LIST[deliveryId];
			filteredDiscounts[i].DESCR = discounts.DISCOUNT_LIST[deliveryId].ACTIONS_DESCR.BASKET;
		}
	}

	if(basketDiscounts && filteredDiscounts)
	{
		basketDiscounts = BX.cleanNode(basketDiscounts, false);
		basketDiscounts.appendChild(
			this.createDiscountsNodeBasket(filteredDiscounts)
		);
	}
};

BX.Sale.Admin.OrderBasket.prototype.createDiscountsNodeBasket = function(discounts)
{
	return BX.Sale.Admin.OrderEditPage.createDiscountsNode(
		"",
		"DISCOUNT_LIST",
		discounts,
		this.discounts,
		"VIEW"
	);
};

BX.Sale.Admin.OrderBasket.prototype.getProductBasketCode = function(product)
{
	if(!product.BASKET_CODE)
	{
		if(product.IS_SET_ITEM == "Y")
		{
			var d = new Date();
			product.BASKET_CODE = "set_"+d.getTime()+Math.floor(Math.random()*1000);
		}
		else
		{
			BX.debug("product.BASKET_CODE is undefined!");
		}
	}

	return product.BASKET_CODE;
};

BX.Sale.Admin.OrderBasket.prototype.setProductsCount = function(count)
{
	if(!this.createBasketBottom)
		return;

	var node = BX(this.idPrefix+'sale-order-basket-products-count');

	if(node)
		node.innerHTML = count;
};

BX.Sale.Admin.OrderBasket.prototype.productAdd = function(product)
{
	var basketCode = this.getProductBasketCode(product),
		productRow = BX(this.createProductRowId(basketCode, product));

	if(productRow)
		return;

	if(parseFloat(product.PRICE) != parseFloat(product.BASE_PRICE) && product.CUSTOM_PRICE && product.CUSTOM_PRICE == 'Y')
		this.customPrices[basketCode] = product.PRICE;

	productRow = this.createProductRow(basketCode, product);
	var table = BX(this.tableId);

	if(this.createBasketBottom && (bottom=BX(this.idPrefix+"sale-order-basket-bottom")))
		table.insertBefore(productRow, bottom);
	else
		table.appendChild(productRow);
};

BX.Sale.Admin.OrderBasket.prototype.productSet = function(product, isReplaceExisting)
{
	var basketCode = this.getProductBasketCode(product);

	if(this.productsCount <= 0)
		this.hideEmptyRow();

	if(BX(this.createProductRowId(basketCode, product))) // product already exist
	{
		if(isReplaceExisting)
		{
			this.productReplace(product, basketCode);
		}
		else
		{
			this.setProductQuantity(
				basketCode,
				this.roundQuantity(parseFloat(this.getProductQuantity(basketCode)) + parseFloat(product.QUANTITY))
			);
		}
	}
	else
	{
		this.productAdd(product);

		if(product.IS_SET_ITEM != "Y")
			this.setProductsCount(++this.productsCount);
	}

	//sets
	if(product.SET_ITEMS && product.SET_ITEMS.length)
	{
		for(var i = product.SET_ITEMS.length-1; i>=0; i--)
		{
			product.SET_ITEMS[i].BASKET_CODE = "set_" + product.BASKET_CODE + "_"+product.SET_ITEMS[i].OFFER_ID;
			this.productSet(product.SET_ITEMS[i], true);
		}
	}

	this.setRowNumbers();
};

BX.Sale.Admin.OrderBasket.prototype.productReplace = function(product, oldProductBasketCode)
{
	var oldProductRow = BX(this.createProductRowId(oldProductBasketCode, product));

	if(!oldProductRow)
		return;

	this.onProductDelete(oldProductBasketCode);

	var	basketCode = this.getProductBasketCode(product),
		newProductRow = this.createProductRow(basketCode, product);

	if(!newProductRow)
		return;

	if(!BX.hasClass(oldProductRow, "basket-bundle-child-hidden"))
		BX.removeClass(newProductRow, "basket-bundle-child-hidden");

	oldProductRow.parentNode.replaceChild(newProductRow, oldProductRow);
	this.setRowNumbers();
};

BX.Sale.Admin.OrderBasket.prototype.onProductDelete = function(basketCode)
{
	BX.Sale.Admin.OrderEditPage.unRegisterProductFieldsUpdaters(basketCode);
};

BX.Sale.Admin.OrderBasket.prototype.roundQuantity = function(quantity)
{
	if (this.formatQuantity == 'AUTO' || parseInt(this.formatQuantity) <= 0)
		quantity = Math.round(quantity*10000)/10000;
	else
		quantity = Math.round(quantity*Math.pow(10, this.formatQuantity)) / Math.pow(10, this.formatQuantity);

	return quantity;
};

BX.Sale.Admin.OrderBasket.prototype.getDiscountCellId = function(basketCode)
{
	return this.idPrefix+"sale-order-basket-product-"+basketCode+"-discount-cell";
};

BX.Sale.Admin.OrderBasket.prototype.createDiscountCell = function(basketCode, product)
{
	var discountsNode = BX.create("text",{html: "&nbsp"}),
		isDiscountsExist = false,
		skuPropsExist = product.SKU_PROPS && Object.keys(product.SKU_PROPS).length > 0;

	if(this.discounts && this.discounts.RESULT && this.discounts.RESULT.BASKET)
	{
		discountsNode = BX.Sale.Admin.OrderEditPage.createDiscountsNode(
			basketCode,
			"BASKET",
			this.discounts.RESULT.BASKET[basketCode] ? this.discounts.RESULT.BASKET[basketCode] : {},
			this.discounts,
			"VIEW"
		);

		isDiscountsExist = this.discounts.RESULT.BASKET[basketCode] ? true : false;
	}

	var td = BX.create('td',{
		props:{
			id: this.getDiscountCellId()
		},
		children: [
			BX.create('div',{
				children: [discountsNode]
			})
		]
	});

	td.colSpan = this.columnsCount-1;

	if(skuPropsExist || isDiscountsExist)
		td.style.borderTop = "1px solid #ddd";

	return td;
};

BX.Sale.Admin.OrderBasket.prototype.setIblocksSkuParamsOrder = function(iBlockId, iblocksSkuParamOrder)
{
	if(!this.iblocksSkuParamsOrder)
		this.iblocksSkuParamsOrder = {};

	if(!this.iblocksSkuParamsOrder[iBlockId])
		this.iblocksSkuParamsOrder[iBlockId] = [];

	if(iblocksSkuParamOrder)
	{
		for(var i in iblocksSkuParamOrder)
		{
			if(!iblocksSkuParamOrder.hasOwnProperty(i))
				continue;

			var exist = false;

			for(var j in this.iblocksSkuParamsOrder[iBlockId])
			{
				if(!this.iblocksSkuParamsOrder[iBlockId].hasOwnProperty(j))
					continue;

				if(this.iblocksSkuParamsOrder[iBlockId][j] == iblocksSkuParamOrder[i])
				{
					exist = true;
					break;
				}
			}

			if(!exist)
				this.iblocksSkuParamsOrder[iBlockId][i] = iblocksSkuParamOrder[i];
		}
	}
};

BX.Sale.Admin.OrderBasket.prototype.setIblocksSkuParams = function(iBlockId, iblocksSkuParam)
{
	if(!this.iblocksSkuParams)
		this.iblocksSkuParams = {};

	if(!this.iblocksSkuParams[iBlockId])
		this.iblocksSkuParams[iBlockId] = {};

	if(iblocksSkuParam)
	{
		for(var i in iblocksSkuParam)
		{
			if(!iblocksSkuParam.hasOwnProperty(i))
				continue;

			if(this.iblocksSkuParams[iBlockId][i] !== undefined && this.iblocksSkuParams[iBlockId][i]['VALUES'] !== undefined && iblocksSkuParam[i]['VALUES'] !== undefined)
			{
				for(var j in iblocksSkuParam[i]['VALUES'])
					if(iblocksSkuParam[i]['VALUES'].hasOwnProperty(j))
						this.iblocksSkuParams[iBlockId][i]['VALUES'][j] = iblocksSkuParam[i]['VALUES'][j];
			}
			else
			{
				this.iblocksSkuParams[iBlockId][i] = iblocksSkuParam[i];
			}
		}
	}
};

BX.Sale.Admin.OrderBasket.prototype.createProductRowBasement = function(basketCode, product)
{
	if(!this.createProductBasement)
		return;

	var result = null,
		tr = BX.create('tr', {props:{
			id: this.idPrefix+"sale-order-basket-product-"+basketCode+"-basement",
			className: "bdb-line"
			}
		}),
		propsTd = this.createProductBasementSkuCell(basketCode, product),
		discountTd = this.createDiscountCell(basketCode, product);

	if(propsTd && discountTd)
	{
		tr.appendChild(propsTd);
		tr.appendChild(discountTd);
		result = tr;
	}

	return result;
};

BX.Sale.Admin.OrderBasket.prototype.createProductBasementSkuCell = function(basketCode, product)
{
	var tbl = this.createSkuPropsTable(basketCode, product),
		result = null;

	if(tbl)
	{
		result = BX.create('td',{
			children: [	tbl	]
		});
	}

	return result;
};

BX.Sale.Admin.OrderBasket.prototype.createProductRowId = function(basketCode, product)
{
	var id = this.idPrefix+"sale-order-basket-product-"+basketCode;

	if(product)
	{
		if(product.IS_SET_ITEM == "Y" && product.PARENT_OFFER_ID)
			id += "-"+product.PARENT_OFFER_ID;
	}

	return id;
};

/**
 * @param {string} basketCode
 * @param {object} product
 * @return {node} product table row Dom node
 */
BX.Sale.Admin.OrderBasket.prototype.createProductRow = function(basketCode, product)
{
	var	cellContent,
		tbody = BX.create('tbody', {props:{
				"id": this.createProductRowId(basketCode, product)
			},
			"style": {
				"textAlign": "left",
				"borderBottom": "1px solid #DDD"
			}
		}),
		menuCell = this.createMenuCell(basketCode, product),
		tr = BX.create('tr');

	if(menuCell)
	{
		if(this.createProductBasement)
			menuCell.rowSpan = 2;

		tr.appendChild(menuCell);
	}

	var field,
		hiddenFields = [];

	if(product.IS_SET_ITEM != "Y")
	{
		hiddenFields = this.createHiddenFields(basketCode, product);
		this.products[basketCode] = product;
		tbody.setAttribute("data-basket-code", basketCode);

		if(product.IS_SET_PARENT && product.OLD_PARENT_ID)
			tbody.setAttribute("data-old-parent-id-parent", product.OLD_PARENT_ID);
	}
	else
	{
		BX.addClass(tbody,"bundle-child-"+product.OLD_PARENT_ID);
		BX.addClass(tbody,"basket-bundle-child-hidden");
		BX.addClass(tbody,"bundle-child");
	}

	for(var fieldId in this.visibleColumns)
	{
		if(!this.visibleColumns.hasOwnProperty(fieldId))
			continue;

		cellContent = this.createProductCell(basketCode, product, fieldId);

		if(cellContent)
		{
			if(hiddenFields)
				while(field = hiddenFields.pop())
					cellContent.appendChild(field);

			tr.appendChild(cellContent);
		}
	}

	tr.setAttribute('onmouseover', this.objName+'.onProductRowMouseOver(this);');
	tr.setAttribute('onmouseout', this.objName+'.onProductRowMouseOut(this);');
	tbody.appendChild(tr);

	if((this.createProductBasement && ((product.SKU_PROPS || (product.PROPS && product.PROPS.length > 0 && this.mode == "view")) && product.IS_SET_ITEM != "Y")) || product.DISCOUNTS)
	{
		var rowBasement = this.createProductRowBasement(basketCode, product);

		if(rowBasement)
			tbody.appendChild(rowBasement);
	}

	return tbody;
};

BX.Sale.Admin.OrderBasket.prototype.createHiddenFields = function(basketCode, product)
{
	return [];
};

BX.Sale.Admin.OrderBasket.prototype.createMenuCell = function(basketCode, product)
{
	var menuSpan,
		menuContent = this.createProductMenuContent(basketCode, product);

	if(menuContent.length <= 0)
		return false;

	if(product.IS_SET_ITEM != "Y")
	{

		menuSpan =  BX.create('span', {
				props:{
					className: "adm-s-order-item-title-icon"
				}
			});

		BX.bind(
			menuSpan,
			"click",
			BX.proxy( function(e){
					menuSpan.blur();
					BX.adminList.ShowMenu(menuSpan, menuContent);
				},
				this
			)
		);
	}
	else
	{
		menuSpan =  BX.create('span', {html: "&nbsp;"});
	}

	return BX.create(
		'td',
		{
			props:{
				className: 'tac bdb-line',
				id: this.idPrefix+"sale-order-basket-product-"+basketCode+"-menu"
			},
			children:[
				menuSpan
			]
		}
	);
};

BX.Sale.Admin.OrderBasket.prototype.onHeadMenu = function(node)
{
	BX.adminList.ShowMenu(node,
		[{
			"ICON": "view",
			"TEXT": BX.message("SALE_ORDER_BASKET_ROW_SETTINGS"),
			"ACTION":  this.objName+'.onSettings()',
			"DEFAULT":true
		}
	]);
};

BX.Sale.Admin.OrderBasket.prototype.setRowNumbers = function()
{
	if(!this.visibleColumns["NUMBER"])
		return;

	var table = BX(this.tableId),
		basketCode = "",
		span = null,
		counter = 1;


	for(var i=0, l=table.tBodies.length; i<l; i++)
	{
		if(BX.hasClass(table.tBodies[i], "bundle-child"))
			continue;

		basketCode = table.tBodies[i].getAttribute("data-basket-code");

		if(!basketCode)
			continue;

		span = BX(this.idPrefix+"sale_order_product_"+basketCode+"_number");

		if(span)
			span.innerHTML = counter++;
		else
			BX.debug("BX.Sale.Admin.OrderBasket.prototype.setRowNumbers can't find: "+basketCode);
	}
};

BX.Sale.Admin.OrderBasket.prototype.getPriceCellId = function(basketCode)
{
	return this.idPrefix+"sale-order-basket-product-"+basketCode+"-price-cell";
};

BX.Sale.Admin.OrderBasket.prototype.getProductSummCellId = function(basketCode)
{
	return this.idPrefix+'sale_order_edit_product_'+basketCode+'_summ';
};

BX.Sale.Admin.OrderBasket.prototype.getQuantityCellId = function(basketCode)
{
	return this.idPrefix+"sale-order-basket-product-"+basketCode+"-quantity-cell";
};

BX.Sale.Admin.OrderBasket.prototype.createProductCell = function(basketCode, product, fieldId)
{
	var result = null,
		cellNodes = [],
		fieldValue = product[fieldId],
		tdClass = "",
		_this = this,
		isSetItem = (BX.type.isNotEmptyString(product.IS_SET_ITEM) && product.IS_SET_ITEM === 'Y'),
		isProductActive = (BX.type.isNotEmptyString(product.PRODUCT_ACTIVE) && product.PRODUCT_ACTIVE === 'Y');

	switch(fieldId)
	{
		case "NUMBER":
			cellNodes.push(
				BX.create(
					'span',
					{
						props:{
							id: !isSetItem ? this.idPrefix+"sale_order_product_"+basketCode+"_number" : "&nbsp;"
						},
						html: "&nbsp;"
					}
				)
			);
			break;

		case "NAME":

			if(product.IS_SET_PARENT == "Y" && product.SET_ITEMS)
			{
				var bundleShow = BX.create('a',{
					props:{
						href:"javascript:void(0);",
						className: "dashed-link show-set-link" + (!isProductActive ? ' product-unactive' : ''),
						title: (!isProductActive ? BX.message('SALE_ORDER_BASKET_PRODUCT_UNACTIVE') : '')
					},
					html: BX.message("SALE_ORDER_BASKET_EXPAND")
				});

				BX.bind(bundleShow, "click", function(e){
					var source = e.target || e.srcElement;
					_this.onToggleBundleChildren(product.OLD_PARENT_ID, source);
				});

				cellNodes.push(
					BX.create('div', {
						children: [bundleShow]
					})
				);
			}

			if(product.EDIT_PAGE_URL)
			{
				if(!fieldValue)
					fieldValue = BX.message('SALE_ORDER_BASKET_NO_NAME');

				node = BX.create('a',{
						props:{
							href:product.EDIT_PAGE_URL,
							target:"_blank",
							className: (!isProductActive ? 'product-unactive' : ''),
							title: (!isProductActive ? BX.message('SALE_ORDER_BASKET_PRODUCT_UNACTIVE') : '')
						},
						html: BX.util.htmlspecialchars(fieldValue)
					});
			}
			else
			{
				var name = product[fieldId] ? product[fieldId] : BX.message('SALE_ORDER_BASKET_NO_NAME');
				node = BX.create('span', {
					style: {fontWeight: "bold"},
					html: BX.util.htmlspecialchars(name)
				});
			}

			cellNodes.push(node);

			if(product.RECOMMENDATION && product.RECOMMENDATION.length > 0)
				cellNodes.push(BX.create('div',{props: {className: 'bx-adm-bigdata-icon-medium-inner'}}));

			break;

		case "QUANTITY":
			if(typeof product.MEASURE_TEXT != "undefined")
				cellNodes.push(document.createTextNode(" "+product.MEASURE_TEXT+" "));

			product['QUANTITY'] = this.roundQuantity(parseFloat(product['QUANTITY']));

			if(isSetItem)
			{
				node = BX.Sale.Admin.OrderBasket.prototype.createFieldQuantity(basketCode, product, fieldId);
				node.id = this.getQuantityCellId(basketCode);
				cellNodes.push(node);
			}
			else
			{
				cellNodes.push(this.createFieldQuantity(basketCode, product, fieldId));
			}

			break;

		case "AVAILABLE":
			cellNodes.push(this.createTextField(basketCode, product, fieldId));
			break;

		case "PRICE":
			if(isSetItem)
			{
				cellNodes.push(BX.Sale.Admin.OrderBasket.prototype.createFieldPrice(basketCode, product, fieldId));
			}
			else
			{
				var priceNode = this.createFieldPrice(basketCode, product, fieldId);
				priceNode.id = this.getPriceCellId(basketCode);
				cellNodes.push(priceNode);
			}
			break;

		case "SUM":
			var price;
			if(typeof(this.customPrices[basketCode]) == "undefined")
				price = product.PRICE;
			else
				price = this.customPrices[basketCode];

			var sum = price*product.QUANTITY;

			cellNodes.push(
				BX.create('strong', {
					props:{
						id: this.getProductSummCellId(basketCode)
					},
					html: BX.Sale.Admin.OrderEditPage.currencyFormat(sum),
					style: {whiteSpace: 'nowrap'}
				})
			);
			break;

		case "IMAGE":
			cellNodes.push(this.createFieldImage(basketCode, product, fieldId));
			tdClass = "adm-s-order-table-ddi-table-img";
			break;

		case "PROPS":
			var node = this.createFieldSkuProps(basketCode, product, fieldId);

			if(node)
				cellNodes.push(node);
			break;
	}


	if(fieldId.indexOf("PROPERTY_") === 0)
	{
		var html = "",
			propCode =  fieldId.substr("PROPERTY_".length);

		if(product.PRODUCT_PROPS_VALUES && product.PRODUCT_PROPS_VALUES[fieldId+"_VALUE"])
		{
			var iblocks = [product.OFFERS_IBLOCK_ID, product.IBLOCK_ID];

			for(var idx in iblocks)
			{
				if(!iblocks.hasOwnProperty(idx))
					continue;

				var iblockId = iblocks[idx];

				if(!iblockId)
					continue;

				if(this.iblocksSkuParams[iblockId])
				{
					for(var i in this.iblocksSkuParams[iblockId])
					{
						if(!this.iblocksSkuParams[iblockId].hasOwnProperty(i))
							continue;

						if(this.iblocksSkuParams[iblockId][i].CODE != propCode)
							continue;

						if(!this.iblocksSkuParams[iblockId][i].VALUES[product.PRODUCT_PROPS_VALUES[fieldId+"_VALUE"]])
							continue;
/*
						if(this.iblocksSkuParams[iblockId][i].VALUES[product.PRODUCT_PROPS_VALUES[fieldId+"_VALUE"]]["PICT"]
							&& this.iblocksSkuParams[iblockId][i].VALUES[product.PRODUCT_PROPS_VALUES[fieldId+"_VALUE"]]["PICT"].length > 0
						)
						{
							html = '<img src="'+this.iblocksSkuParams[iblockId][i].VALUES[product.PRODUCT_PROPS_VALUES[fieldId+"_VALUE"]]["PICT"]+'">';
						}*/
						if(this.iblocksSkuParams[iblockId][i].VALUES[product.PRODUCT_PROPS_VALUES[fieldId+"_VALUE"]]["NAME"]
							&& this.iblocksSkuParams[iblockId][i].VALUES[product.PRODUCT_PROPS_VALUES[fieldId+"_VALUE"]]["NAME"].length > 0
						)
						{
							html = this.iblocksSkuParams[iblockId][i].VALUES[product.PRODUCT_PROPS_VALUES[fieldId+"_VALUE"]]["NAME"];
						}
						else
						{
							html = product.PRODUCT_PROPS_VALUES[fieldId+"_VALUE"];
						}

						if(html)
							html = BX.util.htmlspecialchars(html);

						break;
					}

					if(html.length > 0)
						break;
				}
			}

			if(html.length <= 0)
				html = product.PRODUCT_PROPS_VALUES[fieldId+"_VALUE"];
		}
		else
		{
			html = "";
		}

		cellNodes.push(BX.create('span', {html: html}));
	}

	if(cellNodes.length > 0)
	{
		result = BX.create('td');

		if(tdClass)
			BX.addClass(result, tdClass);

		while(cellNodes.length > 0)
			result.appendChild(cellNodes.pop());

		if(fieldId == "NAME")
		{
			result.style.minWidth = "250px";

			if(isSetItem)
			{
				result.style.fontStyle = "italic";
				result.style.paddingLeft = "40px";
			}
		}
	}

	return result;
};

BX.Sale.Admin.OrderBasket.prototype.onToggleBundleChildren = function(oldParentId, anchor)
{
	if(anchor.innerHTML == BX.message("SALE_ORDER_BASKET_TURN"))
		anchor.innerHTML = BX.message("SALE_ORDER_BASKET_EXPAND");
	else
		anchor.innerHTML = BX.message("SALE_ORDER_BASKET_TURN");

	var  bundleChildren = BX.findChildren(BX(this.tableId), {className: "bundle-child-"+oldParentId}, true);

	for(var i in bundleChildren)
		if(bundleChildren.hasOwnProperty(i))
			BX.toggleClass(bundleChildren[i], "basket-bundle-child-hidden");
};

BX.Sale.Admin.OrderBasket.prototype.createFieldSkuProps = function(basketCode, product, fieldId)
{
	return this.createSkuPropsTable(basketCode, product);
};

BX.Sale.Admin.OrderBasket.prototype.createSkuPropsTable = function(basketCode, product)
{
	var table = BX.create('table'),
			html,
			propIdx = 0,
			skuCodes = [];

	if(product.SKU_PROPS)
	{
		for(var skuId in product.SKU_PROPS)
		{
			if(!product.SKU_PROPS.hasOwnProperty(skuId))
				continue;

			if(!product.SKU_PROPS[skuId]["VALUE"])
				continue;

			if(product.SKU_PROPS[skuId]["VALUE"]["ID"] == "-")
				continue;

			if(product.SKU_PROPS[skuId]["VALUE"] && product.SKU_PROPS[skuId]["VALUE"]["PICT"])
			{
				html = '<div style="width: 17px; height: 17px; text-align: center; border: 1px solid gray;">'+
					'<img  width="17" height="17" src="'+product.SKU_PROPS[skuId]["VALUE"]["PICT"]+'">'+
					'</div>';
			}
			else
			{
				if(!product.SKU_PROPS[skuId]["VALUE"]["NAME"])
					product.SKU_PROPS[skuId]["VALUE"]["NAME"] = skuId;

				html = '<div style="font-size: 9px; padding: 2px 5px; text-align: center; border: 1px solid gray;">'+BX.util.htmlspecialchars(product.SKU_PROPS[skuId]["VALUE"]["NAME"])+'</div>';
			}

			// Unlinked property of type E
			if(!product.SKU_PROPS[skuId]["NAME"])
				product.SKU_PROPS[skuId]["NAME"] = skuId;

			table.appendChild(
					BX.create('tr',{
						children: [
							BX.create('td',{
								html: '<span style="color: gray; font-size: 11px">'+BX.util.htmlspecialchars(product.SKU_PROPS[skuId]["NAME"])+': </span>'
							}),
							BX.create('td',{html: html})
						]
					})
			);

			skuCodes.push(product.SKU_PROPS[skuId]["CODE"]);
			propIdx++;
		}
	}

	if(product.PROPS)
	{
		for(var i in product.PROPS)
		{
			if(!product.PROPS.hasOwnProperty(i))
				continue;

			if(!product.PROPS[i] || skuCodes.indexOf(product.PROPS[i]["CODE"]) != -1)
				continue;

			if(!product.PROPS[i]["NAME"]) product.PROPS[i]["NAME"] = "";
			if(!product.PROPS[i]["VALUE"]) product.PROPS[i]["VALUE"] = "";

			var tr = BX.create('tr',{
				children: [
					BX.create('td',{
						html: '<span style="color: gray; font-size: 11px">'+BX.util.htmlspecialchars(product.PROPS[i]["NAME"])+': </span>'
					}),
					BX.create('td',{
						html: '<div style="font-size: 9px; padding: 2px 5px; text-align: center;">'+BX.util.htmlspecialchars(product.PROPS[i]["VALUE"])+'</div>'
					})
				]
			});

			if(!this.isShowXmlId && (product.PROPS[i]["CODE"] == "PRODUCT.XML_ID" || product.PROPS[i]["CODE"] == "CATALOG.XML_ID"))
				tr.style.display = "none";

			table.appendChild(tr);
			propIdx++;
		}
	}

	return table;
};

BX.Sale.Admin.OrderBasket.prototype.createFieldQuantity = function(basketCode, product, fieldId)
{
	return this.createTextField(basketCode, product, fieldId);
};

BX.Sale.Admin.OrderBasket.prototype.createFieldImage = function(basketCode, product, fieldId)
{
	var pictureNode, resultNode;

	if(product.PICTURE_URL)
	{
		pictureNode = BX.create('img',{
			props:{src: product.PICTURE_URL}
		});
	}
	else
	{
		pictureNode = BX.create('div',{
			props:{
				className: "no_foto"
			},
			text: BX.message("SALE_ORDER_BASKET_NO_PICTURE")
		});
	}

	if(typeof product.EDIT_PAGE_URL != "undefined")
	{
		resultNode = BX.create('a',{
			props: {
				href: product.EDIT_PAGE_URL ? product.EDIT_PAGE_URL : "",
				target:"_blank"
			},
			children: [pictureNode]
		});

		resultNode.style.textAlign = (this.mode == "edit" ? "left" : "center");
	}
	else
	{
		resultNode = BX.create('div',{
			style: {
				width: '150px',
				textAlign: this.mode == "edit" ? "left" : "center"
			},
			children: [pictureNode]
		});
	}

	return resultNode;
};

BX.Sale.Admin.OrderBasket.prototype.createFieldPrice = function(basketCode, product, fieldId)
{
	var resultNode = BX.create('span',{props:{className: "view_price"}}),
		price = "";

	if(typeof product.PRICE != "undefined")
	{
		price = BX.Sale.Admin.OrderEditPage.currencyFormat(product.PRICE);
	}

	resultNode.appendChild(BX.create('div', {
		html: price,
		style: {whiteSpace: 'nowrap'}
		}
	));

	if(price != "")
	{
		var display = "none";

		if(parseFloat(product.BASE_PRICE) > 0 && parseFloat(product.PRICE) != parseFloat(product.BASE_PRICE))
			display = "";

		var basePrice = BX.create('div',{
			props: {className: "base_price"},
			style: {display: display},
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
		});

		resultNode.appendChild(basePrice);
		resultNode.appendChild(priceNotes);
	}

	return resultNode;
};

BX.Sale.Admin.OrderBasket.prototype.createTextField = function(basketCode, product, fieldId)
{
	var text = typeof product[fieldId] != "undefined" ? product[fieldId] : "";
	return BX.create('span', {text: text+" "});
};

BX.Sale.Admin.OrderBasket.prototype.onSettings = function()
{
	this.settingsDialog.show();
};

BX.Sale.Admin.OrderBasket.prototype.createProductMenuContent = function(basketCode, product)
{
	return [];
};

BX.Sale.Admin.OrderBasket.prototype.onProductRowMouseOver = function(rowNode)
{
	BX.addClass(rowNode, "tr_hover");
};

BX.Sale.Admin.OrderBasket.prototype.onProductRowMouseOut = function(rowNode)
{
	BX.removeClass(rowNode, "tr_hover");
};

/*
 *
 *  OrderBasketEdit
 *
 */

BX.Sale.Admin.OrderBasketEdit = function(params)
{
	this.productEditDialog = new BX.Sale.Admin.OrderBasketProductEditDialog(this);
	BX.Sale.Admin.OrderBasket.call(this, params);

	this.settingsDialog = new BX.Sale.Admin.OrderBasket.SettingsDialog({
		basket: this
	});
};

BX.Sale.Admin.OrderBasketEdit.prototype = Object.create(BX.Sale.Admin.OrderBasket.prototype);

BX.Sale.Admin.OrderBasketEdit.prototype.getProductIdBySkuProps = function(params)
{
	BX.Sale.Admin.OrderAjaxer.sendRequest(
		BX.Sale.Admin.OrderEditPage.ajaxRequests.getProductIdBySkuProps(params)
	);
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

	if(typeof basket.WEIGHT_FOR_HUMAN !== "undefined")
		this.totalBlock.setFieldValue('WEIGHT', basket.WEIGHT_FOR_HUMAN);

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

BX.Sale.Admin.OrderBasketEdit.prototype.updateProductPriceCell = function(product)
{
	var basketCode = this.getProductBasketCode(product),
		id = this.getPriceCellId(basketCode),
		oldPriceCell = BX(id);

	if(!oldPriceCell)
		return;

	/*
	if(this.customPrices[basketCode])
		delete this.customPrices[basketCode];
	*/
	var newPriceCell = this.createFieldPrice(basketCode, product),
		priceParent = oldPriceCell.parentNode;

	priceParent.removeChild(oldPriceCell);
	priceParent.appendChild(newPriceCell);
	newPriceCell.id = id;
	this.updateBasePrice(basketCode, product);
	this.updateProviderData(basketCode, product);
	this.updateProductSumm(basketCode);
};

BX.Sale.Admin.OrderBasketEdit.prototype.updateBasePrice = function(basketCode, product)
{
	var form = BX.Sale.Admin.OrderEditPage.getForm();
	var basePrice = form.elements[this.getFieldName(basketCode, "BASE_PRICE")];

	if(basePrice)
		basePrice.value = product.BASE_PRICE;

	var priceBase = form.elements[this.getFieldName(basketCode, "PRICE_BASE")];

	if(priceBase)
		priceBase.value = product.PRICE_BASE;
};

BX.Sale.Admin.OrderBasketEdit.prototype.updateProviderData = function(basketCode, product)
{
	var form = BX.Sale.Admin.OrderEditPage.getForm(),
		providerData = form.elements[this.getFieldName(basketCode, "PROVIDER_DATA")];

	if(providerData)
		providerData.value = product.PROVIDER_DATA;
};

BX.Sale.Admin.OrderBasketEdit.prototype.updateProductDiscountsCell = function(product)
{
	var basketCode = this.getProductBasketCode(product),
		id = this.getDiscountCellId(basketCode),
		oldDiscountCell = BX(id);

	if(!oldDiscountCell)
		return;

	var newDiscountCell = this.createDiscountCell(basketCode, product),
		discountParent = oldDiscountCell.parentNode;

	discountParent.removeChild(oldDiscountCell);
	discountParent.appendChild(newDiscountCell);
	newDiscountCell.id = id;
};

BX.Sale.Admin.OrderBasketEdit.prototype.productUpdateLight = function(product)
{
	var basketCode = this.getProductBasketCode(product),i;

	product["NOTES"] = this.products[basketCode]["NOTES"];
	this.updateProductPriceCell(product);
	this.updateProductDiscountsCell(product);

	//don't send requests to refresh data
	this.canSendUpdateQuantityRequest = false;

	if(this.getProductQuantity(basketCode) != product["QUANTITY"])
		this.setProductQuantity(basketCode, product["QUANTITY"]);

	if(this.getCustomPrice(basketCode) != product["CUSTOM_PRICE"])
		this.setCustomPrice(basketCode, product["CUSTOM_PRICE"]);

	for(i in product)
		if(product.hasOwnProperty(i))
			this.products[basketCode][i] = product[i];

	if(product.SET_ITEMS)
		for(i in product.SET_ITEMS)
			if(product.SET_ITEMS.hasOwnProperty(i))
				this.updateSetQuantity(product.SET_ITEMS[i], basketCode);

	this.canSendUpdateQuantityRequest = true;
};

BX.Sale.Admin.OrderBasketEdit.prototype.updateSetQuantity = function(product, parentBasketCode)
{
	var basketCode = "set_" + parentBasketCode + "_" + product.OFFER_ID,
		qSpqn = BX(this.getQuantityCellId(basketCode)),
		summCell = BX(this.getProductSummCellId(basketCode));

	if(qSpqn)
		qSpqn.innerHTML = product.QUANTITY;

	if(summCell)
		summCell.innerHTML = BX.Sale.Admin.OrderEditPage.currencyFormat(product.QUANTITY*product.PRICE);
};

BX.Sale.Admin.OrderBasketEdit.prototype.setCustomPrice = function(basketCode, value)
{
	var	form = BX.Sale.Admin.OrderEditPage.getForm();
	form.elements[this.getFieldName(basketCode, "CUSTOM_PRICE")].value = value;
};

BX.Sale.Admin.OrderBasketEdit.prototype.getCustomPrice = function(basketCode)
{
	var	form = BX.Sale.Admin.OrderEditPage.getForm();
	return form.elements[this.getFieldName(basketCode, "CUSTOM_PRICE")].value;
};

BX.Sale.Admin.OrderBasketEdit.prototype.getParamsBySkuProps = function(params)
{
	var customPrice = false;

	if(this.customPrices[params.replaceBasketCode] !== undefined)
		customPrice = this.customPrices[params.replaceBasketCode];

	BX.Sale.Admin.OrderAjaxer.sendRequest(
		BX.Sale.Admin.OrderEditPage.ajaxRequests.addProductToBasketBySkuProps({
			oldProductId: params.oldProductId,
			oldProductIblock: params.oldProductIblock,
			quantity: params.quantity,
			replaceBasketCode: params.replaceBasketCode,
			skuPropsVal: params.skuPropsVal,
			columns: this.visibleColumns,
			customPrice: customPrice
		})
	);
};

BX.Sale.Admin.OrderBasketEdit.prototype.getParamsByProductId = function(params, iBlockId, callback)
{
	var customPrice = false;

	if(this.customPrices[params.replaceBasketCode] !== undefined)
		customPrice = this.customPrices[params.replaceBasketCode];

	BX.Sale.Admin.OrderAjaxer.sendRequest(
		BX.Sale.Admin.OrderEditPage.ajaxRequests.addProductToBasket(
			params.id,
			params.quantity,
			params.replaceBasketCode,
			this.visibleColumns,
			customPrice
		)
	);
};

BX.Sale.Admin.OrderBasketEdit.prototype.createProductBasementSkuCell = function(basketCode, product)
{
	var td = BX.create('td',{props:{id: this.idPrefix+"sale-order-basket-product-"+basketCode+"-basement-sku"}}),
		divSku = BX.create('div',{props:{className: 'adm-s-order-table-ddi-table-sku'}}),
		possibleSkuProps = product.SKU_PROPS_POSSIBLE_VALUES,
		title, currentSkuId;

	for(var i in this.iblocksSkuParamsOrder[product.OFFERS_IBLOCK_ID])
	{
		if(!this.iblocksSkuParamsOrder[product.OFFERS_IBLOCK_ID].hasOwnProperty(i))
			continue;

		var idx = this.iblocksSkuParamsOrder[product.OFFERS_IBLOCK_ID][i];

		if(!possibleSkuProps[idx])
			continue;

		title = this.iblocksSkuParams[product.OFFERS_IBLOCK_ID][idx]["NAME"];
		currentSkuId = product.SKU_PROPS[idx]["VALUE"]["ID"];

		divSku.appendChild(
			this.createSkuSelector(basketCode, idx, title, currentSkuId, possibleSkuProps[idx], product)
		);
	}

	td.appendChild(divSku);
	return td;
};

BX.Sale.Admin.OrderBasketEdit.prototype.createSkuSelector = function(basketCode, skuId, title, activeItemId, items, product)
{
	var ul = BX.create('ul', {
			attrs: {
				"data-sku-id": skuId
			}}),
		styleType = 'sku',
		variantsCount = 0,
		activeVariant = 0;

	for(var idx in this.iblocksSkuParams[product.OFFERS_IBLOCK_ID][skuId]["ORDER"])
	{
		if(!this.iblocksSkuParams[product.OFFERS_IBLOCK_ID][skuId]["ORDER"].hasOwnProperty(idx))
			continue;

		var item = this.iblocksSkuParams[product.OFFERS_IBLOCK_ID][skuId]["ORDER"][idx],
			found = false;

		for(var i in items)
		{
			if(!items.hasOwnProperty(i))
				continue;

			if(items[i] == this.iblocksSkuParams[product.OFFERS_IBLOCK_ID][skuId]['VALUES'][item]['ID'])
			{
				found = true;
				break;
			}
		}

		if(!found)
			continue;

		var html,
			itemId = this.iblocksSkuParams[product.OFFERS_IBLOCK_ID][skuId]["VALUES"][item]["ID"];

		if(this.iblocksSkuParams[product.OFFERS_IBLOCK_ID][skuId]["VALUES"][item]["PICT"])
		{
			html = '<img  src="'+this.iblocksSkuParams[product.OFFERS_IBLOCK_ID][skuId]["VALUES"][item]["PICT"]+'">';
		}
		else
		{
			styleType = 'size';
			html = BX.util.htmlspecialchars(this.iblocksSkuParams[product.OFFERS_IBLOCK_ID][skuId]["VALUES"][item]["NAME"]);
		}

		var span = BX.create('span',{
				props: {
					className: 'cnt'
				},
				html: html
			}),
			li = BX.create('li',{
				attrs: {
					"data-value": BX.util.htmlspecialchars(item),
					"data-id": itemId
				},
				children:[span]
			});

		if(itemId == activeItemId)
		{
			BX.addClass(li,'bx-active');
			activeVariant = variantsCount;
		}

		ul.appendChild(li);
		BX.bind(span, "click", BX.delegate(
			function(e) {

				var span = e.target || e.srcElement,
					activeValue = span.parentNode.getAttribute("data-value") || span.parentNode.parentNode.getAttribute("data-value"),
					activeId = span.parentNode.getAttribute("data-id") || span.parentNode.parentNode.getAttribute("data-id"),
					propsVal = this.getSkuProps(basketCode, product.SKU_PROPS);

				activeValue = BX.util.htmlspecialcharsback(activeValue);

				if(activeId == activeItemId)
					return;

				propsVal[skuId] = activeId;

				this.getProductIdBySkuProps({
					productId: product.PRODUCT_ID,
					iBlockId: product.OFFERS_IBLOCK_ID,
					skuProps: propsVal,
					skuOrder: this.iblocksSkuParamsOrder[product.OFFERS_IBLOCK_ID],
					changedSkuId: skuId,
					callback: BX.delegate(
						function(result){

							if(!result.OFFER_ID)
							{
								BX.debug("can't find product id for set of sku props");
								return;
							}

							var offerId = result.OFFER_ID;

							for(var i in this.products)
							{
								if(!this.products.hasOwnProperty(i))
									continue;

								if(i == basketCode)
									continue;

								if(this.products[i].OFFER_ID == offerId)
									if(!confirm(BX.message("SALE_ORDER_BASKET_POSITION_EXISTS").replace("#NAME#", this.products[i].NAME)))
										return;
							}

							this.onSkuSelectorClick(basketCode, ul, skuId, activeValue, activeId);
							this.onSkuPropSelect(basketCode, offerId);						},
						this
					)}
				);
			},
			this
		));

		variantsCount++;
	}

	var leftArrow = BX.create('div', {
			props:{
				className: "adm-s-item-detail-"+styleType+"-box-arrow left"
			},
			events:{
				click: BX.delegate( function(){ this.skuSelectorScrollLeft(ul, variantsCount); }, this)
			}
		}),
		rightArrow = BX.create('div', {
			props:{
				className: "adm-s-item-detail-"+styleType+"-box-arrow right"
			},
			events:{
				click: BX.delegate( function(){ this.skuSelectorScrollRight(ul, variantsCount); }, this)
			}
		});

	var result = BX.create('div',{
		props: {
			className: "adm-s-item-detail-sku"
		},
		children: [
			BX.create('div', {props: {className: "adm-s-item-detail-"+styleType+"-title"}, text: title}),
			leftArrow,
			rightArrow,
			BX.create('div', {
				props:{
					className: "adm-s-item-detail-"+styleType+"-box"
				},
				children: [
					BX.create('div', {
						props:{
							className: "adm-s-item-detail-"+styleType+"-box-container"
						},
						children: [
							ul,
							BX.create('input',{
									props: {
										type: 'hidden',
										name: this.getFieldName(basketCode, "SKU")+"["+skuId+"]",
										value: activeItemId
									}
								}
							)
						]
					})
				]
			})
		]
	});

	var currentOffset = (-1) * this.skuSelectorCountScrollOffset(activeVariant, variantsCount);
	this.skuSelectorSetScroll(ul, currentOffset);
	this.skuSelectorSetArrowsVisibility (ul, currentOffset, variantsCount);

	return result;
};

BX.Sale.Admin.OrderBasketEdit.prototype.getFieldsUpdaters = function()
{
	return {
		"TOTAL_PRICES": {context: this, callback: this.setTotalPrices},
		"BASKET": {context: this, callback: this.setBasket},
		"SUM_PAID": {context: this, callback: this.setSumPaid},
		"PAYABLE": {context: this, callback: this.setSumUnPaid},
		"TAX_VALUE": {context: this, callback: this.setTaxValue},
		"DELIVERY_PRICE": {context: this, callback: this.setDeliveryPrice},
		"DELIVERY_PRICE_DISCOUNT": {context: this, callback: this.setDeliveryPriceDiscount},
		"SHIPMENT[1][PRICE_DELIVERY]": {context: this, callback: this.setDeliveryPrice},
		"COUPONS_LIST": {context: this, callback: this.setCoupons},
		"DISCOUNTS_LIST":  {context: this, callback: this.setDiscounts}
	};
};

BX.Sale.Admin.OrderBasketEdit.prototype.getProductPrice = function(basketCode)
{
	var form = BX.Sale.Admin.OrderEditPage.getForm();
	return form.elements[this.getFieldName(basketCode, "PRICE")].value;
};

BX.Sale.Admin.OrderBasketEdit.prototype.setCoupons = function(coupons)
{
	return BX.Sale.Admin.OrderBasketCoupons.setCoupons(coupons);
};

BX.Sale.Admin.OrderBasketEdit.prototype.getProductQuantity = function(basketCode)
{
	var form = BX.Sale.Admin.OrderEditPage.getForm();
	return form.elements[this.getFieldName(basketCode, "QUANTITY")].value;
};

BX.Sale.Admin.OrderBasketEdit.prototype.setProductQuantity = function(basketCode, quantity)
{
	var form = BX.Sale.Admin.OrderEditPage.getForm(),
		qInput = form.elements[this.getFieldName(basketCode, "QUANTITY")];

	if(!quantity || quantity < 0)
		quantity = 0;
	else
		quantity = this.roundQuantity(quantity);

	if(qInput)
		qInput.value = quantity;

	this.onProductQuantityChange({productId: basketCode});
	return quantity;
};

BX.Sale.Admin.OrderBasketEdit.prototype.setProductPrice = function(basketCode, price)
{
	var form = BX.Sale.Admin.OrderEditPage.getForm(),
		pInput = form.elements[this.getFieldName(basketCode, "PRICE")],
		bpInput = form.elements[this.getFieldName(basketCode, "BASE_PRICE")],
		bpDiv = BX(this.idPrefix+"sale-order-basket-product-"+basketCode+"-base_price"),
		fpDiv = BX(this.idPrefix+"sale-order-basket-product-"+basketCode+"-formatted_price");

	price = parseFloat(price);

	if(!price || price < 0)
		price = 0;
	else
		price = parseFloat(price);

	if(pInput)
		pInput.value = price;

	if(fpDiv)
		fpDiv.innerHTML = BX.Sale.Admin.OrderEditPage.currencyFormat(price);

	if(this.products[basketCode].CUSTOM_PRICE == 'Y')
		this.customPrices[basketCode] = price;
	else
		delete(this.customPrices[basketCode]);

	if(bpInput && bpDiv)
	{
		var basePrice = parseFloat(bpInput.value);

		if(basePrice > 0 && basePrice != price)
		{
			bpDiv.style.display = "";
		}
		else
		{
			bpDiv.style.display = "none";
		}
	}

	this.onProductPriceChange({productId: basketCode});
	return price;
};

BX.Sale.Admin.OrderBasketEdit.prototype.onProductQuantityChange = function(params)
{
	var fieldName = this.getFieldName(params.productId, "QUANTITY"),
		_this = this,
		canSendUpdateQuantityRequest = this.canSendUpdateQuantityRequest;

	clearTimeout(this.qantityUpdaterTimeout);

	this.qantityUpdaterTimeout = setTimeout( function(){

			var tmp = _this.canSendUpdateQuantityRequest;
			_this.canSendUpdateQuantityRequest = canSendUpdateQuantityRequest;
			BX.Sale.Admin.OrderEditPage.callConcreteFieldUpdater(fieldName, params.productId);
			_this.canSendUpdateQuantityRequest = tmp;

		},
		this.qantityUpdaterDelay
	);
};

BX.Sale.Admin.OrderBasketEdit.prototype.onProductPriceChange = function(params)
{
	BX.Sale.Admin.OrderEditPage.callConcreteFieldUpdater(this.getFieldName(params.productId, "PRICE"), params.productId);
};

BX.Sale.Admin.OrderBasketEdit.prototype.updateProductQuantity = function(productId)
{
	this.updateProductSumm(productId);

	if(this.canSendUpdateQuantityRequest)
	{
		BX.Sale.Admin.OrderAjaxer.sendRequest(
			BX.Sale.Admin.OrderEditPage.ajaxRequests.refreshOrderData({
				operation: "FIELD_UPDATE",
				updatedFieldId: "QUANTITY",
				productId: productId
			})
		);
	}
};

BX.Sale.Admin.OrderBasketEdit.prototype.updateProductPrice = function(productId)
{
	this.updateProductSumm(productId);

	BX.Sale.Admin.OrderAjaxer.sendRequest(
		BX.Sale.Admin.OrderEditPage.ajaxRequests.refreshOrderData({
			operation: "FIELD_UPDATE",
			updatedFieldId: "PRICE",
			productId: productId
		})
	);
};

BX.Sale.Admin.OrderBasketEdit.prototype.updateProductSumm = function(productId)
{
	var summ = BX(this.getProductSummCellId(productId));

	if(!summ)
		return;

	summ.innerHTML = BX.Sale.Admin.OrderEditPage.currencyFormat(this.getProductPrice(productId)*this.getProductQuantity(productId));
};

BX.Sale.Admin.OrderBasketEdit.prototype.setTotalPrices = function(prices)
{
	this.totalBlock.setFieldValue("PRICE_BASKET", prices.PRICE_BASKET);
	this.totalBlock.setFieldValue("PRICE_BASKET_DISCOUNTED", prices.PRICE_BASKET_DISCOUNTED);
	this.totalBlock.setFieldValue("PRICE_DELIVERY", prices.PRICE_DELIVERY);
	this.totalBlock.setFieldValue("PRICE_DELIVERY_DISCOUNTED", prices.PRICE_DELIVERY_DISCOUNTED);
	this.totalBlock.setFieldValue("TAX_VALUE", prices.TAX_VALUE);
	this.totalBlock.setFieldValue("SUM_PAID", prices.SUM_PAID);
	this.totalBlock.setFieldValue("SUM_UNPAID", prices.SUM_UNPAID);
};

BX.Sale.Admin.OrderBasketEdit.prototype.setDeliveryPrice = function(price)
{
	this.totalBlock.setFieldValue("PRICE_DELIVERY", price);
};

BX.Sale.Admin.OrderBasketEdit.prototype.setDeliveryPriceDiscount = function(price)
{
	this.totalBlock.setFieldValue("PRICE_DELIVERY_DISCOUNTED", price);
};

BX.Sale.Admin.OrderBasketEdit.prototype.setTaxValue = function(tax)
{
	this.totalBlock.setFieldValue("TAX_VALUE", tax);
};

BX.Sale.Admin.OrderBasketEdit.prototype.addProductSearch = function(params)
{
	var funcName = this.objName+'.getParamsByProductId';
	window[funcName] = BX.proxy(function(params, iblockId){this.getParamsByProductId(params, iblockId);}, this);

	var popup = new BX.CDialog({
		content_url: '/bitrix/tools/sale/product_search_dialog.php?'+
			'lang='+BX.Sale.Admin.OrderEditPage.languageId+
			'&LID='+BX.Sale.Admin.OrderEditPage.siteId+
			'&caller=order_edit'+
			'&func_name='+funcName+
			'&STORE_FROM_ID=0',
		height: Math.max(500, window.innerHeight-400),
		width: Math.max(800, window.innerWidth-400),
		draggable: true,
		resizable: true,
		min_height: 500,
		min_width: 800
	});
	BX.addCustomEvent(popup, 'onWindowRegister', BX.defer(function(){
		popup.Get().style.position = 'fixed';
		popup.Get().style.top = (parseInt(popup.Get().style.top) - BX.GetWindowScrollPos().scrollTop) + 'px';
	}));

	popup.Show();
};

BX.Sale.Admin.OrderBasketEdit.prototype.getCoupons = function()
{
	var form = BX.Sale.Admin.OrderEditPage.getForm();
	return form.elements['COUPONS'].value;
};

BX.Sale.Admin.OrderBasketEdit.prototype.onSkuPropSelect = function(basketCode, newOfferId)
{
	this.getParamsByProductId(
		{
			id: newOfferId,
			quantity: this.getProductQuantity(basketCode),
			replaceBasketCode: basketCode
		},
		0
	);
};

BX.Sale.Admin.OrderBasketEdit.prototype.getSkuProps = function(basketCode, skuProps)
{
	var result = {},
		form = BX.Sale.Admin.OrderEditPage.getForm();

	for(var propId in skuProps)
	{
		if(!skuProps.hasOwnProperty(propId))
			continue;

		var fieldName = this.getFieldName(basketCode, "SKU")+"["+propId+"]";

		if(typeof form.elements[fieldName] != "undefined")
			result[propId] = form.elements[fieldName].value;
	}

	return result;
};

BX.Sale.Admin.OrderBasketEdit.prototype.getFieldName = function(basketCode, type)
{
	return "PRODUCT["+basketCode+"]["+type+"]";
};

BX.Sale.Admin.OrderBasketEdit.prototype.productDeleteClick = function(basketCode)
{
	//this.productDelete(basketCode);

	BX.Sale.Admin.OrderAjaxer.sendRequest(
		BX.Sale.Admin.OrderEditPage.ajaxRequests.refreshOrderData({
			operation: "PRODUCT_DELETE",
			basketCode: basketCode
		})
	);
};

BX.Sale.Admin.OrderBasketEdit.prototype.productDelete = function(basketCode)
{
	var productRaw = BX(this.createProductRowId(basketCode));

	if(!productRaw)
		return;

	var oldParentId = productRaw.getAttribute('data-old-parent-id-parent');

	//set
	if(oldParentId)
	{
		var  bundleChildren = BX.findChildren(productRaw.parentNode, {className: "bundle-child-"+oldParentId}, false);

		for(var i in bundleChildren)
			if(bundleChildren.hasOwnProperty(i))
				bundleChildren[i].parentNode.removeChild(bundleChildren[i]);
	}

	this.onProductDelete(basketCode);

	productRaw.parentNode.removeChild(productRaw);

	this.setRowNumbers();
	this.setProductsCount(--this.productsCount);

	if(this.productsCount <= 0)
		this.showEmptyRow();

	if(this.customPrices[basketCode])
		delete this.customPrices[basketCode];

	delete(this.products[basketCode]);
};

BX.Sale.Admin.OrderBasketEdit.prototype.onSkuSelectorClick = function(basketCode, ul, skuId, activeValue, activeId)
{
	for(var i=0,l=ul.children.length; i<l; i++)
	{
		var li = ul.children[i];

		if(li.getAttribute('data-value') != BX.util.htmlspecialchars(activeValue))
			BX.removeClass(li,'bx-active');
		else
			BX.addClass(li,'bx-active');
	}

	this.setSkuInput(basketCode, skuId, activeId);
};

BX.Sale.Admin.OrderBasketEdit.prototype.setSkuInput = function(basketCode, skuId, value)
{
	var inputName = this.getFieldName(basketCode, "SKU")+"["+skuId+"]",
		form = BX.Sale.Admin.OrderEditPage.getForm();

	form.elements[inputName].value = value;
};

BX.Sale.Admin.OrderBasketEdit.prototype.skuSelectorSetArrowsVisibility = function(ul, currentOffset, variantsCount)
{
	var leftArrow = ul.parentNode.parentNode.parentNode.childNodes[1];

	if(variantsCount <= 5 || currentOffset >= 0)
		leftArrow.style.display = 'none';
	else
		leftArrow.style.display = '';

	var rightArrow = ul.parentNode.parentNode.parentNode.childNodes[2];

	if(variantsCount <= 5 || currentOffset <= 5 - variantsCount)
		rightArrow.style.display = 'none';
	else
		rightArrow.style.display = '';

};

BX.Sale.Admin.OrderBasketEdit.prototype.skuSelectorScrollLeft = function(ul, variantsCount)
{
	var currentOffset = this.skuSelectorGetScrollOffset(ul);

	if(currentOffset >= 0)
		return false;

	this.skuSelectorSetScroll(ul, ++currentOffset);
	this.skuSelectorSetArrowsVisibility (ul, currentOffset);
	return true;
};

BX.Sale.Admin.OrderBasketEdit.prototype.skuSelectorScrollRight = function(ul, variantsCount)
{
	var currentOffset = this.skuSelectorGetScrollOffset(ul);

	if(currentOffset <= 5 - variantsCount)
		return false;

	this.skuSelectorSetScroll(ul, --currentOffset);
	this.skuSelectorSetArrowsVisibility (ul, currentOffset, variantsCount);
	return true;
};

BX.Sale.Admin.OrderBasketEdit.prototype.createDiscountCell = function(basketCode, product)
{
	var discountsNode = BX.create("text",{html: "&nbsp"}),
		isDiscountsExist = false,
		skuPropsExist = product.SKU_PROPS && Object.keys(product.SKU_PROPS).length > 0;


	if(this.discounts && this.discounts.RESULT && this.discounts.RESULT.BASKET)
	{
		discountsNode = BX.Sale.Admin.OrderEditPage.createDiscountsNode(
			basketCode,
			"BASKET",
			this.discounts.RESULT.BASKET[basketCode] ? this.discounts.RESULT.BASKET[basketCode] : {},
			this.discounts,
			"EDIT"
		);

		isDiscountsExist = this.discounts.RESULT.BASKET[basketCode] ? true : false;
	}

	var td = BX.create('td',{
		props:{
			id: this.getDiscountCellId(basketCode)
		},
		children: [
			BX.create('div',{
				children: [discountsNode]
			})
		]
	});

	td.colSpan = this.columnsCount-1;


	if(skuPropsExist || isDiscountsExist)
		td.style.borderTop = "1px solid #ddd";

	return td;
};

BX.Sale.Admin.OrderBasketEdit.prototype.skuSelectorCountScrollOffset = function(activeSkuOrder, skuCount)
{
	var result = 0;
	activeSkuOrder = activeSkuOrder+1;

	if(skuCount > 5 && activeSkuOrder > 2)
	{
		var diff = skuCount - activeSkuOrder;

		result = activeSkuOrder - 3;

		if(diff < 2)
			result = result - (2 - diff);
	}

	return result;
};

BX.Sale.Admin.OrderBasketEdit.prototype.skuSelectorGetStepSize = function()
{
	return 30; //px width of sku square
};

BX.Sale.Admin.OrderBasketEdit.prototype.skuSelectorGetScrollOffset = function(el)
{
	if (!el)
		return false;

	var result  = parseInt(el.style.marginLeft, 10);

	if(!result)
		result = 0;
	else
		result = parseInt(result/this.skuSelectorGetStepSize(), 10);

	return result;
};

BX.Sale.Admin.OrderBasketEdit.prototype.skuSelectorSetScroll = function(el, stepsCount)
{
	el.style.marginLeft = stepsCount * this.skuSelectorGetStepSize() + 'px';
};

BX.Sale.Admin.OrderBasketEdit.prototype.onCouponsRecount = function()
{
	BX.Sale.Admin.OrderAjaxer.sendRequest(
		BX.Sale.Admin.OrderEditPage.ajaxRequests.refreshOrderData({
			operation: "COUPONS_RECOUNT"
		})
	);
};

BX.Sale.Admin.OrderBasketEdit.prototype.createProductMenuContent = function(basketCode)
{
	return [{
			"ICON": "view",
			"TEXT": BX.message("SALE_ORDER_BASKET_PROD_MENU_EDIT"),
			"ACTION":  this.objName+'.productEdit("'+basketCode+'")',
			"DEFAULT":true
		},
		{
			"ICON": "delete",
			"TEXT": BX.message("SALE_ORDER_BASKET_PROD_MENU_DELETE"),
			"ACTION": this.objName+'.productDeleteClick("'+basketCode+'")'
		}
	];
};

BX.Sale.Admin.OrderBasketEdit.prototype.productEdit = function(basketCode)
{
	this.productEditDialog.show(basketCode);
};

BX.Sale.Admin.OrderBasketEdit.prototype.createMeasureRatioNode = function(basketCode, quantityInputNode, ratio)
{
	if(!BX.type.isElementNode(quantityInputNode))
		return null;

	if(!ratio || ratio == 1)
		return null;

	var upArrow = BX.create('a', {
			props:{
				href: "javascript:void(0);",
				title: BX.message("SALE_ORDER_BASKET_UP_RATIO").replace("#RATIO#", ratio),
				className: "plus"
			}
		}),
		downArrow = BX.create('a', {
			props:{
				href: "javascript:void(0);",
				title: BX.message("SALE_ORDER_BASKET_DOWN_RATIO").replace("#RATIO#", ratio),
				className: "minus"
			}
		}),
		_this = this;

	BX.bind(upArrow, "click", function(e){
		quantityInputNode.value = _this.roundQuantity(parseFloat(quantityInputNode.value) + parseFloat(ratio));
		_this.onProductQuantityChange({productId: basketCode});
	});

	BX.bind(downArrow, "click", function(e){
		quantityInputNode.value = _this.roundQuantity(parseFloat(quantityInputNode.value) - parseFloat(ratio));
		_this.onProductQuantityChange({productId: basketCode});
	});

	return BX.create('div', {
		props:{
			className: "quantity_control"
		},
		children:[
			upArrow,
			downArrow
		],
		style: {
			position: 'inherit',
			marginLeft: '3px'
		}
	});
};

BX.Sale.Admin.OrderBasketEdit.prototype.createFieldQuantity = function(basketCode, product, fieldId)
{
	var ratio = typeof product.MEASURE_RATIO != "undefined" ? product.MEASURE_RATIO : 1,
		updater = {},
		result,
		_this = this,
		input  = BX.create('input', {
			props:{
				type: "text",
				name: this.getFieldName(basketCode, "QUANTITY"),
				value: this.roundQuantity(product.QUANTITY),
				className: "tac"
			},
			style: { width: '60px' }
		}),
		ratioNode = this.createMeasureRatioNode(basketCode, input, ratio);

	/*
	 * If we will receive the error during the refreshOrderData error we must restore this value
	 */
	BX.bind(
		input,
		"focus",
		function(e){
			var oldValue = input.value;
			BX.Sale.Admin.OrderEditPage.addRollbackMethod(
				function(){
					var tmp = _this.canSendUpdateQuantityRequest;
					_this.canSendUpdateQuantityRequest = false;
					_this.setProductQuantity(basketCode, oldValue);
					setTimeout(function(){_this.canSendUpdateQuantityRequest = tmp;}, 1);
			});
		}
	);

	BX.bind(
		input,
		"change",
		function(e){
			_this.setProductQuantity( basketCode, input.value);
		}
	);

	BX.bind(input, "keydown", function(e){
			if(!e) e = window.event;
			if(!e) return;
			if(e.keyCode == 13) input.blur();
		}
	);

	updater[this.getFieldName(basketCode, "QUANTITY")] = {
		callback: _this.updateProductQuantity,
		context: _this
	};

	BX.Sale.Admin.OrderEditPage.registerFieldsUpdaters(updater);

	if(ratioNode)
	{
		result = BX.create('span', {
			children: [input, ratioNode],
			style: { display: 'inline-flex' }
		});
	}
	else
	{
		result = input;
	}

	return result;
};

BX.Sale.Admin.OrderBasketEdit.prototype.createFieldPrice = function(basketCode, product, fieldId)
{
	var price;

	if(typeof(this.customPrices[basketCode]) == "undefined")
		price = product.PRICE;
	else
		price = this.customPrices[basketCode];

	var updater = {},
		_this = this,
		inputP = BX.create('input', {
			props:{
				type: "text",
				name: this.getFieldName(basketCode, "PRICE"),
				value: price,
				maxlength: "9",
				size: 5
			}
		}),
		spanInput = BX.create('span', {
			props:{
				className: "edit_price_product"
			},
			children: [
				inputP
			]
		}),
		spanFormattedPrice = BX.create('span', {
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
		apencil = BX.create('a', {
			props:{
				href: "javascript:void(0);"
			},
			children: [
				BX.create('span', {
					props:{
						className: "pencil"
					}
				})
			]
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
				spanInput,
				currencySpan,
				apencil,
				basePrice,
				priceNotes
			]
		});

	inputP.style.width = "60px";

	BX.bind(apencil, "click", function(e){
		_this.onPriceEditEnable(containerDiv, inputP);
	});

	BX.bind(spanFormattedPrice, "click", function(e){
		_this.onPriceEditEnable(containerDiv, inputP);
	});

	BX.bind(inputP, "change", function(e){
		var	form = BX.Sale.Admin.OrderEditPage.getForm();
		form.elements[_this.getFieldName(basketCode, "CUSTOM_PRICE")].value = "Y";
		var price = _this.setProductPrice(basketCode, inputP.value);
		spanFormattedPrice.innerHTML = BX.Sale.Admin.OrderEditPage.currencyFormat(price);
	});

	BX.bind(inputP, "keydown", function(e){
			if(!e) e = window.event;
			if(!e) return;
			if(e.keyCode == 13) inputP.blur();
		}
	);

	BX.bind(inputP, "blur", function(e){
		_this.onPriceEditDisable(containerDiv);
	});

	updater[this.getFieldName(basketCode, "PRICE")] = {
		callback: _this.updateProductPrice,
		context: _this
	};

	BX.Sale.Admin.OrderEditPage.registerFieldsUpdaters(updater);

	return containerDiv;
};

BX.Sale.Admin.OrderBasketEdit.prototype.createDiscountsNodeBasket = function(discounts)
{
	return BX.Sale.Admin.OrderEditPage.createDiscountsNode(
		"",
		"DISCOUNT_LIST",
		discounts,
		this.discounts,
		"EDIT"
	);
};

BX.Sale.Admin.OrderBasketEdit.prototype.getHiddenFieldsNames = function()
{
	return ["CURRENCY", "PRODUCT_PROVIDER_CLASS", "NAME", "DETAIL_PAGE_URL", "WEIGHT",
			"CATALOG_XML_ID", "NOTES", "PRODUCT_XML_ID", "OFFER_ID", "MODULE", "CUSTOM_PRICE", "IS_SET_ITEM", "IS_SET_PARENT", "MEASURE_CODE"];
};

BX.Sale.Admin.OrderBasketEdit.prototype.createHiddenFields = function(basketCode, product)
{
	var result = [];

	if(typeof product["CUSTOM_PRICE"] == "undefined")
		product["CUSTOM_PRICE"] = "N";

	for(var i in product)
	{
		if(!product.hasOwnProperty(i))
			continue;

		if(!product[i] && i != "CATALOG_XML_ID")
			continue;

		if(i == "PROPS")
		{
			result.push(
				BX.create('span', {
					html: this.createSkuPropsHiddenHtml(basketCode, product["PROPS"], product.IS_SET_ITEM, 'PROPS')
				})
			);

			continue;
		}

		if(i == "SKU_PROPS")
		{
			result.push(
				BX.create('span', {
					html: this.createSkuPropsHiddenHtml(basketCode, product["SKU_PROPS"], product.IS_SET_ITEM, 'SKU_PROPS')
				})
			);

			continue;
		}

		if(typeof product[i] == "object")
			continue;

		if(i == "PRICE" || i == "QUANTITY")
			continue;

		result.push(
			BX.create('input', {
				props:{
					type: "hidden",
					name: this.getFieldName(basketCode, i),
					value: product[i]
				}
			})
		);
	}

	return result;
};

BX.Sale.Admin.OrderBasketEdit.prototype.createSkuPropsHiddenHtml = function(basketCode, props, isSetItem, propsName)
{
	if(!props)
		return "";

	var propIdx = 0,
		propsFieldName = this.getFieldName(basketCode, propsName),
		skuCodes = [],
		hiddenFieldsHtml = '';

	if(props)
	{
		for(var i in props)
		{
			if(!props.hasOwnProperty(i))
				continue;

			if(!props[i] || skuCodes.indexOf(props[i]["CODE"]) != -1)
				continue;

			if(isSetItem != "Y")
			{
				var name = props[i]["NAME"] ? BX.util.htmlspecialchars(props[i]["NAME"]) : "",
					value = props[i]["VALUE"] ? BX.util.htmlspecialchars(props[i]["VALUE"]) : "",
					code = props[i]["CODE"] ? BX.util.htmlspecialchars(props[i]["CODE"]) : "",
					sort = props[i]["SORT"] ? BX.util.htmlspecialchars(props[i]["SORT"]) : 100,
					id = props[i]["ID"] ? BX.util.htmlspecialchars(props[i]["ID"]) : 0;

				hiddenFieldsHtml += '<input type="hidden" name="'+propsFieldName+'['+propIdx+'][NAME]" value="'+name+'">'+
				'<input type="hidden" name="'+propsFieldName+'['+propIdx+'][VALUE]" value="'+value+'">'+
				'<input type="hidden" name="'+propsFieldName+'['+propIdx+'][CODE]" value="'+code+'">'+
				'<input type="hidden" name="'+propsFieldName+'['+propIdx+'][ID]" value="'+id+'">'+
				'<input type="hidden" name="'+propsFieldName+'['+propIdx+'][SORT]" value="'+sort+'">';
			}

			propIdx++;
		}
	}

	return hiddenFieldsHtml;
};

BX.Sale.Admin.OrderBasketEdit.prototype.onPriceEditEnable = function(containerNode, inputNode)
{
	BX.addClass(containerNode, "edit_enable");
	inputNode.focus();
};

BX.Sale.Admin.OrderBasketEdit.prototype.onPriceEditDisable = function(containerNode)
{
	BX.removeClass(containerNode, "edit_enable");
};

BX.Sale.Admin.OrderBasketEdit.prototype.addFieldUpdater = function(basketCode, fieldName, callback)
{
	if(!this.fieldsUpdaters[basketCode])
		this.fieldsUpdaters[basketCode] = {};

	if(!this.fieldsUpdaters[basketCode][fieldName])
		this.fieldsUpdaters[basketCode][fieldName] = [];

	if(typeof callback == "function")
		this.fieldsUpdaters[basketCode][fieldName].push(callback);
	else
		BX.debug("BX.Sale.Admin.OrderBasketEdit.prototype.addFieldUpdater() callback is not a function!");
};

BX.Sale.Admin.OrderBasketEdit.prototype.callFieldUpdaters = function(basketCode, fieldName, fieldValue)
{
	if(!this.fieldsUpdaters[basketCode])
		return false;

	if(!this.fieldsUpdaters[basketCode][fieldName])
		return false;

	var updaters = this.fieldsUpdaters[basketCode][fieldName];

	for(var i in updaters)
	{
		if(!updaters.hasOwnProperty(i))
			continue;

		if(typeof updaters[i] != "function")
			continue;

		updaters[i].call(this, fieldValue);
	}
};

BX.Sale.Admin.OrderBasketEdit.prototype.setProductFieldValue = function(basketCode, fieldName, value)
{
	var form = BX.Sale.Admin.OrderEditPage.getForm(),
		formField = form.elements[this.getFieldName(basketCode, fieldName)];

	if(formField && formField.value)
		formField.value = value;


	if(this.fieldsUpdaters[basketCode]
		&& this.fieldsUpdaters[basketCode][fieldName]
		&& typeof (this.fieldsUpdaters[basketCode][fieldName]) == "function"
	)
	{
		this.fieldsUpdaters[basketCode][fieldName].call(this, value);
	}
};

BX.Sale.Admin.OrderBasketEdit.prototype.getProductFieldValue = function(basketCode, fieldName)
{
	var form = BX.Sale.Admin.OrderEditPage.getForm();

	if(!form)
		return "";

	var result = "",
		formField = form.elements[this.getFieldName(basketCode, fieldName)];

	if(formField && formField.value)
		result = formField.value;

	return result;
};

/*
 *
 * OrderBasketEditTotal
 *
 */

BX.Sale.Admin.OrderBasketEditTotal = function(params)
{
	if(!params.fields)
	{
		BX.debug("OrderBasketEditTotal:constructor() params.fields not defined!");
		return;
	}

	this.fields = {};
	this.formulas = {};

	var _this = this;

	var getUnitOfMesure = function(fieldId)
	{
		var type = _this.fields[fieldId]["type"],
			result = "";

		if(type == "currency")
			result = BX.Sale.Admin.OrderEditPage.currencyLang;
		else if(type == "weight")
			result = params.weightUnit;

		return result;
	};

	var getFormattedValue = function(fieldId)
	{
		var value = _this.fields[fieldId]["value"],
			type = _this.fields[fieldId]["type"],
			result = value;

		if(type == "currency")
			result = BX.Sale.Admin.OrderEditPage.currencyFormat(value, false);
		else if(type == "weight")
			result = value;

		return result;
	};

	var setFieldView = function(fieldId)
	{
		var field = BX(_this.fields[fieldId].id);

		if(!field)
		{
			BX.debug("OrderBasketEditTotal:setFieldView() can't find field with id: \""+_this.fields[fieldId].id+"\"");
			return false;
		}

		if(_this.fields[fieldId]["edit"])
		{
			var span = BX.findChild(field, {className: 'formated_field_view'}, true, false);

			if(span)
				span.innerHTML = getFormattedValue(fieldId);
		}
		else
		{
			field.innerHTML = getFormattedValue(fieldId);

			if(_this.fields[fieldId]["type"] != "currency")
				field.innerHTML = field.innerHTML + " " + getUnitOfMesure(fieldId)
		}

		return true;
	};

	var setFieldEditable = function(fieldId)
	{
		var field = BX(_this.fields[fieldId].id);

		if(!field)
		{
			BX.debug("OrderBasketEditTotal:setFieldView() can't find field with id: \""+_this.fields[fieldId].id+"\"");
			return false;
		}

		var input = BX.create('input', {
				props:{
					type: "text",
					name: fieldId,
					value: _this.fields[fieldId].value,
					maxlength: "9",
					size: 5
				}
			}),
			spanInput = BX.create('span', {
				props:{
					className: "edit_field"
				},
				children: [
					input
				]
			}),
			spanFormattedPrice = BX.create('span', {
				props:{
					className: "formated_field_view"
				},
				html: getFormattedValue(fieldId)
			}),
			spanView = BX.create('span', {
				props:{
					className: "view_field"
				},
				children: [
					spanFormattedPrice
				]
			}),
			unitOfMesure = BX.create('span', {
				text: " "+getUnitOfMesure(fieldId)+" "
			}),
			apencil = BX.create('a', {
				props:{
					href: "javascript:void(0);"
				},
				children: [
					BX.create('span', {
						props:{
							className: "pencil"
						}
					})
				]
			}),
			containerDiv = BX.create('span', {
				props:{
					className: "edit_field_container"
				},
				children: [
					spanView,
					spanInput,
					unitOfMesure,
					apencil
				]
			});

		input.style.width = "40px";

		//on change value
		BX.bind(input, "change", function(e){
			_this.setFieldValue(fieldId, this.value);
		});

		//show pensil
		BX.bind(field, "mouseover", function(e){
			BX.addClass(field, "edit_field_hover");
		});

		//hide pensil
		BX.bind(field, "mouseout", function(e){
			BX.removeClass(field, "edit_field_hover");
		});

		//shown input
		BX.bind(field, "click", function(e){
			BX.addClass(field, "edit_enabled");
			input.focus();
		});

		//hide input
		BX.bind(input, "blur", function(e){
			BX.removeClass(field, "edit_enabled");
		});

		field = BX.cleanNode(field, false);
		field.appendChild(containerDiv);
	};

	var setFormulas = function(fieldId)
	{
		_this.formulas[fieldId] = _this.fields[fieldId]["formula"];
	};

	var getFormulas = function(fieldId)
	{
		var result = [];

		for(var i in _this.formulas) //fieldId's
		{
			if(!_this.formulas.hasOwnProperty(i))
				continue;

			var re = new RegExp("\\W*"+fieldId+"\\W*", 'i');

			if(_this.formulas[i].search(re) != -1)
				result.push(i);
		}

		return result;
	};

	var recountFormulasFieldsValues = function(fieldsIds)
	{
		for(var i in fieldsIds)
		{
			if(!fieldsIds.hasOwnProperty(i))
				continue;

			var formula = _this.formulas[fieldsIds[i]],
				re = new RegExp("[\\w_]+", "ig"),
				newFormula = formula.replace(re, function(fieldId){
					var result = _this.getFieldValue(fieldId);
					return result;
				});

			_this.setFieldValue(fieldsIds[i], eval(newFormula));
		}
	};

	var setField = function(fieldId, fieldParams)
	{
		var re = new RegExp("[^\\w_]+", "ig");

		if(fieldId.search(re) != -1)
			BX.debug("OrderBasketEditTotal:setField() wrong field id!");

		_this.fields[fieldId] = fieldParams;

		if(fieldParams.edit)
			setFieldEditable(fieldId);

		if(fieldParams.formula)
			setFormulas(fieldId);
	};

	for(var fieldId in params.fields)
		if(params.fields.hasOwnProperty(fieldId))
			setField(fieldId, params.fields[fieldId]);

	/* --- public methods --- */

	this.setFieldValue = function(fieldId, value)
	{
		if(typeof(_this.fields[fieldId]) != "undefined")
		{
			_this.fields[fieldId].value = value;

			if(typeof(_this.fields[fieldId]["type"]["formula"]) != "undefined")
			{
				var formulasFields = getFormulas(fieldId);

				if(formulasFields)
					recountFormulasFieldsValues(formulasFields);
			}

			if(_this.fields[fieldId]["type"] != "hidden")
				setFieldView(fieldId, value);
		}
		else
		{
			BX.debug("OrderBasketEditTotal:setFieldValue() unknown field id: \""+fieldId+"\"");
		}
	};

	this.getFieldValue= function(fieldId)
	{
		var result = false;

		if(typeof(this.fields[fieldId]) != "undefined")
			result = this.fields[fieldId].value;
		else
			BX.debug("OrderBasketEditTotal: unknown field id: \""+fieldId+"\"");

		return result;
	};
};


/*
 *
 * OrderBasketProductEditDialog
 *
 */

BX.Sale.Admin.OrderBasketProductEditDialog = function(basketObj)
{
	var dialog = null,
		basketCode = 0,
		propCount = 0,
		isNewProduct = true,
		basket = basketObj,
		usedBasketFields = ["CURRENCY", "PRODUCT_PROVIDER_CLASS", "NAME", "DETAIL_PAGE_URL", "WEIGHT",
			"CATALOG_XML_ID", "NOTES", "PRODUCT_XML_ID", "OFFER_ID", "PRICE", "QUANTITY", "PROPS", "MEASURE_CODE",
			"MEASURE_TEXT", "CUSTOM_PRICE", "BASKET_CODE"],
		_this = this;


	/* private methods */

	var setBasketCode = function(code)
	{
		if(!code)
			code = getFreeBasketOfferId();

		basketCode = code;
	};

	var getBasketCode = function()
	{
		return basketCode;
	};

	var getPropsTable = function()
	{
		var table = BX("BASKET_PROP_TABLE");

		if(!table)
			BX.debug("BX.Sale.Admin.OrderBasketProductEditDialog:addPropRow() can't find props table!");

		return table;
	};

	var createPropFieldName = function(fieldId, id)
	{
		return 'FORM_PROD_PROP_' + getBasketCode() + '_'+fieldId+'_' + id;
	};

	var getDialogField = function(fieldName)
	{
		return BX('FORM_PROD_BASKET_'+fieldName);
	};

	var getFreeBasketOfferId = function()
	{
		var i = 0;

		do
		{
			i++;
		}
		while(typeof (basket.products[('n'+i)]) != "undefined");

		return 'n'+i;
	};

	var getProductPropsFromDialog = function()
	{
		var result = [],
			props = ["NAME", "VALUE", "CODE", "SORT"];

		for(var i = 0; i < propCount; i++)
		{
			var property = {};

			for(var j=0; j< props.length; j++)
			{
				var fieldName = createPropFieldName(props[j], i),
					propField = BX(fieldName);

				if(propField && typeof (propField.value) != "undefined")
					property[props[j]] = propField.value;
			}

			result.unshift(property);
		}

		return result;
	};

	var setProps = function(product)
	{
		var props = getProductPropsFromDialog();
		product.PROPS = [];

		for(var i in props)
			if(props.hasOwnProperty(i))
				if(props[i].NAME)
					product.PROPS.push(props[i]);

		return product;
	};

	var setProductParams = function()
	{
		var	basketCode = getBasketCode(),
			dialogField,
			product = basket.products[basketCode] ? basket.products[basketCode] : {MODULE: "", OFFER_ID: 1, BASKET_CODE: basketCode},
			customedPrice = false;

		for(var i in usedBasketFields)
		{
			if(!usedBasketFields.hasOwnProperty(i))
				continue;

			if(dialogField = getDialogField(usedBasketFields[i]))
			{
				if(usedBasketFields[i] == "PRICE")
				{
					var productPrice = Math.round(parseFloat(product[usedBasketFields[i]])*10000),
						dialogPrice = Math.round(parseFloat(dialogField.value)*10000);

					if(productPrice != dialogPrice)
					{
						product["CUSTOM_PRICE"] = "Y";
						customedPrice = true;
						basket.customPrices[basketCode] = dialogField.value;

						if(!product.MODULE)
							product["BASE_PRICE"] = product["PRICE_BASE"] = dialogField.value;
					}
				}
				else if(usedBasketFields[i] == "CUSTOM_PRICE")
				{
					if(customedPrice)
						continue;
				}

				if(dialogField.value || (usedBasketFields[i] != 'OFFER_ID' && usedBasketFields[i] != 'BASKET_CODE'))
					product[usedBasketFields[i]] = dialogField.value;
			}
		}

		product['MANUALLY_EDITED'] = 'Y';

		if(product.BASKET_CODE != basketCode)
			BX.debug('setProductParams: product.BASKET_CODE != basketCode "'+product.BASKET_CODE+'" != "'+basketCode+'"');

		if(!product.BASKET_CODE || product.BASKET_CODE != basketCode)
			product.BASKET_CODE = basketCode;

		if(isNewProduct)
			product.OFFER_ID = parseInt(product.OFFER_ID) + basketCode;

		product = setProps(product);

		basket.productSet(product, !isNewProduct);

		BX.Sale.Admin.OrderAjaxer.sendRequest(
			BX.Sale.Admin.OrderEditPage.ajaxRequests.refreshOrderData({
				operation: "PRODUCT_ADD",
				productId: product.OFFER_ID
			})
		);
	};

	var getProps = function()
	{
		var product = basket.products[getBasketCode()];

		if(!product)
		{
			BX.debug("BX.Sale.Admin.OrderBasketProductEditDialog.getProps() can't find product with basket code: \""+getBasketCode()+"\"");
			return [];
		}

		return basket.products[getBasketCode()]["PROPS"];
	};

	var getProductParams = function()
	{
		var dialogField;
		for(var i in usedBasketFields)
		{
			if(!usedBasketFields.hasOwnProperty(i))
				continue;

			if(usedBasketFields[i] == "PROPS")
			{
				var props = getProps();

				for(var pi in props)
					if(props.hasOwnProperty(pi))
						_this.addPropRow(pi, props[pi]);
			}
			else if(dialogField = getDialogField(usedBasketFields[i]))
			{
				dialogField.value = basket.getProductFieldValue(getBasketCode(), usedBasketFields[i]);
			}
		}

		BX("FORM_PROD_BASKET_MEASURE_CODE").disabled = (basket.getProductFieldValue(getBasketCode(), "PRODUCT_PROVIDER_CLASS") != "");
	};

	var clearProductParams = function()
	{
		var dialogField;

		for(var i in usedBasketFields)
			if(usedBasketFields.hasOwnProperty(i))
				if(dialogField = getDialogField(usedBasketFields[i]))
					dialogField.value = "";

		var table = getPropsTable();

		for(i = table.rows.length-1; i > 1 ; i--)
			table.deleteRow(i);

		BX("FORM_PROD_BASKET_EMPTY_PROP_ROW").style.display = "";

	};

	var loadHtml = function()
	{
		var params = {
			action: "getProductEditDialogHtml",
			currency: BX.Sale.Admin.OrderEditPage.currencyLang,
			objName: basket.objName,
			callback: function(result)
			{
				if(result && result.DIALOG_CONTENT && !result.ERROR)
					createDialog(result.DIALOG_CONTENT);
				else if(result && result.ERROR)
					BX.debug("Error receiving dialog content: " + result.ERROR);
				else
					BX.debug("Error receiving dialog content!");
			}
		};

		BX.Sale.Admin.OrderAjaxer.sendRequest(params);
	};

	var createDialog = function(content)
	{
		dialog = new BX.CDialog({
				'content': content,
				'title': !isNewProduct ? BX.message("SALE_ORDER_BASKET_PROD_EDIT") : BX.message("SALE_ORDER_BASKET_PROD_CREATE"),
				'width': 820,
				'height': 470
//				'resizable': false
			});

		dialog.ClearButtons();
		dialog.SetButtons([
			{
				'title': BX.message("SALE_ORDER_BASKET_PROD_EDIT_ITEM_SAVE"),
				'id': 'save_custom_product',
				'action': function() {
					setProductParams();
					this.parentWindow.Close();
				}
			},
			BX.CDialog.prototype.btnCancel
		]);

		if(!isNewProduct)
			getProductParams();

		basket.productEditDialog.disableButton();
		dialog.Show();
		BX.Sale.Admin.OrderEditPage.unBlockForm();
	};

	/* public methods */

	this.show = function(basketCode)
	{
		propCount = 0;

		if(basketCode)
			isNewProduct = false;
		else
			isNewProduct = true;

		setBasketCode(basketCode);

		if(dialog == null)
		{
			dialog = loadHtml();
		}
		else
		{
			var title;

			if(!isNewProduct)
			{
				clearProductParams();
				getProductParams();
				title = BX.message("SALE_ORDER_BASKET_PROD_EDIT");
			}
			else
			{
				clearProductParams();
				title = BX.message("SALE_ORDER_BASKET_PROD_CREATE");
			}

			dialog.SetTitle(title);
			this.disableButton();

			dialog.Show();
			dialog.adjustSize();
		}
	};

	this.setMeasureText = function()
	{

		var code = BX('FORM_PROD_BASKET_MEASURE_CODE'),
			text = BX('FORM_PROD_BASKET_MEASURE_TEXT');

		for(var o = 0; o < code.options.length; o++)
		{
			if(code.options[o].selected != true)
				continue;

			text.value = code.options[o].innerHTML;
			break;
		}
	};

	this.disableButton = function()
	{
		var button = BX("save_custom_product");

		if(!button)
			return;

		if(this.checkRequiredFields())
			button.disabled = false;
		else
			button.disabled = true;
	};

	this.checkRequiredFields = function()
	{
		var name = BX("FORM_PROD_BASKET_NAME"),
			price = BX("FORM_PROD_BASKET_PRICE"),
			quantity = BX("FORM_PROD_BASKET_QUANTITY");

		if(quantity.value.length > 0 && /\D\./.test(quantity.value))
			quantity.value = parseFloat(quantity.value) || "0";

		if(price.value.length > 0 && /\D\./.test(price.value))
			price.value = parseFloat(price.value) || "0";

		if(name.value.length <= 0)
			return false;

		if(price.value.length <= 0)
			return false;

		if(quantity.value.length <= 0)
			return false;

		return true;
	};

	this.addPropRow = function(id, props)
	{
		var table = getPropsTable();

		if(!id)
			id = propCount;

		if(propCount <=0)
			BX("FORM_PROD_BASKET_EMPTY_PROP_ROW").style.display = "none";

		var sizes = { NAME: 20, VALUE: 20, CODE: 3, SORT: 2 };

		if(!props)
			props = { NAME: "", VALUE: "", CODE: "", SORT: "" };

		var row = table.insertRow(-1),
			cell = row.insertCell(-1),
			name = props["NAME"] ? BX.util.htmlspecialchars(props["NAME"]) : "",
			value = props["VALUE"] ? BX.util.htmlspecialchars(props["VALUE"]) : "",
			code = props["CODE"] ? BX.util.htmlspecialchars(props["CODE"]) : "",
			sort = props["SORT"] ? BX.util.htmlspecialchars(props["SORT"]) : 100;

		cell.innerHTML = '<input type="text" maxlength="250" size="'+sizes["NAME"]+'" name="'+createPropFieldName("NAME", id)+'" id="'+createPropFieldName("NAME", id)+'" value="'+name+'" />';
		cell = row.insertCell(-1);
		cell.innerHTML = '<input type="text" maxlength="250" size="'+sizes["VALUE"]+'" name="'+createPropFieldName("VALUE", id)+'" id="'+createPropFieldName("VALUE", id)+'" value="'+value+'" />';
		cell = row.insertCell(-1);
		cell.innerHTML = '<input type="text" maxlength="250" size="'+sizes["CODE"]+'" name="'+createPropFieldName("CODE", id)+'" id="'+createPropFieldName("CODE", id)+'" value="'+code+'" />';
		cell = row.insertCell(-1);
		cell.innerHTML = '<input type="text" maxlength="250" size="'+sizes["SORT"]+'" name="'+createPropFieldName("SORT", id)+'" id="'+createPropFieldName("SORT", id)+'" value="'+sort+'" />';

		propCount++;
	};
};

BX.Sale.Admin.OrderBasketCoupons =
{
	MODES_LIST: {
		CREATE: 0,
		EDIT: 1,
		VIEW: 2
	},

	statusCouponApplyed: null,
	mode: null,

	getCouponsContainerNode: function()
	{
		return BX("sale-admin-order-coupons-container");
	},

	onSetCoupon: function(e)
	{
		BX.Sale.Admin.OrderEditPage.setDiscountCheckbox(e);
		BX.Sale.Admin.OrderEditPage.refreshDiscounts();
	},

	onDeleteCoupon: function(coupon)
	{
		BX.Sale.Admin.OrderAjaxer.sendRequest(
			{
				action: "deleteCoupon",
				coupon: coupon,
				orderId: BX.Sale.Admin.OrderEditPage.orderId,
				userId: BX.Sale.Admin.OrderBuyer.getBuyerId(),
				callback: function(result){
					if(result && result.ERROR)
						BX.Sale.Admin.OrderEditPage.showDialog("Error: can't delete coupon");
				}
			},
			false,
			true
		);
	},

	onAddCoupons: function()
	{
		var coupons = BX("sale-admin-order-coupons");

		if(!coupons || !coupons.value)
			return;

		BX.Sale.Admin.OrderAjaxer.sendRequest(
			{
				action: "addCoupons",
				coupon: coupons.value,
				orderId: BX.Sale.Admin.OrderEditPage.orderId,
				userId: BX.Sale.Admin.OrderBuyer.getBuyerId(),
				callback: function(result){
					if(result && result.ERROR)
						BX.Sale.Admin.OrderEditPage.showDialog(BX.message('SALE_ORDER_BASKET_ADD_COUPON_ERROR'));
				}
			},
			false,
			true
		);

		coupons.value = "";
	},

	addCouponRow: function(coupon) //for order creation page
	{
		if(!coupon)
			return;

		var container = this.getCouponsContainerNode(),
			params;

		if(!container)
			return;

		params = {
			toUse: (coupon.JS_STATUS === 'APPLYED'),
			coupon: coupon.COUPON,
			title: coupon.JS_CHECK_CODE ? coupon.JS_CHECK_CODE : '',
			discountId: coupon.ORDER_DISCOUNT_ID,
			description: '['+coupon.COUPON+']'
		};

		if(coupon.DISCOUNT_NAME != "")
			params.description += " "+coupon.DISCOUNT_NAME;

		if (coupon.JS_STATUS === 'BAD')
			params.color = 'red';
		else if (coupon.JS_STATUS === 'APPLYED')
			params.color = 'green';
		else
			params.color = 'gray';

		if (coupon.DISCOUNT_SIZE)
			params.discountSize = coupon.DISCOUNT_SIZE;
		else
			params.discountSize = "0 %";

		if (coupon.APPLY)
			params.APPLY = coupon.APPLY;
		else
			params.APPLY = "N";

		params.NEW_COUPON = coupon.SAVED == 'N';

		container.appendChild(
			this.createCouponRowNode(params)
		);
	},

	clearCoupons: function()
	{
		var couponsContainer = this.getCouponsContainerNode();

		if(!couponsContainer)
			return;

		BX.cleanNode(couponsContainer, false);
	},

	createCouponRowNode: function(params)
	{
		var colorClass = "bx-bg-"+params.color,
			setCheckbox = (this.mode === this.MODES_LIST.EDIT),
			setRemover = (this.mode === this.MODES_LIST.CREATE || (this.mode === this.MODES_LIST.EDIT && params.NEW_COUPON)),
			discountSize =  params.discountSize ? params.discountSize : "",
			description = params.description ? BX.util.htmlspecialchars(params.description) : "",
			coupon = params.coupon ? BX.util.htmlspecialchars(params.coupon) : "",
			toUse = params.toUse,
			apply = params.APPLY,
			discountId = params.discountId,
			title = '';

			if(params.title)
				title =	BX.message("SALE_ORDER_BASKET_COUPON_STATUS")+": "+BX.util.htmlspecialchars(params.title);

			return BX.create('li',{
				props: {
					className: "bx-adm-pc-sale-item "+colorClass
				},
				html: (setCheckbox ? '<input type="hidden" value="N" name="DISCOUNTS[COUPON_LIST]['+coupon+']">'+
					'<input type="checkbox" data-discount-id="'+discountId+'" data-coupon="Y" data-discount-coupon="'+coupon+'" class="bx-adm-pc-input-checkbox" name="DISCOUNTS[COUPON_LIST]['+coupon+']" value="Y" onclick="BX.Sale.Admin.OrderBasketCoupons.onSetCoupon(event);"'+(apply == "Y" ? ' checked' : '')+' title="'+BX.message("SALE_ORDER_BASKET_COUPON_APPLY")+'">' : '')+
					'<div class="bx-adm-pc-sale-item-block'+(setCheckbox ? "" : " bx-amd-l0")+(setRemover ? "" : " bx-adm-r0")+'" title="'+title+'">'+
					'<div class="bx-adm-pc-sale-overname"><div class="bx-adm-pc-sale-cost">'+(discountSize.length > 0 ? discountSize : '0')+'</div>'+description+'</div>'+
					'</div>'+
				(setRemover ? '<div class="bx-adm-pc-sale-item-remover" onclick="BX.Sale.Admin.OrderBasketCoupons.onDeleteCoupon(\''+coupon+'\');"  title="'+BX.message("SALE_ORDER_BASKET_COUPON_DELETE")+'"></div>' : '')
		});
	},

	setCoupons: function(coupons)
	{
		this.clearCoupons();

		if(!coupons)
			return;

		for(var i in coupons)
			if(coupons.hasOwnProperty(i))
				this.addCouponRow(coupons[i]);
	}
};