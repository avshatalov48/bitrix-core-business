(function(window) {

	if (!window.BX || BX.CatalogMenu)
		return;

	BX.CatalogMenu = {
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

			var id = !item.id || !BX.type.isNotEmptyString(item.id) ? (item.id = "menu-item-" + this.idCnt++) : item.id;

			if (!this.items[id])
				this.items[id] = new CatalogMenuItem(item);

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
				if (BX.CatalogMenu.overItem == menuItem)
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

				if (menuItem != BX.CatalogMenu.overItem)
				{
					menuItem.itemOut();
				}
				if (menuItem == BX.CatalogMenu.outItem)
				{
					menuItem.itemOut();
				}

			}, 100);
		},

		removeHover : function(curItem)
		{
			if (typeof curItem !== "object")
				return false;

			var items = curItem.parentNode.querySelectorAll('[data-role="bx-menu-item"]');
			for (var i=0; i<items.length; i++)
			{
				if (curItem == items[i])
					continue;

				if (BX.hasClass(items[i], "bx-hover"))
					BX.removeClass(items[i], "bx-hover")
			}
		}
	};

	var CatalogMenuItem = function(item)
	{
		this.element = item;
		this.popup = BX.findChild(item, { className: "bx_children_container" }, false, false);
		this.isLastItem = BX.lastChild(this.element.parentNode) == this.element;
	};

	CatalogMenuItem.prototype.itemOver = function()
	{
		if (!BX.hasClass(this.element, "bx-hover"))
		{
			BX.addClass(this.element, "bx-hover");

			var popup = BX.findChild(this.element, {className:"bx-nav-2-lvl-container"}, true, false);
			if (popup)
			{
				var popupRightEdge = popup.getBoundingClientRect().left + popup.offsetWidth;
				if (popupRightEdge > document.body.clientWidth)
					popup.style.right = 0;
			}
		}
	};

	CatalogMenuItem.prototype.itemOut = function()
	{
		BX.removeClass(this.element, "bx-hover");
	};
})(window);

BX.namespace("BX.Main.MenuComponent");
BX.Main.MenuComponent.CatalogHorizontal = (function()
{
	var CatalogHorizontal = function(menuBlockId, itemImgDesc)
	{
		this.menuBlockId = menuBlockId || "";
		this.itemImgDesc = itemImgDesc || "";

		this.resizeMenu();
		BX.bind(window, "resize", BX.proxy(this.resizeMenu, this));
	};

	CatalogHorizontal.prototype.clickInMobile = function(element, event)
	{
		if (BX.findParent(element, {className: "bx-aside-nav"}, true))
			return;

		event.preventDefault();
		element.onclick = '';
		return false;
	};

	CatalogHorizontal.prototype.toggleInMobile = function(element)
	{
		var parentObj = BX.findParent(element, {className: "bx-nav-parent"});
		var arrow = element.firstChild;
		if (BX.hasClass(parentObj, "bx-opened"))
		{
			BX.removeClass(parentObj, "bx-opened");
			BX.removeClass(arrow, "fa-angle-down");
			BX.addClass(arrow, "fa-angle-left");
		}
		else
		{
			BX.addClass(parentObj, "bx-opened");
			BX.addClass(arrow, "fa-angle-down");
			BX.removeClass(arrow, "fa-angle-left");
		}
	};

	CatalogHorizontal.prototype.resizeMenu = function()
	{
		var templateWrap = this.templateWrap;
		var menuMobile = document.body.querySelector("[data-role='bx-menu-mobile']");
		var menuMobileButton = document.body.querySelector("[data-role='bx-menu-button-mobile']");

		if (document.body.clientWidth <= 767) //mobile
		{
			if (!menuMobile)
			{
				menuMobile = BX.create("div", {
					attrs: {
						className: "bx-aside-nav",
						"data-role" : "bx-menu-mobile"
					},
					children: [ BX.clone(BX("ul_" + this.menuBlockId)) ]
				});
				document.body.insertBefore(menuMobile, document.body.firstChild);
			}

			if (!menuMobileButton)
			{
				menuMobileButton = BX.create("div", {
					attrs: {className: "bx-aside-nav-control bx-closed", "data-role" : "bx-menu-button-mobile"},
					children: [
						BX.create("i", {
							attrs: {className: "fa fa-bars"}
						})
					],
					events: {
						"click" : function() {
							if (BX.hasClass(this, "bx-opened"))
							{
								BX.removeClass(this, 'bx-opened');
								BX.removeClass(menuMobile, 'bx-opened');
								BX.addClass(this, 'bx-closed');
								document.body.style.overflow = "";
								BX.removeClass(document.body, 'bx-opened');
							}
							else
							{

								BX.addClass(this, 'bx-opened');
								BX.addClass(menuMobile, 'bx-opened');
								BX.removeClass(this, 'bx-closed');
								document.body.style.overflow = "hidden";
								BX.addClass(document.body, 'bx-opened');
							}
						}
					}
				});

				document.body.insertBefore(menuMobileButton, document.body.firstChild);
			}
		}
		else
		{
			BX.removeClass(menuMobile, 'bx-opened');
			BX.removeClass(document.body, 'bx-opened');
			document.body.style.overflow = "";

			if (menuMobileButton)
				BX.removeClass(menuMobileButton, 'bx-opened');
		}
	};

	CatalogHorizontal.prototype.changeSectionPicure = function(element, itemId)
	{
		var curLi = BX.findParent(element, {className: "bx-nav-1-lvl"});
		if (!curLi)
			return;

		var imgDescObj = curLi.querySelector("[data-role='desc-img-block']");
		if (!imgDescObj)
			return;

		var imgObj = BX.findChild(imgDescObj, {tagName: "img"}, true, false);
		if (imgObj)
			imgObj.src = this.itemImgDesc[itemId]["PICTURE"];

		var linkObj = BX.findChild(imgDescObj, {tagName: "a"}, true, false);
		if (linkObj)
			linkObj.href = element.href;

		var descObj = BX.findChild(imgDescObj, {tagName: "p"}, true, false);
		if (descObj)
			descObj.innerHTML = this.itemImgDesc[itemId]["DESC"];

	};

	return CatalogHorizontal;
})();



