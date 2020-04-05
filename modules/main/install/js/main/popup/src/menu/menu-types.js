import { PopupOptions } from '../popup/popup-types';

export type MenuOptions = PopupOptions & {
	items: MenuItemOptions[]
};

export type MenuItemOptions = {
	id?: string,
	text?: string,
	title?: string,
	disabled?: boolean,
	href?: string,
	target?: string,
	className?: string,
	delimiter?: boolean,
	menuShowDelay?: number,
	subMenuOffsetX?: number,
	events?: { [event: string]: (event) => {} },
	dataset?: { [key: string]: string },
	onclick?: () => {} | string,
	cacheable?: boolean,
	items?: MenuItemOptions[]
};