import {RecentCallStatus} from 'im.v2.const';

export type CallItem = {
	dialogId: string,
	name: string,
	call: Object,
	state: $Values<typeof RecentCallStatus>
};