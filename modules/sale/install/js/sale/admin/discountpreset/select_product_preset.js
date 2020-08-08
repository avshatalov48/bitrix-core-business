BX.namespace("BX.Sale.Admin.DiscountPreset");

BX.Sale.Admin.DiscountPreset.SelectProduct = (function(){

	var SelectProduct = function (parameters){
		this.tableId = 'sale_discount_preset_product_table';
		this.emptyRowId = 'sale_discount_preset_product_table_empty_row';
		this.idPrefix = 'idPrefix';
		this.siteId = parameters.siteId;
		this.presetId = parameters.presetId;
		this.sectionCount = parameters.sectionCount || 1;
		this.inputNameProduct = parameters.inputNameProduct || 'discount_product[]';
		this.inputNameSection = parameters.inputNameSection || 'discount_section[]';
		this.container = BX('sale_discount_preset_section_box');

		this.productIds = {};

		if(parameters.products)
		{
			for(var i in parameters.products)
			{
				if (!parameters.products.hasOwnProperty(i))
					continue;

				this.productAdd(parameters.products[i]);
			}
		}

		setTimeout(BX.delegate(function(){
			var textAreas = BX.findChildrenByClassName(this.container, 'mli-field', true);
			for(var i in textAreas)
			{
				if(!textAreas.hasOwnProperty(i))
				{
					continue;
				}

				BX.adjust(textAreas[i], {
					style: {
						width: '99%'
					}
				});
			}
		}, this), 200);

		this.setEvents();
	};

	SelectProduct.prototype.setEvents = function() {
		BX.bind(BX('sale_discount_preset_section_add'), "click", BX.delegate(this.onClickAddSection, this));
		BX.bind(BX('sale_discount_preset_product_add'), "click", BX.delegate(this.onClickAddProduct, this));

		BX.addCustomEvent('onDeletePresetProduct', BX.delegate(this.onDeletePresetProduct, this));
	};

	SelectProduct.prototype.onClickSectionToShowPopup = function(event) {
		var target = event.srcElement || event.target;
		var sectionNumber = target.getAttribute('data-section-number');
		var url = 'cat_section_search.php?land=ru&discount=Y&n=sect_' + sectionNumber;
		window.open(url, '', 'scrollbars=yes,resizable=yes,width=900,height=600,top=' + parseInt((screen.height - 500) / 2 - 14, 10) + ',left=' + parseInt((screen.width - 600) / 2 - 5, 10));

		BX.PreventDefault(event);
	};

	SelectProduct.prototype.onClickDeleteSection = function (event)
	{
		var target = event.srcElement || event.target;
		var trParent = BX.findParent(target, {
			tagName: 'tr'
		}, 10);

		if (trParent) {
			BX.remove(trParent);
		}

		BX.PreventDefault(event);
	}
	;

	/**
	 * order basket grid
	 */
	SelectProduct.prototype.onDeletePresetProduct = function(productId)
	{
		var row = BX(this.createProductRowId({PRODUCT_ID: productId}));
		this.productIds[productId] = false;
		BX.remove(row);

		if(BX.findChildren(BX(this.tableId), {tagName: 'tbody'}).length === 1)
		{
			BX.show(BX(this.emptyRowId), 'block');
		}
	};

	SelectProduct.prototype.createProductRowId = function(product)
	{
		return this.idPrefix+"discount-preset-product-"+ (product.OFFER_ID || product.PRODUCT_ID);
	};

	SelectProduct.prototype.createProductMenuContent = function(productId)
	{
		return [{
			"ICON": "delete",
			"TEXT": BX.message("JS_SALE_HANDLERS_DISCOUNTPRESET_DELETE_PRODUCT"),
			"ACTION": "BX.onCustomEvent('onDeletePresetProduct', [" + productId + "])"
		}];
	};

	SelectProduct.prototype.createMenuCell = function(basketCode, product)
	{
		var menuSpan,
			menuContent = this.createProductMenuContent((product.OFFER_ID || product.PRODUCT_ID));

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
	SelectProduct.prototype.createFieldImage = function(basketCode, product, fieldId)
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

			resultNode.style.textAlign = "center";
		}
		else
		{
			resultNode = BX.create('div',{
				style: {
					width: '150px',
					textAlign: 'center'
				},
				children: [pictureNode]
			});
		}

		return resultNode;
	};

	SelectProduct.prototype.createFieldSkuProps = function(basketCode, product, fieldId)
	{
		return this.createSkuPropsTable(basketCode, product);
	};

	SelectProduct.prototype.createSkuPropsTable = function(basketCode, product)
	{
		var table = BX.create('table'),
			html,
			skuCodes = [],
			result = null;

		if(product.SKU_PROPS)
		{
			for(var skuId in product.SKU_PROPS)
			{
				if(!product.SKU_PROPS.hasOwnProperty(skuId))
					continue;

				// Unlinked property of type E
				if(!product.SKU_PROPS[skuId]["VALUE"])
					product.SKU_PROPS[skuId]["VALUE"] = {"NAME": skuId};

				if(product.SKU_PROPS[skuId]["VALUE"]["PICT"])
					html = '<div style="width: 17px; height: 17px; text-align: center; border: 1px solid gray;">'+
					'<img  width="17" height="17" src="'+product.SKU_PROPS[skuId]["VALUE"]["PICT"]+'">'+
					'</div>';
				else
					html = '<div style="font-size: 9px; padding: 2px 5px; text-align: center; border: 1px solid gray;">'+BX.util.htmlspecialchars(product.SKU_PROPS[skuId]["VALUE"]["NAME"])+'</div>';

				// Unlinked property of type E
				if(!product.SKU_PROPS[skuId]["NAME"])
					product.SKU_PROPS[skuId]["NAME"] = skuId;

				var tdPropName = BX.create('td',{
					html: '<span style="color: gray; font-size: 11px">'+BX.util.htmlspecialchars(product.SKU_PROPS[skuId]["NAME"])+' </span>',
					style : {
						'text-align' : 'left'
					}
				});

				table.appendChild(
					BX.create('tr',{
						children: [
							tdPropName,
							BX.create('td',{html: html})
						]
					})
				);

				skuCodes.push(product.SKU_PROPS[skuId]["CODE"]);
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
				if(!product.PROPS[i]["VALUE"])product.PROPS[i]["VALUE"] = "";

				if((product.PROPS[i]["CODE"] == "PRODUCT.XML_ID" || product.PROPS[i]["CODE"] == "CATALOG.XML_ID"))
					continue;

				var tdPropName = BX.create('td',{
					html: '<span style="color: gray; font-size: 11px">'+BX.util.htmlspecialchars(product.PROPS[i]["NAME"])+' </span>',
					style : {
						'text-align' : 'left'
					}
				});

				var tr = BX.create('tr',{
					children: [
						tdPropName,
						BX.create('td',{
							html: '<div style="font-size: 9px; padding: 2px 5px; text-align: center;">'+BX.util.htmlspecialchars(product.PROPS[i]["VALUE"])+'</div>'
						})
					]
				});

				table.appendChild(tr);
			}
		}

		if(table.children.length)
			result = table;

		return result;
	};

	SelectProduct.prototype.createProductCell = function(basketCode, product, fieldId)
	{
		var result = null,
			cellNodes = [],
			fieldValue = product[fieldId],
			tdClass = "",
			_this = this;

		switch(fieldId)
		{
			case "NUMBER":
				cellNodes.push(
					BX.create(
						'span',
						{
							props:{
								id: product.IS_SET_ITEM != "Y" ? this.idPrefix+"sale_order_product_"+basketCode+"_number" : "&nbsp;"
							},
							html: "&nbsp;"
						}
					)
				);
				break;

			case "NAME":

				if(product.EDIT_PAGE_URL)
				{
					node = BX.create('a',{
							props:{
								href:product.EDIT_PAGE_URL,
								target:"_blank"
							},
							html: BX.util.htmlspecialchars(fieldValue)
						});
				}
				else
				{
					node = BX.create('span', {
						style: {fontWeight: "bold"},
						html: BX.util.htmlspecialchars(product[fieldId])
					});
				}

				cellNodes.push(node);

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

				if(product.IS_SET_ITEM == "Y")
				{
					result.style.fontStyle = "italic";
					result.style.paddingLeft = "40px";
				}
			}
		}

		return result;
	};

	SelectProduct.prototype.productAdd = function(product)
	{
		if(product.PRODUCT_ID && !product.OFFER_ID)
		{
			if(this.productIds[product.PRODUCT_ID])
			{
				return;
			}
			this.productIds[product.PRODUCT_ID] = true;
		}

		if(product.PRODUCT_ID && product.OFFER_ID)
		{
			if(this.productIds[product.OFFER_ID])
			{
				return;
			}
			this.productIds[product.OFFER_ID] = true;
		}

		var basketCode = product.id;
		var productRow = this.createProductRow(basketCode, product);
		BX(this.tableId).appendChild(productRow);
		BX.hide(BX(this.emptyRowId));
	};

	SelectProduct.prototype.createProductRow = function(basketCode, product)
	{
		var	cellContent,
			tbody = BX.create('tbody', {props:{
					"id": this.createProductRowId(product)
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
			tr.appendChild(menuCell);
		}

		var field,
			hiddenFields = [];

		if(product.IS_SET_ITEM != "Y")
		{
			tbody.setAttribute("data-product-id", (product.OFFER_ID || product.PRODUCT_ID));

			if(product.IS_SET_PARENT && product.OLD_PARENT_ID)
				tbody.setAttribute("data-old-parent-id-parent", product.OLD_PARENT_ID);
		}
		else
		{
			BX.addClass(tbody,"bundle-child-"+product.OLD_PARENT_ID);
			BX.addClass(tbody,"basket-bundle-child-hidden");
			BX.addClass(tbody,"bundle-child");
		}

		for(var fieldId in {
			IMAGE: 'IMAGE',
			NAME: 'NAME',
			PROPS: 'PROPS'
		})
		{
			// if(!this.visibleColumns.hasOwnProperty(fieldId))
			// 	continue;

			cellContent = this.createProductCell(basketCode, product, fieldId);

			if(cellContent)
			{
				if(hiddenFields)
					while(field = hiddenFields.pop())
						cellContent.appendChild(field);

				tr.appendChild(cellContent);
			}
			else
			{
				var colspan = tr.lastChild.getAttribute('colspan') || 1;
				colspan++;
				tr.lastChild.setAttribute('colspan', colspan);
			}
		}

		tr.appendChild(BX.create('input',{
			props: {
				type: 'hidden',
				name: this.inputNameProduct,
				value: product.OFFER_ID || product.PRODUCT_ID
			}
		}));

		tbody.appendChild(tr);

		return tbody;
	};

	SelectProduct.prototype.getParamsByProductId = function(product, iblockId) {
		BX.ajax({
			url: 'sale_discount_preset_detail.php?' +
					'lang=' + BX.message.LANGUAGE_ID + '&' +
					'action=getProductDetails&' +
					'PRESET_ID=' + BX.util.urlencode(this.presetId) +
					'&public=y'
			,
			method: 'POST',
			dataType: 'json',
			data: {
				productId: product.id,
				iblockId: iblockId,
				quantity: product.quantity,
				sessid: BX.bitrix_sessid(),
				siteId: this.siteId
			},
			onsuccess: BX.delegate(function (data) {
				this.productAdd(data);
			}, this),
			onfailure: function (data)
			{
			}
		});
	};
	/**
	 * end of order basket grid
	 */


	SelectProduct.prototype.onClickAddProduct = function(event) {
		var funcName = 'perDayPreset' + '.getParamsByProductId';
		window[funcName] = BX.proxy(function (params, iblockId)
		{
			this.getParamsByProductId(params, iblockId);
		}, this);

		var popup = new BX.CDialog({
			content_url: '/bitrix/tools/sale/product_search_dialog.php?' +
			'lang=' + BX.message.LANGUAGE_ID +
			'&LID=' + this.siteId +
			'&caller=preset_edit' +
			'&allow_select_parent=Y' +
			'&func_name=' + funcName +
			'&STORE_FROM_ID=0',
			height: Math.max(500, window.innerHeight - 400),
			width: Math.max(800, window.innerWidth - 400),
			draggable: true,
			resizable: true,
			min_height: 500,
			min_width: 800
		});
		BX.addCustomEvent(popup, 'onWindowRegister', BX.defer(function ()
		{
			popup.Get().style.position = 'fixed';
			popup.Get().style.top = (parseInt(popup.Get().style.top) - BX.GetWindowScrollPos().scrollTop) + 'px';
		}));

		popup.Show();

		BX.PreventDefault(event);
	};

	SelectProduct.prototype.onClickAddSection = function(event) {
		var url = 'iblock_section_search.php?lang='+ BX.message.LANGUAGE_ID +'&discount=Y&lookup=jsMLI_select_section';
		var popup = window.open(url, '', 'scrollbars=yes,resizable=yes,width=900,height=600,top=' + parseInt((screen.height - 500) / 2 - 14, 10) + ',left=' + parseInt((screen.width - 600) / 2 - 5, 10));

		BX.PreventDefault(event);
	};

	return SelectProduct;
})();
