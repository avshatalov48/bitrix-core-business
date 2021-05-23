__BitrixSaleMODE = function(params)
{
	for(var key in params)
		this[key] = params[key];
};

__BitrixSaleMODE.prototype.setProductStores = function(params)
{
	for (var storeId in params.qByStoresId)
	{
		this.products[params.productId]["STORES"][storeId]["QUANTITY"] = params.qByStoresId[storeId];

		if(this.products[params.productId]["STORES"][storeId]["BARCODES"])
		{
			var diff = this.products[params.productId]["STORES"][storeId]["BARCODES"].length - params.qByStoresId[storeId];

			if(diff > 0)
			{
				for (var i = diff - 1; i >= 0; i--)
				{
					delete(this.products[params.productId]["STORES"][storeId]["BARCODES"]);
					delete(this.products[params.productId]["STORES"][storeId]["BARCODES_FOUND"]);
				}
			}
		}

		if(!BX("store_ready_"+params.productId))
		{
			var linkContainer = BX("store_link_cont_"+params.productId);

			if(linkContainer)
			{
				var containerInnerHtml = linkContainer.innerHTML;
				linkContainer.innerHTML = containerInnerHtml+'<span id="store_ready_'+params.productId+'" style="color:green;"> - '+BX.message("SMODE_READY")+'</span>';
			}

			var linkDiv = BX("bc_link_div_"+params.productId);

			if(linkDiv && linkDiv.style.display == 'none')
				linkDiv.style.display = '';
		}
	}
};

__BitrixSaleMODE.prototype.setProductBarcodes = function(params)
{
	this.products[params.productId] = params.productData;

	var readyBlock = BX("barcode_checkres_"+params.productId);
	var linkContainer = BX("barcode_link_cont_"+params.productId);

	if(linkContainer)
	{
		if(!readyBlock && params.productData["ALL_CHECKS_RESULT"] == 'Y')
		{
			var containerInnerHtml = linkContainer.innerHTML;
			linkContainer.innerHTML = containerInnerHtml+'<span id="barcode_checkres_'+
				params.productId+
				'" style="color:green;"> - '+
				BX.message("SMODE_READY")+
				'</span>';
		}
		else if(readyBlock && params.productData["ALL_CHECKS_RESULT"] != 'Y')
		{
			readyBlock.parentNode.removeChild(readyBlock);
		}
	}
};

__BitrixSaleMODE.prototype.getProductStores = function(productId)
{
	result = {};

	if(this.products[productId] && this.products[productId]["STORES"])
		result = this.products[productId]["STORES"];

	return result;
};

__BitrixSaleMODE.prototype.getProductInfo = function(productId)
{
	var result = {};

	if(this.products[productId])
		result = this.products[productId];

	return result;
};

__BitrixSaleMODE.prototype.deductOrder = function(params)
{
	var _this = this,
		postData = {
		orderId: this.orderId,
		deducted: params.deducted,
		sessid: BX.bitrix_sessid(),
		action: 'order_deduct'
	};

	if(params.deducted == 'Y')
	{
		postData["products"] = this.products;
		postData["useStores"] = this.useStores;
	}
	else
	{
		postData["undoReason"] = this.getUndoReason();
	}

	BX.ajax({
		timeout:   30,
		method:   'POST',
		dataType: 'json',
		url:       this.ajaxUrl,
		data:      postData,
		onsuccess: function(result)
		{
			if(result && !result.ERROR)
			{
				app.onCustomEvent("onAfterOrderChange", {id: _this.orderId});
				app.closeController({drop: true});
			}
			else if(result.ERROR)
			{
				app.alert({ text: 'ERROR: '+result.ERROR });
			}
			else
			{
				app.alert({ text: BX.message('SMODE_ERROR')+' !result.'});
			}
		},
		onfailure: function()
		{
			app.alert({ text: BX.message('SMODE_ERROR')+' onfailure.'});
		}
	});
};

__BitrixSaleMODE.prototype.getUndoReason = function()
{
	var result = '',
		ur = BX("deduct_undo_reason");

	if(ur)
		result = ur.value;

	return result;
};

__BitrixSaleMODE.prototype.makeFastButton = function(Id, url)
{
	var el = BX(Id);

	if(el)
	{
		new FastButton(el, function(){

			if(app.enableInVersion(8))
				app.showModalDialog({ url: url });
			else
				app.loadPageBlank({ url: url });

		}, false);
	}
};