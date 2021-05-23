(function(window) {

	if (!window.BX || BX.CatalogVertMenu)
		return;

	BX.CatalogVertMenu = {
		items : {},
		idCnt : 1,
		currentItem : null,
		overItem : null,
		outItem : null,
		timeoutOver : null,
		timeoutOut : null,

		getItem : function(item)
		{
			if (!BX.type.isDomNode(item))
				return null;

			var id = !item.id || !BX.type.isNotEmptyString(item.id) ? (item.id = "menu-item-vert-" + this.idCnt++) : item.id;

			if (!this.items[id])
				this.items[id] = new CatalogVertMenuItem(item);

			return this.items[id];
		},

		itemOver : function(item)
		{
			var menuItem = this.getItem(item);
			if (!menuItem)
				return;

			if (this.outItem == menuItem)
			{
				clearTimeout(menuItem.timeoutOut);
			}

			this.overItem = menuItem;

			if (menuItem.timeoutOver)
			{
				clearTimeout(menuItem.timeoutOver);
			}

			menuItem.timeoutOver = setTimeout(function() {
				if (BX.CatalogVertMenu.overItem == menuItem)
				{
					menuItem.itemOver();
				}
			}, 100);
		},

		itemOut : function(item)
		{
			var menuItem = this.getItem(item);
			if (!menuItem)
				return;

			this.outItem = menuItem;

			if (menuItem.timeoutOut)
			{
				clearTimeout(menuItem.timeoutOut);
			}

			menuItem.timeoutOut = setTimeout(function() {

				if (menuItem != BX.CatalogVertMenu.overItem)
				{
					menuItem.itemOut();
				}
				if (menuItem == BX.CatalogVertMenu.outItem)
				{
					menuItem.itemOut();
				}

			}, 100);
		},

		changeSectionPicture : function(element)
		{
			var descrSpan = BX.nextSibling(element);
			var curDescr = descrSpan.innerHTML || '';
			if (descrSpan)
			{
				var curImgWrapObj = BX.nextSibling(descrSpan);
				var curImgObj = BX.clone(BX.firstChild(curImgWrapObj));
			}
			var parentObj = BX.hasClass(element, 'bx_hma_one_lvl') ? element : BX.findParent(element, {className:'bx_hma_one_lvl'});
			var sectionImgObj = BX.findChild(parentObj, {className:'bx_section_picture'}, true, false);
			sectionImgObj.innerHTML = "";
			if (curImgObj)
			{
				sectionImgObj.appendChild(curImgObj);
			}
			var sectionDescrObj = BX.findChild(parentObj, {className:'bx_section_description'}, true, false);
			sectionDescrObj.innerHTML = curDescr;
			BX.previousSibling(sectionDescrObj).innerHTML = element.innerHTML;
			sectionImgObj.parentNode.href = element.href;
		}
	};

	var CatalogVertMenuItem = function(item)
	{
		this.element = item;
		this.popup = BX.findChild(item, { className: "bx_children_container" }, false, false);
	};

	CatalogVertMenuItem.prototype.itemOver = function()
	{
		if (!BX.hasClass(this.element, "hover"))
		{
			BX.addClass(this.element, "hover");
		}
	};

	CatalogVertMenuItem.prototype.itemOut = function()
	{
		BX.removeClass(this.element, "hover");
	};

})(window);

