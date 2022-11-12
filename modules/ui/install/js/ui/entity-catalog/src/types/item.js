import type { ButtonData } from './button';

export type ItemData = {
	id: string | number,
	title: string,
	subtitle?: string,
	groupIds: Array<number | string>,
	description: string,
	tags?: Array<string>,
	button?: ButtonData,
	customData?: { [key: string]: any },
};