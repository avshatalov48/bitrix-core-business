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
			else
			{
				alert("__MobileAppList.prototype.getItemsHtml !result"); //develop
			}
		},
		onfailure: function(){
			alert("__MobileAppList.prototype.getItemsHtml failure"); //develop
		}
	});
};

__MobileAppList.prototype.deleteItem = function(itemId)
{
	//alert("__MobileAppList.prototype.deleteOrder");
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