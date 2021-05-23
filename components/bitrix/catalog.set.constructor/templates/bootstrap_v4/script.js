BX.namespace("BX.Catalog.SetConstructor");

BX.Catalog.SetConstructor = (function()
{
	var SetConstructor = function(params)
	{
		this.numSliderItems = params.numSliderItems || 0;
		this.numSetItems = params.numSetItems || 0;
		this.jsId = params.jsId || "";
		this.ajaxPath = params.ajaxPath || "";
		this.currency = params.currency || "";
		this.lid = params.lid || "";
		this.iblockId = params.iblockId || "";
		this.basketUrl = params.basketUrl || "";
		this.setIds = params.setIds || null;
		this.offersCartProps = params.offersCartProps || null;
		this.itemsRatio = params.itemsRatio || null;
		this.noFotoSrc = params.noFotoSrc || "";
		this.messages = params.messages;

		this.canBuy = params.canBuy;
		this.mainElementPrice = params.mainElementPrice || 0;
		this.mainElementOldPrice = params.mainElementOldPrice || 0;
		this.mainElementDiffPrice = params.mainElementDiffPrice || 0;
		this.mainElementBasketQuantity = params.mainElementBasketQuantity || 1;

		this.parentCont = BX(params.parentContId) || null;
		this.sliderParentCont = this.parentCont.querySelector("[data-role='slider-parent-container']");
		this.sliderItemsCont = this.parentCont.querySelector("[data-role='set-other-items']");
		this.setItemsCont = this.parentCont.querySelector("[data-role='set-items']");

		this.setPriceCont = this.parentCont.querySelector("[data-role='set-price']");
		this.setPriceDuplicateCont = this.parentCont.querySelector("[data-role='set-price-duplicate']");
		this.setOldPriceCont = this.parentCont.querySelector("[data-role='set-old-price']");
		this.setOldPriceRow = this.setOldPriceCont.parentNode.parentNode;
		this.setDiffPriceCont = this.parentCont.querySelector("[data-role='set-diff-price']");
		this.setDiffPriceRow = this.setDiffPriceCont.parentNode.parentNode;

		this.notAvailProduct = this.sliderItemsCont.querySelector("[data-not-avail='yes']");

		this.emptySetMessage = this.parentCont.querySelector("[data-set-message='empty-set']");

		BX.bindDelegate(this.setItemsCont, 'click', { 'attribute': 'data-role' }, BX.proxy(this.deleteFromSet, this));
		BX.bindDelegate(this.sliderItemsCont, 'click', { 'attribute': 'data-role' }, BX.proxy(this.addToSet, this));

		var buyButton = this.parentCont.querySelector("[data-role='set-buy-btn']");

		if (this.canBuy)
		{
			BX.show(buyButton);
			BX.bind(buyButton, "click", BX.proxy(this.addToBasket, this));
		}
		else
		{
			BX.hide(buyButton);
		}

		this.generateSliderStyles();
	};

	SetConstructor.prototype.generateSliderStyles = function()
	{
		var styleNode = BX.create("style", {
			html:	".catalog-set-constructor-slider-slide-"+this.jsId+"{width: " + this.numSliderItems*25 + "%;}"+
					".catalog-set-constructor-slider-item-container-"+this.jsId+"{width: " + (100/this.numSliderItems) + "%;}"+
					"@media (max-width:767px){"+
					".catalog-set-constructor-slider-slide-"+this.jsId+"{width: " + this.numSliderItems*20*3 + "%;}}",
			attrs: {
				id: "bx-set-const-style-" + this.jsId
			}});

		if (BX("bx-set-const-style-" + this.jsId))
		{
			BX.remove(BX("bx-set-const-style-" + this.jsId));
		}

		this.parentCont.appendChild(styleNode);
	};

	SetConstructor.prototype.deleteFromSet = function()
	{
		var target = BX.proxy_context,
			item,
			itemId,
			itemName,
			itemUrl,
			itemImg,
			itemPrintPrice,
			itemPrice,
			itemPrintOldPrice,
			itemOldPrice,
			itemDiffPrice,
			itemMeasure,
			itemBasketQuantity,
			i,
			l,
			newSliderNode;

		if (target && target.hasAttribute('data-role') && target.getAttribute('data-role') == 'set-delete-btn')
		{
			item = target.parentNode.parentNode;

			itemId = item.getAttribute("data-id");
			itemName = item.getAttribute("data-name");
			itemUrl = item.getAttribute("data-url");
			itemImg = item.getAttribute("data-img");
			itemPrintPrice = item.getAttribute("data-print-price");
			itemPrice = item.getAttribute("data-price");
			itemPrintOldPrice = item.getAttribute("data-print-old-price");
			itemOldPrice = item.getAttribute("data-old-price");
			itemDiffPrice = item.getAttribute("data-diff-price");
			itemMeasure = item.getAttribute("data-measure");
			itemBasketQuantity = item.getAttribute("data-quantity");

			newSliderNode = BX.create("div", {
				attrs: {
					className: "catalog-set-constructor-slider-item-container catalog-set-constructor-slider-item-container-" + this.jsId,
					"data-id": itemId,
					"data-img": itemImg ? itemImg : "",
					"data-url": itemUrl,
					"data-name": itemName,
					"data-print-price": itemPrintPrice,
					"data-print-old-price": itemPrintOldPrice,
					"data-price": itemPrice,
					"data-old-price": itemOldPrice,
					"data-diff-price": itemDiffPrice,
					"data-measure": itemMeasure,
					"data-quantity": itemBasketQuantity
				},
				children: [
					BX.create("div", {
							attrs: {
								className: "catalog-set-constructor-slider-item"
							},
							children: [
								BX.create("div", {
									attrs: {
										className: "catalog-set-constructor-slider-item-img"
									},
									children: [
										BX.create("div", {
											attrs: {
												className: "catalog-set-constructor-slider-item-img-container"
											},
											children: [
												BX.create("img", {
													attrs: {
														src: itemImg ? itemImg : this.noFotoSrc,
														className: "img-responsive"
													}
												})
											]
										})
									]
								}),
								BX.create("div", {
									attrs: {
										className: "catalog-set-constructor-slider-item-title"
									},
									children: [
										BX.create("a", {
											attrs: {
												href: itemUrl
											},
											html: itemName
										})
									]
								}),
								BX.create("div", {
									attrs: {
										className: "catalog-set-constructor-slider-item-price"
									},
									children: [
										BX.create("div", {
											attrs: {
												className: "catalog-set-constructor-slider-item-price-new"
											},
											html: itemPrintPrice + ' * ' + itemBasketQuantity + itemMeasure
										}),
										BX.create("div", {
											attrs: {
												className: "catalog-set-constructor-slider-item-price-old"
											},
											html: itemPrice != itemOldPrice ? itemPrintOldPrice : ""
										})
									]
								}),
								BX.create("div", {
									attrs: {
										className: "catalog-set-constructor-slider-item-add-btn"
									},
									children: [
										BX.create("a", {
											attrs: {
												className: "btn btn-default btn-sm",
												"data-role": "set-add-btn"
											},
											html: this.messages.ADD_BUTTON
										})
									]
								})
							]
						}
					)]
			});

			if (!!this.notAvailProduct)
				this.sliderItemsCont.insertBefore(newSliderNode, this.notAvailProduct);
			else
				this.sliderItemsCont.appendChild(newSliderNode);

			this.numSliderItems++;
			this.numSetItems--;
			this.generateSliderStyles();
			BX.remove(item);

			for (i = 0, l = this.setIds.length; i < l; i++)
			{
				if (this.setIds[i] == itemId)
					this.setIds.splice(i, 1);
			}

			this.recountPrice();

			if (this.numSetItems <= 0 && !!this.emptySetMessage)
				BX.adjust(this.emptySetMessage, { style: { display: 'inline-block' }, html: this.messages.EMPTY_SET });

			if (this.numSliderItems > 0 && this.sliderParentCont)
			{
				this.sliderParentCont.style.display = '';
			}
		}
	};

	SetConstructor.prototype.addToSet = function()
	{
		var target = BX.proxy_context,
			item,
			itemId,
			itemName,
			itemUrl,
			itemImg,
			itemPrintPrice,
			itemPrice,
			itemPrintOldPrice,
			itemOldPrice,
			itemDiffPrice,
			itemMeasure,
			itemBasketQuantity,
			newSetNode;

		if (!!target && target.hasAttribute('data-role') && target.getAttribute('data-role') == 'set-add-btn')
		{
			item = target.parentNode.parentNode.parentNode;

			itemId = item.getAttribute("data-id");
			itemName = item.getAttribute("data-name");
			itemUrl = item.getAttribute("data-url");
			itemImg = item.getAttribute("data-img");
			itemPrintPrice = item.getAttribute("data-print-price");
			itemPrice = item.getAttribute("data-price");
			itemPrintOldPrice = item.getAttribute("data-print-old-price");
			itemOldPrice = item.getAttribute("data-old-price");
			itemDiffPrice = item.getAttribute("data-diff-price");
			itemMeasure = item.getAttribute("data-measure");
			itemBasketQuantity = item.getAttribute("data-quantity");

			newSetNode = BX.create("tr", {
					attrs: {
						"data-id": itemId,
						"data-img": itemImg ? itemImg : "",
						"data-url": itemUrl,
						"data-name": itemName,
						"data-print-price": itemPrintPrice,
						"data-print-old-price": itemPrintOldPrice,
						"data-price": itemPrice,
						"data-old-price": itemOldPrice,
						"data-diff-price": itemDiffPrice,
						"data-measure": itemMeasure,
						"data-quantity": itemBasketQuantity
					},
					children: [
						BX.create("td", {
							attrs: {
								className: "catalog-set-constructor-items-list-table-cell-img"
							},
							children: [
								BX.create("img", {
									attrs: {
										src: itemImg ? itemImg : this.noFotoSrc,
										className: "img-responsive"
									}
								})
							]
						}),
						BX.create("td", {
							attrs: {
								className: "catalog-set-constructor-items-list-table-cell-name"
							},
							children: [
								BX.create("a", {
									attrs: {
										href: itemUrl
									},
									html: itemName
								})
							]
						}),
						BX.create("td", {
							attrs: {
								className: "catalog-set-constructor-items-list-table-cell-price"
							},
							children: [
								BX.create("span", {
									attrs: {
										className: "catalog-set-constructor-product-new-price"
									},
									html: itemPrintPrice + ' * ' + itemBasketQuantity + itemMeasure
								}),
								BX.create("br"),
								BX.create("span", {
									attrs: {
										className: "catalog-set-constructor-product-old-price"
									},
									html: itemPrice != itemOldPrice ? itemPrintOldPrice : ""
								})
							]
						}),
						BX.create("td", {
							attrs: {
								className: "catalog-set-constructor-items-list-table-cell-del"
							},
							children: [
								BX.create("div", {
									attrs: {
										className: "catalog-set-constructor-items-list-table-cell-delete-btn",
										"data-role": "set-delete-btn"
									}
								})
							]
						})
					]
				}
			);
			this.setItemsCont.appendChild(newSetNode);

			this.numSliderItems--;
			this.numSetItems++;
			this.generateSliderStyles();
			BX.remove(item);
			this.setIds.push(itemId);
			this.recountPrice();

			if (this.numSetItems > 0 && !!this.emptySetMessage)
				BX.adjust(this.emptySetMessage, { style: { display: 'none' }, html: '' });

			if (this.numSliderItems <= 0 && this.sliderParentCont)
			{
				this.sliderParentCont.style.display = 'none';
			}
		}
	};

	SetConstructor.prototype.recountPrice = function()
	{
		var sumPrice = this.mainElementPrice*this.mainElementBasketQuantity,
			sumOldPrice = this.mainElementOldPrice*this.mainElementBasketQuantity,
			sumDiffDiscountPrice = this.mainElementDiffPrice*this.mainElementBasketQuantity,
			setItems = BX.findChildren(this.setItemsCont, {tagName: "tr"}, true),
			i,
			l,
			ratio;
		if (setItems)
		{
			for(i = 0, l = setItems.length; i<l; i++)
			{
				ratio = Number(setItems[i].getAttribute("data-quantity")) || 1;
				sumPrice += Number(setItems[i].getAttribute("data-price"))*ratio;
				sumOldPrice += Number(setItems[i].getAttribute("data-old-price"))*ratio;
				sumDiffDiscountPrice += Number(setItems[i].getAttribute("data-diff-price"))*ratio;
			}
		}

		this.setPriceCont.innerHTML = BX.Currency.currencyFormat(sumPrice, this.currency, true);
		this.setPriceDuplicateCont.innerHTML = BX.Currency.currencyFormat(sumPrice, this.currency, true);
		if (Math.floor(sumDiffDiscountPrice*100) > 0)
		{
			this.setOldPriceCont.innerHTML = BX.Currency.currencyFormat(sumOldPrice, this.currency, true);
			this.setDiffPriceCont.innerHTML = BX.Currency.currencyFormat(sumDiffDiscountPrice, this.currency, true);
			BX.style(this.setOldPriceRow, 'display', 'table-row');
			BX.style(this.setDiffPriceRow, 'display', 'table-row');
		}
		else
		{
			BX.style(this.setOldPriceRow, 'display', 'none');
			BX.style(this.setDiffPriceRow, 'display', 'none');
			this.setOldPriceCont.innerHTML = '';
			this.setDiffPriceCont.innerHTML = '';
		}
	};

	SetConstructor.prototype.addToBasket = function()
	{
		var target = BX.proxy_context;

		BX.showWait(target.parentNode);

		BX.ajax.post(
			this.ajaxPath,
			{
				sessid: BX.bitrix_sessid(),
				action: 'catalogSetAdd2Basket',
				set_ids: this.setIds,
				lid: this.lid,
				iblockId: this.iblockId,
				setOffersCartProps: this.offersCartProps,
				itemsRatio: this.itemsRatio
			},
			BX.proxy(function(result)
			{
				BX.closeWait();
				document.location.href = this.basketUrl;
			}, this)
		);
	};

	return SetConstructor;
})();