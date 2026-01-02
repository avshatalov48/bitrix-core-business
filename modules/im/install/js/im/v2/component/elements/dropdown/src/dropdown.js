import { MenuManager, Menu } from 'main.popup';

import 'ui.forms';

import './dropdown.css';

export type DropdownItem = {
	value: string,
	text: string,
	default?: boolean
};

// @vue/component
export const Dropdown = {
	name: 'ChatDropdown',
	props:
	{
		items: {
			type: Object,
			required: true,
		},
		id: {
			type: String,
			required: true,
		},
	},
	emits: ['itemChange'],
	data()
	{
		return {
			selectedElement: '',
			menuOpened: false,
		};
	},
	computed:
	{
		formattedItems(): {[value: string]: DropdownItem}
		{
			const map = {};
			this.items.forEach((item) => {
				map[item.value] = item;
			});

			return map;
		},
		defaultItem(): DropdownItem
		{
			return this.items.find((item) => {
				return item.default === true;
			});
		},
	},
	created()
	{
		this.menuInstance = null;
		if (this.defaultItem)
		{
			this.selectedElement = this.defaultItem.value;
		}
	},
	beforeUnmount()
	{
		this.menuInstance?.destroy();
	},
	methods:
	{
		toggleMenu()
		{
			if (!this.menuInstance)
			{
				this.menuInstance = this.getMenuInstance();
			}

			if (this.menuOpened)
			{
				this.menuInstance.close();

				return;
			}

			this.menuInstance.show();
			const width = this.$refs.container.clientWidth;
			this.menuInstance.getPopupWindow().setWidth(width);
			this.menuOpened = true;
		},
		getMenuInstance(): Menu
		{
			return MenuManager.create({
				id: this.id,
				bindOptions: { forceBindPosition: true, position: 'bottom' },
				targetContainer: document.body,
				bindElement: this.$refs.container,
				items: this.getMenuItems(),
				events: {
					onClose: () => {
						this.menuOpened = false;
					},
				},
			});
		},
		getMenuItems(): Array<{ text: string, onclick: Function }>
		{
			return Object.values(this.formattedItems).map((item) => {
				return {
					text: item.text,
					onclick: () => {
						this.selectedElement = item.value;
						this.$emit('itemChange', item.value);
						this.menuInstance.close();
					},
				};
			});
		},
	},
	template: `
		<div class="bx-im-dropdown__container bx-im-dropdown__scope">
			<div @click="toggleMenu" class="ui-ctl ui-ctl-xl ui-ctl-w100 ui-ctl-after-icon ui-ctl-dropdown" ref="container">
				<div class="ui-ctl-after ui-ctl-icon-angle"></div>
				<div class="ui-ctl-element">{{ formattedItems[selectedElement].text }}</div>
			</div>
		</div>
	`,
};
