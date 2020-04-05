__MobileAppList = function(params) {

	for(var key in params)
		this[key] = params[key];

	this.itemIdPrefix = "mobile-list-item-";
};

__MobileAppList.prototype.processItemsHtml = function(arItemsHtml, insertToBottom)
{
	for(var itemId in arItemsHtml)
	{
		var arItem = BX(this.itemIdPrefix+itemId);

		if(arItem)
			this.updateItem(itemId, arItemsHtml[itemId]);
		else
			this.addItem(arItemsHtml[itemId],insertToBottom);
	}

};

__MobileAppList.prototype.getItemsHtml = function(arItem, insertToBottom)
{
	var _this = this;

	postData = {
		ajax_mode: 'Y',
		items: arItem
	};

	app.showPopupLoader({"text":"getting items html"});

	BX.ajax({
		timeout:   30,
		method:   'POST',
		dataType: 'json',
		url:       this.ajaxUrl,
		data:      postData,
		onsuccess: function(result) {
			app.hidePopupLoader();
			if(result)
			{
					_this.processItemsHtml(result, insertToBottom);
			}
/*			else
			{
				alert("__MobileAppList.prototype.getItemsHtml !result"); //develop
			}
		},
		onfailure: function(){
			alert("__MobileAppList.prototype.getItemsHtml failure"); //develop */
		}
	});
};

__MobileAppList.prototype.deleteItem = function(itemId)
{
	var arItem = BX(this.itemIdPrefix+itemId);

	if(arItem)
	{
		arItem.parentNode.removeChild(arItem);
		return true;
	}

	return false;
};

__MobileAppList.prototype.makeDomObjFromHtml = function(itemHtml)
{
	var tmpParentDomObj = document.createElement("DIV");
	tmpParentDomObj.innerHTML = itemHtml;
	var children = BX.findChildren(tmpParentDomObj);

	if(!children[0])
		return false;

	//document.replaceChild(children[0],tmpParentDomObj);

	return children[0];
};

__MobileAppList.prototype.updateItem = function(itemId, itemNewHtml)
{
	var itemOldDomObj = BX(this.itemIdPrefix+itemId);

	if(!itemOldDomObj)
		return false;

	itemOldDomObj.id = itemOldDomObj.id+"_old";

	var newItemDomObj = this.makeDomObjFromHtml(itemNewHtml);

	itemOldDomObj.parentNode.replaceChild(newItemDomObj,itemOldDomObj);

	return true;
};

__MobileAppList.prototype.addItem = function(itemHtml, insertToBottom)
{
	if(!itemHtml)
	{
		alert("__MobileAppList.prototype.addItem !arItem"); //develop
		return false;
	}

	var newItem = this.makeDomObjFromHtml(itemHtml);

	var itemsListObj = BX("mobile-list");
	var listInnerHtml = itemsListObj.innerHTML;

	if(insertToBottom)
		listInnerHtml = listInnerHtml+itemHtml;
	else
		listInnerHtml = itemHtml+listInnerHtml;

	itemsListObj.innerHTML = listInnerHtml;

	return true;
};

__MobileAppList.prototype.toggleItem = function(id)
{
	var	obj = BX(id);
	if(!obj)
		return;

	var contentDiv = BX.findChild(obj, { className: "mapp_itemlist_item_content"}, true);

	if(!contentDiv || !contentDiv.style)
		return;

	if(BX.hasClass(contentDiv, 'closed'))
	{
		BX.removeClass(obj, 'mapp_item_folded');
		BX.addClass(obj, 'mapp_item_gray');
	}
	else
	{
		BX.addClass(obj, 'mapp_item_folded');
		BX.removeClass(obj, 'mapp_item_gray');
	}

	BX.toggleClass(contentDiv, 'closed');
};

__MobileAppList.prototype.makeFastButton = function(id)
{
	var _this = this,
		obj = BX(id);

	if(!obj || !id)
		return;

	new FastButton(obj,	function(e){ _this.toggleItem(id);	}, false);
};