window.JCCatalogStoreSKU = function(params)
{
	var i;

	if(!params)
		return;

	this.config = {
		'id' : params.ID,
		'showEmptyStore'	: params.SHOW_EMPTY_STORE,
		'useMinAmount'		: params.USE_MIN_AMOUNT,
		'minAmount'			: params.MIN_AMOUNT
	};

	this.messages = params.MESSAGES;
	this.sku = params.SKU;
	this.stores = params.STORES;
	this.obStores = {};
	for (i in this.stores)
		this.obStores[this.stores[i]] = BX(this.config.id+"_"+this.stores[i]);

	BX.addCustomEvent(window, "onCatalogStoreProductChange", BX.proxy(this.offerOnChange, this));
};

window.JCCatalogStoreSKU.prototype.offerOnChange = function(id)
{
	var curSku = this.sku[id],
		k,
		message,
		parent;

	for (k in this.obStores)
	{
		message = (!!this.config.useMinAmount) ? this.getStringCount(0) : '0';
		BX.adjust(this.obStores[k], {html: message});
		if (!!curSku[k])
		{
			message = (!!this.config.useMinAmount) ? this.getStringCount(curSku[k]) : curSku[k];
			BX.adjust(this.obStores[k],  {html: message});
		}
		parent = BX.findParent(this.obStores[k], {tagName: 'li'});
		if (!!this.config.showEmptyStore || curSku[k] > 0)
			BX.show(parent);
		else
			BX.hide(parent);
	}
};

window.JCCatalogStoreSKU.prototype.getStringCount = function(num)
{
	if (num == 0)
		return this.messages['ABSENT'];
	else if (num >= this.config.minAmount)
		return this.messages['LOT_OF_GOOD'];
	else
		return this.messages['NOT_MUCH_GOOD'];
};