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
		this.multiple = null;
		this.emptyText = null;
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
		init(dropdown)
		{
			this.id = dropdown.id;
			this.dropdown = dropdown;
			this.items = this.getItems();
			this.value = this.getValue();
			this.menuId = this.getMenuId();
			this.multiple = this.getMultiple();
			this.emptyText = this.getEmptyText();
			this.menu = this.createMenu();
			this.menu.popupWindow.show();
			this.adjustPosition();

			BX.bind(this.dropdown, 'click', BX.delegate(this.showMenu, this));
		},

		getMenuId()
		{
			return this.id + '_menu';
		},

		getItems()
		{
			let result;

			try {
				const str = BX.data(this.dropdown, this.dataItems);
				result = eval(str);
			} catch (err) {
				result = [];
			}

			return result;
		},

		// single
		getValue()
		{
			return BX.data(this.dropdown, this.dataValue);
		},

		getValueItem()
		{
			const value = this.getValue();
			return this.getItems().find((item) => item.VALUE === value);
		},

		// multiple
		getValueAsArray()
		{
			let value = this.getValue();
			if (value === undefined)
			{
				value = '';
			}
			return value.toString().split(',').filter((i) => i !== '');
		},

		getValueItems()
		{
			const values = this.getValueAsArray();
			return this.getItems().filter((item) => values.includes(item.VALUE));
		},

		toggleValue(value)
		{
			if (this.multiple)
			{
				if (value || value === 0 || value === '0')
				{
					const values = this.getValueAsArray();
					const index = values.indexOf(value);

					if (index < 0)
					{
						values.push(value);
					}
					else
					{
						values.splice(index, 1);
					}

					this.dropdown.dataset[this.dataValue] = values.join(',');
				}
				else
				{
					this.dropdown.dataset[this.dataValue] = null;
				}
			}
			else
			{
				this.dropdown.dataset[this.dataValue] = value;
			}
		},

		getValueText()
		{
			if (this.multiple)
			{
				return this.getValueItems().map((item) => item.NAME).filter((i) => !!i).join(", ") || this.emptyText;
			}

			const item = this.getValueItem();
			return item ? item.NAME : this.emptyText;
		},

		getMultiple()
		{
			return this.dropdown.dataset.multiple === 'Y';
		},

		getEmptyText()
		{
			return this.dropdown.dataset.emptyText || null;
		},

		prepareMenuItems()
		{
			const self = this;
			let attrs, subItem;
			const currentValue = this.multiple ? this.getValueAsArray() : this.getValue();

			function prepareItems(items)
			{
				const isHtmlEntity = self.dropdown.dataset['htmlEntity'] === 'true';
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
							html: isHtmlEntity ? item.NAME: null,
							text: isHtmlEntity ? null: item.NAME
						})
					]});

					const selected =
						self.multiple
						? currentValue.includes(item.VALUE)
						: currentValue === item.VALUE
					;
					return {
						html: subItem.innerHTML,
						className: selected ? self.selectedClass : self.notSelectedClass,
						delimiter: item.DELIMITER,
						items: 'ITEMS' in item ? prepareItems(item.ITEMS) : null
					};
				});
			}

			const items = prepareItems(this.getItems());
			BX.onCustomEvent(window, 'Dropdown::onPrepareItems', [this.id, this.menuId, items])
			return items;
		},

		createMenu()
		{
			const self = this;

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
						'onPopupShow'() {
							self._onShowMenu();
						}
					}
				}
			);
		},

		showMenu()
		{
			this.menu = BX.PopupMenu.getMenuById(this.menuId);

			if (!this.menu)
			{
				this.menu = this.createMenu();
				this.menu.popupWindow.show();
			}

			this.adjustPosition();
		},

		adjustPosition()
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

		getSubItem(node)
		{
			return BX.Grid.Utils.getByClass(node, this.dropdownItemClass, true);
		},

		refresh(item)
		{
			const subItem = this.getSubItem(item);
			let value = BX.data(subItem, this.dataValue);
			if (BX.Type.isUndefined(value))
			{
				value = '';
			}

			this.toggleValue(value);
			BX.firstChild(this.dropdown).innerText = this.getValueText();
		},

		selectItem(node)
		{
			const self = this;

			(this.menu.menuItems || []).forEach(function(current) {
				// multiple
				if (self.multiple)
				{
					if (node === current.layout.item)
					{
						if (BX.hasClass(node, self.selectedClass))
						{
							BX.addClass(current.layout.item, self.notSelectedClass);
							BX.removeClass(current.layout.item, self.selectedClass);
						}
						else
						{
							BX.removeClass(current.layout.item, self.notSelectedClass);
							BX.addClass(current.layout.item, self.selectedClass);
						}
					}
					return;
				}

				// single
				BX.removeClass(current.layout.item, self.selectedClass);

				if (node !== current.layout.item)
				{
					BX.addClass(current.layout.item, self.notSelectedClass);
				}
				else
				{
					BX.removeClass(current.layout.item, self.notSelectedClass);
					BX.addClass(current.layout.item, self.selectedClass);
				}
			});
		},

		lockedItem(node) {

			BX.addClass(node, this.lockedClass);
		},

		getDataItemIndexByValue(items, value)
		{
			let result;

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

		getDataItemByValue(value)
		{
			const result = this.getItems().filter(function(current) {
				return current.VALUE === value;
			});

			return result.length > 0 ? result[0] : null;
		},

		_onShowMenu()
		{
			const self = this;

			BX.addClass(this.dropdown, this.activeClass);
			(this.menu.menuItems || []).forEach(function(current) {
				BX.bind(current.layout.item, 'click', BX.delegate(self._onItemClick, self));
			});
		},

		_onCloseMenu()
		{
			BX.removeClass(this.dropdown, this.activeClass);
			BX.PopupMenu.destroy(this.menuId);
		},

		_onItemClick(event)
		{
			const item = this.getMenuItem(event.target);
			let value, dataItem;
			const subItem = this.getSubItem(item);
			const isPseudo = BX.data(subItem, 'pseudo');

			if (!(isPseudo === 'true'))
			{
				this.refresh(item);
				this.selectItem(item);

				if (!this.multiple)
				{
					this.menu.popupWindow.close();
				}

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

		getMenuItem(node)
		{
			let item = node;

			if (!BX.hasClass(item, this.menuItemClass))
			{
				item = BX.findParent(item, {class: this.menuItemClass});
			}

			return item;
		}
	};
})();
