import type { TextNodeOptions } from '../common/text-node-options';

export type TagItemOptions = {
	id: string,
	entityId: number | string,
	entityType?: string,
	title?: string | TextNodeOptions,
	avatar?: string,
	textColor?: string,
	bgColor?: string,
	fontWeight?: string,
	link?: string,
	onclick?: Function,
	maxWidth?: number,
	deselectable?: boolean,
	animate?: boolean,
	customData?: { [key: string]: any }
};