BX.namespace('BX.Catalog.Admin');
BX.Catalog.Admin.IblockCatalog = (function()
{
var classDescription = function(params)
{
	this.errorCode = 0;
	this.containerId = (typeof(params.containerId) !== 'undefined' && params.containerId ? params.containerId : '');
	if (!this.containerId)
		this.errorCode = -1;
	this.enableSaleRecurring = !(typeof(params.enableSaleRecurring) !== 'undefined' && params.enableSaleRecurring === false);
	this.isSku = (typeof(params.isSku) !== 'undefined' && params.isSku === true);
	this.rows = {
		subscription: null,
		yandexExport: null,
		vat: null,
		skuData: null
	};
	this.controls = {
		catalog: null,
		useSku: null,
		sku: null,
		subscription: null,
		yandexExport: null
	};

	if (this.errorCode === 0)
		BX.ready(BX.proxy(this.init, this));
};

classDescription.prototype.init = function()
{
	if (this.enableSaleRecurring)
		this.rows.subscription = BX('tr_SUBSCRIPTION');
	this.rows.yandexExport = BX('tr_YANDEX_EXPORT');
	this.rows.vat = BX('tr_VAT_ID');

	this.controls.catalog = BX('CATALOG_Y');
	if (!this.isSku)
	{
		this.controls.useSku = BX('USE_SKU_Y');
		this.rows.skuData = BX('sku_data');
	}
	if (this.enableSaleRecurring)
	{
		this.controls.subscription = BX('SUBSCRIPTION_Y');
	}
	this.controls.yandexExport = BX('YANDEX_EXPORT_Y');

	if (this.errorCode !== 0)
		return;

	BX.bindDelegate(BX(this.containerId), 'click', { 'attribute': 'data-checkbox' }, BX.proxy(this.clickHandler, this))
};

classDescription.prototype.clickHandler = function()
{
	var target = BX.proxy_context,
		displayMode;

	if (target.id === this.controls.catalog.id)
	{
		displayMode = (target.checked ? 'table-row' : 'none');
		if (this.enableSaleRecurring && this.rows.subscription)
		{
			BX.adjust(this.rows.subscription, {style: {display: displayMode}});
			if (!target.checked)
				this.controls.subscription.checked = false;
		}
		BX.adjust(this.rows.yandexExport, { style: { display: displayMode } });
		BX.adjust(this.rows.vat, { style: { display: displayMode } });
		if (!this.isSku && this.controls.useSku)
			this.controls.useSku.disabled = false;
		if (!target.checked)
			this.controls.yandexExport.checked = false;
	}
	else if (!this.isSku && target.id === this.controls.useSku.id && !this.controls.useSku.disabled)
	{
		displayMode = (target.checked ? 'block' : 'none');
		BX.adjust(this.rows.skuData, { style: { display: displayMode } });
		if (this.enableSaleRecurring && this.controls.subscription)
			this.controls.subscription.disabled = target.checked;
	}
	else if (this.enableSaleRecurring && target.id === this.controls.subscription.id && !this.controls.subscription.disabled)
	{
		if (!this.isSku && this.controls.useSku)
			this.controls.useSku.disabled = target.checked;
	}
};

classDescription.prototype.destroy = function()
{
	this.rows.subscription = null;
	this.rows.yandexExport = null;
	this.rows.vat = null;
	this.rows.skuData = null;

	this.controls.catalog = null;
	this.controls.useSku = null;
	this.controls.sku = null;
	this.controls.subscription = null;
	this.controls.yandexExport = null;
};

return classDescription;
})();