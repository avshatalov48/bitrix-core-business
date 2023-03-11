import {BaseEvent} from "main.core.events";

export type menuOptions = {
	popupOptions: PopupOptions,
	contentData: {
		rows: Array<RowOptions>,
	},
	events?: { [eventName: string]: (event: BaseEvent) => void },
};

export type PopupOptions = {
	id: string,
	target: Element | { left: number, top: number } | null | MouseEvent,
	autoHide?: boolean,
	closeByEsc?: boolean,
	offsetLeft?: number,
	offsetTop?: number,
	overlay?: {
		backgroundColor?: string,
		opacity?: number,
	},
	cacheable?: boolean,
	buttons?: [],
	events?: { [eventName: string]: (event: BaseEvent) => void },
};

export type RowOptions = {
	label?: string,
	values: Array<{
		id: string,
		text: string,
	}>,
	onClick?: (event: BaseEvent) => void,
};