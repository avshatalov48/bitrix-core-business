import {TabId} from '../const';

export type Tab = {
	id: $Values<typeof TabId>,
	loc: string,
	isNew: boolean
};