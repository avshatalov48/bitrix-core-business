import {ItemData} from './item';

export type GroupData = {
	id: GroupId,
	name: string,
	icon?: string,
	tags: Array<string>,
	adviceTitle?: string,
	adviceAvatar?: string,
	customData?: {},
	selected?: boolean,
	disabled?: boolean,
	compare?: (ItemData, ItemData) => number,
}

export type GroupId = number | string;