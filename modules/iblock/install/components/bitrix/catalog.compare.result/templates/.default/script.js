BX.namespace("BX.Iblock.Catalog");

BX.Iblock.Catalog.CompareClass = (function()
{
	var CompareClass = function(wrapObjId, reloadUrl)
	{
		this.wrapObjId = wrapObjId;
		this.reloadUrl = reloadUrl;
		BX.addCustomEvent(window, 'onCatalogDeleteCompare', BX.proxy(this.reload, this));
	};

	CompareClass.prototype.MakeAjaxAction = function(url)
	{
		BX.showWait(BX(this.wrapObjId));
		BX.ajax.post(
			url,
			{
				ajax_action: 'Y'
			},
			BX.proxy(this.reloadResult, this)
		);
	};

	CompareClass.prototype.reload = function()
	{
		BX.showWait(BX(this.wrapObjId));
		BX.ajax.post(
			this.reloadUrl,
			{
				compare_result_reload: 'Y'
			},
			BX.proxy(this.reloadResult, this)
		);
	};

	CompareClass.prototype.reloadResult = function(result)
	{
		BX.closeWait();
		BX(this.wrapObjId).innerHTML = result;
	};

	CompareClass.prototype.delete = function(url)
	{
		BX.showWait(BX(this.wrapObjId));
		BX.ajax.post(
			url,
			{
				ajax_action: 'Y'
			},
			BX.proxy(this.deleteResult, this)
		);
	};

	CompareClass.prototype.deleteResult = function(result)
	{
		BX.closeWait();
		BX.onCustomEvent('OnCompareChange');
		BX(this.wrapObjId).innerHTML = result;
	};

	return CompareClass;
})();