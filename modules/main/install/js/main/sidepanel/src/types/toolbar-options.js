import { type ToolbarItemOptions } from './toolbar-item-options';

export type ToolbarOptions = {
	context: string,
	position?: { top?: string, left?: string, right?: string, bottom?: string },
	shiftedPosition?: { top?: string, left?: string, right?: string, bottom?: string },
	collapsed?: boolean,
	maxVisibleItems?: number,
	items?: ToolbarItemOptions[],
};
