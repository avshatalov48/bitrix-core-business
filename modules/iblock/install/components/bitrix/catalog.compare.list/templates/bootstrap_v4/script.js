(function (window) {

if (!!window.JCCatalogCompareList)
{
	return;
}

window.JCCatalogCompareList = function (params)
{
	this.obCompare = null;
	this.obAdminPanel = null;
	this.visual = params.VISUAL;
	this.ajax = params.AJAX;
	this.position = params.POSITION;

	BX.ready(BX.proxy(this.init, this));
};

window.JCCatalogCompareList.prototype.init = function()
{
	this.obCompare = BX(this.visual.ID);
	if (BX.type.isElementNode(this.obCompare))
	{
		BX.addCustomEvent(window, "OnCompareChange", BX.proxy(this.reload, this));
		BX.bindDelegate(this.obCompare, 'click', {tagName : 'a'}, BX.proxy(this.deleteCompare, this));
		if (this.position.fixed && this.position.align.vertical === 'top')
		{
			this.obAdminPanel = BX('bx-panel');
			if (BX.type.isElementNode(this.obAdminPanel))
			{
				this.setVerticalAlign();
				BX.addCustomEvent(window, 'onTopPanelCollapse', BX.proxy(this.setVerticalAlign, this));
			}
		}
	}
};

window.JCCatalogCompareList.prototype.reload = function()
{
	BX.showWait(this.obCompare);
	BX.ajax.post(
		this.ajax.url,
		this.ajax.reload,
		BX.proxy(this.reloadResult, this)
	);
};

window.JCCatalogCompareList.prototype.reloadResult = function(result)
{
	var mode = 'none';
	BX.closeWait();
	this.obCompare.innerHTML = result;
	if (BX.type.isNotEmptyString(result))
	{
		if (result.indexOf('<table') >= 0)
			mode = 'block';
	}
	BX.style(this.obCompare, 'display', mode);
};

window.JCCatalogCompareList.prototype.deleteCompare = function()
{
	var target = BX.proxy_context,
		itemID,
		url;

	if (!!target && target.hasAttribute('data-id'))
	{
		itemID = parseInt(target.getAttribute('data-id'), 10);
		if (!isNaN(itemID))
		{
			BX.showWait(this.obCompare);
			url = this.ajax.url + this.ajax.templates.delete + itemID.toString();
			BX.ajax.loadJSON(
				url,
				this.ajax.params,
				BX.proxy(this.deleteCompareResult, this)
			);
		}
	}
};

window.JCCatalogCompareList.prototype.deleteCompareResult = function(result)
{
	var tbl,
		i,
		cnt,
		newCount;

	BX.closeWait();
	if (BX.type.isPlainObject(result))
	{
		if (BX.type.isNotEmptyString(result.STATUS) && result.STATUS === 'OK' && !!result.ID)
		{
			BX.onCustomEvent('onCatalogDeleteCompare', [result.ID]);

			tbl = this.obCompare.querySelector('table[data-block="item-list"]');
			if (BX.type.isElementNode(tbl))
			{
				if (tbl.rows.length > 1)
				{
					for (i = 0; i < tbl.rows.length; i++)
					{
						if (
							tbl.rows[i].hasAttribute('data-row-id')
							&& tbl.rows[i].getAttribute('data-row-id') === ('row' + result.ID)
						)
						{
							tbl.deleteRow(i);
						}
					}
					if (BX.type.isNotEmptyString(result.COUNT) || BX.type.isNumber(result.COUNT))
					{
						newCount = parseInt(result.COUNT, 10);
						if (!isNaN(newCount))
						{
							cnt = this.obCompare.querySelector('span[data-block="count"]');
							if (BX.type.isElementNode(cnt))
							{
								cnt.innerHTML = newCount.toString();
							}
							cnt = null;
							BX.style(this.obCompare, 'display', (newCount > 0 ? 'block' : 'none'));
						}
					}
				}
				else
				{
					this.reload();
				}
			}
			tbl = null;
		}
	}
};

window.JCCatalogCompareList.prototype.setVerticalAlign = function()
{
	var topSize;
	if (BX.type.isElementNode(this.obCompare) && BX.type.isElementNode(this.obAdminPanel))
	{
		topSize = parseInt(this.obAdminPanel.offsetHeight, 10);
		if (isNaN(topSize))
		{
			topSize = 0;
		}
		topSize +=5;
		BX.style(this.obCompare, 'top', topSize.toString()+'px');
	}
};

})(window);