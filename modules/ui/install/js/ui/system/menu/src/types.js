import type { PopupOptions } from 'main.popup';
import type { CounterOptions } from 'ui.cnt';
import type { ButtonOptions } from 'ui.buttons';

export type MenuOptions = PopupOptions & {
	sections: MenuSectionOptions[],
	items: MenuItemOptions[],
	richHeader: {
		design: 'default' | 'copilot',
		title: string,
		subtitle: string,
		icon: string,
		onClick: Function,
	},
	closeOnItemClick: boolean,
};

export type MenuItemOptions = {
	id: string,
	sectionCode: string,
	design: 'default' | 'accent-1' | 'accent-2' | 'alert' | 'copilot' | 'disabled',
	onClick: Function,
	title: string,
	subtitle: string,
	badgeText: BadgeText,
	isSelected: boolean,
	icon: string,
	extraIcon: {
		icon: string,
		onClick: Function,
		isSelected: boolean,
	},
	counter: CounterOptions,
	svg: SVGElement,
	subMenu: MenuOptions[],
	isLocked: boolean,
	closeOnSubItemClick: boolean,
	uiButtonOptions: ButtonOptions,
};

type BadgeText = {
	title: string,
	color: string,
};

export type MenuSectionOptions = {
	design: 'default' | 'accent',
	code: string,
	title: string,
};

export type MenuItemCallbacks = {
	getTargetContainer: Function,
	onMouseEnter: Function,
	onSubMenuItemClick: Function,
};
