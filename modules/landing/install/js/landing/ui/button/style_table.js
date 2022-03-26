;(function() {
	"use strict";

	BX.namespace("BX.Landing.UI.Button");

	BX.Landing.UI.Button.StyleTable = function(id, options)
	{
		BX.Landing.UI.Button.EditorAction.apply(this, arguments);
		this.options = options;
	};

	BX.Landing.UI.Button.StyleTable.prototype = {
		constructor: BX.Landing.UI.Button.StyleTable,
		__proto__: BX.Landing.UI.Button.EditorAction.prototype,

		onClick: function(event)
		{
			event.preventDefault();
			event.stopPropagation();

			var table = this.options.table;
			var options = [];
			options.table = table;
			options.setTd = this.options.setTd;
			options.target = this.options.target;
			if (!this.menu)
			{
				this.menu = new BX.PopupMenuWindow({
					id: "change-table-style-menu-" + BX.Text.getRandom(),
					bindElement: this.layout,
					zIndex: -678,
					items: [
						new BX.PopupMenuItem({
							id: "style1",
							text: BX.Landing.Loc.getMessage("LANDING_TABLE_STYLE_1"),
							onclick: this.onChange,
							table: table,
							options: options,
						}),
						new BX.PopupMenuItem({
							id: "style2",
							text: BX.Landing.Loc.getMessage("LANDING_TABLE_STYLE_2"),
							onclick: this.onChange,
							table: table,
							options: options,
						}),
						new BX.PopupMenuItem({
							id: "style3",
							text: BX.Landing.Loc.getMessage("LANDING_TABLE_STYLE_3"),
							onclick: this.onChange,
							table: table,
							options: options,
						}),
						new BX.PopupMenuItem({
							id: "style4",
							text: BX.Landing.Loc.getMessage("LANDING_TABLE_STYLE_4"),
							onclick: this.onChange,
							table: table,
							options: options,
						}),
						new BX.PopupMenuItem({
							id: "style5",
							text: BX.Landing.Loc.getMessage("LANDING_TABLE_STYLE_5"),
							onclick: this.onChange,
							table: table,
							options: options,
						}),
						new BX.PopupMenuItem({
							id: "style6",
							text: BX.Landing.Loc.getMessage("LANDING_TABLE_STYLE_6"),
							onclick: this.onChange,
							table: table,
							options: options,
						}),
						new BX.PopupMenuItem({
							id: "style7",
							text: BX.Landing.Loc.getMessage("LANDING_TABLE_STYLE_7"),
							onclick: this.onChange,
							table: table,
							options: options,
						}),
						new BX.PopupMenuItem({
							id: "style8",
							text: BX.Landing.Loc.getMessage("LANDING_TABLE_STYLE_8"),
							onclick: this.onChange,
							table: table,
							options: options,
						}),
						new BX.PopupMenuItem({
							id: "style9",
							text: BX.Landing.Loc.getMessage("LANDING_TABLE_STYLE_9"),
							onclick: this.onChange,
							table: table,
							options: options,
						}),
						new BX.PopupMenuItem({
							id: "style10",
							text: BX.Landing.Loc.getMessage("LANDING_TABLE_STYLE_10"),
							onclick: this.onChange,
							table: table,
							options: options,
						}),
					]
				});
			}
			var menuItems = this.menu.menuItems;
			menuItems.forEach(function(menuItem) {
				menuItem.menuItems = menuItems;
			});
			if (table.classList.contains('landing-table-style-1'))
			{
				menuItems[0].layout.item.style.fontWeight = "bold";
			}
			if (table.classList.contains('landing-table-style-2'))
			{
				menuItems[1].layout.item.style.fontWeight = "bold";
			}
			if (table.classList.contains('landing-table-style-3'))
			{
				menuItems[2].layout.item.style.fontWeight = "bold";
			}
			if (table.classList.contains('landing-table-style-4'))
			{
				menuItems[3].layout.item.style.fontWeight = "bold";
			}
			if (table.classList.contains('landing-table-style-5'))
			{
				menuItems[4].layout.item.style.fontWeight = "bold";
			}
			if (table.classList.contains('landing-table-style-6'))
			{
				menuItems[5].layout.item.style.fontWeight = "bold";
			}
			if (table.classList.contains('landing-table-style-7'))
			{
				menuItems[6].layout.item.style.fontWeight = "bold";
			}
			if (table.classList.contains('landing-table-style-8'))
			{
				menuItems[7].layout.item.style.fontWeight = "bold";
			}
			if (table.classList.contains('landing-table-style-9'))
			{
				menuItems[8].layout.item.style.fontWeight = "bold";
			}
			if (table.classList.contains('landing-table-style-10'))
			{
				menuItems[9].layout.item.style.fontWeight = "bold";
			}
			if (this.menu.popupWindow.isShown())
			{
				this.menu.close();
			}
			else
			{
				this.menu.show();
			}
		},

		onChange: function(event, menuItem)
		{
			event.stopPropagation();
			menuItem.menuWindow.close();

			var newTableStyle;
			var styleNumber;
			var setTableStyles = [
				'landing-table-style-1',
				'landing-table-style-2',
				'landing-table-style-3',
				'landing-table-style-4',
				'landing-table-style-5',
				'landing-table-style-6',
				'landing-table-style-7',
				'landing-table-style-8',
				'landing-table-style-9',
				'landing-table-style-10',
			];
			switch (menuItem.id) {
				case 'style1':
					newTableStyle = setTableStyles[0];
					styleNumber = 0;
					break;
				case 'style2':
					newTableStyle = setTableStyles[1];
					styleNumber = 1;
					break;
				case 'style3':
					newTableStyle = setTableStyles[2];
					styleNumber = 2;
					break;
				case 'style4':
					newTableStyle = setTableStyles[3];
					styleNumber = 3;
					break;
				case 'style5':
					newTableStyle = setTableStyles[4];
					styleNumber = 4;
					break;
				case 'style6':
					newTableStyle = setTableStyles[5];
					styleNumber = 5;
					break;
				case 'style7':
					newTableStyle = setTableStyles[6];
					styleNumber = 6;
					break;
				case 'style8':
					newTableStyle = setTableStyles[7];
					styleNumber = 7;
					break;
				case 'style9':
					newTableStyle = setTableStyles[8];
					styleNumber = 8;
					break;
				case 'style10':
					newTableStyle = setTableStyles[9];
					styleNumber = 9;
					break;
			}
			if (newTableStyle !== undefined)
			{
				var count = 0;
				menuItem.menuItems.forEach(function(menuItem) {
					if (count === styleNumber)
					{
						menuItem.layout.item.style.fontWeight = "bold";
					}
					else
					{
						menuItem.layout.item.style.fontWeight = "normal";
					}
					count++;
				});
				setTableStyles.forEach(function(tableStyle) {
					if (tableStyle === newTableStyle)
					{
						menuItem.table.classList.add(tableStyle);
						var lightTextColor = '#cccccc';
						var darkTextColor = '#333333';
						if (styleNumber >= 5)
						{
							BX.Landing.UI.Button.ColorAction.prototype.prepareOptionsForApplyColorInTableCells(lightTextColor, menuItem.options.options.options);
						}
						else
						{
							BX.Landing.UI.Button.ColorAction.prototype.prepareOptionsForApplyColorInTableCells(darkTextColor, menuItem.options.options.options);
						}
					}
					else
					{
						menuItem.table.classList.remove(tableStyle);
					}
				})
			}
			BX.Landing.Block.Node.Text.currentNode.onChange(true);
		}
	};
})();