(function() {
	"use strict";

	BX.namespace('BX.UI');

	BX.UI.DropdownMenu = function(options)
	{
		this.container = options.container;
		this.items = [];

		// this.init();
	};

	BX.UI.DropdownMenu.prototype = {

		init: function()
		{
			var items = this.container.querySelectorAll('.ui-sidepanel-menu-item');

			for(var i = 0; i < items.length; i++)
			{
				var item = {};

				item.id = i;
				item.container = null;
				item.link = null;
				item.button = null;
				item.submenu = null;

				item.container = items[i];
				item.link = item.container.querySelector('.ui-sidepanel-menu-link');
				item.operativeItem = item.link.getAttribute("bx-operative") === "Y";

				this.items.push(item);

				if (item.container.classList.contains('ui-sidepanel-menu-active'))
				{
					item.activeItem = true;
				}

				if (item.container.querySelector('.ui-sidepanel-menu-notice-icon'))
				{
					item.noticeItem = true;
				}
			}

			this.loadData();
		},

		loadData: function()
		{
			for(var i = 0; i < this.items.length; i++)
			{
				this.addItem(this.items[i]);
			}
		},

		addItem: function(options)
		{
			var item = new BX.UI.DropdownMenuItem(options);
			item.menu = this;

			this.items[options.id] = item;
		},

		resetItems: function()
		{
			for(var i = 0; i < this.items.length; i++)
			{
				if (this.items[i].activeItem)
				{
					this.items[i].reset();
				}
			}
		},

		resetSubItems: function()
		{
			for(var i = 0; i < this.items.length; i++)
			{
				this.items[i].resetSubItems();
			}
		}
	};

	var itemsMap = new WeakMap();

	BX.UI.DropdownMenuItem = function(options)
	{
		this.container = options.container;
		this.link = options.link;
		this.button = null;
		this.activeItem = options.activeItem ? options.activeItem : null;
		this.noticeItem = options.noticeItem ? options.noticeItem : null;
		this.operativeItem = options.operativeItem ? options.operativeItem : null;
		this.submenu = null;
		this.subItems = [];
		this.submenuOpen = false;
		this.newBadge = null;
		this.counter = null;
		this.addItem = null;

		this.init();
		itemsMap.set(this.container, this);
	};

	/**
	 * @param {HTMLElement} node
	 * @returns {BX.UI.DropdownMenuItem} | undefined
	 */
	BX.UI.DropdownMenuItem.getItemByNode = function(node)
	{
		if (BX.Dom.hasClass(node, 'ui-sidepanel-menu-link'))
		{
			return itemsMap.get(node.parentNode);
		}

		return itemsMap.get(node);
	};

	BX.UI.DropdownMenuItem.prototype = {
		init: function()
		{
			if (this.isSubmenuExist())
			{
				this.submenu = this.container.querySelector('.ui-sidepanel-submenu');
				this.button = this.getToggleButton();
				this.link.appendChild(this.button);

				// this.newBadge = this.getNewItemBadge();
				// this.link.appendChild(this.newBadge);
				//
				// this.counter = this.getCounter();
				// this.link.appendChild(this.counter);
			}

			var subItems = this.container.querySelectorAll('.ui-sidepanel-submenu-item'),
				submenuVisibilityStateVisible = false;

			for(var i = 0; i < subItems.length; i++)
			{
				var subItem = {};

				subItem.id = i;
				subItem.container = subItems[i];

				this.subItems.push(subItem);

				if (subItem.container.classList.contains('ui-sidepanel-submenu-active'))
				{
					subItem.activeSubItem = true;
					submenuVisibilityStateVisible = true;
				}

				// var editBtn = subItem.container.querySelector('.ui-sidepanel-edit-btn');
				//
				// editBtn.addEventListener('click', function() {
				// 	if(!subItem.container.classList.contains('ui-sidepanel-submenu-edit-mode'))
				// 	{
				// 		subItem.container.classList.add('ui-sidepanel-submenu-edit-mode');
				// 	}
				// 	else
				// 	{
				// 		subItem.container.classList.remove('ui-sidepanel-submenu-edit-mode');
				// 	}
				// }.bind(this));
			}

			// this.addItem = this.getAddItem();
			// this.submenu.appendChild(this.addItem);

			this.loadData();

			if (this.isSubmenuExist() && (
				this.activeItem === true && this.operativeItem === true ||
				submenuVisibilityStateVisible === true
			))
			{
				this.showSubmenu();
				this.setNewToggleButtonName();
			}

			this.loadData();
			this.addEvents();
		},

		loadData: function()
		{
			for(var i = 0; i < this.subItems.length; i++)
			{
				this.addSubItem(this.subItems[i]);
			}
		},

		activate: function()
		{
			this.activeItem = true;
			this.container.classList.add('ui-sidepanel-menu-active');
		},

		reset: function()
		{
			this.activeItem = null;
			this.container.classList.remove('ui-sidepanel-menu-active');
		},

		addNoticeIcon: function()
		{
			this.noticeItem = true;
			if (!this.container.querySelector('.ui-sidepanel-menu-notice-icon'))
			{
				this.container.children[0].appendChild(this.getNoticeIcon());
			}
		},

		removeNoticeIcon: function()
		{
			this.noticeItem = null;

			if (this.container.querySelector('.ui-sidepanel-menu-notice-icon'))
			{
				this.container.querySelector('.ui-sidepanel-menu-notice-icon').remove();
			}
		},

		getNoticeIcon: function()
		{
			this.noticeIcon = document.createElement('span');
			this.noticeIcon.className = 'ui-sidepanel-menu-notice-icon';

			return this.noticeIcon;
		},

		showSubmenu: function()
		{
			this.submenuOpen = true;
			this.submenu.style.height = this.getSubmenuHeight();
		},

		hideSubmenu: function()
		{
			this.submenuOpen = false;
			this.submenu.style.height = 0;
		},

		getSubmenuHeight: function()
		{
			var subItemsHeight = 0;

			for(var i = 0; i < this.subItems.length; i++)
			{
				subItemsHeight = subItemsHeight + ((this.subItems[i].getHeight() + 6) - 3);
			}

			return subItemsHeight + 'px';
		},

		addEvents: function()
		{
			this.link.addEventListener('click', this.setActiveHandler.bind(this))
		},

		setActiveHandler: function(e)
		{
			this.menu.resetItems();
			this.activate();

			if (this.link.getAttribute('bx-operative') !== 'Y')
			{
				this.link.classList.add('ui-sidepanel-menu-disable-active-state');
			}
			else
			{
				this.link.classList.remove('ui-sidepanel-menu-disable-active-state');
			}

			if (this.isSubmenuExist())
			{
				if (!this.submenuOpen)
				{
					this.showSubmenu();
					this.setNewToggleButtonName();
					this.menu.resetSubItems();
					e && e.preventDefault();
				}
				else
				{
					this.hideSubmenu();
					this.setDefaultToggleButtonName();
					this.menu.resetSubItems();
					e && e.preventDefault();
				}
			}
			else
			{
				if (this.link.classList.contains('ui-sidepanel-menu-disable-active-state'))
				{
					this.link.classList.remove('ui-sidepanel-menu-disable-active-state');
				}
				this.menu.resetSubItems();
			}
		},

		isSubmenuExist: function()
		{
			if (this.container.querySelector('.ui-sidepanel-submenu'))
			{
				return true;
			}

			return false;
		},

		getToggleButton: function()
		{
			this.buttonContainer = document.createElement('div');
			this.buttonContainer.className = 'ui-sidepanel-toggle-btn';
			this.setDefaultToggleButtonName();

			return this.buttonContainer;
		},

		setNewToggleButtonName: function()
		{
			this.buttonContainer.innerHTML = BX.message("UI_SIDEPANEL_MENU_BUTTON_CLOSE");
		},

		setDefaultToggleButtonName: function()
		{
			this.buttonContainer.innerHTML = BX.message("UI_SIDEPANEL_MENU_BUTTON_OPEN");
		},

		getNewItemBadge: function()
		{
			this.itemBadgeNewContainer = document.createElement('div');
			this.itemBadgeNewContainer.className = 'ui-sidepanel-badge-new';

			return this.itemBadgeNewContainer;
		},

		getCounter: function()
		{
			this.counterContainer = document.createElement('span');
			this.counterContainer.className = 'ui-sidepanel-counter';

			return this.counterContainer;
		},

		getAddItem: function()
		{
			this.addItemContainer = document.createElement('a');
			this.addItemContainer.className = 'ui-sidepanel-add-item';
			this.setAddItemName();

			return this.addItemContainer;
		},

		setAddItemName: function()
		{
			this.addItemContainer.innerHTML = BX.message("UI_SIDEPANEL_MENU_ADD_ITEM");
		},

		addSubItem: function(options)
		{
			var item = new BX.UI.DropdownMenuSubItem(options);
			item.subMenu = this;

			this.subItems[options.id] = item;
		},

		resetSubItems: function()
		{
			for(var i = 0; i < this.subItems.length; i++)
			{
				if (this.subItems[i].activeSubItem)
				{
					this.subItems[i].reset();
				}
			}
		},
	};

	BX.UI.DropdownMenuSubItem = function(options)
	{
		this.container = options.container;
		this.id = options.id;
		this.activeSubItem = options.activeSubItem ? options.activeSubItem : null;
		// this.activeSubItem = null;
		this.subMenu = null;

		this.init();
	};

	BX.UI.DropdownMenuSubItem.prototype = {
		init: function()
		{
			this.addEvents();
		},

		activate: function()
		{
			this.activeSubItem = true;
			this.container.classList.add('ui-sidepanel-submenu-active');
		},

		reset: function()
		{
			this.activeSubItem = null;
			this.container.classList.remove('ui-sidepanel-submenu-active');
		},

		addEvents: function()
		{
			this.container.addEventListener('click', function() {

				if (this.activeSubItem)
				{
					return;
				}

				if (!this.activeSubItem && !this.activeItem)
				{
					this.subMenu.menu.resetItems();
				}

				this.subMenu.menu.resetSubItems();
				this.subMenu.resetSubItems();
				this.activate();

			}.bind(this))
		},

		getHeight: function()
		{
			return this.container.offsetHeight;
		},
	};
})();