;(function() {
	'use strict';

	BX.namespace('BX.Main');


	/**
	 * BX.Main.dropdown
	 * @param dropdown
	 */
	BX.Main.dropdown = function(dropdown)
	{
		this.id = null;
		this.dropdown = null;
		this.items = null;
		this.value = null;
		this.menuId = null;
		this.menu = null;
		this.menuItems = null;
		this.dataItems = 'items';
		this.dataValue = 'value';
		this.dataPseudo = 'pseudo';
		this.dropdownItemClass = 'main-dropdown-item';
		this.activeClass = 'main-dropdown-active';
		this.selectedClass = 'main-dropdown-item-selected';
		this.notSelectedClass = 'main-dropdown-item-not-selected';
		this.lockedClass = 'main-dropdown-item-locked';
		this.menuItemClass = 'menu-popup-item';
		this.init(dropdown);
	};

	BX.Main.dropdown.prototype = {
		init: function(dropdown)
		{
			this.id = dropdown.id;
			this.dropdown = dropdown;
			this.items = this.getItems();
			this.value = this.getValue();
			this.menuId = this.getMenuId();
			this.menu = this.createMenu();
			this.menu.popupWindow.show();
			this.adjustPosition();

			BX.bind(this.dropdown, 'click', BX.delegate(this.showMenu, this));
		},

		getMenuId: function()
		{
			return this.id + '_menu';
		},

		getItems: function()
		{
			var result;

			try {
				var str = BX.data(this.dropdown, this.dataItems);
				result = eval(str);
			} catch (err) {
				result = [];
			}

			return result;
		},

		getValue: function()
		{
			return BX.data(this.dropdown, this.dataValue);
		},

		prepareMenuItems: function()
		{
			var self = this;
			var attrs, subItem;
			var currentValue = this.getValue();

			function prepareItems(items)
			{
				return items.map(function(item) {
					attrs = {};
					attrs['data-'+self.dataValue] = item.VALUE;
					attrs['data-'+self.dataPseudo] = 'PSEUDO' in item && item.PSEUDO ? 'true' : 'false';

					subItem = BX.create('div', {children: [
						BX.create('span', {
							props: {
								className: self.dropdownItemClass
							},
							attrs: attrs,
							text: item.NAME
						})
					]});

					return {
						text: subItem.innerHTML,
						className: currentValue === item.VALUE ? self.selectedClass : self.notSelectedClass,
						delimiter: item.DELIMITER,
						items: 'ITEMS' in item ? prepareItems(item.ITEMS) : null
					};
				});
			}

			const items = prepareItems(this.getItems());
			BX.onCustomEvent(window, 'Dropdown::onPrepareItems', [this.id, this.menuId, items])
			return items;
		},

		createMenu: function()
		{
			var self = this;

			return BX.PopupMenu.create(
				this.getMenuId(),
				this.dropdown,
				this.prepareMenuItems(),
				{
					'autoHide': true,
					'offsetTop': -8,
					'offsetLeft': +(this.dropdown.dataset.menuOffsetLeft || 40),
					'maxHeight': +(this.dropdown.dataset.menuMaxHeight || 170),
					'angle': {
						'position': 'bottom',
						'offset': 0
					},
					'events': {
						'onPopupClose': BX.delegate(this._onCloseMenu, this),
						'onPopupShow': function() {
							self._onShowMenu();
						}
					}
				}
			);
		},

		showMenu: function()
		{
			this.menu = BX.PopupMenu.getMenuById(this.menuId);

			if (!this.menu)
			{
				this.menu = this.createMenu();
				this.menu.popupWindow.show();
			}

			this.adjustPosition();
		},

		adjustPosition: function()
		{
			if (this.dropdown.dataset.popupPosition === 'fixed')
			{
				var container = this.menu.popupWindow.popupContainer;

				container.style.setProperty('top', 'auto');
				container.style.setProperty('bottom', '45px');
				container.style.setProperty('left', '0px');

				this.dropdown.appendChild(container);
			}
		},

		getSubItem: function(node)
		{
			return BX.Grid.Utils.getByClass(node, this.dropdownItemClass, true);
		},

		refresh: function(item)
		{
			var subItem = this.getSubItem(item);
			var value = BX.data(subItem, this.dataValue);

			BX.firstChild(this.dropdown).innerText = subItem.innerText;
			this.dropdown.dataset[this.dataValue] = value;
		},

		selectItem: function(node)
		{
			var self = this;

			(this.menu.menuItems || []).forEach(function(current) {
				BX.removeClass(current.layout.item, self.selectedClass);

				if (node !== current.layout.item)
				{
					BX.addClass(current.layout.item, self.notSelectedClass);
				}
				else
				{
					BX.removeClass(current.layout.item, self.notSelectedClass);
				}
			});

			BX.addClass(node, this.selectedClass);
		},

		lockedItem: function(node) {

			BX.addClass(node, this.lockedClass);
		},

		getDataItemIndexByValue: function(items, value)
		{
			var result;

			if (BX.type.isArray(items))
			{
				items.map(function(current, index) {
					if (current.VALUE === value)
					{
						result = index;
						return false;
					}
				});
			}

			return false;
		},

		getDataItemByValue: function(value)
		{
			var result = this.getItems().filter(function(current) {
				return current.VALUE === value;
			});

			return result.length > 0 ? result[0] : null;
		},

		_onShowMenu: function()
		{
			var self = this;

			BX.addClass(this.dropdown, this.activeClass);
			(this.menu.menuItems || []).forEach(function(current) {
				BX.bind(current.layout.item, 'click', BX.delegate(self._onItemClick, self));
			});
		},

		_onCloseMenu: function()
		{
			BX.removeClass(this.dropdown, this.activeClass);
			BX.PopupMenu.destroy(this.menuId);
		},

		_onItemClick: function(event)
		{
			var item = this.getMenuItem(event.target);
			var value, dataItem;
			var subItem = this.getSubItem(item);
			var isPseudo = BX.data(subItem, 'pseudo');

			if (!(isPseudo === 'true'))
			{
				this.refresh(item);
				this.selectItem(item);
				this.menu.popupWindow.close();
				value = this.getValue();
				dataItem = this.getDataItemByValue(value);
			}
			else
			{
				value = BX.data(subItem, 'value');
				dataItem = this.getDataItemByValue(value);
			}

			event.stopPropagation();

			BX.onCustomEvent(window, 'Dropdown::change', [this.dropdown.id, event, item, dataItem, value]);
		},

		getMenuItem: function(node)
		{
			var item = node;

			if (!BX.hasClass(item, this.menuItemClass))
			{
				item = BX.findParent(item, {class: this.menuItemClass});
			}

			return item;
		}
	};
})();
