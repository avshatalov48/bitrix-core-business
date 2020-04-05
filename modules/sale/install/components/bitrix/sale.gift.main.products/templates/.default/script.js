BX.namespace("BX.Sale");
BX.Sale.GiftMainProductsClass = (function ()
{
	var GiftMainProductsClass = function (parameters)
	{
		this.ajaxUrl = '/bitrix/components/bitrix/sale.gift.main.products/ajax.php';
		this.contextAjaxData = parameters.contextAjaxData || {};
		this.mainProductState = parameters.mainProductState || null;
		this.injectId = parameters.injectId || null;
		this.isGift = !!parameters.isGift;
		this.productId = parameters.productId;
		this.offerId = parameters.offerId;
		this.offers = parameters.offers || [];

		this.setEvents();

		//detect #as_gift
		if(document.location.hash.match(/as_gift/g))
		{
			if(this.isGift)
			{
				this.enableGift();
			}
			else
			{
				this.raiseNonGiftEvent();
			}
		}

		BX.bindDelegate(BX(this.injectId), "click", {tagName: 'a'}, BX.proxy(this.clickNavLink, this));
	};

	GiftMainProductsClass.prototype.clickNavLink = function(e)
	{
		if(this.onPageNavigationByLink(BX.proxy_context))
		{
			return BX.PreventDefault(e);
		}
	};

	GiftMainProductsClass.prototype.setEvents = function()
	{
		BX.addCustomEvent('onCatalogStoreProductChange', BX.proxy(this.onCatalogStoreProductChange, this));
		BX.addCustomEvent('onAddToBasketMainProduct', BX.proxy(this.onAddToBasketMainProduct, this));
	};

	GiftMainProductsClass.prototype.unsubscribeEvents = function()
	{
		BX.removeCustomEvent('onCatalogStoreProductChange', BX.proxy(this.onCatalogStoreProductChange, this));
	};

	GiftMainProductsClass.prototype.onAddToBasketMainProduct = function(productObject)
	{
		this.enableGift();
	};

	GiftMainProductsClass.prototype.onCatalogStoreProductChange = function(offerId)
	{
		if(offerId == this.offerId)
		{
			return;
		}
		BX.ajax({
			url: this.ajaxUrl,
			method: 'POST',
			data: BX.merge(this.contextAjaxData, {offerId: offerId, mainProductState: this.mainProductState, SITE_ID: BX.message('SITE_ID')}),
			dataType: 'html',
			processData: false,
			start: true,
			onsuccess: BX.delegate(function (html) {
				this.offerId = offerId;
				var ob = BX.processHTML(html);
				if(!ob.HTML)
				{
					if(document.location.hash.match(/as_gift/g))
					{
						//raise event from previous state. It's trick.
						if(this.isGift)
						{
							this.raiseGiftEvent();
						}
						else
						{
							this.raiseNonGiftEvent();
						}
					}

					return;
				}
				this.unsubscribeEvents();

				BX(this.injectId).innerHTML = ob.HTML;
				BX.ajax.processScripts(ob.SCRIPT);
			}, this)
		});

	};

	GiftMainProductsClass.prototype.onPageNavigationByLink = function(link)
	{
		var isValidNavigationLink = BX.delegate(function(link)
		{
			if(!BX.type.isElementNode(link) || !link.href)
			{
				return false;
			}
			if(link.href.indexOf(this.ajaxUrl) >= 0)
			{
				return true;
			}
			return link.href.indexOf('PAGEN_') !== -1;
		}, this);

		if(!isValidNavigationLink(link))
		{
			return false;
		}

		BX.ajax({
			url: link.href,
			method: 'POST',
			data: BX.merge(this.contextAjaxData, {SITE_ID: BX.message('SITE_ID')}),
			dataType: 'html',
			processData: false,
			start: true,
			onsuccess: BX.delegate(function (html) {
				var ob = BX.processHTML(html);
				if(!ob.HTML)
				{
					return;
				}
				this.unsubscribeEvents();

				BX(this.injectId).innerHTML = ob.HTML;
				BX.ajax.processScripts(ob.SCRIPT);
			}, this)
		});

		return true;
	};

	GiftMainProductsClass.prototype.enableGift = function()
	{
		this.isGift = true;
		this.raiseGiftEvent();
	};

	GiftMainProductsClass.prototype.raiseGiftEvent = function()
	{
		BX.onCustomEvent('onSaleProductIsGift', [this.productId, this.offerId]);
	};

	GiftMainProductsClass.prototype.raiseNonGiftEvent = function()
	{
		BX.onCustomEvent('onSaleProductIsNotGift', [this.productId, this.offerId]);
	};

	return GiftMainProductsClass;
})();

