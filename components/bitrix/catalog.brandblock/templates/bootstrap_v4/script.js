;(function(window) {
if (window.JCIblockBrands)
{
	return;
}

window.JCIblockBrands = function (params)
{
	this.blockID = '';
	this.obBlock = null;
	this.dataName = 'data-popup';
	if (!!params && typeof params === 'object')
	{
		if (!!params.blockID && BX.type.isNotEmptyString(params.blockID))
		{
			this.blockID = params.blockID;
		}
		if (!!params.dataName && BX.type.isNotEmptyString(params.dataName))
		{
			this.dataName = params.dataName;
		}
	}
	if (this.blockID !== '')
	{
		BX.ready(BX.delegate(this.Init, this));
	}
};

window.JCIblockBrands.prototype.Init = function()
{
	if (this.blockID === '')
	{
		return;
	}
	this.obBlock = BX(this.blockID);
	if (!!this.obBlock)
	{
		BX.bindDelegate(this.obBlock, 'mouseover', { 'attribute': this.dataName }, BX.proxy(this.mouseOver, this));
		BX.bindDelegate(this.obBlock, 'mouseout', { 'attribute': this.dataName }, BX.proxy(this.mouseOut, this));
	}
};

window.JCIblockBrands.prototype.mouseOver = function()
{
	var strValue = '',
		target = BX.proxy_context,
		popup = null,
		popupParams = null;

	if (!!target && target.hasAttribute(this.dataName))
	{
		strValue = target.getAttribute(this.dataName);
		popup = BX(strValue);
		if (!!popup)
		{
			if (!BX.hasClass(target, 'hover'))
			{
				BX.addClass(target, 'hover');
				if (popup.offsetHeight > 40)
				{
					popup.style.top = "-1px";
				}
				else
				{
					popup.style.top = "50%";
					popup.style.marginTop = "-"+parseInt(popup.offsetHeight, 10)/2+"px";
				}
			}
		}
	}
};

window.JCIblockBrands.prototype.mouseOut = function()
{
	var strValue = '',
		target = BX.proxy_context,
		popup = null;
	if (!!target && target.hasAttribute(this.dataName))
	{
		strValue = target.getAttribute(this.dataName);
		popup = BX(strValue);
		if (!!popup)
		{
			BX.removeClass(target, "hover");
		}
	}
};
})(window);