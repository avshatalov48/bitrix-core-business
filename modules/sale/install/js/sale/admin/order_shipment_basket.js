/**
 * Class BX.Sale.Admin.OrderBasket
 */


BX.Sale.Admin.ShipmentBasket = function (params)
{
	this.products = params.products;
	this.useStoreControl = params.useStoreControl || false;
	BX.Sale.Admin.OrderBasketEdit.apply(this, arguments);

	if (Object.keys(this.products).length == 0)
	{
		var tbl = BX(this.tableId);
		tbl.appendChild(this.createEmptyFooter());
	}
};

BX.Sale.Admin.ShipmentBasket.prototype = Object.create(BX.Sale.Admin.OrderBasketEdit.prototype);

BX.Sale.Admin.ShipmentBasket.prototype.getProductBasketCode = function(product)
{
	return product.BASKET_ID;
};

BX.Sale.Admin.ShipmentBasket.prototype.createEmptyFooter = function()
{
	var message = BX.message('SALE_ORDER_SHIPMENT_VIEW_BASKET_NO_PRODUCTS');
	var count = Object.keys(this.visibleColumns).length + 1;

	var tBody = BX.create('tbody', {
		props : {
			id : this.idPrefix+'_empty_footer'
		}
	});
	var tr = BX.create('tr');

	var td = BX.create('td', {
		text : message
	});

	td.style.textAlign = 'center';
	td.style.fontSize = '1.4em';
	td.colSpan = count;
	tr.appendChild(td);
	tBody.appendChild(tr);

	return tBody;
};

BX.Sale.Admin.ShipmentBasket.prototype.createProductRow = function(basketCode, product)
{
	var	cellContent,
		updaters = {},
		_this = this,
		tBody = BX.create(
			'tbody',
			{
				props:
				{
					"id": this.createProductRowId(basketCode, product)
				},
				"style" :
				{
					'textAlign' : "left",
					'borderBottom' : "1px solid #DDD"
				}
			}
		),
		tr = BX.create('tr',
			{
				style : {
					'vertical-align': 'top'
				}
			}
		);

	if(product.IS_SET_ITEM != "Y")
	{
		tBody.setAttribute("data-basket-code", basketCode);

		if(product.IS_SET_PARENT == 'Y' && product.OLD_PARENT_ID)
			tBody.setAttribute("data-old-parent-id-parent", product.OLD_PARENT_ID);
	}
	else
	{
		BX.addClass(tBody, "bundle-child-"+product.OLD_PARENT_ID);
		BX.addClass(tBody, "basket-bundle-child-hidden");
		BX.addClass(tBody, "bundle-child");
	}

	tr.setAttribute('data-index', 1);

	for(var fieldId in this.visibleColumns)
	{
		if (!this.visibleColumns.hasOwnProperty(fieldId))
			continue;
		cellContent = this.createProductCell(basketCode, product, fieldId, 1);
		if(cellContent)
			tr.appendChild(cellContent);
	}

	tBody.appendChild(tr);
	return tBody;
};

BX.Sale.Admin.ShipmentBasket.prototype.createSkuPropsTable = BX.Sale.Admin.OrderBasket.prototype.createSkuPropsTable;

BX.Sale.Admin.ShipmentBasket.prototype.createProductCell = function(basketCode, product, fieldId, index)
{
	var result = null,
		cellNodes = [],
		_this = this,
		fieldValue = product[fieldId],
		tdClass = "",
		i = null,
		storeId = 0,
		span = null;

	switch(fieldId)
	{
		case "NUMBER":

			cellNodes.push(
				BX.create(
					'span',
					{
						props: {
							id: this.idPrefix + "sale_order_product_" + basketCode + "_number"
						},
						text: this.index
					}
				)
			);
			break;

		case "NAME":
			var name;
			if (product.IS_SET_PARENT == "Y" && product.SET_ITEMS)
			{
				var bundleShow = BX.create('a', {
					props: {
						href: "javascript:void(0);",
						className: "dashed-link show-set-link"
					},
					html: BX.message("SALE_ORDER_BASKET_EXPAND")
				});

				BX.bind(bundleShow, "click", function (e)
				{
					var source = e.target || e.srcElement;
					_this.onToggleBundleChildren(product.OLD_PARENT_ID, source);
				});

				cellNodes.push(
					BX.create('div', {
						children: [bundleShow]
					})
				);
			}

			if (product.EDIT_PAGE_URL)
				name = BX.create('a', {props: {href: product.EDIT_PAGE_URL, target: "_blank"}, text: fieldValue});
			else
				name = BX.create('span', {text: fieldValue});

			cellNodes.push(name);
			break;

		case "QUANTITY"	:
			if (!!product.MEASURE_TEXT)
				cellNodes.push(document.createTextNode(" "+product.MEASURE_TEXT+" "));

            cellNodes.push(BX.create('span',{
                props:{},
                text: product.QUANTITY
            }));

			cellNodes.push(BX.create('input', {
				props : {
					id: basketCode+'_quantity',
					type : 'hidden',
					value: product.QUANTITY
				}
		    }));

			break;

		case "AVAILABLE":
			cellNodes.push(BX.create('span', {text:fieldValue}));
			break;

		case "PRICE":
			cellNodes.push(
				BX.create('span', {
					text: " "+BX.Sale.Admin.OrderEditPage.currencyLang+" "
				})
			);

			var inputP = BX.create('input', {
				props:{
					type: "text",
					name: this.getFieldName(basketCode, "PRICE"),
					value: product.PRICE
				}
			});

			inputP.style.width = "40px";
			cellNodes.push(inputP);
			break;

		case "SUM":
			cellNodes.push(
				BX.create('strong', {
					props:{
						id: this.idPrefix+"sale_order_edit_product_"+basketCode+"_summ"
					},
					text: BX.Sale.Admin.OrderEditPage.currencyFormat(product.PRICE*product.QUANTITY)
				})
			);
			break;

		case "IMAGE":
			cellNodes.push(this.createFieldImage(basketCode, product, fieldId));
			tdClass = "adm-s-order-table-ddi-table-img";
			break;

		case "STORE":
			if (!!product.BARCODE_INFO && Object.keys(product.BARCODE_INFO).length > 0)
			{
				for (storeId in product.BARCODE_INFO)
				{
					if (!product.BARCODE_INFO.hasOwnProperty(storeId))
						continue;
					for (i in product.STORES)
					{
						if (!product.STORES.hasOwnProperty(i))
							continue;
						if (storeId == product.STORES[i].STORE_ID)
						{
							cellNodes.push(BX.create('br'));
							cellNodes.push(BX.create('span', {text : product.STORES[i].STORE_NAME}));
							break;
						}
					}
				}
			}
			else
			{
				cellNodes.push(BX.create('span'));
			}
			cellNodes.push(BX.create('span'));
			break;

		case "AMOUNT":
			if (!!product.MEASURE_TEXT)
			{
				cellNodes.push(
					BX.create('span',
						{
							text: product.AMOUNT + " " + product.MEASURE_TEXT + " "
						}
					)
				);
			}
			else
			{
				cellNodes.push(BX.create('span', {'text' : product.AMOUNT}));
			}

			break;

		case "CUR_AMOUNT":
			if (!!product.BARCODE_INFO && Object.keys(product.BARCODE_INFO).length > 0)
			{
				for (storeId in product.BARCODE_INFO)
				{
					if (!product.BARCODE_INFO.hasOwnProperty(storeId))
						continue;
					var quantity = 0;
					for (i in product.BARCODE_INFO[storeId])
					{
						if (product.BARCODE_INFO[storeId].hasOwnProperty(i))
							quantity += parseFloat(product.BARCODE_INFO[storeId][i].QUANTITY);
					}

					var measureText = (!!product.MEASURE_TEXT) ? product.MEASURE_TEXT : '';
					cellNodes.push(BX.create('br'));
					cellNodes.push(
						BX.create('span', {
							text : quantity+' '+measureText
						})
					);
				}
			}
			else
			{
				cellNodes.push(BX.create('span'));
			}
			break;
		case "REMAINING_QUANTITY":
			if (!!product.MEASURE_TEXT)
			{
				cellNodes.push(
					BX.create('span',
						{
							text: " " + product.MEASURE_TEXT + " "
						}
					)
				);
			}

			span = BX.create('span', {
				props: {
					id: basketCode + '_store_remaining_quantity_' + index,
					className : basketCode + '_store_remaining_quantity'
				},
				'text': (product.STORES.length > 0 && product.STORES[0].hasOwnProperty('AMOUNT')) ? product.STORES[0].AMOUNT : '0'
			});
			cellNodes.push(span);
			break;

		case "BARCODE":
			if (!!product.BARCODE_INFO && Object.keys(product.BARCODE_INFO).length > 0
			&& (this.useStoreControl || this.isProductSupportedMarkingCode(product)))
			{
				var count = Object.keys(product.BARCODE_INFO).length;

				for(var c = 1; c <= count; c++)
				{
					cellNodes.push(this.createBlockBarcode(basketCode, product, c));
				}
			}
			else
			{
				cellNodes.push(BX.create('span'));
			}
			break;

		case "PROPS":
			var node = this.createFieldSkuProps(basketCode, product, fieldId);

			if(node)
				cellNodes.push(node);
			else
				cellNodes.push(BX.create('span'));
			break;
	}

	if(fieldId.indexOf("PROPERTY_") === 0)
	{
		var html;

		if(product.PRODUCT_PROPS_VALUES && product.PRODUCT_PROPS_VALUES[fieldId+"_VALUE"])
			html = product.PRODUCT_PROPS_VALUES[fieldId+"_VALUE"];
		else
			html = "";

		cellNodes.push(BX.create('span', {html: html}));
	}

	if(cellNodes.length > 0)
	{
		result = BX.create('td');
		BX.addClass(result, fieldId);
		switch (fieldId)
		{
			case 'IMAGE' :
				result.style.textAlign = 'center';
				break;
			case 'NUMBER' :
				result.style.textAlign = 'center';
				result.style.width = '30px';
				break;
			case 'NAME' :
				result.style.textAlign = 'left';
				break;
			case 'QUANTITY' :
			case 'REMAINING_QUANTITY' :
			case 'CUR_AMOUNT' :
			case 'AMOUNT' :
				result.style.textAlign = 'right';
				break;
			case 'BARCODE' :
			case 'STORE' :
				result.style.paddingLeft = '20px';
		}

		if (tdClass)
			BX.addClass(result, tdClass);

		while(cellNodes.length > 0)
		{
			var el = cellNodes.pop();
			if (!!el)
				result.appendChild(el);
		}
	}

	return result;
};

BX.Sale.Admin.ShipmentBasket.prototype.isProductSupportedMarkingCode = function(product)
{
	return 	product && product.IS_SUPPORTED_MARKING_CODE && product.IS_SUPPORTED_MARKING_CODE === 'Y';
};

BX.Sale.Admin.ShipmentBasket.prototype.createBlockBarcode = function(basketCode, product, index)
{
	if (!product.IS_SET_PARENT || product.IS_SET_PARENT !== 'Y')
	{
		var type = parseInt(product.QUANTITY) === 1 ? BX.Sale.Admin.Order.ShipmentBasketBarcodeView.TYPE_INPUT : BX.Sale.Admin.Order.ShipmentBasketBarcodeView.TYPE_LINK;

		var barcode = new BX.Sale.Admin.Order.ShipmentBasketBarcodeView({
			basketId: basketCode,
			product: product,
			index: index,
			readonly: true,
			type: type,
			orderId: this.orderId,
			useStoreControl: this.useStoreControl
		});

		return barcode.render();
	}
	else
	{
		return BX.create('span');
	}
};

BX.Sale.Admin.ShipmentBasket.prototype.createFieldImage = function(basketCode, product, fieldId)
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

	resultNode = BX.create('span',{
		children: [pictureNode]
	});

	return resultNode;
};
/**
 * Class BX.Sale.Admin.ShipmentBasketEdit
 */

BX.Sale.Admin.ShipmentBasketEdit = function(params)
{
	this.link = params.link; // for connection between shipment basket and system basket
	this.shipment = null;
	this.products = params.products;
	this.isShipped = !!params.isShipped;
	this.useStoreControl = params.useStoreControl || false;
	this.orderId = params.orderId || 0;
	BX.Sale.Admin.OrderBasketEdit.apply(this, arguments);
	this.index = 0;
	this.basket = null;
	this.productsOrder = params.productsOrder;
	this.updateShipmentTimer = null;

	this.removeEmptyFooter(this.idPrefix);

	if (Object.keys(this.products).length == 0)
	{
		var tbl = BX(this.tableId);
		tbl.appendChild(this.createEmptyFooter());
	}

	this.initGroupActions();
};

BX.Sale.Admin.ShipmentBasketEdit.prototype = Object.create(BX.Sale.Admin.OrderBasketEdit.prototype);

BX.Sale.Admin.ShipmentBasketEdit.prototype.createProductCell = function(basketCode, product, fieldId, index)
{
	var result = null,
		cellNodes = [],
		_this = this,
		fieldValue = product[fieldId],
		tdClass = "",
		stack = [],
		i = null,
		showStoreInfo =  this.useStoreControl && !!product.STORES && product.STORES.length > 0;

	switch(fieldId)
	{
		case "NUMBER":
			if ((product.IS_SET_PARENT == 'Y' && product.SET_ITEMS > 0)
				||
				(product.IS_SET_PARENT && product.IS_SET_ITEM == 'N')
				||
				(!product.MODULE))
			{
				cellNodes.push(
					BX.create(
						'span',
						{
							props: {
								id: this.idPrefix + "sale_order_product_" + basketCode + "_number"
							},
							text: this.index
						}
					)
				);
			}
			else
			{
				cellNodes.push(BX.create('span'));
			}
			break;

		case "NAME":
			var name;
			if (product.IS_SET_PARENT == "Y" && product.SET_ITEMS)
			{
				var bundleShow = BX.create('a', {
					props: {
						href: "javascript:void(0);",
						className: "dashed-link show-set-link"
					},
					html: BX.message("SALE_ORDER_BASKET_EXPAND")
				});

				BX.bind(bundleShow, "click", function (e)
				{
					var source = e.target || e.srcElement;
					_this.onToggleBundleChildren(product.OLD_PARENT_ID, source);
				});

				cellNodes.push(
					BX.create('div', {
						children: [bundleShow]
					})
				);
			}

			if (product.EDIT_PAGE_URL)
				name = BX.create('a', {props: {href: product.EDIT_PAGE_URL, target: "_blank"}, text: fieldValue});
			else
				name = BX.create('span', {text: fieldValue});

			cellNodes.push(name);
			break;

		case "QUANTITY":
			if (!!product.MEASURE_TEXT)
				cellNodes.push(document.createTextNode(" " + product.MEASURE_TEXT + " "));

			cellNodes.push(BX.create('span', {
				props: {},
				text: product.QUANTITY
			}));

			cellNodes.push(BX.create('input', {
				props: {
					id: basketCode + '_quantity',
					name: this.getFieldName(basketCode, 'QUANTITY'),
					type: 'hidden',
					value: product.QUANTITY
				}
			}));

			break;

		case "AVAILABLE":
			cellNodes.push(BX.create('span', {text: fieldValue}));
			break;
		case "IMAGE":
			cellNodes.push(this.createFieldImage(basketCode, product, fieldId));
			tdClass = "adm-s-order-table-ddi-table-img";
			break;
		case "AMOUNT":
			cellNodes = this.createFieldAmount(cellNodes, product);
			break;
		case "STORE":
			if (product.IS_SET_PARENT != 'Y' && showStoreInfo)
			{
				stack = [this.createBlockStore(basketCode, product, index)];
				if (!!product.BARCODE_INFO && Object.keys(product.BARCODE_INFO).length > 0)
					stack = this.recoveryDeliveryStore(product, stack[0]);

				if (this.isShipped)
				{
					for (i in stack)
					{
						if (!stack.hasOwnProperty(i))
							continue;
						var obStore = BX.findChild(stack[i], {tag: 'select'}, true);
						obStore.parentNode.appendChild(
							BX.create('input', {
								props : {
									type: 'hidden',
									name : obStore.getAttribute('name'),
									value : obStore.options[obStore.selectedIndex].value
								}
							})
						);
					}
				}

				if (product.STORES && parseInt(product.QUANTITY) > 1)
				{
					var spanAddStore = BX.create('span', {
						props: {
							className: 'adm-bus-shipment-basket-store-add'
						},
						text: BX.message('SALE_ORDER_SHIPMENT_BASKET_ADD_NEW_STORE')
					});

					var countStores = product.STORES.length;
					if (countStores < 2 || stack.length == countStores)
						BX.hide(spanAddStore);

					BX.bind(spanAddStore, "click", BX.proxy(function ()
					{
						if (this.isShipped)
						{
							BX.Sale.Admin.OrderEditPage.showDialog(BX.message('SALE_ORDER_SHIPMENT_BASKET_ERROR_ALREADY_SHIPPED'));
							return;
						}
						var tr = BX.findParent(spanAddStore, {tag: 'tr'}, true);

						var storeBlock = BX.findChildByClassName(tr, 'STORE');
						var children = BX.findChildren(storeBlock, {tag: 'div'});
						var index = children.length + 1;
						for (var i in children)
						{
							if (!children.hasOwnProperty(i))
								continue;
							var chIndex = children[i].getAttribute('data-index');
							if (chIndex == index)
								index++;
						}

						var newStore = this.createBlockStore(this.getProductBasketCode(product), product, index);
						storeBlock.insertBefore(newStore, spanAddStore);

						var curAmountBlock = BX.findChildByClassName(tr, 'CUR_AMOUNT');
						var newCurAmount = this.createBlockCurAmount(this.getProductBasketCode(product), product, index);
						curAmountBlock.appendChild(newCurAmount);

						var remainingQuantity = BX.findChildByClassName(tr, 'REMAINING_QUANTITY');
						var newRemainingQuantity = this.createBlockRemainingQuantity(this.getProductBasketCode(product), product, index);
						remainingQuantity.appendChild(newRemainingQuantity);

						var barcode = BX.findChildByClassName(tr, 'BARCODE');
						var newBarcode = this.createBlockBarcode(this.getProductBasketCode(product), product, index);
						barcode.appendChild(newBarcode);

						if (product['STORES'].length < index + 1)
							BX.hide(spanAddStore);

					}, this));

					stack.push(spanAddStore);
				}
				for (i in stack)
				{
					if (stack.hasOwnProperty(i))
						cellNodes.unshift(stack[i]);
				}
			}
			else
			{
				cellNodes.push(BX.create('span'));
			}
			break;
		case "CUR_AMOUNT":
			if (product.IS_SET_PARENT != 'Y' && showStoreInfo)
			{
				stack = [this.createBlockCurAmount(basketCode, product, index)];
				if (!!product.BARCODE_INFO && Object.keys(product.BARCODE_INFO).length > 0)
					stack = this.recoveryDeliveryCurAmount(product, stack[0]);

				for (i in stack)
				{
					if (stack.hasOwnProperty(i))
						cellNodes.unshift(stack[i]);
				}
			}
			else
			{
				cellNodes.push(BX.create('span'));
			}
			break;
		case "REMAINING_QUANTITY":
			if (product.IS_SET_PARENT != 'Y' && product.MODULE && showStoreInfo)
			{
				stack = [this.createBlockRemainingQuantity(basketCode, product, index)];
				if (!!product.BARCODE_INFO && Object.keys(product.BARCODE_INFO).length > 0)
					stack = this.recoveryRemainingQuantity(product, stack[0]);

				for (i in stack)
				{
					if (stack.hasOwnProperty(i))
						cellNodes.unshift(stack[i]);
				}
			}
			else
			{
				cellNodes.push(BX.create('span'));
			}
			break;
		case "BARCODE":
			if (product.IS_SET_PARENT != 'Y' && (showStoreInfo || this.isProductSupportedMarkingCode(product)))
			{
				var count = 1;

				if (!!product.BARCODE_INFO && Object.keys(product.BARCODE_INFO).length > 1)
				{
					count = Object.keys(product.BARCODE_INFO).length;
				}

				for(var c = count; c > 0; c--)
				{
					cellNodes.push(this.createBlockBarcode(basketCode, product, c));
				}
			}
			else
			{
				cellNodes.push(BX.create('span'));
			}
			break;
		case "PROPS":
			var node = this.createFieldSkuProps(basketCode, product, fieldId);

			if(node)
				cellNodes.push(node);
			else
				cellNodes.push(BX.create('span'));
			break;
	}

	if(fieldId.indexOf("PROPERTY_") === 0)
	{
		if(product.PRODUCT_PROPS_VALUES && product.PRODUCT_PROPS_VALUES[fieldId+"_VALUE"])
			cellNodes.push(BX.create('span', {html:product.PRODUCT_PROPS_VALUES[fieldId+"_VALUE"]}));
	}

	if(cellNodes.length > 0)
	{
		result = BX.create('td');
		BX.addClass(result, fieldId);
		switch (fieldId)
		{
			case "NUMBER" :
				result.style.width = '45px';
				result.style.textAlign = 'center';
				break;
			case 'NAME' :
			case 'STORE' :
				result.style.textAlign = 'left';
				break;
			case 'QUANTITY' :
			case 'REMAINING_QUANTITY' :
			case 'AMOUNT' :
			case 'CUR_AMOUNT' :
				result.style.textAlign = 'right';
				break;
			case 'IMAGE' :
			case 'BARCODE' :
				result.style.textAlign = 'center';
				break;
		}

		if (tdClass)
			BX.addClass(result, tdClass);

		while(cellNodes.length > 0)
		{
			var el = cellNodes.pop();
			if (!!el)
				result.appendChild(el);
		}
	}

	return result;
};

BX.Sale.Admin.ShipmentBasketEdit.prototype.isProductSupportedMarkingCode = function(product)
{
	return 	product && product.IS_SUPPORTED_MARKING_CODE && product.IS_SUPPORTED_MARKING_CODE === 'Y';
};

BX.Sale.Admin.ShipmentBasketEdit.prototype.createFieldAmount = function(container, product)
{
	var basketCode = this.getProductBasketCode(product);

	if (!!product.MEASURE_TEXT)
	{
		container.push(
			BX.create('span',
				{
					text: " " + product.MEASURE_TEXT + " "
				}
			)
		);
	}

	var input = BX.create('input',
		{
			props: {
				type: "text",
				name: this.getFieldName(basketCode, "AMOUNT"),
				value: product.AMOUNT,
				id: basketCode + '_amount',
				autocomplete : 'off'
			},
			attrs : {
				readOnly : this.isShipped
			},
			style: {
				'width': '25px'
			}
		});

	BX.bind(input, 'keydown', function(e){
		if(!e) e = window.event;
		if(!e) return;
		if(e.keyCode == 13) input.blur();
	});

	if (product.IS_SET_ITEM == 'Y')
		input.readOnly = true;

	BX.bind(input, 'change', BX.proxy(function ()
	{
		if (product.IS_SET_PARENT == 'Y')
		{
			var setItems = product.SET_ITEMS;
			for (var i in setItems)
			{
				if (!setItems.hasOwnProperty(i))
					continue;
				var setItemBasketCode = this.getProductBasketCode(setItems[i]);
				var obAmount = BX(setItemBasketCode+'_'+'amount');
				obAmount.value = product.BASE_ELEMENTS_QUANTITY[setItems[i].OFFER_ID]*input.value;
			}
		}
		var tr = BX.findParent(input, {tag: 'tr'}, true);
		var curAmount = BX.findChildrenByClassName(tr, this.getProductBasketCode(product)+'_cur_amount', true);

		if (!!product.STORES && product.STORES.length == 1)
			curAmount[0].value = input.value;

		this.updateShipment();
	}, this));

	container.push(input);

	return container;
};

BX.Sale.Admin.ShipmentBasketEdit.prototype.recoveryDeliveryStore = function(product, element)
{
	var stack = [element];
	var index = 1;

	for (var storeId in product.BARCODE_INFO)
	{
		if (!product.BARCODE_INFO.hasOwnProperty(storeId))
			continue;

		var stackElement = stack[stack.length-1];
		var obStore = BX.findChild(stackElement, {tag: 'select'}, true);
		if (obStore)
		{
			for (var k in obStore.options)
			{
				if (!obStore.options.hasOwnProperty(k))
					continue;

				var option = obStore.options[k];
				if (option.value == storeId)
				{
					obStore.options[k].selected = true;
					break;
				}
			}
			if (Object.keys(product.BARCODE_INFO).length > index)
			{
				var el = this.createBlockStore(this.getProductBasketCode(product), product, ++index);
				stack.push(el);
			}
		}
	}
	return stack;
};

BX.Sale.Admin.ShipmentBasketEdit.prototype.recoveryDeliveryCurAmount = function(product, element)
{
	var stack = [element];
	var index = 1;
	var basketCode = this.getProductBasketCode(product);

	for (var storeId in product.BARCODE_INFO)
	{
		if (!product.BARCODE_INFO.hasOwnProperty(storeId))
			continue;

		var stackElement = stack[stack.length-1];
		var barcodeInfo = product['BARCODE_INFO'][storeId];
		for (var i in barcodeInfo)
		{
			if (!barcodeInfo.hasOwnProperty(i))
				continue;

			var obAmount = BX.findChildByClassName(stackElement, basketCode + '_cur_amount', true);


				if (product.BARCODE_MULTI === 'N' && product.IS_SUPPORTED_MARKING_CODE === 'N')
				{
					obAmount.value = barcodeInfo[i].QUANTITY;
				}
				else
				{
					if (i == 0)
					{
						obAmount.value = 0;
					}

					obAmount.value = parseFloat(obAmount.value) + parseFloat(barcodeInfo[i].QUANTITY);
				}

		}
		if (Object.keys(product.BARCODE_INFO).length > index)
		{
			var el = this.createBlockCurAmount(this.getProductBasketCode(product), product, ++index);
			stack.push(el);
		}
	}
	return stack;
};

BX.Sale.Admin.ShipmentBasketEdit.prototype.recoveryRemainingQuantity = function(product, element)
{
	var stack = [element];
	var index = 1;
	var basketCode = this.getProductBasketCode(product);

	for (var storeId in product.BARCODE_INFO)
	{
		if (!product.BARCODE_INFO.hasOwnProperty(storeId))
			continue;

		var stackElement = stack[stack.length-1];

		var obRemainingQuantity = BX.findChildByClassName(stackElement, basketCode + '_store_remaining_quantity');
		for (var i in product['STORES'])
		{
			if (!product['STORES'].hasOwnProperty(i))
				continue;

			if (product['STORES'][i].STORE_ID == storeId)
			{
				obRemainingQuantity.innerHTML = product['STORES'][i].AMOUNT;
				break;
			}
		}
		if (Object.keys(product.BARCODE_INFO).length > index)
			stack.push(this.createBlockRemainingQuantity(this.getProductBasketCode(product), product, ++index));
	}
	return stack;
};

BX.Sale.Admin.ShipmentBasketEdit.prototype.createEmptyFooter = function()
{
	var message = BX.message('SALE_ORDER_SHIPMENT_BASKET_NO_PRODUCTS');
	var count = Object.keys(this.visibleColumns).length + 2; // 2 - columns checkbox, menu

	var tBody = BX.create('tbody', {
		props : {
			id : this.idPrefix+'_empty_footer'
		},
		children : [
			BX.create('tr',
			{
				children : [
					BX.create('td', {
						text : message,
						attrs : {
							'colspan' : count
						},
						style : {
							'text-align' : 'center',
							'font-size' : '1.4em'
						}
					})
				]
			})
		]
	});

	return tBody;
};

BX.Sale.Admin.ShipmentBasketEdit.prototype.moveToSystemBasket = function (basketCode)
{
	if (this.isShipped)
	{
		BX.Sale.Admin.OrderEditPage.showDialog(BX.message('SALE_ORDER_SHIPMENT_BASKET_ERROR_ALREADY_SHIPPED'));
		return;
	}

	var productRowParent = null;

	this.link.products[basketCode] = this.products[basketCode];
	delete this.products[basketCode];

	var productRaw = BX(this.idPrefix+"sale-order-basket-product-"+basketCode);

	if(productRaw)
	{
		productRowParent = productRaw.parentNode;
		if (productRowParent)
			productRowParent.removeChild(productRaw);

	}

	//set
	var oldParentId = productRaw.getAttribute('data-old-parent-id-parent');
	if(oldParentId)
	{
		var  bundleChildren = BX.findChildrenByClassName(productRowParent, "bundle-child-"+oldParentId, false);

		for(var i in bundleChildren)
		{
			if (!bundleChildren.hasOwnProperty(i))
				continue;

			bundleChildren[i].parentNode.removeChild(bundleChildren[i]);
		}
	}

	this.removeEmptyFooter(this.idPrefix);

	if (Object.keys(this.products).length == 0)
	{
		var tbl = BX(this.tableId);
		tbl.appendChild(this.createEmptyFooter());
	}

	this.setRowNumbers();
	this.updateCountBasketItems();

	this.updateShipment();
};


BX.Sale.Admin.ShipmentBasketEdit.prototype.updateShipment = function()
{
	var _this = this;
	if (this.updateShipmentTimer)
		clearTimeout(this.updateShipmentTimer);

	this.updateShipmentTimer = setTimeout(BX.proxy(function(){_this.shipment.updateDeliveryInfo();}, this), 2000);

};

BX.Sale.Admin.ShipmentBasketEdit.prototype.removeEmptyFooter = function (prefix)
{
	var empty_footer = BX(prefix+'_empty_footer');
	if (empty_footer)
		BX.remove(empty_footer);
};

BX.Sale.Admin.ShipmentBasketEdit.prototype.productSet = function(product, isReplaceExisting)
{
	var table = BX(this.tableId);
	var basketCode = this.getProductBasketCode(product);

	table.appendChild(this.productAdd(product));

	if(product.IS_SET_ITEM != "Y")
		this.setProductsCount(++this.productsCount);

	//sets
	if(product.SET_ITEMS && product.SET_ITEMS.length)
		for(var i = product.SET_ITEMS.length-1; i>=0; i--)
			this.productSet(product.SET_ITEMS[i], true);

	this.setRowNumbers();
	this.updateCountBasketItems();
};

BX.Sale.Admin.ShipmentBasketEdit.prototype.productAdd = function(product)
{
	var basketCode = this.getProductBasketCode(product);
	var productRow = BX(this.createProductRowId(basketCode, product));

	if (productRow)
		BX.remove(productRow);

	return this.createProductRow(basketCode, product);
};

BX.Sale.Admin.ShipmentBasketEdit.prototype.createDivWrapper = function(product, columnCode, index)
{
	var className = this.getDivWrapperClassName(product, columnCode, index);
	var divWrapper = BX.create('div',
		{
			props : {
				'className' : className
			},
			style : {
				'height': '29px',
				'white-space': 'nowrap'
			}
		});
	divWrapper.setAttribute('data-index', index);
	if (index > 1)
		divWrapper.style.marginTop = '5px';

	return divWrapper;
};

BX.Sale.Admin.ShipmentBasketEdit.prototype.getDivWrapperClassName = function(product, columnCode, index)
{
	var basketCode = this.getProductBasketCode(product);

	var className = columnCode+'-'+basketCode+'-'+index;
	if (product.OLD_PARENT_ID)
		className += '-'+product.OLD_PARENT_ID;

	return className;
};

BX.Sale.Admin.ShipmentBasketEdit.prototype.createBlockStore = function(basketCode, product, index)
{
	if (!!product.STORES && product.STORES.length > 0)
	{
		var divWrapper = this.createDivWrapper(product, 'store', index);
		BX.addClass(divWrapper, 'barcode_record');

		var selectStore = BX.create('select', {
			props: {
				name: this.getFieldName(basketCode, "BARCODE_INFO")+'[' + index + '][STORE_ID]',
				className: 'store_select, ' + basketCode + '_select_store_' + index
			},
			attrs : {
				disabled : this.isShipped
			},
			style : {
				'width' : '175px'
			}
		});

		for (var i in product.STORES)
		{
			if (!product.STORES.hasOwnProperty(i))
				continue;

			var option = BX.create('option', {
				'props': {
					'value': product.STORES[i].STORE_ID
				},
				'text': product.STORES[i].STORE_NAME
			});

			selectStore.appendChild(option);
		}

		BX.bind(selectStore, 'change', BX.proxy(function ()
		{
			var storeId = selectStore.options[selectStore.selectedIndex].value;
			var currStoreQuantity = BX(basketCode + '_store_remaining_quantity_' + index);

			for (var i in product.STORES)
			{
				if (!product.STORES.hasOwnProperty(i))
					continue;

				if (product.STORES[i].STORE_ID == storeId)
					currStoreQuantity.innerHTML = product.STORES[i].AMOUNT;
			}
		}, this));

		divWrapper.appendChild(selectStore);

		if (Object.keys(product.STORES).length > 1 && !this.isShipped)
		{
			var delDiv = BX.create('div', {
				props: {
					className: 'btdel'
				}
			});

			BX.bind(delDiv, 'click', BX.proxy(function ()
			{
				var block = BX.findParent(delDiv, {tag: 'div'});
				var index = block.getAttribute('data-index');
				var tr = BX.findParent(delDiv, {tag: 'tr'}, true);
				var selectCount = BX.findChildrenByClassName(tr, 'barcode_record', true);
				if (selectCount.length <= 1)
					return;

				if (product['STORES'].length < index + 1)
				{
					var tr_add_store = BX.findChildByClassName(tr, 'adm-bus-shipment-basket-store-add');
					tr_add_store.style.display = 'inline';
				}

				var deletedEssences = ['store', 'remaining_quantity', 'cur_amount', 'barcode'];
				for (var i in deletedEssences)
				{
					if (!deletedEssences.hasOwnProperty(i))
						continue;

					var essence = BX.findChildByClassName(tr, this.getDivWrapperClassName(product, deletedEssences[i], index), true);
					if (essence)
						BX.remove(essence);
				}

				var sum = 0;
				var curAmount = BX.findChildrenByClassName(tr, this.getProductBasketCode(product) + '_cur_amount', true);
				for (i in curAmount)
				{
					if (curAmount.hasOwnProperty(i))
						sum += parseFloat(curAmount[i].value);
				}

				var input = BX(this.getProductBasketCode(product) + '_amount');
				if (sum > parseFloat(input.value))
				{
					for (i in curAmount)
					{
						if (curAmount.hasOwnProperty(i))
							BX.addClass(curAmount[i], 'adm-bus-shipment-basket-error');
					}
				}
				else
				{
					for (i in curAmount)
					{
						if (curAmount.hasOwnProperty(i) && BX.hasClass(curAmount[i], 'adm-bus-shipment-basket-error'))
							BX.removeClass(curAmount[i], 'adm-bus-shipment-basket-error');
					}
				}


			}, this));

			divWrapper.appendChild(delDiv);
		}

		return divWrapper;
	}
	else
	{
		return BX.create('span');
	}
};

BX.Sale.Admin.ShipmentBasketEdit.prototype.createBlockRemainingQuantity = function(basketCode, product, index)
{
	var divWrapper = this.createDivWrapper(product, 'remaining_quantity', index);

	var measureText = null;
	if (!!product.MEASURE_TEXT)
	{
		measureText = BX.create('span',
			{
				text: " " + product.MEASURE_TEXT + " "
			}
		);
	}

	var span = BX.create('span', {
		props: {
			id: basketCode + '_store_remaining_quantity_' + index,
			className : basketCode + '_store_remaining_quantity'
		},
		'text': (!!product.STORES && product.STORES.length > 0 && product.STORES[0].hasOwnProperty('AMOUNT')) ? product.STORES[0].AMOUNT : '0'
	});

	divWrapper.appendChild(span);
	if (measureText)
		divWrapper.appendChild(measureText);

	return divWrapper;
};


BX.Sale.Admin.ShipmentBasketEdit.prototype.createBlockCurAmount = function(basketCode, product, index)
{
	if (!product.MODULE)
		return BX.create('span');

	var divWrapper = this.createDivWrapper(product, 'cur_amount', index);

	var measureText = null;
	if (!!product.MEASURE_TEXT)
		measureText = BX.create('span', {text: " "+product.MEASURE_TEXT+" "});

	var obAmount = BX(basketCode+'_amount');
	if (!!obAmount)
	{
		var parent = BX.findParent(obAmount, {tag: 'tr'}, true);
		var children = BX.findChildrenByClassName(parent, basketCode + '_cur_amount', true);
		var amount = parseFloat(obAmount.value);
		var sum = 0;
		for (var i in children)
		{
			if (children.hasOwnProperty(i))
				sum += parseFloat(children[i].value);
		}

		var quantity = (sum >= amount) ? 0 : amount - sum;
	}
	else
	{
		quantity = product.AMOUNT;
	}

	var input = BX.create('input',
		{
			props: {
				type: "text",
				name: this.getFieldName(basketCode, "BARCODE_INFO")+'[' + index + '][QUANTITY]',
				value: quantity,
				className: basketCode + '_cur_amount',
				id: basketCode + '_cur_amount_' + index,
				autocomplete : 'off'
			},
			attrs : {
				readOnly : this.isShipped
			},
			style : {
				'width' : '25px'
			}
		});

	BX.bind(input, 'keydown', function(e){
		if(!e) e = window.event;
		if(!e) return;
		if(e.keyCode == 13) input.blur();
	});

	BX.bind(input, 'change', BX.proxy(function ()
	{
		var tr = BX.findParent(input, {tag: 'tr'}, true);
		var children = BX.findChildrenByClassName(tr, basketCode + '_cur_amount', true);
		var obAmount = BX(basketCode + '_amount');

		if (!!product.STORES && product.STORES.length == 1 && product.IS_SET_ITEM != 'Y')
			obAmount.value = children[0].value;
	}, this));

	divWrapper.appendChild(input);
	if (measureText)
		divWrapper.appendChild(measureText);

	return divWrapper;
};

BX.Sale.Admin.ShipmentBasketEdit.prototype.getActualStoreQuantity = function(basketCode, index)
{
	var input = BX(basketCode + '_cur_amount_' + index);
	return input ? parseFloat(input.value) : 0;
};

BX.Sale.Admin.ShipmentBasketEdit.prototype.getActualAmount = function(basketCode)
{
	var input = BX(basketCode + '_amount');
	return input ? parseFloat(input.value) : 0;
};

BX.Sale.Admin.ShipmentBasketEdit.prototype.getActualBarcodeQuantity = function(basketCode, index)
{
	var result = 0;

	if(this.useStoreControl)
	{
		result = this.getActualStoreQuantity(basketCode, index);
	}
	else
	{
		result = this.getActualAmount(basketCode);
	}

	return result;
};

BX.Sale.Admin.ShipmentBasketEdit.prototype.getActualStoreIdByIndex = function(basketCode, index)
{
	var name = this.getFieldName(basketCode, "BARCODE_INFO")+'[' + index + '][STORE_ID]',
		elements = document.getElementsByName(name);

	var result = 0;

	if(elements && elements[0])
	{
		result = parseInt(elements[0].value);
	}

	return result;
};

BX.Sale.Admin.ShipmentBasketEdit.prototype.createBlockBarcode = function(basketCode, product, index)
{
	if (!product.IS_SET_PARENT || product.IS_SET_PARENT !== 'Y')
	{
		var type = parseInt(product.QUANTITY) === 1 ? BX.Sale.Admin.Order.ShipmentBasketBarcodeView.TYPE_INPUT : BX.Sale.Admin.Order.ShipmentBasketBarcodeView.TYPE_BUTTON;

		var divWrapper = this.createDivWrapper(product, 'barcode', index),
			barcodeParams = {
				basketId: basketCode,
				product: product,
				index: index,
				type: type,
				orderId: this.orderId,
				useStoreControl: this.useStoreControl,
				dataFieldTemplate: '<input'+
					' type="hidden"'+
					' name="' + this.getFieldName(basketCode, 'BARCODE_INFO') + '[' + index +'][BARCODE][#ITERATOR#][#DATA_TYPE#]"'+
					' class="1_barcode_#DATA_TYPE_LOWER#">'
			},
			barcode = null;

		if(this.isShipped)
		{
			barcode = new BX.Sale.Admin.Order.ShipmentBasketBarcodeView(barcodeParams);
		}
		else
		{
			barcodeParams.getActualBarcodeQuantityMethod = this.getActualBarcodeQuantity.bind(this);
			barcodeParams.getActualStoreIdByIndexMethod = this.getActualStoreIdByIndex.bind(this);

			barcode = new BX.Sale.Admin.Order.ShipmentBasketBarcodeEdit(barcodeParams);
		}

		divWrapper.appendChild(barcode.render());
		return divWrapper;
	}
	else
	{
		return BX.create('span');
	}
};

BX.Sale.Admin.ShipmentBasketEdit.prototype.createProductMenuContent = function(basketCode)
{
	return [
		{
			"ICON": "delete",
			"TEXT": BX.message("SALE_ORDER_BASKET_PROD_MENU_DELETE"),
			"ACTION": this.objName+'.moveToSystemBasket("'+basketCode+'")'
		}
	];
};

BX.Sale.Admin.ShipmentBasketEdit.prototype.createProductRow = function(basketCode, product)
{
	var	cellContent,
		updaters = {},
		_this = this,
		tBody = BX.create(
			'tbody',
			{
				props:
				{
					"id": this.createProductRowId(basketCode, product)
				},
				"style" :
				{
					"textAlign": "left",
					"borderBottom": "1px solid #DDD"
				}
			}
		),
		menuCell = this.createMenuCell(basketCode, product),
		tr = BX.create('tr',{
			style : {
				'vertical-align' : 'top'
			}
		});

	if(product.IS_SET_ITEM != "Y")
	{
		var hiddenFields = this.createHiddenFields(basketCode, product);
		tBody.setAttribute("data-basket-code", basketCode);

		if(product.IS_SET_PARENT == 'Y' && product.OLD_PARENT_ID)
			tBody.setAttribute("data-old-parent-id-parent", product.OLD_PARENT_ID);
	}
	else
	{
		BX.addClass(tBody, "bundle-child-"+product.OLD_PARENT_ID);
		BX.addClass(tBody, "basket-bundle-child-hidden");
		BX.addClass(tBody, "bundle-child");
	}

	tr.setAttribute('data-index', 1);

	if(this.createProductBasement)
		menuCell.rowSpan = 2;

	tr.appendChild(menuCell);

	tr.appendChild(this.createCheckboxField(product));

	for(var fieldId in this.visibleColumns)
	{
		if (!this.visibleColumns.hasOwnProperty(fieldId))
			continue;

		cellContent = this.createProductCell(basketCode, product, fieldId, 1);
		if(cellContent)
			tr.appendChild(cellContent);
	}

	tBody.appendChild(tr);
	return tBody;
};

BX.Sale.Admin.ShipmentBasketEdit.prototype.createCheckbox = function(basketCode, product)
{
	var checkbox =  BX.create('input', {
			props:{
				type: 'checkbox',
				className : 'checkboxForDelete'
			},
			style : {
				'margin' : '0'
			}
		});
	checkbox.readOnly = this.isShipped;
	checkbox.setAttribute('data-basket-code', basketCode);
	BX.bind(checkbox, "click", BX.proxy( function(){
		var obCount = BX(this.idPrefix+'_selected_count');
		if (!obCount)
			return;
		var count = parseInt(obCount.innerHTML);
		var btDel = BX('action_delete_button');
		if (!!checkbox.checked)
			obCount.innerHTML = ++count;
		else
			obCount.innerHTML = --count;
		if (count <= 0)
			BX.addClass(btDel, 'adm-edit-disable');
		else
			BX.removeClass(btDel, 'adm-edit-disable');
	},this));

	return checkbox;
};

BX.Sale.Admin.ShipmentBasketEdit.prototype.createCheckboxField = function(product)
{
	var basketCode = this.getProductBasketCode(product);
	var td = BX.create('td');
	if (
		(product.IS_SET_PARENT == 'Y' && product.SET_ITEMS > 0)
		|| (product.IS_SET_PARENT && product.IS_SET_ITEM == 'N')
		|| (!product.MODULE)
	)
	{
		td.appendChild(this.createCheckbox(basketCode, product));
	}

	var hiddenRequiredFields = ['MODULE', 'PRODUCT_ID', 'OFFER_ID', 'ORDER_DELIVERY_BASKET_ID', 'BASKET_ID'];
	for (var i in hiddenRequiredFields)
	{
		if (!hiddenRequiredFields.hasOwnProperty(i))
			continue;

		if (product[hiddenRequiredFields[i]])
		{
			td.appendChild(BX.create('input', {
				props: {
					type: 'hidden',
					name: this.getFieldName(basketCode, hiddenRequiredFields[i]),
					value: product[hiddenRequiredFields[i]]
				}
			}));
		}
	}

	return td;
};

BX.Sale.Admin.ShipmentBasketEdit.prototype.createMenuCell = function(basketCode, product)
{
	var menuSpan,
		menuContent = this.createProductMenuContent(basketCode);

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
				className: 'tac',
				id: this.idPrefix+"sale-order-basket-product-"+basketCode+"-menu"
			},
			children:[
				menuSpan
			]
		}
	);
};

BX.Sale.Admin.ShipmentBasketEdit.prototype.updateCountBasketItems = function()
{
	var productCount = BX(this.idPrefix+'_count');
	var table = BX(this.tableId);
	var counter = 0;
	var basketCode = null;
	if (productCount)
	{
		for(var i=0, l=table.tBodies.length; i<l; i++)
		{
			if(BX.hasClass(table.tBodies[i], "bundle-child"))
				continue;

			if (basketCode = table.tBodies[i].getAttribute("data-basket-code"))
				counter++;

		}

		productCount.innerHTML = counter;
	}
};

BX.Sale.Admin.ShipmentBasketEdit.prototype.getProductBasketCode = function(product)
{
	return product.BASKET_ID;
};

BX.Sale.Admin.ShipmentBasketEdit.prototype.groupMoveToSystemBasket = function (_this)
{
    var allSelected = BX('action_target');
	var i = null;
    if (allSelected.checked)
    {
	    if (confirm(BX.message("SALE_ORDER_SHIPMENT_BASKET_ALL_PRODUCTS_DEL")))
	    {
		    for (i in this.products)
		    {
			    if (this.products.hasOwnProperty(i) && !!this.products[i])
			    {
					var basketCode = this.getProductBasketCode(this.products[i]);
					this.moveToSystemBasket(basketCode);
					var tBody = BX(this.createProductRowId(basketCode, this.products[i]));
					BX.remove(tBody);
			    }
		    }
			BX.addClass(_this, 'adm-edit-disable');
	    }
    }
    else
    {
		var tableId = BX(this.tableId);
		var checkboxes = BX.findChildrenByClassName(tableId, "checkboxForDelete", true);
	    if (!BX.hasClass(_this, 'adm-edit-disable') && confirm(BX.message("SALE_ORDER_SHIPMENT_BASKET_SELECTED_PRODUCTS_DEL")))
	    {
		    var obCount = BX(this.idPrefix + '_selected_count');
		    for (i in checkboxes)
		    {
			    if (checkboxes.hasOwnProperty(i) && !!checkboxes[i].checked)
					this.moveToSystemBasket(checkboxes[i].getAttribute("data-basket-code"));
			}
			BX.html(obCount, '0');
			BX.addClass(_this, 'adm-edit-disable');
	    }
    }

};

BX.Sale.Admin.ShipmentBasketEdit.prototype.getFieldName = function(basketCode, type)
{
	return "SHIPMENT[1][PRODUCT]["+basketCode+"]["+type+"]";
};

BX.Sale.Admin.ShipmentBasketEdit.prototype.initGroupActions = function ()
{
	var action = BX('action_target');
	BX.bind(action, 'change', function() {
		var btnGroupDel = BX('action_delete_button');
		if (!this.checked)
			BX.addClass(btnGroupDel, 'adm-edit-disable');
		else
			BX.removeClass(btnGroupDel, 'adm-edit-disable');
	});
};

BX.Sale.Admin.ShipmentBasketEdit.prototype.addRowEmptyBasket = function()
{
	var tBody = BX.create('tbody', {
		props : {
			id : 'row_empty_basket'
		}
	});
	var tdCount = 0;
	BX.appendChild(tBody, BX.create('tr'));
	for (var i in this.visibleColumns)
	{
		++tdCount;
		BX.append(tr, BX.create('td'))
	}
	tr.colspan = tdCount;

	return tr;
};

BX.Sale.Admin.ShipmentBasketEdit.prototype.createFieldImage = function(basketCode, product, fieldId)
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

	resultNode = BX.create('span',{
		children: [pictureNode]
	});

	return resultNode;
};

BX.Sale.Admin.ShipmentBasketEdit.prototype.checkProductByBarcode = function(_this)
{
	if (this.isShipped)
	{
		BX.Sale.Admin.OrderEditPage.showDialog(BX.message('SALE_ORDER_SHIPMENT_BASKET_ERROR_ALREADY_SHIPPED'));
		return;
	}
	var barcode = _this.previousElementSibling.value;

	var request = {
		'action': 'getProductIdByBarcode',
		'barcode' : barcode,
		'callback' : BX.proxy(function (result) {
			if (result.ERROR && result.ERROR.length > 0)
			{
				BX.Sale.Admin.OrderEditPage.showDialog(result.ERROR);
			}
			else
			{
				var productId = parseInt(result.RESULT.PRODUCT_ID);
				var inBasket = false;
				for (var i in this.link.products)
				{
					if (!this.link.products.hasOwnProperty(i))
						continue;

					if (this.link.products[i].OFFER_ID == productId)
					{
						this.products[i] = this.link.products[i];
						delete this.link.products[i];

						this.productSet(this.products[i], false);
						this.removeEmptyFooter(this.idPrefix);
						this.updateCountBasketItems();
						inBasket = true;
						break;
					}
				}
				if (!inBasket)
					BX.Sale.Admin.OrderEditPage.showDialog(BX.message('SALE_ORDER_SHIPMENT_BASKET_ERROR_NOT_FOUND'));
			}
			_this.previousElementSibling.value = '';
		}, this)
	};
	BX.Sale.Admin.OrderAjaxer.sendRequest(request);
};

/**
 * Class BX.Sale.Admin.SystemShipmentBasketEdit
 */

BX.Sale.Admin.SystemShipmentBasketEdit = function(params)
{
	this.products = params.products;
	this.tableId = params.tableId;
	this.objName = params.objName;
	this.idPrefix = params.idPrefix;
	this.productsOrder = params.productsOrder;
	this.visibleColumns = params.visibleColumns;
};

BX.Sale.Admin.SystemShipmentBasketEdit.prototype = Object.create(BX.Sale.Admin.ShipmentBasketEdit.prototype);

BX.Sale.Admin.SystemShipmentBasketEdit.prototype.addProductSearch = function()
{
	if (this.link.isShipped)
	{
		BX.Sale.Admin.OrderEditPage.showDialog(BX.message('SALE_ORDER_SHIPMENT_BASKET_ERROR_ALREADY_SHIPPED'));
		return;
	}

	var thead = '<thead><tr><td class="adm-s-order-table-context-menu-column"><span class="adm-s-order-table-title-icon"></span></td><td></td>';
	var i = null;

	for (i in this.visibleColumns)
	{
		if (this.visibleColumns.hasOwnProperty(i))
			thead += '<td>' + this.visibleColumns[i] + '</td>';
	}
	thead += '</thead></tr>';

	if (BX(this.tableId))
		BX.remove(BX(this.tableId));

	this.addProductDialog = new BX.CDialog({
		'content':'<table id="'+this.tableId+'" class="adm-s-order-table-ddi-table" width="100%">'+thead+'<tbody></tbody></table>',
		'title': BX.message['PAYMENT_WINDOW_VOUCHER_TITLE'],
		'width': 1100,
		'height': 400,
		'resizable': false,
		'buttons': [
			new BX.CWindowButton({
				'title' : BX.message('SALE_ORDER_SHIPMENT_BASKET_ADD'),
				'action' : BX.proxy(function()
				{
					this.groupAdd();
				}, this),
				'className' : 'adm-btn-save'
			}),
			new BX.CWindowButton({
				'title' : BX.message('SALE_ORDER_SHIPMENT_BASKET_CLOSE'),
				'action' : function ()
				{
					BX.WindowManager.Get().Close();
				}
			})
		]
	});

	if(this.productsOrder && this.productsOrder.length)
		for(i = 0, l = this.productsOrder.length-1; i <= l; i++)
			if (!!this.products[this.productsOrder[i]])
				this.productSet(this.products[this.productsOrder[i]], true);

	if (Object.keys(this.products).length == 0)
	{
		var tbl = BX(this.tableId);
		tbl.appendChild(this.createEmptyFooter());
	}


	this.addProductDialog.Show();
};


BX.Sale.Admin.SystemShipmentBasketEdit.prototype.createProductMenuContent = function(basketCode)
{
	return [
		{
			"ICON": "view",
			"TEXT": BX.message("SALE_ORDER_BASKET_PROD_MENU_ADD"),
			"ACTION": this.objName+'.moveToBasket("'+basketCode+'")',
			"DEFAULT":true
		}
	];
};

BX.Sale.Admin.SystemShipmentBasketEdit.prototype.createFieldAmount= function (container, product)
{
	if (!!product.MEASURE_TEXT)
	{
		container.push(
			BX.create('span',
				{
					text: " " + product.MEASURE_TEXT + " "
				}
			)
		);
	}

	container.push(
		BX.create('span',
			{
				text : product.AMOUNT
			}
		)
	);
	return container;
};

BX.Sale.Admin.SystemShipmentBasketEdit.prototype.moveToBasket = function (basketCode)
{
	var productRowParent = null;
	this.link.products[basketCode] = this.products[basketCode];
	this.link.productSet(this.products[basketCode], false);
	var productRaw = BX(this.createProductRowId(basketCode, this.products[basketCode]));

	if(productRaw)
	{
		productRowParent = productRaw.parentNode;
		if (productRowParent)
			productRowParent.removeChild(productRaw);
	}

	// set
	var oldParentId = productRaw.getAttribute('data-old-parent-id-parent');
	if(oldParentId)
	{
		var  bundleChildren = BX.findChildrenByClassName(productRowParent, "bundle-child-"+oldParentId, false);

		for(var i in bundleChildren)
		{
			if (bundleChildren.hasOwnProperty(i))
				bundleChildren[i].parentNode.removeChild(bundleChildren[i]);
		}
	}

	delete this.products[basketCode];

	if (Object.keys(this.products).length == 0)
	{
		var tbl = BX(this.tableId);
		tbl.appendChild(this.createEmptyFooter());
	}
	this.removeEmptyFooter(this.link.idPrefix);
	this.link.updateCountBasketItems();

	this.link.updateShipment();
};


BX.Sale.Admin.SystemShipmentBasketEdit.prototype.groupAdd = function ()
{
	var obTable = BX(this.tableId);
	var checkboxes = BX.findChildrenByClassName(obTable, "checkboxForDelete", true);
	for (var i in checkboxes)
	{
		if (checkboxes.hasOwnProperty(i) && !!checkboxes[i].checked)
			this.moveToBasket(checkboxes[i].getAttribute("data-basket-code"));
	}
};


BX.Sale.Admin.SystemShipmentBasketEdit.prototype.productAdd = function(product)
{
	var productRaw = BX.Sale.Admin.ShipmentBasketEdit.prototype.productAdd.apply(this, [product]);

	if (product.IS_SET_ITEM != 'Y')
	{
		BX.bind(productRaw, 'dblclick', BX.proxy(function ()
		{
			this.moveToBasket(this.getProductBasketCode(product));
		}, this));
	}

	return productRaw;
};