;(function() {
	'use strict';

	BX.namespace('BX.Main');

	/**
	 * BX.Main.submenu
	 * @type {{data: Array, init: BX.Main.submenu.init, toggleMenu: BX.Main.submenu.toggleMenu, pushMenu: BX.Main.submenu.pushMenu, getMenu: BX.Main.submenu.getMenu, createMenu: BX.Main.submenu.createMenu}}
	 */
	BX.Main.submenu = {
		data: [],
		init: function(anchor, id, level, menuItems)
		{
			this.id = id;
			this.anchor = anchor;
			this.menuItems = menuItems;
			this.level = level;

			this.anchorRect = BX.pos(this.anchor);
			this.toggleMenu();
		},

		toggleMenu: function()
		{
			var currentLeft;
			var tempLeft;
			var tmpWindow;
			var current = this.anchor.menu;
			var currentWindow = current ? this.anchor.menu.popupWindow : null;

			if (current && currentWindow.isShown())
			{
				currentLeft = currentWindow.bindOptions.left;

				this.data.forEach(function(currentMenu) {
					tmpWindow = currentMenu.popupWindow;
					tempLeft = tmpWindow.bindOptions.left;

					if (currentLeft <= tempLeft)
					{
						currentMenu.popupWindow.close();
					}
				});
			}
			else
			{
				BX.PopupMenu.destroy(this.id);
				this.anchor.menu = this.createMenu();
				this.pushMenu(this.anchor.menu);
				this.anchor.menu.popupWindow.adjustPosition(BX.pos(this.anchor));
				this.anchor.menu.popupWindow.show();

				current = this.anchor.menu;
				currentWindow = current.popupWindow;
				currentLeft = currentWindow.bindOptions.left;

				this.data.forEach(function(currentMenu) {
					tmpWindow = currentMenu.popupWindow;
					tempLeft = tmpWindow.bindOptions.left;

					if (currentLeft <= tempLeft && current.id !== currentMenu.id)
					{
						currentMenu.popupWindow.close();
					}
				});
			}
		},

		pushMenu: function(menu)
		{
			var index = this.getMenu(menu);
			if (menu)
			{
				if (index === null)
				{
					this.data.push(menu);
				}
				else
				{
					this.data[index] = menu;
				}
			}
		},

		getMenu: function(menu)
		{
			var index = null;

			this.data.forEach(function(current, key) {
				if (current.id === menu.id)
				{
					index = key;
					return false;
				}
			});

			return index;
		},

		createMenu: function()
		{
			return BX.PopupMenu.create(
				this.id,
				this.anchor,
				this.menuItems,
				{
					'autoHide': true,
					'offsetTop': -((this.anchorRect.height / 2) + 26),
					'offsetLeft': this.anchorRect.width + 4,
					'angle': {
						'position': 'left',
						'offset': ((this.anchorRect.height / 2) - 8)
					}
				}
			);
		}
	};
})();