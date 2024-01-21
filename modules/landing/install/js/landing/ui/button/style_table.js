(function() {
	'use strict';

	BX.namespace('BX.Landing.UI.Button');

	BX.Landing.UI.Button.StyleTable = function(id, options, textNode)
	{
		BX.Landing.UI.Button.EditorAction.apply(this, arguments);
		this.options = options;
		this.textNode = textNode;
	};

	BX.Landing.UI.Button.StyleTable.prototype = {
		constructor: BX.Landing.UI.Button.StyleTable,
		__proto__: BX.Landing.UI.Button.EditorAction.prototype,

		onClick(event)
		{
			event.preventDefault();
			event.stopPropagation();

			const table = this.options.table;
			const options = [];
			options.table = table;
			options.setTd = this.options.setTd;
			options.target = this.options.target;
			if (!this.menu)
			{
				this.menu = new BX.PopupMenuWindow({
					id: `change-table-style-menu-${BX.Text.getRandom()}`,
					bindElement: this.layout,
					zIndex: -678,
					events: {
						onPopupClose: function() {
							this.textNode.onChange(true);
						}.bind(this),
					},
					items: [
						new BX.PopupMenuItem({
							id: 'style1',
							text: BX.Landing.Loc.getMessage('LANDING_TABLE_STYLE_1'),
							onclick: this.onChangeStyle,
							table,
							options,
						}),
						new BX.PopupMenuItem({
							id: 'style2',
							text: BX.Landing.Loc.getMessage('LANDING_TABLE_STYLE_2'),
							onclick: this.onChangeStyle,
							table,
							options,
						}),
						new BX.PopupMenuItem({
							id: 'style3',
							text: BX.Landing.Loc.getMessage('LANDING_TABLE_STYLE_3'),
							onclick: this.onChangeStyle,
							table,
							options,
						}),
						new BX.PopupMenuItem({
							id: 'style4',
							text: BX.Landing.Loc.getMessage('LANDING_TABLE_STYLE_4'),
							onclick: this.onChangeStyle,
							table,
							options,
						}),
						new BX.PopupMenuItem({
							id: 'style5',
							text: BX.Landing.Loc.getMessage('LANDING_TABLE_STYLE_5'),
							onclick: this.onChangeStyle,
							table,
							options,
						}),
						new BX.PopupMenuItem({
							id: 'style6',
							text: BX.Landing.Loc.getMessage('LANDING_TABLE_STYLE_6'),
							onclick: this.onChangeStyle,
							table,
							options,
						}),
						new BX.PopupMenuItem({
							id: 'style7',
							text: BX.Landing.Loc.getMessage('LANDING_TABLE_STYLE_7'),
							onclick: this.onChangeStyle,
							table,
							options,
						}),
						new BX.PopupMenuItem({
							id: 'style8',
							text: BX.Landing.Loc.getMessage('LANDING_TABLE_STYLE_8'),
							onclick: this.onChangeStyle,
							table,
							options,
						}),
						new BX.PopupMenuItem({
							id: 'style9',
							text: BX.Landing.Loc.getMessage('LANDING_TABLE_STYLE_9'),
							onclick: this.onChangeStyle,
							table,
							options,
						}),
						new BX.PopupMenuItem({
							id: 'style10',
							text: BX.Landing.Loc.getMessage('LANDING_TABLE_STYLE_10'),
							onclick: this.onChangeStyle,
							table,
							options,
						}),
					],
				});
			}
			this.menuItems = this.menu.menuItems;
			if (BX.Dom.hasClass(table, 'landing-table-style-1'))
			{
				BX.Dom.style(this.menuItems[0].layout.item, 'font-weight', 'bold');
			}

			if (BX.Dom.hasClass(table, 'landing-table-style-2'))
			{
				BX.Dom.style(this.menuItems[1].layout.item, 'font-weight', 'bold');
			}

			if (BX.Dom.hasClass(table, 'landing-table-style-3'))
			{
				BX.Dom.style(this.menuItems[2].layout.item, 'font-weight', 'bold');
			}

			if (BX.Dom.hasClass(table, 'landing-table-style-4'))
			{
				BX.Dom.style(this.menuItems[3].layout.item, 'font-weight', 'bold');
			}

			if (BX.Dom.hasClass(table, 'landing-table-style-5'))
			{
				BX.Dom.style(this.menuItems[4].layout.item, 'font-weight', 'bold');
			}

			if (BX.Dom.hasClass(table, 'landing-table-style-6'))
			{
				BX.Dom.style(this.menuItems[5].layout.item, 'font-weight', 'bold');
			}

			if (BX.Dom.hasClass(table, 'landing-table-style-7'))
			{
				BX.Dom.style(this.menuItems[6].layout.item, 'font-weight', 'bold');
			}

			if (BX.Dom.hasClass(table, 'landing-table-style-8'))
			{
				BX.Dom.style(this.menuItems[7].layout.item, 'font-weight', 'bold');
			}

			if (BX.Dom.hasClass(table, 'landing-table-style-9'))
			{
				BX.Dom.style(this.menuItems[8].layout.item, 'font-weight', 'bold');
			}

			if (BX.Dom.hasClass(table, 'landing-table-style-10'))
			{
				BX.Dom.style(this.menuItems[9].layout.item, 'font-weight', 'bold');
			}

			if (this.menu.popupWindow.isShown())
			{
				this.menu.close();
				BX.Dom.style(
					this.menu.popupWindow.popupContainer,
					'top',
					`${parseInt(BX.Dom.style(this.menu.popupWindow.popupContainer, 'top'), 10) + 60}px`,
				);
			}
			else
			{
				this.menu.show();
				BX.Dom.style(
					this.menu.popupWindow.popupContainer,
					'top',
					`${parseInt(BX.Dom.style(this.menu.popupWindow.popupContainer, 'top'), 10) - 60}px`,
				);
			}
		},

		onChangeStyle(event, menuItem)
		{
			event.stopPropagation();
			menuItem.menuWindow.close();

			let newTableStyle = '';
			let styleNumber = '';
			const setTableStyles = [
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
			switch (menuItem.id)
			{
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
				default:
					break;
			}

			if (newTableStyle !== undefined)
			{
				let count = 0;
				this.menuItems.forEach((item) => {
					if (count === styleNumber)
					{
						BX.Dom.style(item.layout.item, 'font-weight', 'bold');
					}
					else
					{
						BX.Dom.style(item.layout.item, 'font-weight', 'normal');
					}
					count++;
				});

				context.applyTableStyles(setTableStyles, newTableStyle, styleNumber, menuItem);
			}
		},

		applyTableStyles(tableStyles, newTableStyle, styleNumber, menuItem)
		{
			tableStyles.forEach((tableStyle) => {
				if (tableStyle === newTableStyle)
				{
					BX.Dom.addClass(menuItem.table, tableStyle);
					if (styleNumber >= 5)
					{
						const lightTextColor = '#ccc';
						BX.Landing.UI.Button.TableColorAction.prototype.prepareOptionsForApplyColorInTableCells(
							lightTextColor,
							menuItem.options.options.options,
						);
					}

					if (styleNumber < 5)
					{
						const darkTextColor = '#333';
						BX.Landing.UI.Button.TableColorAction.prototype.prepareOptionsForApplyColorInTableCells(
							darkTextColor,
							menuItem.options.options.options,
						);
					}
				}
				else
				{
					BX.Dom.removeClass(menuItem.table, tableStyle);
				}
			});
		},
	};

	const context = new BX.Landing.UI.Button.StyleTable();
})();
