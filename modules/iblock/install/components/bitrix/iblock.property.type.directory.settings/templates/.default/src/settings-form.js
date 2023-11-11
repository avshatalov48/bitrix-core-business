import { Loc, Type, Uri } from 'main.core';
import { Menu, MenuManager } from 'main.popup';
import { BitrixVue } from 'ui.vue3';
import { GridController } from './grid-controller';

export class SettingsForm
{
	gridController: GridController;
	directoryItems: Array;
	newDirectoryValue = '-1';
	selectedDirectory: String;
	app: BitrixVue;

	static createApp(gridController: GridController, options): SettingsForm
	{
		const form = new SettingsForm(gridController, options);

		form.app = BitrixVue.createApp(form.getAppConfig()).mount(options.settingsFormSelector);

		return form;
	}

	constructor(gridController: GridController, options)
	{
		this.gridController = gridController;
		this.directoryItems = Type.isArray(options.directoryItems) ? options.directoryItems : [];

		this.selectedDirectory = this.newDirectoryValue;
		if (options.selectedDirectory)
		{
			const selectedItem = this.directoryItems.find((item) => item.VALUE === options.selectedDirectory);
			if (selectedItem)
			{
				this.selectedDirectory = selectedItem.VALUE;
			}
		}
	}

	reloadDirectory(directoryTableName): never
	{
		const url = new Uri(location.href);
		url.setQueryParam('directoryTableName', directoryTableName);
		location.href = url.toString();
	}

	getDirectoryName(): String
	{
		return this.app.directoryName || '';
	}

	getDirectoryValue(): String
	{
		return this.app.directoryValue || '';
	}

	getAppConfig()
	{
		const form = this;

		return (function() {
			return {
				data()
				{
					return {
						directoryName: null,
						directoryValue: form.selectedDirectory,
						directoryItems: form.directoryItems,
					};
				},

				computed: {
					selectedDirectoryName()
					{
						if (this.isNewDirectory)
						{
							return Loc.getMessage('IBLOCK_PROPERTY_TYPE_DIRECTORY_SETTINGS_NEW_DIRECTORY_NAME');
						}

						return this.directoryItemsMap[this.directoryValue];
					},

					directoryItemsMap()
					{
						const result = {};

						this.directoryItems.forEach((item) => {
							result[item.VALUE] = item.NAME;
						});

						return result;
					},

					directoryItemsFull()
					{
						const result = [
							{
								NAME: Loc.getMessage('IBLOCK_PROPERTY_TYPE_DIRECTORY_SETTINGS_NEW_DIRECTORY_NAME'),
								VALUE: form.newDirectoryValue,
							},
						];

						result.push(...this.directoryItems);

						return result;
					},

					directoryItemsAsMenuItems()
					{
						return this.directoryItemsFull.map((item) => {
							return {
								id: item.VALUE,
								text: item.NAME,
								onclick: this.onSelectDirectoryItem.bind(this),
							};
						});
					},

					isNewDirectory()
					{
						return this.directoryValue === form.newDirectoryValue;
					},
				},

				methods: {
					getDirectoryDropdownMenu(bindElement: Element|null): Menu
					{
						const menuId = 'directory-items';
						let menu = MenuManager.getMenuById(menuId);

						// destroy menu if binded element destroyed
						if (
							menu
							&& bindElement
							&& menu.getPopupWindow().bindElement !== bindElement
						)
						{
							MenuManager.destroy(menu.getId());
							menu = null;
						}

						if (!menu && bindElement)
						{
							menu = MenuManager.create({
								id: menuId,
								items: this.directoryItemsAsMenuItems,
								bindElement,
							});
						}

						return menu;
					},

					toggleDirectoryDropdown(e)
					{
						this.getDirectoryDropdownMenu(e.target).toggle();
					},

					onSelectDirectoryItem(e, item)
					{
						this.directoryValue = item.id;
						this.getDirectoryDropdownMenu().close();

						form.reloadDirectory(this.directoryValue);
					},

					normalizeName(e)
					{
						const input = e.target;
						if (input)
						{
							input.value = BX.translit(
								input.value,
								{
									change_case: 'L',
									replace_space: '',
									delete_repeat_replace: true,
								}
							);
						}
					},

					addNewRow()
					{
						form.gridController.prependRowEditor();
					},
				},
			};
		})();
	}
}
