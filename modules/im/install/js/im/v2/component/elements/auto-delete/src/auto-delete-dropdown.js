import 'ui.forms';
import { Tag } from 'main.core';
import { MenuManager, type Menu } from 'main.popup';

import { AutoDeleteDelay } from 'im.v2.const';
import { AutoDeleteManager } from 'im.v2.lib.auto-delete';

import './css/auto-delete-dropdown.css';

import type { JsonObject } from 'main.core';

const MENU_ID = 'im-auto-delete-delay-dropdown';

// @vue/component
export const AutoDeleteDropdown = {
	name: 'AutoDeleteDropdown',
	props: {
		currentDelay: {
			type: Number,
			required: true,
		},
	},
	emits: ['delayChange'],
	data(): JsonObject
	{
		return {
			menuOpened: false,
			selectedValue: AutoDeleteDelay.Off,
		};
	},
	computed:
	{
		isEnabled(): boolean
		{
			return this.currentDelay !== AutoDeleteDelay.Off;
		},
		autoDeleteText(): string
		{
			return AutoDeleteManager.getStatusText(this.currentDelay);
		},
	},
	beforeUnmount()
	{
		this.menuInstance?.destroy();
	},
	methods: {
		toggleMenu()
		{
			if (this.currentDelay === AutoDeleteDelay.Off)
			{
				return;
			}

			this.menuInstance = this.getMenuInstance();
			if (this.menuOpened)
			{
				this.menuInstance.close();

				return;
			}

			this.menuInstance.show();
			this.menuOpened = true;
		},
		getMenuInstance(): Menu
		{
			return MenuManager.create({
				id: MENU_ID,
				bindOptions: { forceBindPosition: true, position: 'bottom' },
				offsetTop: 6,
				targetContainer: document.body,
				bindElement: this.$refs.dropdown,
				className: 'bx-im-messenger__scope bx-im-auto-delete-dropdown__scope',
				width: 193,
				items: this.getMenuItems(),
				events: {
					onClose: () => {
						this.menuOpened = false;
						this.menuInstance.destroy();
					},
				},
			});
		},
		getMenuItems(): Array<{ html: HTMLElement, onclick: Function }>
		{
			return Object.values(AutoDeleteDelay).map((delay) => {
				const isSelected = delay === this.currentDelay;

				return {
					html: this.getMenuItemHtml(delay, isSelected),
					onclick: () => {
						this.$emit('delayChange', delay);
						this.menuInstance.close();
					},
				};
			});
		},
		getMenuItemHtml(delay: number, selected: boolean): HTMLElement
		{
			const icon = selected ? '<span class="bx-im-auto-delete-dropdown__icon"></span>' : '';

			return Tag.render`
				<span class="bx-im-auto-delete-dropdown__item">
					${AutoDeleteManager.getStatusText(delay)}
					${icon}
				</span>
			`;
		},
	},
	template: `
		<div
			ref="dropdown"
			class="bx-im-auto-delete-dropdown__container bx-im-auto-delete-dropdown__scope"
			:class="{'--enabled': isEnabled}"
			@click="toggleMenu"
		>
			{{ autoDeleteText }}
		</div>
	`,
};
